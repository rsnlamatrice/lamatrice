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
	<span class="span10 margin0px">
		<span class="row-fluid">
			<span class="recordLabel font-x-x-large textOverflowEllipsis span pushDown" title="{$RECORD->getName()}">
				{foreach item=NAME_FIELD from=$MODULE_MODEL->getNameFields()}{*$NAME_FIELD*}
					{assign var=FIELD_MODEL value=$MODULE_MODEL->getField($NAME_FIELD)}
					{if $FIELD_MODEL->getPermissions()}
						<span class="{$NAME_FIELD}">
								
						{if ($FIELD_MODEL->get('uitype') eq '72' || $FIELD_MODEL->get('uitype') eq '71')}{*Amount, add currency symbol*}
							{$RECORD->getDisplayValue($NAME_FIELD)}&nbsp;
							{if isset($CURRENT_USER_MODEL)}{$CURRENT_USER_MODEL->get('currency_symbol')}{else}&euro;{/if}
						{elseif ($FIELD_MODEL->get('uitype') eq '6' || $FIELD_MODEL->get('uitype') eq '5')}{*Date*}
							{$RECORD->getDisplayValue($NAME_FIELD)}
						{else}
							{$RECORD->get($NAME_FIELD)}
						{/if}
						</span>&nbsp;
					{/if}
				{/foreach}
				{if $RECORD->get('sent2compta')}
					<br><span style="color: red;">{vtranslate('LBL_ALREADY_SENT_2_COMPTA')}</span>
				{/if}
				
				{assign var=CODE_AFFAIRE value=""}
				{if $RECORD->getCampaign()}
					{assign var=CODE_AFFAIRE value=$RECORD->getCampaign()->get('codeaffaire')}
					{if $CODE_AFFAIRE || ! $RECORD->getCoupon()}
						{assign var=NAME_FIELD value='campaign_no'}
						{assign var=RELATED_NAME value=$RECORD->getDisplayValue($NAME_FIELD)}
					{/if}
				{/if}
				{if !$CODE_AFFAIRE && $RECORD->getCoupon()}
					{assign var=CODE_AFFAIRE value=$RECORD->getCoupon()->get('codeaffaire')}
					{assign var=NAME_FIELD value='notesid'}
					{assign var=RELATED_NAME value=$RECORD->getDisplayValue($NAME_FIELD)}
				{/if}
				{if $CODE_AFFAIRE}
					<br><span title="Code affaire">{$CODE_AFFAIRE}</span>&nbsp;-&nbsp;{$RELATED_NAME}
				{elseif $RELATED_NAME}
					<br>{$RELATED_NAME}
				{/if}
			</span>
		</span>
	</span>
{/strip}