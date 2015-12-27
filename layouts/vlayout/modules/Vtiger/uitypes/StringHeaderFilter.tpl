{*<!--
/*********************************************************************************
   * ED150413
   * Header filter input
 ********************************************************************************/
-->
		ED150413
		possibilité de passer INPUT_CLASS
				valeur par défaut : 'input-large'
		possibilité de passer TITLE
		*}
{strip}
{if !isset($FIELD_MODEL)}{assign var=FIELD_MODEL value=$LISTVIEW_HEADER}{/if}
{if !isset($INPUT_CLASS)}
		{assign var="INPUT_CLASS" value='input-small'}
{/if}
<input id="{$MODULE}_headerFilter_fieldName_{$FIELD_MODEL->get('name')}" type="text" 
		class="{$INPUT_CLASS}" 
		data-field-type="{$FIELD_MODEL->getFieldDataType()}"
		data-field-name="{$FIELD_MODEL->getFieldName()}"
		value="{$FIELD_MODEL->getFilterOperatorDisplayValue()}{$FIELD_MODEL->get('fieldvalue')}"
		{if isset($TITLE)} title="{$TITLE}" placeholder="{$TITLE}"{/if}
		{if $HEADERFILTER_SEARCH_OPERATOR} data-operator="{$HEADERFILTER_SEARCH_OPERATOR}"{/if}
/>
{/strip}