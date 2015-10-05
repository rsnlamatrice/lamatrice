<?php

class RSNQueriesVariables_Utils_Helper {

	public static function getRelatedQueryRecordModel($variableId) {
		$db = PearDatabase::getInstance();
		$query = 'SELECT `vtiger_rsnsqlqueries`.`rsnsqlqueriesid` FROM `vtiger_rsnsqlqueries`
					INNER JOIN `vtiger_crmentityrel` ON `vtiger_rsnsqlqueries`.`rsnsqlqueriesid` = `vtiger_crmentityrel`.`crmid` AND `vtiger_crmentityrel`.`relcrmid` = ?';
		$params = array($variableId);
		$result = $db->pquery($query, $params);
		$noOfRows = $db->num_rows($result);

		if ($noOfRows == 1) {
			$row = $db->query_result_rowdata($result, 0);
			return Vtiger_Record_Model::getInstanceById($row['rsnsqlqueriesid'], 'RSNSQLQueries');
			//tmp get record model !
		} else {
			//not found !!
			return null;
		}
	}
}