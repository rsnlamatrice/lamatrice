<table style="width:80%;margin-left:auto;margin-right:auto;margin-top: 10px" cellpadding="5" class="searchUIBasic well">
	<tr>
		<td align="right" colspan="2">
			<!--TMP Choose correct buttons function of what is displayed upper !!!!!!!!!!! -->
			<button name="next" class="create btn"
			   onclick="location.href='index.php?module={$FOR_MODULE}&view=Import&return_module={$FOR_MODULE}&return_action=index'" ><strong>{'LBL_IMPORT_MORE'|@vtranslate:$MODULE}</strong></button>
			&nbsp;&nbsp;
			<button name="next" class="delete btn"
					onclick="location.href='index.php?module={$FOR_MODULE}&view=Import&mode=undoImport&foruser={$OWNER_ID}'"><strong>{'LBL_UNDO_LAST_IMPORT'|@vtranslate:$MODULE}</strong></button>
			&nbsp;&nbsp;
			<button name="cancel" class="edit btn btn-success"
					onclick="location.href='index.php?module={$FOR_MODULE}&view=List'"><strong>{'LBL_FINISH_BUTTON_LABEL'|@vtranslate:$MODULE}</strong></button>
		</td>
	</tr>
</table>