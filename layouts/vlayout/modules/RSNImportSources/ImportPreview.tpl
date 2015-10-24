{strip}
{* Import_Data_Action::
$IMPORT_RECORD_NONE = 0;
$IMPORT_RECORD_CREATED = 1;
$IMPORT_RECORD_SKIPPED = 2;
$IMPORT_RECORD_UPDATED = 3;
$IMPORT_RECORD_MERGED = 4;
$IMPORT_RECORD_FAILED = 5;*}
{assign var=ROW_STATUS_COLORS value=array('inherit', 'green', 'yellow', 'green', 'green', '#FFC0A0')}
<div class="marginLeftZero" style="overflow: scroll;width:95%;">
	{if sizeof($PREVIEW_DATA) gt 0}
		<table style="margin-left:auto;margin-right:auto;margin-top:10px;" cellpadding="10" class="importPreview searchUIBasic well">
			{foreach from=$PREVIEW_DATA key=MODULE_NAME item=MODULE_DATA}
				{include file='ImportPreviewModuleContent.tpl'|@vtemplate_path:'RSNImportSources'}
				{if $PREVIEW_DATA@last}{assign var=ROW_OFFSET value=count($MODULE_DATA)}{/if}
			{/foreach}
			<tfoot>
				{if $IMPORTABLE_ROWS_COUNT}
					<tr>
						<td class="style1" align="left" colspan="2">
							{if $PREIMPORT_VALIDATING_FORM_URL}
								<a class="showPreImportValidatingForm" href="{$PREIMPORT_VALIDATING_FORM_URL}" style="margin-left: 2em">afficher la validations des contacts</a>
							{/if}
							Nombre de lignes à importer : {$IMPORTABLE_ROWS_COUNT}{if true || $SOURCE_ROWS_COUNT neq $IMPORTABLE_ROWS_COUNT}&nbsp;/&nbsp;{$SOURCE_ROWS_COUNT}{/if}
							{if $ROW_OFFSET < $SOURCE_ROWS_COUNT}
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