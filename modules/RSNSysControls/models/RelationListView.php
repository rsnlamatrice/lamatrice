<?php
/*+***********************************************************************************
 * ED150817
 *************************************************************************************/

class RSNSysControls_RelationListView_Model extends Vtiger_RelationListView_Model {
	
	public function getEntries($pagingModel) {
		$db = PearDatabase::getInstance();
		//echo __FILE__; $db->setDebug(true);
		
		$parentModule = $this->getParentRecordModel()->getModule();
		$relationModel = $this->getRelationModel();
		if($relationModel)
			return parent::getEntries($pagingModel);
		
		//Relation model inconnu === spécificité de ce module
		
		$relationModule = $this->getRelatedModuleModel();
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
		//echo "<pre>".__FILE__." : $limitQuery</pre>";
		//echo_callstack();
		$result = $db->pquery($limitQuery, array());
		//ED150704
		if(!$result){
			echo "<code>Désolé, la formulation de la recherche provoque une erreur.</code>";
			$db->echoError();
			echo "<pre>".__FILE__." : $limitQuery</pre>";
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

	/**
	 * Function to get Relation query
	 * @return <String>
	 */
	public function getRelationQuery() {
		$recordModel = $this->getParentRecordModel();
		$relationModel = $this->getRelationModel();
		if($relationModel)
			return parent::getRelationQuery();
		
		$parentModuleModel = $recordModel->getModule();
		$relatedModuleModel = $this->getRelatedModuleModel();
		$relatedModuleName = $relatedModuleModel->getName();
		$ctrlQuery = $recordModel->getQueryFieldValue();
		
		$nonAdminQuery = $parentModuleModel->getNonAdminAccessControlQueryForRelation($relatedModuleName);
		
		//modify query if any module has summary fields, those fields we are displayed in related list of that module
		$relatedListFields = $relatedModuleModel->getConfigureRelatedListFields();
		if(count($relatedListFields) === 0) {
			die("Le module $relatedModuleName ne fournit pas les champs via getConfigureRelatedListFields().");
		}
		$relatedListFields[] = 'id';
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$queryGenerator = new QueryGenerator($relatedModuleName, $currentUser);
		$queryGenerator->setFields($relatedListFields);
		$query = $queryGenerator->getQuery();
		
		$query = 'SELECT vtiger_crmentity.crmid, ' . substr($query, stripos('SELECT ') + 6);
		
		$query .= ' AND vtiger_crmentity.crmid IN (SELECT crmid FROM (
			/*SysControl*/'.$ctrlQuery.'/*/SysControl*/
		) _syscontrolquery)';
		
		if ($nonAdminQuery) {
			$query = appendFromClauseToQuery($query, $nonAdminQuery);
		}
		
		//print_r("<pre>$query</pre>");
		return $query;
	}
}