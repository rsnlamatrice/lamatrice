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
	</div>
	<h4>
		Périodicités prises en compte à la date du {$DATE_PRELEVEMENTS->format('d/m/Y')} : {implode(', ', $PERIODICITES)} *
	</h4>
	{if $PRELEVEMENTS_ACTIFS}
	<div class="blockContainer span8">
	<table class="table table-bordered">
		<thead>
			<tr class="listViewHeaders">
				<th class="medium"><a>Périodicité</a></th>
				<th class="medium"><a>FIRST</a></th>
				<th class="medium"><a>RECUR</a></th>
				<th class="medium"><a>Totaux</a></th>
			</tr>
		</thead>
		{foreach item=STAT key=PERIODICITE from=$PRELEVEMENTS_ACTIFS}
		<tr>
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
	{/if}
</div>
{/strip}