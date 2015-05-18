<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Picklist_Module_Model extends Vtiger_Module_Model {

    public function getPickListTableName($fieldName) {
        return 'vtiger_'.$fieldName;
    }

    public function getFieldsByType($type) {
        $hardCodedPickFields = array('hdnTaxType','email_flag');

        $fieldModels = parent::getFieldsByType($type);
        $fields = array();
        foreach($fieldModels as $fieldName=>$fieldModel) {
            if(in_array($fieldName,$hardCodedPickFields)){
                continue;
            }
            $fields[$fieldName] = Settings_Picklist_Field_Model::getInstanceFromFieldObject($fieldModel);
        }
        return $fields;
    }

    public function addPickListValues($fieldModel, $newValue, $rolesSelected = array()) {
        $db = PearDatabase::getInstance();
        $pickListFieldName = $fieldModel->getName();
        $id = $db->getUniqueID("vtiger_$pickListFieldName");
        vimport('~~/include/ComboUtil.php');
        $picklist_valueid = getUniquePicklistID();
	$tableName = 'vtiger_'.$pickListFieldName;
	$maxSeqQuery = 'SELECT max(sortorderid) as maxsequence FROM '.$tableName;
	$result = $db->pquery($maxSeqQuery, array());
	$sequence = $db->query_result($result,0,'maxsequence');

	/* ED141128
	 * depuis l'ajout des colonnes uicolor et uiicon, il faut retrouver la liste des colonnes
	*/
	$columns = $db->getColumnNames($tableName);
	
        $params = array($id, $newValue);
	foreach($columns as $column)
	switch($column){
	    case 'picklist_valueid':
		$params[] = $picklist_valueid;
		break;
	    case 'sortorderid':
		$params[] = ++$sequence;
		break;
	    case 'presence':
		$params[] = 1;
		break;
	    case 'uicolor':
	    case 'uiicon':
		$params[] = null;
		break;
	}
	if($fieldModel->isRoleBased()) {
	    $columns = implode(', ', array_slice($columns, 0, 5));
            $sql = 'INSERT INTO '.$tableName.' ('.$columns . ') VALUES (?,?,?,?,?)';
            $result = $db->pquery($sql, array_slice($params, 0, 5));//array($id, $newValue, 1, $picklist_valueid,++$sequence)
        }else{
            $columns = implode(', ', array_slice($columns, 0, 4));
            $sql = 'INSERT INTO '.$tableName.' ('.$columns . ') VALUES (?,?,?,?)';
            $result = $db->pquery($sql, array_slice($params, 0, 4));//array($id, $newValue, ++$sequence, 1)
        }
	if(!$result){
	    echo("<br>ERREUR DANS addPickListValues (" . __FILE__ .")");
	    $db->echoError();
	    var_dump(array($id, $newValue, $picklist_valueid, $sequence, 1), $result);
	    die($sql);
	}
        if($fieldModel->isRoleBased() && !empty($rolesSelected)) {
            $sql = "select picklistid from vtiger_picklist where name=?";
            $result = $db->pquery($sql, array($pickListFieldName));
            $picklistid = $db->query_result($result,0,"picklistid");
            //add the picklist values to the selected roles
            for($j=0;$j<count($rolesSelected);$j++){
                $roleid = $rolesSelected[$j];

                $sql ="SELECT max(sortid)+1 as sortid
                       FROM vtiger_role2picklist left join vtiger_$pickListFieldName
                           on vtiger_$pickListFieldName.picklist_valueid=vtiger_role2picklist.picklistvalueid
                       WHERE roleid=? and picklistid=?";
                $sortid = $db->query_result($db->pquery($sql, array($roleid, $picklistid)),0,'sortid');

                $sql = "insert into vtiger_role2picklist values(?,?,?,?)";
                $db->pquery($sql, array($roleid, $picklist_valueid, $picklistid, $sortid));
            }

        }
        return $picklist_valueid;
    }

    public function renamePickListValues($pickListFieldName, $oldValue, $newValue, $moduleName) {
		$db = PearDatabase::getInstance();

		/* ED141205
		* récupère aussi la colonne primary key des tables contenant le champ
		*/
	       $query = '
		    SELECT vtiger_field.tablename, vtiger_field.columnname, vtiger_entityname.entityidcolumn
		    FROM vtiger_field
		    JOIN vtiger_entityname
			ON vtiger_entityname.tabid = vtiger_field.tabid
		    WHERE vtiger_field.fieldname = ?
		    AND vtiger_field.presence IN (0,2)
		';
		$result = $db->pquery($query, array($pickListFieldName));
		$num_rows = $db->num_rows($result);

		//As older look utf8 characters are pushed as html-entities,and in new utf8 characters are pushed to database
		//so we are checking for both the values
		$query = 'UPDATE ' . $this->getPickListTableName($pickListFieldName) . ' SET ' . $pickListFieldName . '=? WHERE ' . $pickListFieldName . '=? OR ' . $pickListFieldName . '=?';
		$db->pquery($query, array($newValue, $oldValue, Vtiger_Util_Helper::toSafeHTML($oldValue)));

		for ($i = 0; $i < $num_rows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$tableName = $row['tablename'];
			$columnName = $row['columnname'];
			$entityidcolumn = $row['entityidcolumn'];
			$query = 'UPDATE ' . $tableName . ' SET ' . $columnName . '=? WHERE ' . $columnName . '=?';
			$db->pquery($query, array($newValue, $oldValue));
			
			/* ED141205
			 * traite aussi les éléments des picklist multiples
			 */
			$query = 'SELECT ' . $entityidcolumn . ' AS id, ' . $columnName . ' AS value FROM ' . $tableName
			    . ' WHERE ' . $columnName . ' LIKE CONCAT(\'% |##| \', ?)
				OR ' . $columnName . ' LIKE CONCAT(?, \' |##| %\')
			    ';
			$result2 = $db->pquery($query, array($oldValue, $oldValue));
			$num_rows2 = $db->num_rows($result2);
			for ($j = 0; $j < $num_rows2; $j++) {
			    $row = $db->raw_query_result_rowdata($result2, $j);
			    $multi_value = $row['value'];
			    
			    $row['value'] = preg_replace('/' . preg_quote(' |##| ' . $oldValue) . '$/', ' |##| ' . $newValue, $row['value']);
			    $row['value'] = preg_replace('/^' . preg_quote($oldValue . ' |##| ') . '/', $newValue . ' |##| ', $row['value']);
			    $row['value'] = preg_replace('/' . preg_quote(' |##| ' . $oldValue . ' |##| ') . '/', ' |##| ' . $newValue . ' |##| ', $row['value']);
			    
			    if($multi_value == $row['value'])
				print_r("Aucun changement dans $multi_value -> preg_quote = " . preg_quote(' |##| ' . $oldValue . ' |##| ') );
			    else {
				$query = 'UPDATE ' . $tableName . ' SET ' . $columnName . '=? WHERE ' . $entityidcolumn . '=?';
				$db->pquery($query, array($row['value'], $row['id']));
			    }
			}
		}

		$query = "UPDATE vtiger_field SET defaultvalue=? WHERE defaultvalue=? AND columnname=?";
		$db->pquery($query, array($newValue, $oldValue, $columnName));

		vimport('~~/include/utils/CommonUtils.php');

		$query = "UPDATE vtiger_picklist_dependency SET sourcevalue=? WHERE sourcevalue=? AND sourcefield=?";
		$db->pquery($query, array($newValue, $oldValue, $pickListFieldName));
				
		$em = new VTEventsManager($db);
		$data = array();
		$data['fieldname'] = $pickListFieldName;
		$data['oldvalue'] = $oldValue;
		$data['newvalue'] = $newValue;
		$data['module'] = $moduleName;
		$em->triggerEvent('vtiger.picklist.afterrename', $data);
		
		return true;
	}

    public function remove($pickListFieldName , $valueToDelete, $replaceValue , $moduleName) {
        $db = PearDatabase::getInstance();
		if(!is_array($valueToDelete)) {
			$valueToDelete = array($valueToDelete);
		}
		//As older look utf8 characters are pushed as html-entities,and in new utf8 characters are pushed to database
		//so we are checking for both the values
		foreach ($valueToDelete as $key => $value) {
			$encodedValueToDelete[$key]  = Vtiger_Util_Helper::toSafeHTML($value);
		}
		$mergedValuesToDelete = array_merge($valueToDelete, $encodedValueToDelete);
		
        $fieldModel = Settings_Picklist_Field_Model::getInstance($pickListFieldName,$this);
        //if role based then we need to delete all the values in role based picklist
        if($fieldModel->isRoleBased()) {
            $picklistValueIdToDelete = array();
            $query = 'SELECT picklist_valueid FROM '.$this->getPickListTableName($pickListFieldName).
                     ' WHERE '.$pickListFieldName.' IN ('.generateQuestionMarks($valueToDelete).') OR '.$pickListFieldName.' IN ('.generateQuestionMarks($encodedValueToDelete).')';
            $result = $db->pquery($query,$mergedValuesToDelete);
            $num_rows = $db->num_rows($result);
            for($i=0;$i<$num_rows;$i++) {
                $picklistValueIdToDelete[] = $db->query_result($result,$i,'picklist_valueid');
            }
            $query = 'DELETE FROM vtiger_role2picklist WHERE picklistvalueid IN ('.generateQuestionMarks($picklistValueIdToDelete).')';
            $db->pquery($query,$picklistValueIdToDelete);
        }

        $query = 'DELETE FROM '. $this->getPickListTableName($pickListFieldName).
					' WHERE '.$pickListFieldName.' IN ('.  generateQuestionMarks($valueToDelete).') OR '.$pickListFieldName.' IN ('.generateQuestionMarks($encodedValueToDelete).')';
        $db->pquery($query,$mergedValuesToDelete);

        vimport('~~/include/utils/CommonUtils.php');
        $tabId = getTabId($moduleName);
        $query = 'DELETE FROM vtiger_picklist_dependency WHERE sourcevalue IN ('. generateQuestionMarks($valueToDelete) .')'.
				' AND sourcefield=?';
		$params = array();
		array_push($params, $valueToDelete);
		array_push($params, $pickListFieldName);
        $db->pquery($query, $params);

        $query='SELECT tablename,columnname FROM vtiger_field WHERE fieldname=? AND presence in (0,2)';
        $result = $db->pquery($query, array($pickListFieldName));
        $num_row = $db->num_rows($result);

        for($i=0; $i<$num_row; $i++) {
            $row = $db->query_result_rowdata($result, $i);
            $tableName = $row['tablename'];
            $columnName = $row['columnname'];

            $query = 'UPDATE '.$tableName.' SET '.$columnName.'=? WHERE '.$columnName.' IN ('.  generateQuestionMarks($valueToDelete).')';
			$params = array($replaceValue);
			array_push($params, $valueToDelete);
            $db->pquery($query, $params);
        }
		
		$query = 'UPDATE vtiger_field SET defaultvalue=? WHERE defaultvalue IN ('. generateQuestionMarks($valueToDelete) .') AND columnname=?';
		$params = array($replaceValue);
		array_push($params, $valueToDelete);
		array_push($params, $columnName);
		$db->pquery($query, $params);
		
		$em = new VTEventsManager($db);
		$data = array();
		$data['fieldname'] = $pickListFieldName;
		$data['valuetodelete'] = $valueToDelete;
		$data['replacevalue'] = $replaceValue;
		$data['module'] = $moduleName;
		$em->triggerEvent('vtiger.picklist.afterdelete', $data);

        return true;
    }

    public function enableOrDisableValuesForRole($picklistFieldName, $valuesToEnables, $valuesToDisable, $roleIdList) {
        $db = PearDatabase::getInstance();
        //To disable die On error since we will be doing insert without chekcing
        $dieOnErrorOldValue = $db->dieOnError;
        $db->dieOnError = false;

		$sql = "select picklistid from vtiger_picklist where name=?";
		$result = $db->pquery($sql, array($picklistFieldName));
		$picklistid = $db->query_result($result, 0, "picklistid");

		$pickListValueList = array_merge($valuesToEnables,$valuesToDisable);
		
		//As older look utf8 characters are pushed as html-entities,and in new utf8 characters are pushed to database
		//so we are checking for both the values
		foreach ($pickListValueList as $key => $value) {
			$encodedValueToAssign[$key]  = Vtiger_Util_Helper::toSafeHTML($value);
		}
		
        $pickListValueDetails = array();
        $query = 'SELECT picklist_valueid,'. $picklistFieldName.
                 ' FROM '.$this->getPickListTableName($picklistFieldName).
                 ' WHERE '.$picklistFieldName .' IN ('.  generateQuestionMarks($pickListValueList).') OR '.$picklistFieldName .' IN ('.  generateQuestionMarks($encodedValueToAssign).')';
		$params = array();
		array_push($params, $pickListValueList);
		array_push($params, $encodedValueToAssign);
        $result = $db->pquery($query, $params);
		$num_rows = $db->num_rows($result);

        for($i=0; $i<$num_rows; $i++) {
            $row = $db->query_result_rowdata($result,$i);

            $pickListValueDetails[decode_html($row[$picklistFieldName])] =array('picklistvalueid'=>$row['picklist_valueid'],
																	'picklistid'=>$picklistid);
        }
	$insertValueList = array();
        $deleteValueList = array();
        foreach($roleIdList as $roleId) {
            foreach($valuesToEnables  as $picklistValue) {
		        $valueDetail = $pickListValueDetails[$picklistValue];
				if(empty($valueDetail)){
					 $valueDetail = $pickListValueDetails[Vtiger_Util_Helper::toSafeHTML($picklistValue)];
				}
                $pickListValueId = $valueDetail['picklistvalueid'];
                $picklistId = $valueDetail['picklistid'];
                $insertValueList[] = '("'.$roleId.'","'.$pickListValueId.'","'.$picklistId.'")';
            }

            foreach($valuesToDisable as $picklistValue) {
                $valueDetail = $pickListValueDetails[$picklistValue];
				if(empty($valueDetail)){
					 $valueDetail = $pickListValueDetails[Vtiger_Util_Helper::toSafeHTML($picklistValue)];
				}
                $pickListValueId = $valueDetail['picklistvalueid'];
                $picklistId = $valueDetail['picklistid'];
                $deleteValueList[] = ' ( roleid = "'.$roleId.'" AND '.'picklistvalueid = "'.$pickListValueId.'") ';
            }
        }
	
	$query = 'INSERT IGNORE INTO vtiger_role2picklist (roleid,picklistvalueid,picklistid) VALUES '.implode(',',$insertValueList);
	$result = $db->pquery($query,array());
	var_dump($result, $query);

	$deleteQuery = 'DELETE FROM vtiger_role2picklist WHERE '.implode(' OR ',$deleteValueList);

	$result = $db->pquery($deleteQuery,array());

        //retaining to older value
        $db->dieOnError = $dieOnErrorOldValue;

    }

    /*
     * ED141127 : ajout de colonnes uicolor et uiicon
     */
    public function updateSequence($pickListFieldName , $picklistValues, $picklistData, $picklistProperties) {
        $db = PearDatabase::getInstance();
	
	/* table have uicolor and uiicon columns ? */
	$ui = array();
	if(is_array($picklistData)){
	    $query = 'SHOW COLUMNS FROM '.$this->getPickListTableName($pickListFieldName).'  LIKE \'ui%\' ';
	    $columns = $db->pquery($query, array());
	    $num_rows = $db->num_rows($columns);

	    for($i=0; $i<$num_rows; $i++) {
		$row = $db->query_result_rowdata($columns,$i);
		$ui[$row['field']] = TRUE;
	    }
	    /*var_dump( $ui );
	    unset($ui['uicolor']);*/
	    /* add missing columns */
	    $uiproperties = array();
	    $params = array();
	    $properties_sql = "";
	    foreach($picklistProperties as $property => $enabled){
		if($enabled) $uiproperties[] = $property;
		if(strlen($properties_sql))
		    $properties_sql .= ', ';
		else
		    $properties_sql = ' SET ';
		$properties_sql .= $property . ' = ?';
		$params[] = $enabled;
	    }
	    $properties_sql = "UPDATE vtiger_picklist "
		. $properties_sql
		. " WHERE name = ?";
	    $params[] = $pickListFieldName;
	    //var_dump( $properties_sql, $params );
	    $db->pquery($properties_sql, $params) ;
	    
	    // TODO toutes les picklists ne sont pas dans cette table !
	    
	    foreach($uiproperties as $uicolumn){
		if(!isset($ui[$uicolumn])){
		    $query = 'ALTER TABLE `'.$this->getPickListTableName($pickListFieldName).'` ADD `'.$uicolumn.'` VARCHAR(128) NULL';
		    //var_dump( $query );
		    $result = $db->pquery($query, array());
		    $ui[$uicolumn] = $result;
		}
	    }
	}
    
        $query = 'UPDATE '.$this->getPickListTableName($pickListFieldName)
	.' SET sortorderid = CASE ';
        foreach($picklistValues as $values => $sequence) {
            $query .= ' WHEN '.$pickListFieldName.'="'.$db->sql_escape_string($values).'" OR '.$pickListFieldName.'="'.$db->sql_escape_string(Vtiger_Util_Helper::toSafeHTML($values)).'" THEN "'.$sequence.'"';
        }
	$query .= ' END';
        
	/* ui columns */
	foreach($ui as $uicolumn => $enabled)
	    if($enabled){
		$query .= ', '.$uicolumn.' = CASE ';
		foreach($picklistData as $values => $data) {
		    $query .= ' WHEN '.$pickListFieldName.'="'.$db->sql_escape_string($values).'" OR '.$pickListFieldName.'="'.$db->sql_escape_string(Vtiger_Util_Helper::toSafeHTML($values)).'" THEN "'.$data[$uicolumn].'"';
		}
		$query .= ' END';
	    }
	
	$result = $db->pquery($query, array());
    }


    public static function getPicklistSupportedModules() {
         $db = PearDatabase::getInstance();

        // vtlib customization: Ignore disabled modules.
        $query = 'SELECT distinct vtiger_tab.tablabel, vtiger_tab.name as tabname
                  FROM vtiger_tab
                        inner join vtiger_field on vtiger_tab.tabid=vtiger_field.tabid
                  WHERE uitype IN (15,33,16) and vtiger_field.tabid NOT IN (29,10)  and vtiger_tab.presence != 1 and vtiger_field.presence in (0,2)
                  ORDER BY vtiger_tab.tabid ASC';
        // END
        $result = $db->pquery($query, array());

        $modulesModelsList = array();
        while($row = $db->fetch_array($result)){
            $moduleLabel = $row['tablabel'];
            $moduleName  = $row['tabname'];
            $instance = new self();
            $instance->name = $moduleName;
            $instance->label = $moduleLabel;
            $modulesModelsList[] = $instance;
        }
        return $modulesModelsList;
    }


    /**
	 * Static Function to get the instance of Vtiger Module Model for the given id or name
	 * @param mixed id or name of the module
	 */
	public static function getInstance($value) {
		//TODO : add caching
		$instance = false;
		    $moduleObject = parent::getInstance($value);
		    if($moduleObject) {
			$instance = self::getInstanceFromModuleObject($moduleObject);
		    }
		return $instance;
	}

	/**
	 * Function to get the instance of Vtiger Module Model from a given Vtiger_Module object
	 * @param Vtiger_Module $moduleObj
	 * @return Vtiger_Module_Model instance
	 */
	public static function getInstanceFromModuleObject(Vtiger_Module $moduleObj){
		$objectProperties = get_object_vars($moduleObj);
		$moduleModel = new self();
		foreach($objectProperties as $properName=>$propertyValue){
			$moduleModel->$properName = $propertyValue;
		}
		return $moduleModel;
	}
}