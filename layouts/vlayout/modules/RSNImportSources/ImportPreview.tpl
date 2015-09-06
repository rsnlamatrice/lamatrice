{strip}
	static $IMPORT_RECORD_NONE = 0;
	static $IMPORT_RECORD_CREATED = 1;
	static $IMPORT_RECORD_SKIPPED = 2;
	static $IMPORT_RECORD_UPDATED = 3;
	static $IMPORT_RECORD_MERGED = 4;
	static $IMPORT_RECORD_FAILED = 5;
{assign var=ROW_STATUS_COLORS value=array('inherit', 'green', 'yellow', 'green', 'green', '#FFC0A0')}
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
										<th></th>
										{foreach from=$MODULE_DATA[0] key=FIELD_NAME item=VALUE}
											<th class="redColor">{$FIELD_NAME}</th>
										{/foreach}
									</tr></thead>
								{/if}
								<tbody>
								{foreach item=ROW key=ROW_INDEX from=$MODULE_DATA}
									{assign var=ROW_OFFSET value=$ROW_OFFSET + 1}
									<tr style="background-color: {$ROW_STATUS_COLORS[$ROW['status']]};">
										<th style="color: gray;">{$ROW_OFFSET}</th>
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
			{/foreach}
			<tfoot>
				{if $IMPORTABLE_ROWS_COUNT}
					<tr>
						<td class="style1" align="left" colspan="2">
							Nombre de lignes à importer : {$IMPORTABLE_ROWS_COUNT}
							{if $ROW_OFFSET < $IMPORTABLE_ROWS_COUNT}
								<a class="getMorePreviewData" href="{$MORE_DATA_URL}" style="margin-left: 2em">voir plus de lignes</a>
							{/if}		
						</td>
					</tr>
				{/if}
				<tr>
					<!-- tmp add next button !! -->
					<!-- tmp add call manu if problem !! -->
					<!-- tmp replace the return button by a cancel button !! -->
					<!--<td align="right">
						
					<button name="cancel" class="delete btn btn-danger"
						onclick="location.href='index.php?module=RSNImportSources&view=Index&for_module={$FOR_MODULE}'"><strong>{'LBL_RETURN'|@vtranslate:$MODULE}</strong></button>
					</td>-->
				</tr>
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