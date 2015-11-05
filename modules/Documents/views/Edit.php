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
		
		//Code affaire
		$fieldName = 'codeaffaire';
		$value = $request->get($fieldName);
	    if($value)
			$request->set($fieldName, strtoupper($value));
		
		//Dossier par défaut
		$fieldName = 'folderid';
		$folder = $request->get($fieldName);
	    $isRelationOperation = $request->get('relationOperation');
		if(!$folder && $isRelationOperation){
			if($request->get('sourceModule') === 'Contacts')
				$folder = '(Gestion des adresses)';
			elseif(substr($request->get('sourceModule'), 0, strlen('RSNMedia')) === 'RSNMedia')
				$folder = '(Presse-Média)';
		}
	    if($folder && !is_numeric($folder)){
			$document = Documents_Folder_Model::getInstanceByName($folder);
			if($document)
				$request->set($fieldName, $document->getId());
	    }
		
		return parent::process($request);
	}
}
?>