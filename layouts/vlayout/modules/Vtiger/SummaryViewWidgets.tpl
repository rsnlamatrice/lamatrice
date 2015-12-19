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
{if !empty($MODULE_SUMMARY)}
	<div class="row-fluid">
		<div class="span7">
			<div class="summaryView row-fluid">
			{$MODULE_SUMMARY}
			</div>
{/if}
{* ED141210
 * ajout de $NO_ACTIVITIES_WIDGET
 * en consquence, l'ordre des colonnes change, d'o $MODULO_LEFT
 *}
{if $NO_ACTIVITIES_WIDGET}
	{assign var=MODULO_LEFT value=1}
{else}
	{assign var=MODULO_LEFT value=0}
{/if}
			{foreach item=DETAIL_VIEW_WIDGET from=$DETAILVIEW_LINKS['DETAILVIEWWIDGET'] name=count}
				{if $smarty.foreach.count.index % 2 == $MODULO_LEFT && $DETAIL_VIEW_WIDGET}
					<div class="summaryWidgetContainer">
						<div class="widgetContainer_{$smarty.foreach.count.index}" data-url="{$DETAIL_VIEW_WIDGET->getUrl()}"
						     data-name="{$DETAIL_VIEW_WIDGET->getLabel()}" data-module="{$DETAIL_VIEW_WIDGET->get('linkName')}">
							<div class="widget_header row-fluid">
								{assign var=RECORD_ACTIONS value=$DETAIL_VIEW_WIDGET->get('action')}
								<span class="span{if is_array($RECORD_ACTIONS) && (count($RECORD_ACTIONS)>1)}6{else}8{/if} margin0px">
									<h4>{vtranslate($DETAIL_VIEW_WIDGET->getLabel(),$MODULE_NAME)}</h4></span>
								{if is_array($RECORD_ACTIONS)}
									{* ED141101 'actionlabel' property of widget. As 'action', it is an array. *}
									{assign var=RECORD_ACTION_LABELS value=$DETAIL_VIEW_WIDGET->get('actionlabel')}
									{assign var=RECORD_ACTION_IDX value=0}
									{foreach item=RECORD_ACTION from=$RECORD_ACTIONS}
										{assign var=IS_SELECT_BUTTON value=$RECORD_ACTION eq "Select"}
										{assign var=IS_REFRESH_BUTTON value=$RECORD_ACTION eq "Refresh"}
										<button type="button" class="btn addButton  pull-right {* ED141210 add pull-right *}
										{if $IS_SELECT_BUTTON eq true} selectRelation
										{elseif $IS_REFRESH_BUTTON eq true} refreshWidget {/if} "
										{if $IS_SELECT_BUTTON} data-moduleName={$DETAIL_VIEW_WIDGET->get('linkName')} {/if}
										data-url="{$DETAIL_VIEW_WIDGET->get('actionURL')}"
										{if !$IS_SELECT_BUTTON && !$IS_REFRESH_BUTTON} name="addButton"{/if}
										data-name="{$DETAIL_VIEW_WIDGET->get('linkField')}">
											{if $IS_REFRESH_BUTTON}<i class="icon-refresh icon-white"></i>
											{elseif $IS_SELECT_BUTTON eq false}<i class="icon-plus icon-white"></i>{/if}
											{if $RECORD_ACTION_LABELS}{assign var=RECORD_ACTION_LABEL value=$RECORD_ACTION_LABELS[$RECORD_ACTION_IDX]}
											{else}{assign var=RECORD_ACTION_LABEL value=vtranslate('LBL_'|cat:strtoupper($RECORD_ACTION),$MODULE_NAME)}
											{/if}
											&nbsp;<strong>{$RECORD_ACTION_LABEL}</strong>
										</button>
										{assign var=RECORD_ACTION_IDX value=$RECORD_ACTION_IDX+1}
									{/foreach}
									{* SG141106 class relatedModuleName added for compability with getRelatedModuleName() function in js script *}
									<input type="hidden" name="relatedModule" class="relatedModuleName" value="{$DETAIL_VIEW_WIDGET->get('linkName')}" />
								{/if}
							</div>
							<div class="widget_contents">
							</div>
						</div>
					</div>
				{/if}
			{/foreach}
		</div>
		<div class="span5" style="overflow: hidden">
			{if !($NO_ACTIVITIES_WIDGET)}
				<div id="relatedActivities">
					{$RELATED_ACTIVITIES}
				</div>
			{/if}
			{foreach item=DETAIL_VIEW_WIDGET from=$DETAILVIEW_LINKS['DETAILVIEWWIDGET'] name=count}
				{if $smarty.foreach.count.index % 2 != $MODULO_LEFT && $DETAIL_VIEW_WIDGET}
					<div class="summaryWidgetContainer">
						<div class="widgetContainer_{$smarty.foreach.count.index}" data-url="{$DETAIL_VIEW_WIDGET->getUrl()}"
						     data-name="{$DETAIL_VIEW_WIDGET->getLabel()}" data-module="{$DETAIL_VIEW_WIDGET->get('linkName')}">
							<div class="widget_header row-fluid">
								{assign var=RECORD_ACTIONS value=$DETAIL_VIEW_WIDGET->get('action')}
								<span class="span{if is_array($RECORD_ACTIONS) && (count($RECORD_ACTIONS)>1)}5{else}7{/if} margin0px"><h4>{vtranslate($DETAIL_VIEW_WIDGET->getLabel(),$MODULE_NAME)}</h4></span>
								{if is_array($RECORD_ACTIONS)}
									{* ED141101 'actionlabel' property of widget. As 'action', it is an array. *}
									{assign var=RECORD_ACTION_LABELS value=$DETAIL_VIEW_WIDGET->get('actionlabel')}
									{assign var=RECORD_ACTION_IDX value=0}
									{foreach item=RECORD_ACTION from=$RECORD_ACTIONS}
										{assign var=IS_SELECT_BUTTON value={$RECORD_ACTION eq "Select"}}
										{assign var=IS_REFRESH_BUTTON value={$RECORD_ACTION eq "Refresh"}}
										<button type="button" class="btn addButton  pull-right {* ED141210 add pull-right *}
										{if $IS_SELECT_BUTTON eq true} selectRelation
										{elseif $IS_REFRESH_BUTTON eq true} refreshWidget {/if} "
										{if $IS_SELECT_BUTTON eq true} data-moduleName={$DETAIL_VIEW_WIDGET->get('linkName')} {/if}
										data-url="{$DETAIL_VIEW_WIDGET->get('actionURL')}"
										{if $IS_SELECT_BUTTON neq true}name="addButton"{/if}
										data-name="{$DETAIL_VIEW_WIDGET->get('linkField')}">
											{if $IS_REFRESH_BUTTON}<i class="icon-refresh icon-white"></i>
											{elseif $IS_SELECT_BUTTON eq false}<i class="icon-plus icon-white"></i>{/if}
											{if $RECORD_ACTION_LABELS}{assign var=RECORD_ACTION_LABEL value=$RECORD_ACTION_LABELS[$RECORD_ACTION_IDX]}
											{else}{assign var=RECORD_ACTION_LABEL value=vtranslate('LBL_'|cat:strtoupper($RECORD_ACTION),$MODULE_NAME)}
											{/if}
											&nbsp;<strong>{$RECORD_ACTION_LABEL}</strong>
										</button>
										{assign var=RECORD_ACTION_IDX value=$RECORD_ACTION_IDX+1}
									{/foreach}
									{* SG141106 debug : class relatedModuleName added for compability with getRelatedModuleName() function in js script *}
									<input type="hidden" name="relatedModule" class="relatedModuleName" value="{$DETAIL_VIEW_WIDGET->get('linkName')}" />
								{/if}
							</div>
							<div class="widget_contents">
							</div>
						</div>
					</div>
				{/if}
			{/foreach}
		</div>
	</div>
{/strip}