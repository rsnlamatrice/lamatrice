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
 * Vtiger ListView Model Class
 */
class Vtiger_DuplicatesListView_Model extends Vtiger_ListView_Model {
	
	/**
	 * Function to get the list of listview links for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associate array of Link Type to List of Vtiger_Link_Model instances
	 */
	public function getListViewLinks($linkParams) {
		return array();
	}

	/**
	 * Function to get the list view entries
	 * @param Vtiger_Paging_Model $pagingModel
	 * @return <Array> - Associative array of record id mapped to Vtiger_Record_Model instance.
	 */
	public function getListViewEntries($pagingModel) {
		$db = PearDatabase::getInstance();

		$moduleName = $this->getModule()->get('name');
		$moduleFocus = CRMEntity::getInstance($moduleName);
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$queryGenerator = $this->get('query_generator');
		$listViewContoller = $this->get('listview_controller');

		//echo "<br><br><br><br>".__FILE__;
		
		$searchKey = $this->get('search_key');
		$searchValue = $this->get('search_value');
		$operator = $this->get('operator');
		if(!empty($searchKey)) {
			//var_dump(array('search_field' => $searchKey, 'search_text' => array_map(mb_detect_encoding, $searchValue), 'operator' => $operator));
			$queryGenerator->addUserSearchConditions(array('search_field' => $searchKey, 'search_text' => $searchValue, 'operator' => $operator));
		}

		$listQuery = $this->getQuery();
		//echo("<p style=\"margin-top:6em\"> ICICICI getListViewEntries $listQuery </p>");
	
		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();

		//duplicatestatus, duplicatefields
		$orderBy = 'crmid1, crmid';
		$sortOrder = 'ASC';
		$listQuery .= ' ORDER BY '. $orderBy . ' ' .$sortOrder;


	//echo("<br><br><br>333333333333333333 getListViewEntries <pre>$listQuery</pre> ");
	
		$viewid = ListViewSession::getCurrentView($moduleName);
		ListViewSession::setSessionQuery($moduleName, $listQuery, $viewid);

		$listQuery .= " LIMIT $startIndex,".($pageLimit+1);

		$listResult = $db->pquery($listQuery, array());
		if(!$listResult){
			echo $db->echoError() . '<pre>' . $listQuery . '</pre>';
		}
		
		$listViewRecordModels = array();
				
		// ICI LE PBLM DE CHAMPS QUI DISPARAISSENT RATTRAPPABLE PLUS BAS dans $moduleModel->getRecordFromArray($record, $rawData);
		$listViewEntries =  $listViewContoller->getListViewRecords($moduleFocus, $moduleName, $listResult); 
		$pagingModel->calculatePageRange($listViewEntries);

		if($db->num_rows($listResult) > $pageLimit){
			array_pop($listViewEntries);
			$pagingModel->set('nextPageExists', true);
		}else{
			$pagingModel->set('nextPageExists', false);
		}
/*var_dump(get_class($listViewContoller));
$reflector = new ReflectionClass($listViewContoller);
echo $reflector->getFileName();
var_dump($listResult);*/
                    
		$index = 0;
		$firstEntityId = 0;
		$groupEntities = array();
		//foreach($listViewEntries as $recordId => $record) {
		for($index = 0; $index < count($listViewEntries); $index++){
			$rawData = $db->query_result_rowdata($listResult, $index);
			$recordId = $rawData['crmid'];
			$record = $listViewEntries[$recordId];
			$record['id'] = $recordId;
			$record['crmid1'] = $rawData['crmid1'];
			$record['crmid2'] = $rawData['crmid2'];
			$record['duplicatestatus'] = $rawData['duplicatestatus'];
			$record['duplicatefields'] = $rawData['duplicatefields'];
			//$listViewRecordModels[$recordId] = $moduleModel->getRecordFromArray($record, $rawData);
			$listViewRecordModel = $moduleModel->getRecordFromArray($record, $rawData);
		
			//Nouveau groupe
			if( $firstEntityId != $rawData['crmid1']
			&& $firstEntityId != $rawData['crmid2']){
				//ajoute les précédents
				if($firstEntityId){
					$groupEntities[$firstEntityId]->set('duplicates_group_length', count($groupEntities));
					foreach($groupEntities as $id=>$groupEntity)
						$listViewRecordModels[$id] = $groupEntity;
				}
				$groupEntities = array();
				$firstEntityId = $recordId;
			}
			$groupEntities[$recordId] = $listViewRecordModel;
		}
		//Supprime le dernier groupe qui n'est peut-être pas complet
		
		return $listViewRecordModels;
	}

	function getQuery() {
		$queryGenerator = $this->get('query_generator');
		$listQuery = $queryGenerator->getQuery();
		
		$status = array(0);
		
		$sqlParts = explode(' WHERE ', $listQuery);
		
		$sqlParts[0] .= ' INNER JOIN vtiger_duplicateentities
			ON (vtiger_crmentity.crmid = vtiger_duplicateentities.crmid1
			OR vtiger_crmentity.crmid = vtiger_duplicateentities.crmid2)
			AND duplicatestatus IN (' . implode(',', $status) . ')
		';
		
		$listQuery = implode(' WHERE ', $sqlParts);
		$listQuery = preg_replace('/^\s*SELECT\s/i'
								  , 'SELECT vtiger_crmentity.crmid
									, vtiger_duplicateentities.duplicatestatus
									, vtiger_duplicateentities.duplicatefields
									, CONCAT(vtiger_duplicateentities.crmid1, \'|\', vtiger_duplicateentities.crmid2) AS duplicatesgroupkey
									, vtiger_duplicateentities.crmid1
									, vtiger_duplicateentities.crmid2
									, '
								  , $listQuery);
		
		return $listQuery;
	}
	
	/** ED150904
	 * Function to get the alphabet fields
	 * @return <Array> - List of Vtiger_Field_Model instances
	 */
	public function getAlphabetFields($listViewHeaders) {
		$headerFieldModels = array();
		$moduleAlphabetFields = array('duplicatestatus', 'duplicatefields');
		foreach($moduleAlphabetFields as $fieldName) {
			if(!array_key_exists($fieldName, $listViewHeaders))
				continue;
			$fieldModel = $listViewHeaders[$fieldName];
			if($fieldModel)
				$headerFieldModels[$fieldName] = $fieldModel;
		}
		return $headerFieldModels;
	}
	
	
	
	/**
	 * Static Function to get the Instance of Vtiger ListView model for a given module and custom view
	 * @param <String> $moduleName - Module Name
	 * @param <Number> $viewId - Custom View Id
	 * @return Vtiger_ListView_Model instance
	 *
	 * ED150121 : au chargement de la page, on a la liste par défaut (all) avec ses colonnes issues de <CRMEntity> -> list_fields_name .
	 * au rechargement d'une vue, ok.
	 */
	public static function getInstance($moduleName, $viewId='0', $moreFilters = FALSE) {
		if(!$viewId || $viewId === '0'){
			$customView = new CustomView();
			$viewId = $customView->getViewId($moduleName);
		}
		return self::getInstanceWithClassName('DuplicatesListView', $moduleName, $viewId, $moreFilters);
	}
}
