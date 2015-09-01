{*<!--
/*********************************************************************************
							TMP !!!!
manage cancel button using importclassname
 ********************************************************************************/
-->*}
{strip}

{literal}
<script type="text/javascript">
/* rafraichissement périodique
 * cf ImportSchedule.tpl
 * TODO : lorsque l'import est fini, il faudrait afficher les résultats. En l'état, on obtient la création d'un nouvel import.
 */
jQuery(document).ready(function() {
	setTimeout(function() {
		jQuery('button[name="ok"]:visible:first').click();
		}, 2 * 60 * 1000);
});
</script>
{/literal}

<form onsubmit="VtigerJS_DialogBox.block();" action="index.php" enctype="multipart/form-data" method="POST" name="importStatusForm">
	<input type="hidden" name="module" value="{$FOR_MODULE}" />
	<input type="hidden" name="view" value="Import" /><!--TMP Import Module ???? -->
	{if $CONTINUE_IMPORT eq 'true'}
	<input type="hidden" name="mode" value="continueImport" />
	{else}
	<input type="hidden" name="mode" value="" />
	{/if}
</form>
<table style="width:80%;margin-left:auto;margin-right:auto;margin-top:10px;" cellpadding="10" class="searchUIBasic well">
	<tr>
		<td class="font-x-large" align="left" colspan="2">
			{'LBL_IMPORT'|@vtranslate:$MODULE} {$FOR_MODULE|@vtranslate:$FOR_MODULE}&nbsp;-&nbsp;
			<span class="redColor">
				{if $IMPORT_STATUS eq Import_Queue_Action::$IMPORT_STATUS_HALTED}
					{'LBL_HALTED'|@vtranslate:$MODULE} !!!
					
					<button class="btn" name="continue" style="margin-left: 2em;"
						id="continueHaltedImport"
						onclick="location.href='index.php?for_module={$FOR_MODULE}&module=RSNImportSources&view=Index&mode=continueHaltedImport&import_id={$IMPORT_ID}'"><strong>{'LBL_REACTIVATE'|@vtranslate:$MODULE}</strong></button>
				{else}
					<a href="index.php?for_module={$FOR_MODULE}&module=RSNImportSources&view=Index&mode=continueHaltedImport&import_id={$IMPORT_ID}"
						onclick="return confirm('Retour &agrave; l\'&eacute;tat de programmation horaire.
												\r&Ecirc;tes vous s&ucirc;r que le traitement n\'est pas en cours ?
												\rLes cons&eacute;quences pourraient &ecirc;tre graves (doublons &agrave; gogo).');"
						style="color: red; vertical-align: top;"
					>{'LBL_RUNNING'|@vtranslate:$MODULE} ...</a>
				{/if}</span>
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
			<table cellpadding="10" cellspacing="0" align="center" class="dvtSelectedCell thickBorder importContents">
				<tr>
					<td>{'LBL_TOTAL_RECORDS_IMPORTED'|@vtranslate:$MODULE}</td>
					<td width="10%">:</td>
					<td width="35%"><b>{$IMPORT_RESULT.IMPORTED} / {$IMPORT_RESULT.TOTAL}</b>
						{if $IMPORT_RESULT.TOTAL}
							<br><i>soit {(int)($IMPORT_RESULT.IMPORTED / $IMPORT_RESULT.TOTAL * 100)}&nbsp;%</i>
						{/if}
					</td>
				</tr>
				<tr>
					<td>{'LBL_NUMBER_OF_RECORDS_CREATED'|@vtranslate:$MODULE}</td>
					<td width="10%">:</td>
					<td width="35%">{$IMPORT_RESULT.CREATED}</td>
				</tr>
				<tr>
					<td>{'LBL_NUMBER_OF_RECORDS_UPDATED'|@vtranslate:$MODULE}</td>
					<td width="10%">:</td>
					<td width="35%">{$IMPORT_RESULT.UPDATED}</td>
				</tr>
				{if $IMPORT_RESULT.SKIPPED}
					<tr>
						<td>{'LBL_NUMBER_OF_RECORDS_SKIPPED'|@vtranslate:$MODULE}</td>
						<td width="10%">:</td>
						<td width="35%">{$IMPORT_RESULT.SKIPPED}</td>
					</tr>
				{/if}
				{if $IMPORT_RESULT.MERGED}
					<tr>
						<td>{'LBL_NUMBER_OF_RECORDS_MERGED'|@vtranslate:$MODULE}</td>
						<td width="10%">:</td>
						<td width="35%">{$IMPORT_RESULT.MERGED}</td>
					</tr>
				{/if}
				<tr>
					<td>{'LBL_TOTAL_RECORDS_FAILED'|@vtranslate:$MODULE}</td>
					<td width="10%">:</td>
					<td width="35%">{if $IMPORT_RESULT.FAILED}<span style="color: red;">{$IMPORT_RESULT.FAILED}</span>{else}0{/if} / {$IMPORT_RESULT.TOTAL}
					{if $IMPORT_RESULT['FAILED'] neq '0'}
						&nbsp;&nbsp;<a class="cursorPointer" onclick="return window.open('index.php?module={$MODULE}&view=List&mode=getImportDetails&type=failed&start=1&foruser={$OWNER_ID}&for_module={$FOR_MODULE}','failed','width=700,height=650,resizable=no,scrollbars=yes,top=150,left=200');">{'LBL_DETAILS'|@vtranslate:$MODULE}</a><!-- TMP Link -->
					{/if}
				</tr>
			</table>
		</td>
	</tr>
</table>
{/strip}
