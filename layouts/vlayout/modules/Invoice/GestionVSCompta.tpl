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
		<select name="date" onchange="$(this).parents('form:first').submit();" value="{$SELECTED_DATE}">{foreach item=DATE_LABEL key=DATE from=$DATES}
			<option value="{$DATE}" {if $SELECTED_DATE eq $DATE}selected="selected"{/if}>{$DATE_LABEL}</option>
		{/foreach}</select>
	</form>
	<table class="table table-bordered equalSplit detailview-table sources-count-{count($ALL_SOURCES)}">
		<caption style="text-align: left; font-weight: bold; font-size: larger;">{$TITLE}
		
		{if count($ALL_SOURCES) > 1}
			<br><label>&nbsp;<input type="checkbox" style="display: inline;"
					  onchange="var $table = $(this).parents('table:first'), show = this.checked;
					  if(show) $table.addClass('show-pointee');
					  else $table.removeClass('show-pointee');
					  ">Afficher les lignes équivalentes</label>
			</caption>
			<tr>
				<td class="date"></td>
				<td><table class="compte">
					<tr>
						{foreach item=SOURCE_NAME from=$ALL_SOURCES}<td>{$SOURCE_NAME}</td>{/foreach}
					</tr>
					</table>
				</td>
			</tr>
		{/if}
		{foreach item=COMPTES key=DATE from=$ENTRIES}
			{assign var=POINTEE value=count($ALL_SOURCES) > 1 && abs($COMPTES['TOTAUX']['COG'] - $COMPTES['TOTAUX']['LAM']) < 0.01}
			{* controle plus precis de chaque compte *}
			{if $POINTEE}
				{foreach item=SOURCES key=COMPTE from=$COMPTES}
					{if ! (count($ALL_SOURCES) > 1 && count($SOURCES) eq 2 && abs($SOURCES['COG'] - $SOURCES['LAM']) < 0.01)}
						{assign var=POINTEE value=false}
						{break}
					{/if}
				{/foreach}
			{/if}
			<tr {if $POINTEE}class="pointee"{/if}>
				<td class="date"><b><a href="{$ROWS_URL}&date={$DATE}">{$DATE}</a></b></td>
				<td><table class="compte">
				{foreach item=SOURCES key=COMPTE from=$COMPTES}
					{assign var=POINTEE value=count($ALL_SOURCES) > 1 && count($SOURCES) eq 2 && abs($SOURCES['COG'] - $SOURCES['LAM']) < 0.01}
					<tr {if $POINTEE}class="pointee"{/if}>
						<th colspan="2"><b><a href="{$ROWS_URL}&date={$DATE}&compte={$COMPTE}">
						{if $COMPTE neq "TOTAUX"}compte {/if}{$COMPTE}
						{if count($ALL_SOURCES) eq 2 && (count($SOURCES) eq 1 || abs($SOURCES['COG'] - $SOURCES['LAM']) >= 0.01) }
							&nbsp;: {if strpos($COMPTE, 'Nombre') !== 0}
									{money_format('%.2n', $SOURCES['COG'] - $SOURCES['LAM'])} &euro;
								{else}
									{$SOURCES['COG'] - $SOURCES['LAM']}
								{/if}
						{/if}
						</a></b></th>
					</tr>
					<tr {if $POINTEE}class="pointee"{/if}>
						{foreach item=SOURCE_NAME key=SOURCE_KEY from=$ALL_SOURCES}
							<td>{if $SOURCES[$SOURCE_KEY]}
								{if strpos($COMPTE, 'Nombre') !== 0}
									{money_format('%.2n', $SOURCES[$SOURCE_KEY])} &euro;
								{else}
									{$SOURCES[$SOURCE_KEY]}
								{/if}
							{/if}</td>
						{/foreach}
					</tr>
				{/foreach}
				</table>
				</td>
			</tr>
		{/foreach}
	</table>
</div>
{/strip}
