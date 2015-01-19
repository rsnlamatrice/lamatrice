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
 /* ED141010
 */
-->*}
{strip}
{assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($FIELD_MODEL->getFieldInfo()))}
{assign var=FOLDER_VALUES value=$FIELD_MODEL->getDocumentFolders()}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
{foreach item=FOLDER_INFO key=FOLDER_VALUE from=$FOLDER_VALUES}
	{if $FIELD_MODEL->get('fieldvalue') eq $FOLDER_VALUE}
            {assign var=UICOLOR value=$FOLDER_INFO['uicolor']}
            {break}
        {/if}
{/foreach}
<select class="chzn-select" name="{$FIELD_MODEL->getFieldName()}"
        data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
        data-fieldinfo='{$FIELD_INFO}' {if !empty($SPECIAL_VALIDATOR)}data-validator={Zend_Json::encode($SPECIAL_VALIDATOR)}{/if}
        {if $UICOLOR} style="background-color: {$UICOLOR}"{/if}
        >
{foreach item=FOLDER_INFO key=FOLDER_VALUE from=$FOLDER_VALUES}
	<option value="{$FOLDER_VALUE}" {if $FIELD_MODEL->get('fieldvalue') eq $FOLDER_VALUE} selected {/if}
        style="background-color: {$FOLDER_INFO['uicolor']}">{$FOLDER_INFO['name']}
        <span style="background-color: {$FOLDER_INFO['uicolor']}; display:inline-block; width: 16px; height: 16px; margin-left: 4px;">&nbsp;</span></option>
{/foreach}
</select>
{/strip}