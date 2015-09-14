<br>
<table width="100%" cellspacing="0" cellpadding="2">
	<tr>
		<td><strong>{'LBL_SELECT_'|cat:$RELATED_MODULE|cat:'_STEP'|@vtranslate:$MODULE}:</strong></td>
		<td class="big">{'LBL_SELECT_'|cat:$RELATED_MODULE|cat:'_STEP_DESCRIPTION'|@vtranslate:$MODULE}</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td colspan="3" style="padding-left: 1em;"> 
			<input type="hidden" class="validateconfiguration" value="validateRelatedRecordSelected" />
			<input type="hidden" class="onLoad" value="registerRelatedRecordSelectionEvent" />
			<input type="hidden" id="related_module" name="related_module" value="{$RELATED_MODULE}" />
			<input type="hidden" id="related_record_fieldname" name="related_record_fieldname" value="{$RELATED_FIELD_MODEL->getFieldName()}" />
			<input type="hidden" name="related_search_key" value="{$RELATED_SEARCH_KEY}" />
			<input type="hidden" name="related_search_value" value="{$RELATED_SEARCH_VALUE}" />
			
			{include file=vtemplate_path($RELATED_FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE) FIELD_MODEL=$RELATED_FIELD_MODEL}
		</td>
	</tr>
</table>