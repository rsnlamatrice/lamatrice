{*<!--
/*********************************************************************************
  ** ED150413
  *
 ********************************************************************************/
-->*}
{strip}
{if !isset($FIELD_MODEL)}{assign var=FIELD_MODEL value=$LISTVIEW_HEADER}{/if}
{if !isset($INPUT_CLASS)}
    {assign var="INPUT_CLASS" value='input-mini'}
{/if}
{assign var="FIELD_VALUE" value=$FIELD_MODEL->get('fieldvalue')}
<select id="{$MODULE}_{$smarty.request.view}_headerFilter_fieldName_{$FIELD_MODEL->get('name')}"
        class="{$INPUT_CLASS}" 
        data-field-type="{$FIELD_MODEL->getFieldDataType()}"
        data-field-name="{$FIELD_MODEL->getFieldName()}"
        data-operator="c"
        >
        <option value=" "></option>
        <option value="">(tous)</option>
	<option value="yes" {if $FIELD_VALUE == 'yes'} selected {/if}>{vtranslate('yes')}</option>
	<option value="no" {if $FIELD_VALUE == 'no'} selected {/if}>{vtranslate('no')}</option>
</select>
{/strip}