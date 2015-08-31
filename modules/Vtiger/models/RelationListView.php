<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_RelationListView_Model extends Vtiger_Base_Model {

	protected $relationModel = false;
	protected $parentRecordModel = false;

	public function setRelationModel($relation){
		$this->relationModel = $relation;
		return $this;
	}

	public function getRelationModel() {
		return $this->relationModel;
	}

	public function setParentRecordModel($parentRecord){
		$this->parentRecordModel = $parentRecord;
		return $this;
	}

	public function getParentRecordModel(){
		return $this->parentRecordModel;
	}

	//ED150817
	public function getRelatedModuleModel() {
		$relationModel = $this->getRelationModel();
		if($relationModel)
			return $this->getRelationModel()->getRelationModuleModel();
		
		$relationModuleName = $this->get('relationModuleName');
		return Vtiger_Module_model::getInstance($relationModuleName);
	}

	public function getCreateViewUrl(){
		$relationModel = $this->getRelationModel();
		$relatedModel = $relationModel->getRelationModuleModel();
		$parentRecordModule = $this->getParentRecordModel();
		$parentModule = $parentRecordModule->getModule();

		$createViewUrl = $relatedModel->getCreateRecordUrl().'&sourceModule='.$parentModule->get('name').
								'&sourceRecord='.$parentRecordModule->getId().'&relationOperation=true';

		//To keep the reference fieldname and record value in the url if it is direct relation
		if($relationModel->isDirectRelation()) {
			$relationField = $relationModel->getRelationField();
			$createViewUrl .='&'.$relationField->getName().'='.$parentRecordModule->getId();
		}
		return $createViewUrl;
	}

	/* ED150124 */
	public function getDeleteViewUrl(){
		$relationModel = $this->getRelationModel();
		$relatedModel = $relationModel->getRelationModuleModel();
		$parentRecordModule = $this->getParentRecordModel();
		$parentModule = $parentRecordModule->getModule();

		$createViewUrl = $relatedModel->getDeleteRelationUrl().'&sourceModule='.$parentModule->get('name').
								'&sourceRecord='.$parentRecordModule->getId().'&relationOperation=true';

		//To keep the reference fieldname and record value in the url if it is direct relation
		if($relationModel->isDirectRelation()) {
			$relationField = $relationModel->getRelationField();
			$createViewUrl .='&'.$relationField->getName().'='.$parentRecordModule->getId();
		}
		return $createViewUrl;
	}

	public function getCreateEventRecordUrl(){
		$relationModel = $this->getRelationModel();
		$relatedModel = $relationModel->getRelationModuleModel();
		$parentRecordModule = $this->getParentRecordModel();
		$parentModule = $parentRecordModule->getModule();

		$createViewUrl = $relatedModel->getCreateEventRecordUrl().'&sourceModule='.$parentModule->get('name').
								'&sourceRecord='.$parentRecordModule->getId().'&relationOperation=true';

		//To keep the reference fieldname and record value in the url if it is direct relation
		if($relationModel->isDirectRelation()) {
			$relationField = $relationModel->getRelationField();
			$createViewUrl .='&'.$relationField->getName().'='.$parentRecordModule->getId();
		}
		return $createViewUrl;
	}

	/* ED150124 */
	public function getDeleteRelationUrl(){
		$relationModel = $this->getRelationModel();
		$relatedModule = $relationModel->getRelationModuleModel();
		$parentRecordModule = $this->getParentRecordModel();
		$parentModule = $parentRecordModule->getModule();

		$deleteViewUrl = $parentModule->getDeleteRelationUrl().
							'&relatedModule='.$relatedModule->get('name').
							'&record='.$parentRecordModule->getId().
							'&relationOperation=true';

		//To keep the reference fieldname and record value in the url if it is direct relation
		if($relationModel->isDirectRelation()) {
			$relationField = $relationModel->getRelationField();
			$deleteViewUrl .='&'.$relationField->getName().'='.$parentRecordModule->getId();
		}
		return $deleteViewUrl;
	}

	/* ED150811 */
	public function getPrintRelationUrl(){
		$relationModel = $this->getRelationModel();
		$relatedModule = $relationModel->getRelationModuleModel();
		$parentRecordModule = $this->getParentRecordModel();
		$parentModule = $parentRecordModule->getModule();

		$printViewUrl = $parentModule->getPrintRelationUrl().
							'&relatedModule='.$relatedModule->get('name').
							'&record='.$parentRecordModule->getId().
							'&relationOperation=true';

		//To keep the reference fieldname and record value in the url if it is direct relation
		if($relationModel->isDirectRelation()) {
			$relationField = $relationModel->getRelationField();
			$printViewUrl .='&'.$relationField->getName().'='.$parentRecordModule->getId();
		}
		return $printViewUrl;
	}

	/* ED150814 */
	public function getImportRelationUrl(){
		$relationModel = $this->getRelationModel();
		$relatedModule = $relationModel->getRelationModuleModel();
		$parentRecordModule = $this->getParentRecordModel();
		$parentModule = $parentRecordModule->getModule();

		$importViewUrl = $parentModule->getImportRelationUrl().
							'&relatedModule='.$relatedModule->get('name').
							'&record='.$parentRecordModule->getId().
							'&relationOperation=true';
							
		//To keep the reference fieldname and record value in the url if it is direct relation
		if($relationModel->isDirectRelation()) {
			$relationField = $relationModel->getRelationField();
			$importViewUrl .='&'.$relationField->getName().'='.$parentRecordModule->getId();
		}
		return $importViewUrl;
	}

	public function getCreateTaskRecordUrl(){
		$relationModel = $this->getRelationModel();
		$relatedModel = $relationModel->getRelationModuleModel();
		$parentRecordModule = $this->getParentRecordModel();
		$parentModule = $parentRecordModule->getModule();

		$createViewUrl = $relatedModel->getCreateTaskRecordUrl().'&sourceModule='.$parentModule->get('name').
								'&sourceRecord='.$parentRecordModule->getId().'&relationOperation=true';

		//To keep the reference fieldname and record value in the url if it is direct relation
		if($relationModel->isDirectRelation()) {
			$relationField = $relationModel->getRelationField();
			$createViewUrl .='&'.$relationField->getName().'='.$parentRecordModule->getId();
		}
		return $createViewUrl;
	}

	public function getLinks(){
		$relationModel = $this->getRelationModel();
		/*ED141016*/
		if($relationModel == null) return null;
		
		$actions = $relationModel->getActions();
		
		$selectLinks = $this->getSelectRelationLinks();
		foreach($selectLinks as $selectLinkModel) {
			$selectLinkModel->set('_selectRelation',true)->set('_module',$relationModel->getRelationModuleModel());
		}

		$deleteLinks = $this->getDeleteRelationLinks();
		foreach($deleteLinks as $deleteLinksModel) {
			$deleteLinksModel->set('_deleteRelation',true)->set('_module',$relationModel->getRelationModuleModel());
		}

		$importLinks = $this->getImportRelationLinks();
		foreach($importLinks as $importLinksModel) {
			$importLinksModel->set('_importRelation',true)->set('_module',$relationModel->getRelationModuleModel());
		}

		$printLinks = $this->getPrintRelationLinks();
		foreach($printLinks as $printLinksModel) {
			$printLinksModel->set('_printRelation',true)->set('_module',$relationModel->getRelationModuleModel());
		}
		$addLinks = $this->getAddRelationLinks();
		$links = array_merge($printLinks, $selectLinks, $importLinks, $addLinks, $deleteLinks);
		$relatedLink = array();
		$relatedLink['LISTVIEWBASIC'] = $links;
		return $relatedLink;
	}

	public function getSelectRelationLinks() {
		$relationModel = $this->getRelationModel();
		$selectLinkModel = array();

		if(!$relationModel->isSelectActionSupported()) {
			return $selectLinkModel;
		}

		$relatedModel = $relationModel->getRelationModuleModel();

		$selectLinkList = array(
			array(
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => vtranslate('LBL_SELECT')." ".vtranslate('SINGLE_' . $relatedModel->get('label'), $relatedModel->getName()),
				'linkurl' => '',
				'linkicon' => '',
			)
		);


		foreach($selectLinkList as $selectLink) {
			$selectLinkModel[] = Vtiger_Link_Model::getInstanceFromValues($selectLink);
		}
		return $selectLinkModel;
	}

	/* ED150124 */
	public function getDeleteRelationLinks() {
		$relationModel = $this->getRelationModel();
		$deleteLinkModel = array();
		
		if(!$relationModel->isDeleteActionSupported()) {
			return $deleteLinkModel;
		}

		$relatedModel = $relationModel->getRelationModuleModel();

		$deleteLinkList = array(
			array(
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => vtranslate('LBL_DELETE_RELATIONS', $relationModel->getParentModuleModel()->getName()),
				'linkurl' => $this->getDeleteRelationUrl(),
				'linkicon' => '',
			)
		);


		foreach($deleteLinkList as $deleteLink) {
			$deleteLinkModel[] = Vtiger_Link_Model::getInstanceFromValues($deleteLink);
		}
		return $deleteLinkModel;
	}

	public function getAddRelationLinks() {
		$relationModel = $this->getRelationModel();
		$addLinkModel = array();

		if(!$relationModel->isAddActionSupported()) {
			return $addLinkModel;
		}
		$relatedModel = $relationModel->getRelationModuleModel();

		if($relatedModel->get('label') == 'Calendar'){

			$addLinkList[] = array(
					'linktype' => 'LISTVIEWBASIC',
					'linklabel' => vtranslate('LBL_ADD_EVENT'),
					'linkurl' => $this->getCreateEventRecordUrl(),
					'linkicon' => '',
			);
			$addLinkList[] = array(
					'linktype' => 'LISTVIEWBASIC',
					'linklabel' => vtranslate('LBL_ADD_TASK'),
					'linkurl' => $this->getCreateTaskRecordUrl(),
					'linkicon' => '',
			);
		}else{
			$addLinkList = array(
				array(
					'linktype' => 'LISTVIEWBASIC',
					// NOTE: $relatedModel->get('label') assuming it to be a module name - we need singular label for Add action.
					'linklabel' => vtranslate('LBL_ADD')." ".vtranslate('SINGLE_' . $relatedModel->getName(), $relatedModel->getName()),
					'linkurl' => $this->getCreateViewUrl(),
					'linkicon' => '',
				)
			);
		}

		foreach($addLinkList as $addLink) {
			$addLinkModel[] = Vtiger_Link_Model::getInstanceFromValues($addLink);
		}
		return $addLinkModel;
	}


	/* ED150811 */
	public function getPrintRelationLinks() {
		$relationModel = $this->getRelationModel();
		$printLinkModel = array();
		
		if(!$relationModel->isPrintActionSupported()) {
			return $printLinkModel;
		}

		$relatedModel = $relationModel->getRelationModuleModel();

		$printLinkList = array(
			array(
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => vtranslate('LBL_PRINT_RELATIONS', $relationModel->getParentModuleModel()->getName()),
				'linkurl' => $this->getPrintRelationUrl(),
				'linkicon' => 'icon-print',
			)
		);


		foreach($printLinkList as $printLink) {
			$printLinkModel[] = Vtiger_Link_Model::getInstanceFromValues($printLink);
		}
		return $printLinkModel;
	}

	/* ED150814 */
	public function getImportRelationLinks() {
		$relationModel = $this->getRelationModel();
		$importLinkModel = array();
		
		if(!$relationModel->isSelectActionSupported()) {
			return $importLinkModel;
		}

		$relatedModel = $relationModel->getRelationModuleModel();

		$importLinkList = array(
			array(
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => vtranslate('LBL_IMPORT', $relationModel->getParentModuleModel()->getName()) . ' ' . vtranslate($relatedModel->getName()),
				'linkurl' => $this->getImportRelationUrl(),
				'linkicon' => '',
			)
		);


		foreach($importLinkList as $importLink) {
			$importLinkModel[] = Vtiger_Link_Model::getInstanceFromValues($importLink);
		}
		return $importLinkModel;
	}

	public function getEntries($pagingModel) {
		$db = PearDatabase::getInstance();
		//echo __FILE__; $db->setDebug(true);
		
		$parentModule = $this->getParentRecordModel()->getModule();
		$relationModule = $this->getRelationModel()->getRelationModuleModel();
		
		$relatedColumnFields = $relationModule->getConfigureRelatedListFields();
		if(count($relatedColumnFields) <= 0){
			$relatedColumnFields = $relationModule->getRelatedListFields();
		}
		
		$query = $this->getRelationQuery();
		
		//ED150704
		$searchKey = $this->get('search_key');
		if(!empty($searchKey)) {
			$query = $this->updateQueryWithSearchCondition($query);
		}

		//echo "<pre>".__FILE__." getRelationQuery : $query</pre>";
		if ($this->get('whereCondition')) {
			$query = $this->updateQueryWithWhereCondition($query);
		}
		
		
		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();

		$orderBy = $this->getForSql('orderby');
		$sortOrder = $this->getForSql('sortorder');
		if($orderBy) {

			$orderByFieldModuleModel = $relationModule->getFieldByColumn($orderBy);
			if($orderByFieldModuleModel && $orderByFieldModuleModel->isReferenceField()) {
			    //If reference field then we need to perform a join with crmentity with the related to field
			    $queryComponents = $split = spliti(' where ', $query);
			    $selectAndFromClause = $queryComponents[0];
			    $whereCondition = $queryComponents[1];
			    $qualifiedOrderBy = 'vtiger_crmentity'.$orderByFieldModuleModel->get('column');
			    $selectAndFromClause .= ' LEFT JOIN vtiger_crmentity AS '.$qualifiedOrderBy.' ON '.
						    $orderByFieldModuleModel->get('table').'.'.$orderByFieldModuleModel->get('column').' = '.
						    $qualifiedOrderBy.'.crmid ';
			    $query = $selectAndFromClause.' WHERE '.$whereCondition;
			    $query .= ' ORDER BY '.$qualifiedOrderBy.'.label '.$sortOrder;
			} elseif($orderByFieldModuleModel && $orderByFieldModuleModel->isOwnerField()) {
				$query .= ' ORDER BY CONCAT(vtiger_users.first_name, " ", vtiger_users.last_name) '.$sortOrder;
			} else{
				// Qualify the the column name with table to remove ambugity
				$qualifiedOrderBy = $orderBy;
				$orderByField = $relationModule->getFieldByColumn($orderBy);
				if ($orderByField) {
					$qualifiedOrderBy = $relationModule->getOrderBySql($qualifiedOrderBy);
				}
				$query = "$query ORDER BY $qualifiedOrderBy $sortOrder";
			}
		}
		$limitQuery = $query .' LIMIT '.$startIndex.','.($pageLimit+1); /* ED140907 + 1 instead of two db query */
		//echo "<pre>".__FILE__." : $query</pre>";
		//echo_callstack();
		$result = $db->pquery($limitQuery, array());
		//ED150704
		if(!$result){echo "<pre>".__FILE__." : $query</pre>";
			echo_callstack();
			
			echo "<code>Désolé, la formulation de la recherche provoque une erreur.</code>";
			return array();
		}
		$relatedRecordList = array();
		
		$max_rows = min($db->num_rows($result), $pageLimit);/* ED140907 + 1 instead of two db query */
		
		for($i=0; $i < $max_rows; $i++ ) {
			$row = $db->fetch_row($result,$i);
			$newRow = array();
			foreach($row as $col=>$val){
			    if(array_key_exists($col,$relatedColumnFields)){
				$newRow[$relatedColumnFields[$col]] = $val;
			    }
			}
		
			//To show the value of "Assigned to"
			$newRow['assigned_user_id'] = $row['smownerid'];
			$record = Vtiger_Record_Model::getCleanInstance($relationModule->get('name'));
			$record->setData($newRow)->setModuleFromInstance($relationModule);
			$record->setId($row['crmid']);
			/*if(isset($relatedRecordList[$row['crmid']]))
				var_dump($newRow);*/
			$relatedRecordList[$row['crmid']] = $record;
		}
		//var_dump($relatedRecordList);
		/* ED140917
		 * dans le cas d'une relation (n,n), il y a moins de $relatedRecordList que de lignes dans la table
		 * ce qui fausse les infos pour la navigation page-suivante
		echo $max_rows;
		echo ' -- ';
		echo count($relatedRecordList);
		*/
		
		$pagingModel->calculatePageRange($relatedRecordList);

		/* ED140907 + 1 instead of two db query */
		$pagingModel->set('nextPageExists', $db->num_rows($result) > $pageLimit);
		/*
		$nextLimitQuery = $query. ' LIMIT '.($startIndex+$pageLimit).' , 1';
		$nextPageLimitResult = $db->pquery($nextLimitQuery, array());
		if($db->num_rows($nextPageLimitResult) > 0){
			$pagingModel->set('nextPageExists', true);
		}else{
			$pagingModel->set('nextPageExists', false);
		}*/
		
		return $relatedRecordList;
	}

	public function getHeaders() {
		$relatedModuleModel = $this->getRelatedModuleModel();//ED150817

		$summaryFieldsList = $relatedModuleModel->getSummaryViewFieldsList();
		$headerFields = array();
		if(count($summaryFieldsList) > 0) {
			foreach($summaryFieldsList as $fieldName => $fieldModel) {
				$headerFields[$fieldName] = $fieldModel;
			}
		} else {
			$headerFieldNames = $relatedModuleModel->getRelatedListFields();
			foreach($headerFieldNames as $fieldName) {
				$headerFields[$fieldName] = $relatedModuleModel->getField($fieldName);
			}
		}
		
		switch($relatedModuleModel->name){
		case "Documents":
			$headerFields = array_merge($headerFields, $relatedModuleModel->getRelationHeaders());
		    	
			break;
	
		case "Campaigns":
			$headerFields = array_merge($headerFields, $relatedModuleModel->getRelationHeaders());
		    	
			break;
	
		default:
			$parentRecordModule = $this->getParentRecordModel();
			$parentModule = $parentRecordModule->getModule();
			switch($parentModule->getName()){
			case "Documents":
				$headerFields = array_merge($headerFields, $parentModule->getRelationHeaders());
				
				break;
		
			default:
				break;
			}
			break;
		}
		
		//ED150704
		$this->initListViewHeadersFilters($listViewHeaders);
		
		return $headerFields;
	}

	/** ED150414
	 * Function to init fields as list view header filters
	 * @return <Array> - List of Vtiger_Field_Model instances
	 */
	protected function initListViewHeadersFilters($listViewHeaders) {
		
		$search_fields = $this->get('search_key');
		$search_inputs = $this->get('search_input');
		$search_texts = $search_inputs ? $search_inputs : $this->get('search_value');
		$operators = $this->get('operator');
		//ED150414 may be array of fields, then values and operators are also arrays
		if(!is_array($search_fields))
			$search_fields = array($search_fields);
		if(!is_array($search_texts))
			$search_texts = array($search_texts);
		if(!is_array($operators))
			$operators = array($operators);
		for($i = 0; $i < count($search_fields) && $i < count($search_texts); $i++){
			$fieldName = $search_fields[$i];
			if(isset($listViewHeaders[$fieldName])
			&&  !($search_texts[$i] == '' && $operators[$i] == 'e')){
				//var_dump($fieldName, $search_texts[$i]);
				$listViewHeaders[$fieldName]->set('fieldvalue', $search_texts[$i]);
				$listViewHeaders[$fieldName]->set('filterOperator', $operators[$i]);
			}
		}
		return $listViewHeaders;
	}
	
	/**
	 * Function to get Relation query
	 * @return <String>
	 */
	public function getRelationQuery() {
		$relationModel = $this->getRelationModel();
		$recordModel = $this->getParentRecordModel();
		$query = $relationModel->getQuery($recordModel);
		// ED141018
		// ED141129 TODO utiliser $crmentity->uicolor_field
		
		if(strpos($query,'vtiger_attachmentsfolder'))
			$query = preg_replace('/(^|\sUNION\s+)SELECT\s/i', '$1SELECT vtiger_attachmentsfolder.uicolor, ', $query, 1);
		//var_dump(get_class($relationModel));
		//print_r("<pre>$query</pre>");
		return $query;
	}

	public static function getInstance($parentRecordModel, $relationModuleName, $label=false) {
		$parentModuleName = $parentRecordModel->getModule()->get('name');
		$className = Vtiger_Loader::getComponentClassName('Model', 'RelationListView', $parentModuleName);
		
		/*var_dump('$parentModuleName = ' . $parentModuleName);
		var_dump('$className = ' . $className);
		*/
		$instance = new $className();

		$parentModuleModel = $parentRecordModel->getModule();
		$relationModuleModel = Vtiger_Module_Model::getInstance($relationModuleName);

		$relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relationModuleModel, $label);
		$instance->setRelationModel($relationModel)->setParentRecordModel($parentRecordModel);
		$instance->set('relationModuleName', $relationModuleName);//ED150817
		return $instance;
	}

	/**
	 * Function to get Total number of record in this relation
	 * @return <Integer>
	 */
	public function getRelatedEntriesCount() {
		$db = PearDatabase::getInstance();
		$relationQuery = $this->getRelationQuery();
		$position = stripos($relationQuery, ' from ');
		if ($position) {
			$split = spliti(' from ', $relationQuery);
			$splitCount = count($split);
			$relationQuery = 'SELECT count(*) AS count ';
			for ($i=1; $i<$splitCount; $i++) {
				$relationQuery .= ' FROM ' .$split[$i];
			}
		}
		$result = $db->pquery($relationQuery, array());
		return $db->query_result($result, 0, 'count');
	}

	/**
	 * Function to update relation query
	 * @param <String> $relationQuery
	 * @return <String> $updatedQuery
	 */
	public function updateQueryWithWhereCondition($relationQuery) {
		$condition = '';

		$whereCondition = $this->get("whereCondition");
		$count = count($whereCondition);
		if ($count > 1) {
			$appendAndCondition = true;
		}

		$i = 1;
		foreach ($whereCondition as $fieldName => $fieldValue) {
			$condition .= " $fieldName = '$fieldValue' ";
			if ($appendAndCondition && ($i++ != $count)) {
				$condition .= " AND ";
			}
		}

		$pos = stripos($relationQuery, 'where');
		if ($pos) {
			$split = spliti('where', $relationQuery);
			$updatedQuery = $split[0] . ' WHERE ' . $split[1] . ' AND ' . $condition;
		} else {
			$updatedQuery = $relationQuery . ' WHERE ' . $condition;
		}
		return $updatedQuery;
	}

	/** ED150704
	 * Function to update relation query
	 * @param <String> $relationQuery
	 * @return <String> $updatedQuery
	 */
	public function updateQueryWithSearchCondition($relationQuery) {
		$condition = '';

		$searchKey = $this->get('search_key');
		$searchValue = $this->get('search_value');
		$operator = $this->get('operator');
		//var_dump($searchKey, $operator, $searchValue);
		$whereCondition = array();
		for($i = 0; $i < count($searchKey); $i++){
			$value = str_replace("'", "\'", $searchValue[$i]);
			switch($operator[$i]) {
				case 'e': $sqlOperator = "=";
					break;
				case 'n': $sqlOperator = "<>";
					break;
				case 's': $sqlOperator = "LIKE";
					$value = "$value%";
					break;
				case 'ew': $sqlOperator = "LIKE";
					$value = "%$value";
					break;
				case 'ct'://ED150619
				case 'ca'://ED150619
				case 'c': $sqlOperator = "LIKE";
					$value = "%$value%";
					break;
				case 'kt'://ED150619
				case 'ka'://ED150619
				case 'k': $sqlOperator = "NOT LIKE";
					$value = "%$value%";
					break;
				case 'l': $sqlOperator = "<";
					break;
				case 'g': $sqlOperator = ">";
					break;
				case 'm': $sqlOperator = "<=";
					break;
				case 'h': $sqlOperator = ">=";
					break;
				case 'a': $sqlOperator = ">";
					break;
				case 'b': $sqlOperator = "<";
					break;
				/*ED150307*/
				case 'vwi': $sqlOperator = " IN ";
					break;
				case 'vwx': $sqlOperator = " NOT IN ";
					break;
			}
			
			//TODO
			//cas particulier, conversion fieldname -> columnname
			switch($searchKey[$i]){
			 case 'accounttype':
				$searchKey[$i] = 'contacttype';
				break;
			}
			
			$whereCondition[] = $searchKey[$i] .' '. $sqlOperator . " '" . $value . "'";
		}
		
		$condition = implode(' AND ', $whereCondition);

		$pos = stripos($relationQuery, 'where');
		if ($pos) {
			$split = spliti('where', $relationQuery);
			$updatedQuery = $split[0] . ' WHERE
			' . $split[1] . ' AND ' . $condition;
		} else {
			$updatedQuery = $relationQuery . ' WHERE
			' . $condition;
		}
		
		
		
		//echo "<pre>$updatedQuery</pre>";
		
		return $updatedQuery;
	}

}
