{*<!--
/*********************************************************************************
  ** ED150928
  *
 ********************************************************************************/
-->*}
{strip}
{foreach key=index item=jsModel from=$SCRIPTS}
	<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
{/foreach}
<div id="massEditContainer" class='modelContainer'>
	<div class="modal-header contentsBackground">
		<button type="button" class="close " data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="massEditHeader">{vtranslate($MODULE, $MODULE)} - {vtranslate('LBL_SEND2COMPTA', $MODULE)}</h3>
	</div>
	<form class="form-horizontal send2Compta" id="massEdit" name="MassEdit" method="post" action="index.php">
		<input type="hidden" name="module" value="{$MODULE}" />
		<input type="hidden" name="view" value="Send2Compta" />
		<input type="hidden" name="mode" value="validateSend2Compta" />
		<input type="hidden" name="viewname" value="{$CVID}" />
		<input type="hidden" name="selected_ids" value={ZEND_JSON::encode($SELECTED_IDS)}>

		<div name='massEditContent'>
			<div class="modal-body">
				<div class="row-fluid">
					<span class="span3">Nombre de factures concernées :</span>
					<span class="span2">{$INVOICES_COUNT}</span>
				</div>
				<div class="row-fluid">
					<span class="span3">Montant total :</span>
					<span class="span2">{CurrencyField::convertToUserFormat($INVOICES_TOTAL)} €</span>
				</div>
				
				<div class="row-fluid" style="margin-top: 2em;">
					<a class="downloadSend2Compta" href="#downloadSend2Compta">Télécharger le fichier pour la compta</a>
				</div>
			</div>
		</div>
		{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
	</form>
</div>
{/strip}