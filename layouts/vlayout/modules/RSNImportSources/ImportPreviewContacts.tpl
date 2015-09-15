{strip}
{* Import_Data_Action::
$IMPORT_RECORD_NONE = 0;
$IMPORT_RECORD_CREATED = 1;
$IMPORT_RECORD_SKIPPED = 2;
$IMPORT_RECORD_UPDATED = 3;
$IMPORT_RECORD_MERGED = 4;
$IMPORT_RECORD_FAILED = 5;*}
<div class="marginLeftZero" style="overflow: scroll;width:95%;">
	{if sizeof($PREVIEW_DATA) gt 0}
		<table style="margin-left:auto;margin-right:auto;margin-top:10px;" cellpadding="10" class="searchUIBasic well">
			{foreach from=$PREVIEW_DATA key=MODULE_NAME item=MODULE_DATA}
				<tr>
					<td class="font-x-large" align="left" colspan="2">
						<span class="big">{'LBL_IMPORT_PREVIEW_FOR_MODULE'|@vtranslate:$MODULE} <b>{$MODULE_NAME|@vtranslate:$MODULE_NAME}</b> :</span>
					</td>
				</tr>
				<tr>
					<td valign="top">
						{if sizeof($MODULE_DATA) gt 0}
							<table cellpadding="10" cellspacing="0" class="dvtSelectedCell thickBorder importContents">
								{if $ROW_OFFSET === 0}
									<thead><tr>
										<th colspan="3"></th>
										{foreach from=$MODULE_DATA[0] key=FIELD_NAME item=VALUE}
											{if $FIELD_NAME[0] === '_'}
												{continue}
											{/if}
											<th class="redColor">{$FIELD_NAME}</th>
										{/foreach}
									</tr></thead>
								{/if}
								<tbody>
								{foreach item=ROW key=ROW_INDEX from=$MODULE_DATA}
									{assign var=ROW_OFFSET value=$ROW_OFFSET + 1}
									{if $ROW['status']}
										{assign var=ROW_CLASS value='ROW_STATUS_COLORS_'|cat:$ROW['status']}
									{else}
										{assign var=ROW_CLASS value='RECORDID_STATUS_COLORS_'|cat:$ROW['_contactid_status']}
									{/if}
									<tr class="preimport-row {$ROW_CLASS}">
										<th colspan="3">{$ROW_OFFSET}</th>
										{foreach key=FIELD_NAME item=VALUE from=$ROW}
											{if $FIELD_NAME[0] === '_'}
												{continue}
											{/if}
											<td>
											{if $FIELD_NAME eq '_contactid_status'}
												{vtranslate('LBL_RECORDID_STATUS_'|cat:$VALUE, $MODULE)}
											{else}
												{$VALUE}
											{/if}
											</td>
										{/foreach}
									</tr>
									{assign var=CONTACT_ROWS value=$ROW['_contact_rows']}
									{if $CONTACT_ROWS}
										{assign var=CONTACT_ROW_INDEX value=0}
										{foreach item=CONTACT_ROW key=CONTACT_ID from=$CONTACT_ROWS}
											<tr class="contact-row" data-contactid="{$CONTACT_ID}">
												{if $CONTACT_ROW_INDEX === 0}
													<th rowspan="{count($CONTACT_ROWS) + 1}">
														{$ROW['_contactid_source']}
													</th>
												{/if}
												<th>
													<input type="radio" name="contact_related_to_{$ROW_INDEX}"
															{if $CONTACT_ROW_INDEX === 0}checked="checked"{/if}
													/>
												</th>
												<th>
													<a href="{$CONTACTS_MODULE_MODEL->getDetailViewUrl($CONTACT_ID)}" target="_blank">
														<span class="icon-rsn-small-isgroup{$CONTACT_ROW['isgroup']}"></span>
													</a>
												</th>
												{foreach key=FIELD_NAME item=VALUE from=$ROW}
													{if $FIELD_NAME[0] === '_'}
														{continue}
													{/if}
													{assign var=CONTACT_FIELD value=$CONTACTS_FIELDS_MAPPING[$FIELD_NAME]}
													{if $CONTACT_FIELD && array_key_exists($CONTACT_FIELD, $CONTACT_ROW)}
														<td data-fieldname="{$FIELD_NAME}"
														{if strcasecmp($ROW[$FIELD_NAME], $CONTACT_ROW[$CONTACT_FIELD]) === 0}
															class="values-eq"
														{else}
															class="values-neq"
														{/if}>{$CONTACT_ROW[$CONTACT_FIELD]}</td>
													{/if}
												{/foreach}
											</tr>
											{assign var=CONTACT_ROW_INDEX value=$CONTACT_ROW_INDEX+1}
										{/foreach}
									{/if}
									{if $ROW['status'] == 0 && $ROW['_contactid_status'] !== null}
										<tr class="contact-row" data-contactid="">
											{if ! $CONTACT_ROWS}
												<th></th>
											{/if}
											<td colspan="3" class="select-contact">
												<input type="radio" name="contact_related_to_{$ROW_INDEX}"/>
												<a href="#"><i>sélectionner...</i></a></td>
											<td colspan="3" class="create-contact">
												<label><input type="radio" name="contact_related_to_{$ROW_INDEX}"/>
													<i>créer</i></label></td>
										</tr>
									{/if}
								{/foreach}
								</tbody>
							</table>
						{else}
							<span class="big font-x-large">{'LBL_NO_DATA'|@vtranslate:$MODULE}:</span>
						{/if}
					</td>
				</tr>
			{/foreach}
			<tfoot>
				{if $IMPORTABLE_ROWS_COUNT}
					<tr>
						<td class="style1" align="left" colspan="2">
							Nombre de lignes à importer : {$IMPORTABLE_ROWS_COUNT}{if true || $SOURCE_ROWS_COUNT neq $IMPORTABLE_ROWS_COUNT}&nbsp;/&nbsp;{$SOURCE_ROWS_COUNT}{/if}
							{if $ROW_OFFSET < $SOURCE_ROWS_COUNT}
								<a class="getMorePreviewData" href="{$MORE_DATA_URL}" style="margin-left: 2em">voir plus de lignes</a>
							{/if}		
						</td>
					</tr>
				{/if}
			</tfoot>
		</table>
		<div style="padding-left: 4em;">
			<form onsubmit="" action="index.php" enctype="multipart/form-data" method="POST" name="selectImportSource">
				<input type="hidden" name="module" value="{$MODULE}" />
				<input type="hidden" id="for_module" name="for_module" value="{$FOR_MODULE}" />
				<input type="hidden" name="view" value="Index" />
				<input type="hidden" name="mode" value="import" /><!--TMP Import Module ???? -->
				<input type="hidden" name="ImportSource" value="{$IMPORT_SOURCE}" />
				{include file='PreviewButtons.tpl'|@vtemplate_path:'RSNImportSources'}<!-- TMP -->
			</form>
		</div>
	{else}
		<table style="width:80%;margin-left:auto;margin-right:auto;margin-top:10px;" cellpadding="10" class="searchUIBasic well">
			<tr>
				<td class="font-x-large" align="left" colspan="2">
					<span class="big">{'LBL_NO_DATA'|@vtranslate:$MODULE}:</span>
				</td>
			</tr>
		</table>
	{/if}
</div>
{/strip}