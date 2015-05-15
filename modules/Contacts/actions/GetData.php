<?php
/*+***********************************************************************************
 * ED150515
 * 
 *************************************************************************************/

class Contacts_GetData_Action extends Vtiger_GetData_Action {

	public function process(Vtiger_Request $request) {
		$sourceModule = $request->get('source_module');
		if($sourceModule == 'Accounts'){
			//Add reference contact information
			$record = $request->get('record');
			$recordModel = Vtiger_Record_Model::getInstanceById($record, $sourceModule);
			$contacts = $recordModel->getAccountMainContact();
			$response = parent::process($request, array('MainContacts' => $contacts));
		}
		else
			$response = parent::process($request);
	}
}
