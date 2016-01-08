<?php
/*+***********************************************************************************
 * ED160108
 *************************************************************************************/

class Contacts_Delete_Action extends Vtiger_Delete_Action {

	public function process(Vtiger_Request $request) {
		if($request->get('mode') === 'tagAsDeleted'){
			return $this->tagAsDeleted($request);
		}
		return parent::process($request);
	}
	
	
	public function tagAsDeleted(Vtiger_Request $request){
		
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$ajaxDelete = $request->get('ajaxDelete');
		
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		$recordModel->set('mode', 'edit');
		$recordModel->set('firstname', trim($recordModel->get('firstname') . ' ' . $recordModel->get('lastname')));
		$recordModel->set('lastname', '[SUPPRIMÉ(E)]');
		$recordModel->set('contacttype', 'SUPPRIMÉ');
		$recordModel->set('mailingzip', '');
		$recordModel->set('mailingcity', '');
		$recordModel->set('rsnpai', 4);
		/* "ne pas" partout */
		foreach( array('emailoptout', 'donotcall', 'donotprospect', 'donotrelanceadh', 'donotappeldoncourrier', 'donotrelanceabo', 'donotappeldonweb')
				as $fieldName){
			$recordModel->set($fieldName, 1);
		}
		$recordModel->save();
		
		$accountRecordModel = $recordModel->getAccountRecordModel(false);
		if($accountRecordModel && count($accountRecordModel->getContactsRecordModels()) === 1){
			$accountRecordModel->set('mode', 'edit');
			$accountRecordModel->set('accountname', trim($accountRecordModel->get('accountname') . ' [SUPPRIMÉ(E)]'));
			$accountRecordModel->save();
		}
		
		if($ajaxDelete) {
			$detailViewUrl = $recordModel->getDetailViewUrl();
			$response = new Vtiger_Response();
			$response->setResult($detailViewUrl);
			return $response;
		} else {
			$moduleModel = $recordModel->getModule();
			$listViewUrl = $moduleModel->getListViewUrl();
			header("Location: $listViewUrl");
		}
	}
}
