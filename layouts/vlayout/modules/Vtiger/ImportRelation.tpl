{*<!--
/*********************************************************************************
** ED150814
*
 ********************************************************************************/
-->*}
{strip}
<div class='container-fluid editViewContainer'>
	<form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="index.php" enctype="multipart/form-data">

		<div class="contentHeader row-fluid">
			<h3 class="span10 textOverflowEllipsis">
				{$RECORD_MODEL->getName()} - {vtranslate('LBL_IMPORT', $MODULE)} {vtranslate($RELATED_MODULE)}
			</h3>
			<span class="pull-right">
				<button class="btn btn-success" type="submit"><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>
				<a class="cancelLink" type="reset" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL', $MODULE)}</a>
			</span>
		</div>
</div>
{/strip}
