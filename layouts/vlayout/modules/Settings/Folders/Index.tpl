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
	<div class="container-fluid" id="foldersContainer">
		<div class="widget_header row-fluid">
			<div class="span8"><h3>{vtranslate('LBL_DOCUMENTS_FOLDERS', $QUALIFIED_MODULE)}</h3></div>
		</div>
		<hr>
		

<div class="tab-content layoutContent padding20 themeTableColor overflowVisible">
	<div class="tab-pane active" id="allValuesLayout">	
		<div class="row-fluid" style="max-width: 40em">
			<form name="folders" action="index.php" method="post" class="form-horizontal" id="folders">
				<input type="hidden" name="module" value="{$MODULE_NAME}" />
				<input type="hidden" name="action" value="Save" />
				<input type="hidden" name="parent" value="Settings" />
			
				<table id="pickListValuesTable" class="table table-bordered table-condensed table-striped" style="table-layout: fixed">
					<tbody>
					{assign var=FOLDER_INDEX value=0}
					{foreach item=FOLDER from=$ALL_FOLDERS}
						{assign var=INPUT_ID value='folder-'|cat:$FOLDER_INDEX|cat:'-uicolor'}
						<tr class="pickListValue cursorPointer" data-key="{Vtiger_Util_Helper::toSafeHTML($FOLDER.folderid)}">
							<td class="textOverflowEllipsis">
							<div class="pull-left">
								<input type="hidden" name="folderid[]" value="{$FOLDER.folderid}"/>
								<input type="hidden" name="sequence[]" value="{$FOLDER_INDEX}"/>
								<input type="hidden" name="deleted[]" value=""/>
								<input type="hidden" name="changed[]" value=""/>
								<img class="alignMiddle" src="{vimage_path('drag.png')}"/>&nbsp;&nbsp;
							</div>
							<div class="pull-left">
								<input name="foldername[]" value="{Vtiger_Util_Helper::toSafeHTML($FOLDER["foldername"])}"
									{if ($FOLDER["foldername"] eq 'Default')} type="hidden"{/if}
									class="nameField"
									/>
								{if ($FOLDER["foldername"] eq 'Default')}<span style="width: 12em; ">Dossier par défaut</span>{/if}
								<a class="notes-counter" style="font-size:smaller; padding-left: 6px;">{if $FOLDER["notes_counter"]}{$FOLDER["notes_counter"]} document{if $FOLDER["notes_counter"] > 1}s{/if}{else}vide{/if}</a>
								<br><span class="folder--description span4" name="description[]" style="padding-left: 4px; font-style: italic;">
								{if $FOLDER["description"]}{Vtiger_Util_Helper::toSafeHTML($FOLDER["description"])}
								{else}<span style="color:#CCCCCC">&nbsp;...&nbsp;</span>
								{/if}</span>
								<textarea class="span4 hide" name="description[]">{Vtiger_Util_Helper::toSafeHTML($FOLDER["description"])}</textarea>
							</div>
							
							<div class="pull-right actions" style="width: 2em; padding-top:2px;">
								<span class="actionImages hide">
								{if $FOLDER["foldername"] neq 'Default'}
								    <a title="{vtranslate('LBL_DELETE', $MODULE)}" class="icon-trash alignMiddle"></a>
								    <a title="{vtranslate('LBL_DUPLICATE', $MODULE)}" class="icon-plus alignMiddle"></a>
								{/if}
								</span>
								&nbsp;
							</div>
							
							<div class="picklist-color pull-right" style="margin-right: 3em;">
								{if $FOLDER["uicolor"]}
									{assign var=UICOLOR value=$FOLDER["uicolor"]}
								{else}
									{assign var=UICOLOR value=''}
								{/if}
								<input type="hidden" class="colorField"
								id="{$INPUT_ID}"
								name="uicolor[]" value="{$UICOLOR}"/>
								<div id="{$INPUT_ID}-colorSelector" class="colorpicker-holder"><div style="background-color: {$UICOLOR}"></div></div>
							</div>
							</td>
						</tr>
						{assign var=FOLDER_INDEX value=$FOLDER_INDEX + 1}
						{/foreach}
					</tbody>	
				</table>
				
				<div class="row-fluid paddingTop20">
				    <div class=" span6">
					<button class="btn btn-success pull-right hide"
						type="submit" name="saveFoldersList">
					    <strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong>
					</button>
				    </div>
				</div>
			</form>
		</div>	
	</div>
{/strip}