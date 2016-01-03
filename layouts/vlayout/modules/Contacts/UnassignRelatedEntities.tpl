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
<div id="massEditContainer" class='modelContainer'>
	<div class="modal-header contentsBackground">
		<button type="button" class="close " data-dismiss="modal" aria-hidden="true">&times;</button>
		<h3 id="massEditHeader">{if count($RELATED_ENTRIES) > 1}{vtranslate('LBL_UNASSIGN_'|cat:$RELATED_MODULE|cat:'_MULTI', $MODULE)}{else}{vtranslate('LBL_UNASSIGN_'|cat:$RELATED_MODULE, $MODULE)}{/if}
			&nbsp;{vtranslate('LBL_IN_RELATION_WITH', $MODULE)} {$UNASSIGNABLE_COUNTER} {if $UNASSIGNABLE_COUNTER > 1}{vtranslate($MODULE, $MODULE)}{else}{vtranslate('SINGLE_'|cat:$MODULE, $MODULE)}{/if}</h3>
	</div>
	<form class="form-horizontal" id="massEdit" name="MassEdit" method="post" action="index.php">
		<input type="hidden" name="module" value="{$MODULE}" />
		<input type="hidden" name="relatedmodule" value="{$RELATED_MODULE}" />
		<input type="hidden" name="action" value="UnassignRelatedEntities" />
		<input type="hidden" name="viewname" value="{$CVID}" />
		<input type="hidden" name="selected_ids" value={ZEND_JSON::encode($SELECTED_IDS)}>
		<input type="hidden" name="excluded_ids" value={ZEND_JSON::encode($EXCLUDED_IDS)}>
        <input type="hidden" name="search_key" value= "{$SEARCH_KEY}" />
        <input type="hidden" name="operator" value="{$OPERATOR}" />
        <input type="hidden" name="search_value" value="{$ALPHABET_VALUE}" />
        
		<div name='massEditContent'>
			<div class="modal-body">
				{foreach key=RELATED_KEY item=RELATED_RECORD from=$RELATED_ENTRIES name=relatedList}
					<div class="row-fluid">
						<label class="span3"><input type="hidden" name="related_ids[]" value="{$RELATED_RECORD->getId()}"/>{$RELATED_RECORD->getName()}</label>
						<span class="span6">
							<label style="display: inline-block"><input type="radio" name="use_dateapplication_{$RELATED_KEY}" value="0" checked="checked"/>&nbsp;quelque soit la date</label>
							<br>
							<label style="display: inline-block"><input type="radio" name="use_dateapplication_{$RELATED_KEY}" value="1"/>&nbsp;à la date du&nbsp;</label>
								<input type="text" name="dateapplication[]" value="{$CURRENT_DATE}" class="input-medium" />
						</span>
					</div>
					{if !$smarty.foreach.relatedList.last}
						<br/>
						<hr/>
					{/if}
				{/foreach}
				<!--div class="row-fluid">
					<label class="span2">{vtranslate('Comment')} :</label>
					<span class="span6"><textarea name="comment" class="input-xxlarge"/></span>
				</div-->
			</div>
		</div>
		{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
	</form>
</div>
{/strip}