<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Vtiger_FindDuplicate_Model extends Vtiger_Base_Model {

	public function setModule($moduleModel) {
		$this->module = $moduleModel;
		return $this;
	}

	public function getModule() {
		return $this->module;
	}

	function getListViewHeaders() {
		$db = PearDatabase::getInstance();
		$moduleModel = $this->getModule();
		$listViewHeaders = array();
		$listViewHeaders[] = new Vtiger_Base_Model(array('name' => 'recordid', 'label' => 'Record Id'));
		$headers = $db->getFieldsArray($this->result);
		foreach($headers as $header) {
			$fieldModel = $moduleModel->getFieldByColumn($header);
			if($fieldModel) {
				$listViewHeaders[] = $fieldModel;
			}
		}
		return $listViewHeaders;
	}

	function getListViewEntries(Vtiger_Paging_Model $paging) {
        $db = PearDatabase::getInstance();
        $moduleModel = $this->getModule();
        $module = $moduleModel->getName();

        $fields = $this->get('fields');
        $fieldModels = $moduleModel->getFields();
        if(is_array($fields)) {
            foreach($fields as $fieldName) {
                $fieldModel = $fieldModels[$fieldName];
                $tableColumns[] = $fieldModel->get('table').'.'.$fieldModel->get('column');
            }
        }

        $startIndex = $paging->getStartIndex();
        $pageLimit = $paging->getPageLimit();
		$ignoreEmpty = $this->get('ignoreEmpty');

		//ED150910
		$source_query = $this->get('source_query');//selected ids
		$among_query = $this->get('among_query');//among selected ids


		//ED151220 : possibilité de ne choisir que 2 enregistrements à fusionner, sans recherche de doublon
		$focus = CRMEntity::getInstance($module);
		$query = $focus->getQueryForDuplicates($module, $tableColumns, '', $ignoreEmpty, $source_query, $among_query);

		$query .= " LIMIT $startIndex, ". ($pageLimit+1);
		$result = $db->pquery($query, array());
		if(!$result){
			echo $db->echoError($query);
			die();
		}
		//echo "<br><br><br><br><br><pre>$query</pre>";

        $rows = $db->num_rows($result);
		$this->result = $result;

        $group = 'group0';
        $temp = $fieldValues = array(); $groupCount = 0;
        $groupRecordCount = 0;
        $entries = array();
        for($i=0; $i<$rows; $i++) {
			$entries[] = $db->query_result_rowdata($result, $i);
		}

		$paging->calculatePageRange($entries);

        if ($rows > $pageLimit) {
			array_pop($entries);
            $paging->set('nextPageExists', true);
        } else {
            $paging->set('nextPageExists', false);
        }
		$rows = count($entries);

		for ($i=0; $i<$rows; $i++) {
			$row = $entries[$i];
            if($i != 0 && $tableColumns) {
                //ED150910 I have added a record_label column $slicedArray = array_slice($row, 2);
				// nota : $row[0] and $row['recordid'] exist, so double counter
                $slicedArray = array_slice($row, 4);
                array_walk($temp, 'lower_array');
                array_walk($slicedArray, 'lower_array');
                $arrDiff = array_diff($temp, $slicedArray);
                if(count($arrDiff) > 0) {
                    $groupCount++;
                    $temp = $slicedArray;
                    $groupRecordCount = 0;
                }
                $group = "group".$groupCount;
            }
            $fieldValues[$group][$groupRecordCount]['recordid'] = $row['recordid'];
			$nColumn = 0;
			foreach($row as $field => $value) {
                //ED150910 I have added a record_label column $slicedArray = array_slice($row, 2);
                // nota : $row[0] and $row['recordid'] exist, so double counter
                if($i == 0 && $nColumn++ >= 4 /*$field != 'recordid' && $field != 'record_label'*/) $temp[$field] = $value;
                $fieldModel = $fieldModels[$field];
                $resultRow[$field] = $value;
            }
            $fieldValues[$group][$groupRecordCount++] = $resultRow;
        }
		//var_dump($fieldValues);
        return $fieldValues;
    }

	public static function getInstance($moduleName) {

		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'FindDuplicate', $moduleName);
		$instance = new $modelClassName();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		return $instance->setModule($moduleModel);
	}

	public function getRecordCount() {
		if($this->rows) {
			$rows = $this->rows;
		} else {
			$db = PearDatabase::getInstance();

			$moduleModel = $this->getModule();
			$module = $moduleModel->getName();
			$fields = $this->get('fields');
			$fieldModels = $moduleModel->getFields();
			if(is_array($fields)) {
				foreach($fields as $fieldName) {
					$fieldModel = $fieldModels[$fieldName];
					$tableColumns[] = $fieldModel->get('table').'.'.$fieldModel->get('column');
				}
			}
			$focus = CRMEntity::getInstance($module);
			$ignoreEmpty = $this->get('ignoreEmpty');

			//ED151220 : possibilité de ne choisir que 2 enregistrements à fusionner, sans recherche de doublon
			//ED150910
			$source_query = $this->get('source_query');//selected ids
			$among_query = $this->get('among_query');//among selected ids

			$query = $focus->getQueryForDuplicates($module, $tableColumns, '', $ignoreEmpty, $source_query, $among_query);

			$position = stripos($query, 'from');
			if ($position) {
				$split = preg_split('/from /i', $query);
				$splitCount = count($split);
				$query = 'SELECT count(*) AS count ';
				for ($i=1; $i<$splitCount; $i++) {
					$query = $query. ' FROM ' .$split[$i];
				}
			}
			$result = $db->pquery($query, array());
			$rows = $db->query_result($result, 0, 'count');

		}
		return $rows;
	}

	/* Modules concernés par la recherche de doublons
	 */
	public static function getScheduledSearchModules(){
		//TODO $modules = Vtiger_Module_Model::getAll();
		return array('Contacts');
	}

	/* Inject duplicated records in vtiger_duplicateentities table
	 * http://localhost/lamatrice/index.php?module=Contacts&action=FindDuplicate&mode=runScheduledSearch
	 */
	public function runScheduledSearch(){
		$moduleModel = $this->getModule();
		$moduleName = $moduleModel->getName();
		$duplicateTableName = $this->getDuplicateEntitiesTable();
		$tableColumns = $this->getFindDuplicateFields();

		if(!$tableColumns)
			return;

		if(!is_array($tableColumns[0]))
			$tableColumns = array($tableColumns);

		for($nTableColumns = 0; $nTableColumns < count($tableColumns); $nTableColumns++){
			$moduleQuery = $this->getScheduledSearchBasicQuery($moduleName, $tableColumns[$nTableColumns]);

			//echo "<pre>getScheduledSearchBasicQuery.moduleQuery : $moduleQuery</pre>";

			$focus = CRMEntity::getInstance($moduleName);
			$fields = $moduleModel->getFields();
			/*foreach($tableColumns[$nTableColumns] as $n => $tableColumn)
				$tableColumns[$nTableColumns][$n] = $fields[$tableColumn]->table . '.' . $tableColumn;
			var_dump($tableColumns[$nTableColumns]);*/
			//$query = $focus->getQueryForDuplicates($moduleName, $tableColumns[$nTableColumns], '', false, $moduleQuery, $moduleQuery);
			//echo "<pre>getQueryForDuplicates.Query : $query</pre>";

			//$moduleQuery .= ' LIMIT 1000';

			$query = 'SELECT crm1.'.$focus->table_index . ', crm2.'.$focus->table_index . '
				, 0 AS duplicatestatus
				, \''.implode(',',$tableColumns[$nTableColumns]).'\' AS  duplicatefields
				, NULL AS mergeaction
				, NOW() AS checkdate
				FROM (' . $moduleQuery . ') as crm1
				INNER JOIN (' . $moduleQuery . ') as crm2
					ON crm1.'.$focus->table_index.' < crm2.'.$focus->table_index.' /* rend unique le couple */
			';
			foreach($tableColumns[$nTableColumns] as $n => $tableColumn){
				$query .= " AND crm1.$tableColumn = crm2.$tableColumn";
			}

			$queryJoin = $this->getScheduledSearchJoinQuery($moduleName, $tableColumns[$nTableColumns]);
			if($queryJoin)
				$query .= '
					'.$queryJoin;

			$queryWhere = $this->getScheduledSearchWhereQuery($moduleName, $tableColumns[$nTableColumns]);
			if($queryWhere)
				$query .= '
					WHERE ' . $queryWhere;

			//echo "<pre>$query</pre>";
			//die();
			$query = 'INSERT INTO ' . $duplicateTableName . '
				(`crmid1`, `crmid2`, `duplicatestatus`, `duplicatefields`, `mergeaction`, `checkdate`)
				' . $query . '
				ON DUPLICATE KEY UPDATE mergeaction = mergeaction
			';

			//echo "<pre>$query</pre>";
			//continue;

			$db = PearDatabase::getInstance();
			$result = $db->query($query);
			if(!$result){
				$db->echoError();
			}

			$this->runCleanDuplicateTable();
		}
	}

	/**
	 * Retourne un filtre sur la requête de recherche de doublon.
	 * Cette requête est utilisée pour effectuer la recherche de doublons.
	 */
	function getScheduledSearchWhereQuery($moduleName, $tableColumns){
	}

	/**
	 * Retourne une simple requête sur tous les enregistrements de la table.
	 * Cette requête est utilisée pour effectuer la recherche de doublons.
	 */
	function getScheduledSearchBasicQuery($moduleName, $tableColumns){

		if(is_array($tableColumns[0])){
			//recherches multiples
			$query = '';
			for($i = 0; $i < count($tableColumns); $i++){
				if($i)
					$query .= ' UNION ';
				$query .= $this->getScheduledSearchBasicQuery($moduleName, $tableColumns[$i]);
			}
			return $query;
		}

		$moduleModel = $this->getModule();
		$moduleName = $moduleModel->getName();
		$focus = CRMEntity::getInstance($moduleName);
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$queryGenerator = new QueryGenerator($moduleName, $currentUser);
		$queryGenerator->initForAllCustomView();

		$moduleFields = $moduleModel->getFields();

		$fields = $tableColumns;
		$fields[] = 'id';
		$queryGenerator->setFields($fields);

		$query = $queryGenerator->getQuery();

		//Pas tous vide
		$query .= " AND NOT (";
		foreach($tableColumns as $n => $tableColumn){
			$columnTable = $moduleFields[$tableColumn]->table;
			if($n) $query .= " AND ";
			$query .= "IFNULL($columnTable.$tableColumn,'') = ''";
		}
		$query .= ")";

		return $query;
	}

	/* Fields to find duplicates
	 * @returns $tableColumns
	 * 	May be an array of column names or,  for multiple search, an array of array of column names
	 */
	public function getFindDuplicateFields(){
		return $this->getModule()->getNameFields();
	}

	/* Inject duplicated records in vtiger_duplicateentities table
	 */
	public function getDuplicateEntitiesTable(){
		$moduleName = $this->getModule()->getName();
		$focus = CRMEntity::getInstance($moduleName);
		return $focus->duplicate_entities_table;
	}

	/* nettoie la table des duplicates
	 */
	public function runCleanDuplicateTable(){

	}
}