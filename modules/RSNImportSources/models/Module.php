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
	 * Url pour obtenir plus de lignes de prévisualisation du pré-import
	 */
	public function getPreviewDataViewUrl($importSource, $module, $offset = 0, $limit = 12){
		return 'index.php?module='.$this->get('name')
			.'&view=Index&mode=getPreviewData'
			.'&for_module='.$module
			.'&ImportSource='.$importSource
			.'&page_offset='.$offset.'&page_limit='.$limit
		;
	}
	
	/**
	 * Url pour valider les données de pré-importations
	 */
	public function getValidatePreImportRowsUrl($importSource, $module, $offset = 0, $limit = 12){
		return 'index.php?module='.$this->get('name')
			.'&action=PreImport'
			.'&mode=validateRows'
			.'&for_module='.$module
			.'&ImportSource='.$importSource
		;
	}

	/**
	 * Function to get the list of recently visisted records
	 * @param <Number> $limit
	 * @return <Array> - List of Vtiger_Record_Model or Module Specific Record Model instances
	 */
	public function getPreImportRecords($whereItsTimeToAutoRun = false, $limit=999) {
		$db = PearDatabase::getInstance();

		$query = 'SELECT vtiger_crmentity.*
				, vtiger_rsnimportsources.`modules` AS `rsnimportsourcesmodules` '/*nécessité de mettre les fieldName à la place des columnName*/.'
				, vtiger_rsnimportsources.*
				, vtiger_rsnimportsourcescf.*
				/*, vtiger_rsnimportsources.`rsnimportsourcesid`, vtiger_rsnimportsources.`disabled`, vtiger_rsnimportsources.`class`, vtiger_rsnimportsources.`sortorderid`
				, vtiger_rsnimportsources.`title`
				, vtiger_rsnimportsources.`lastimport`, vtiger_rsnimportsources.`autoenabled`, vtiger_rsnimportsources.`autoperiod`, vtiger_rsnimportsources.`autosourcedata`, vtiger_rsnimportsources.`autolasttime`, vtiger_rsnimportsources.`autolastresult`*/
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

		$records = array();
		for($i=0; $i<$noOfRows; ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			$row['id'] = $row['crmid'];
			$records[$row['id']] = $this->getRecordFromArray($row);
		}
		return $records;
	}
}
