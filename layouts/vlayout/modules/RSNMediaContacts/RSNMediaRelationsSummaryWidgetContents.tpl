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
				<li>
					<div class="row-fluid">
						<div class="span2 textOverflowEllipsis">
							<a href="{$RELATED_RECORD->getDetailViewUrl()}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}" title="{$RELATED_RECORD->getDisplayValue('lastname')}">
								{$RELATED_RECORD->getDisplayValue('daterelation')}
							</a>
						</div>
						<div class="span5 textOverflowEllipsis">
							<a href="{$RELATED_RECORD->getDetailViewUrl()}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}" title="{$RELATED_RECORD->getDisplayValue('lastname')}">
								{$RELATED_RECORD->getName()}
								{if $RELATED_RECORD->get('comment')}<br/>{$RELATED_RECORD->get('comment')}{/if}
							</a>
						</div>
						<div class="span3 textOverflowEllipsis">
							<a href="{$RELATED_RECORD->getDetailViewUrl()}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}" title="{$RELATED_RECORD->getDisplayValue('lastname')}">
								{$RELATED_RECORD->getDisplayValue('byuserid')}
							</a>
						</div>
						<div class="span1">
								{assign var=FIELD_VALUE value=$RELATED_RECORD->get('satisfaction')}
								{if is_array($FIELD_VALUE)}{assign var=FIELD_VALUE value=$FIELD_VALUE[0]}{/if}
								{if $FIELD_VALUE eq null}{assign var=FIELD_VALUE value=0}{/if}
								{if $PICKLIST_VALUES && array_key_exists($FIELD_VALUE, $PICKLIST_VALUES)}
									{assign var=PICKLIST_ITEM value=$PICKLIST_VALUES[$FIELD_VALUE]}
								{else}
									{assign var=PICKLIST_ITEM value=$FIELD_VALUE}
								{/if}
								{if is_array($PICKLIST_ITEM)}
									{assign var=PICKLIST_LABEL value=$PICKLIST_ITEM['label']}
									{if isset($PICKLIST_ITEM['class'])}
									    {assign var=PICKLIST_CLASS value=$PICKLIST_ITEM['class']}
									{else}
									    {assign var=PICKLIST_CLASS value=''}
									{/if}
									{assign var=PICKLIST_ICON value=$PICKLIST_ITEM['icon']}
								{else}
									{assign var=PICKLIST_LABEL value=$PICKLIST_ITEM}
									{assign var=PICKLIST_ICON value=false}
									{assign var=PICKLIST_CLASS value=false}
								{/if}
								<label for="{$UID}{$PICKLIST_KEY}" class="{$PICKLIST_CLASS}">
								{if $PICKLIST_ICON}<span class="{$PICKLIST_ICON}"></span>
								{else}
								    &nbsp;{$PICKLIST_LABEL}
								{/if}</label>
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
