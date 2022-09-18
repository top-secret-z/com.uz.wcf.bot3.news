<?php

/*
 * Copyright by Udo Zaydowicz.
 * Modified by SoftCreatR.dev.
 *
 * License: http://opensource.org/licenses/lgpl-license.php
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
namespace news\system\event\listener;

use news\data\category\NewsCategoryNodeTree;
use news\data\entry\Entry;
use news\data\news\image\standard\NewsImageStandard;
use news\data\news\News;
use wcf\data\uzbot\notification\UzbotNotify;
use wcf\data\uzbot\type\UzbotType;
use wcf\system\category\CategoryHandler;
use wcf\system\event\listener\IParameterizedEventListener;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Listen to addForm events for Bot
 */

class UzbotAddFormNewsListener implements IParameterizedEventListener
{
    /**
     * instance of UzbotAddForm
     */
    protected $eventObj;

    /**
     * general data
     */
    protected $newsCategoryList;

    /**
     * entry data
     */
    protected $newsCategoryIDs = [];

    protected $newsBigVersion = 0;

    protected $newsIsDisabled = 0;

    protected $newsOnlySidebar = 0;

    protected $newsIsHot = 0;

    protected $newsSource = '';

    protected $newsSourceUrl = '';

    protected $newsImageID = 0;

    /**
     * @inheritDoc
     */
    public function execute($eventObj, $className, $eventName, array &$parameters)
    {
        $this->eventObj = $eventObj;
        $this->{$eventName}();
    }

    /**
     * Handles the readData event. Only in UzbotEdit!
     */
    protected function readData()
    {
        if (empty($_POST)) {
            if (!empty($this->eventObj->uzbot->newsEntryData)) {
                $newsEntryData = \unserialize($this->eventObj->uzbot->newsEntryData);

                $this->newsCategoryIDs = \unserialize($newsEntryData['newsCategoryIDs']);
                $this->newsBigVersion = $newsEntryData['newsBigVersion'];
                $this->newsIsDisabled = $newsEntryData['newsIsDisabled'];
                $this->newsOnlySidebar = $newsEntryData['newsOnlySidebar'];
                $this->newsIsHot = $newsEntryData['newsIsHot'];
                $this->newsSource = $newsEntryData['newsSource'];
                $this->newsSourceUrl = $newsEntryData['newsSourceUrl'];
                $this->newsImageID = $newsEntryData['newsImageID'];
            }
        }
    }

    /**
     * Handles the assignVariables event.
     */
    protected function assignVariables()
    {
        // get categories
        $categoryTree = new NewsCategoryNodeTree('de.wbb-elite.news.news');
        $this->newsCategoryList = $categoryTree->getIterator();
        $this->newsCategoryList->setMaxDepth(0);

        WCF::getTPL()->assign([
            'newsCategoryList' => $this->newsCategoryList,
            'newsCategoryIDs' => $this->newsCategoryIDs,
            'newsBigVersion' => $this->newsBigVersion,
            'newsIsDisabled' => $this->newsIsDisabled,
            'newsOnlySidebar' => $this->newsOnlySidebar,
            'newsIsHot' => $this->newsIsHot,
            'newsSource' => $this->newsSource,
            'newsSourceUrl' => $this->newsSourceUrl,
            'newsImageID' => $this->newsImageID,
        ]);
    }

    /**
     * Handles the readFormParameters event.
     */
    protected function readFormParameters()
    {
        if (isset($_REQUEST['newsCategoryIDs']) && \is_array($_REQUEST['newsCategoryIDs'])) {
            $this->newsCategoryIDs = ArrayUtil::toIntegerArray($_REQUEST['newsCategoryIDs']);
        }

        $this->newsBigVersion = $this->newsIsDisabled = $this->newsOnlySidebar = $this->newsIsHot = 0;
        if (isset($_POST['newsIsDisabled'])) {
            $this->newsIsDisabled = \intval($_POST['newsIsDisabled']);
        }
        if (isset($_POST['newsBigVersion'])) {
            $this->newsBigVersion = \intval($_POST['newsBigVersion']);
        }
        if (isset($_POST['newsIsHot'])) {
            $this->newsIsHot = \intval($_POST['newsIsHot']);
        }
        if (isset($_POST['newsOnlySidebar'])) {
            $this->newsOnlySidebar = \intval($_POST['newsOnlySidebar']);
        }

        if (isset($_POST['newsSource'])) {
            $this->newsSource = StringUtil::trim($_POST['newsSource']);
        }
        if (isset($_POST['newsSourceUrl'])) {
            $this->newsSourceUrl = StringUtil::trim($_POST['newsSourceUrl']);
        }
        if (isset($_POST['newsImageID'])) {
            $this->newsImageID = \intval($_POST['newsImageID']);
        }
    }

    /**
     * Handles the validate event.
     */
    protected function validate()
    {
        // Get type / notify data
        $type = UzbotType::getTypeByID($this->eventObj->typeID);
        $notify = UzbotNotify::getNotifyByID($this->eventObj->notifyID);

        // news notify
        if ($notify->notifyTitle == 'news') {
            // newsCategoryIDs
            if (empty($this->newsCategoryIDs)) {
                throw new UserInputException('newsCategoryIDs', 'notConfigured');
            }
            $categories = [];
            foreach ($this->newsCategoryIDs as $categoryID) {
                $category = CategoryHandler::getInstance()->getCategory($categoryID);
                if ($category === null) {
                    throw new UserInputException('newsCategoryIDs', 'invalid');
                }
            }

            // image ID
            if ($this->newsImageID) {
                $image = new NewsImageStandard($this->newsImageID);
                if (!$image->imageID) {
                    throw new UserInputException('newsImageID', 'missing');
                }
            }
        }
    }

    /**
     * Handles the save event.
     */
    protected function save()
    {
        $newsEntryData = [
            'newsCategoryIDs' => \serialize($this->newsCategoryIDs),
            'newsBigVersion' => $this->newsBigVersion,
            'newsIsDisabled' => $this->newsIsDisabled,
            'newsOnlySidebar' => $this->newsOnlySidebar,
            'newsIsHot' => $this->newsIsHot,
            'newsSource' => $this->newsSource,
            'newsSourceUrl' => $this->newsSourceUrl,
            'newsImageID' => $this->newsImageID,
        ];

        $this->eventObj->additionalFields = \array_merge($this->eventObj->additionalFields, [
            'newsEntryData' => \serialize($newsEntryData),
        ]);
    }

    /**
     * Handles the saved event.
     */
    protected function saved()
    {
        // not yet ...
    }
}
