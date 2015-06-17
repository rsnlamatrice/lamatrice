<?php

class RSNImportSources_Detail_View extends Vtiger_Detail_View {

    /**
     * Method to get details about import specified in request parameters.
     * @param Vtiger_Request $request: the curent request.
     */
    public function getImportDetails(Vtiger_Request $request) {
        $viewer = $this->getViewer($request);
		$moduleName = $request->get('for_module');
        $user = Users_Record_Model::getCurrentUserModel();
        $importRecords= RSNImportSources_Data_Action::getImportDetails($user, $moduleName);
        $viewer->assign('IMPORT_RECORDS', $importRecords);
        $viewer->assign('TYPE',$request->get('type'));
		$viewer->assign('MODULE', $moduleName);
        $viewer->view('ImportDetails.tpl', 'Import');//TODO: do not call template from Import Module
    }
}