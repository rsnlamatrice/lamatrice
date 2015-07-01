<?php
/*+**********************************************************************************
 * ED150630
 ************************************************************************************/

class Services_List_View extends Vtiger_List_View {

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 *
	 * ED150630 : ajoute Products.Edit.js avant Services.Products.js
	 */
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();
		$inheritedModuleName = 'Products';

		$jsFileNames = array(
			"modules.$inheritedModuleName.resources.Edit",
			"modules.$moduleName.resources.Edit",
		);
		//Supprime le module Services pour mieux l'ajouter
		unset($headerScriptInstances["modules.$moduleName.resources.Edit"]);
		
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}