<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Invoice_MassSave_Action extends Inventory_MassSave_Action {

	public function __construct() {
		parent::__construct();
		$this->exposeMethod('enCoursStatusToValidated');
		$this->exposeMethod('comptaStatusToEnCours');
		
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
			|| $recordModel->get('invoicestatus') === 'Comtpa')
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
	 * Changement du statut des factures en cours en status Validée
	 */
	public function enCoursStatusToValidated(Vtiger_Request $request){
		$this->changeInvoiceStatus($request, 'Created', 'Validated');
	}
	/**
	 * Changement du statut des factures Compta en status en cours 
	 */
	public function comptaStatusToEnCours(Vtiger_Request $request){
		$this->changeInvoiceStatus($request, array('Compta', 'Validated'), 'Created');
	}
	/**
	 * Changement du statut des factures 
	 */
	public function changeInvoiceStatus(Vtiger_Request $request, $fromStatus, $toStatus){
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
		if(!is_array($fromStatus)){
			$searchKey[] = 'invoicestatus';
			$searchValue[] = $fromStatus;
			$operator[] = 'e';
		}
		$request->set('search_key', $searchKey);
		$request->set('search_value', $searchValue);
		$request->set('operator', $operator);
		//global $adb; $adb->setDebug(true);
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
			$query = 'UPDATE vtiger_invoice
				JOIN vtiger_crmentity
					ON vtiger_invoice.invoiceid = vtiger_crmentity.crmid
				JOIN vtiger_invoicecf
					ON vtiger_invoicecf.invoiceid = vtiger_crmentity.crmid
				SET invoicestatus = ?
				, modifiedtime = NOW()
				, modifiedby = ?
			';
			if(!is_array($fromStatus) && $fromStatus === 'Compta'
			 || is_array($fromStatus) && in_array('Compta', $fromStatus)){
				$query .= ', sent2compta = NULL
						   , received = IF(receivedmoderegl = "Chèque" AND receivedcomments LIKE "Validation%", 0, received)
						   , balance = IF(receivedmoderegl = "Chèque" AND receivedcomments LIKE "Validation%", total, balance)
						   , receivedcomments = IF(receivedmoderegl = "Chèque" AND receivedcomments LIKE "Validation%", "", receivedcomments)
						   ';
			}
			$query .= '
				WHERE vtiger_crmentity.deleted = 0
				AND vtiger_crmentity.crmid IN ('.generateQuestionMarks($ids).')
			';
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$params = array_merge(
				array(
					$toStatus,
					$currentUser->getId(),
				), $ids
			);
			
			if(is_array($fromStatus)){
				$query .= ' AND invoicestatus IN ('.generateQuestionMarks($fromStatus).')';
				$params = array_merge($params, $fromStatus);
			}
			else{
				$query .= ' AND invoicestatus = ?';
				$params[] = $fromStatus;
			}
			
			$result = $adb->pquery($query, $params);
			if(!$result){
				$response->setResult($adb->echoError('Erreur de modification des factures', true));
			}
			else{
				$modified = count($ids);//$adb->getAffectedRowCount($result) retourne 3x le nombre;
				if($modified)
					$response->setResult("$modified facture(s) modifiée(s)");
				else
					$response->setResult('Aucune facture modifiée');
			}
		}
		else
			$response->setResult('Aucune facture à modifier');

		$response->emit();
	}
}