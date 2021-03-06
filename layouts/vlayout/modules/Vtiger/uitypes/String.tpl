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
-->
		ED150102
		possibilité de passer INPUT_CLASS
				valeur par dÈfaut : 'input-large'
		possibilité de passer TITLE
		
		ED151204 || !$FIELD_MODEL->isEditable()
		*}
{strip}
{assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($FIELD_MODEL->getFieldInfo()))}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
{*ED150526*}
{if isset($FORCE_FIELD_NAME)}
		{assign var="FIELD_NAME" value=$FORCE_FIELD_NAME}
{else}
		{assign var="FIELD_NAME" value=$FIELD_MODEL->get('name')}
{/if}
{if !isset($INPUT_CLASS)}
		{assign var="INPUT_CLASS" value='input-large'}
{/if}
{* AV150415 : add uiclass *}
<input id="{$MODULE}_editView_fieldName_{$FIELD_NAME}" type="text" 
	   class="{$INPUT_CLASS} {if $FIELD_MODEL->isNameField()}nameField{/if} {$FIELD_MODEL->get('uiclass')} ui-fieldid-{$FIELD_MODEL->get('id')}" 
	   data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true}required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" 
	   name="{$FIELD_NAME}{*$FIELD_MODEL->getFieldName()*}" 
	   value="{$FIELD_MODEL->get('fieldvalue')}"
		{if ($FIELD_MODEL->get('uitype') eq '106' && $MODE neq '') || $FIELD_MODEL->get('uitype') eq '3' 
		|| $FIELD_MODEL->get('uitype') eq '4'
		|| $FIELD_MODEL->isReadOnly()
		|| !$FIELD_MODEL->isEditable()} 
				readonly 
		{/if}
		{if isset($TITLE)} title="{$TITLE}" placeholder="{$TITLE}"{/if}
data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator={Zend_Json::encode($SPECIAL_VALIDATOR)}{/if} />
{* TODO - Handler Ticker Symbol field  ($FIELD_MODEL->get('uitype') eq '106' && $MODE eq 'edit') ||*}
{/strip}