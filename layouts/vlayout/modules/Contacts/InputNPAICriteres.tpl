{*<!--
/*********************************************************************************
  ** ED150813
  *
 ********************************************************************************/
-->*}
{strip}
<div class='container-fluid editViewContainer'>
<form class="form-horizontal" id="EditView" name="EditView" method="post" action="index.php" enctype="multipart/form-data">
	<div class="contentHeader row-fluid">
		<h3>Saisie des NPAI et affectations de critères</h3>
		<span class="pull-right">
			<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
			<a class="cancelLink" type="reset" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL', $MODULE)}</a>
		</span>
	</div>
	<table class="table">
		<thead>
			<th>Référence du contact</th>
			<th>Contact connu</th>
			<th class="critere" data-critere="NPAI">NPAI
				<a href="" class="remove-column" style="margin-left: 2em;" title="Supprimer la saisie des NPAI"><span class="icon-trash"></span></a>
			</th>
			<th class="critere-model hide"></th>
			<th class="helper"><button class="add-critere">ajouter un critère</button></th>
		</thead>
		<tbody>
		<tr class="row-model hide">
			<td>
				<input type="hidden" name="contactid"/>
				<input type="hidden" class="contact_no" name="contact_no"/>
			</td>
			<td class="contact-details"></td>
			<td class="critere" data-critere="NPAI"></th>
			<td class="critere-model hide"><input type='hidden' name="critereid"/>
				date : <input name="critere-dateapplication" size="8"/>
				<br>info : <input name="critere-reldata" size="16"/>
			</th>
			<td ><a href onclick="var $tr = $(this).parents('tr:first');
				$tr.remove();
				return false;">supprimer</a></th>
		</tr>
		</tbody>
		<tfoot>
		<tr class="inputs">
			<td colspan="5"><input class="contact_no" value="CID202396"/></td>
		</tr>
		</tbody>
	</table>
</form>
</div>
{/strip}