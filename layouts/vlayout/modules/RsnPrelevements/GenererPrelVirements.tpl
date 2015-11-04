{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
{strip}
<div class="detailViewContainer">
	<form class="form-horizontal recordEditView" id="EditView" name="EditView" method="post" action="{$RELOAD_URL}" enctype="multipart/form-data">
		<input type="hidden" name="module" value="{$MODULE_NAME}"/>
				
	<div class="row-fluid detailViewTitle">
		<div class="span12">
			<h2>Génération des prélèvements pour la date du {$DATE_VIREMENTS->format('d/m/Y')}</h2>
			<p></p>
		</div>
		<span class="pull-right">
			{if !$NOT_EDITABLE}
				<input type="hidden" class="submit-mode" name="" value=""/>
				{if $AVAILABLE_VIREMENTS}
				<button class="btn btn-success" type="submit" onclick="$(this).prevAll('input.submit-mode').attr('name', 'action').val('Generate');">
					<strong>{vtranslate('LBL_GENERATE', $MODULE)}</strong></button>
				&nbsp;
				{/if}
				<button class="btn btn-success" type="submit" onclick="$(this).prevAll('input.submit-mode').attr('name', 'view').val('GenererPrelVirements');">
					<strong>Recalculer</strong></button>
			{elseif !is_numeric($NOT_EDITABLE)}
			    <i>{vtranslate($NOT_EDITABLE, $MODULE)}</i>
			{/if}
			<a href="{$CANCEL_URL}" class="cancelLink" type="reset">{vtranslate('LBL_CANCEL', $MODULE)}</a>
		</span>
	</div>
	<table class="table table-bordered blockContainer ">
	<tr>
		<td class="fieldLabel medium">
			<label class="muted pull-right marginRight10px">Date du virement</label>
		</td>
		<td class="fieldValue medium">
			<div class="row-fluid date">
				<input name="date_virements" value="{$DATE_VIREMENTS->format('d-m-Y')}" class="dateField" data-date-format="dd-mm-yyyy"/>
			</div>
		</td>
	</tr>
	{if $EXISTING_PRELVIREMENTS}
		<tr>
			<td class="fieldLabel medium">
				<label class="muted pull-right marginRight10px">Message aux donateurs</label>
			</td>
			<td class="fieldValue medium">
			<div class="row-fluid"><span class="span10">
				<input id="msg_virements" name="msg_virements" value="{$MSG_VIREMENTS}" onchange="this.value = this.value.toUpperCase();"/>
			</span></div>
			</td>
		</tr>
	{/if}
	</table>
	{if $EXISTING_PRELVIREMENTS}
		<div class="detailViewInfo row-fluid" style="margin-top: 1em; padding-left: 1em;">
			<h3>Ordres de prélèvements prêts pour la banque</h3>
			{foreach item=ITEM key=RECUR_FIRST from=$EXISTING_PRELVIREMENTS}
				<div class="detailViewInfo row-fluid" style="margin-top: 1em; padding-left: 1em;">
					<h4>{if $RECUR_FIRST eq 'FIRST'}Premiers prélèvements (FIRST)
					{else}Prélèvements suivants (RECUR)
					{/if}
						<a href="{$DOWNLOAD_URL}&recur_first={$RECUR_FIRST}" style="padding-left: 2em;"
						 onclick="var msg=$('#msg_virements').val();
						 if(!msg){
							alert('Le message aux donateurs est vide !');
							return false;
						 }
						 this.href = this.href.replace(/\&msg_virements\=[^\&]*/, '') + '&msg_virements=' + encodeURIComponent(msg);
						 ">
							<strong><i class="icon-download" style="vertical-align: middle; margin-right: 4px;"></i>Télécharger</strong>
						</a>
							{if $RECUR_FIRST eq 'FIRST'}
							<a href="{$PRINT_FIRSTS_URL}" style="padding-left: 2em;">
								<strong><i class="icon-print" style="vertical-align: middle; margin-right: 4px;"></i>Imprimer les lettres de remerciements</strong>
							</a>
						{/if}
					</h4>
					&nbsp;<b>{$ITEM['nombre']}</b> prélèvement{if $ITEM['nombre']>1}s{/if}
					&nbsp;pour un montant total de <b>{$ITEM['montant']} &euro;</b>
				</div>
			{/foreach}
			<!--pre>{print_r($EXISTING_PRELVIREMENTS, true)}</pre-->
		</div>
	{/if}
	{if $AVAILABLE_VIREMENTS}
		<div class="detailViewInfo row-fluid" style="margin-top: 1em; padding-left: 1em;">
			<h3 title="Utiliser le bouton Générer">Ordres de prélèvements à générer ({implode(', ', $TYPES_PRLVNTS)} / {implode(', ', $PERIODICITES)})</h3>
			{foreach item=ITEM key=RECUR_FIRST from=$AVAILABLE_VIREMENTS}
				<div class="detailViewInfo row-fluid" style="margin-top: 1em; padding-left: 1em;">
					<h4>{if $RECUR_FIRST eq 'FIRST'}Premiers prélèvements (FIRST)
					{else}Prélèvements suivants (RECUR)
					{/if}</h4>
					&nbsp;<b>{$ITEM['nombre']}</b> prélèvement{if $ITEM['nombre']>1}s{/if}
					&nbsp;pour un montant total de <b>{$ITEM['montant']} &euro;</b>
				</div>
			{/foreach}
			<!--pre>{print_r($AVAILABLE_VIREMENTS, true)}</pre-->
		</div>
	{/if}
	{if $DUPLICATES_VIREMENTS}
		<div class="detailViewInfo row-fluid" style="margin-top: 3em; padding-left: 1em;">
			<h3><span class="ui-icon ui-icon-alert"></span>&nbsp;
				Attention, {if count($DUPLICATES_VIREMENTS) === 1}un contact a{else}des contacts ont{/if} plusieurs ordres de prélèvements déjà générés
			</h3>
			{foreach item=ITEM key=CONTACT_ID from=$DUPLICATES_VIREMENTS}
				<div class="detailViewInfo row-fluid" style="margin-top: 1em; padding-left: 1em;">
					<h4><a href="{$ITEM['url']}" target="_blank">{$ITEM['contactname']} ({$ITEM['contact_no']})</a></h4>
					&nbsp;<b>{$ITEM['nombre']}</b> prélèvements
				</div>
			{/foreach}
		</div>
	{/if}
	{if $DUPLICATES_PRELVIREMENTS}
		<div class="detailViewInfo row-fluid" style="margin-top: 3em; padding-left: 1em;">
			<h3><span class="ui-icon ui-icon-alert"></span>&nbsp;
				Attention, {if count($DUPLICATES_PRELVIREMENTS) === 1}un contact a{else}des contacts ont{/if} plusieurs ordres de prélèvements déjà générés
			</h3>
			{foreach item=ITEM key=CONTACT_ID from=$DUPLICATES_PRELVIREMENTS}
				<div class="detailViewInfo row-fluid" style="margin-top: 1em; padding-left: 1em;">
					<h4><a href="{$ITEM['url']}" target="_blank">{$ITEM['contactname']} ({$ITEM['contact_no']})</a></h4>
					&nbsp;<b>{$ITEM['nombre']}</b> ordres de prélèvements
				</div>
			{/foreach}
		</div>
	{/if}
	</form>
	<script>
		$(document.body).ready(function(){
			app.registerEventForDatePickerFields($('.detailViewContainer'));
		});
	</script>
</div>
{/strip}