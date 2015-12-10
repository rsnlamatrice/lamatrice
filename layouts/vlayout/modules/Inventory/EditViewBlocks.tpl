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
<div class='editViewContainer container-fluid'>
	{assign var=FORMID value='EditView'}
	<form id="{$FORMID}" class="form-horizontal recordEditView" name="EditView" method="post" action="index.php" enctype="multipart/form-data">
		{assign var=WIDTHTYPE value=$USER_MODEL->get('rowheight')}
		{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
			<input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
		{/if}
		<input type="hidden" name="module" value="{$MODULE}" />
		<input type="hidden" name="action" value="Save" />
		<input type="hidden" name="record" value="{$RECORD_ID}" />
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
			{* ED150629 PurchaseOrder *}
			{if isset($POTYPE_FIELD_MODEL)}
				<input type="hidden" name="potype" value="{$POTYPE_FIELD_MODEL->get('fieldvalue')}"/>
				{assign var=POTYPE_LABEL value=vtranslate('LBL_POTYPE_'|cat:$POTYPE_FIELD_MODEL->get('fieldvalue'), $MODULE)}
				{if $RECORD_ID neq ''}
					<h3 title="{vtranslate('LBL_EDITING', $MODULE)} {$POTYPE_LABEL} {$RECORD_STRUCTURE_MODEL->getRecordName()}">{vtranslate('LBL_EDITING', $MODULE)} {$POTYPE_LABEL} - {$RECORD_STRUCTURE_MODEL->getRecordName()}
				{else}
					<h3>{vtranslate('LBL_CREATING_NEW', $MODULE)} {$POTYPE_LABEL}
				{/if}
				<span class="span2">
					{assign var=FIELD_MODEL value=$POTYPE_FIELD_MODEL}
					{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getDetailViewTemplateName(),$MODULE) FIELD_MODEL=$FIELD_MODEL USER_MODEL=$USER_MODEL MODULE=$MODULE RECORD=$RECORD RECORD_MODEL=$RECORD}
				</span>
			{elseif $RECORD_ID neq ''}
				<h3>{vtranslate('LBL_EDITING', $MODULE)}&nbsp;
				{if $RECORD->get('typedossier') === 'Avoir'
				|| $RECORD->get('typedossier') === 'Remboursement'}
					<b>{vtranslate($RECORD->get('typedossier'), $MODULE )}</b>
				{else}
					{vtranslate($SINGLE_MODULE_NAME, $MODULE)}
				{/if}
				&nbsp;- {$RECORD_STRUCTURE_MODEL->getRecordName()}
			{else}
				<h3>{vtranslate('LBL_CREATING_NEW', $MODULE)}&nbsp;
				{if $RECORD->get('typedossier') === 'Avoir'
				|| $RECORD->get('typedossier') === 'Remboursement'}
					{vtranslate($RECORD->get('typedossier'), $MODULE )}
				{else}
					{vtranslate($SINGLE_MODULE_NAME, $MODULE)}
				{/if}
			{/if}
			{if $RECORD->get('invoicestatus') === 'Cancelled'}
				<br><span style="color: red;">{vtranslate($RECORD->get('invoicestatus'), $MODULE )}</span>
			{/if}
			{if $RECORD->get('sent2compta')}
				<br><span style="color: red;">{vtranslate('LBL_ALREADY_SENT_2_COMPTA')}</span>
			{/if}
			</h3>
			<span class="pull-right" style="padding-left: 12px;">
				{if !$NOT_EDITABLE}
					<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
				{elseif !is_numeric($NOT_EDITABLE)}
					<i>{vtranslate($NOT_EDITABLE, $MODULE)}</i>
				{/if}
				<a class="cancelLink" type="reset" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL', $MODULE)}</a>
			</span>
			<hr>
		</div>
		{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name="EditViewBlockLevelLoop"}
			{if $BLOCK_FIELDS|@count lte 0}{continue}{/if}
			{if $BLOCK_LABEL eq 'LBL_RSNREGLEMENTS'}
				{assign var=REGLEMENTS_BLOCK_FIELDS value=$BLOCK_FIELDS}
				{continue}
			{/if}
			{assign var=IS_HIDDEN value=($BLOCK_LABEL eq 'LBL_ADDRESS_INFORMATION' || $BLOCK_LABEL eq 'LBL_DESCRIPTION_INFORMATION' || $BLOCK_LABEL eq 'LBL_CUSTOM_INFORMATION')}
			<table class="table table-bordered blockContainer showInlineTable">
			<thead><tr>
				<th class="blockHeader" colspan="4">
					{if $IS_HIDDEN}
						<img class="cursorPointer alignMiddle blockToggle {if !($IS_HIDDEN)} hide {/if} "  src="{vimage_path('arrowRight.png')}" data-mode="hide"}>
						<img class="cursorPointer alignMiddle blockToggle {if ($IS_HIDDEN)} hide {/if}"  src="{vimage_path('arrowDown.png')}" data-mode="show"}>
						&nbsp;&nbsp;
					{/if}
					{vtranslate($BLOCK_LABEL, $MODULE)}</th>
			</tr></thead>
			<tbody {if $IS_HIDDEN} class="hide" {/if}>
			{if ($BLOCK_LABEL eq 'LBL_ADDRESS_INFORMATION') and ($MODULE neq 'PurchaseOrder') }
				<tr>
				<td class="fieldLabel {$WIDTHTYPE}" name="copyHeader1">
					<label class="muted pull-right marginRight10px" name="togglingHeader">{vtranslate('LBL_BILLING_ADDRESS_FROM', $MODULE)}</label>
				</td>
				<td class="fieldValue {$WIDTHTYPE}" name="copyAddress1">
					<div class="row-fluid">
						<div class="span5">
							{*ED150707 <span class="row-fluid margin0px">
								<label class="radio">
								  <input type="radio" name="copyAddressFromRight" class="accountAddress" data-copy-address="billing" checked="">{vtranslate('SINGLE_Accounts', $MODULE)}
								</label>
							</span>*}
							<span class="row-fluid margin0px">
								<label class="radio">
								  <input type="radio" name="copyAddressFromRight" class="contactAddress" data-copy-address="billing" checked="">{vtranslate('SINGLE_Contacts', $MODULE)}
								</label>
							</span>
							<span class="row-fluid margin0px" name="togglingAddressContainerRight">
								<label class="radio">
							  <input type="radio" name="copyAddressFromRight" class="shippingAddress" data-target="shipping" checked="">{vtranslate('Shipping Address', $MODULE)}
								</label>
							</span>
							<span class="row-fluid margin0px hide" name="togglingAddressContainerLeft">
								<label class="radio">
							  <input type="radio" name="copyAddressFromRight"  class="billingAddress" data-target="billing" checked="">{vtranslate('Billing Address', $MODULE)}
								</label>
							</span>
						</div>
					</div>
				</td>
				<td class="fieldLabel {$WIDTHTYPE}" name="copyHeader2">
					<label class="muted pull-right marginRight10px" name="togglingHeader">{vtranslate('LBL_SHIPPING_ADDRESS_FROM', $MODULE)}</label>
				</td>
				<td class="fieldValue {$WIDTHTYPE}" name="copyAddress2">
					<div class="row-fluid">
						<div class="span5">
							{*ED150707 <span class="row-fluid margin0px">
								<label class="radio">
								  <input type="radio" name="copyAddressFromLeft" class="accountAddress" data-copy-address="shipping" checked="">{vtranslate('SINGLE_Accounts', $MODULE)}
								</label>
							</span>*}
							<span class="row-fluid margin0px">
								<label class="radio">
								  <input type="radio" name="copyAddressFromLeft" class="contactAddress" data-copy-address="shipping" checked="">{vtranslate('SINGLE_Contacts', $MODULE)}
								</label>
							</span>
							<span class="row-fluid margin0px" name="togglingAddressContainerLeft">
								<label class="radio">
							  <input type="radio" name="copyAddressFromLeft" class="billingAddress" data-target="billing" checked="">{vtranslate('Billing Address', $MODULE)}
								</label>
							</span>
							<span class="row-fluid margin0px hide" name="togglingAddressContainerRight">
								<label class="radio">
							  <input type="radio" name="copyAddressFromLeft" class="shippingAddress" data-target="shipping" checked="">{vtranslate('Shipping Address', $MODULE)}
								</label>
							</span>
						</div>
					</div>
				</td>
			</tr>
			{/if}
			<tr>
			{assign var=COUNTER value=0}
			{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
				{if $FIELD_NAME eq 'accountdiscounttype'}{* already included in LineItemsEdit.tpl *}
					{continue}
				{* ED150629 PurchaseOrder : sent2compta only for invoices *}
				{elseif $FIELD_NAME eq 'sent2compta' && isset($POTYPE_FIELD_MODEL) && $POTYPE_FIELD_MODEL->get('fieldvalue') neq 'invoice'}
						{continue}
				{elseif $MODULE neq 'PurchaseOrder' && $FIELD_NAME eq 'account_id'}
						{continue}
				{/if}
				{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
				{if $FIELD_MODEL->get('uitype') eq "20" or $FIELD_MODEL->get('uitype') eq "19"}
					{if $COUNTER eq '1'}
						<td class="{$WIDTHTYPE}"></td><td class="{$WIDTH_TYPE_CLASSSES[$WIDTHTYPE]}"></td></tr><tr>
						{assign var=COUNTER value=0}
					{/if}
				{/if}
				{if $COUNTER eq 2}
					</tr><tr>
					{assign var=COUNTER value=1}
				{else}
					{assign var=COUNTER value=$COUNTER+1}
				{/if}
				<td class="fieldLabel {$WIDTHTYPE}">
					{if $isReferenceField neq "reference"}<label class="muted pull-right marginRight10px">{/if}
					{if $FIELD_MODEL->isMandatory() eq true && $isReferenceField neq "reference"} <span class="redColor">*</span> {/if}
					{if $MODULE neq 'PurchaseOrder' && $FIELD_NAME eq 'contact_id'}
						{* ED150707 hide account input *}
						{assign var=ACCOUNT_FIELD value=$BLOCK_FIELDS['account_id']}
						<input type="hidden" name="{$ACCOUNT_FIELD->get('name')}" value="{$ACCOUNT_FIELD->get('fieldvalue')}"/>
					{/if}
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
								<select class="chzn-select referenceModulesList streched" style="width:140px;">
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
						{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) COUNTER=$COUNTER}
					{else}
						{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
					{/if}
					{if $isReferenceField neq "reference"}</label>{/if}
				</td>
				{if $FIELD_MODEL->get('uitype') neq "83"}
					<td class="fieldValue {$WIDTHTYPE}" {if $FIELD_MODEL->get('uitype') eq '19'} colspan="3" {assign var=COUNTER value=$COUNTER+1} {/if} {if $FIELD_MODEL->get('uitype') eq '20'} colspan="3"{/if}>
						{* empêche l'enregistrement du type
						ED151026
						if $FIELD_MODEL->getName() === 'typedossier' && ($RECORD->get('typedossier') === 'Avoir' || $RECORD->get('typedossier') === 'Remboursement')}
							{if $FIELD_MODEL->set('disabled', true)}{/if}
						{/if*}
						{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) BLOCK_FIELDS=$BLOCK_FIELDS}
						{if $FIELD_MODEL->get('name') eq 'contact_id'}
							<span class="accounttype {if !$ACCOUNTTYPE}hide{/if}">{$ACCOUNTTYPE}</span>
						{/if}
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