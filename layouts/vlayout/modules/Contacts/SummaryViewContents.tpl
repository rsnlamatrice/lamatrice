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
 /* ED141010 : add RECORD_MODEL=$RECORD
 *
 * ED150123 : champs DO NOT inclus avec emailoptout (ne pas envoyer d'email)
 *	champ phone ajouté avec email
 */
-->*}
{strip}
{assign var=DONOT_FIELDS value=array('emailoptout', 'donotcall', 'donotprospect', 'donotrelanceadh', 'donotappeldoncourrier', 'donotrelanceabo', 'donotappeldonweb')}
<table class="summary-table">
	<tbody>
	{foreach item=FIELD_MODEL key=FIELD_NAME from=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS']}
		{if $FIELD_NAME neq 'modifiedtime'
		&& $FIELD_NAME neq 'createdtime'
		&& $FIELD_NAME neq 'mailingcountry'
		&& $FIELD_NAME neq 'mailingzip'
		&& $FIELD_NAME neq 'donotcall'
		&& $FIELD_NAME neq 'donotprospect'
		&& $FIELD_NAME neq 'donotrelanceadh'
		&& $FIELD_NAME neq 'donotappeldoncourrier'
		&& $FIELD_NAME neq 'donotrelanceabo'
		&& $FIELD_NAME neq 'donotappeldonweb'
		&& $FIELD_NAME neq 'phone'}
			<tr class="summaryViewEntries">
				<td class="fieldLabel" style="width:30%"><label class="muted">
				{if $FIELD_NAME == 'emailoptout'}
					Ne pas...
				{elseif $FIELD_NAME == 'email'}
					Email, téléphone
				{else}
					{vtranslate($FIELD_MODEL->get('label'),$MODULE_NAME)}
				{/if}</label></td>
				<td class="fieldValue" style="width:70%">
					<div class="row-fluid">
						<span class="value span10" style="word-wrap: break-word;">
							{if $FIELD_MODEL->get('uitype') eq '15'}{* ED141005 *}
								{$RECORD->getDisplayValue($FIELD_NAME)}
							{* ED150105 "référent du compte" masqué si par de compte *}
							{elseif ($FIELD_NAME == 'reference') && (!$RECORD->get('account_id'))}
								<i>pas de compte</i>
							{* DO NOT *}
							{elseif $FIELD_NAME eq 'emailoptout'}
								{assign var=DONOT_COUNTER value=0}
								{assign var=DO_COUNTER value=0}
								{* compte le nombre de cochés et de pas cochés *}
								{foreach item=DONOT_FIELD from=$DONOT_FIELDS}
									{if $RECORD->get($DONOT_FIELD)}
										{assign var=DONOT_COUNTER value=$DONOT_COUNTER+1}
									{else}
										{assign var=DO_COUNTER value=$DO_COUNTER+1}
									{/if}
								{/foreach}
								<div class="inline-children">
								{if (($DO_COUNTER > 0) && ($DONOT_COUNTER eq 0)) }
									{assign var=DONNOT_ALLTHESAME value=$RECORD->getPicklistValuesDetails('(all the same)')}
									{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD LABELS=$DONNOT_ALLTHESAME}
								{elseif (($DONOT_COUNTER > 0) && ($DO_COUNTER eq 0)) }
									{assign var=DONNOT_ALLTHESAME value=$RECORD->getPicklistValuesDetails('(all the same)')}
									{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD LABELS=$DONNOT_ALLTHESAME}
								{else}
									{assign var=FIELD_MODEL_TMP value=$FIELD_MODEL}
									{foreach item=DONOT_FIELD from=$DONOT_FIELDS}
										{if $RECORD->get($DONOT_FIELD)}
											{assign var=FIELD_MODEL value=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS'][$DONOT_FIELD]}
											{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
										{/if}
									{/foreach}
									{assign var=FIELD_MODEL value=$FIELD_MODEL_TMP}
								{/if}
								</div>
							{* concaténation de cp + ville + pays *}
							{elseif $FIELD_NAME eq 'mailingcity'}
								{$RECORD->getDisplayValue('mailingzip')}&nbsp;
								
								{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
								
								{if $RECORD->get('mailingcountry')}
									&nbsp;-&nbsp;{$RECORD->getDisplayValue('mailingcountry')}
								{/if}
							{* concaténation de email + phone *}
							{elseif $FIELD_NAME eq 'email'}
								{if $RECORD->get('phone')}
									{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
									&nbsp;-&nbsp;{$RECORD->getDisplayValue('phone')}
								{/if}
								
							{else}
								{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
							{/if}
						</span>
						{if $FIELD_MODEL->isEditable() eq 'true' && ($FIELD_MODEL->getFieldDataType()!=Vtiger_Field_Model::REFERENCE_TYPE) && $IS_AJAX_ENABLED && $FIELD_MODEL->isAjaxEditable() eq 'true' && $FIELD_MODEL->get('uitype') neq 69}
							
							<span class="summaryViewEdit cursorPointer span2">
								<i class="icon-pencil" title="{vtranslate('LBL_EDIT',$MODULE_NAME)}"></i>
							</span>
									
							{* DO NOT *}
							{if $FIELD_NAME eq 'emailoptout'}
								{assign var=FIELD_MODEL_TMP value=$FIELD_MODEL}
								{foreach item=DONOT_FIELD from=$DONOT_FIELDS}
									<span class="hide edit span10">{* ED141010 : add RECORD_MODEL=$RECORD*}
										{assign var=FIELD_MODEL value=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS'][$DONOT_FIELD]}
										{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD_MODEL=$RECORD}
										<input type="hidden" class="fieldname" value='{$DONOT_FIELD}' data-prev-value='{$FIELD_MODEL->get('fieldvalue')}' />
									</span>
								{/foreach}
								{assign var=FIELD_MODEL value=$FIELD_MODEL_TMP}
							{else}
								<span class="hide edit span10">{* ED141010 : add RECORD_MODEL=$RECORD*}
									{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD_MODEL=$RECORD}
									{if $FIELD_MODEL->getFieldDataType() eq 'multipicklist'}
										<input type="hidden" class="fieldname" value='{$FIELD_NAME}[]' data-prev-value='{$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'))}' />
									{else}
										<input type="hidden" class="fieldname" value='{$FIELD_NAME}' data-prev-value='{$FIELD_MODEL->get('fieldvalue')}' />
									{/if}
								</span>
								{if $FIELD_NAME eq 'mailingcity'}
									{assign var=FIELD_NAME value='mailingzip'}
									{assign var=FIELD_MODEL value=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS'][$FIELD_NAME]}
									<span class="hide edit span10" title="Code postal. Préfixez avec le code du pays (exple : B-1531)">{* ED141010 : add RECORD_MODEL=$RECORD*}
										{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD_MODEL=$RECORD}
										<input type="hidden" class="fieldname" value='{$FIELD_NAME}' data-prev-value='{$FIELD_MODEL->get('fieldvalue')}' />
									</span>								
									
									{assign var=FIELD_NAME value='mailingcountry'}
									{assign var=FIELD_MODEL value=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS'][$FIELD_NAME]}
									<span class="hide edit span10" title="Pays">{* ED141010 : add RECORD_MODEL=$RECORD*}
										{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD_MODEL=$RECORD}
										<input type="hidden" class="fieldname" value='{$FIELD_NAME}' data-prev-value='{$FIELD_MODEL->get('fieldvalue')}' />
									</span>
								
								{* email, phone *}
								{elseif $FIELD_NAME eq 'email'}
									{assign var=FIELD_NAME value='phone'}
									{assign var=FIELD_MODEL value=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS'][$FIELD_NAME]}
									<span class="hide edit span10">{* ED141010 : add RECORD_MODEL=$RECORD*}
										{assign var=FIELD_MODEL value=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS'][$FIELD_NAME]}
										{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD_MODEL=$RECORD}
										<input type="hidden" class="fieldname" value='{$FIELD_NAME}' data-prev-value='{$FIELD_MODEL->get('fieldvalue')}' />
									</span>
								{/if}
							{/if}
						{/if}
					</div>
				</td>
			</tr>
		{/if}
	{/foreach}
	</tbody>
</table>
<hr>
<div class="row-fluid">
	<div class="span4 toggleViewByMode">
		{assign var="CURRENT_VIEW" value="full"}
		{assign var="CURRENT_MODE_LABEL" value="{vtranslate('LBL_COMPLETE_DETAILS',{$MODULE_NAME})}"}
		<button type="button" class="btn changeDetailViewMode cursorPointer"><strong>{vtranslate('LBL_SHOW_FULL_DETAILS',$MODULE_NAME)}</strong></button>
		{assign var="FULL_MODE_URL" value={$RECORD->getDetailViewUrl()|cat:'&mode=showDetailViewByMode&requestMode=full'} }
		<input type="hidden" name="viewMode" value="{$CURRENT_VIEW}" data-nextviewname="full" data-currentviewlabel="{$CURRENT_MODE_LABEL}"
			data-full-url="{$FULL_MODE_URL}"  />
	</div>
	<div class="span8">
		<div class="pull-right">
			<div>
				<p>
					<small>
						<em>{vtranslate('LBL_CREATED_ON',$MODULE_NAME)} <b>{Vtiger_Util_Helper::formatDateTimeIntoDayString($RECORD->get('createdtime'))}</b></em>
					</small>
				</p>
			</div>
			<div>
				<p>
					<small>
						<em>{vtranslate('LBL_MODIFIED_ON',$MODULE_NAME)} <b>{Vtiger_Util_Helper::formatDateTimeIntoDayString($RECORD->get('modifiedtime'))}</b></em>
					</small>
				</p>
			</div>
		</div>
	</div>
</div>
{/strip}