<?php

class RSNSQLQueries_Module_Model extends Vtiger_Module_Model {
	
	/**
	 * Function to save a given record model of the current module
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function saveRecord(Vtiger_Record_Model $recordModel) {

		//tmp check var before and save var after -> clean Query string before save it !
		// $result = parent::saveRecord($recordModel);
		// $recordModel->checkVariables();

		$recordModel->checkVariables();
		$result = parent::saveRecord($recordModel);
		$recordModel->saveVariables();

		return $result;
	}

}
