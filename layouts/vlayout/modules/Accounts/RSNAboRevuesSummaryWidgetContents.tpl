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
		<div class="relatedContainer">
			<ul class="unstyled">
				<li>
					<div class="row-fluid">
						<div class="span4 textOverflowEllipsis">
							<a href="{$RELATED_RECORD->getDetailViewUrl()}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}" title="{$RELATED_RECORD->getName()}">
								{*<div style="background-color:{$RELATED_RECORD->get('uicolor')}; margin-left:0;" class="picklistvalue-uicolor">&nbsp;</div>*}
								{$RELATED_RECORD->getDisplayValue('rsnabotype')}
								{if $RELATED_RECORD->get('rsnabotype') == 'D&eacute;p&ocirc;t'}
								    &nbsp;({$RELATED_RECORD->getDisplayValue('nbexemplaires')} ex.)
								{/if}
							</a>
						</div>
						<div class="span2 textOverflowEllipsis">
							<a href="{$RELATED_RECORD->getDetailViewUrl()}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}" title="{$RELATED_RECORD->getName()}">
							    <div style="background-color:{if $RELATED_RECORD->get('isabonne')}green{else}red{/if}; margin-left:0;" class="picklistvalue-uicolor">&nbsp;</div>
							    {$RELATED_RECORD->getDisplayValue('debutabo')}
							</a>
						</div>
						<div class="span2 textOverflowEllipsis">
							<a href="{$RELATED_RECORD->getDetailViewUrl()}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}" title="{$RELATED_RECORD->getName()}">
							    {$RELATED_RECORD->getDisplayValue('finabo')}&nbsp;
							</a>
						</div>
						<div class="span2 textOverflowEllipsis">
							<a href="{$RELATED_RECORD->getDetailViewUrl()}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}" title="{$RELATED_RECORD->getName()}">
							    	{if ($RELATED_RECORD->get('rsnabotype') == 'D&eacute;p&ocirc;t') || ($RELATED_RECORD->getDisplayValue('nbexemplaires') > 1)}
								    {$RELATED_RECORD->getDisplayValue('nbexemplaires')} ex.
								{/if}
							</a>
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
