{assign var="MODULELIST" value=Vtiger_Moduleslist_UIType::getListOfModules()}
<select class="chzn-select" id="{$MODULE}_editView_fieldName_{$FIELD_MODEL->get('name')}" name="{$FIELD_MODEL->get('name')}">
	<optgroup>
		{foreach key=RELATED_MODULE_KEY item=RELATED_MODULE from=$MODULELIST}
			<option value="{trim($RELATED_MODULE_KEY)}" {if trim(decode_html($FIELD_MODEL->get('fieldvalue'))) eq trim($RELATED_MODULE_KEY)} selected {/if}>{trim(vtranslate($RELATED_MODULE_KEY,$RELATED_MODULE_KEY))}</option>
		{/foreach}
	</optgroup>
</select>