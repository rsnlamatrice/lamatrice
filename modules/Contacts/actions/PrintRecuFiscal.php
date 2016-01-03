<?php
/*+***********************************************************************************
 * ED151105
 *************************************************************************************/

class Contacts_PrintRecuFiscal_Action extends Accounts_PrintRecuFiscal_Action {
	
	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		if(!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'Save')) {
			throw new AppException(vtranslate($moduleName).' '.vtranslate('LBL_NOT_ACCESSIBLE'));
		}
	}

	public function process(Vtiger_Request $request) {
		$notes_ids = $request->get('related_ids');
		$asColumnName = 'contactid';
		$sourceIdsQuery = $this->getRecordsQueryFromRequest($request, $asColumnName);
		$documentRecordModel = Vtiger_Record_Model::getInstanceById($notes_ids[0], 'Documents');
		
		$this->generatePDF($request, $sourceIdsQuery, $documentRecordModel);
	}
}
