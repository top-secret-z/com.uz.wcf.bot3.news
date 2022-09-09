<div class="section notifyNewsSettings">
	<header class="sectionHeader">
		<h2 class="sectionTitle">{lang}wcf.acp.uzbot.notify.news.setting{/lang}</h2>
	</header>
	
	<dl{if $errorField == 'newsCategoryIDs'} class="formError"{/if}>
		<dt><label for="newsCategoryIDs">{lang}wcf.acp.uzbot.notify.news.categoryIDs{/lang}</label></dt>
		<dd></dd>
	</dl>
	
	{if !$flexibleNewsCategoryList|isset}{assign var=flexibleNewsCategoryList value=$newsCategoryList}{/if}
	{if !$flexibleNewsCategoryListName|isset}{assign var=flexibleNewsCategoryListName value='newsCategoryIDs'}{/if}
	{if !$flexibleNewsCategoryListID|isset}{assign var=flexibleNewsCategoryListID value='flexibleNewsCategoryList'}{/if}
	{if !$flexibleNewsCategoryListSelectedIDs|isset}{assign var=flexibleNewsCategoryListSelectedIDs value=$newsCategoryIDs}{/if}
	<ol class="flexibleCategoryList" id="{$flexibleNewsCategoryListID}">
		{foreach from=$flexibleNewsCategoryList item=categoryItem}
			<li>
				<div class="containerHeadline">
					<h3><label{if $categoryItem->getDescription()} class="jsTooltip" title="{$categoryItem->getDescription()}"{/if}><input type="checkbox" name="{$flexibleNewsCategoryListName}[]" value="{@$categoryItem->categoryID}" class="jsCategory"{if $categoryItem->categoryID|in_array:$flexibleNewsCategoryListSelectedIDs} checked{/if}> {$categoryItem->getTitle()}</label></h3>
				</div>
				
				{if $categoryItem->hasChildren()}
					<ol>
						{foreach from=$categoryItem item=subCategoryItem}
							<li>
								<label{if $subCategoryItem->getDescription()} class="jsTooltip" title="{$subCategoryItem->getDescription()}"{/if} style="font-size: 1rem;"><input type="checkbox" name="{$flexibleNewsCategoryListName}[]" value="{@$subCategoryItem->categoryID}" class="jsChildCategory"{if $subCategoryItem->categoryID|in_array:$flexibleNewsCategoryListSelectedIDs} checked{/if}> {$subCategoryItem->getTitle()}</label>
								
								{if $subCategoryItem->hasChildren()}
									<ol>
										{foreach from=$subCategoryItem item=subSubCategoryItem}
											<li>
												<label{if $subSubCategoryItem->getDescription()} class="jsTooltip" title="{$subSubCategoryItem->getDescription()}"{/if}><input type="checkbox" name="{$flexibleNewsCategoryListName}[]" value="{@$subSubCategoryItem->categoryID}" class="jsSubChildCategory"{if $subSubCategoryItem->categoryID|in_array:$flexibleNewsCategoryListSelectedIDs} checked{/if}> {$subSubCategoryItem->getTitle()}</label>
											</li>
										{/foreach}
									</ol>
								{/if}
							</li>
						{/foreach}
					</ol>
				{/if}
			</li>
		{/foreach}
	</ol>
	
	{if $errorField == 'newsCategoryIDs'}
		<small class="innerError">
			{lang}wcf.acp.uzbot.notify.news.categoryIDs.error.{@$errorType}{/lang}
		</small>
	{/if}
	
	<dl>
		<dt>{lang}wcf.acp.uzbot.notify.news.status{/lang}</dt>
		<dd>
			<label><input name="newsIsDisabled" type="checkbox" value="1"{if $newsIsDisabled} checked{/if}> {lang}wcf.acp.uzbot.notify.news.status.isDisabled{/lang}</label>
			<label><input name="newsIsHot" type="checkbox" value="1"{if $newsIsHot} checked{/if}> {lang}wcf.acp.uzbot.notify.news.status.isHot{/lang}</label>
			<label><input name="newsBigVersion" type="checkbox" value="1"{if $newsBigVersion} checked{/if}> {lang}wcf.acp.uzbot.notify.news.status.bigVersion{/lang}</label>
			<label><input name="newsOnlySidebar" type="checkbox" value="1"{if $newsOnlySidebar} checked{/if}> {lang}wcf.acp.uzbot.notify.news.status.onlySidebar{/lang}</label>
		</dd>
	</dl>
	
	<dl>
		<dt><label for="newsSource">{lang}wcf.acp.uzbot.notify.news.source{/lang}</label></dt>
		<dd>
			<input type="text" id="newsSource" name="newsSource" value="{$newsSource}" maxlength="255" class="long" />
		</dd>
	</dl>
	
	<dl>
		<dt><label for="newsSourceUrl">{lang}wcf.acp.uzbot.notify.news.sourceUrl{/lang}</label></dt>
		<dd>
			<input type="text" id="newsSourceUrl" name="newsSourceUrl" value="{$newsSourceUrl}" maxlength="255" class="long" />
		</dd>
	</dl>
	
	<dl{if $errorField == 'newsImageID'} class="formError"{/if}>
		<dt><label for="newsImageID">{lang}wcf.acp.uzbot.notify.news.newsImageID{/lang}</label></dt>
		<dd>
			<input type="number" name="newsImageID" id="newsImageID" value="{$newsImageID}" class="small" min="0" />
			<small>{lang}wcf.acp.uzbot.notify.news.newsImageID.description{/lang}</small>
			
			{if $errorField == 'newsImageID'}
				<small class="innerError">
					{lang}wcf.acp.uzbot.notify.news.newsImageID.error.{@$errorType}{/lang}
				</small>
			{/if}
		</dd>
	</dl>
</div>
