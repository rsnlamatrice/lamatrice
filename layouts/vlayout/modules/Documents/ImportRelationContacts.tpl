{*<!--
/*********************************************************************************
** ED150814
*
 ********************************************************************************/
-->*}
{strip}
<div class='container-fluid editViewContainer'>
	<div class="contentHeader row-fluid">
		<h3 class="span10 textOverflowEllipsis">
			{$RECORD_MODEL->getName()} - {vtranslate('LBL_IMPORT', $MODULE)} {vtranslate($RELATED_MODULE)}
		</h3>
	</div>
	{if !$CURRENT_IMPORT_FILE}
	<form class="form-horizontal" id="ImportView" name="EditView" method="post" action="index.php" enctype="multipart/form-data">
		<span class="span2">Fichier à importer</span>
		<span class="span8"><input type="file" name="import_file"/></span>
		
		<span class="pull-right">
			<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_SEND', $MODULE)}</strong></button>
			<a class="cancelLink" type="reset" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL', $MODULE)}</a>
		</span>
	</form>
	{else}
	<form class="form-horizontal" id="ImportView" name="EditView" method="post" action="index.php" enctype="multipart/form-data">
		<div class="contentHeader row-fluid">
			Importation en cours de {$CURRENT_IMPORT_FILE}
		</div>
		
	</form>
	{/if}
</div>
{/strip}
