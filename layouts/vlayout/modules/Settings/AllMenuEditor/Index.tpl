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
			<div class="span8"><h3>{vtranslate('LBL_ALLMENU_EDITOR', $QUALIFIED_MODULE)}</h3></div>
		</div>
		<hr>
		
		<div class="contents moreMenus">
			<form name="menuEditor" action="index.php" method="post" class="form-horizontal" id="menuEditor">
				<input type="hidden" name="module" value="{$MODULE_NAME}" />
				<input type="hidden" name="action" value="Save" />
				<input type="hidden" name="parent" value="Settings" />
				<input type="hidden" name="itemsPositions" />
				<div class="row-fluid paddingTop20">
					<ul class="allMenu" >
						{foreach key=PARENT_NAME item=MODULES_LIST from=$ALL_MODULES}
							<li class="menuParent" data-parent="{$PARENT_NAME}">
								<h4>{vtranslate("LBL_$PARENT_NAME", $QUALIFIED_MODULE)}</h4>
								<hr>
								<ul>
								{foreach key=MODULE_NAME item=MODULE_MODEL from=$MODULES_LIST}
									{assign var=TABID value=$MODULE_MODEL->getId()}
									<li class="menuItem" data-menu="{$TABID}">
										{vtranslate($MODULE_NAME, $MODULE_NAME)}</option>
								{/foreach}
								</ul>
							</li>
						{/foreach}
					</ul>
				</div>
				<div class="row-fluid paddingTop20">
				    <div class="span6">
					<button class="btn btn-success pull-right"
						type="submit" name="saveMenusList">
					    <strong>{vtranslate('LBL_SAVE', $QUALIFIED_MODULE)}</strong>
					</button>
				    </div>
				</div>
			</form>
		</div>	
	</div>
{/strip}