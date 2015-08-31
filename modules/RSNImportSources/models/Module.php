<?php
/*+***********************************************************************************
 * 
 * ************************************************************************************/

class RSNImportSources_Module_Model extends Vtiger_Module_Model {
    /**
	 * Function to check whether the entity has an quick create menu
	 * @return <Boolean> true/false
     */
    public function isQuickCreateMenuVisible() {
        return false ;
    }

	/** ED150825, à cause de l'héritage de Import, List devient ListView
	 * Function to get the ListView Component Name
	 * @return string
	 */
	public function getListViewName() {
		return 'ListView';
	}
	/** ED150825, à cause de l'héritage de Import, List devient ListView
	 * Function to get the url for default view of the module
	 * @return <string> - url
	 */
	public function getDefaultUrl() {
		return $this->getListViewUrl();
	}
	
	

	/**
	 * Function to get the list of recently visisted records
	 * @param <Number> $limit
	 * @return <Array> - List of Vtiger_Record_Model or Module Specific Record Model instances
	 */
	public function getPreImportRecords($whereItsTimeToAutoRun = false, $limit=999) {
		$db = PearDatabase::getInstance();

		$query = 'SELECT *
			FROM vtiger_rsnimportsources
			JOIN vtiger_crmentity
				ON vtiger_rsnimportsources.rsnimportsourcesid = vtiger_crmentity.crmid
			JOIN vtiger_rsnimportsourcescf
				ON vtiger_rsnimportsources.rsnimportsourcesid = vtiger_rsnimportsourcescf.rsnimportsourcesid
			WHERE vtiger_crmentity.deleted = 0
			AND autoenabled = 1
		';
		if($whereItsTimeToAutoRun){
			$query .= '
				AND (autolasttime IS NULL
					OR autolasttime < DATE_SUB(NOW(), INTERVAL `autoperiod` MINUTE)
				)
			';
		}
		$query .= '
			ORDER BY autolasttime ASC
			LIMIT ' . $limit;
		$params = array($limit);
		$result = $db->pquery($query, $params);
		if(!$result){
			var_dump($query, $params);
			$db->echoError();
			return false;
		}
		$noOfRows = $db->num_rows($result);

		$recentRecords = array();
		for($i=0; $i<$noOfRows; ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			$row['id'] = $row['crmid'];
			$recentRecords[$row['id']] = $this->getRecordFromArray($row);
		}
		return $recentRecords;
	}
}
