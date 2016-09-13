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
 *	référent du compte
 */
-->*}
{strip}
{* champs Ne pas... *}
{assign var=DONOT_FIELDS value=array('emailoptout', 'donotcall', 'donotprospect', 'donotrelanceadh', 'donotappeldoncourrier', 'donotrelanceabo', 'donotappeldonweb', 'donotrecufiscal', 'donototherdocuments')}
{assign var=SKIP_FIELDS value=array(
		'modifiedtime','createdtime',
		'mailingcountry','mailingzip','mailingstreet2','mailingrnvpeval','mailingrnvpcharade',
		'donotcall','donotprospect','donotrelanceadh','donotappeldoncourrier','donotrelanceabo','donotappeldonweb','donotrecufiscal','donototherdocuments',
		'reference',
		'phone','mobile','homephone',
		'rsnnpai',
		'contact_no','firstname','isgroup',
		'leadsource',
		'latitude')}
{* ED150515 : account_id needed for 'reference' changing confirmation *}
<input type=hidden name="account_id" data-value='{$RECORD->get('account_id')}' />
	
<table class="summary-table">
	<tbody>
	{foreach item=FIELD_MODEL key=FIELD_NAME from=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS']}
		{if !in_array($FIELD_NAME, $SKIP_FIELDS)		
		&& ($FIELD_NAME neq 'description' || $RECORD->get($FIELD_NAME) )
		&& ($FIELD_NAME neq 'longitude' || $FIELD_MODEL->get('fieldvalue'))
		}
			<tr class="summaryViewEntries">
				<td class="fieldLabel" style="width:30%"><label class="muted">
				{if $FIELD_NAME == 'emailoptout'}
					Ne pas...
				{elseif $FIELD_NAME == 'email'}
					Email, téléphones
				{elseif $FIELD_NAME == 'lastname'}
					Contact
					<span class="pull-right" style="padding-right:4px;">
						{$RECORD->get('contact_no')}
					</span>
				{elseif $FIELD_NAME == 'contacttype'}
					Type de contact / origine
				{elseif $FIELD_NAME == 'longitude'}
					Coordonnées GPS
				{elseif $FIELD_NAME == 'mailingcity'}
					Adresse
					{* status NPAI *}
					{if $RECORD->get('rsnnpai') !== ''}
						{assign var=FIELD_MODEL_TMP value=$FIELD_MODEL}
						{assign var=FIELD_NAME_TMP value=$FIELD_NAME}
						{assign var=FIELD_NAME value='rsnnpai'}
						{assign var=FIELD_MODEL value=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS'][$FIELD_NAME]}
						{if !$FIELD_MODEL}{$FIELD_NAME} n'existe pas !{/if}
						<span class="pull-right" style="padding-right:4px;">
							{if $RECORD->get('rsnnpai') neq '0' && !$RECORD->get('rsnnpaidate')}
								<span style="margin-left:19px;">NPAI</span>
							{/if}
							{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
						</span>
						{assign var=FIELD_MODEL value=$FIELD_MODEL_TMP}
						{assign var=FIELD_NAME value=$FIELD_NAME_TMP}	
						{if $RECORD->get('rsnnpaidate')}
							<div style="padding-left: 6px; white-space: nowrap;">
								NPAI au {DateTimeField::convertToUserFormat($RECORD->get('rsnnpaidate'))}
							</div>
						{/if}
					{/if}
					{* RNVP *}
					{if $RECORD->initRNVPLabel()}
						<div style="padding-left: 6px;">
							RNVP : {$RECORD->get('mailingRNVPLabel')}
						</div>
					{/if}
				{else}
					{vtranslate($FIELD_MODEL->get('label'),$MODULE_NAME)}
				{/if}</label></td>
				<td class="fieldValue" style="width:70%">
					<div class="row-fluid">
						<span class="value span10" style="word-wrap: break-word;">
							{if $FIELD_MODEL->get('uitype') eq '15'}{* ED141005 *}
								{$RECORD->getDisplayValue($FIELD_NAME)}
							{* concaténation de prénom + nom + mailingstreet2 *}
							{elseif $FIELD_NAME eq 'lastname'}
								{$RECORD->getHtmlLabel('isgroup,firstname,lastname,mailingstreet2')}
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
									{* affiche "si, on peut tout faire" *}
									{assign var=DONNOT_ALLTHESAME value=$RECORD->getPicklistValuesDetails('(all the same)')}
									{* bug si vide *}
									{if $FIELD_MODEL->get('fieldvalue') === ''}
										{* cherche le 1er item *}
										{foreach item=DONNOT_ALLTHESAME_ITEM key=DONNOT_ALLTHESAME_KEY from=$DONNOT_ALLTHESAME}
											{assign var=TMP value=$FIELD_MODEL->set('fieldvalue', $DONNOT_ALLTHESAME_KEY)}
											{break}
										{/foreach}
									{/if}
									{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD LABELS=$DONNOT_ALLTHESAME}
								{elseif (($DONOT_COUNTER > 0) && ($DO_COUNTER eq 0)) }
									{* affiche "on ne peut rien faire" *}
									{*assign var=DONNOT_ALLTHESAME value=$RECORD->getPicklistValuesDetails('(all the same)')}
									{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD LABELS=$DONNOT_ALLTHESAME*}
									{* affiche "reçu fiscal seul" *}
									{assign var=FIELD_MODEL_TMP value=$FIELD_MODEL}
									{assign var=FIELD_NAME_TMP value=$FIELD_NAME}
									{assign var=FIELD_NAME value='donototherdocuments'}
									{assign var=FIELD_MODEL value=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS'][$FIELD_NAME]}
									{if !$FIELD_MODEL}{$FIELD_NAME} n'existe pas !{/if}
									{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
									{assign var=FIELD_MODEL value=$FIELD_MODEL_TMP}
									{assign var=FIELD_NAME value=$FIELD_NAME_TMP}	
								{else}
									{* affiche les labels "ne pas" *}
									{assign var=FIELD_MODEL_TMP value=$FIELD_MODEL}
									{foreach item=DONOT_FIELD from=$DONOT_FIELDS}
										{if $RECORD->get($DONOT_FIELD)}
											{assign var=FIELD_MODEL value=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS'][$DONOT_FIELD]}
											{if FIELD_MODEL === null}
												{$DONOT_FIELD} inconnu (need UPDATE `vtiger_field` SET `summaryfield` = '1' WHERE `vtiger_field`.`fieldid` = ??{$DONOT_FIELD};)
											{else}
												{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
											{/if}
										{/if}
									{/foreach}
									{assign var=FIELD_MODEL value=$FIELD_MODEL_TMP}
								{/if}
								</div>
							{* concaténation de cp + ville + pays *}
							{elseif $FIELD_NAME eq 'mailingcity'}
								{if $RECORD->get('mailingstreet')}
									{$RECORD->getDisplayValue('mailingstreet')}&nbsp;-&nbsp;
								{/if}
								{$RECORD->getDisplayValue('mailingzip')}&nbsp;
								
								{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
								
								{if $RECORD->get('mailingcountry')}
									&nbsp;-&nbsp;{$RECORD->getDisplayValue('mailingcountry')}
								{/if}
								{if $RECORD->get('mailingmodifiedtime') != $RECORD->get('modifiedtime')
								&& $RECORD->get('mailingmodifiedtime') != $RECORD->get('rsnnpaidate')}
									<div>
										Adr. au {DateTimeField::convertToUserFormat($RECORD->get('mailingmodifiedtime'))}
									</div>
								{/if}
							{* concaténation de nom du compte + reference *}
							{elseif $FIELD_NAME eq 'account_id'}
								{* origin *}
								{if $RECORD->get($FIELD_NAME)}
									<span style="float: left; opacity: 0.8;">
									{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
									</span>
									<span style="float: left; margin-left: 1em;">
									{assign var=FIELD_MODEL_TMP value=$FIELD_MODEL}
									{assign var=FIELD_NAME_TMP value=$FIELD_NAME}
									{assign var=FIELD_NAME value='reference'}
									{assign var=FIELD_MODEL value=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS'][$FIELD_NAME]}
									{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
									{assign var=FIELD_MODEL value=$FIELD_MODEL_TMP}
									{assign var=FIELD_NAME value=$FIELD_NAME_TMP}
									</span>
									{if !$RECORD->get($FIELD_NAME)}
										<span style="float: left;">
										référent du compte
										</span>
									{/if}
								{/if}
								
							{* concaténation de email + phone *}
							{elseif $FIELD_NAME eq 'email'}
								{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
								{if $RECORD->get('phone')}
									&nbsp;-&nbsp;{$RECORD->getDisplayValue('phone')}
								{/if}
								{if $RECORD->get('mobile')}
									&nbsp;-&nbsp;{$RECORD->getDisplayValue('mobile')}
								{/if}
								{if $RECORD->get('homephone')}
									&nbsp;-&nbsp;{$RECORD->getDisplayValue('homephone')}
								{/if}
								
							{* type de contact + origin *}
							{elseif $FIELD_NAME == 'contacttype'}
								{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
								{* origin *}
								{if $RECORD->get('leadsource')}
									{if $RECORD->get($FIELD_NAME)}<br>{/if}
									{assign var=FIELD_MODEL_TMP value=$FIELD_MODEL}
									{assign var=FIELD_NAME_TMP value=$FIELD_NAME}
									{assign var=FIELD_NAME value='leadsource'}
									{assign var=FIELD_MODEL value=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS'][$FIELD_NAME]}
									{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
									{assign var=FIELD_MODEL value=$FIELD_MODEL_TMP}
									{assign var=FIELD_NAME value=$FIELD_NAME_TMP}
								{/if}
							{elseif $FIELD_NAME eq 'longitude'}
								{assign var=FIELD_MODEL_2 value=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS']['latitude']}
								<a href="https://www.google.fr/maps/search/{include file=$FIELD_MODEL_2->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL_2 USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD},{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}" target="_blank" title="Voir sur google maps">
									{include file=$FIELD_MODEL_2->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL_2 USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD},{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
								</a>
							{else}
								{include file=$FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName()|@vtemplate_path FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
								
							{/if}
						</span>
						{if $FIELD_MODEL->isEditable() eq 'true' && ($FIELD_MODEL->getFieldDataType()!=Vtiger_Field_Model::REFERENCE_TYPE) && $IS_AJAX_ENABLED && $FIELD_MODEL->isAjaxEditable() eq 'true' && $FIELD_MODEL->get('uitype') neq 69}
							{if $FIELD_NAME neq 'longitude'}
								<span class="summaryViewEdit cursorPointer span2">
									<i class="icon-pencil" title="{vtranslate('LBL_EDIT',$MODULE_NAME)}"></i>
								</span>
							{/if}
									
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
								
								{* AVANT *}
								{* lastname : isgroup, street2 *}
								{if $FIELD_NAME eq 'lastname'}
									{assign var=FIELD_MODEL_TMP value=$FIELD_MODEL}
									{assign var=FIELD_NAME_TMP value=$FIELD_NAME}
									{assign var=FIELD_NAME value='firstname'}
									{assign var=FIELD_MODEL value=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS'][$FIELD_NAME]}
									{if $FIELD_MODEL}
									<span class="hide edit span10" title="Prénom">
										{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD_MODEL=$RECORD TITLE=vtranslate($FIELD_MODEL->get('label'),$MODULE_NAME)}
										<input type="hidden" class="fieldname" value='{$FIELD_NAME}' data-prev-value='{$FIELD_MODEL->get('fieldvalue')}' />
									</span>
									{/if}
									{assign var=FIELD_MODEL value=$FIELD_MODEL_TMP}
									{assign var=FIELD_NAME value=$FIELD_NAME_TMP}	
								{* code postal *}
								{elseif $FIELD_NAME eq 'mailingcity'}
									{assign var=FIELD_MODEL_TMP value=$FIELD_MODEL}
									{assign var=FIELD_NAME_TMP value=$FIELD_NAME}
									{assign var=FIELD_NAME value='mailingzip'}
									{assign var=FIELD_MODEL value=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS'][$FIELD_NAME]}
									{if !$FIELD_MODEL}{$FIELD_NAME} n'existe pas !{/if}
									<span class="hide edit span10" title="Code postal. Préfixez avec le code du pays (exple : B-1531)">{* ED141010 : add RECORD_MODEL=$RECORD*}
										{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD_MODEL=$RECORD TITLE=vtranslate($FIELD_MODEL->get('label'),$MODULE_NAME)}
										<input type="hidden" class="fieldname" value='{$FIELD_NAME}' data-prev-value='{$FIELD_MODEL->get('fieldvalue')}' />
									</span>	
									{assign var=FIELD_MODEL value=$FIELD_MODEL_TMP}
									{assign var=FIELD_NAME value=$FIELD_NAME_TMP}
								{elseif $FIELD_NAME eq 'longitude'}
									{assign var=FIELD_MODEL_TMP value=$FIELD_MODEL}
									{assign var=FIELD_NAME_TMP value=$FIELD_NAME}
									{assign var=FIELD_NAME value='latitude'}
									{assign var=FIELD_MODEL value=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS'][$FIELD_NAME]}
									<span class="hide edit span10">{* ED141010 : add RECORD_MODEL=$RECORD*}
										{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD_MODEL=$RECORD}
										<input type="hidden" class="fieldname" value='{$DONOT_FIELD}' data-prev-value='{$FIELD_MODEL->get('fieldvalue')}' />
									</span>
									{assign var=FIELD_MODEL value=$FIELD_MODEL_TMP}
									{assign var=FIELD_NAME value=$FIELD_NAME_TMP}
								{/if}
								
								{* original *}
								<span class="hide edit span10">{* ED141010 : add RECORD_MODEL=$RECORD*}
									{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD_MODEL=$RECORD TITLE=vtranslate($FIELD_MODEL->get('label'),$MODULE_NAME)}
									{if $FIELD_MODEL->getFieldDataType() eq 'multipicklist'}
										<input type="hidden" class="fieldname" value='{$FIELD_NAME}[]' data-prev-value='{$FIELD_MODEL->getDisplayValue($FIELD_MODEL->get('fieldvalue'))}' />
									{else}
										<input type="hidden" class="fieldname" value='{$FIELD_NAME}' data-prev-value='{$FIELD_MODEL->get('fieldvalue')}' />
									{/if}
								</span>
								
								{* APRES *}
								{* lastname : isgroup, street2 *}
								{if $FIELD_NAME eq 'lastname'}
									{assign var=FIELD_NAME value='isgroup'}
									{assign var=FIELD_MODEL value=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS'][$FIELD_NAME]}
									{if $FIELD_MODEL}
									<span class="hide edit span10" title="Particulier / Structure">
										{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD_MODEL=$RECORD TITLE=vtranslate($FIELD_MODEL->get('label'),$MODULE_NAME)}
										<input type="hidden" class="fieldname" value='{$FIELD_NAME}' data-prev-value='{$FIELD_MODEL->get('fieldvalue')}' />
									</span>
									{/if}
									{if $RECORD->get('isgroup') neq '0' && $RECORD->get('mailingstreet2')}
										{assign var=FIELD_NAME value='mailingstreet2'}
										{assign var=FIELD_MODEL value=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS'][$FIELD_NAME]}
										{if $FIELD_MODEL}
										<span class="hide edit span10" title="Nom du groupe">
											{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD_MODEL=$RECORD TITLE=vtranslate($FIELD_MODEL->get('label'),$MODULE_NAME)}
											<input type="hidden" class="fieldname" value='{$FIELD_NAME}' data-prev-value='{$FIELD_MODEL->get('fieldvalue')}' />
										</span>
										{/if}
									{/if}
								{* pays *}
								{elseif $FIELD_NAME eq 'mailingcity'}
									{assign var=FIELD_NAME value='mailingcountry'}
									{assign var=FIELD_MODEL value=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS'][$FIELD_NAME]}
									<span class="hide edit span10" title="Pays">{* ED141010 : add RECORD_MODEL=$RECORD*}
										{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD_MODEL=$RECORD TITLE=vtranslate($FIELD_MODEL->get('label'),$MODULE_NAME)}
										<input type="hidden" class="fieldname" value='{$FIELD_NAME}' data-prev-value='{$FIELD_MODEL->get('fieldvalue')}' />
									</span>
								
								{* email, phone, mobile, homephone *}
								{elseif $FIELD_NAME eq 'email'}
									{assign var=FIELD_NAME value='phone'}
									{assign var=FIELD_MODEL value=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS'][$FIELD_NAME]}
									<span class="hide edit span10">{* ED141010 : add RECORD_MODEL=$RECORD*}
										{assign var=FIELD_MODEL value=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS'][$FIELD_NAME]}
										{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD_MODEL=$RECORD TITLE=vtranslate($FIELD_MODEL->get('label'),$MODULE_NAME)}
										<input type="hidden" class="fieldname" value='{$FIELD_NAME}' data-prev-value='{$FIELD_MODEL->get('fieldvalue')}' />
									</span>
									{assign var=FIELD_NAME value='mobile'}
									{assign var=FIELD_MODEL value=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS'][$FIELD_NAME]}
									<span class="hide edit span10">{* ED141010 : add RECORD_MODEL=$RECORD*}
										{assign var=FIELD_MODEL value=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS'][$FIELD_NAME]}
										{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD_MODEL=$RECORD TITLE=vtranslate($FIELD_MODEL->get('label'),$MODULE_NAME)}
										<input type="hidden" class="fieldname" value='{$FIELD_NAME}' data-prev-value='{$FIELD_MODEL->get('fieldvalue')}' />
									</span>
									{assign var=FIELD_NAME value='homephone'}
									{assign var=FIELD_MODEL value=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS'][$FIELD_NAME]}
									<span class="hide edit span10">{* ED141010 : add RECORD_MODEL=$RECORD*}
										{assign var=FIELD_MODEL value=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS'][$FIELD_NAME]}
										{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD_MODEL=$RECORD TITLE=vtranslate($FIELD_MODEL->get('label'),$MODULE_NAME)}
										<input type="hidden" class="fieldname" value='{$FIELD_NAME}' data-prev-value='{$FIELD_MODEL->get('fieldvalue')}' />
									</span>
								{* type de contact -> origin *}
								{elseif $FIELD_NAME == 'contacttype'}
									{assign var=FIELD_NAME value='leadsource'}
									{assign var=FIELD_MODEL value=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS'][$FIELD_NAME]}
									<span class="hide edit span10">{* ED141010 : add RECORD_MODEL=$RECORD*}
										{assign var=FIELD_MODEL value=$SUMMARY_RECORD_STRUCTURE['SUMMARY_FIELDS'][$FIELD_NAME]}
										{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD_MODEL=$RECORD TITLE=vtranslate($FIELD_MODEL->get('label'),$MODULE_NAME)}
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
{include file=vtemplate_path('SummaryViewContentsFooter.tpl',$MODULE_NAME)}
{/strip}