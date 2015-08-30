<table width="100%" cellspacing="0" cellpadding="2">
	<tr>
		<td><strong>{'LBL_IMPORT_STEP_2'|@vtranslate:$MODULE}:</strong></td>
		<td class="big">{'LBL_SELECT_FILE_STEP_DESCRIPTION'|@vtranslate:$MODULE}</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td colspan="3" data-import-upload-size="{$IMPORT_UPLOAD_SIZE}" style="padding-left: 1em;"> 
			<input type="hidden" class="validateconfiguration" value="validateFile" />
			<input type="hidden" class="onLoad" value="registerFileConfigurationEvent" />
			<input type="hidden" id="curent_file_type" name="type" value="csv" />
			<input type="hidden" name="is_scheduled" value="1" />
			<input type="radio"  id="import_file_mode_upload" name="import_file_src_mode" value="upload" checked="checked"/>&nbsp;
				<input type="file" name="import_file[]" id="import_file" multiple
					onchange="if(this.value) $('#import_file_mode_upload').get(0).checked = true;"/>
			<br>
			<input type="radio" id="import_file_mode_localpath" name="import_file_src_mode" value="localpath"/>&nbsp;
				<input type="text" name="import_file_localpath" id="import_file_localpath"
					title="Chemin d'un fichier d&eacute;j&agrave; pr&eacute;sent sur le serveur"
					value="/home/emmanuel/transferts/Adresse-1-mini.csv"
					onchange="if(this.value) $('#import_file_mode_localpath').get(0).checked = true;"
					style="width: 90%;"/>
			<!-- input type="hidden" name="userfile_hidden" value=""/ -->
		</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>{'LBL_IMPORT_NEEDED_FILE_TYPE'|@vtranslate:$MODULE} <strong id="needed_file_type">{$IMPORT_ULPOAD_FILE_TYPE}</strong>,
			{'LBL_IMPORT_NEEDED_FILE_ENCODING'|@vtranslate:$MODULE} <strong id="needed_file_encoding">{$IMPORT_ULPOAD_FILE_ENCODING}</strong>
			<span class="file_delimiter" ><br/>{'LBL_IMPORT_NEEDED_FILE_DELIMITER'|@vtranslate:$MODULE} <strong id="needed_file_delimiter">{$SUPPORTED_DELIMITERS[$IMPORT_ULPOAD_FILE_DELIMITER]|cat:'_PLURAL'|@vtranslate:$MODULE}</strong></span>
		<td><a id="show_advanced_file_configuration" style="color: blue;" href="#">{'LBL_ADVANCED_FILE_CONFIGURATION'|@vtranslate:$MODULE}</a></td>
	</tr>
	<tr id="advanced_file_configuration">
		<td>&nbsp;</td>
		<td colspan="2">
			<table>
				<tr id="file_type_container">
					
					<td><span>{'LBL_FILE_TYPE'|@vtranslate:$MODULE}</span></td>
					<td>
						<select name="file_type" id="file_type">
							{foreach item=_FILE_TYPE from=$SUPPORTED_FILE_TYPES}
								<option value="{$_FILE_TYPE}" {if $_FILE_TYPE eq $IMPORT_ULPOAD_FILE_TYPE}selected{/if}>{$_FILE_TYPE|@vtranslate:$MODULE}</option>
							{/foreach}
						</select>
					</td>
				</tr>
				<tr id="file_encoding_container">
					<td><span>{'LBL_CHARACTER_ENCODING'|@vtranslate:$MODULE}</span></td>
					<td>
						<select name="file_encoding" id="file_encoding">
							{foreach key=_FILE_ENCODING item=_FILE_ENCODING_LABEL from=$SUPPORTED_FILE_ENCODING}
								<option value="{$_FILE_ENCODING}" {if $_FILE_ENCODING eq $IMPORT_ULPOAD_FILE_ENCODING}selected{/if}>{$_FILE_ENCODING_LABEL|@vtranslate:$MODULE}</option>
							{/foreach}
						</select>
					</td>
				</tr>
				<tr id="delimiter_container" class="file_delimiter">
					<td><span>{'LBL_DELIMITER'|@vtranslate:$MODULE}</span></td>
					<td>
						<select name="delimiter" id="file_delimiter">
							{foreach key=_DELIMITER item=_DELIMITER_LABEL from=$SUPPORTED_DELIMITERS}
								<option value="{$_DELIMITER}" {if $_DELIMITER eq $IMPORT_ULPOAD_FILE_DELIMITER}selected{/if}>
									{$_DELIMITER_LABEL|@vtranslate:$MODULE}
								</option>
							{/foreach}
						</select>
						{foreach key=_DELIMITER item=_DELIMITER_LABEL from=$SUPPORTED_DELIMITERS}
							<div style="display:none;" class="delimiter_plural" value="{$_DELIMITER}">{$_DELIMITER_LABEL|cat:'_PLURAL'|@vtranslate:$MODULE}</div>
						{/foreach}
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>