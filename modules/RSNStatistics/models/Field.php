<?php
/*+**********************************************************************************
 * 
 ************************************************************************************/

class RSNStatistics_Field_Model extends Vtiger_Field_Model {

	public function getPicklistValuesForCustomView(){
		switch($this->get('column')){
		case 'stats_periodicite':
			if($this->get('parentid'))
				return RSNStatisticsResults_Module_Model::getResultsPeriods($this->get('parentid'));
			else
				throw new Exception('La propriété parentid devrait fournir le rsnstatisticsid du contexte');
		default:
			return parent::getPicklistValuesForCustomView();
		}
	}
	
	///**
	// * Returns distinct values of 'code' column in parent rsnstatistic
	// * @return as getPicklistValues
	// */
	//public function getDistinctStatCodes(){
	//	global $adb;
	//	$statId = $this->get('parentid');
	//	if(!$statId)
	//		throw new Exception('La propriété parentid devrait fournir le rsnstatisticsid du contexte');
	//	$tableName = RSNStatistics_Utils_Helper::getStatsTableNameFromId($statId);
	//	$query = "SELECT DISTINCT code
	//		FROM $tableName
	//		ORDER BY code
	//	";
	//	$result = $adb->query($query);
	//	if(!$result){
	//		echo "<pre>$statId : $query</pre>";
	//		$adb->echoError();
	//		return array();
	//	}
	//	$values = array();
	//	while($row = $adb->getNextRow($result))
	//		$values[$row[0]] = $row[0];
	//	return $values;
	//}
}
?>