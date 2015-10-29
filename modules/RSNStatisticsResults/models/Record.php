<?php

class RSNStatisticsResults_Record_Model extends Vtiger_Record_Model {
		
	/**
	 * ED141109
	 * getPicklistValuesDetails
	 */
	public function getPicklistValuesDetails($fieldname){
		switch($fieldname){
			case 'relmodule':
				return $this->getRelatedModuleNames();
			default:
				return parent::getPicklistValuesDetails($fieldname);
		}
	}
	
	function getRelatedModuleNames(){
		if($this->get('rsnstatisticsid')){
			$recordModel = Vtiger_Record_Model::getIntanceById($this->get('rsnstatisticsid'), 'RSNStatistics');
			$modules = explode(' |##| ', $recordModel->get('relmodule'));
		}
		else{
			$modules = array();
			$stats = RSNStatistics_Utils_Helper::getRelatedStatistics(array());
			foreach( $stats as $stat )
				$modules = array_merge($modules, explode(' |##| ', $stat['relmodule']));
		}
		$values = array();
		foreach($modules as $module){
			$values[$module] = array('label' => vtranslate($module, $module));
		}
		return $values;
	}
}
