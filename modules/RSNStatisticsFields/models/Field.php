<?php
/*+**********************************************************************************
 * 
 ************************************************************************************/

class RSNStatisticsFields_Field_Model extends Vtiger_Field_Model {

// $rowData vient de SELECT `rsnstatisticsfieldsid`, `fieldname`, `fieldtype`, `uniquecode`, `statisticparameters`, `rsnsqlqueriesid`, `sequence`, `aggregatefunction`
	public static function getInstanceFromRowData($rowData) {
//		$this->id = $valuemap['fieldid'];
//		$this->name = $valuemap['fieldname'];
//		$this->label= $valuemap['fieldlabel'];
//		$this->column = $valuemap['columnname'];
//		$this->table  = $valuemap['tablename'];
//		$this->uitype = $valuemap['uitype'];
//		$this->typeofdata = $valuemap['typeofdata'];
//		$this->helpinfo = $valuemap['helpinfo'];
//		$this->masseditable = $valuemap['masseditable'];
//		$this->displaytype   = $valuemap['displaytype'];
//		$this->generatedtype = $valuemap['generatedtype'];
//		$this->readonly      = $valuemap['readonly'];
//		$this->presence      = $valuemap['presence'];
//		$this->defaultvalue  = $valuemap['defaultvalue'];
//        $this->quickcreate = $valuemap['quickcreate'];
//		$this->sequence = $valuemap['sequence'];
//		$this->summaryfield = $valuemap['summaryfield'];
//		$this->uiclass = $valuemap['uiclass']; // AV150415: add the uiclass attribute
//		$this->block= $blockInstance || !$valuemap['block'] ? $blockInstance : Vtiger_Block::getInstance($valuemap['block'], $moduleInstance);//ED151025 ||!$valuemap['block']


		$fieldInstance = new self();
		$rowData['fieldid'] = $rowData['rsnstatisticsfieldsid'];
		$rowData['fieldlabel'] = $rowData['fieldname'];
		$rowData['fieldname'] = $rowData['uniquecode'];
		$rowData['columnname'] = $rowData['uniquecode'];
		$rowData['uitype'] = self::getVtigerUIType($rowData['fieldtype']);
		$rowData['typeofdata'] = self::getVtigerTypeOfData($rowData['fieldtype']);
		$rowData['displaytype'] = 1;
		$rowData['readonly'] = 1;
		$fieldInstance->initialize($rowData);
		$fieldInstance->set('parentid', $rowData['rsnstatisticsid']);
		$rowData['tablename'] = RSNStatistics_Utils_Helper::getStatsTableNameFromId($row['rsnstatisticsid']);;
		return $fieldInstance;
	}
	
	public function getTableName(){
		return $this->get('table');
	}
	
	public function getColumnName(){
		return $this->get('column');
	}
	
	public static function getVtigerUIType($statFieldType){
		switch($statFieldType){
			case 'INT':
				return 7;
			case 'DOUBLE':
				return 7;
			case 'PERCENT':
				return 9;
			case 'CURRENCY':
				return 72;
			case 'DATE':
				return 5;
			default:
				return 1;
		}
	}
	
	public static function getVtigerTypeOfData($statFieldType){
		switch($statFieldType){
			case 'INT':
			case 'DOUBLE':
			case 'PERCENT':
			case 'CURRENCY':
				return 'N~O';
			case 'DATE':
				return 'D~O';
			default:
				return 'V~O';
		}
	}
}
?>