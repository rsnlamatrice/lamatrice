<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

//Coming after FindDuplicates and MergeRecord
class Vtiger_ProcessDuplicates_Action extends Vtiger_Action_Controller {
	
	function checkPermission(Vtiger_Request $request) {
		$module = $request->getModule();
		$records = $request->get('records');
		if($records) {
			foreach($records as $record) {
				$recordPermission = Users_Privileges_Model::isPermitted($module, 'EditView', $record);
				if(!$recordPermission) {
					throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
				}
			}
		}
	}

	function process (Vtiger_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$records = $request->get('records');
		$primaryRecord = $request->get('primaryRecord');
		$primaryRecordModel = Vtiger_Record_Model::getInstanceById($primaryRecord, $moduleName);

		//Affectation des nouvelles valeurs
		$fields = $moduleModel->getMergeableFields();//ED150910 getMergeableFields instead of getFields
		foreach($fields as $field) {
			$fieldValue = $request->get($field->getName());
			if($field->isEditable()) {
				//ED150910
				if(is_array($fieldValue)){
					switch($field->get('uitype')){
						case 33 :
							$fieldValue = clean_pickList_values_string($fieldValue);
							break;
						default :
							$fieldValue = implode(',', $fieldValue);
							break;
					}
				}
				elseif($moduleName == 'Contacts' && $field->getName() === 'description'
				       && $primaryRecordModel->get($field->getName())){
					$fieldValue = $primaryRecordModel->get($field->getName()) . "\r\n" . $fieldValue;
				}
				$primaryRecordModel->set($field->getName(), $fieldValue);
			}
		}
		$primaryRecordModel->set('mode', 'edit');
		$primaryRecordModel->save();

		$deleteRecords = array_diff($records, array($primaryRecord));
		foreach($deleteRecords as $deleteRecord) {
			$recordPermission = Users_Privileges_Model::isPermitted($moduleName, 'Delete', $deleteRecord);
			if($recordPermission) {
				$primaryRecordModel->transferRelationInfoOfRecords(array($deleteRecord));
				$record = Vtiger_Record_Model::getInstanceById($deleteRecord);
				$record->delete();
			}
		}

		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}