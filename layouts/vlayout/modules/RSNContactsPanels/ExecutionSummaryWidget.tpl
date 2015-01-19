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
			<div class="row-fluid"><span class="span3">{$QUERY_PARAM['name']}</span>
			<span class="span9">{$QUERY_PARAM['value']}</span></div>
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