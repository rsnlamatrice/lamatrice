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
<div class='container-fluid editViewContainer'>
	<form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php" enctype="multipart/form-data">
		{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
		{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
			<input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
		{/if}
		{assign var=QUALIFIED_MODULE_NAME value={$MODULE}}
		{assign var=IS_PARENT_EXISTS value=strpos($MODULE,":")}
		{if $IS_PARENT_EXISTS}
			{assign var=SPLITTED_MODULE value=":"|explode:$MODULE}
			<input type="hidden" name="module" value="{$SPLITTED_MODULE[1]}" />
			<input type="hidden" name="parent" value="{$SPLITTED_MODULE[0]}" />
		{else}
			<input type="hidden" name="module" value="{$MODULE}" />
		{/if}
		<input type="hidden" name="action" value="Save" />
		<input type="hidden" name="record" value="{$RECORD_ID}" />
		<input type="hidden" name="defaultCallDuration" value="{$USER_MODEL->get('callduration')}" />
		<input type="hidden" name="defaultOtherEventDuration" value="{$USER_MODEL->get('othereventduration')}" />
		{if $IS_RELATION_OPERATION }
			<input type="hidden" name="sourceModule" value="{$SOURCE_MODULE}" />
			<input type="hidden" name="sourceRecord" value="{$SOURCE_RECORD}" />
			<input type="hidden" name="relationOperation" value="{$IS_RELATION_OPERATION}" />
		{/if}
		<div class="contentHeader row-fluid">
		{assign var=SINGLE_MODULE_NAME value='SINGLE_'|cat:$MODULE}
		{if $RECORD_ID neq ''}
			<h3 class="span8 textOverflowEllipsis" title="{vtranslate('LBL_EDITING', $MODULE)} {vtranslate($SINGLE_MODULE_NAME, $MODULE)} {$RECORD_STRUCTURE_MODEL->getRecordName()}">
				{vtranslate('LBL_EDITING', $MODULE)} {vtranslate($SINGLE_MODULE_NAME, $MODULE)} - {$RECORD_STRUCTURE_MODEL->getRecordName()} - {$RECORD_MODEL->get('contact_no')}
			
				{if $RECORD_MODEL->get('isgroup') neq '0' && $RECORD_MODEL->get('mailingstreet2')}
				       <span class="mailingstreet2-synchronized" style="margin-left: 1em;">{htmlentities($RECORD_MODEL->get('mailingstreet2'))}</span>
			       {/if}
			</h3>
		{else}
			<h3 class="span8 textOverflowEllipsis">{vtranslate('LBL_CREATING_NEW', $MODULE)} {vtranslate($SINGLE_MODULE_NAME, $MODULE)}</h3>
		{/if}
			<span class="pull-right">
				<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
				<a class="cancelLink" type="reset" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL', $MODULE)}</a>
			</span>
		</div>
		{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name="EditViewBlockLevelLoop"}
			{if $BLOCK_FIELDS|@count lte 0}{continue}{/if}
			{assign var=IS_HIDDEN value=(($BLOCK_LABEL eq 'Adresse secondaire' && !$RECORD_MODEL->get('use_address2_for_revue') && !$RECORD_MODEL->get('use_address2_for_recu_fiscal'))
			 || $BLOCK_LABEL eq 'LBL_CUSTOM_INFORMATION' || $BLOCK_LABEL eq 'LBL_IMAGE_INFORMATION'
			 || ($BLOCK_LABEL eq 'Groupe, Structure' && $RECORD_MODEL->get('isgroup') eq '0'))}
			
			<table class="table table-bordered blockContainer showInlineTable {if $BLOCK_LABEL eq "LBL_ADDRESS_INFORMATION"}current-address{/if}">
			<thead><tr>
				<th class="blockHeader" colspan="4">
					{if $IS_HIDDEN}
						<img class="cursorPointer alignMiddle blockToggle {if !($IS_HIDDEN)} hide {/if} "  src="{vimage_path('arrowRight.png')}" data-mode="hide"}>
						<img class="cursorPointer alignMiddle blockToggle {if ($IS_HIDDEN)} hide {/if}"  src="{vimage_path('arrowDown.png')}" data-mode="show"}>
						&nbsp;&nbsp;
					{/if}
					{vtranslate($BLOCK_LABEL, $MODULE)}
					{if $BLOCK_LABEL eq 'Adresse secondaire'}
						{if $RECORD_MODEL->get('othermodifiedtime')}
							<div style="display: inline-block; margin-left: 2em; opacity: 0.8;">
								{$RECORD_MODEL->getDisplayValue('othermodifiedtime')}
							</div>
						{/if}
						<a id="adresse_secondaire"/>
						<label class="blockToggler" style="margin-left: 3em; display: inline-block; color: white;">
						{assign var=FIELD_NAME value='use_address2_for_revue'}
						{assign var=FIELD_MODEL value=$BLOCK_FIELDS[$FIELD_NAME]}
						{assign var=UITYPEMODEL value=$FIELD_MODEL->getUITypeModel()->getTemplateName()}
						{include file=vtemplate_path($UITYPEMODEL,$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS RECORD_MODEL=$RECORD_MODEL}
						&nbsp;{vtranslate($FIELD_NAME, $MODULE)}
						</label>
						
						<label class="blockToggler" style="margin-left: 2em; display: inline-block; color: white;">
						{assign var=FIELD_NAME value='use_address2_for_recu_fiscal'}
						{assign var=FIELD_MODEL value=$BLOCK_FIELDS[$FIELD_NAME]}
						{assign var=UITYPEMODEL value=$FIELD_MODEL->getUITypeModel()->getTemplateName()}
						{include file=vtemplate_path($UITYPEMODEL,$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS RECORD_MODEL=$RECORD_MODEL}
						&nbsp;{vtranslate($FIELD_NAME, $MODULE)}
						</label>
					{elseif $BLOCK_LABEL eq 'LBL_ADDRESS_INFORMATION'}
						{if $RECORD_MODEL->get('mailingmodifiedtime')}
							<div style="display: inline-block; margin-left: 2em; opacity: 0.8;">
								{$RECORD_MODEL->getDisplayValue('mailingmodifiedtime')}
							</div>
						{/if}
						{if $RECORD_MODEL->initRNVPLabel()}
							<div style="display: inline-block; margin-left: 2em; opacity: 0.8;">
								RNVP : {$RECORD_MODEL->get('mailingRNVPLabel')}
							</div>
						{/if}
						{if $RECORD_MODEL->get('use_address2_for_revue') || $RECORD_MODEL->get('use_address2_for_recu_fiscal')}
							{* ED150912 notification de l'existence d'une adresse secondaire spécifique *}
							<div style="display: inline-block; margin-left: 4em; opacity: 0.6;">
								<a href="#adresse_secondaire">{vtranslate('LBL_ADDRESS2_EXISTS', $MODULE)} : 
									{if $RECORD_MODEL->get('use_address2_for_revue')}&nbsp;{vtranslate('USE ADDRESS2 FOR REVUE', $MODULE)}{/if}
									{if $RECORD_MODEL->get('use_address2_for_recu_fiscal')}&nbsp;{vtranslate('USE ADDRESS2 FOR RECU FISCAL', $MODULE)}{/if}
								</a>
							</div>
						{/if}
					{elseif $BLOCK_LABEL eq 'LBL_BLOCK_DO_NOT'}
						{* ED150912 sélection de tous ou aucun *}
						{assign var=UID value='change-all-donot'}
						<div id="{$UID}" class="buttonset ui-buttonset" style="display: inline-block; margin-left: 5em; opacity: 0.6;">
							<input type="radio" name="LBL_BLOCK_DO_NOT" id="{$UID}-0" value="0"/>
								<label for="{$UID}-0" class="ui-buttonset">
									<span class="ui-icon ui-icon-unlocked darkgreen"></span>&nbsp;</label>
							<input type="radio" name="LBL_BLOCK_DO_NOT" id="{$UID}-1" value="1"/>
								<label for="{$UID}-1" class="ui-buttonset">
									<span class="ui-icon ui-icon-locked darkred"></span>&nbsp;</label>
						</div>
					{/if}
			</th>
			</tr></thead>
			<tbody {if $IS_HIDDEN} class="hide" {/if}>
			<tr>
			{assign var=COUNTER value=0}
			{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
			
				{if $FIELD_NAME eq 'otherstreet2'
				|| $FIELD_NAME eq 'mailingstreet2'
				|| $FIELD_NAME eq 'otherstreet3'
				|| $FIELD_NAME eq 'mailingstreet3'
				|| $FIELD_NAME eq 'otheraddressformat'
				|| $FIELD_NAME eq 'mailingaddressformat'
				|| $FIELD_NAME eq 'mailingzip'
				|| $FIELD_NAME eq 'mailingmodifiedtime'
				|| $FIELD_NAME eq 'othermodifiedtime'
				|| $FIELD_NAME eq 'otherzip'
				|| $FIELD_NAME eq 'mailingpobox'
				|| $FIELD_NAME eq 'otherpobox'
				|| $FIELD_NAME eq 'mailingcountry'
				|| $FIELD_NAME eq 'othercountry'
				|| $FIELD_NAME eq 'rsnnpaicomment'
				|| $FIELD_NAME eq 'rsnnpaidate'
				|| $FIELD_NAME eq 'use_address2_for_revue'
				|| $FIELD_NAME eq 'use_address2_for_recu_fiscal'}
					{continue}
				{/if}
				
				{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
				{if $FIELD_MODEL->get('uitype') eq "20" || $FIELD_MODEL->get('uitype') eq "19" || $FIELD_NAME eq 'isgroup'}
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
				{if ($COUNTER eq '1')
				&& ($FIELD_NAME eq 'otherstate'
				|| $FIELD_NAME eq 'mailingstate')}
					<td class="{$WIDTHTYPE}"></td><td class="{$WIDTHTYPE}"></td>
					{assign var=COUNTER value=$COUNTER+1}
				{/if}
				<td class="fieldLabel {$WIDTHTYPE}">
					{if $isReferenceField neq "reference"}<label class="muted pull-right marginRight10px">{/if}
					{if $FIELD_MODEL->isMandatory() eq true && $isReferenceField neq "reference"} <span class="redColor">*</span> {/if}
					{if $isReferenceField eq "reference"}
						{assign var="REFERENCE_LIST" value=$FIELD_MODEL->getReferenceList()}
						{assign var="REFERENCE_LIST_COUNT" value=count($REFERENCE_LIST)}
						{if $REFERENCE_LIST_COUNT > 1}
							{assign var="DISPLAYID" value=$FIELD_MODEL->get('fieldvalue')}
							{assign var="REFERENCED_MODULE_STRUCT" value=$FIELD_MODEL->getUITypeModel()->getReferenceModule($DISPLAYID)}
							{if !empty($REFERENCED_MODULE_STRUCT)}
								{assign var="REFERENCED_MODULE_NAME" value=$REFERENCED_MODULE_STRUCT->get('name')}
							{/if}
							<span class="pull-right">
								{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}
								<select id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->getName()}_dropDown" class="chzn-select referenceModulesList streched" style="width:140px;">
									<optgroup>
										{foreach key=index item=value from=$REFERENCE_LIST}
											<option value="{$value}" {if $value eq $REFERENCED_MODULE_NAME} selected {/if}>{vtranslate($value, $MODULE)}</option>
										{/foreach}
									</optgroup>
								</select>
							</span>
						{else}
							<label class="muted pull-right marginRight10px">{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}{vtranslate($FIELD_MODEL->get('label'), $MODULE)}</label>
						{/if}
					{else if $FIELD_MODEL->get('uitype') eq "83"}
						{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) COUNTER=$COUNTER MODULE=$MODULE}
					{else}
						{if $FIELD_NAME eq 'othercity'
						|| $FIELD_NAME eq 'mailingcity'}
							<span class="subFieldLabel">BP, lieu-dit</span>
							<span class="subFieldLabel">CP, Ville</span>
							<span class="subFieldLabel">Pays</span>
						{elseif $FIELD_NAME eq 'otherstreet'
						|| $FIELD_NAME eq 'mailingstreet'}
							<span class="subFieldLabel ui-buttonset-small">
								{* addressformat *}
								{assign var=TITLE value='Format de l\'adresse'}
								{assign var=FIELD_NAMETMP value=$FIELD_NAME}
								{assign var=FIELD_MODELTMP value=$FIELD_MODEL}
								{assign var=FIELD_NAME value=str_replace('street','addressformat', $FIELD_NAME)}
								{assign var=FIELD_MODEL value=$BLOCK_FIELDS[$FIELD_NAME]}
								{assign var=UITYPEMODEL value=$FIELD_MODEL->getUITypeModel()->getTemplateName()}
								{include file=vtemplate_path($UITYPEMODEL,$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS RECORD_MODEL=$RECORD_MODEL}
								{assign var=FIELD_NAME value=$FIELD_NAMETMP}
								{assign var=FIELD_MODEL value=$FIELD_MODELTMP}	
							</span>
							<span class="subFieldLabel">{vtranslate($FIELD_MODEL->get('label'), $MODULE)}</span>
							<span class="subFieldLabel" style="color: #88bbbb;">
								<button class="address-pagesblanches ui-button" title="Pages blanches">Pages bl.</button>
								<button class="address-sna-check ui-button" title="Contrôle par le Service National des Adresses">SNA</button>
								&gt;&gt;
							</span>
						{elseif $FIELD_NAME eq 'rsnnpai'}
							{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
							{if $RECORD_MODEL->get('rsnnpaidate')} au {$RECORD_MODEL->getDisplayValue('rsnnpaidate')}{/if}
						{else}
							{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
						{/if}
					{/if}
					{if $isReferenceField neq "reference"}</label>{/if}
				</td>
				{if $FIELD_MODEL->get('uitype') neq "83"}
					<td class="fieldValue {$WIDTHTYPE}" {if $FIELD_MODEL->get('uitype') eq '19' or $FIELD_MODEL->get('uitype') eq '20' or $FIELD_NAME eq 'rsnnpai' or $FIELD_NAME eq 'isgroup'} colspan="3" {assign var=COUNTER value=$COUNTER+1} {/if}>
						<div class="row-fluid">
							<span class="{if $FIELD_NAME eq 'rsnnpai'}span6{else}span10{/if} {* ED141005 *}
								{if strpos($FIELD_NAME, 'street') !== false} field-street{/if}{* ED151222 *}">
								{* street2/3*}
								{if $FIELD_NAME eq 'otherstreet'
								|| $FIELD_NAME eq 'mailingstreet'}
									{* street2 *}
									{assign var=TITLE value='Structure ou chez, n°appt, n°BAL, escalier, couloir, ...'}
									{assign var=FIELD_NAMETMP value=$FIELD_NAME}
									{assign var=FIELD_MODELTMP value=$FIELD_MODEL}
									{assign var=FIELD_NAME value=$FIELD_NAME|cat:'2'}
									{assign var=FIELD_MODEL value=$BLOCK_FIELDS[$FIELD_NAME]}
									{assign var=UITYPEMODEL value=$FIELD_MODEL->getUITypeModel()->getTemplateName()}
									{include file=vtemplate_path($UITYPEMODEL,$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS RECORD_MODEL=$RECORD_MODEL}
									{assign var=FIELD_NAME value=$FIELD_NAMETMP}
									{assign var=FIELD_MODEL value=$FIELD_MODELTMP}									
									
									<br>
									{* street3 *}
									{assign var=TITLE value='Complément d\'adresse, bât., rés., ...'}
									{assign var=FIELD_NAMETMP value=$FIELD_NAME}
									{assign var=FIELD_MODELTMP value=$FIELD_MODEL}
									{assign var=FIELD_NAME value=$FIELD_NAME|cat:'3'}
									{assign var=FIELD_MODEL value=$BLOCK_FIELDS[$FIELD_NAME]}
									{assign var=UITYPEMODEL value=$FIELD_MODEL->getUITypeModel()->getTemplateName()}
									{include file=vtemplate_path($UITYPEMODEL,$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS RECORD_MODEL=$RECORD_MODEL}
									{assign var=FIELD_NAME value=$FIELD_NAMETMP}
									{assign var=FIELD_MODEL value=$FIELD_MODELTMP}									
								
									<br>
								{/if}
								
								{* BP pobox*}
								{* zip code *}
								{if $FIELD_NAME eq 'othercity'
								|| $FIELD_NAME eq 'mailingcity'}
									{assign var=TITLE value='Boîte postale, lieu-dit, ...'}
									{assign var=FIELD_NAMETMP value=$FIELD_NAME}
									{assign var=FIELD_MODELTMP value=$FIELD_MODEL}
									{assign var=FIELD_NAME value=str_replace('city', 'pobox', $FIELD_NAME)}
									{assign var=FIELD_MODEL value=$BLOCK_FIELDS[$FIELD_NAME]}
									{assign var=UITYPEMODEL value=$FIELD_MODEL->getUITypeModel()->getTemplateName()}
									{include file=vtemplate_path($UITYPEMODEL,$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS RECORD_MODEL=$RECORD_MODEL INPUT_CLASS='input-large'}
									{assign var=FIELD_NAME value=$FIELD_NAMETMP}
									{assign var=FIELD_MODEL value=$FIELD_MODELTMP}
									<br>
									{assign var=TITLE value='Code postal. Pour les adresses étrangères, commence par le code du pays. Exple : BE-6350.'}
									{assign var=FIELD_NAMETMP value=$FIELD_NAME}
									{assign var=FIELD_MODELTMP value=$FIELD_MODEL}
									{assign var=FIELD_NAME value=str_replace('city', 'zip', $FIELD_NAME)}
									{assign var=FIELD_MODEL value=$BLOCK_FIELDS[$FIELD_NAME]}
									{assign var=UITYPEMODEL value=$FIELD_MODEL->getUITypeModel()->getTemplateName()}
									{include file=vtemplate_path($UITYPEMODEL,$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS RECORD_MODEL=$RECORD_MODEL INPUT_CLASS='input-mini'}
									{assign var=FIELD_NAME value=$FIELD_NAMETMP}
									{assign var=FIELD_MODEL value=$FIELD_MODELTMP}
								{/if}
								
								{* Main : field de la boucle *}
								{* title (tooltiptext) *}
								{if $FIELD_NAME eq 'otherstreet'
								|| $FIELD_NAME eq 'mailingstreet'}
									{assign var=TITLE value='Adresse principale, n° et rue, ...'}
								{elseif $FIELD_NAME eq 'othercity'
								|| $FIELD_NAME eq 'mailingcity'}
									{assign var=TITLE value='Ville'}
								{else}
									{assign var=TITLE value=''}
								{/if}
								{assign var=UITYPEMODEL value=$FIELD_MODEL->getUITypeModel()->getTemplateName()}
								{include file=vtemplate_path($UITYPEMODEL,$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS RECORD_MODEL=$RECORD_MODEL TITLE=$TITLE}
								
								{if $FIELD_NAME eq 'otherstreet'
								|| $FIELD_NAME eq 'mailingstreet'}
								
								{* pays *}
								{elseif $FIELD_NAME eq 'othercity'
								|| $FIELD_NAME eq 'mailingcity'}
									<br>
									{assign var=TITLE value='Pays'}
									{assign var=FIELD_NAMETMP value=$FIELD_NAME}
									{assign var=FIELD_MODELTMP value=$FIELD_MODEL}
									{assign var=FIELD_NAME value=str_replace('city', 'country', $FIELD_NAME)}
									{assign var=FIELD_MODEL value=$BLOCK_FIELDS[$FIELD_NAME]}
									{assign var=UITYPEMODEL value=$FIELD_MODEL->getUITypeModel()->getTemplateName()}
									{include file=vtemplate_path($UITYPEMODEL,$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS RECORD_MODEL=$RECORD_MODEL}
									{assign var=FIELD_NAME value=$FIELD_NAMETMP}
									{assign var=FIELD_MODEL value=$FIELD_MODELTMP}
								
								{/if}
								
							</span>
							{*rsnnpaicomment*}
							{if $FIELD_NAME eq 'rsnnpai'}
								<span class="span5" title="Commentaires sur le NPAI">
								{assign var=FIELD_NAMETMP value=$FIELD_NAME}
								{assign var=FIELD_MODELTMP value=$FIELD_MODEL}
								{assign var=FIELD_NAME value=$FIELD_NAME|cat:'comment'}
								{assign var=FIELD_MODEL value=$BLOCK_FIELDS[$FIELD_NAME]}
								{assign var=UITYPEMODEL value=$FIELD_MODEL->getUITypeModel()->getTemplateName()}
								{include file=vtemplate_path($UITYPEMODEL,$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS RECORD_MODEL=$RECORD_MODEL}
								{assign var=FIELD_NAME value=$FIELD_NAMETMP}
								{assign var=FIELD_MODEL value=$FIELD_MODELTMP}
								</span>
							{/if}
							
							{*isgroup : add duplicate mailingstreet2 *}
							{if $FIELD_NAME eq 'isgroup'}
								<span class="mailingstreet2-synchronize-holder pull-right {if $RECORD_MODEL->get('isgroup') eq '0'}hide{/if}"
								 title="Nom apparaissant en 2ème ligne d'adresse">
									{* isgroup_mailingstreet2 *}
									{assign var=TITLE value='Structure ou chez'}
									{assign var=FIELD_NAMETMP value=$FIELD_NAME}
									{assign var=FIELD_MODELTMP value=$FIELD_MODEL}
									{assign var=FIELD_NAME value='mailingstreet2'}
									{assign var=FIELD_MODEL value=$RECORD_STRUCTURE_MODEL->getField($FIELD_NAME)}
									{assign var=UITYPEMODEL value=$FIELD_MODEL->getUITypeModel()->getTemplateName()}
									{include file=vtemplate_path($UITYPEMODEL,$MODULE) FORCE_FIELD_NAME='mailingstreet2_synchronize' BLOCK_FIELDS=$BLOCK_FIELDS RECORD_MODEL=$RECORD_MODEL INPUT_CLASS='input-large'}
									{assign var=FIELD_NAME value=$FIELD_NAMETMP}
									{assign var=FIELD_MODEL value=$FIELD_MODELTMP}
					       
									<span class="subFieldLabel ui-buttonset-small marginRight10px pull-right">
										{* addressformat *}
										{assign var=TITLE value='Format de l\'adresse'}
										{assign var=FIELD_NAMETMP value=$FIELD_NAME}
										{assign var=FIELD_MODELTMP value=$FIELD_MODEL}
										{assign var=FIELD_NAME value='mailingaddressformat'}
										{assign var=FIELD_MODEL value=$RECORD_STRUCTURE_MODEL->getField($FIELD_NAME)}
										{assign var=UITYPEMODEL value=$FIELD_MODEL->getUITypeModel()->getTemplateName()}
										{include file=vtemplate_path($UITYPEMODEL,$MODULE) FORCE_FIELD_NAME='mailingaddressformat_synchronize' BLOCK_FIELDS=$BLOCK_FIELDS RECORD_MODEL=$RECORD_MODEL}
										{assign var=FIELD_NAME value=$FIELD_NAMETMP}
										{assign var=FIELD_MODEL value=$FIELD_MODELTMP}	
									</span>
								</span>
							{/if}
						</div>
						
					</td>
				{/if}
				{if $BLOCK_FIELDS|@count eq 1 and $FIELD_MODEL->get('uitype') neq "19" and $FIELD_MODEL->get('uitype') neq "20" and $FIELD_MODEL->get('uitype') neq "30" and $FIELD_MODEL->get('name') neq "recurringtype"}
					<td class="{$WIDTHTYPE}"></td><td class="{$WIDTHTYPE}"></td>
				{/if}
			{/foreach}
			</tr>
			</tbody>
			</table>
			<br>
		{/foreach}
{/strip}