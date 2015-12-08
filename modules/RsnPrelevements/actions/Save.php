<?php
/*+***********************************************************************************
 * 
 *************************************************************************************/

class RsnPrelevements_Save_Action extends Vtiger_Save_Action {

	/**
	 * Function to save record
	 * @param <Vtiger_Request> $request - values of the record
	 * @return <RecordModel> - record Model of saved record
	 */
	public function saveRecord($request) {
		$recordModel = parent::saveRecord($request);
		
		if( ! $request->get('record') && $request->get('isDuplicateFrom') ) {
			$duplicateFrom = $request->get('isDuplicateFrom');
			$originalRecordModel = Vtiger_Record_Model::getInstanceById($duplicateFrom, $recordModel->getModuleName());
			if($originalRecordModel->get('etat') == 0){
				$originalRecordModel->set('mode', 'edit');
				$originalRecordModel->set('etat', 2);
				$originalRecordModel->save();
			}
		}
		return $recordModel;
	}
}
