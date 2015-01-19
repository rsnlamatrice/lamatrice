{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
  * ED141005
  * picklist affiché en bouton radio
 ********************************************************************************/
-->*}
{strip}
{assign var="FIELD_INFO" value=Zend_Json::encode($FIELD_MODEL->getFieldInfo())}
{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
{assign var=FIELD_NAME value=$FIELD_MODEL->getFieldName()}
{assign var=SELECTED_VALUE value=$FIELD_MODEL->get('fieldvalue')}
<div class="{if $OCCUPY_COMPLETE_WIDTH} row-fluid {/if}">
		{*if $FIELD_MODEL->isEmptyPicklistOptionAllowed()}<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>{/if*}
	{foreach item=PICKLIST_VALUE key=PICKLIST_NAME from=$PICKLIST_VALUES}
	{if $LABELS && ($PICKLIST_NAME eq $PICKLIST_VALUE)}
		{if !empty($LABELS[$PICKLIST_VALUE])}
			{assign var=PICKLIST_NAME value=$LABELS[$PICKLIST_VALUE]}
		{/if}
	{/if}
	<label>
        <input type="radio" 
		name="{$FIELD_NAME}"
		data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
		data-fieldinfo='{$FIELD_INFO|escape}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}
		data-selected-value='{$SELECTED_VALUE}'
		value="{$PICKLIST_VALUE}"
	{if trim(decode_html($SELECTED_VALUE)) eq trim($PICKLIST_VALUE)}
		checked="checked"
	{/if}
	/>&nbsp;{$PICKLIST_NAME}
	</label>
    {/foreach}
</div>
{/strip}