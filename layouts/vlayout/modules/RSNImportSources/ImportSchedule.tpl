{*<!--
/*********************************************************************************
							TMP !!!!
manage cancel button using importclassname
 ********************************************************************************/
-->*}
<table style="width:80%;margin-left:auto;margin-right:auto;margin-top:10px;" cellpadding="10" class="searchUIBasic well">
	<tr>
		<td class="font-x-large" align="left" colspan="2">
			<strong>{'LBL_IMPORT_SCHEDULED'|@vtranslate:$MODULE} {$FOR_MODULE|@vtranslate:$FOR_MODULE}</strong>
		</td>
	</tr>
	{if $ERROR_MESSAGE neq ''}
	<tr>
		<td class="style1" align="left" colspan="2">
			{$ERROR_MESSAGE}
		</td>
	</tr>
	{/if}
	{if $RESULT_DETAILS}
	<tr>
		<td class="style1" align="left" colspan="2">
			{include file="ImportResultDetails.tpl"|@vtemplate_path:$MODULE}
		</td>
	</tr>
	{/if}
	<tr>
		<td colspan="2" valign="top">
			<table cellpadding="10" cellspacing="0" align="center" class="dvtSelectedCell thickBorder importContents">
				<tr>
					<td>{'LBL_SCHEDULED_IMPORT_DETAILS'|@vtranslate:$MODULE}</td>
				</tr>
			</table>
		</td>
	</tr>
</table>