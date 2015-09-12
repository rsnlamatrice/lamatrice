<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Class Documents_Edit_View extends Vtiger_Edit_View {
	
	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);

		$moduleName = $request->getModule();

		$jsFileNames = array(
				"libraries.jquery.ckeditor.ckeditor",
				"libraries.jquery.ckeditor.adapters.jquery",
				'modules.Vtiger.resources.CkEditor',
		);
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	/** ED150912
	 * Intercepte les valeurs par défaut
	 */
	public function process(Vtiger_Request $request) {
	    
		$fieldName = 'codeaffaire';
		$value = $request->get($fieldName);
	    if($value)
			$request->set($fieldName, strtoupper($value));
		
		$fieldName = 'folderid';
		$value = $request->get($fieldName);
	    if($value && !is_numeric($value)){
			$document = Documents_Folder_Model::getInstanceByName($value);
			$request->set($fieldName, $document->getId());
	    }
		
		return parent::process($request);
	}
}
?>
