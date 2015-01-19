<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Critere4D_Detail_View extends Vtiger_Detail_View {

	function __construct() {
		parent::__construct();
	}
	
	
	
	function preProcess(Vtiger_Request $request, $display=true) {
		
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		
		$viewer = $this->getViewer($request);
		
		$relationListModel = Vtiger_RelationListView_Model::getInstance($recordModel, "Contacts");
		$nbApplications = $relationListModel->getRelatedEntriesCount();
		$viewer->assign('CONTACTS_COUNT', $nbApplications);
		//echo $nbApplications === 0 ? '' : ($nbApplications . ' application' . ($nbApplications === 1 ? '' : 's'));
		
		parent::preProcess($request, $display);
	}
	
	
	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	public function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
				'modules.Vtiger.resources.List',
				"modules.$moduleName.resources.List",
				'modules.CustomView.resources.CustomView',
				"modules.$moduleName.resources.CustomView",
				"modules.Emails.resources.MassEdit",
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}


	public function getHeaderCss(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$parentCSSScripts = parent::getHeaderCss($request);
		$styleFileNames = array(
			"~/layouts/vlayout/modules/$moduleName/resources/css/style.css",
			);
		$cssScriptInstances = $this->checkAndConvertCssStyles($styleFileNames);
		$headerCSSScriptInstances = array_merge($parentCSSScripts, $cssScriptInstances);
		return $headerCSSScriptInstances;
	}

	
}