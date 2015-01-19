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
	<div class="contactsContainer relatedRow">
		<ul class="unstyled">
			<li title="{$RELATED_RECORD->get('label')}" data-id="{$RELATED_RECORD->getId()}">
				<div class="row-fluid ">
					<div class="fieldLabel span4 textOverflowEllipsis">
						<div class="actions pull-right">
							<a href="{$RELATED_RECORD->getDetailViewUrl()}" class="hide" title="Cliquez pour modifier la variable."><i class="icon-pencil"></i></a>
							<a class="relationDelete {if !$RELATED_RECORD->get('disabled')}hide{/if}" title="Variable inutilisée. Cliquez pour la supprimer."><i title="Supprimer" class="icon-trash alignMiddle"></i></a>
						</div>
						<a href="{$RELATED_RECORD->getDetailViewUrl()}" id="{$MODULE}_{$RELATED_MODULE}_Related_Record_{$RELATED_RECORD->get('id')}" title="{$RELATED_RECORD->getDisplayValue('lastname')}"
						{if $RELATED_RECORD->get('disabled')}
							class="line-through"
						{/if}>
							{$RELATED_RECORD->get('name')}
						</a>
					</div>
					<div class="fieldValue span8 textOverflowEllipsis">
						{*les combos chosen ne s'affichent pas correctement dans les widgets : modif dans le .css non satisfaisante *}
						{assign var=FIELD_MODEL value=$RELATED_RECORD->getQueryField()}
						{if is_object($FIELD_MODEL)}
							{assign var=UITYPEMODEL value=$FIELD_MODEL->getUITypeModel()->getTemplateName()}
							{include file=vtemplate_path($UITYPEMODEL,$MODULE) RECORD_MODEL=$RELATED_RECORD}
						{else}
							<input value="{$RELATED_RECORD->get('defaultvalue')}"/>
						{/if}
					</div>
				</div>
			</li>
		</ul>
	</div>
{/foreach}
{/strip}
