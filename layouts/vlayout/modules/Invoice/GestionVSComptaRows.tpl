{*<!--
/*********************************************************************************
** 
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
		<caption style="text-align: left; font-weight: bold; font-size: larger;">Ecritures dans La Matrice et Cogilog
			&nbsp;{if $SELECTED_COMPTE}pour le compte {$SELECTED_COMPTE}{/if}
			&nbsp;{if $SELECTED_DATE}à la date du {$SELECTED_DATE}{/if}
		<br><label>&nbsp;<input type="checkbox" style="display: inline;"
				  onchange="var $table = $(this).parents('table:first'), show = this.checked;
				  if(show) $table.addClass('show-pointee');
				  else $table.removeClass('show-pointee');
				  ">Afficher les lignes équivalentes</label></caption>
		{foreach item=SOURCES_ENTRIES key=DATE from=$ENTRIES}
			<tr>
				<td class="date"><b>{$DATE}</b></td>
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
{/strip}
