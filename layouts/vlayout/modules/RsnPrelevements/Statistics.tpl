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
	<div class="row-fluid detailViewTitle">
		<div class="span12">
			<h2>Prélèvements actifs</h2>
			<p></p>
		</div>
		<div class="span12">
		<b>
			Périodicités prises en compte à la date du {$DATE_PRELEVEMENTS->format('d/m/Y')} : {implode(', ', $PERIODICITES)} *
		</b>
		</div>
	</div>
	{if $PRELEVEMENTS_ACTIFS}
		<div class="blockContainer span8" style="margin-top: 2em;">
		<table class="table table-bordered">
			<thead>
				<tr class="listViewHeaders">
					<th class="medium"><a>Périodicité</a></th>
					<th class="medium"><a>FIRST</a></th>
					<th class="medium"><a>RECUR</a></th>
					<th class="medium"><a>Total</a></th>
				</tr>
			</thead>
			{foreach item=STAT key=PERIODICITE from=$PRELEVEMENTS_ACTIFS}
			<tr {if strpos($PERIODICITE, 'Tota') !== false} class="totaux"{/if}>
				<td class="fieldLabel medium">
					<label class="muted pull-right marginRight10px">{if in_array($PERIODICITE, $PERIODICITES)}* {/if}{$PERIODICITE}</label>
				</td>
				{foreach item=FIRST_RECUR from=$FIRST_RECUR_LIST}
					<td class="fieldValue medium">
						{if {$STAT[$FIRST_RECUR]}}
							{$STAT[$FIRST_RECUR]['nombre']}
							<span class="pull-right">{$STAT[$FIRST_RECUR]['montant']} &euro;</span>
						{/if}
					</td>
				{/foreach}
			</tr>
			{/foreach}
		</table>
		</div>
		<style>{* attention, ceci bloque les scripts si on en avait besoin *}
			.totaux td {
				border-top: 1px solid black;
			}
		</style>
	{/if}
	<div class="blockContainer span8" style="margin-top: 2em;">
	{if $DUPLICATES_VIREMENTS}
		<div class="detailViewInfo row-fluid" style="margin-top: 3em; padding-left: 1em;">
			<h3><span class="ui-icon ui-icon-alert"></span>&nbsp;
				Attention, {if count($DUPLICATES_VIREMENTS) === 1}un contact a{else}des contacts ont{/if} plusieurs prélèvements actifs pour ce mois
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
				Attention, {if count($DUPLICATES_PRELVIREMENTS) === 1}un contact a{else}des contacts ont{/if} plusieurs ordres de prélèvements déjà générés pour ce mois
			</h3>
			{foreach item=ITEM key=CONTACT_ID from=$DUPLICATES_PRELVIREMENTS}
				<div class="detailViewInfo row-fluid" style="margin-top: 1em; padding-left: 1em;">
					<h4><a href="{$ITEM['url']}" target="_blank">{$ITEM['contactname']} ({$ITEM['contact_no']})</a></h4>
					&nbsp;<b>{$ITEM['nombre']}</b> ordres de prélèvements
				</div>
			{/foreach}
		</div>
	{/if}
	</div>
	
</div>
{/strip}