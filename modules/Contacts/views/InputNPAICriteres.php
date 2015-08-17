<?php

/* +***********************************************************************************
 * ED150813
 * *********************************************************************************** */

class Contacts_InputNPAICriteres_View extends Vtiger_Edit_View {

	public function process(Vtiger_Request $request) {
		
		$moduleName = $request->getModule();
		
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('InputNPAICriteres.tpl', $moduleName);
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			"modules.$moduleName.resources.InputNPAICriteres",
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

}