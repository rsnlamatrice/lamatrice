<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class RsnReglements_MassSave_Action extends Vtiger_MassSave_Action {

	public function __construct() {
		parent::__construct();
		$this->exposeMethod('enCoursStatusToValidated');
	}
	
	public function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
		
		$moduleName = $request->getModule();
		$recordModels = $this->getRecordModelsFromRequest($request);

		foreach($recordModels as $recordId => $recordModel) {
			if(Users_Privileges_Model::isPermitted($moduleName, 'Save', $recordId)) {
				//Inventory line items getting wiped out
				$_REQUEST['action'] = 'MassEditSave';
				$recordModel->save();
			}
		}

		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}

	/**
	 * Function to get the record model based on the request parameters
	 * @param Vtiger_Request $request
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	public function getRecordModelsFromRequest(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$recordIds = $this->getRecordsListFromRequest($request);
		$recordModels = array();

		$fieldModelList = $moduleModel->getFields();
		foreach($recordIds as $recordId) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleModel);
			//ED151202 verrouillage
			if($recordModel->get('sent2compta')
			|| $recordModel->get('reglementstatus') === 'Comtpa')
				continue;
			
			$recordModel->set('id', $recordId);
			$recordModel->set('mode', 'edit');

			foreach ($fieldModelList as $fieldName => $fieldModel) {
				$fieldValue = $request->get($fieldName, null);
				$fieldDataType = $fieldModel->getFieldDataType();

				if($fieldDataType == 'time') {
					$fieldValue = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldValue);
				} else if($fieldDataType === 'date') {
					$fieldValue = $fieldModel->getUITypeModel()->getDBInsertValue($fieldValue);
				}

				if(isset($fieldValue) && $fieldValue != null && !is_array($fieldValue)) {
					$fieldValue = trim($fieldValue);
					$recordModel->set($fieldName, $fieldValue);
				}
			}
			$recordModels[$recordId] = $recordModel;
		}
		return $recordModels;
	}


	/**
	 * Changement du statut des des factures en cours en status Validée
	 */
	public function enCoursStatusToValidated(Vtiger_Request $request){
		//Ajout du filtre "invoicestatus = 'Created'
		$searchKey = $request->get('search_key');
		$searchValue = $request->get('search_value');
		$operator = $request->get('operator');
		if($searchKey){
			if(!is_array($searchKey)){
				$searchKey = array($searchKey);
				$searchValue = array($searchValue);
				$operator = array($operator);
			}
		}
		else{
			$searchKey = array();
			$searchValue = array();
			$operator = array();
		}
		$searchKey[] = 'reglementstatus';
		$searchValue[] = 'Created';
		$operator[] = 'e';
		
		$request->set('search_key', $searchKey);
		$request->set('search_value', $searchValue);
		$request->set('operator', $operator);
		
		$moduleName = $request->getModule();
		$recordIds = $this->getRecordsListFromRequest($request);
		$ids = array();
		foreach($recordIds as $recordId) {
			if(Users_Privileges_Model::isPermitted($moduleName, 'Save', $recordId)) {
				$ids[] = $recordId;
				/* la méthode Save fout le bordel 
				 *$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
				if($recordModel->get('invoicestatus') === 'Created'){
					$recordModel->set('mode', 'edit');
					$recordModel->set('invoicestatus', 'Validated');
					$recordModel->save();
				}*/
			}
		}
		
		$response = new Vtiger_Response();
		if($ids){
			global $adb;
			$query = 'UPDATE vtiger_rsnreglements
				JOIN vtiger_crmentity
					ON vtiger_rsnreglements.rsnreglementsid = vtiger_crmentity.crmid
				SET reglementstatus = ?
				, modifiedtime = NOW()
				WHERE reglementstatus = ?
				AND vtiger_crmentity.deleted = 0
				AND vtiger_crmentity.crmid IN ('.generateQuestionMarks($ids).')
			';
			$params = array_merge(
				array(
					'Validated',
					'Created',
				), $ids
			);
			$result = $adb->pquery($query, $params);
			if(!$result){
				$response->setResult($adb->echoError('Erreur de modification des factures', true));
			}
			else{
				$modified = $adb->getAffectedRowCount($result);
				if($modified)
					$response->setResult("$modified règlement(s) modifié(s)");
				else
					$response->setResult('Aucune règlement modifié');
			}
		}
		else
			$response->setResult('Aucune règlement à modifier');

		$response->emit();
	}
}