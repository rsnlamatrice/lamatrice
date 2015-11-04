<?php
/*+***********************************************************************************
 * ABANDON
 *************************************************************************************/

class RsnPrelevements_PrintRemerciements_View extends Vtiger_Index_View {
	
	function preProcess(Vtiger_Request $request) {
		return true;
	}

	function postProcess(Vtiger_Request $request) {
		return true;
	}
	
	public function process(Vtiger_Request $request) {
		
		$moduleName = $request->get('module');
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		
		$dateVir = $moduleModel->getNextDateToGenerateVirnts($request->get('date_virements'));
		
		$prelVirements = $moduleModel->getExistingPrelVirements( $dateVir, 'FIRST' );
		
		$senderRecord = Vtiger_CompanyDetails_Model::getInstanceById();
		
		$viewer = $this->getViewer($request);
		
		$viewer->assign('MODULE', $moduleName);
		
		$viewer->assign('RECORDS', $prelVirements);

		$viewer->assign('SENDER_RECORD', $senderRecord);
		
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('VIEW', $request->get('view'));

		
		echo $viewer->view('PrintRemerciements.tpl', $moduleName, 'true');
	}
}
