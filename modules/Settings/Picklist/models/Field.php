<?php

/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Picklist_Field_Model extends Vtiger_Field_Model {

	//ED150829
	var $pickListName;

    public function isEditable() {
        $nonEditablePickListValues = array( 'campaignrelstatus', 'duration_minutes','email_flag','hdnTaxType',
                        'payment_duration','recurringtype','recurring_frequency','visibility');
        if(in_array($this->getName(), $nonEditablePickListValues)) return false;
        return true;
    }

    /**
     * Function which will give the picklistvalues for given roleids
     * @param type $roleIdList -- array of role ids
     * @param type $groupMode -- Intersection/Conjuction , intersection will give only picklist values that exist for all roles
     * @return type -- array
     */
    public function getPicklistValues($roleIdList, $groupMode='INTERSECTION') {
        if(!$this->isRoleBased()) {
            return parent::getPicklistValues();
        }
        $intersectionMode = false;
        if($groupMode == 'INTERSECTION') {
            $intersectionMode = true;
        }

        $db = PearDatabase::getInstance();
        $fieldName = $this->getName();
        $tableName = 'vtiger_'.$fieldName;
        $idColName = $fieldName.'id';
        $query = 'SELECT '.$fieldName;
        if($intersectionMode) {
            $query .= ',count(roleid) as rolecount ';
        }
        $query .= ' FROM  vtiger_role2picklist INNER JOIN '.$tableName.' ON vtiger_role2picklist.picklistvalueid = '.$tableName.'.picklist_valueid'.
                 ' WHERE roleid IN ('.generateQuestionMarks($roleIdList).') order by sortid';
        if($intersectionMode) {
            $query .= ' GROUP BY picklistvalueid';
        }
		$result = $db->pquery($query, $roleIdList);
	
        $pickListValues = array();
        $num_rows = $db->num_rows($result);
        for($i=0; $i<$num_rows; $i++) {
            $rowData = $db->query_result_rowdata($result, $i);
            if($intersectionMode) {
                //not equal if specify that the picklistvalue is not present for all the roles
                if($rowData['rolecount'] != count($roleIdList)){
                    continue;
                }
            }
			//Need to decode the picklist values twice which are saved from old ui
            $pickListValues[] = decode_html(decode_html($rowData[$fieldName]));
        }
        return $pickListValues;
    }

    /**
	 * Function to get instance
	 * @param <String> $value - fieldname or fieldid
	 * @param <type> $module - optional - module instance
	 * @return <Vtiger_Field_Model>
	 */
	public static function getInstance($value, $module = false) {
		$fieldObject = parent::getInstance($value, $module);
		if($fieldObject) {
			$alternatePicklistName = $fieldObject->getPickListName();
			if($alternatePicklistName != $fieldObject->getName()){
				$fieldObject = self::getFieldFromPicklistName($alternatePicklistName);
			}
			return self::getInstanceFromFieldObject($fieldObject);
		}
		return false;
	}

	/** ED160108
	 * Function to get field object from picklistname
	 * @param <String> $value - fieldname
	 * @return <Int>
	 */
	public static function getFieldFromPicklistName($picklistName) {
		global $adb;
		$query = 'SELECT fieldid
			FROM vtiger_field
			WHERE fieldname = ?
			LIMIT 1';
		$result = $adb->pquery($query, array($picklistName));
		return parent::getInstance($adb->query_result($result, 0, 0), $module);
	}

    /**
	 * Static Function to get the instance fo Vtiger Field Model from a given Vtiger_Field object
	 * @param Vtiger_Field $fieldObj - vtlib field object
	 * @return Vtiger_Field_Model instance
	 */
	public static function getInstanceFromFieldObject(Vtiger_Field $fieldObj) {
		$objectProperties = get_object_vars($fieldObj);
		$fieldModel = new self();
		foreach($objectProperties as $properName=>$propertyValue) {
			$fieldModel->$properName = $propertyValue;
		}
		/* ED150829 */
		if(!$fieldModel->pickListName){
			//var_dump('$fieldModel->pickListName', get_class($fieldObj), $fieldObj->getPickListName());
			$fieldModel->pickListName = $fieldObj->getPickListName();
		}
		return $fieldModel;
	}
	
	/* ED150829
	 * Nom du picklist correspond au champ
	*/
	public function getPickListName() {
		if(!$this->pickListName)
			return parent::getPickListName();
		return $this->pickListName;
	}

	/**
     * Function which will give the editable picklist values for a field
     * @param type $fieldName -- string
     * @return type -- array of values
     */
	public static function getEditablePicklistValues($fieldName){
		$cache = Vtiger_Cache::getInstance();
		$EditablePicklistValues = $cache->get('EditablePicklistValues', $fieldName);
        if($EditablePicklistValues) {
            return $EditablePicklistValues;
        }
        $db = PearDatabase::getInstance();
		
        $query="SELECT $fieldName FROM vtiger_$fieldName WHERE presence=1 AND $fieldName <> '--None--'
			ORDER BY $fieldName";
        $values = array();
        $result = $db->pquery($query, array());
        $num_rows = $db->num_rows($result);
        for($i=0; $i<$num_rows; $i++) {
			//Need to decode the picklist values twice which are saved from old ui
            $values[] = decode_html(decode_html($db->query_result($result,$i,$fieldName)));
        }
		$cache->set('EditablePicklistValues', $fieldName, $values);
        return $values;
	}

	/**
     * Function which will give the non editable picklist values for a field
     * @param type $fieldName -- string
     * @return type -- array of values
     */
	public static function getNonEditablePicklistValues($fieldName){
		$cache = Vtiger_Cache::getInstance();
		$NonEditablePicklistValues = $cache->get('NonEditablePicklistValues', $fieldName);
        if($NonEditablePicklistValues) {
            return $NonEditablePicklistValues;
        }
        $db = PearDatabase::getInstance();

        $query = "select $fieldName from vtiger_$fieldName where presence=0";
        $values = array();
        $result = $db->pquery($query, array());
        $num_rows = $db->num_rows($result);
        for($i=0; $i<$num_rows; $i++) {
			//Need to decode the picklist values twice which are saved from old ui
            $values[] = decode_html(decode_html($db->query_result($result,$i,$fieldName)));
        }
        $cache->set('NonEditablePicklistValues', $fieldName, $values);
        return $values;
	}
	
	/**
	 * Retourne les champs complémentaires d'une table de picklist values
	 */
	public function getSettingFieldInfos($module){
		$pickListFieldName = $this->getPickListName();
		global $adb;
		$pickListFieldName = Vtiger_Util_Helper::getRelatedFieldName($pickListFieldName);
		
		//définition des champs disponibles dans la table vtiger_picklist
		//sous la forme columnname1[:fieldmodel1]|columnname2[:fieldmodel2]
		//où fieldmodel est de la forme tablename.fieldname ou type
		$query = 'SELECT settingfields
			FROM vtiger_picklist
			WHERE name = ?';
		$result = $adb->pquery($query, array($pickListFieldName));
		if(!$adb->getRowCount($result))
			return false;
		$row = $adb->getNextRow($result);
		if(!$row[0])
			return false;
		$settingFieldNames = array();
		$fieldInfos = explode('|', $row[0]);
		foreach($fieldInfos as $fieldInfo){
			$fieldInfo = explode(':', $fieldInfo);
			if(count($fieldInfo) > 1){
				if(strpos($fieldInfo[1], '.')){
					$fieldModel = explode('.', $fieldInfo[1]);
					$fieldInfo[1] = array('table'=> $fieldModel[0], 'fieldname'=> $fieldModel[1]);
				}
			}
			else
				$fieldInfo[1] = false;
			$settingFieldNames[$fieldInfo[0]] = array(
				'columnname' => $fieldInfo[0],
				'fieldmodel' => $fieldInfo[1],
			);
		}
		return $settingFieldNames;
	}
	
	/**
	 * Retourne les champs complémentaires d'une table de picklist values
	 */
	public function getSettingFieldModels($module){
		$pickListFieldName = $this->getPickListName();
		$pickListFieldName = Vtiger_Util_Helper::getRelatedFieldName($pickListFieldName);
		global $adb;
		
		//définition des champs disponibles dans la table vtiger_picklist
		//sous la forme array('columnname'=>, 'fieldmodel' =>)
		//où fieldmodel est de la forme array('tablename'=>, 'fieldname'=>) ou 'type'
		$settingFieldNames = $this->getSettingFieldInfos($module);
		
		//Colonnes dans la table des valeurs
		$tableName = 'vtiger_'.$pickListFieldName;
		$query = 'SHOW COLUMNS FROM '.$tableName;
		$result = $adb->pquery($query);
		$fieldModels = array();
		while($row = $adb->getNextRow($result,false)){
			$settingFieldName = $settingFieldNames[$row['field']];
			if(!$settingFieldName)
				continue;
			$field = new Vtiger_Field_Model();
			$field->set('module', $module);
			$field->set('name', $row['field']);
			$field->set('column', $tableName.':'.$row['field']);
			$field->set('label', $row['field']);
			$field->set('typeofdata', 'V~O');
			switch(explode('(', $row['type'])[0]){
			case 'int':
				$field->set('uitype', 7);
				break;
			case 'tinyint':
				$field->set('uitype', 56);
				break;
			default:
				$field->set('uitype', 1);
				break;
			}

			if(is_array($settingFieldName['fieldmodel'])){
				//modele de champ d'après vtiger_field
				//TODO
				$field->set('label', $settingFieldName['fieldmodel']['fieldname']);
			}
			elseif($settingFieldName['fieldmodel']){
				//type de données
				//TODO
				switch($settingFieldName['fieldmodel']){
				case 'bool':
				case 'boolean':
					$field->set('uitype', 56);
					break;
				}
			}
			
			$fieldModels[$row['field']] = $field;
		}
		return $fieldModels;
	}
	
	//Retourne les valeurs des champs complémentaires pour une valeurs du picklist
	function getSettingFieldValue($pickListValue){
		
		$pickListFieldName = $this->getPickListName();
		$pickListFieldName = Vtiger_Util_Helper::getRelatedFieldName($pickListFieldName);
		global $adb;
		
		//définition des champs disponibles dans la table vtiger_picklist
		//sous la forme array('columnname'=>, 'fieldmodel' =>)
		//où fieldmodel est de la forme array('tablename'=>, 'fieldname'=>) ou 'type'
		$settingFieldNames = $this->getSettingFieldInfos($module);
		//Colonnes dans la table des valeurs
		$tableName = 'vtiger_'.$pickListFieldName;
		
		$query = '';
		foreach($settingFieldNames as $settingFieldName => $settingFieldInfo){
			if(!$query)
				$query = 'SELECT ';
			else
				$query .= ', ';
			$query .= $settingFieldName;
		}
		$query .= ' FROM '.$tableName
			.' WHERE '.$pickListFieldName.' = ?';
		$result = $adb->pquery($query, array($pickListValue));
		if(!$result){
			$adb->echoError(__FILE.'::getSettingFieldValue()');
			die();
		}
		if($row = $adb->getNextRow($result, false)){
			$data = array();
			foreach($settingFieldNames as $settingFieldName => $settingFieldInfo){
				$data[$settingFieldName] = $row[$settingFieldName];
			}
			return $data;
		}
		else
			return false;

	}
	//Sauve les valeurs des champs complémentaires pour une valeurs du picklist
	function saveSettingFieldValue($pickListValue, Vtiger_Request $request){
		
		$pickListFieldName = $this->getPickListName();
		$pickListFieldName = Vtiger_Util_Helper::getRelatedFieldName($pickListFieldName);
		global $adb;
		
		//définition des champs disponibles dans la table vtiger_picklist
		//sous la forme array('columnname'=>, 'fieldmodel' =>)
		//où fieldmodel est de la forme array('tablename'=>, 'fieldname'=>) ou 'type'
		$settingFieldNames = $this->getSettingFieldInfos($module);
		//Colonnes dans la table des valeurs
		$tableName = 'vtiger_'.$pickListFieldName;
		
		$query = 'UPDATE '.$tableName;
		$params = array();
		foreach($settingFieldNames as $settingFieldName => $settingFieldInfo){
			if(count($params) === 0)
				$query .= ' SET ';
			else
				$query .= ', ';
			$query .= $settingFieldName.' = ?';
			$value = $request->get($settingFieldName);
			//TODO init $settingFieldInfo['type'] and switch value
			if($value === 'on')
				$value = 1;
			$params[] = $value;
		}
		$query .= ' WHERE '.$pickListFieldName.' = ?';
		$params[] = $pickListValue;
		$result = $adb->pquery($query, $params);
		if(!$result){
			return $adb->echoError(__FILE.'::saveSettingFieldValue()', true);
		}
		return true;

	}

}