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
		<h3 id="massEditHeader">{vtranslate('LBL_PRINT_RECU_FISCAL', $MODULE)}
			&nbsp;{vtranslate('LBL_FOR', $MODULE)} {$ASSIGNABLE_COUNTER} {if $ASSIGNABLE_COUNTER > 1}{vtranslate($MODULE, $MODULE)}{else}{vtranslate('SINGLE_'|cat:$MODULE, $MODULE)}{/if}</h3>
	</div>
	<form class="form-horizontal" id="massEdit" name="MassEdit" method="post" action="index.php">
		<input type="hidden" name="module" value="{$MODULE}" />
		<input type="hidden" name="relatedmodule" value="{$RELATED_MODULE}" />
		<input type="hidden" name="action" value="PrintRecuFiscal" />
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
						<label class="span10">
							&nbsp;<input type="radio" name="related_ids[]" value="{$RELATED_RECORD->getId()}" {if $smarty.foreach.relatedList.index === 1}checked="checked"{/if}/>
								{$RELATED_RECORD->getName()}
							{if $RELATED_RECORD->get('recufiscal_infos')}
								{assign var=INFOS value=$RELATED_RECORD->get('recufiscal_infos')}
								<i>&nbsp;(&eacute;dit&eacute; le {$INFOS['date_edition']}
								{if $INFOS['montant']}, {$INFOS['montant']} &euro;{/if}
								{if $INFOS['recu_fiscal_num']}, n¡ {$INFOS['recu_fiscal_num']}{/if}
								)</i>
							{/if}
						</label>
					</div>
				{/foreach}
			</div>
		</div>
		<div class="modal-footer">
			<input type="checkbox" id="toto" name="toto" value=/><label style="display:inline;" for="toto">G&eacute;n&eacute;rer un re&ccedil;u fiscal modificatif</label><br/>
			<div class=" pull-right cancelLinkContainer">
				<a class="cancelLink" type="reset" data-dismiss="modal">{vtranslate('LBL_CLOSE', $MODULE)}</a>
			</div>
			<button class="btn btn-success" type="submit" name="saveButton"><strong>{vtranslate('LBL_DOWNLOAD', $MODULE)}</strong></button>
			{if $IS_EMAIL_SENDABLE}
				<button class="btn btn-success" {* TODO *}disabled="disabled"{* TODO *} type="submit" name="sendEmail" value="1"><strong>{vtranslate('LBL_SEND_BY_EMAIL', $MODULE)}</strong></button>
			{/if}
		</div>
	</form>
</div>
{/strip}