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
<div class="detailViewInfo">
	<form method="GET" action="{$FORM_URL}">
		<input type="hidden" name="module" value="{$MODULE_NAME}"/>
		<input type="hidden" name="view" value="GestionVSComptaRows"/>
		<label>Mois à analyser :</label>
		<select name="date">{foreach item=DATE_LABEL key=DATE from=$DATES}
			<option value="{$DATE}" {if $SELECTED_DATE eq $DATE}selected="selected"{/if}>{$DATE_LABEL}</option>
		{/foreach}</select>
		<input type="submit"/>
	</form>
	<table class="table table-bordered equalSplit detailview-table">
		<caption style="text-align: left; font-weight: bold; font-size: larger;">Ecritures dans Lamatrice et Cogilog
			&nbsp;{if $SELECTED_COMPTE}pour le compte {$SELECTED_COMPTE}{/if}
			&nbsp;{if $SELECTED_DATE}à la date du {$SELECTED_DATE}{/if}
		<br><label><input type="checkbox" style="display: inline;"
				  onchange="var $table = $(this).parents('table:first'), show = this.checked;
				  if(show) $table.addClass('show-pointee');
				  else $table.removeClass('show-pointee');
				  ">Afficher les lignes équivalentes</label></caption>
		{foreach item=SOURCES_ENTRIES key=DATE from=$ENTRIES}
			<tr>
				<td style="width: 10%;"><b>{$DATE}</b></td>
				{foreach item=SOURCE_LABEL key=SOURCE from=$SOURCES}
					<td style="vertical-align: top;"><b>{$SOURCE_LABEL}</b>
						{if $SOURCES_ENTRIES[$SOURCE]}
							<table>
							{foreach item=ECRITURE from=$SOURCES_ENTRIES[$SOURCE]}
								<tr {if $ECRITURE['pointee']}class="pointee"{/if}>
									<td colspan="2" style="border-top: 1px solid black;">{$ECRITURE['nomfacture']}</td>
								</tr>
								<tr {if $ECRITURE['pointee']}class="pointee"{/if}>
									<td style="text-align: right;">{$ECRITURE['compte']}</td>
									<td style="text-align: right;">{$ECRITURE['montant']}</td>
								</tr>
							{/foreach}
							</table>
						{/if}
					</td>
				{/foreach}
			</tr>
		{/foreach}
	</table>
</div>
<style>
	tr.pointee {
		display: none;
	}
	table.show-pointee tr.pointee {
		display: table-row;
		background-color: #BBBB55;
	}
</style>
{/strip}
