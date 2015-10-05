<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class RSNSQLQueries_GetFieldData_Action extends Vtiger_Action_Controller {

	function checkPermission(Vtiger_Request $request) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		if(!$currentUser->isAdminUser()) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED', 'Vtiger'));
		}
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		$result = array();

		 if ($mode == 'tables') {
		 	$result = $this->getTablesList($request);
		 } else if ($mode == 'columns') {
		 	$result = $this->getTableColumns($request);
		 } else if ($mode == 'variables') {
		 	$result = $this->getVariablesList($request);//tmp need the Query id !!!!
		 } else if ($mode == 'SQLQueries') {
		 	$result = $this->getSQLQueriesList($request);
		 }

		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}

	public function getTablesList(Vtiger_Request $request) {
		$db = PearDatabase::getInstance();
		$query = "SHOW TABLES";
		$result = $db->pquery($query, array());
		$values = array();
		$num_rows = $db->num_rows($result);

		$tablesList = array();
		for($i=0; $i<$num_rows; $i++) {
			$row = $db->query_result($result,$i);
			$tablesList[] = array('keyword'=> $row, 'displayed_keyword'=> '`' . $row . '`');
		}

		return $tablesList;
	}

	public function getTableColumns(Vtiger_Request $request) {
		$relatedTable = $request->get('relatedTable');
		$db = PearDatabase::getInstance();
		$query = "SHOW COLUMNS FROM " . $relatedTable;
		$result = $db->pquery($query, array());
		$values = array();
		$num_rows = $db->num_rows($result);

		$columnsList = array();
		for($i=0; $i<$num_rows; $i++) {
			$field = $db->query_result($result,$i);
			$columnsList[] = array('keyword'=> $field, 'displayed_keyword'=> '`' . $field . '`');
		}

		return $columnsList;
	}

	public function getVariablesList(Vtiger_Request $request) {
		$moduleName = $request->get('module');
		$recordId = $request->get('record');
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		return $recordModel->getRelatedVariablesNames();
	}

	public function getSQLQueriesList(Vtiger_Request $request) {
		$db = PearDatabase::getInstance();
		$query = "SELECT `vtiger_rsnsqlqueries`.`name` FROM `vtiger_rsnsqlqueries`
				INNER JOIN `vtiger_crmentity` ON `vtiger_crmentity`.`crmid` = `vtiger_rsnsqlqueries`.`rsnsqlqueriesid`
				WHERE `vtiger_crmentity`.`deleted` = FALSE";
		$result = $db->pquery($query, array());
		$values = array();
		$num_rows = $db->num_rows($result);

		$SQLQueriesList = array();
		for($i=0; $i<$num_rows; $i++) {
			$SQLQueriesList[] = $db->query_result($result,$i);
		}

		return $SQLQueriesList;
	}
}
