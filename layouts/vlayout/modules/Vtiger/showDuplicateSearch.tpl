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
<div class='modelContainer'>
	<div class="modal-header contentsBackground">
        <button data-dismiss="modal" class="close" title="{vtranslate('LBL_CLOSE')}">&times;</button>
		<h3>{vtranslate('LBL_MERGING_CRITERIA_SELECTION', $MODULE)}</h3>
	</div>
	<form class="form-horizontal" id="findDuplicate" action="index.php">
		<input type='hidden' name='module' value='{$MODULE}' />
		<input type='hidden' name='view' value='FindDuplicates' />
		
		{* ED150626 *}
            <input type="hidden" name="viewname" value="{$VIEWID}" />
            <input type="hidden" name="selected_ids" value={ZEND_JSON::encode($SELECTED_IDS)}>
            <input type="hidden" name="excluded_ids" value={ZEND_JSON::encode($EXCLUDED_IDS)}>
            <input type="hidden" id="page" name="page" value="{$PAGE}" />
            <input type="hidden" name="search_key" value= "{$SEARCH_KEY}" />
            <input type="hidden" name="operator" value="{$OPERATOR}" />
            <input type="hidden" name="search_value" value="{$ALPHABET_VALUE}" />
		<br>
        <div class="row-fluid">
			<div class="span">&nbsp;</div>
			<div class="span10">
				<h4>Enregistrements à tester</h4>
				<div class="well exportContents marginLeftZero">
					<div class="row-fluid">
						{if !empty($SELECTED_IDS)}
						<div class="row-fluid" style="height:30px">
							<label><input type="radio" name="source_ids" value="FromSelectedRecords" checked="checked"/>
							&nbsp;Depuis les enregistrements sélectionnés
							</label>
							{if empty($SELECTED_IDS)}&nbsp; <span class="redColor">{vtranslate('LBL_NO_RECORD_SELECTED',$MODULE)}</span>{/if}
						</div>
						{/if}
						<div class="row-fluid" style="height:30px">
							<label><input type="radio" name="source_ids" value="FromCurrentPage" {if empty($SELECTED_IDS)} checked="checked" {/if} />
							&nbsp;Depuis la page courante
							</label>
						</div>
						<div class="row-fluid" style="height:30px">
							<label><input type="radio"  name="source_ids" value="FromAllView" />
							&nbsp;Depuis toutes les données de la vue
							</label>
						</div>
					</div>
				</div>
				<h4>Comparer à</h4>
				<div class="well exportContents marginLeftZero">
					<div class="row-fluid">
						{if !empty($SELECTED_IDS)}
						<div class="row-fluid" style="height:30px">
							<label><input type="radio" name="among_ids" value="AmongSelectedRecords"/>
							&nbsp;Parmi les enregistrements sélectionnés
							</label>
							{if empty($SELECTED_IDS)}&nbsp; <span class="redColor">{vtranslate('LBL_NO_RECORD_SELECTED',$MODULE)}</span>{/if}
						</div>
						{/if}
						<div class="row-fluid" style="height:30px">
							<label><input type="radio" name="among_ids" value="AmongCurrentPage" />
							&nbsp;Parmi la page courante
							</label>
						</div>
						<div class="row-fluid" style="height:30px">
							<label><input type="radio"  name="among_ids" value="AmongAllView"
										   checked="checked" />
							&nbsp;Depuis toutes les données de la vue
							</label>
						</div>
						<div class="row-fluid" style="height:30px">
							<label><input type="radio"  name="among_ids" value="AmongAllDB"/>
							&nbsp;Parmi toute la base de données (très long !)
							</label>
						</div>
					</div>
				</div>
			</div>
		</div>
		<br>
        <div class="row-fluid">
			<div class="span">&nbsp;</div>
			<div class="span10 control-group">
				<h4>Champs de comparaison</h4>
				<div class="well exportContents marginLeftZero controls">
					<div class="row-fluid">
						<span class="span6">
							<select id="fieldList" class="select2 row-fluid" multiple="true" name="fields[]"
								data-validation-engine="validate[required]">
								{foreach from=$FIELDS item=FIELD}
									{if $FIELD->isViewableInDetailView()}
										<option value="{$FIELD->getName()}">{vtranslate($FIELD->get('label'), $MODULE)}</option>
									{/if}
								{/foreach}
							</select>
						</span>
					</div>
					<div class="row-fluid">
						<label><input type="checkbox" name="ignoreEmpty" checked /><span class="alignMiddle">&nbsp;{vtranslate('LBL_IGNORE_EMPTY_VALUES', $MODULE)}</span></label>
					</div>
				</div>
			</div>
		</div>
		<br>
		<div class="modal-footer">
			<div class="pull-right cancelLinkContainer">
				<a class="cancelLink" type="reset" data-dismiss="modal" data-dismiss="modal">{vtranslate('LBL_CANCEL', $MODULE)}</a>
			</div>
			<button class="btn btn-success" type="submit" disabled="true">
				<strong>{vtranslate('LBL_FIND_DUPLICATES', $MODULE)}</strong>
			</button>
		</div>
	</form>
</div>
{/strip}