<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Services_Detail_View extends Products_Detail_View {
	
	public function getHeaderScripts(Vtiger_Request $request) {
		parent::getHeaderScripts($request);
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();
		$modulePopUpFile = 'modules.'.$moduleName.'.resources.Edit';
		unset($headerScriptInstances[$modulePopUpFile]);
		$modulePopUpFile = 'modules.'.$moduleName.'.resources.Detail';
		unset($headerScriptInstances[$modulePopUpFile]);
		$modulePopUpFile = 'modules.'.$moduleName.'.resources.RelatedList';
		unset($headerScriptInstances[$modulePopUpFile]);


		$jsFileNames = array(
				'modules.Products.resources.Edit',
				'modules.Products.resources.Detail',
				'modules.Products.resources.RelatedList',
				"modules.$moduleName.resources.Edit",
				"modules.$moduleName.resources.Detail",
				"modules.$moduleName.resources.RelatedList",
		);
		$jsFileNames[] = $modulePopUpFile;

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}
