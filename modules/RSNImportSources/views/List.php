<?php

class RSNImportSources_List_View extends Import_List_View {
    
    /**
     * Method to get details about import specified in request parameters.
     * @param Vtiger_Request $request: the curent request.
     */
    public function getImportDetails(Vtiger_Request $request) {
        $viewer = $this->getViewer($request);
		$moduleName = $request->get('for_module');
        $user = Users_Record_Model::getCurrentUserModel();
        $importRecords= RSNImportSources_Data_Action::getImportDetails($user, $moduleName, $request->get('type'));
        $viewer->assign('IMPORT_RECORDS', $importRecords);
        $viewer->assign('TYPE', $request->get('type'));
		$viewer->assign('MODULE', $moduleName);
        $viewer->view('ImportDetails.tpl', 'Import');//TODO: do not call template from Import Module
    }

    public function getHeaderCss(Vtiger_Request $request) {
	    $headerCssInstances = parent::getHeaderCss($request);

	    $cssFileNames = array(
		    '~/layouts/vlayout/modules/RSNImportSources/resources/css/style.css',
	    );
	    $cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
	    $headerCssInstances = array_merge($headerCssInstances, $cssInstances);

	    return $headerCssInstances;
    }
}