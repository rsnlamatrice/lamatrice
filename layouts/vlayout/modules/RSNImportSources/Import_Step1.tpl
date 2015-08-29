<!-- TMP Take care of Data SOurce number: if none -> stop import here; else if one -> juste display data source instead of select list; else display select list -->
<!-- TMP When import source is selected, load using ajax the step one of this specific import ? -> disable next button util this is not loaded !! -->
<table width="100%" cellspacing="0" cellpadding="2">
	<tr>
		<td><strong style="white-space: nowrap;">{'LBL_IMPORT_STEP_1'|@vtranslate:$MODULE}:</strong></td>
		<td class="big">{'LBL_IMPORT_STEP_1_DESCRIPTION'|@vtranslate:$MODULE} :</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td data-import-upload-size="{$IMPORT_UPLOAD_SIZE}">
			{if sizeof($SOURCES) gt 0}
				<select id="SelectSourceDropdown" name="ImportSource" style="width: 420px;">
					{if sizeof($SOURCES) gt 1}
						<option disabled selected></option>
					{/if}
					{foreach item=SOURCE from=$SOURCES}
						{assign var=DESCRIPTION value=htmlentities($SOURCE['description']|cat:'<br>'|cat:$SOURCE['lastimport'])}
						<option value="{$SOURCE['classname']}" {if $DEFAULT_SOURCE eq $SOURCE['classname']}selected="selected"{/if}
							title="{$DESCRIPTION}">
							{'LBL_FROM'|@vtranslate:$MODULE} {$SOURCE['sourcename']|@vtranslate:$MODULE} ({$SOURCE['sourcetype']|@vtranslate:$MODULE})
						</option>
						{if $DEFAULT_SOURCE eq $SOURCE['classname']}
							{assign var=DEFAULT_DESCRIPTION value=$DESCRIPTION}
						{/if}
					{/foreach}
				</select>
				<pre id="data-import-selected-description" {if $DEFAULT_DESCRIPTION eq htmlentities('<br>')}style="display: none;"{/if}>{$DEFAULT_DESCRIPTION}</pre>
			{else}
				<strong>{'LBL_NO_DATA_SOURCE'|@vtranslate:$MODULE}</stron>
			{/if}
		</td>
	</tr>
</table>