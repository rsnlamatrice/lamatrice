{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 
 
 ED141006
	ajout d'un niveau dans l'url : &view=Outils&sub=
	applique la classe selectedQuickLink avec cette contrainte supplémentaire
*
 ********************************************************************************/
-->*}
{strip}
<div class="quickLinksDiv">
	{foreach item=SIDEBARLINK from=$QUICK_LINKS['SIDEBARLINK']}
		{assign var="SIDE_LINK_URL" value=decode_html($SIDEBARLINK->getUrl())}
		{assign var="EXPLODED_PARSE_URL" value=explode('?',$SIDE_LINK_URL)}
		{assign var="COUNT_OF_EXPLODED_URL" value=count($EXPLODED_PARSE_URL)}
		{if $COUNT_OF_EXPLODED_URL gt 1}
			{assign var="EXPLODED_URL" value=$EXPLODED_PARSE_URL[$COUNT_OF_EXPLODED_URL-1]}
		{/if}
		{assign var="PARSE_URL" value=explode('&',$EXPLODED_URL)}
		{assign var="CURRENT_LINK_VIEW" value='view='|cat:$CURRENT_VIEW}
		{assign var="LINK_LIST_VIEW" value=in_array($CURRENT_LINK_VIEW,$PARSE_URL)}
		{assign var="CURRENT_MODULE_NAME" value='module='|cat:$MODULE}
		{assign var="CURRENT_LINK_SUB" value='sub='|cat:$CURRENT_SUB}
		{assign var="IS_LINK_MODULE_NAME" value=in_array($CURRENT_MODULE_NAME,$PARSE_URL) && in_array($CURRENT_LINK_SUB,$PARSE_URL)}
		<p onclick="window.location.href='{$SIDEBARLINK->getUrl()}'" id="{$MODULE}_sideBar_link_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($SIDEBARLINK->getLabel())}"
		   class="{if $LINK_LIST_VIEW and $IS_LINK_MODULE_NAME}selectedQuickLink {else}unSelectedQuickLink{/if}"><a class="quickLinks" {* ED150306 href="{$SIDEBARLINK->getUrl()}" *}>
				<strong>{vtranslate($SIDEBARLINK->getLabel(), $MODULE)}</strong>
		</a></p>
		{if $SIDEBARLINK->get('children')}
			{assign var="LINK_CHILDREN" value=$SIDEBARLINK->get('children')}
			{foreach item=SIDEBARLINK from=$LINK_CHILDREN }
				{assign var="SIDE_LINK_URL" value=decode_html($SIDEBARLINK->getUrl())}
				{assign var="EXPLODED_PARSE_URL" value=explode('?',$SIDE_LINK_URL)}
				{assign var="COUNT_OF_EXPLODED_URL" value=count($EXPLODED_PARSE_URL)}
				{if $COUNT_OF_EXPLODED_URL gt 1}
					{assign var="EXPLODED_URL" value=$EXPLODED_PARSE_URL[$COUNT_OF_EXPLODED_URL-1]}
				{/if}
				{assign var="PARSE_URL" value=explode('&',$EXPLODED_URL)}
				{assign var="CURRENT_LINK_VIEW" value='view='|cat:$CURRENT_VIEW}
				{assign var="LINK_LIST_VIEW" value=in_array($CURRENT_LINK_VIEW,$PARSE_URL)}
				{assign var="CURRENT_MODULE_NAME" value='module='|cat:$MODULE}
				{assign var="CURRENT_LINK_SUB" value='sub='|cat:$CURRENT_SUB}
				{assign var="IS_LINK_MODULE_NAME" value=in_array($CURRENT_MODULE_NAME,$PARSE_URL) && in_array($CURRENT_LINK_SUB,$PARSE_URL)}
				<p onclick="window.location.href='{$SIDEBARLINK->getUrl()}'"id="{$MODULE}_sideBar_link_{Vtiger_Util_Helper::replaceSpaceWithUnderScores($SIDEBARLINK->getLabel())}"
				   class="{if $LINK_LIST_VIEW and $IS_LINK_MODULE_NAME}selectedQuickLink {else}unSelectedQuickLink{/if} sub-link"><a class="quickLinks" {* ED150306 href="{$SIDEBARLINK->getUrl()}" *}>
						<span>{vtranslate($SIDEBARLINK->getLabel(), $MODULE)}</span>
				</a></p>
			{/foreach}
		{/if}
	{/foreach}
</div>
<style>
	.sub-link {
		margin-left: 16px;
		margin-top: -16px;
		font-size: 14px;
	}
</style>
{/strip}