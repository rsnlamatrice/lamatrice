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
		{if $IS_DUPLICATE_FROM}{* ED150207 *}
			<input type="hidden" name="isDuplicateFrom" value="{$IS_DUPLICATE_FROM}" />
		{/if}
		<div class="contentHeader row-fluid">
		{assign var=SINGLE_MODULE_NAME value='SINGLE_'|cat:$MODULE}
		{if $RECORD_ID neq ''}
			<h3 class="span8 textOverflowEllipsis" title="{vtranslate('LBL_EDITING', $MODULE)} {vtranslate($SINGLE_MODULE_NAME, $MODULE)} {$RECORD_STRUCTURE_MODEL->getRecordName()}">{vtranslate('LBL_EDITING', $MODULE)} {vtranslate($SINGLE_MODULE_NAME, $MODULE)} - {$RECORD_STRUCTURE_MODEL->getRecordName()}
		
			{if $RECORD_MODEL->get('etat') neq '0'}
				<br>
				<span class="recordLabel font-x-x-large" style="color: red">
					{if $RECORD_MODEL->get('etat') == 1}Prélèvement suspendu
					{elseif $RECORD_MODEL->get('etat') == 2}Prélèvement arrété
					{/if}
				</span>
			{/if}
			{if $RelatedPrelVirementsCount}
				<br>
				<span class="recordLabel font-x-x-large" style="color: red">
				{if $RelatedPrelVirementsCount == 1}Un ordre de prélèvement existe déjà
				{else}{$RelatedPrelVirementsCount} ordres de prélèvement existent déjà
				{/if}
				</span>
			{/if}
			</h3>
		{else}
			<h3 class="span8 textOverflowEllipsis">{vtranslate('LBL_CREATING_NEW', $MODULE)} {vtranslate($SINGLE_MODULE_NAME, $MODULE)}</h3>
		{/if}
			<span class="pull-right">
				{if !$NOT_EDITABLE}
					<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
				{elseif !is_numeric($NOT_EDITABLE)}
				    <i>{vtranslate($NOT_EDITABLE, $MODULE)}</i>
				{/if}
				<a class="cancelLink" type="reset" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL', $MODULE)}</a>
			</span>
		</div>
		{if $IS_DUPLICATE_FROM}{* ED11204 *}
			<div class="contentHeader row-fluid">
				<h3>Le prélèvement dupliqué sera arrété</h3>
			</div>
		{/if}
		{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name="EditViewBlockLevelLoop"}
			{if $BLOCK_FIELDS|@count lte 0}{continue}{/if}
			<table class="table table-bordered blockContainer showInlineTable block-{$BLOCK_LABEL}">
			<tr>
				<th class="blockHeader" colspan="4">{vtranslate($BLOCK_LABEL, $MODULE)}</th>
			</tr>
			<tr>
			{assign var=COUNTER value=0}
			{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
				{if $FIELD_NAME eq 'codeguichet'
				|| $FIELD_NAME eq 'numcompte'
				|| $FIELD_NAME eq 'clerib'
				|| $FIELD_NAME eq 'nom'
				|| $FIELD_NAME eq 'sepaibanbban'
				|| $FIELD_NAME eq 'sepabic'
				|| $FIELD_NAME eq 'sepaibancle'
				|| $FIELD_NAME eq 'sepadatesignature'
				|| $FIELD_NAME eq 'separum'}
					{continue}
				{/if}

				{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
				{if $FIELD_MODEL->get('uitype') eq "20" or $FIELD_MODEL->get('uitype') eq "19"}
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

				{if $FIELD_NAME eq 'codebanque'}
					{assign var=FIELD_NAMETMP value=$FIELD_NAME}
					{assign var=FIELD_MODELTMP value=$FIELD_MODEL}

					<td class="fieldLabel {$WIDTHTYPE}">
						<table style="width:100%;">
							<tr>
								<td class="fieldLabel {$WIDTHTYPE}"></td>
								<td><div class="text-center" style="width:100px;margin:0px 5px;display:inline-block;">Code Banque</div>
								<div class="text-center" style="width:100px;margin:0px 5px;display:inline-block;">Code Guichet</div>
								<div class="text-center" style="width:220px;margin:0px 5x;display:inline-block;">N° de Compte</div>
								<div class="text-center" style="width:50px;margin:0px 5px;display:inline-block;">Cle RIB</div></td>
							</tr>
							<tr>
								<td class="fieldLabel {$WIDTHTYPE}">
									<label class="muted pull-right marginRight10px"><span class="redColor">*</span> RIB</label>
								</td>
								<td>
									{* Code Banque *}
									{assign var=TITLE value='Code Banque'}									
									{assign var=FIELD_NAME value='codebanque'}
									{assign var=FIELD_MODEL value=$BLOCK_FIELDS[$FIELD_NAME]}
									{assign var=UITYPEMODEL value=$FIELD_MODEL->getUITypeModel()->getTemplateName()}
									{include file=vtemplate_path($UITYPEMODEL,$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS RECORD_MODEL=$RECORD_MODEL INPUT_CLASS='input-medium'}

									{* Code Guichet *}
									{assign var=TITLE value='Code Guichet...'}
									{assign var=FIELD_NAME value='codeguichet'}
									{assign var=FIELD_MODEL value=$BLOCK_FIELDS[$FIELD_NAME]}
									{assign var=UITYPEMODEL value=$FIELD_MODEL->getUITypeModel()->getTemplateName()}
									{include file=vtemplate_path($UITYPEMODEL,$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS RECORD_MODEL=$RECORD_MODEL INPUT_CLASS='input-medium'}

									{* Num Compte *}
									{assign var=TITLE value='N° de Compte'}
									{assign var=FIELD_NAME value='numcompte'}
									{assign var=FIELD_MODEL value=$BLOCK_FIELDS[$FIELD_NAME]}
									{assign var=UITYPEMODEL value=$FIELD_MODEL->getUITypeModel()->getTemplateName()}
									{include file=vtemplate_path($UITYPEMODEL,$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS RECORD_MODEL=$RECORD_MODEL}

									{* Cle RIB *}
									{assign var=TITLE value='Clé RIB'}
									{assign var=FIELD_NAME value='clerib'}
									{assign var=FIELD_MODEL value=$BLOCK_FIELDS[$FIELD_NAME]}
									{assign var=UITYPEMODEL value=$FIELD_MODEL->getUITypeModel()->getTemplateName()}
									{include file=vtemplate_path($UITYPEMODEL,$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS RECORD_MODEL=$RECORD_MODEL INPUT_CLASS='input-small'}
								</td>
							</tr>
							<tr>
								{* Nom *}
								{assign var=TITLE value='nom'}
								{assign var=FIELD_NAME value='nom'}
								{assign var=FIELD_MODEL value=$BLOCK_FIELDS[$FIELD_NAME]}
								{assign var=UITYPEMODEL value=$FIELD_MODEL->getUITypeModel()->getTemplateName()}
								<td class="fieldLabel {$WIDTHTYPE}">
									<label class="muted pull-right marginRight10px">
									{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}
										nom
									</label>
								</td>
								<td>
									{include file=vtemplate_path($UITYPEMODEL,$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS RECORD_MODEL=$RECORD_MODEL INPUT_CLASS='input-large'}
								</td>
							</tr>
						</table>
					</td>

					{assign var=FIELD_NAME value=$FIELD_NAMETMP}
					{assign var=FIELD_MODEL value=$FIELD_MODELTMP}
					{assign var=TITLE value=''}
				{elseif $FIELD_NAME eq 'sepaibanpays'}
					{assign var=FIELD_NAMETMP value=$FIELD_NAME}
					{assign var=FIELD_MODELTMP value=$FIELD_MODEL}

					<td class="fieldLabel {$WIDTHTYPE}">
						<table style="width:100%">
							<tr>
								<td class="fieldLabel {$WIDTHTYPE}"></td>
								<td>
									<div class="text-center" style="width:50px;margin:0px 5px;display:inline-block;">Pays</div>
									<div class="text-center" style="width:50px;margin:0px 5px;display:inline-block;">Clé</div>
									<div class="text-center" style="width:210px;margin:0px 5px;display:inline-block;">BBAN</div>
								</td>
							</tr>
							<tr>
								<td class="fieldLabel {$WIDTHTYPE}">
									<label class="muted pull-right marginRight10px"><span class="redColor">*</span> IBAN</label>
								</td>
								<td>
									{* Code Pays *}
									{assign var=TITLE value='Pays'}									
									{assign var=FIELD_NAME value='sepaibanpays'}
									{assign var=FIELD_MODEL value=$BLOCK_FIELDS[$FIELD_NAME]}
									{assign var=UITYPEMODEL value=$FIELD_MODEL->getUITypeModel()->getTemplateName()}
									{include file=vtemplate_path($UITYPEMODEL,$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS RECORD_MODEL=$RECORD_MODEL INPUT_CLASS='input-small'}

									{* Clé *}
									{assign var=TITLE value='Clé'}
									{assign var=FIELD_NAME value='sepaibancle'}
									{assign var=FIELD_MODEL value=$BLOCK_FIELDS[$FIELD_NAME]}
									{assign var=UITYPEMODEL value=$FIELD_MODEL->getUITypeModel()->getTemplateName()}
									{include file=vtemplate_path($UITYPEMODEL,$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS RECORD_MODEL=$RECORD_MODEL INPUT_CLASS='input-small'}

									{* BBAN *}
									{assign var=TITLE value='BBAN'}
									{assign var=FIELD_NAME value='sepaibanbban'}
									{assign var=FIELD_MODEL value=$BLOCK_FIELDS[$FIELD_NAME]}
									{assign var=UITYPEMODEL value=$FIELD_MODEL->getUITypeModel()->getTemplateName()}
									{include file=vtemplate_path($UITYPEMODEL,$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS RECORD_MODEL=$RECORD_MODEL}
								</td>
							</tr>
							<tr>
								{* BIC *}
								{assign var=TITLE value='BIC'}
								{assign var=FIELD_NAME value='sepabic'}
								{assign var=FIELD_MODEL value=$BLOCK_FIELDS[$FIELD_NAME]}
								{assign var=UITYPEMODEL value=$FIELD_MODEL->getUITypeModel()->getTemplateName()}
								<td class="fieldLabel {$WIDTHTYPE}">
									<label class="muted pull-right marginRight10px">
									{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}
										BIC
									</label>
								</td>
								<td>
									{include file=vtemplate_path($UITYPEMODEL,$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS RECORD_MODEL=$RECORD_MODEL INPUT_CLASS='input-large'}
								</td>
							</tr>
							<tr>
								{assign var=FIELD_NAME value='sepadatesignature'}
								{assign var=FIELD_MODEL value=$BLOCK_FIELDS[$FIELD_NAME]}
								<td class="fieldLabel {$WIDTHTYPE}">
									<label class="muted pull-right marginRight10px">
									{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}
										Date de signature
									</label>
								</td>
								<td>
									<div style="width:300px;">
										{* Date de signature *}
										{assign var=TITLE value=''}
										{assign var=UITYPEMODEL value=$FIELD_MODEL->getUITypeModel()->getTemplateName()}
										{include file=vtemplate_path($UITYPEMODEL,$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS RECORD_MODEL=$RECORD_MODEL}
									</div>
								</td>
							</tr>
							<tr>
								{* RUM non modifiable *}
								{assign var=FIELD_NAME value='separum'}
								<td class="fieldLabel {$WIDTHTYPE}">
									<label class="muted pull-right marginRight10px">
										RUM
									</label>
								</td>
								<td>
									<div style="width:300px;">
										{$RECORD_MODEL->get($FIELD_NAME)}
									</div>
								</td>
							</tr>
						</table>
					</td>

					{assign var=FIELD_NAME value=$FIELD_NAMETMP}
					{assign var=FIELD_MODEL value=$FIELD_MODELTMP}
					{assign var=TITLE value=''}
				{else}
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
								{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
							{/if}
						{if $isReferenceField neq "reference"}</label>{/if}
					</td>
					{if $FIELD_MODEL->get('uitype') neq "83"}
						<td class="fieldValue {$WIDTHTYPE}" {if $FIELD_MODEL->get('uitype') eq '19' or $FIELD_MODEL->get('uitype') eq '20'} colspan="3" {assign var=COUNTER value=$COUNTER+1} {/if}>
							<div class="row-fluid">
								<span class="span10">
									{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS}
								</span>
							</div>
						</td>
					{/if}
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