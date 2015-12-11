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
					$uitype = 5;//bug -> USER_MODEL not defined ...
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
	public function getSQLOperation(&$value){
		//var_dump($this);
		switch(html_entity_decode ( $this->get('rsnvariableoperator') )){
			case '<' :
			case 'inférieur' :
			case 'l' :
				return " < '$value'";
			case '>' :
			case 'supérieur' :
			case 'g' :
				return " > '$value'";
			case '<=' :
			case 'inférieur ou égal' :
			case 'm' :
				return " <= '$value'";
			case '>=' :
			case 'supérieur ou égal' :
			case 'h' :
				return " >= '$value'";
			case '=' :
			case 'égal' :
			case 'e' :
				return " = '$value'";
			case '<>' :
			case '!=' :
			case 'différent' :
			case 'n' :
				return " != '$value'";
			case 'contient' :
			case 'c' :
				return " LIKE '%$value%'";
			case 'ne contient pas' :
			case 'k' :
				return " NOT LIKE '%$value%'";
			case 'commence par' :
			case 's' :
			case '^' :
				return " LIKE '$value%'";
			case 'finit par' :
			case 'ew' :
				return " LIKE '%$value'";
			case 'parmi' :
				//TODO
				return " IN ($value)";
			default:
				//var_dump(html_entity_decode ( $this->get('rsnvariableoperator') ));
				return ' # operateur "' . $this->get('rsnvariableoperator') . '" inconnu #';
				return $this->get('RSNVariableOperator');
		}
	}
	
	//Transforme la valeur en fonction du type du champ
	function getValueForSQL($value){
		if(!$value)
			return $value;
		$fieldModel = $this->getQueryField();
		
		switch($fieldModel->get('uitype')){
			case '5'://date
			case '6'://date
				$value = preg_replace('/^(\d{1,2})\D(\d{1,2})\D(\d{4})/', '$3-$2-$1', $value);
				break;
			default:
				$value = decode_html($value);
				break;
		}
		return $value;
		
	}
}
