{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/
-->*}
{strip}
	<div class="container-fluid" id="menuEditorContainer">
		<div class="widget_header row-fluid">
			<div class="span8"><h3>{vtranslate('LBL_MENU_EDITOR', $QUALIFIED_MODULE)}</h3></div>
		</div>
		<hr>
		
		<div class="contents">
			<form name="menuEditor" action="index.php" method="post" class="form-horizontal" id="menuEditor">
				<input type="hidden" name="module" value="{$MODULE_NAME}" />
				<input type="hidden" name="action" value="Save" />
				<input type="hidden" name="parent" value="Settings" />
				<div class="row-fluid">
					<div class="span2" style="margin-top: 6px;">Pour le rôle&nbsp;&nbsp;</div>
					<div class="span8"><select id="roleselector" name="roleid">
						<option value="" {if !$ROLEID }selected="selected"{/if}></option>
						{if !$CURRENT_ROLE}{assign var=CURRENT_ROLE value='H1'}{/if}
						{foreach key=ROLEID item=ROLE from=$ALL_ROLES}
						<option value="{$ROLE->get('roleid')}" {if $ROLEID eq $CURRENT_ROLE}selected="selected"{/if}>{$ROLE->get('rolename')}</option>
						{/foreach}
					</select></div>
				</div>
				<div class="row-fluid paddingTop20">
					{assign var=SELECTED_MODULE_IDS value=array()}
					
					<select data-placeholder="{vtranslate('LBL_ADD_MENU_ITEM',$QUALIFIED_MODULE)}" id="menuListSelectElement" class="select2 span12" multiple="" data-validation-engine="validate[required]" >
						{foreach key=SELECTED_MODULE item=MODULE_MODEL from=$SELECTED_MODULES}
							{array_push($SELECTED_MODULE_IDS, $MODULE_MODEL->getId())}
						{/foreach}
						
						{foreach key=PARENT_NAME item=MODULES_LIST from=$ALL_MODULES}
							<optgroup label='{vtranslate("LBL_$PARENT_NAME", $QUALIFIED_MODULE)}'>
								{foreach key=MODULE_NAME item=MODULE_MODEL from=$MODULES_LIST}
									{assign var=TABID value=$MODULE_MODEL->getId()}
									<option value="{$TABID}" {if in_array($TABID, $SELECTED_MODULE_IDS)} selected {/if}>
										{vtranslate($MODULE_NAME, $MODULE_NAME)}</option>
								{/foreach}
							</optgroup>
						{/foreach}
					</select>
				</div>
				<div class="row-fluid paddingTop20">
				    <div class=" span6">
					<button class="btn btn-success pull-right {if !$DUPLICATE_ROLE || ($DUPLICATE_ROLE eq $CURRENT_ROLE)}hide{/if}"
						type="submit" name="saveMenusList">
					    <strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong>
					</button>
				    </div>
				</div>
				<input type="hidden" name="selectedModulesList" value='' />
				<input type="hidden" name="topMenuIdsList" value='{ZEND_JSON::encode($SELECTED_MODULE_IDS)}' />
			</form>
			
			{* ED141226 *}
			<div class="row-fluid paddingTop20">
				<div class=" span2" style="margin-top: 6px;">Dupliquer depuis le rôle&nbsp;&nbsp;</div>
				<div class="span8"><select id="duplicateroleselector" name="duplicate-roleid">
					{if !$DUPLICATE_ROLE}{assign var=DUPLICATE_ROLE value=$CURRENT_ROLE}{/if}
					{foreach key=ROLEID item=ROLE from=$ALL_ROLES}
					<option value="{$ROLE->get('roleid')}" {if $ROLEID eq $DUPLICATE_ROLE}selected="selected"{/if}>{$ROLE->get('rolename')}</option>
					{/foreach}
				</select>
				&nbsp;&nbsp;<a id="duplicaterole-reload" class="hide" href="">recharger pour pouvoir enregistrer</a></div>
			</div>
		</div>	
	</div>
{/strip}