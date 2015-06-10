<table style="width:80%;margin-left:auto;margin-right:auto;margin-top: 10px" cellpadding="5" class="searchUIBasic well">
	<tr>
		<td align="right" colspan="2">
			<!--TMP Choose correct buttons function of what is displayed upper !!!!!!!!!!! -->
			<a href='index.php?for_module={$FOR_MODULE}&module=RSNImportSources&view=Index&mode=cancelImport&ImportSource={$IMPORT_SOURCE}'>{'LBL_CANCEL_IMPORT'|@vtranslate:$MODULE}</a>
			&nbsp;&nbsp;
			<button class="btn btn-success" name="ok"
				onclick="location.href='index.php?for_module={$FOR_MODULE}&module=RSNImportSources&view=Index'"><strong>{'LBL_OK_BUTTON_LABEL'|@vtranslate:$MODULE}</strong></button>
		</td>
	</tr>
</table>