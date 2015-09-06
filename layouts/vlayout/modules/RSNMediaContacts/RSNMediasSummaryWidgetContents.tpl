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
<div class="relatedContainer">
    <input type="hidden" name="relatedModuleName" class="relatedModuleName" value="{$RELATED_MODULE}" />
</div>
	
	{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
		<div class="contactsContainer">
			<ul class="unstyled">
				<li>
					<div class="row-fluid">
					    <div class="span4 textOverflowEllipsis">
						    <a href="{$RELATED_RECORD->getDetailViewUrl()}">
							    {$RELATED_RECORD->getDisplayValue('nom')}
						    </a>
					    </div>
					    <div class="span5 textOverflowEllipsis">
						    <a href="{$RELATED_RECORD->getDetailViewUrl()}">
							    {$RELATED_RECORD->getDisplayValue('rsntypesmedia')}
						    </a>
					    </div>
					</div>
				</li>
			</ul>
		</div>
	{/foreach}
	{assign var=NUMBER_OF_RECORDS value=count($RELATED_RECORDS)}
	{if $NUMBER_OF_RECORDS eq 15}
		<div class="row-fluid">
			<div class="pull-right">
				<a class="moreRecentContacts cursorPointer">{vtranslate('LBL_MORE',$MODULE_NAME)}</a>
			</div>
		</div>
	{/if}
{/strip}
