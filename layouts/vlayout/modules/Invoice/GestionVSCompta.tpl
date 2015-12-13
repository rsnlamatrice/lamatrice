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
		<input type="hidden" name="view" value="{$FORM_VIEW}"/>
		<label>Mois à analyser :</label>
		<select name="date" onchange="$(this).parents('form:first').submit();">{foreach item=DATE_LABEL key=DATE from=$DATES}
			<option value="{$DATE}" {if $SELECTED_DATE eq $DATE}selected="selected"{/if}>{$DATE_LABEL}</option>
		{/foreach}</select>
	</form>
	<table class="table table-bordered equalSplit detailview-table">
		<caption style="text-align: left; font-weight: bold; font-size: larger;">Ecart entre la Gestion et la Compta <small>(les écarts négatifs indiquent qu'il y a plus dans La Matrice que dans Cogilog)</small>
		
		<br><label>&nbsp;<input type="checkbox" style="display: inline;"
				  onchange="var $table = $(this).parents('table:first'), show = this.checked;
				  if(show) $table.addClass('show-pointee');
				  else $table.removeClass('show-pointee');
				  ">Afficher les lignes équivalentes</label>
		</caption>
		<tr>
			<td class="date"></td>
			<td><table class="compte"><tr><td>Compta</td><td>Gestion</td></tr></table></td>
		</tr>
		{foreach item=COMPTES key=DATE from=$ENTRIES}
			{assign var=POINTEE value=abs($COMPTES['TOTAUX']['COG'] - $COMPTES['TOTAUX']['LAM']) < 0.01}
			<tr {if $POINTEE}class="pointee"{/if}>
				<td class="date"><b><a href="{$ROWS_URL}&date={$DATE}">{$DATE}</a></b></td>
				<td><table class="compte">
				{foreach item=SOURCES key=COMPTE from=$COMPTES}
					{assign var=POINTEE value=count($SOURCES) eq 2 && abs($SOURCES['COG'] - $SOURCES['LAM']) < 0.01}
					<tr {if $POINTEE}class="pointee"{/if}>
						<th colspan="2"><b><a href="{$ROWS_URL}&date={$DATE}&compte={$COMPTE}">
						<b>{if $COMPTE neq "TOTAUX"}compte {/if}{$COMPTE}</b>
						{if count($SOURCES) eq 2 && abs($SOURCES['COG'] - $SOURCES['LAM']) >= 0.01 }
							&nbsp;: {money_format('%.2n', $SOURCES['COG'] - $SOURCES['LAM'])} &euro;
						{/if}
						</a></b></th>
					</tr>
					<tr {if $POINTEE}class="pointee"{/if}>
						<td>{if $SOURCES['COG']}{money_format('%.2n', $SOURCES['COG'])} &euro;{/if}</td>
						<td>{if $SOURCES['LAM']}{money_format('%.2n', $SOURCES['LAM'])} &euro;{/if}</td>
					</tr>
				{/foreach}
				</table>
				</td>
			</tr>
		{/foreach}
	</table>
</div>
{/strip}
