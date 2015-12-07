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
  * passage du paramètre LABELS contenant un tableau [value=name]
 ********************************************************************************/
-->*}
{strip}
{assign var="FIELD_INFO" value=Zend_Json::encode($FIELD_MODEL->getFieldInfo())}
{assign var=PICKLIST_LABELS value=$FIELD_MODEL->getPicklistValues()}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
{assign var=FIELD_NAME value=$FIELD_MODEL->getFieldName()}
{assign var=SELECTED_VALUE value=$FIELD_MODEL->get('fieldvalue')}
{assign var=UID value=uniqid('btnset')}
<div id="{$UID}" class="{if $OCCUPY_COMPLETE_WIDTH} row-fluid {/if}">
    {*if $FIELD_MODEL->isEmptyPicklistOptionAllowed()}<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>{/if*}
    {foreach item=PICKLIST_LABEL key=PICKLIST_KEY from=$PICKLIST_LABELS}
        {if $LABELS && ($PICKLIST_KEY eq $PICKLIST_LABEL)}
                {if !empty($LABELS[$PICKLIST_KEY])}
                    {if is_array($LABELS[$PICKLIST_KEY])}
                        {assign var=PICKLIST_LABEL value=$LABELS[$PICKLIST_KEY]['label']}
                        {assign var=PICKLIST_CLASS value=$LABELS[$PICKLIST_KEY]['class']}
                        {assign var=PICKLIST_ICON value=$LABELS[$PICKLIST_KEY]['icon']}
                    {else}
                        {assign var=PICKLIST_LABEL value=$LABELS[$PICKLIST_LABEL]}
                    {/if}
                {/if}
        {/if}
        <input type="radio" 
                name="{$FIELD_NAME}"
                id="{$UID}{$PICKLIST_KEY}" 
                data-validation-engine="validate[{if $FIELD_MODEL->isMandatory() eq true} required,{/if}funcCall[Vtiger_Base_Validator_Js.invokeValidation]]"
                data-fieldinfo='{$FIELD_INFO|escape}' {if !empty($SPECIAL_VALIDATOR)}data-validator='{Zend_Json::encode($SPECIAL_VALIDATOR)}'{/if}
                data-selected-value='{$SELECTED_VALUE}'
                value="{$PICKLIST_KEY}"
        {if trim(decode_html($SELECTED_VALUE)) eq trim($PICKLIST_KEY)}
                checked="checked"
        {/if}
		{if $FIELD_MODEL->isReadOnly() || !$FIELD_MODEL->isEditable()} 
				disabled="disabled"
		{/if}
        /><label for="{$UID}{$PICKLIST_KEY}" class="ui-buttonset {$PICKLIST_CLASS}">
            {if $PICKLIST_ICON}<span class="{$PICKLIST_ICON}"></span>&nbsp;{/if}
            {$PICKLIST_LABEL}</label>
    {/foreach}
    <script>$(document.body).ready(function(){ $('#{$UID}').buttonset(); });</script>
</div>
{/strip}