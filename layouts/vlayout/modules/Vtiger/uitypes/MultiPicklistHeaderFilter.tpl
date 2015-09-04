{*<!--
/*********************************************************************************
  ** ED150413
  *
 ********************************************************************************/
-->*}
{strip}
{if !isset($FIELD_MODEL)}{assign var=FIELD_MODEL value=$LISTVIEW_HEADER}{/if}
{if !isset($INPUT_CLASS)}
    {assign var="INPUT_CLASS" value='input-small'}
{/if}
{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
{assign var="FIELD_VALUE_LIST" value=explode(' |##| ',$FIELD_MODEL->get('fieldvalue'))}
<select id="{$MODULE}_{$smarty.request.view}_headerFilter_fieldName_{$FIELD_MODEL->get('name')}"
        class="{$INPUT_CLASS}" 
        data-field-type="{$FIELD_MODEL->getFieldDataType()}"
        data-field-name="{$FIELD_MODEL->getFieldName()}"
        data-operator="c"
        >
        <option value=" "></option>
        <option value="">(tous)</option>
        {foreach key=PICKLIST_VALUE item=PICKLIST_LABEL from=$PICKLIST_VALUES}
            <option value="{$PICKLIST_VALUE}"
                {if in_array(Vtiger_Util_Helper::toSafeHTML($PICKLIST_VALUE), $FIELD_VALUE_LIST) || in_array($PICKLIST_VALUE, $FIELD_VALUE_LIST)} selected {/if}>{Vtiger_Util_Helper::toSafeHTML($PICKLIST_LABEL)}
            </option>
        {/foreach}
</select>
{/strip}