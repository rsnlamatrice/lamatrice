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
		return parent::initializeListViewContents($request, $viewer);
	}
	
	/* ED150903 dans includes/main/WebUI.php function triggerPreProcess() conditionne le preProcess à !isAjax
	* Maintenant, on a besoin des folders dans tous les cas
	*/
	function initViewerFolders (Vtiger_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();
		$documentModuleModel = Vtiger_Module_Model::getInstance($moduleName);
		$defaultCustomFilter = $documentModuleModel->getDefaultCustomFilter();
		$folderList = Documents_Module_Model::getAllFolders();
		$viewer->assign('DEFAULT_CUSTOM_FILTER_ID', $defaultCustomFilter);
		$viewer->assign('FOLDERS', $folderList);
	}
    
}