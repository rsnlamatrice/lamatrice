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
<div class="row-fluid conditionRow marginBottom10px">
	<span class="span4">
		
		<select class="{if empty($NOCHOSEN)}chzn-select{/if} row-fluid" name="columnname">
			<option value="none">{vtranslate('LBL_SELECT_FIELD',$MODULE)}</option>
			{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE}
				<optgroup label='{vtranslate($BLOCK_LABEL, $SOURCE_MODULE)}'>
				{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS}
					{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
					{assign var=MODULE_MODEL value=$FIELD_MODEL->getModule()}
					{if !empty($COLUMNNAME_API)}
						{assign var=columnNameApi value=$COLUMNNAME_API}
					{else}
						{assign var=columnNameApi value=getCustomViewColumnName}
					{/if}
					<option value="{$FIELD_MODEL->$columnNameApi()}" data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$FIELD_NAME}"
					{if decode_html($FIELD_MODEL->$columnNameApi()) eq $CONDITION_INFO['columnname']}
						{assign var=FIELD_TYPE value=$FIELD_MODEL->getFieldType()}
						{assign var=SELECTED_FIELD_MODEL value=$FIELD_MODEL}
						{if $FIELD_MODEL->getFieldDataType() == 'reference'}
							{$FIELD_TYPE='V'}
						{elseif $FIELD_MODEL->getFieldDataType() == 'picklist' || $FIELD_MODEL->getFieldDataType() == 'multipicklist' || $FIELD_MODEL->getFieldDataType() == 'buttonset'}
							{$FIELD_TYPE='PL'}
						{/if}
						{$FIELD_INFO['value'] = decode_html($CONDITION_INFO['value'])}
						selected="selected"
					{/if}
					{if ($MODULE_MODEL->get('name') eq 'Calendar') && ($FIELD_NAME eq 'recurringtype')}
						{assign var=PICKLIST_VALUES value = Calendar_Field_Model::getReccurencePicklistValues()}
						{$FIELD_INFO['picklistvalues'] = $PICKLIST_VALUES}
					{/if}
					{if $FIELD_MODEL->getFieldDataType() eq 'reference'}
						{assign var=referenceList value=$FIELD_MODEL->getWebserviceFieldObject()->getReferenceList()}
						{if is_array($referenceList) && in_array('Users', $referenceList)}
								{assign var=USERSLIST value=array()}
								{assign var=CURRENT_USER_MODEL value = Users_Record_Model::getCurrentUserModel()}
								{assign var=ACCESSIBLE_USERS value = $CURRENT_USER_MODEL->getAccessibleUsers()}
								{foreach item=USER_NAME from=$ACCESSIBLE_USERS}
										{$USERSLIST[$USER_NAME] = $USER_NAME}
								{/foreach}
								{$FIELD_INFO['picklistvalues'] = $USERSLIST}
								{$FIELD_INFO['type'] = 'picklist'}
						{/if}
					{/if}
					data-fieldinfo='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($FIELD_INFO))}' >
					{if $SOURCE_MODULE neq $MODULE_MODEL->get('name')}
						({vtranslate($MODULE_MODEL->get('name'), $MODULE_MODEL->get('name'))})  {vtranslate($FIELD_MODEL->get('label'), $MODULE_MODEL->get('name'))}
					{else}
						{vtranslate($FIELD_MODEL->get('label'), $SOURCE_MODULE)}
					{/if}
				</option>
				{/foreach}
				</optgroup>
			{/foreach}
			{* ED150212 - related modules views *}
			{assign var=SELECTED_VIEW value=$CONDITION_INFO['columnname']}
			{assign var=SELECTED_VIEW_ESC value=htmlentities($SELECTED_VIEW)}
			{foreach key=RELATED_NAME item=RELATED_VIEWS from=$RELATED_MODELS_VIEWS}
				{if $RELATED_NAME eq $SOURCE_MODULE}
					{assign var=RELATION_LABEL value=vtranslate('LBL_RELATED_'|cat:strtoupper($RELATED_NAME), $SOURCE_MODULE)}
				{else}
					{assign var=RELATION_LABEL value=vtranslate($RELATED_NAME, $SOURCE_MODULE)}
				{/if}
				<optgroup label='[{$RELATION_LABEL}]'>
				{* each view of module *}
				{foreach key=VIEW_IDX item=RELATED_VIEW from=$RELATED_VIEWS}
					{if $RELATED_VIEW['id'] eq $RECORD_ID}{break}{/if}
					{assign var=RELATED_FIELDS value=$RELATED_VIEW['fields']}
					{assign var=VIEW_ID value=$RELATED_VIEW['id']}
					{assign var=VIEW_LABEL value=$RELATED_VIEW['name']}
					{assign var=CONDITION_NAME value="["|cat:$RELATED_NAME|cat:":"|cat:$VIEW_LABEL|cat:":"|cat:$VIEW_ID|cat:"]"}
					<option value="{$CONDITION_NAME}" data-fieldtype="VW" data-field-name="(exists)"
						{if $CONDITION_NAME eq $SELECTED_VIEW
						|| $CONDITION_NAME eq $SELECTED_VIEW_ESC}
							{assign var=FIELD_TYPE value='VW'}
							selected="selected"
						{/if}
						data-fieldinfo='{*Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($FIELD_INFO))*}'
					{* translate *}
					{assign var=VIEW_LABEL value="["|cat:$RELATION_LABEL|cat:"] "|cat:vtranslate($VIEW_LABEL, $RELATED_NAME)}
					>{$VIEW_LABEL}
					</option>
				{/foreach}
		
				{* add related module name fields *}
				{foreach item=FIELD_MODEL key=FIELD_NAME from=Vtiger_Field_Model::getEntityNameFieldModels($RELATED_NAME)}
					{assign var=CONDITION_NAME value="["|cat:$RELATED_NAME|cat:"::::]"|cat:$FIELD_MODEL->getCustomViewColumnName()}
					{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
					<option value="{$CONDITION_NAME}" data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$CONDITION_NAME}"
						data-view="[{$RELATED_NAME}][]"
						{if $CONDITION_NAME eq $CONDITION_INFO['columnname']}
							{assign var=FIELD_TYPE value=$FIELD_MODEL->getFieldType()}
							{assign var=SELECTED_FIELD_MODEL value=$FIELD_MODEL}
							{if $FIELD_MODEL->getFieldDataType() == 'reference'}{$FIELD_TYPE='V'}{/if}
							{$FIELD_INFO['value'] = decode_html($CONDITION_INFO['value'])}
							selected="selected"
						{/if}
					
						data-fieldinfo='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($FIELD_INFO))}'
					>{vtranslate($FIELD_MODEL->get('label'), $RELATED_NAME)}
					</option>
				{/foreach}
				
				{* add relation fields *}
				{foreach key=VIEW_IDX item=RELATED_VIEW from=$RELATED_VIEWS}
					{if $RELATED_VIEW['id'] eq $RECORD_ID}{break}{/if}
					{assign var=RELATED_FIELDS value=$RELATED_VIEW['fields']}
					{assign var=VIEW_ID value=$RELATED_VIEW['id']}
					{assign var=VIEW_LABEL value=$RELATED_VIEW['name']}
					{foreach key=FIELD_NAME item=FIELD_MODEL from=$RELATED_FIELDS}
						{assign var=CONDITION_NAME value="["|cat:$RELATED_NAME|cat:":"|cat:$VIEW_LABEL|cat:":"|cat:$VIEW_ID|cat:":"|cat:$FIELD_MODEL->getCustomViewColumnName()|cat:"]"}
						{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
						<option value="{$CONDITION_NAME}" data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$CONDITION_NAME}"
							data-view="[{$RELATED_NAME}][{$VIEW_LABEL}]"
							{if $CONDITION_NAME eq $CONDITION_INFO['columnname']}
								{assign var=FIELD_TYPE value=$FIELD_MODEL->getFieldType()}
								{assign var=SELECTED_FIELD_MODEL value=$FIELD_MODEL}
								{if $FIELD_MODEL->getFieldDataType() == 'reference'}{$FIELD_TYPE='V'}{/if}
								{$FIELD_INFO['value'] = decode_html($CONDITION_INFO['value'])}
								selected="selected"
							{/if}
						
							data-fieldinfo='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($FIELD_INFO))}'
						>{vtranslate($FIELD_MODEL->get('label'), $RELATED_NAME)}
						</option>
					{/foreach}
					{break}
				{/foreach}
				</optgroup>
			{/foreach}
			
			{* ED150507 - Contacts panels *}
			{if isset($CONTACTS_PANELS)}
				{assign var=RELATED_NAME value='RSNContactsPanels'}
				{* each panel *}
				{foreach key=VIEW_IDX item=RECORD_MODEL from=$CONTACTS_PANELS}
					<optgroup label='* [Panel] {$RECORD_MODEL->getName()} *'>
					{*assign var=RELATED_FIELDS value=$RECORD_MODEL->getFields()*}
					{assign var=VIEW_ID value=$RECORD_MODEL->getId()}
					{assign var=VIEW_LABEL value=$RECORD_MODEL->getName()}
					{assign var=PANEL_CONDITION_NAME value="["|cat:$RELATED_NAME|cat:":"|cat:$VIEW_LABEL|cat:":"|cat:$VIEW_ID|cat:"]"}
					<option value="{$PANEL_CONDITION_NAME}" data-fieldtype="PANEL" data-field-name="(exists)"
						{if $PANEL_CONDITION_NAME eq $CONDITION_INFO['columnname']}
							{assign var=FIELD_TYPE value='VW'}
							selected="selected"
						{/if}
						data-fieldinfo='{*Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($FIELD_INFO))*}'
					>
					[Panel] {vtranslate($VIEW_LABEL, $RELATED_NAME)}
					</option>
					{* variables du panel *}
					{foreach item=VARIABLE from=$RECORD_MODEL->getVariablesRecordModels()}
						{assign var=VARIABLE_ID value=$VARIABLE->getId()}
						{assign var=VARIABLE_NAME value=$VARIABLE->getName()}
						{assign var=VARIABLE_AS_FIELD value=$VARIABLE->get('fieldid')}
						{assign var=VARIABLE_LABEL value=$VARIABLE->getName()}
						{assign var=FIELD_MODEL value=$VARIABLE->getQueryField()}
						{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfoForCustomView()}
						{assign var=CONDITION_NAME value=$PANEL_CONDITION_NAME|cat:":"|cat:$VARIABLE_AS_FIELD|cat:":"|cat:$VARIABLE_ID|cat:":"|cat:$VARIABLE_NAME|cat:":"|cat:$FIELD_MODEL->get('typeofdata')}
						<option value="{$CONDITION_NAME}" data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$VARIABLE_NAME}"
							{if $CONDITION_NAME eq $CONDITION_INFO['columnname']}
								{assign var=FIELD_TYPE value=$FIELD_MODEL->getFieldType()}
								{assign var=SELECTED_FIELD_MODEL value=$FIELD_MODEL}
								{$FIELD_INFO['value'] = decode_html($CONDITION_INFO['value'])}
								selected="selected"
							{/if}
							data-fieldinfo='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($FIELD_INFO))}'
						>
						{$VARIABLE_LABEL}
						</option>
					{/foreach}
					</optgroup>
				{/foreach}
			{/if}
			
			{* ED151025 - Statistiques *}
			{if isset($RELATED_STATISTICS)}
				{assign var=RELATED_MODULE_NAME value='RSNStatisticsResults'}
				{* each panel *}
				{foreach key=STAT_ID item=STAT_DATA from=$RELATED_STATISTICS}
					{assign var=RECORD_MODEL value=$STAT_DATA['recordModel']}
					{assign var=STAT_LABEL value=$RECORD_MODEL->getName()}
					<optgroup label='* [Stat] {$STAT_LABEL} *'>
					{foreach item=FIELD_MODEL from=$STAT_DATA['fields']}
						{assign var=STATFIELD_ID value=$FIELD_MODEL->get('id')}
						{assign var=STATFIELD_COLUMN_NAME value=$FIELD_MODEL->get('column')}
						{assign var=STATFIELD_LABEL value=$FIELD_MODEL->get('label')}
						{assign var=CV_CONDITION_NAME value="["|cat:$RELATED_MODULE_NAME|cat:":"|cat:$STATFIELD_COLUMN_NAME|cat:":"|cat:$STAT_ID|cat:":"|cat:$STATFIELD_ID|cat:":"|cat:$FIELD_MODEL->get('typeofdata')|cat:"]"}
						{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfoForCustomView()}
						<option value="{$CV_CONDITION_NAME}" data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$STATFIELD_COLUMN_NAME}"
							{if $CV_CONDITION_NAME eq $CONDITION_INFO['columnname']}
								{assign var=FIELD_TYPE value=$FIELD_MODEL->getFieldType()}
								{assign var=SELECTED_FIELD_MODEL value=$FIELD_MODEL}
								{$FIELD_INFO['value'] = decode_html($CONDITION_INFO['value'])}
								selected="selected"
							{/if}
							data-fieldinfo='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($FIELD_INFO))}'
						>
						{$STATFIELD_LABEL}
						</option>
					{/foreach}
					</optgroup>
				{/foreach}
			{/if}
		</select>
	</span>
	{if false}{*ED151122 je ne vois pas à quoi ça sert*}
	<span class="hide">
		<select class="relatedviews-fields">
			{* ED150212 - related modules views
			 * specific fields
			 *}
					
			{foreach key=RELATED_NAME item=RELATED_VIEWS from=$RELATED_MODELS_VIEWS}
				{foreach key=VIEW_IDX item=RELATED_VIEW from=$RELATED_VIEWS}
					{if $RELATED_VIEW['id'] eq $RECORD_ID}{break}{/if}
					{assign var=RELATED_FIELDS value=$RELATED_VIEW['fields']}
					{assign var=VIEW_ID value=$RELATED_VIEW['id']}
					{assign var=VIEW_LABEL value=$RELATED_VIEW['name']}
					<optgroup label='[{vtranslate($RELATED_NAME, $SOURCE_MODULE)}] {vtranslate($VIEW_LABEL, $RELATED_NAME)}'>
						{* relation : exists | excluded *}
						{assign var=COLUMN_NAME value=$RELATED_NAME|cat:"::"|cat:$VIEW_ID|cat:"::"|cat:$VIEW_LABEL}
						<option value="{$COLUMN_NAME}" data-fieldtype="VW" data-field-name="@RELATION"
							data-view="[{$RELATED_NAME}][{$VIEW_LABEL}]"
						{if in_array($COLUMN_NAME, $SELECTED_FIELDS)}
							selected="selected"
						{/if}
							data-fieldinfo='{*Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($FIELD_INFO))*}'
						{assign var=COLUMN_NAME value=vtranslate('LBL_RELATION_TO', $RELATED_NAME)|cat:" ["|cat:vtranslate($RELATED_NAME, $SOURCE_MODULE)|cat:"] "|cat:vtranslate($VIEW_LABEL, $RELATED_NAME)}
						>{$COLUMN_NAME}
						</option>
						{foreach key=FIELD_NAME item=FIELD_MODEL from=$RELATED_FIELDS}
							{assign var=COLUMN_NAME value=$RELATED_NAME|cat:"::"|cat:$VIEW_ID|cat:"::"|cat:$VIEW_LABEL|cat:"::"|cat:$FIELD_MODEL->getCustomViewColumnName()}
							{assign var=FIELD_INFO value=$FIELD_MODEL->getFieldInfo()}
							<option value="{$FIELD_MODEL->getCustomViewColumnName()}" data-fieldtype="{$FIELD_MODEL->getFieldType()}" data-field-name="{$COLUMN_NAME}"
								data-view="[{$RELATED_NAME}][{$VIEW_LABEL}]"
							{if decode_html($FIELD_MODEL->getCustomViewColumnName()) eq $CONDITION_INFO['columnname']}
								{assign var=FIELD_TYPE value=$FIELD_MODEL->getFieldType()}
								{assign var=SELECTED_FIELD_MODEL value=$FIELD_MODEL}
								{if $FIELD_MODEL->getFieldDataType() == 'reference'}
									{$FIELD_TYPE='V'}
								{/if}
								{$FIELD_INFO['value'] = decode_html($CONDITION_INFO['value'])}
								selected="selected"
							{/if}
							
								data-fieldinfo='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($FIELD_INFO))}'
							>{vtranslate($FIELD_MODEL->get('label'), $RELATED_NAME)}
							</option>
						{/foreach}
					</optgroup>
				{/foreach}
			{/foreach}
			{* ED150507 - Contacts panels *}
			{if isset($CONTACTS_PANELS)}
				{assign var=RELATED_NAME value='RSNContactsPanels'}
				<optgroup label='* {vtranslate($RELATED_NAME)} *'>
				{* each panel *}
				{foreach key=VIEW_IDX item=RECORD_MODEL from=$CONTACTS_PANELS}
					{if $RECORD_MODEL->getId() eq $RECORD_ID}{break}{/if}
					{assign var=VIEW_ID value=$RECORD_MODEL->getId()}
					{assign var=VIEW_LABEL value=$RECORD_MODEL->getName()}
					<optgroup label='[{vtranslate($RELATED_NAME, $SOURCE_MODULE)}] {vtranslate($VIEW_LABEL, $RELATED_NAME)}'>
						{* relation : exists | excluded *}
						{assign var=COLUMN_NAME value="["|cat:$RELATED_NAME|cat:":"|cat:$VIEW_LABEL|cat:":"|cat:$VIEW_ID|cat:"]"}
						<option value="{$COLUMN_NAME}" data-fieldtype="PANEL" data-field-name="@RELATION"
							data-view="[{$RELATED_NAME}][{$VIEW_LABEL}]"
						{if in_array($COLUMN_NAME, $SELECTED_FIELDS)}
							selected="selected"
						{/if}
							data-fieldinfo='{*Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($FIELD_INFO))*}'
						{assign var=COLUMN_NAME value=vtranslate('LBL_RELATION_TO', $RELATED_NAME)|cat:" ["|cat:vtranslate($RELATED_NAME, $SOURCE_MODULE)|cat:"] "|cat:vtranslate($VIEW_LABEL, $RELATED_NAME)}
						>{$COLUMN_NAME}
						</option>
					</optgroup>
				{/foreach}
			{/if}
		</select>
	</span>
	{/if}
	<span class="span3">
		{if empty($NOCHOSEN) && !$FIELD_TYPE}<span class="ui-icon ui-icon-alert"></span>{/if}{*ED151021*}
		<select class="{if empty($NOCHOSEN)}chzn-select{/if} row-fluid" name="comparator" value="{$CONDITION_INFO['comparator']}">
			 <option value="none">{vtranslate('LBL_NONE',$MODULE)}</option>
			{assign var=ADVANCE_FILTER_OPTIONS value=$ADVANCED_FILTER_OPTIONS_BY_TYPE[$FIELD_TYPE]}
			{if $FIELD_TYPE eq 'D' || $FIELD_TYPE eq 'DT'}
			    {assign var=DATE_FILTER_CONDITIONS value=array_keys($DATE_FILTERS)}
			    {assign var=ADVANCE_FILTER_OPTIONS value=array_merge($ADVANCE_FILTER_OPTIONS,$DATE_FILTER_CONDITIONS)}
			{/if}
			{foreach item=ADVANCE_FILTER_OPTION from=$ADVANCE_FILTER_OPTIONS}
				<option value="{$ADVANCE_FILTER_OPTION}"
				{if $ADVANCE_FILTER_OPTION eq $CONDITION_INFO['comparator']}
						selected="selected"
				{/if}
				>{vtranslate($ADVANCED_FILTER_OPTIONS[$ADVANCE_FILTER_OPTION])}</option>
			{/foreach}
		</select>
	</span>
	<span class="span4 fieldUiHolder">
		<input name="{if $SELECTED_FIELD_MODEL}{$SELECTED_FIELD_MODEL->get('name')}{/if}" data-value="value" class="row-fluid" type="text" value="{$CONDITION_INFO['value']|escape}" />
	</span>
	<span class="hide">
		<!-- TODO : see if you need to respect CONDITION_INFO condition or / and  -->
		{if empty($CONDITION)}
			{assign var=CONDITION value="and"}
		{/if}
		<input type="hidden" name="column_condition" value="{$CONDITION}" />
	</span>
	 <span class="span1">
		<i class="deleteCondition icon-trash alignMiddle" title="{vtranslate('LBL_DELETE', $MODULE)}"></i>
		
		<img class="sort-handler alignMiddle" src="{vimage_path('drag.png')}" style="margin-left: 2em;"/>
	</span>
</div>
{/strip}