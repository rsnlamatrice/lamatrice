<?php
/*+***********************************************************************************
 * ED151025
 *************************************************************************************/

/**
 * RSNStatisticsResults Module Model Class
 * is NOT an entity type
 */
class RSNStatisticsResults_Module_Model extends Vtiger_Module_Model {


	public function isPermitted($actionName) {
		switch($actionName){
			case 'Duplicate':
			case 'EditView':
			case 'Delete':
				return false;
			default:
				return parent::isPermitted($actionName);
		}
	}
		
		
	/**
	 * Function returns the url that shows Calendar Import result
	 * @return <String> url
	 */
	public function getImportResultUrl() {
		return false;
	}
	/**
	 * Function to check whether the module is enabled for quick create
	 * @return <Boolean> - true/false
	 */
	public function isQuickCreateSupported() {
		return false;
	}

	/**
	 * Function to get the Quick Links for the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Vtiger_Link_Model instances
	 */
	public function getSideBarLinks($linkParams) {
		$linkTypes = array('SIDEBARLINK', 'SIDEBARWIDGET');
		$links = Vtiger_Link_Model::getAllByType($this->getId(), $linkTypes, $linkParams);

		$quickLinks = array(
			array(
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_RECORDS_LIST',
				'linkurl' => $this->getListViewUrl(),
				'linkicon' => '',
			),
		);
		foreach($quickLinks as $quickLink) {
			$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
		}

		return $links;
	}


	/**
	 * Function returns Settings Links
	 * @return Array
	 */
	public function getSettingLinks() {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$settingLinks = array();

		/*if($currentUserModel->isAdminUser()) {
			$settingLinks[] = array(
					'linktype' => 'LISTVIEWSETTING',
					'linklabel' => 'LBL_EDIT_FIELDS',
					'linkurl' => 'index.php?parent=Settings&module=LayoutEditor&sourceModule='.$this->getName(),
					'linkicon' => Vtiger_Theme::getImagePath('LayoutEditor.gif')
			);

		$settingLinks[] = array(
					'linktype' => 'LISTVIEWSETTING',
					'linklabel' => 'LBL_EDIT_PICKLIST_VALUES',
					'linkurl' => 'index.php?parent=Settings&module=Picklist&view=Index&source_module='.$this->getName(),
					'linkicon' => ''
			);
		}*/
		return $settingLinks;
	}
	
	

	/**
	 * Function returns the default column for Alphabetic search
	 * @return <String> columnname
	 */
	public function getAlphabetSearchField(){
		return 'relmodule,rsnstatisticsid';
	}
	
	/**
	 * Returns distinct values of 'code' (period) column in parent rsnstatistic
	 * @return an array of code=>name pairs
	 */
	public function getResultsPeriods($statId){
		global $adb;
		$tableName = RSNStatistics_Utils_Helper::getStatsTableNameFromId($statId);
		$query = "SELECT DISTINCT code, name
			FROM $tableName
			ORDER BY code, name
		";
		$result = $adb->query($query);
		if(!$result){
			echo "<pre>$statId : $query</pre>";
			$adb->echoError();
			return array();
		}
		$values = array();
		while($row = $adb->getNextRow($result))
			$values[$row[0]] = $row[1];
		return $values;
	}
	
	//AUR_TMP check row in db !!!!!
	public function getRelatedListFields($parentModuleName) {
		$relatedListFields = array(
			'name' => 'name',
			'begin_date' => 'begin_date',
			'end_date' => 'end_date',
		);
		
		$relatedStatsFieldsCode = RSNStatistics_Utils_Helper::getModuleRelatedStatsFieldsCodes($parentModuleName);
		foreach($relatedStatsFieldsCode as $code) {
			$relatedListFields[$code] = $code;
		}
		return $relatedListFields;
	}
}
