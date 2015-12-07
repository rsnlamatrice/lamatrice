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
 /* ED141009
  * copy of \layouts\vlayout\modules\Vtiger\uitypes\String.tpl
 */
-->*}
{strip}
{assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($FIELD_MODEL->getFieldInfo()))}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
{assign var="FIELD_NAME" value=$FIELD_MODEL->get('name')}
{assign var="VALUE" value=$FIELD_MODEL->get('fieldvalue')}
{assign var="INPUT_ID" value=uniqid('f')}{*"`$MODULE`_editView_fieldName_`$FIELD_NAME`"*} {*{$MODULE}_editView_fieldName_{$FIELD_NAME}*}
<input id="{$INPUT_ID}" type="hidden" 
	class="input-large {if $FIELD_MODEL->isNameField()}nameField{/if} colorField" 
	data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" 
	name="{$FIELD_MODEL->getFieldName()}" 
	value="{$VALUE}"
	{if $FIELD_MODEL->isReadOnly()} 
		readonly 
	{/if} 
data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator={Zend_Json::encode($SPECIAL_VALIDATOR)}{/if} />
<div id="{$INPUT_ID}-colorSelector" class="colorpicker-holder"><div style="background-color: {$VALUE}"></div></div>
{if !$FIELD_MODEL->isReadOnly() && $FIELD_MODEL->isEditable()}
<script>if ($('#{$INPUT_ID}-colorSelector').length == 0){* that happens with quick create modal form *}
	$(document).ready(function(){
		app.registerEventForColorPickerFields($('#{$INPUT_ID}-colorSelector'));
	});
</script>
{/if}
{/strip}