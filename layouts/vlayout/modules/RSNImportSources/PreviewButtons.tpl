<button type="submit" name="import"  class="btn btn-success">
	<strong>{'LBL_IMPORT_BUTTON_LABEL'|@vtranslate:$MODULE}</strong>
</button>
&nbsp;&nbsp;
<!-- tmp cancel button: clear preimport table-->
<a name="cancel" class="cursorPointer cancelLink" value="{'LBL_CANCEL'|@vtranslate:$MODULE}" onclick="window.history.back();/*location.href='index.php?module={$FOR_MODULE}&view=List'*/">
		{'LBL_CANCEL'|@vtranslate:$MODULE}
</a>

<span class="span4">
	Importation 
	<label style="display: inline; margin-left: 4px; margin-right: 2px;">
		<input type="radio" name="is_scheduled" value="0" {if ! $IS_SCHEDULED}checked="checked"{/if} style="display: inline"/>&nbsp;maintenant</label>
	<label style="display: inline; margin-left: 4px; margin-right: 2px;">
		<input type="radio" name="is_scheduled" value="1" {if $IS_SCHEDULED}checked="checked"{/if} style="display: inline"/>&nbsp;programmée</label>
</span>