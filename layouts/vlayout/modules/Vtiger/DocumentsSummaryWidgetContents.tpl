{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
* ("License"); You may not use this file except in compliance with the License
* The Original Code is:  vtiger CRM Open Source
* The Initial Developer of the Original Code is vtiger.
* Portions created by vtiger are Copyright (C) vtiger.
* All Rights Reserved.
*
********************************************************************************/
-->*}
{strip}
	<div class="row-fluid">
		<span class="span7">
			<strong>{vtranslate('Title','Documents')}</strong>
		</span>
		<span class="span4">
			<strong>Date</strong>
		</span>
		<span class="span1 horizontalLeftSpacingForSummaryWidgetHeader">
			<span {* ED141210 class="pull-right" *}>
				{*<strong>{vtranslate('File Name', 'Documents')}</strong>*}
			</span>
		</span>
	</div>
	{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
							
		{assign var=DOWNLOAD_FILE_URL value=$RELATED_RECORD->getDownloadFileURL()}
		{assign var=DOWNLOAD_STATUS value=$RELATED_RECORD->get('filestatus')}
		{assign var=DOWNLOAD_LOCATION_TYPE value=$RELATED_RECORD->get('filelocationtype')}
		<div class="recentActivitiesContainer" id="relatedDocuments">
			<ul class="unstyled">
				<li>
					<div class="row-fluid" id="documentRelatedRecord">
						<span class="span7 textOverflowEllipsis">
							<a href="{$RELATED_RECORD->getDetailViewUrl()}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}" title="{$RELATED_RECORD->getDisplayValue('notes_title')}">
								<div style="background-color:{$RELATED_RECORD->get('uicolor')}; margin-left:0;" class="picklistvalue-uicolor">&nbsp;</div>
								{$RELATED_RECORD->getDisplayValue('notes_title')}
							</a>
						</span>
						<span class="span4 textOverflowEllipsis">
							{if is_string($RELATED_RECORD->get('dateapplication'))}
								{preg_replace('/^(\d{4})\D(\d{2})\D(\d{2}).*$/', '$3-$2-$1', $RELATED_RECORD->get('dateapplication'))}
							{elseif is_object($RELATED_RECORD->get('dateapplication'))}
								{$RELATED_RECORD->get('dateapplication')->format('d/m/Y')}
							{else is_array($RELATED_RECORD->get('dateapplication'))}
								{foreach item=DATE from=$RELATED_RECORD->get('dateapplication')}
									{$DATE->format('d/m/Y')}&nbsp;
								{/foreach}
							{/if}&nbsp;
							{if $RELATED_RECORD->get('data')}
								{htmlentities($RELATED_RECORD->get('data'))}
							{/if}
						</span>
						<span class="span1 textOverflowEllipsis" id="DownloadableLink">
							{if $DOWNLOAD_FILE_URL}
								<a href="{$DOWNLOAD_FILE_URL}"><span class="ui-icon ui-icon-disk"></span>&nbsp;</a>
							{/if}
							{*ED150907 if $DOWNLOAD_STATUS eq 1}
								{$RELATED_RECORD->getDisplayValue('filename', $RELATED_RECORD->getId(), $RELATED_RECORD)}
							{else}
								{$RELATED_RECORD->get('filename')} 
							{/if*}
						</span>
					</div>
				</li>
			</ul>
		</div>
	{/foreach}
	{assign var=NUMBER_OF_RECORDS value=count($RELATED_RECORDS)}
	{if $NUMBER_OF_RECORDS eq 5}
		<div class="row-fluid">
			<div class="pull-right">
				<a class="moreRecentDocuments cursorPointer">{vtranslate('LBL_MORE',$MODULE_NAME)}</a>
			</div>
		</div>
	{/if}
{/strip}