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
		<input type="hidden" name="view" value="GestionVSCompta"/>
		<label>Mois à analyser :</label>
		<select name="date">{foreach item=DATE_LABEL key=DATE from=$DATES}
			<option value="{$DATE}" {if $SELECTED_DATE eq $DATE}selected="selected"{/if}>{$DATE_LABEL}</option>
		{/foreach}</select>
		<input type="submit"/>
	</form>
	<table class="table table-bordered equalSplit detailview-table">
		<caption style="text-align: left; font-weight: bold; font-size: larger;">Ecart entre Lamatrice et Cogilog <small>(les écarts négatifs indiquent qu'il y a plus dans La Matrice que dans Cogilog)</small></caption>
		{foreach item=COMPTES key=DATE from=$ENTRIES}
			<tr>
				<td><b><a href="{$ROWS_URL}&date={$DATE}">{$DATE}</a></b></td>
				{foreach item=SOURCES key=COMPTE from=$COMPTES}
					<td><b><a href="{$ROWS_URL}&date={$DATE}&compte={$COMPTE}">{$COMPTE}</a></b>
						<br>
						{if count($SOURCES) eq 2}<span style="color:blue">écart&nbsp;:&nbsp;{money_format('%.2n', $SOURCES['LAM'] - $SOURCES['COG'])}</span>
						{elseif $SOURCES['COG']}<span style="color:red">Cogilog&nbsp;:&nbsp;{money_format('%.2n', $SOURCES['COG'])}</span>
						{elseif $SOURCES['LAM']}<span style="color:green">La Matrice&nbsp;:&nbsp;{money_format('%.2n', $SOURCES['LAM'])}</span>
						{/if}
					</td>
				{/foreach}
			</tr>
		{/foreach}
	</table>
</div>
{/strip}
