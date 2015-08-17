{*<!--
/*********************************************************************************
  ** ED150700
  *
 ********************************************************************************/
-->*}
{strip}

{if $IFRAME_SRC}
	<iframe width="800" height="400" src="{$IFRAME_SRC}"></iframe>
{else}
<div>
	<form class="address-check-editor">
	{* bouton prenant le focus hors écran, pour une question esthétique *}
	<button style="position: absolute; left: -9999px;"></button>
	<table>
		<thead>
			<tr>
				<th/>
				<th>Adresse actuelle</th>
				<th>Nouvelle proposition</th>
				<th>A appliquer</th>
				<th/>
			</tr>
		</thead>
		{foreach key=FIELD_NAME item=FIELD_INFO from=$COMPARAISON}
		<tr data-field="{$FIELD_NAME}">
		{if $FIELD_NAME eq '_status_'}
			<td></td>
			<td></td>
			<td></td>
			<td class="submit-cell">
				{if (is_string($FIELD_INFO) && $FIELD_INFO neq 'equal') || ($FIELD_INFO['status'] neq 'equal')}
					<button class="btn btn-success" type="submit"><strong>Mettre à jour</strong></button>
				{/if}
			</td>
		{else}
			<td>
				<h4>{vtranslate($FIELD_NAME)}</h4>
			</td>
			<td><label>
				{if $FIELD_INFO['status'] eq 'equal'}
				=
				{else}
				<input type="radio" name="source_{$FIELD_NAME}" value="original" title="Garder la valeur originale"/>
				{/if}
				<span class="address-value original-value">{htmlentities($FIELD_INFO['original'])}</span>
			</label>
			</td>
			<td><label>
				{if $FIELD_INFO['status'] eq 'equal'}
				=
				{else}
				<input type="radio" name="source_{$FIELD_NAME}" value="new" checked="checked" title="Mettre à jour avec la nouvelle valeur"/>
				{/if}
				<span class="address-value new-value">{htmlentities($FIELD_INFO['new'])}</span>
			</label>
			</td>
			<td>
				<input class="address-result" name="{$MAPPING[$FIELD_NAME]}" value="{htmlentities($FIELD_INFO['new'])}"/>
				
			</td>
		{/if}
		</tr>
		{/foreach}
	</table>
	</form>
	{*
	<br><br><br>
	<table>
		</tr>
			<td style="border: 1px solid gray">
				<table>
				{foreach key=FIELD_NAME item=VALUE from=$ORIGINAL_ADDRESS}
				{if is_string($VALUE)}
					<tr>
						<td>{vtranslate($FIELD_NAME)}</td>
						<td>{htmlentities($VALUE)}</td>
					</tr>
				{/if}
				{/foreach}
				</table>
			</td>
			<td>&nbsp;</td>
			<td style="border: 1px solid gray">
				<table>
				{foreach key=FIELD_NAME item=VALUE from=$NEW_ADDRESS}
					<tr>
						<td>{htmlentities($VALUE)}</td>
					</tr>
				{/foreach}
				</table>
			</td>
		</tr>
	</table>*}
</div>
{/if}
{/strip}