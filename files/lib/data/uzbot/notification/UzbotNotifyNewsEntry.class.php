<?php
namespace news\data\uzbot\notification;
use news\data\news\News;
use news\data\news\NewsAction;
use news\data\news\image\standard\NewsImageStandard;
use news\data\news\image\NewsImageAction;
use news\data\news\image\NewsImageEditor;
use news\system\label\object\NewsLabelObjectHandler;
use wcf\data\uzbot\Uzbot;
use wcf\data\uzbot\log\UzbotLogEditor;
use wcf\system\exception\SystemException;
use wcf\system\html\input\HtmlInputProcessor;
use wcf\system\label\object\UzbotNotificationLabelObjectHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\MessageUtil;
use wcf\util\StringUtil;

/**
 * Creates news for Bot
 *
 * @author		2019-2022 Zaydowicz
 * @license		GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package		com.uz.wcf.bot3.news
 */
class UzbotNotifyNewsEntry {
	public function send(Uzbot $bot, $content, $subject, $teaser, $language, $receiver, $tags) {
		// prepare text and data
		$defaultLanguage = LanguageFactory::getInstance()->getLanguage(LanguageFactory::getInstance()->getDefaultLanguageID());
		
		$content = MessageUtil::stripCrap($content);
		$subject = MessageUtil::stripCrap(StringUtil::stripHTML($subject));
		if (mb_strlen($subject) > 255) $subject = mb_substr($subject, 0, 250) . '...';
		
		$content = str_replace('[bot-title]', $bot->botTitle, $content);
		
		// set publication time
		$publicationTime = TIME_NOW;
		if (isset($bot->publicationTime) && $bot->publicationTime) {
			$publicationTime = $bot->publicationTime;
		}
		
		if (!$bot->testMode) {
			$htmlInputProcessor = new HtmlInputProcessor();
			$htmlInputProcessor->process($content, 'de.wbb-elite.news.message', 0);
			$text = $htmlInputProcessor->getHtml();
			
			// get notification data
			$newsEntryData = unserialize($bot->newsEntryData);
			
			// tags to include feedreader
			if (!MODULE_TAGGING) {
				$tags = [];
			}
			else {
				if (isset($bot->feedreaderUseTags) && $bot->feedreaderUseTags) {
					if (isset($bot->feedreaderTags) && !empty($bot->feedreaderTags)) {
						$tags = array_unique(array_merge($tags, $bot->feedreaderTags));
					}
				}
			}
			
			// create news
			try {
				$data = [
						'userID' => $bot->senderID,
						'username' => $bot->sendername,
						'onlySidebar' => $newsEntryData['newsOnlySidebar'],
						'bigVersion' => $newsEntryData['newsBigVersion'],
						'isDeleted' => 0,
						'time' => $publicationTime,
						'isDisabled' => $newsEntryData['newsIsDisabled'],
						'subject' => $subject,
						'text' => $text,
						'teaser' => $teaser,
						'isMultilingual' => 0,
						'isHot' => $newsEntryData['newsIsHot'],
						'enableTime' => 0,
						'isUzbot' => 1
				];
				
				// sources
				$sources = [];
				$source = MessageUtil::stripCrap($newsEntryData['newsSource']);
				$sourceUrl = MessageUtil::stripCrap($newsEntryData['newsSourceUrl']);
				if (!empty($source) || !empty($sourceUrl)) {
					$sources[] = [
							'sourceSubject' => $source,
							'sourceUrl' => $sourceUrl
					];
				}
				
				// image
				$previewImage = null;
				if ($newsEntryData['newsImageID']) {
					$image = new NewsImageStandard($newsEntryData['newsImageID']);
					if ($image->imageID) {
						// copy standard image
						$previewImage = NewsImageEditor::create([
								'uploadTime' => TIME_NOW,
								'userID' => $bot->senderID,
								'filename' => $image->filename,
								'fileExtension' => $image->fileExtension,
								'filesize' => $image->filesize,
								'languageID' => null,
								'fileType' => $image->fileType,
								'fileHash' => $image->fileHash
						]);
						$dir = dirname($previewImage->getLocation());
						if (!@ file_exists($dir)) {
							FileUtil::makePath($dir);
						}
						@copy($image->getLocation(), $previewImage->getLocation());
						$action = new NewsImageAction([$previewImage], 'generateThumbnails');
						$action->executeAction();
					}
				}
				
				$newsData = [
						'data' => $data,
						'categoryIDs' => unserialize($newsEntryData['newsCategoryIDs']),
						'attachmentHandler' => null,
						'htmlInputProcessor' => $htmlInputProcessor,
						'previewImage' => $previewImage,
						'sources' => $sources,
						'tags' => $tags
				];
				
				$this->objectAction = new NewsAction([], 'create', $newsData);
				$resultValues = $this->objectAction->executeAction();
				
				$news = new News($resultValues['returnValues']->newsID);
				
				// save labels only from version 3.1
				$assignedLabels = UzbotNotificationLabelObjectHandler::getInstance()->getAssignedLabels([$bot->botID], false);
				if (!empty($assignedLabels)) {
					$labelIDs = [];
					foreach ($assignedLabels as $labels) {
						foreach ($labels as $label) {
							$labelIDs[] = $label->labelID;
						}
					}
					NewsLabelObjectHandler::getInstance()->setLabels($labelIDs, $news->newsID);
				}
			}
			catch (SystemException $e) {
				// users may get lost; check sender again to abort
				if (!$bot->checkSender(true, true)) return false;
				
				// report any other error und continue
				if ($bot->enableLog) {
					$error = $defaultLanguage->get('wcf.acp.uzbot.log.notify.error') . ' ' . $e->getMessage();
					
					UzbotLogEditor::create([
							'bot' => $bot,
							'status' => 1,
							'count' => 1,
							'additionalData' => $error
					]);
				}
			}
		}
		else {
			if (mb_strlen($content) > 63500) $content = mb_substr($content, 0, 63500) . ' ...';
			$result = serialize([$subject, $teaser, $content]);
			
			UzbotLogEditor::create([
					'bot' => $bot,
					'count' => 1,
					'testMode' => 1,
					'additionalData' => $result
			]);
		}
	}
}
