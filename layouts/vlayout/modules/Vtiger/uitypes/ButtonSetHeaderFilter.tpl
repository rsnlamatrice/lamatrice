{*<!--
/*********************************************************************************
  ** ED150413
  *
 ********************************************************************************/
-->*}
{strip}
{if !isset($FIELD_MODEL)}
	{if isset($LISTVIEW_HEADER)}{assign var=FIELD_MODEL value=$LISTVIEW_HEADER}
	{elseif isset($HEADER_FIELD)}{assign var=FIELD_MODEL value=$HEADER_FIELD}{/if}
{/if}
{if !isset($INPUT_CLASS)}
    {assign var="INPUT_CLASS" value='input-small'}
{/if}
{assign var=PICKLIST_VALUES value=$FIELD_MODEL->getPicklistValues()}
{assign var="SPECIAL_VALIDATOR" value=$FIELD_MODEL->getValidator()}
{assign var="FIELD_VALUE_LIST" value=explode(' |##| ',$FIELD_MODEL->get('fieldvalue'))}{*sic*}
{if !$RECORD_MODEL}
	{if $RELATED_RECORD}{assign var=RECORD_MODEL value=$RELATED_RECORD}
	{elseif $RECORD}{assign var=RECORD_MODEL value=$RECORD}
	{else !$RECORD_MODEL}RECORD_MODEL manquant{/if}
{/if}
{assign var=PICKLIST_LABELS value=$RECORD_MODEL->getPicklistValuesDetails($FIELD_MODEL->getFieldName())}
<select id="{$MODULE}_{$smarty.request.view}_headerFilter_fieldName_{$FIELD_MODEL->get('name')}"
        class="{$INPUT_CLASS}" 
        data-field-type="{$FIELD_MODEL->getFieldDataType()}"
        data-field-name="{$FIELD_MODEL->getFieldName()}"
        data-operator="="
        >
        <option value=" "></option>
        <option value="">(tous)</option>
	{foreach item=PICKLIST_ITEM key=PICKLIST_KEY from=$PICKLIST_LABELS}
	    {if is_array($PICKLIST_ITEM)}
		{assign var=PICKLIST_LABEL value=$PICKLIST_ITEM['label']}
		{if !$PICKLIST_LABEL}
		    {assign var=PICKLIST_LABEL value=$PICKLIST_KEY}
		{/if}
	    {else}
		{assign var=PICKLIST_LABEL value=$PICKLIST_ITEM}
	    {/if}
            <option value="{Vtiger_Util_Helper::toSafeHTML($PICKLIST_KEY)}"
                {if in_array(Vtiger_Util_Helper::toSafeHTML($PICKLIST_KEY), $FIELD_VALUE_LIST)} selected {/if}>{vtranslate($PICKLIST_LABEL, $MODULE)}
            </option>
        {/foreach}
</select>
{/strip}