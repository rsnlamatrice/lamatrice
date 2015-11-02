<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Products_ListView_Model extends Vtiger_ListView_Model {
/*
	 * Function to give advance links of a module
	 *	@RETURN array of advanced links
	 */
	public function getAdvancedLinks(){
		$advancedLinks = parent::getAdvancedLinks();
		
		//ED150928 Recalcule des quantités en commande pour tous les produits
		$moduleModel = $this->getModule();
		$createPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'EditView');
		if($createPermission) {
			//Quantité en demande
			$advancedLink = array(
				'linktype' => 'LISTVIEW',
				'linklabel' => '---',
				'linkurl' => '',
				'linkicon' => ''
			);
			$advancedLinks[] = $advancedLink;
		
			$advancedLink = array(
				'linktype' => 'LISTVIEW',
				'linklabel' => 'Recalcul du dépôt-vente',
				'linkurl' => $moduleModel->getRefreshQtyInDemandUrl(),
				'linkicon' => ''
			);
			$advancedLinks[] = $advancedLink;
		
		}

		return $advancedLinks;
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
		
		$this->setListViewSearchConditions($pagingModel);
	
		$listViewContoller = $this->get('listview_controller');

		
		$orderBy = $this->getForSql('orderby');
		$sortOrder = $this->getForSql('sortorder');

		//List view will be displayed on recently created/modified records
		if(empty($orderBy) && empty($sortOrder) && $moduleName != "Users"){
			$orderBy = 'modifiedtime';
			$sortOrder = 'DESC';
		}

		if(!empty($orderBy)){
		    $columnFieldMapping = $moduleModel->getColumnFieldMapping();
		    $orderByFieldName = $columnFieldMapping[$orderBy];
		    $orderByFieldModel = $moduleModel->getField($orderByFieldName);
		    if($orderByFieldModel && $orderByFieldModel->getFieldDataType() == Vtiger_Field_Model::REFERENCE_TYPE){
			//IF it is reference add it in the where fields so that from clause will be having join of the table
			$queryGenerator = $this->get('query_generator');
			$queryGenerator->addWhereField($orderByFieldName);
		    }
		}

		$listQuery = $this->getQuery();

		$listQuery = $this->replacePriceWithTaxQuery($listQuery, $moduleName);

		if($this->get('subProductsPopup')){
			$listQuery = $this->addSubProductsQuery($listQuery);
		}

		$sourceModule = $this->get('src_module');
		$sourceField = $this->get('src_field');
		if(!empty($sourceModule)) {
			if(method_exists($moduleModel, 'getQueryByModuleField')) {
				$overrideQuery = $moduleModel->getQueryByModuleField($sourceModule, $sourceField, $this->get('src_record'), $listQuery);
				if(!empty($overrideQuery)) {
					$listQuery = $overrideQuery;
				}
			}
		}

		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();

		if(!empty($orderBy)) {
            if($orderByFieldModel && $orderByFieldModel->getFieldDataType() == Vtiger_Field_Model::REFERENCE_TYPE){
                $referenceModules = $orderByFieldModel->getReferenceList();
                $referenceNameFieldOrderBy = array();
                foreach($referenceModules as $referenceModuleName) {
                    $referenceModuleModel = Vtiger_Module_Model::getInstance($referenceModuleName);
                    $referenceNameFields = $referenceModuleModel->getNameFields();

                    $columnList = array();
                    foreach($referenceNameFields as $nameField) {
                        $fieldModel = $referenceModuleModel->getField($nameField);
                        $columnList[] = $fieldModel->get('table').$orderByFieldModel->getName().'.'.$fieldModel->get('column');
                    }
                    if(count($columnList) > 1) {
                        $referenceNameFieldOrderBy[] = getSqlForNameInDisplayFormat(array('first_name'=>$columnList[0],'last_name'=>$columnList[1]),'Users').' '.$sortOrder;
                    } else {
                        $referenceNameFieldOrderBy[] = implode('', $columnList).' '.$sortOrder ;
                    }
                }
                $listQuery .= ' ORDER BY '. implode(',',$referenceNameFieldOrderBy);
            }else{
                $listQuery .= ' ORDER BY '. $orderBy . ' ' .$sortOrder;
            }
		}

		$viewid = ListViewSession::getCurrentView($moduleName);
		ListViewSession::setSessionQuery($moduleName, $listQuery, $viewid);

		//For Products popup in Price Book Related list
		if($sourceModule !== 'PriceBooks' && $sourceField !== 'priceBookRelatedList') {
			$listQuery .= " LIMIT $startIndex,".($pageLimit+1);
		}
		//echo "<pre style='margin-top:6em;'>$listQuery</pre>";

		$listResult = $db->pquery($listQuery, array());

		$listViewRecordModels = array();
		$listViewEntries =  $listViewContoller->getListViewRecords($moduleFocus,$moduleName, $listResult);
		$pagingModel->calculatePageRange($listViewEntries);

		if($db->num_rows($listResult) > $pageLimit && $sourceModule !== 'PriceBooks' && $sourceField !== 'priceBookRelatedList'){
			array_pop($listViewEntries);
			$pagingModel->set('nextPageExists', true);
		}else{
			$pagingModel->set('nextPageExists', false);
		}

		$index = 0;
		foreach($listViewEntries as $recordId => $record) {
			$rawData = $db->query_result_rowdata($listResult, $index++);
			$record['id'] = $recordId;
			$listViewRecordModels[$recordId] = $moduleModel->getRecordFromArray($record, $rawData);
		}
		return $listViewRecordModels;
	}
	
	/** 
	 * Function to set the list view search conditions.
	 * @param Vtiger_Paging_Model $pagingModel
	 */
	protected function setListViewSearchConditions($pagingModel = false) {
		$queryGenerator = $this->get('query_generator');
		
		$searchKey = $this->get('search_key');
		$searchValue = $this->get('search_value');
		$operator = $this->get('operator');
		if($searchKey === 'productname'
		|| $searchKey === 'servicename'){
			if(!$operator)
				$operator = 's';
			//tableau de tableau pour définir le OR
			$searchKey = 	array(array($searchKey, '', 'productcode'));
			$searchValue = 	array(array($searchValue, '', $searchValue));
			$operator = 	array(array($operator, 'OR', 's'));
		}
		if(!empty($searchKey)) {
			$this->set('search_key', $searchKey);
			$this->set('search_value', $searchValue);
			$this->set('operator', $operator);
		}
		
		return parent::setListViewSearchConditions($pagingModel);
	}

	public function addSubProductsQuery($listQuery){
		$splitQuery = split('WHERE', $listQuery);
		$query = " LEFT JOIN vtiger_seproductsrel ON vtiger_seproductsrel.crmid = vtiger_products.productid AND vtiger_seproductsrel.setype='Products'";
		$splitQuery[0] .= $query;
		$productId = $this->get('productId');
		$query1 = " AND vtiger_seproductsrel.productid = $productId";
		$splitQuery[1] .= $query1;
		$listQuery = $splitQuery[0]. ' WHERE ' . $splitQuery[1];
		return $listQuery;
	}

	public function getSubProducts($subProductId){
		$flag = false;
		if(!empty($subProductId)){
			$db = PearDatabase::getInstance();
			$result = $db->pquery('SELECT crmid FROM vtiger_seproductsrel WHERE productid = ?', array($subProductId));
			if($db->num_rows($result) > 0){
				$flag = true;
			}
		}
		return $flag;
	}

	/* ED150430
	 * add relation with tax to show TTC price
	 */
	public function replacePriceWithTaxQuery($listQuery, $moduleName){
		$tableName = $moduleName == 'Services' ? 'vtiger_service' : 'vtiger_products';
		$query = ' LEFT JOIN vtiger_producttaxrel ON vtiger_crmentity.crmid = vtiger_producttaxrel.productid';
		$listQuery = preg_replace('/\sWHERE\s/i', $query . ' WHERE ', $listQuery, 1);
		
		$query = $tableName . '.unit_price * (1 + IFNULL(vtiger_producttaxrel.taxpercentage, 0)/100)';
		//occurence du SELECT
		$listQuery = preg_replace('/' . $tableName . '\.unit_price/', $query . ' as unit_price', $listQuery, 1);
		//occurences suivantes (WHERE ...)
		$listQuery = preg_replace('/(^.*' . $tableName . '\.unit_price.*)' . $tableName . '\.unit_price/', '$1'. $query, $listQuery);
		
		return $listQuery;
	}

	/**
	 * Function to get the list view entries
	 * @param Vtiger_Paging_Model $pagingModel
	 * @return <Array> - Associative array of record id mapped to Vtiger_Record_Model instance.
	 */
	public function getListViewCount() {
		$db = PearDatabase::getInstance();

		$queryGenerator = $this->get('query_generator');

        $searchKey = $this->get('search_key');
		$searchValue = $this->get('search_value');
		$operator = $this->get('operator');
		if(!empty($searchKey)) {
			$queryGenerator->addUserSearchConditions(array('search_field' => $searchKey, 'search_text' => $searchValue, 'operator' => $operator));
		}

		$listQuery = $this->getQuery();

		if($this->get('subProductsPopup')){
			$listQuery = $this->addSubProductsQuery($listQuery);
		}

		$sourceModule = $this->get('src_module');
		if(!empty($sourceModule)) {
			$moduleModel = $this->getModule();
			if(method_exists($moduleModel, 'getQueryByModuleField')) {
				$overrideQuery = $moduleModel->getQueryByModuleField($sourceModule, $this->get('src_field'), $this->get('src_record'), $listQuery);
				if(!empty($overrideQuery)) {
					$listQuery = $overrideQuery;
				}
			}
		}
		$position = stripos($listQuery, ' from ');
		if ($position) {
			$split = spliti(' from ', $listQuery);
			$splitCount = count($split);
			$listQuery = 'SELECT count(*) AS count ';
			for ($i=1; $i<$splitCount; $i++) {
				$listQuery = $listQuery. ' FROM ' .$split[$i];
			}
		}

		if($this->getModule()->get('name') == 'Calendar'){
			$listQuery .= ' AND activitytype <> "Emails"';
		}

		$listResult = $db->pquery($listQuery, array());
		return $db->query_result($listResult, 0, 'count');
	}

}
