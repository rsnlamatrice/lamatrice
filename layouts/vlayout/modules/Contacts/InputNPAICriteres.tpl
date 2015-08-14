{*<!--
/*********************************************************************************
  ** ED150813
  *
 ********************************************************************************/
-->*}
{strip}
<style>
	tr.save-done td {
		opacity: 0.6;
	}
	tr.save-done td.actions {
		opacity: 1;
	}
	.buttonset label {
	    margin-bottom: 0;
	}
	.buttonset {
	    margin-bottom: 5px;
	}
</style>
<div class='container-fluid editViewContainer'>
<form class="form-horizontal" id="EditView" name="EditView" method="post" action="index.php" enctype="multipart/form-data">
	<input type="hidden" name="module" value="{$MODULE}"/>
	<input type="hidden" name="action" value="SaveAjax"/>
	<input type="hidden" name="mode" value="saveNPAICriteres"/>
	<div class="contentHeader row-fluid">
		<h3>Saisie des NPAI et affectations de critères</h3>
		<span class="pull-right">
			<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
			<a class="cancelLink" type="reset" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL', $MODULE)}</a>
		</span>
	</div>
	<table class="table">
		<thead>
			<th>Contact</th>
			<th class="critere-NPAI" data-critere="NPAI">NPAI
				<a href="" class="remove-column" style="margin-left: 2em;" title="Supprimer la saisie des NPAI"><span class="icon-trash"></span></a>
			</th>
			<th class="critere-model critere hide"></th>
			<th class="helper"><button class="add-critere">ajouter un critère</button></th>
		</thead>
		<tbody>
		<tr class="row-model hide">
			<td class="contact-details">
				<input type="hidden" name="contactid[]"/>
			</td>
			<td class="critere-NPAI" data-critere="NPAI"></td>
			<td class="critere-model critere hide"><input type='hidden' name="critereid[]"/>
				<label><input type="checkbox" name="save-critere[]"
					onchange="var $this=$(this), $table=$this.parent().nextAll('table:first');
						if(this.checked) $table.removeClass('hide'); else $table.addClass('hide');"/>&nbsp;attribuer</label>
				<table class="hide">
					<tr><td>date : </td><td><input name="critere-dateapplication[]" class="input-small" value="{date('d-m-Y')}"/></td></tr>
					<tr><td>info : </td><td><input name="critere-reldata[]" class="input-medium"/></td></tr>
				</table>
			</td>
			<td class="actions"><a href onclick="var $tr = $(this).parents('tr:first'); $tr.remove(); return false;" title="supprimer la ligne"><span class="icon-trash"></span></a></td>
		</tr>
		</tbody>
		<tfoot>
		<tr class="inputs">
			<td colspan="3"><input class="contact_no" value="CID202396"/></td>
		</tr>
		</tbody>
	</table>
</form>
</div>
{/strip}