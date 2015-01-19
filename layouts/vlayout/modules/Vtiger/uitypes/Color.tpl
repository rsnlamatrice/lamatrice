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
  * copy of \layouts\vlayout\modules\Vtiger\uitypes\ColorPicker.tpl
 */
-->*}
{strip}
{assign var="FIELD_INFO" value=Vtiger_Util_Helper::toSafeHTML(Zend_Json::encode($FIELD_MODEL->getFieldInfo()))}
{assign var="FIELD_NAME" value=$FIELD_MODEL->get('name')}
{assign var="VALUE" value=$FIELD_MODEL->get('fieldvalue')}
{assign var="INPUT_ID" value="`$MODULE`_editView_fieldName_`$FIELD_NAME`"} 
<div id="{$INPUT_ID}-colorSelector" class="colorpicker-holder" {if !$FIELD_MODEL->isReadOnly()}title="2 clics pour editer"{/if}><div style="background-color: {$VALUE}"></div></div>
{/strip}