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
		jQuery('button[name="ok"]:last').click();
		}, 1 * 60 * 1000);
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
						onclick="location.href='index.php?for_module={$FOR_MODULE}&module=RSNImportSources&view=Index&mode=continueHaltedImport&import_id={$IMPORT_ID}'"><strong>{'LBL_REACTIVATE'|@vtranslate:$MODULE}</strong></button>
				{else}
					{'LBL_RUNNING'|@vtranslate:$MODULE} ...
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
					<td width="30%">{$IMPORT_RESULT.IMPORTED} / {$IMPORT_RESULT.TOTAL}</td>
				</tr>
				<tr>
					<td colspan="3">
						<table cellpadding="10" cellspacing="0" class="calDayHour">
							<tr>
								<td>{'LBL_NUMBER_OF_RECORDS_CREATED'|@vtranslate:$MODULE}</td>
								<td width="10%">:</td>
								<td width="10%">{$IMPORT_RESULT.CREATED}</td>
							</tr>
							<tr>
								<td>{'LBL_NUMBER_OF_RECORDS_UPDATED'|@vtranslate:$MODULE}</td>
								<td width="10%">:</td>
								<td width="10%">{$IMPORT_RESULT.UPDATED}</td>
							</tr>
							{if in_array($FOR_MODULE, $INVENTORY_MODULES) eq FALSE}
							<tr>
								<td>{'LBL_NUMBER_OF_RECORDS_SKIPPED'|@vtranslate:$MODULE}</td>
								<td width="10%">:</td>
								<td width="10%">{$IMPORT_RESULT.SKIPPED}</td>
							</tr>
							<tr>
								<td>{'LBL_NUMBER_OF_RECORDS_MERGED'|@vtranslate:$MODULE}</td>
								<td width="10%">:</td>
								<td width="10%">{$IMPORT_RESULT.MERGED}</td>
							</tr>
							{/if}
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
{/strip}