{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
affichage du détail d'un contact


A noter que les champs d'adresses sont triés dans modules\Contacts\models\DetailRecordStructure.php
 ********************************************************************************/
 
 ED141010 : add RECORD_MODEL=$RECORD
-->*}
{strip}
	{foreach key=BLOCK_LABEL_KEY item=FIELD_MODEL_LIST from=$RECORD_STRUCTURE}
	{assign var=BLOCK value=$BLOCK_LIST[$BLOCK_LABEL_KEY]}
	{if $BLOCK eq null or $FIELD_MODEL_LIST|@count lte 0}{continue}{/if}
	{assign var=IS_HIDDEN value=$BLOCK->isHidden()}
	{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
	{assign var=BLOCK_DO_NOT value=$BLOCK_LABEL_KEY == 'LBL_BLOCK_DO_NOT'}{* ED140926 force checkbox instead of yes/no text *}
	
	<input type=hidden name="timeFormatOptions" data-value='{$DAY_STARTS}' />
	{* ED150515 : account_id needed for 'reference' changing confirmation *}
	<input type=hidden name="account_id" data-value='{$RECORD->get('account_id')}' />
	<table class="table table-bordered equalSplit detailview-table">
		<thead>
		<tr>
			<th class="blockHeader" colspan="4">
				<img class="cursorPointer alignMiddle blockToggle {if !($IS_HIDDEN)} hide {/if} "  src="{vimage_path('arrowRight.png')}" data-mode="hide" data-id={$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}>
				<img class="cursorPointer alignMiddle blockToggle {if ($IS_HIDDEN)} hide {/if}"  src="{vimage_path('arrowDown.png')}" data-mode="show" data-id={$BLOCK_LIST[$BLOCK_LABEL_KEY]->get('id')}>
				&nbsp;&nbsp;{vtranslate({$BLOCK_LABEL_KEY},{$MODULE_NAME})}
				{if $BLOCK_DO_NOT}
					<div class="rsn_block_values_summary">{* see \layouts\vlayout\modules\RSN\resources\css\style.css *}
						{assign var=COUNTER value=0}
						{foreach item=FIELD_MODEL key=FIELD_NAME from=$FIELD_MODEL_LIST}
							{if !$FIELD_MODEL->isViewableInDetailView() || $FIELD_MODEL->get('uitype') neq "56" || $FIELD_MODEL->get('fieldvalue') eq false}
								{continue}
							{/if}
							{if $COUNTER > 0}, {/if}
							{vtranslate({$FIELD_MODEL->get('label')},{$MODULE_NAME})}
							{assign var=COUNTER value=$COUNTER + 1}
						{/foreach}
					</div>
				
				{* ED150810 *}
				{elseif $BLOCK_LABEL_KEY eq 'LBL_ADDRESS_INFORMATION'}
					{if $RECORD->get('mailingmodifiedtime')}
						<label style="margin-left: 2em; display: inline-block; color: white; opacity: 0.8;">
							{$RECORD->getDisplayValue('mailingmodifiedtime')}
						</label>
					{/if}
					{if $RECORD->initRNVPLabel()}
						<label style="margin-left: 1em; display: inline-block; color: white; opacity: 0.8;">
							RNVP : {$RECORD->get('mailingRNVPLabel')}
						</label>
					{/if}
					{if $RECORD->get('use_address2_for_revue') || $RECORD->get('use_address2_for_recu_fiscal')}
						<label style="margin-left: 3em; display: inline-block; color: white;">
							cf adresse secondaire 
							{assign var=FIELD_NAME value='use_address2_for_revue'}
							{if $RECORD->get($FIELD_NAME)}
								,&nbsp;{vtranslate($FIELD_NAME, $MODULE_NAME)}
							{/if}
							{assign var=FIELD_NAME value='use_address2_for_recu_fiscal'}
							{if $RECORD->get($FIELD_NAME)}
								,&nbsp;{vtranslate($FIELD_NAME, $MODULE_NAME)}
							{/if}
						</label>
					{/if}
				
				{* ED150810 *}
				{elseif $BLOCK_LABEL_KEY eq 'Adresse secondaire'}
					{if $RECORD->get('othermodifiedtime')}
						<label style="margin-left: 2em; display: inline-block; color: white;">
							{$RECORD->getDisplayValue('othermodifiedtime')}
						</label>
					{/if}
					{assign var=FIELD_NAME value='use_address2_for_revue'}
					{if $RECORD->get($FIELD_NAME)}
						<label style="margin-left: 4em; display: inline-block; color: white;">
						&nbsp;{vtranslate($FIELD_NAME, $MODULE_NAME)}
						</label>
					{/if}					
					{assign var=FIELD_NAME value='use_address2_for_recu_fiscal'}
					{if $RECORD->get($FIELD_NAME)}
						<label style="margin-left: 2em; display: inline-block; color: white;">
						&nbsp;{vtranslate($FIELD_NAME, $MODULE_NAME)}
						</label>
					{/if}
					
				{/if}
			</th>
		</tr>
		</thead>
		 <tbody {if $IS_HIDDEN} class="hide" {/if}>
		{assign var=COUNTER value=0}
		{assign var=BLOC_HAS_DESCRIPTION_ROW value=false}
		<tr>
		{foreach item=FIELD_MODEL key=FIELD_NAME from=$FIELD_MODEL_LIST}
			{if !$FIELD_MODEL->isViewableInDetailView()}
				 {continue}
			 {/if}
			{if $FIELD_NAME eq 'contact_no'
			
			|| $FIELD_NAME eq 'mailingrnvpeval'
			|| $FIELD_NAME eq 'mailingrnvpcharade'
			|| $FIELD_NAME eq 'otherrnvpeval'
			|| $FIELD_NAME eq 'otherrnvpcharade'}
				 {continue}
			 {/if}
			{if $FIELD_MODEL->get('uitype') eq "69" || $FIELD_MODEL->get('uitype') eq "105"}
				{if $COUNTER neq 0}
					{if $COUNTER eq 2}
						</tr><tr>
						{assign var=COUNTER value=0}
					{/if}
				{/if}
				<td class="fieldLabel {$WIDTHTYPE}"><label class="muted pull-right marginRight10px">{vtranslate({$FIELD_MODEL->get('label')},{$MODULE_NAME})}</label></td>
				<td class="fieldValue">
					<div id="imageContainer" width="300" height="200">
						{foreach key=ITER item=IMAGE_INFO from=$IMAGE_DETAILS}
							{if !empty($IMAGE_INFO.path) && !empty({$IMAGE_INFO.orgname})}
								<img src="{$IMAGE_INFO.path}_{$IMAGE_INFO.orgname}" width="300" height="200">
							{/if}
						{/foreach}
					</div>
				</td>
				{assign var=COUNTER value=$COUNTER+1}
			{else}
				{if $FIELD_MODEL->get('uitype') eq "20" or (($FIELD_MODEL->get('uitype') eq "19") && ($FIELD_NAME neq "rsnnpaicomment"))}{* ED150122 : commentaire sur NPAI affichŽ plus petit *}
					{assign var=BLOC_HAS_DESCRIPTION_ROW value=true}
					{if $COUNTER eq '1'}
						<td class="{$WIDTHTYPE}"></td><td class="{$WIDTHTYPE}"></td></tr><tr>
						{assign var=COUNTER value=0}
					{/if}
				{/if}
				 {if $COUNTER eq 2}
					 </tr><tr>
					{assign var=COUNTER value=1}
				{else}
					{assign var=COUNTER value=$COUNTER+1}
				 {/if}
				 <td class="fieldLabel {$WIDTHTYPE}" id="{$MODULE}_detailView_fieldLabel_{$FIELD_MODEL->getName()}">
					<label class="muted pull-right marginRight10px">
						 {vtranslate({$FIELD_MODEL->get('label')},{$MODULE_NAME})}
						 {if ($FIELD_MODEL->get('uitype') eq '72') && ($FIELD_MODEL->getName() eq 'unit_price')}
							({$BASE_CURRENCY_SYMBOL})
						{/if}
					 </label>
				 </td>
				 <td class="fieldValue {$WIDTHTYPE}" id="{$MODULE}_detailView_fieldValue_{$FIELD_MODEL->getName()}"
						{if ($FIELD_MODEL->get('uitype') eq "19") && ($FIELD_NAME neq "rsnnpaicomment") or $FIELD_MODEL->get('uitype') eq '20'} colspan="3" {assign var=COUNTER value=$COUNTER+1} {/if}>
					 <span class="value" data-field-type="{$FIELD_MODEL->getFieldDataType()}">
						{if $BLOCK_DO_NOT && $FIELD_MODEL->get('uitype') == '56' }{* ED141005 *}
						{include file=vtemplate_path('uitypes/Boolean.tpl',$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
						{elseif $FIELD_MODEL->get('uitype') eq '15'}{* ED141005 *}
							{$RECORD->getDisplayValue($FIELD_NAME)}
						{* ED150105 "referent du compte" masque si pas de compte *}
						{elseif ($MODULE_NAME eq 'Contacts') && ($FIELD_NAME == 'reference') && (!$RECORD->get('account_id'))}
							<i>pas de compte</i>
						{else}
							{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
							
							{if $FIELD_NAME eq 'lastname' && $RECORD->get('isgroup') neq '0'}
							       <span class="mailingstreet2-synchronized" style="margin-left: 1em;">{htmlentities($RECORD->get('mailingstreet2'))}</span>
						       {/if}
						{/if}
					 </span>
					 {if $IS_AJAX_ENABLED && $FIELD_MODEL->isEditable() eq 'true' && ($FIELD_MODEL->getFieldDataType()!=Vtiger_Field_Model::REFERENCE_TYPE) && $FIELD_MODEL->isAjaxEditable() eq 'true'}
						 <span class="hide edit">
						{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD_MODEL=$RECORD}
						{if $FIELD_MODEL->getFieldDataType() eq 'multipicklist'}
						   <input type="hidden" class="fieldname" value='{$FIELD_MODEL->get('name')}[]' data-prev-value='{$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'))}' />
						{else}
						    <input type="hidden" class="fieldname" value='{$FIELD_MODEL->get('name')}' data-prev-value='{Vtiger_Util_Helper::toSafeHTML($FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue')))}' />
						{/if}
						 </span>
					 {/if}
					{*rsnnpaicomment*}
					{*if $FIELD_NAME eq 'rsnnXXXpai'}
					<div class="pull-right">
						{assign var=FIELD_NAMETMP value=$FIELD_NAME}
						{assign var=FIELD_MODELTMP value=$FIELD_MODEL}
						{assign var=FIELD_NAME value=$FIELD_NAME|cat:'comment'}
						{assign var=FIELD_MODEL value=$FIELD_MODEL_LIST[$FIELD_NAME]}
						<span class="value" title="Commentaires sur le NPAI" data-field-type="{$FIELD_MODEL->getFieldDataType()}">
							{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
						</span>
						{if $IS_AJAX_ENABLED && $FIELD_MODEL->isEditable() eq 'true'}
						<span class="hide edit">
							{assign var=UITYPEMODEL value=$FIELD_MODEL->getUITypeModel()->getTemplateName()}
							{include file=vtemplate_path($UITYPEMODEL,$MODULE) BLOCK_FIELDS=$FIELD_MODEL_LIST  FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
							{assign var=FIELD_NAME value=$FIELD_NAMETMP}
							{assign var=FIELD_MODEL value=$FIELD_MODELTMP}
							{assign var=COUNTER value=1}
						</span>
						{/if}
					</div>
					{/if*}
				 </td>
			 {/if}

		{if $FIELD_MODEL_LIST|@count eq 1 and (($FIELD_MODEL->get('uitype') neq "19") || ($FIELD_NAME eq "rsnnpaicomment")) and $FIELD_MODEL->get('uitype') neq "20" and $FIELD_MODEL->get('uitype') neq "30" and $FIELD_MODEL->get('name') neq "recurringtype" and $FIELD_MODEL->get('uitype') neq "69" and $FIELD_MODEL->get('uitype') neq "105"}
			<td class="{$WIDTHTYPE}"></td><td class="{$WIDTHTYPE}"></td>
		{elseif $BLOC_HAS_DESCRIPTION_ROW && count($FIELD_MODEL_LIST) == 1}
			{* ligne vide avec 4 cellules pour que le commentaire s'aligne à gauche
			 * mais c'est moche, une ligne vide. TODO.
			 *}
			<tr><td></td><td></td><td></td><td></td></tr>
		{/if}
		{/foreach}
		</tr>
		</tbody>
	</table>
	<br>
	{/foreach}
{/strip}