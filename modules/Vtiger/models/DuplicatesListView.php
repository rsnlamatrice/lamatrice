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
	
		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();

		$listQuery = $this->getQuery($startIndex, $pageLimit);
		//echo("<p style=\"margin-top:6em\"> ICICICI getListViewEntries $listQuery </p>");

		//duplicatestatus, duplicatefields
		$orderBy = 'crmid1, crmid';
		$sortOrder = 'ASC';
		$listQuery .= ' ORDER BY '. $orderBy . ' ' .$sortOrder;


	//echo("<br><br><br>333333333333333333 getListViewEntries <pre>$listQuery</pre> ");
	//die();
		$viewid = ListViewSession::getCurrentView($moduleName);
		ListViewSession::setSessionQuery($moduleName, $listQuery, $viewid);

		$listQuery .= " LIMIT 0,".($pageLimit+1);//suppression $startIndex car intégré dans getQuery($startIndex, $pageLimit)

		
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
		$firstEntityId1 = 0;
		$firstEntityId2 = 0;
		$groupEntities = array();
		
		//echo '<br><br><br><br>';
		
		//foreach($listViewEntries as $recordId => $record) {
		for($index = 0; $index < count($listViewEntries); $index++){
			$rawData = $db->query_result_rowdata($listResult, $index);
			$recordId = $rawData['crmid'];
			if($listViewRecordModels[$recordId] || $groupEntities[$recordId])
				continue;
			$record = $listViewEntries[$recordId];
			$record['id'] = $recordId;
			$record['crmid1'] = $rawData['crmid1'];
			$record['crmid2'] = $rawData['crmid2'];
			$record['duplicatestatus'] = $rawData['duplicatestatus'];
			$record['duplicatefields'] = $rawData['duplicatefields'];
			//$listViewRecordModels[$recordId] = $moduleModel->getRecordFromArray($record, $rawData);
			$listViewRecordModel = $moduleModel->getRecordFromArray($record, $rawData);
			//Nouveau groupe
			if( $firstEntityId1 != $rawData['crmid1']
			&& $firstEntityId2 != $rawData['crmid2']
			&& $firstEntityId1 != $rawData['crmid2']
			&& $firstEntityId2 != $rawData['crmid1']){
				
				//ajoute les précédents
				if($firstEntityId){
					$groupEntities[$firstEntityId]->set('duplicates_group_length', count($groupEntities));
					//complete list. array_merge does not work to keep order
					foreach($groupEntities as $id=>$groupEntity)
						$listViewRecordModels[$id] = $groupEntity;
				}
				$groupEntities = array();
				$firstEntityId = $recordId;
				$firstEntityId1 = $rawData['crmid1'];
				$firstEntityId2 = $rawData['crmid2'];
			}
			$groupEntities[$recordId] = $listViewRecordModel;
		}
		//Supprime le dernier groupe qui n'est peut-être pas complet
		
		return $listViewRecordModels;
	}

	//TODO en théorie, le nom de la table vtiger_duplicateentities dépend du module
	function getQuery($startIndex = 0, $pageLimit = 30) {
		$queryGenerator = $this->get('query_generator');
		$listQuery = $queryGenerator->getQuery();
			
		//FROM xxx WHERE
		$sqlParts = explode(' WHERE ', $listQuery);
		
		//status
		$requestSearchQuery = $this->getRequestSearchQuery();
		if($requestSearchQuery)
			$requestSearchQuery = "WHERE $requestSearchQuery";
			
		//TODO limit query because of very slow query
		$sqlParts[0] .= ' INNER JOIN (
				SELECT *
				FROM vtiger_duplicateentities
				'.$requestSearchQuery.'
				LIMIT '.$startIndex.', '.($pageLimit * 2).'
			) vtiger_duplicateentities
			ON vtiger_crmentity.crmid = vtiger_duplicateentities.crmid1
			OR vtiger_crmentity.crmid = vtiger_duplicateentities.crmid2
		';
		
		$listQuery = implode(' WHERE ', $sqlParts);
		//SELECT
		$listQuery = preg_replace('/^\s*SELECT\s/i'
								  , 'SELECT vtiger_crmentity.crmid
									, vtiger_duplicateentities.duplicatestatus
									, vtiger_duplicateentities.duplicatefields
									, vtiger_duplicateentities.crmid1
									, vtiger_duplicateentities.crmid2
									, '
								  , $listQuery);
		
		return $listQuery;
	}
	
	function getRequestSearchQuery(){
		
		$searchKey = $this->get('search_key');
		$searchValue = $this->get('search_value');
		
		if(empty($searchKey)) {
			$searchKey = array('duplicatestatus');
			$searchValue = array(0);
		}
		elseif(! is_array($searchKey)) {
			$searchKey = array($searchKey);
			$searchValue = array($searchValue);
		}
		$query = '';
		for($i = 0; $i < count($searchKey); $i++){
			if(!$searchValue[$i] && $searchValue[$i] != '0')
				continue;
			if($query)
				$query .= ' AND ';
			$query .= $searchKey[$i] . '= \'' . str_replace("'", "\\'", $searchValue[$i]) . '\'';
		}
		
		return $query;
	}
	
	/** ED150904
	 * Function to get the alphabet fields
	 * @return <Array> - List of Vtiger_Field_Model instances
	 */
	public function getAlphabetFields($listViewHeaders) {
		$headerFieldModels = array();
		$moduleAlphabetFields = array('duplicatestatus', 'duplicatefields');
		foreach($moduleAlphabetFields as $fieldName) {
			$fieldModel = new Vtiger_Field_Model();
			$fieldModel->set('name', $fieldName);
			$fieldModel->set('label', $fieldName);
			$fieldModel->set('column', $fieldName);
			$fieldModel->set('table', 'vtiger_duplicateentities');
			$fieldModel->set('uitype', 402);
			$fieldModel->set('datatype', 'V~M');
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
	
	/**
	 * Function to get the list view entries
	 * @param Vtiger_Paging_Model $pagingModel
	 * @return <Array> - Associative array of record id mapped to Vtiger_Record_Model instance.
	 */
	public function getListViewCount() {
		$db = PearDatabase::getInstance();

		$queryGenerator = $this->get('query_generator');
		
		$this->setListViewSearchConditions();
		
		$requestSearchQuery = $this->getRequestSearchQuery();
		if($requestSearchQuery)
			$requestSearchQuery = "WHERE $requestSearchQuery";
		$listQuery = 'SELECT COUNT(*)
				FROM vtiger_duplicateentities
				'.$requestSearchQuery.'
		';
		$listResult = $db->pquery($listQuery, array());
		if(!$listResult){
			$db->echoError('Impossible de compter le nombre de lignes.');
			echo '<pre>'; print_r($listQuery); echo '</pre>'; 
			return 0;
		}
		return $db->query_result($listResult, 0, 0);
	}
}
