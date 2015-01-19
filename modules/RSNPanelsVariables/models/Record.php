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
		
		//$fieldObject = Vtiger_Field_Model::getInstance($columnName);
		
		global $adb;
		$params = explode('.', $columnName);
		if(count($params) == 1){
			//un seul argument
			switch(strtolower($params[0])){
				case "boolean":
				case "bool":
					$uitype = 56;
					break;
				case "int":
				case "integer":
					$uitype = 7;
					break;
				case "date":
					$uitype = 5;
					break;
				case "color":
					$uitype = 401;
					break;
				case "buttonset":
					$uitype = 402;
					break;
				default:
					$uitype = 1;
					break;
			}
			$result = $adb->pquery('SELECT fieldid, tabid
					       FROM vtiger_field
					       WHERE uitype = ?				       
					       LIMIT 1', array($uitype));
			
		}
		else {
			$result = $adb->pquery('SELECT fieldid, tabid
					       FROM vtiger_field
					       WHERE tablename = ?
						AND columnname = ?				       
					       LIMIT 1', $params);
		}
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
	
	
	
	
	
	/* ED150117 */
	public function getSQLOperation(&$value, &$params = FALSE){
		//var_dump($this);
		switch(html_entity_decode ( $this->get('rsnvariableoperator') )){
			case '<' :
			case 'inférieur' :
				return ' < ?';
			case '>' :
			case 'supérieur' :
				return ' > ?';
			case '<=' :
			case 'inférieur ou égal' :
				return ' <= ?';
			case '>=' :
			case 'supérieur ou égal' :
				return ' >= ?';
			case '=' :
			case 'égal' :
				return ' = ?';
			case '<>' :
			case '!=' :
			case 'différent' :
				return ' != ?';
			case 'contient' :
				return ' LIKE CONCAT(\'%\', ?, \'%\')';
			case 'ne contient pas' :
				return ' NOT LIKE CONCAT(\'%\', ?, \'%\')';
			default:
				//var_dump(html_entity_decode ( $this->get('rsnvariableoperator') ));
				return ' # operateur "' . $this->get('rsnvariableoperator') . '" inconnu #';
				return $this->get('RSNVariableOperator');
		}
	}
}
