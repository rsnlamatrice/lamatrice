<?php

class RSNQueriesVariables_Module_Model extends Vtiger_Module_Model {
    
	/**
	 * Function to check whether the entity has an quick create menu
	 * @return <Boolean> true/false
	 * ED141024
	 */
	public function isQuickCreateMenuVisible() {
		return false ;
	}
	
	/**
	 * Function to save a given record model of the current module
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function saveRecord(Vtiger_Record_Model $recordModel) {
		if (!$recordModel->isUnsavedRecord() && !$recordModel->get('disabled') && ! $recordModel->get('doNotUpdateQueryString')) {
			$currentRecordModel = Vtiger_Record_Model::getInstanceById($recordModel->getId(), $recordModel->getModule()->getName());
			$queryRecordModel = RSNQueriesVariables_Utils_Helper::getRelatedQueryRecordModel($recordModel->getId());
			$queryRecordModel->updateQueryStringVariable($currentRecordModel, $recordModel);
		}
		$result = parent::saveRecord($recordModel);
		return $result;
	}

}