<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Vtiger Entity Record Model Class
 *
 *
 * TODO :
 * ALTER TABLE `vtiger_field` ADD INDEX(`tablename`)
 *
 * 
 */
class RSNPanelsVariables_Record_Model extends Vtiger_Record_Model {

	/**
	 * retourne le Field associé à la variable d'après le champ fieldid
	 * @param $columnName = FALSE, équivaut à $this->get('fieldid')
	 * 	de la forme tablename.columnname
	 */
	public function getQueryField($columnName = FALSE){
		if(!$columnName)
			$columnName = $this->get('fieldid');
		$fieldObject = Vtiger_Cache::get('rsnpanelvariable-field', $columnName);
		if($fieldObject){
			$fieldObject->set('fieldvalue', $this->get('defaultvalue'));
			return $fieldObject;
		}
		
		$fieldObject = Vtiger_Field_Model::getInstance($columnName);
		
		global $adb;
		$params = explode('.', $columnName);
		$result = $adb->pquery('SELECT fieldid, tabid
				       FROM vtiger_field
				       WHERE '. (count($params) > 1 ? 'tablename = ? AND ' : '') . '
				       columnname = ?				       
				       LIMIT 1', $params);
		while ($row = $adb->fetch_array($result)) {
			$fieldObject = Vtiger_Field_Model::getInstanceFromFieldId($row['fieldid'],$row['tabid']);
			if(count($fieldObject)){
				$fieldObject = $fieldObject[0];
				//$fieldObject->set('name', 'defaultvalue');
				$fieldObject->set('fieldvalue', $this->get('defaultvalue'));
			}
			else
				$fieldObject = null;
			Vtiger_Cache::set('rsnpanelvariable-field', $columnName, $fieldObject);
			return $fieldObject;
		}
		return null;
	}
}
