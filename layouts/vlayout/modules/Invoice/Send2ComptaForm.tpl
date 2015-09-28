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
	<form class="form-horizontal" id="massEdit" name="MassEdit" method="post" action="index.php">
		<input type="hidden" name="module" value="{$MODULE}" />
		<input type="hidden" name="action" value="Send2Compta" />
		<input type="hidden" name="viewname" value="{$CVID}" />
		<input type="hidden" name="selected_ids" value={ZEND_JSON::encode($SELECTED_IDS)}>
		<input type="hidden" name="excluded_ids" value={ZEND_JSON::encode($EXCLUDED_IDS)}>
		<input type="hidden" name="search_key" value= "{$SEARCH_KEY}" />
		<input type="hidden" name="operator" value="{$OPERATOR}" />
		<input type="hidden" name="search_value" value="{$ALPHABET_VALUE}" />

		<div name='massEditContent'>
			<div class="modal-body">
				<div class="row-fluid">
					<span class="span3">Nombre de factures concernées :</span>
					<span class="span2">{$VALUES['count']}</span>
				</div>
				<div class="row-fluid">
					<span class="span3">Montant total :</span>
					<span class="span2">{CurrencyField::convertToUserFormat($VALUES['total'])} €</span>
				</div>
			</div>
		</div>
		{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
	</form>
</div>
{/strip}