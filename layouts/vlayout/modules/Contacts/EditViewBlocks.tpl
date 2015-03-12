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
			<h3 class="span8 textOverflowEllipsis" title="{vtranslate('LBL_EDITING', $MODULE)} {vtranslate($SINGLE_MODULE_NAME, $MODULE)} {$RECORD_STRUCTURE_MODEL->getRecordName()}">{vtranslate('LBL_EDITING', $MODULE)} {vtranslate($SINGLE_MODULE_NAME, $MODULE)} - {$RECORD_STRUCTURE_MODEL->getRecordName()}</h3>
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
			<table class="table table-bordered blockContainer showInlineTable {if $BLOCK_LABEL eq "LBL_ADDRESS_INFORMATION"}current-address{/if}">
			<tr>
				<th class="blockHeader" colspan="4">{vtranslate($BLOCK_LABEL, $MODULE)}</th>
			</tr>
			<tr>
			{assign var=COUNTER value=0}
			{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
			
				{if $FIELD_NAME eq 'otherstreet2'
				|| $FIELD_NAME eq 'mailingstreet2'
				|| $FIELD_NAME eq 'otherstreet3'
				|| $FIELD_NAME eq 'mailingstreet3'
				|| $FIELD_NAME eq 'mailingzip'
				|| $FIELD_NAME eq 'otherzip'
				|| $FIELD_NAME eq 'mailingpobox'
				|| $FIELD_NAME eq 'otherpobox'
				|| $FIELD_NAME eq 'mailingcountry'
				|| $FIELD_NAME eq 'othercountry'
				|| $FIELD_NAME eq 'rsnnpaicomment'}
					{continue}
				{/if}
				
				{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
				{if $FIELD_MODEL->get('uitype') eq "20" || $FIELD_MODEL->get('uitype') eq "19"}
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
							<span class="subFieldLabel">&nbsp;</span>
							<span class="subFieldLabel">{vtranslate($FIELD_MODEL->get('label'), $MODULE)}</span>
							<span class="subFieldLabel" style="color: #88bbbb;">&gt;</span>
						{else}
							{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
						{/if}
					{/if}
					{if $isReferenceField neq "reference"}</label>{/if}
				</td>
				{if $FIELD_MODEL->get('uitype') neq "83"}
					<td class="fieldValue {$WIDTHTYPE}" {if $FIELD_MODEL->get('uitype') eq '19' or $FIELD_MODEL->get('uitype') eq '20' or $FIELD_NAME eq 'rsnnpai'} colspan="3" {assign var=COUNTER value=$COUNTER+1} {/if}>
						<div class="row-fluid">
							<span class="{if $FIELD_NAME eq 'rsnnpai'}span6{else}span10{/if}">{* ED141005 *}
								{* street2/3*}
								{if $FIELD_NAME eq 'otherstreet'
								|| $FIELD_NAME eq 'mailingstreet'}
									{* street2 *}
									{assign var=TITLE value='Nom de la structure ou n°appt, n°BAL, escalier, couloir, ...'}
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
						</div>
						
					</td>
				{/if}
				{if $BLOCK_FIELDS|@count eq 1 and $FIELD_MODEL->get('uitype') neq "19" and $FIELD_MODEL->get('uitype') neq "20" and $FIELD_MODEL->get('uitype') neq "30" and $FIELD_MODEL->get('name') neq "recurringtype"}
					<td class="{$WIDTHTYPE}"></td><td class="{$WIDTHTYPE}"></td>
				{/if}
				{if $MODULE eq 'Events' && $BLOCK_LABEL eq 'LBL_EVENT_INFORMATION' && $smarty.foreach.blockfields.last }
					{include file=vtemplate_path('uitypes/FollowUp.tpl',$MODULE) COUNTER=$COUNTER}
				{/if}
			{/foreach}
			</tr>
			</table>
			<br>
		{/foreach}
{/strip}