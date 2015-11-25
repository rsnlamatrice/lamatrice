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
	
	{assign var=ALL_CONDITION_CRITERIA value=$ADVANCE_CRITERIA[1] }
	{*assign var=ANY_CONDITION_CRITERIA value=$ADVANCE_CRITERIA[2] *}

	{if empty($ALL_CONDITION_CRITERIA) }
		{assign var=ALL_CONDITION_CRITERIA value=array()}
	{/if}

	{*if empty($ANY_CONDITION_CRITERIA) }
		{assign var=ANY_CONDITION_CRITERIA value=array()}
	{/if*}

<div class="filterContainer">
	<input type="hidden" name="date_filters" data-value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($DATE_FILTERS))}' />
	<input type=hidden name="advanceFilterOpsByFieldType" data-value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($ADVANCED_FILTER_OPTIONS_BY_TYPE))}' />
	{foreach key=ADVANCE_FILTER_OPTION_KEY item=ADVANCE_FILTER_OPTION from=$ADVANCED_FILTER_OPTIONS}
		{$ADVANCED_FILTER_OPTIONS[$ADVANCE_FILTER_OPTION_KEY] = vtranslate($ADVANCE_FILTER_OPTION, $MODULE)}
	{/foreach}
	<input type=hidden name="advanceFilterOptions" data-value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($ADVANCED_FILTER_OPTIONS))}' />
	<div class="allConditionContainer conditionGroup contentsBackground well">
		<div class="header">
			<span><strong>{vtranslate('LBL_ALL_CONDITIONS',$MODULE)}</strong></span>
			&nbsp;
			<span>({vtranslate('LBL_ALL_CONDITIONS_DESC',$MODULE)})</span>
		</div>
		<div class="contents">
			<div class="conditionList">
			{foreach item=CONDITION_INFO from=$ALL_CONDITION_CRITERIA['columns']}
				{include file='AdvanceFilterConditionJSInitialized.tpl'|@vtemplate_path:$QUALIFIED_MODULE RECORD_STRUCTURE=$RECORD_STRUCTURE CONDITION_INFO=$CONDITION_INFO MODULE=$MODULE}
			{/foreach}
			{*if count($ALL_CONDITION_CRITERIA) eq 0}
				{include file='AdvanceFilterCondition.tpl'|@vtemplate_path:$QUALIFIED_MODULE RECORD_STRUCTURE=$RECORD_STRUCTURE MODULE=$MODULE CONDITION_INFO=array()}
			{/if*}
			</div>
			<div class="hide basic conditionRow-template">
				{include file='AdvanceFilterCondition.tpl'|@vtemplate_path:$QUALIFIED_MODULE RECORD_STRUCTURE=$RECORD_STRUCTURE CONDITION_INFO=array() MODULE=$MODULE NOCHOSEN=true}
			</div>
			<div class="addCondition">
				<button type="button" class="btn"><strong>{vtranslate('LBL_ADD_CONDITION',$MODULE)}</strong></button>
			</div>
			<div class="groupCondition">
				{assign var=GROUP_CONDITION value=$ALL_CONDITION_CRITERIA['condition']}
				{if empty($GROUP_CONDITION)}
					{assign var=GROUP_CONDITION value="and"}
				{/if}
				<input type="hidden" name="condition" value="{$GROUP_CONDITION}" />
			</div>
		</div>
	</div>
	{foreach item=ANY_CONDITION_CRITERIA key=ADVANCE_CRITERIA_INDEX from=$ADVANCE_CRITERIA}
		{if $ADVANCE_CRITERIA_INDEX eq '1'}{continue}{/if}
		<div class="anyConditionContainer conditionGroup contentsBackground well">
			<div class="header">
				<span><strong>{vtranslate('LBL_ANY_CONDITIONS',$MODULE)}</strong></span>
				&nbsp;
				<span>({vtranslate('LBL_ANY_CONDITIONS_DESC',$MODULE)})</span>
			</div>
			<div class="contents">
				<div class="conditionList">
				{foreach item=CONDITION_INFO from=$ANY_CONDITION_CRITERIA['columns']}
					{include file='AdvanceFilterConditionJSInitialized.tpl'|@vtemplate_path:$QUALIFIED_MODULE RECORD_STRUCTURE=$RECORD_STRUCTURE CONDITION_INFO=$CONDITION_INFO MODULE=$MODULE CONDITION="or"}
				{/foreach}
				{*if count($ANY_CONDITION_CRITERIA) eq 0}
					{include file='AdvanceFilterCondition.tpl'|@vtemplate_path:$QUALIFIED_MODULE RECORD_STRUCTURE=$RECORD_STRUCTURE MODULE=$MODULE CONDITION_INFO=array() CONDITION="or"}
				{/if*}
				</div>
				<div class="hide basic">
					{include file='AdvanceFilterCondition.tpl'|@vtemplate_path:$QUALIFIED_MODULE RECORD_STRUCTURE=$RECORD_STRUCTURE MODULE=$MODULE CONDITION_INFO=array() CONDITION="or" NOCHOSEN=true}
				</div>
				<div class="addCondition">
					<button type="button" class="btn"><strong>{vtranslate('LBL_ADD_CONDITION',$MODULE)}</strong></button>
				
					<span class="pull-right addConditionsGroup"><button type="button" class="btn" title="{vtranslate('LBL_CREATE_NEW_CONDITIONS_GROUP', $MODULE)}"><i class="ui-icon ui-icon-plus"></i></button></span>
					<span class="pull-right deleteConditionsGroup"><button type="button" class="btn" title="{vtranslate('LBL_REMOVE_CONDITIONS_GROUP', $MODULE)}"><i class="ui-icon ui-icon-trash"></i></button></span>
				</div>
				<div class="groupCondition">
					{assign var=GROUP_CONDITION value=$ALL_CONDITION_CRITERIA['condition']}
					{if empty($GROUP_CONDITION)}
						{assign var=GROUP_CONDITION value="and"}
					{/if}
					<input type="hidden" name="condition" value="{$GROUP_CONDITION}" />
				</div>
			</div>
		</div>
	{/foreach}
</div>
{/strip}