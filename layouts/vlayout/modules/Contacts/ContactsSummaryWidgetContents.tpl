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
						<div class="span6 textOverflowEllipsis">
							<a href="{$RELATED_RECORD->getDetailViewUrl()}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}" title="{$RELATED_RECORD->getDisplayValue('lastname')}">
								{if $RELATED_RECORD->get('isgroup') eq '0'}
								    <span class="icon-rsn-small-contact"></span>
								{else}<span class="icon-rsn-small-collectif"></span>
								{/if}
								&nbsp;{$RELATED_RECORD->getName()}
								{if $RELATED_RECORD->get('isgroup') neq '0' && $RELATED_RECORD->get('mailingstreet2')}
								    &nbsp;-&nbsp;{$RELATED_RECORD->get('mailingstreet2')}
								{/if}
							</a>
						</div>
						<div class="span4 textOverflowEllipsis">
							<span>
								{assign var=RELTYPES value=$RELATED_RECORD->get('contreltype')}
								{if is_array($RELTYPES)}{$RELTYPES[0]}{/if}
							</span>
						</div>
						
					</div>
				</li>
			</ul>
		</div>
	{/foreach}
	{assign var=NUMBER_OF_RECORDS value=count($RELATED_RECORDS)}
	{if $NUMBER_OF_RECORDS eq 5}
		<div class="row-fluid">
			<div class="pull-right">
				<a class="moreRecentContacts cursorPointer">{vtranslate('LBL_MORE',$MODULE_NAME)}</a>
			</div>
		</div>
	{/if}
{/strip}
