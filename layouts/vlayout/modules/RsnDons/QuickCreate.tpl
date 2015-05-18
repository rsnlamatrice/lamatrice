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
 /***
 * Hérité par RsnAbonnements et RsnAdhesions
 */
-->*}
{strip}
{foreach key=index item=jsModel from=$SCRIPTS}
	<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
{/foreach}
		
<div class="modelContainer">
<div class="modal-header contentsBackground">
	<button class="close" aria-hidden="true" data-dismiss="modal" type="button" title="{vtranslate('LBL_CLOSE')}">x</button>
    <h3>{vtranslate('LBL_QUICK_CREATE', $MODULE)} {vtranslate($SINGLE_MODULE, $MODULE)}</h3>
</div>
{assign var=FORMID value=uniqid('form')}
<form id="{$FORMID}" class="form-horizontal recordEditView" name="QuickCreate" method="post" action="index.php">
	{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
		<input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
	{/if}
	<input type="hidden" name="module" value="{$MODULE}">
	<input type="hidden" name="action" value="SaveAjax">
	<div class="quickCreateContent">
		<div class="modal-body">
			<table class="massEditTable table table-bordered">
				<tr>
				{assign var=COUNTER value=0}
				{foreach key=FIELD_NAME item=FIELD_MODEL from=$RECORD_STRUCTURE name=blockfields}
					{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
					{assign var="refrenceList" value=$FIELD_MODEL->getReferenceList()}
					{assign var="refrenceListCount" value=count($refrenceList)}
					{if $FIELD_MODEL->get('uitype') eq "19"}
					    {if $COUNTER eq '1'}
						<td></td><td></td></tr><tr>
						{assign var=COUNTER value=0}
					    {/if}
					{/if}
					{if $COUNTER eq 2}
						</tr><tr>
						{assign var=COUNTER value=1}
					{else}
						{assign var=COUNTER value=$COUNTER+1}
					{/if}
					<td class='fieldLabel'>
						{if $isReferenceField neq "reference"}<label class="muted pull-right">{/if}
						{if $FIELD_MODEL->isMandatory() eq true && $isReferenceField neq "reference" && $FIELD_NAME neq 'assigned_user_id'} <span class="redColor">*</span> {/if}
						{if $isReferenceField eq "reference"}
							{if $refrenceListCount > 1}
								{assign var="DISPLAYID" value=$FIELD_MODEL->get('fieldvalue')}
								{assign var="REFERENCED_MODULE_STRUCT" value=$FIELD_MODEL->getUITypeModel()->getReferenceModule($DISPLAYID)}
								{if !empty($REFERENCED_MODULE_STRUCT)}
									{assign var="REFERENCED_MODULE_NAME" value=$REFERENCED_MODULE_STRUCT->get('name')}
								{/if}
								<span class="pull-right">
									{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}
									<select style="width: 150px;" class="chzn-select referenceModulesList" id="referenceModulesList">
										<optgroup>
											{foreach key=index item=value from=$refrenceList}
												<option value="{$value}" {if $value eq $REFERENCED_MODULE_NAME} selected {/if} >{vtranslate($value, $value)}</option>
											{/foreach}
										</optgroup>
									</select>
								</span>		
							{else}
								<label class="muted pull-right">{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}{vtranslate($FIELD_MODEL->get('label'), $MODULE)}</label>
							{/if}
						{elseif $FIELD_NAME eq 'assigned_user_id'}
						{else}
							{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
						{/if}
					{if $isReferenceField neq "reference"}</label>{/if}
					</td>
					<td class="fieldValue" {if $FIELD_MODEL->get('uitype') eq '19'} colspan="3" {assign var=COUNTER value=$COUNTER+1} {/if}>
						{if $FIELD_NAME eq 'serviceid'}
							{include file='modules/Vtiger/uitypes/Picklist.tpl' RECORD_MODEL=$RECORD_MODEL PICKLIST_VALUES=$SERVICES_LIST PICKLIST_ADD_ATTR='unit_price'}{* ED141024 *}
						{elseif $FIELD_NAME eq 'campaign_no'}
							{include file='modules/Vtiger/uitypes/Picklist.tpl' RECORD_MODEL=$RECORD_MODEL PICKLIST_VALUES=$CAMPAIGNS_LIST}{* ED141024 *}
						{elseif $FIELD_NAME eq 'notesid'}
							{include file='modules/Vtiger/uitypes/Picklist.tpl' RECORD_MODEL=$RECORD_MODEL PICKLIST_VALUES=$COUPONS_LIST PICKLIST_ADD_ATTR='campaignid'}{* ED141024 *}
						{elseif $FIELD_NAME eq 'assigned_user_id'}
							<span class="hide">{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) RECORD_MODEL=$RECORD_MODEL}{* ED141010 *}</span>
						{else}
							{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) RECORD_MODEL=$RECORD_MODEL}{* ED141010 *}
						{/if}
					</td>
				{/foreach}
				</tr>
			</table>
		</div>
	</div>
	<div class="modal-footer quickCreateActions">
		{assign var="EDIT_VIEW_URL" value=$MODULE_MODEL->getCreateRecordUrl()}
			<a class="cancelLink cancelLinkContainer pull-right" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
			<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
			<!--button class="btn" id="goToFullForm" data-edit-view-url="{$EDIT_VIEW_URL}" type="button"><strong>{vtranslate('LBL_GO_TO_FULL_FORM', $MODULE)}</strong></button-->
	</div>
	<script>{* La sélection d'un coupn permet l'affectation de la campagne correspondante *}
	$().ready(function(){
		{* Listes dependantes d'un coupon vers une campagne *}
		$(document.body)
		.on('change', '#{$FORMID} select[name="notesid"]', function(){
			var campaignid = ($(this).children('[selected][campaignid]').attr('campaignid'));
			if (campaignid) {
				var $dest = $(this).parents('form:first').find('select[name="campaign_no"]');
				if ($dest.length == 0) {
					alert('Campagne introuvable');
				}
				else {
					$seloption = $dest.children('option[value="' + campaignid + '"]:first');
					if ($seloption.length) {
						$dest.val(campaignid);
						$seloption.attr('selected', 'selected');
						$dest.select2("val",campaignid); {* ne fonctionne pas bien *}
						$dest.next().find('> a > span:first').html($seloption.html());
					}
					else {
						alert('Campagne introuvable');
					}
				}
			}
		})
		{* Lors de la selection d'un service, affecte le prix *}
		.on('change', '#{$FORMID} select[name="serviceid"]', function(){
			var price = ($(this).children('[selected]').attr('unit_price'));
			if (!isNaN(price)) {
				var $dest = $(this).parents('form:first').find(':input[name="montant"]');
				if ($dest.length == 0) {
					alert('Montant introuvable');
				}
				else if($dest.attr('set-from-dependent') != 'forbidden'){
					$dest.val(parseFloat(price).toFixed(2));
					$dest.attr('set-from-dependent', 'serviceid|unit_price');
				}
			}
		})
		{* Lors de la saisie du prix, interdit l'affectation automatique *}
		.on('change', '#{$FORMID} input[name="montant"]', function(){
			$(this).attr('set-from-dependent','forbidden');
		})
		;
	})</script>
</form>
</div>
{/strip}