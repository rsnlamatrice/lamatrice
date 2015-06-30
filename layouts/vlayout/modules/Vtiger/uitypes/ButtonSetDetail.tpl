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
  * ou retour de tableau par RECORD_MODEL->getPicklistValuesDetails($FIELD_NAME)
 ********************************************************************************/
-->*}
{strip}
{assign var="FIELD_INFO" value=Zend_Json::encode($FIELD_MODEL->getFieldInfo())}
{assign var=FIELD_NAME value=$FIELD_MODEL->getFieldName()}
{assign var=FIELD_LABEL value=Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode(vtranslate($FIELD_MODEL->get('label'),$MODULE)))}
{if $LABELS}
    {assign var=PICKLIST_LABELS value=$LABELS}
{else}
    {if $RECORD}{assign var=RECORD_MODEL value=$RECORD}{/if}
    {if !$RECORD_MODEL}
	Erreur : RECORD_MODEL manquant
    {else}
	{assign var=PICKLIST_LABELS value=$RECORD_MODEL->getPicklistValuesDetails($FIELD_NAME)}
    {/if}
{/if}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
{assign var=SELECTED_VALUE value=$FIELD_MODEL->get('fieldvalue')}
{assign var=UID value=uniqid('btnset')}
<div id="{$UID}" class="{if $OCCUPY_COMPLETE_WIDTH} row-fluid {/if}">
    {*if $FIELD_MODEL->isEmptyPicklistOptionAllowed()}<option value="">{vtranslate('LBL_SELECT_OPTION','Vtiger')}</option>{/if*}
    {foreach item=PICKLIST_ITEM key=PICKLIST_KEY from=$PICKLIST_LABELS}
        {if trim(decode_html($SELECTED_VALUE)) eq trim($PICKLIST_KEY)}
	    {if is_array($PICKLIST_ITEM)}
		{assign var=PICKLIST_LABEL value=$PICKLIST_ITEM['label']}
		{if isset($PICKLIST_ITEM['class'])}
		    {assign var=PICKLIST_CLASS value=$PICKLIST_ITEM['class']}
		{else}
		    {assign var=PICKLIST_CLASS value=''}
		{/if}
		{assign var=PICKLIST_ICON value=$PICKLIST_ITEM['icon']}
		{assign var=PICKLIST_TITLE value=$FIELD_LABEL|cat:' '|cat:$PICKLIST_ITEM['title']}
	    {else}
		{assign var=PICKLIST_LABEL value=$PICKLIST_ITEM}
	    {/if}
	    <label for="{$UID}{$PICKLIST_KEY}" class="ui-buttonset {$PICKLIST_CLASS}" title="{$PICKLIST_TITLE}">
		{if $PICKLIST_ICON}<span class="{$PICKLIST_ICON}"></span>&nbsp;{/if}
		{$PICKLIST_LABEL}</label>
	    {break}
	{/if}
    {/foreach}
</div>
{/strip}