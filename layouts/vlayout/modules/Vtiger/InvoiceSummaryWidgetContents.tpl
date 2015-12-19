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
	{foreach item=RELATED_RECORD from=$RELATED_RECORDS name=recordList}
		{if $smarty.foreach.recordList.index === 0}	
			{assign var=ALL_STATUS value=$RELATED_RECORD->getPicklistValuesDetails('invoicestatus')}
		{/if}
		<div class="recentInvoicesContainer row-fluid">
			<ul class="unstyled">
				<li class="row-fluid">
					<div class="row-fluid">
						<span class="textOverflowEllipsis span6">
							{assign var=STATUS value=$ALL_STATUS[$RELATED_RECORD->get('invoicestatus')]}
							{assign var=SUBJECT1 value=$RELATED_RECORD->getDisplayValue('typedossier')}
							{assign var=SUBJECT2 value=$RELATED_RECORD->getDisplayValue('subject')}
							{if !$SUBJECT2}
								{assign var=SUBJECT2 value=$SUBJECT1}
							{elseif $SUBJECT1 && ($SUBJECT1 neq $SUBJECT2)}
								{assign var=SUBJECT2 value=$SUBJECT1|cat:' - '|cat:$SUBJECT2}	
							{/if}
							<a href="{$RELATED_RECORD->getDetailViewUrl()}" title="{$SUBJECT2}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}">
								{if $STATUS && $STATUS['icon']}
									<span class="{$STATUS['icon']}"></span>
								{/if}
								{$SUBJECT2}
							</a>
						</span>
						<span class="textOverflowEllipsis span2">
							<a href="{$RELATED_RECORD->getDetailViewUrl()}" title="{$RELATED_RECORD->getDisplayValue('subject')}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}">
								{$RELATED_RECORD->getDisplayValue('invoicedate')}
							</a>
						</span>
						<span class="textOverflowEllipsis span3">
							<a href="{$RELATED_RECORD->getDetailViewUrl()}"
							   class="pull-right"
							   title="{$RELATED_RECORD->getDisplayValue('subject')}"
							   id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}">
								{$RELATED_RECORD->getDisplayValue('hdnGrandTotal')}&nbsp;&euro;
								{if (float)$RELATED_RECORD->get('balance')}
									&nbsp;/ {$RELATED_RECORD->getDisplayValue('balance')}&nbsp;&euro;
								{/if}
							</a>
						</span>
					</div>
				</li>
			</ul>
		</div>
	{/foreach}
	{assign var=NUMBER_OF_RECORDS value=count($RELATED_RECORDS)}
	{if $NUMBER_OF_RECORDS eq 15}
		<div class="row-fluid">
			<div class="pull-right">
				<a class="moreRecentInvoices cursorPointer">{vtranslate('LBL_MORE',$MODULE_NAME)}</a>
			</div>
		</div>
	{/if}
{/strip}