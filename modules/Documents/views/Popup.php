<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Documents_Popup_View extends Vtiger_Popup_View {
	
	/*
	 * Function to initialize the required data in smarty to display the List View Contents
	 */
	public function initializeListViewContents(Vtiger_Request $request, Vtiger_Viewer $viewer) {
		$this->initViewerFolders ($request);
		parent::initializeListViewContents($request, $viewer);
	}
	
	/* ED150903 dans includes/main/WebUI.php function triggerPreProcess() conditionne le preProcess à !isAjax
	* Maintenant, on a besoin des folders dans tous les cas
	*/
	function initViewerFolders (Vtiger_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();
		$documentModuleModel = Vtiger_Module_Model::getInstance($moduleName);
		$defaultCustomFilter = $documentModuleModel->getDefaultCustomFilter();
		$viewer->assign('DEFAULT_CUSTOM_FILTER_ID', $defaultCustomFilter);
		
		$searchKeys = $request->get('search_key');
		if(in_array('folderid', $searchKeys)){
			foreach($searchKeys as $index => $searchKey)
				if($searchKey === 'folderid'){
					$folderName = decode_html($request->get('search_value')[$index]);
					$viewer->assign('FOLDER_NAME', $folderName);
					break;
				}
			if(count($searchKeys) === 1){
				$searchValues = $request->get('search_value');
				$searchOperators = $request->get('operator');
				$searchInputs = $request->get('search_input');
				$searchKeys[] = 'notes_title';
				$searchValues[] = '';
				$searchOperators[] = 's';
				$searchInputs[] = '';
				$request->set('search_key', $searchKeys);
				$request->set('search_value', $searchValues);
				$request->set('operator', $searchOperators);
				$request->set('search_input', $searchInputs);
			}
			$folderList = Documents_Module_Model::getAllFolders($folderName);
		}
		else{
			$folderList = Documents_Module_Model::getAllFolders();
		}
		$viewer->assign('FOLDERS', $folderList);
	}
    
}