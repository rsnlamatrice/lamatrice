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
-->*}{*
ED141024
		argument PICKLIST_VALUES may be passed
		PICKLIST_VALUE may be an array. Then, gets 'label' property.
*}
{strip}
{assign var="FIELD_INFO" value=Zend_Json::encode($FIELD_MODEL->getFieldInfo())}
{if !isset($PICKLIST_VALUES)}
{assign var=PICKLIST_DATA value=array()}
{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues($PICKLIST_DATA)}
{/if}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
{* AV150415 : add uiclass *}
<select class="chzn-select {if $OCCUPY_COMPLETE_WIDTH} row-fluid {/if} {$FIELD_MODEL->get('uiclass')}" name="{$FIELD_MODEL->getFieldName()}"
	data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
	data-fieldinfo='{$FIELD_INFO|escape}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}
	data-selected-value='{$FIELD_MODEL->get('fieldvalue')}'
	{if $FIELD_MODEL->get('disabled')} disabled="disabled"{/if}
>
		{if $FIELD_MODEL->isEmptyPicklistOptionAllowed()}<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>{/if}
		{foreach item=PICKLIST_ITEM key=PICKLIST_NAME from=$PICKLIST_VALUES}
				{if is_array($PICKLIST_ITEM)}{assign var=PICKLIST_VALUE value=$PICKLIST_ITEM['label']}
				{else}{assign var=PICKLIST_VALUE value=$PICKLIST_ITEM}{/if}
				<option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_NAME)}" {if trim(decode_html($FIELD_MODEL->get('fieldvalue'))) eq trim($PICKLIST_NAME)} selected {/if}
				{* ED141110 ajout d un attribut personnalisé *}
				{if $PICKLIST_ADD_ATTR} {$PICKLIST_ADD_ATTR}="{$PICKLIST_ITEM[$PICKLIST_ADD_ATTR]}"{/if}>{$PICKLIST_VALUE}</option>
		{/foreach}
</select>
{/strip}