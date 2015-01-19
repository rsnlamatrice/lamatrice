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
	{assign var=PICKLIST_VALUES value=$RELATED_RECORD_MODEL->getListViewPicklistValues('satisfaction')}
	
	{foreach item=RELATED_RECORD from=$RELATED_RECORDS}
		<div class="contactsContainer">
			<ul class="unstyled">
				<li title="{$RELATED_RECORD->get('label')}">
					<div class="row-fluid">
						<div class="span4 textOverflowEllipsis">
							<a href="{$RELATED_RECORD->getDetailViewUrl()}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}" title="{$RELATED_RECORD->getDisplayValue('lastname')}">
								{if $RELATED_RECORD->get('disabled')}/!\&nbsp;{/if}
								{$RELATED_RECORD->get('name')}
							</a>
						</div>
						<div class="span8 textOverflowEllipsis">
							<a href="{$RELATED_RECORD->getDetailViewUrl()}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}" title="{$RELATED_RECORD->getDisplayValue('lastname')}">
								<input value="{$RELATED_RECORD->get('defaultvalue')}"/>
							</a>
						</div>
					</div>
				</li>
			</ul>
		</div>
	{/foreach}
{/strip}
