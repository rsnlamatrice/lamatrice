{*<!--
/*********************************************************************************
							TMP !!!!
 ********************************************************************************/
-->*}
<input type="hidden" name="module" value="{$FOR_MODULE}" />
<table style="width:80%;margin-left:auto;margin-right:auto;margin-top: 10px" cellpadding="5" class="searchUIBasic well">
	<tr>
		<td class="font-x-large" align="left" colspan="2">
			<strong>{'LBL_IMPORT'|@vtranslate:$MODULE} {$FOR_MODULE|@vtranslate:$FOR_MODULE} - {'LBL_RESULT'|@vtranslate:$MODULE}</strong>
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
		<td valign="top">
			{include file="ImportResultDetails.tpl"|@vtemplate_path:'RSNImportSources'}<!--TMP Import Module ???? -->
		</td>
	</tr>
	<tr>
		<td align="right" colspan="2">
			<button class="cancel btn"
			onclick="return window.open('index.php?module={$FOR_MODULE}&view=List&start=1&foruser={$OWNER_ID}&viename=all','test','width=700,height=650,resizable=1,scrollbars=0,top=150,left=200');"><strong>{'LBL_VIEW_LAST_IMPORTED_RECORDS'|@vtranslate:$MODULE}</strong></button>
		<!--{include file='Import_Finish_Buttons.tpl'|@vtemplate_path:'Import'}--><!--TMP Import Module ???? -->
		</td>
	</tr>
</table>