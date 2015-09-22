<button type="submit" name="import"  class="btn btn-success">
	<strong>{'LBL_IMPORT_BUTTON_LABEL'|@vtranslate:$MODULE}</strong>
</button>
&nbsp;&nbsp;
<!-- tmp cancel button: clear preimport table-->
<a name="cancel" class="cursorPointer cancelLink" value="{'LBL_CANCEL'|@vtranslate:$MODULE}" onclick="window.history.back();/*location.href='index.php?module={$FOR_MODULE}&view=List'*/">
		{'LBL_CANCEL'|@vtranslate:$MODULE}
</a>