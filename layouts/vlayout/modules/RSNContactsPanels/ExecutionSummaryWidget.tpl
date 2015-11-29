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
		Résultat : <b style="font-size: 32px; padding-left: 1em;">{$RESULT}</b>
		{/if}
		<br>
		<br>
		<div style="font-family: monospace;">
		{foreach item=QUERY_PARAM from=$QUERY_PARAMS}
			<div class="row-fluid visible-on-hover"><span class="span3"
				style="margin-left: {($QUERY_PARAM['depth']-1)*6}px;
					{if strtoupper($QUERY_PARAM['operation']) eq 'PANEL'} text-decoration: underline;{/if}">
				{$QUERY_PARAM['name']}</span>
			<span class="span8 {if strtoupper($QUERY_PARAM['operation']) eq 'PANEL'} hide{/if}">{$QUERY_PARAM['value']}</span></div>
		{/foreach}
		</div>
		<br>
		<div class="visible-on-hover"><i class="icon-play"></i>
			<pre class="hide">{$QUERY}</pre>
		</div>
	</div>
	<div class="pull-right alignMiddle">
		<a href="" title="TODO">voir les contacts</a>
	</div>
{/strip}