<?php
/*+**********************************************************************************
 * 
 ************************************************************************************/

class RSNStatisticsResults_Field_Model extends Vtiger_Field_Model {

	public static function getInstanceForStatisticsFieldsIdField() {
		$fieldInstance = new self();
		$rowData = array();
		$rowData['fieldid'] = 0;
		$rowData['fieldlabel'] = 'Statistique';
		$rowData['fieldname'] = 'rsnstatisticsfieldsid';
		$rowData['columnname'] = 'rsnstatisticsfieldsid';
		$rowData['uitype'] = 10;
		$rowData['relatedto'] = 'RSNStatisticsFields';
		$rowData['typeofdata'] = 'V~M';
		$rowData['displaytype'] = 1;
		$rowData['readonly'] = 1;
		$fieldInstance->initialize($rowData);
		return $fieldInstance;
	}
	public static function getInstanceForRelatedIdField() {
		$fieldInstance = new self();
		$rowData = array();
		$rowData['fieldid'] = 0;
		$rowData['fieldlabel'] = 'Elément lié';
		$rowData['fieldname'] = 'crmid';
		$rowData['columnname'] = 'crmid';
		$rowData['uitype'] = 10;
		$rowData['typeofdata'] = 'V~M';
		$rowData['displaytype'] = 1;
		$rowData['readonly'] = 1;
		$fieldInstance->initialize($rowData);
		return $fieldInstance;
	}
	public static function getInstanceForRelatedModuleField() {
		$fieldInstance = new self();
		$rowData = array();
		$rowData['fieldid'] = 0;
		$rowData['fieldlabel'] = 'Module';
		$rowData['fieldname'] = 'relmodule';
		$rowData['columnname'] = 'relmodule';
		$rowData['uitype'] = 402;
		$rowData['typeofdata'] = 'V~M';
		$rowData['displaytype'] = 1;
		$rowData['readonly'] = 1;
		$fieldInstance->initialize($rowData);
		return $fieldInstance;
	}

	public static function getInstanceForPeriodField() {
		$fieldInstance = new self();
		$rowData = array();
		$rowData['fieldid'] = 0;
		$rowData['fieldlabel'] = 'Période';
		$rowData['fieldname'] = 'code';
		$rowData['columnname'] = 'code';
		$rowData['uitype'] = 1;
		$rowData['typeofdata'] = 'V~M';
		$rowData['displaytype'] = 402;
		$rowData['readonly'] = 1;
		$fieldInstance->initialize($rowData);
		return $fieldInstance;
	}

	
	public function getTableName(){
		return $this->get('table');
	}
	
	public function getColumnName(){
		return $this->get('column');
	}
	
	public static function getDisplayValueForFieldType($value, $statFieldType, &$unit = ''){
		if($value === null)
			return $value;
		switch($statFieldType){
			case 'INT':
				break;
			case 'DOUBLE':
				break;
			case 'PERCENT':
				$unit = '%';
				break;
			case 'CURRENCY':
				$unit = ' €';
				break;
			case 'DATE':
				$value = str_replace($value, ' 00:00:00');
				break;
			default:
				break;
		}
		return $value;
	}

}
?>