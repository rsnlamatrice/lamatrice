<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Inventory_ServicesPopup_View extends Vtiger_Popup_View {

	/**
	 * Function returns module name for which Popup will be initialized
	 * @param type $request
	 */
	function getModule($request) {
		return 'Services';
	}

	/*
	 * Function to initialize the required data in smarty to display the List View Contents
	 */
	public function initializeListViewContents(Vtiger_Request $request, Vtiger_Viewer $viewer) {
		//src_module value is added just to stop showing inactive services
		$request->set('src_module', $request->getModule());

		parent::initializeListViewContents($request, $viewer);
		$viewer->assign('MODULE', $request->getModule());
		$viewer->assign('GETURL', 'getTaxesURL');
		$viewer->assign('VIEW', 'ServicesPopup');
	}
	
	 /**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);

		$jsFileNames = array('modules.Inventory.resources.Popup');
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

		return $headerScriptInstances;
	}


}