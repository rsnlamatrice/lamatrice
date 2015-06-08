{strip}
<div class="contentsDiv span10 marginLeftZero">
	<form onsubmit="" action="index.php" enctype="multipart/form-data" method="POST" name="selectImportSource">
		<input type="hidden" name="module" value="{$MODULE}" />
		<input type="hidden" id="for_module" name="for_module" value="{$FOR_MODULE}" />
		<input type="hidden" name="view" value="Index" />
		<input type="hidden" name="mode" value="preImport" /><!--TMP secondStep name !! -->
		<table style="margin-left: auto;margin-right: auto;width: 100%;" class="searchUIBasic" cellspacing="12">
			<tr>
				<td class="font-x-large" align="left" colspan="2">
					<strong>{'LBL_IMPORT'|@vtranslate:$MODULE} {$FOR_MODULE|@vtranslate:$FOR_MODULE}</strong>
				</td>
			</tr>
			{if $ERROR_MESSAGE neq ''}
			<tr>
				<td class="style1" align="left" colspan="2">
					{$ERROR_MESSAGE}
				</td>
			</tr>
			{/if}
			<tr>
				<td class="leftFormBorder1 importContents" width="40%" valign="top" colspan="1">
					{include file='Import_Step1.tpl'|@vtemplate_path:'RSNImport'}
				</td>
				{if sizeof($SOURCES) gt 0}
					<td id="sourceconfiguration" class="leftFormBorder1 importContents" width="40%" valign="top" colspan="1">
						{include file='NothingToConfigure.tpl'|@vtemplate_path:'RSNImport'}
					</td>
				{/if}
			</tr>
			<tr>
				<td align="right" colspan="2">
				{include file='SelectImportSourceButtons.tpl'|@vtemplate_path:'RSNImport'}<!-- TMP -->
				</td>
			</tr>
		</table>
	</form>
</div>
{/strip}