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
				{if $INVOICES_COUNT}
					<div class="row-fluid">
						<span class="span4">Nombre de règlements concernés :</span>
						<span class="span2">{$INVOICES_COUNT}</span>
						{if $INVOICES_COUNT == 200}<span class="span5"><i>{$INVOICES_COUNT} est le nombre maximum de règlements traités en une seule fois. Bref, il vous faut recommencer plusieurs fois l'opération.</i></span>{/if}
					</div>
					<div class="row-fluid">
						<span class="span4">Montant total :</span>
						<span class="span2">{CurrencyField::convertToUserFormat($INVOICES_TOTAL)} €</span>
					</div>
					
					<div class="row-fluid" style="margin-top: 2em; font-size: larger; text-decoration: underline;">
						<a class="downloadSend2Compta" href="#downloadSend2Compta">Cliquez ici pour télécharger le fichier pour la compta</a>
					</div>
					<div class="row-fluid" style="font-style: italic;">
						Le fichier devrait se trouver dans votre répertoire "Téléchargements".
						<br>Il doit être importé dans la compta (menu Fichier/Importer), en spécifiant les formats "Tabulation" et "UTF-8".
					</div>
					
					<div class="row-fluid" style="margin-top: 4em; font-size: larger; font-style: italic;">
						Après avoir téléchargé le fichier, vous devez cliquer sur 
						&nbsp;<button class="btn btn-success" type="submit" name="saveButton"><strong>Valider le transfert en compta</strong></button>
					</div>
					<div class="row-fluid" style="font-style: italic;">
						Les règlements seront définitivement verrouillés et ne seront plus transférables en compta.
						<br>Effectuer l'importation en compta et ne pas valider le transfert provoque des doublons en compta.
					</div>
				{else}
					<div class="row-fluid" style="margin-top: 2em; font-size: larger;">
						Aucune donnée à exporter
					</div>
				{/if}
				
				{if $VALIDATABLE_COUNT}
					<div class="row-fluid" style="margin-top: 4em;">
						<h4>Pour info, règlements en cours de création et validables :</h4>
						<br/>
						<span class="span3">entre le :</span>
						<span class="span2">{DateTimeField::convertToUserFormat($VALIDATABLE_DATEMINI)}</span>
						<br/>
						<span class="span3">et le :</span>
						<span class="span2">{DateTimeField::convertToUserFormat($VALIDATABLE_DATEMAXI)}</span>
						<br/>
						<span class="span3">Nombre :</span>
						<span class="span2">{$VALIDATABLE_COUNT}</span>
						<br/>
						<span class="span3">Montant total :</span>
						<span class="span2">{CurrencyField::convertToUserFormat($VALIDATABLE_TOTAL)} €</span>
					</div>
				{/if}
			</div>
		</div>
		<div class="modal-footer">
			<div class=" pull-right cancelLinkContainer">
				<a class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
			</div>
		</div>
	</form>
</div>
{/strip}