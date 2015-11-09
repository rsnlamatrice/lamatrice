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
<input type="hidden" id="popUpClassName" value="Vtiger_MergeRecord_Js"/>
<div style='background: white;'>
	<div>
		<br>
		<div style='margin-left:10px'><h3>{vtranslate('LBL_MERGE_RECORDS_IN', $MODULE)} > {$MODULE}</h3></div><br>
		<div class='alert-info'>{vtranslate('LBL_MERGE_RECORDS_DESCRIPTION', $MODULE)}</div>
	</div>

	<form class="form-horizontal contentsBackground" name="massMerge" method="post" action="index.php">
		<input type="hidden" name=module value="{$MODULE}" />
		<input type="hidden" name="action" value="ProcessDuplicates" />
		<input type="hidden" name="records" value={Zend_Json::encode($RECORDS)} />

	<div>
		<table id="mergeRecords" class='table table-bordered table-condensed'>
			<thead class='listViewHeaders'>
				<tr>
					<th>
						{vtranslate('LBL_FIELDS', $MODULE)}
					</th>
					{foreach item=RECORD from=$RECORDMODELS name=recordList}
						<th class="record">
							<label>
								<input {if $smarty.foreach.recordList.index eq 0}checked{/if} type=radio value="{$RECORD->getId()}" name=primaryRecord style='bottom:1px;position:relative;'/>
								 &nbsp; #{$smarty.foreach.recordList.index+1}
							</label>
							<a href="{$RECORD->get('url')}" target="_blank">{$RECORD->getHtmlLabel()}</a>
							{if count($RECORDMODELS) > 2}
							<span style="float: right"><a href="#" title="supprimer de la fusion"><span class="ui-icon ui-icon-trash"></span></a></span>{/if}
						</th>
					{/foreach}
				</tr>
			</thead>
			<tbody>
				{foreach item=BLOCK from=$BLOCKS}
					<tr class='listViewHeaders'>
						<th {if !$BLOCK@first} colspan="{count($RECORDMODELS)+1}"{/if}>
							{vtranslate($BLOCK->label, $MODULE)}
						</th>
						{if $BLOCK@first}
							{foreach item=RECORD from=$RECORDMODELS name=recordList}
								<th style="font-size: smaller">
									{vtranslate('LBL_CREATED_ON')} : {$RECORD->getDisplayValue('createdtime')}
									&nbsp;-&nbsp;{vtranslate('LBL_MODIFIED_ON')} : {$RECORD->getDisplayValue('modifiedtime')}
								</th>
							{/foreach}
						{/if}
					</tr>
					{foreach item=FIELD from=$BLOCK->fields}
						{if $FIELD->isEditable()}
						<tr class="records-value">
							<td>
								{vtranslate($FIELD->get('label'), $MODULE)}
							</td>
							{foreach item=RECORD from=$RECORDMODELS name=recordList}
								<td><label>
									{if $FIELD->get('uitype') == 33 }
										<input checked type=checkbox name="{$FIELD->getName()}[]"
										data-id="{$RECORD->getId()}" value="{$RECORD->get($FIELD->getName())}" style='bottom:1px;position:relative;'/>
									{else}
										<input type=radio {if $smarty.foreach.recordList.index eq 0}checked{/if} name="{$FIELD->getName()}"
										data-id="{$RECORD->getId()}" value="{$RECORD->get($FIELD->getName())}" style='bottom:1px;position:relative;'/>
									{/if}
									<span class="value" data-field-type="{$FIELD->getFieldDataType()}">
										{assign var=FIELD_NAME value=$FIELD->getName()}
										{if $FIELD->get('uitype') eq '15'}{* ED141005 *}
											{$RECORD->getDisplayValue($FIELD_NAME)}
										{else}
											{assign var=tmp value=$FIELD->set('fieldvalue', $RECORD->get($FIELD_NAME))}
											{include file=vtemplate_path($FIELD->getUITypeModel()->getDetailViewTemplateName(),$MODULE_NAME) FIELD_MODEL=$FIELD USER_MODEL=$USER_MODEL MODULE=$MODULE_NAME RECORD=$RECORD}
										{/if}
										{* &nbsp;&nbsp;{$RECORD->getDisplayValue($FIELD->getName())}*}
									</span>
								</label>
								</td>
							{/foreach}
						</tr>
						{/if}
					{/foreach}
				{/foreach}
				{foreach item=RELATED_MODULE from=$RELATED_MODULES name=modulesList}
					{if $smarty.foreach.modulesList.index eq 0}
						<tr class='listViewHeaders'>
							<td colspan="{count($RECORDMODELS)+1}">
								<h4 style="color: white">Modules</h4>
							</td>
						</tr>
					{/if}
					<tr>
						<td>
							{vtranslate($RELATED_MODULE, $RELATED_MODULE)}
						</td>
						{foreach item=RECORD from=$RECORDMODELS name=recordList}
							<td>
								{*ED151012*}
								{if $RECORD->get('_related_module_'|cat:$RELATED_MODULE)}
									{if $RECORD->get('_related_module_'|cat:$RELATED_MODULE) > 1}
										{$RECORD->get('_related_module_'|cat:$RELATED_MODULE)} {vtranslate($RELATED_MODULE, $RELATED_MODULE)}
									{else}
										{$RECORD->get('_related_module_'|cat:$RELATED_MODULE)} {vtranslate('SINGLE_'|cat:$RELATED_MODULE, $RELATED_MODULE)}
									{/if}
								{/if}
							</td>
						{/foreach}
					</tr>
				{/foreach}
			</tbody>
		</table>
	</div>
	<div class='row-fluid'>
		<div class="offset4">
			<button type=submit class='btn btn-success'>{vtranslate('LBL_MERGE', $MODULE)}</button>
		</div>
	</div>
	</form>
	<br>
</div>
{/strip}