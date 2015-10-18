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
	<div class="recordDetails">
		{if $ERROR}
		<code>{$ERROR}</code>
		{else}
			Résultat : <b style="font-size: 32px; padding-left: 1em;">{if !$RESULT}aucun résultat{/if}</b>
			{if is_array($RESULT)}
				<table cellspacing=2 cellpadding=2 border=1>
					<thead>
						<tr>
							{foreach item=FIELD from=$RESULT_FIELDS}
								<th style="color:black;"><span title="{vtranslate($FIELD->name)}">
									{$FIELD->name}
								</span></th>
							{/foreach}
						</tr>
					</thead>
					<tbody>
						{foreach item=RESULT_ROW from=$RESULT}
							<tr>
								{foreach item=ROW_VALUE key=FIELD_NAME from=$RESULT_ROW}
									<td>{if $FIELD_NAME eq 'crmid'}
											<a href="?module={$RELATED_MODULE}&view=Detail&record={$ROW_VALUE}">{$ROW_VALUE}</a>
										{else}
											{$ROW_VALUE}
										{/if}
									</td>
								{/foreach}
							</tr>
						{/foreach}
					</tbody>
				</table>
			{/if}
		{/if}
		<br>
		<br>
		<div style="font-family: monospace;">
		{foreach item=QUERY_PARAM from=$QUERY_PARAMS}
			<div class="row-fluid visible-on-hover"><span class="span3"
				style="margin-left: {($QUERY_PARAM['depth']-1)*6}px;
					{if strtoupper($QUERY_PARAM['operation']) eq 'PANEL'} text-decoration: underline;{/if}">
				{$QUERY_PARAM['name']}</span>
			<span class="span8 {if strtoupper($QUERY_PARAM['operation']) eq 'PANEL'} hide{/if}">
				{if is_array($QUERY_PARAM['value'])}
					<div style="white-space:pre;">{print_r($QUERY_PARAM['value'], true)}</div>
				{else}
					{$QUERY_PARAM['value']}
				{/if}
			</span></div>
		{/foreach}
		</div>
		<br>
		<div class="visible-on-hover"><i class="icon-play"></i>
			<pre class="hide">{$QUERY}
			
			<div style="font-family: monospace;">Paramètres : {print_r($QUERY_PARAMS_VALUES, true)}
			</div></pre>
		</div>
	</div>
	<div class="pull-right alignMiddle">
		<a href="">voir les contacts</a>
	</div>
{/strip}