{strip}
{* Import_Data_Action::
$IMPORT_RECORD_NONE = 0;
$IMPORT_RECORD_CREATED = 1;
$IMPORT_RECORD_SKIPPED = 2;
$IMPORT_RECORD_UPDATED = 3;
$IMPORT_RECORD_MERGED = 4;
$IMPORT_RECORD_FAILED = 5;*}
				<tr>
					<td class="font-x-large" align="left" colspan="2">
						<span class="big">{'LBL_IMPORT_PREVIEW_FOR_MODULE'|@vtranslate:$MODULE} <b>{$MODULE_NAME|@vtranslate:$MODULE_NAME}</b> :</span>
					</td>
				</tr>
				<tr>
					<td valign="top">
						{if sizeof($MODULE_DATA) gt 0}
							<table cellpadding="10" cellspacing="0" class="dvtSelectedCell thickBorder importContents" data-module="{$MODULE_NAME}">
								{if $ROW_OFFSET === 0}
									<thead><tr>
										<th></th>
										{foreach from=$MODULE_DATA[0] key=FIELD_NAME item=VALUE}
											<th class="redColor">{$FIELD_NAME}</th>
										{/foreach}
									</tr></thead>
								{/if}
								<tbody>
								{if $PREVIEW_DATA@last}
									{assign var=MODULE_ROW_OFFSET value=$ROW_OFFSET + 1}
								{else}
									{assign var=MODULE_ROW_OFFSET value=0}
								{/if}
								{foreach item=ROW key=ROW_INDEX from=$MODULE_DATA}
									{assign var=MODULE_ROW_OFFSET value=$MODULE_ROW_OFFSET + 1}
									<tr style="background-color: {$ROW_STATUS_COLORS[$ROW['status']]};">
										<th style="color: gray;">{$MODULE_ROW_OFFSET}</th>
										{foreach key=FIELD_NAME item=VALUE from=$ROW}
											<td>{$VALUE}</td>
										{/foreach}
									</tr>
								{/foreach}
								</tbody>
							</table>
						{else}
							<span class="big font-x-large">{'LBL_NO_DATA'|@vtranslate:$MODULE}:</span>
						{/if}
					</td>
				</tr>
{/strip}