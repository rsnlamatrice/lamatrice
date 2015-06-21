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
class Vtiger_ListView_Model extends Vtiger_Base_Model {

	/* ED150308
	 * 
	 */
	public static function getInstanceWithFilters($filters){
		
	}

	/**
	 * Function to get the Module Model
	 * @return Vtiger_Module_Model instance
	 */
	public function getModule() {
		return $this->get('module');
	}

	/**
	 * Function to get the Quick Links for the List view of the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Vtiger_Link_Model instances
	 */
	public function getSideBarLinks($linkParams) {
		$linkTypes = array('SIDEBARLINK', 'SIDEBARWIDGET');
		$moduleLinks = $this->getModule()->getSideBarLinks($linkParams);

		$listLinkTypes = array('LISTVIEWSIDEBARLINK', 'LISTVIEWSIDEBARWIDGET');
		$listLinks = Vtiger_Link_Model::getAllByType($this->getModule()->getId(), $listLinkTypes);

		if($listLinks['LISTVIEWSIDEBARLINK']) {
			foreach($listLinks['LISTVIEWSIDEBARLINK'] as $link) {
				$moduleLinks['SIDEBARLINK'][] = $link;
			}
		}

		if($listLinks['LISTVIEWSIDEBARWIDGET']) {
			foreach($listLinks['LISTVIEWSIDEBARWIDGET'] as $link) {
				$moduleLinks['SIDEBARWIDGET'][] = $link;
			}
		}

		return $moduleLinks;
	}

	/**
	 * Function to get the list of listview links for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associate array of Link Type to List of Vtiger_Link_Model instances
	 */
	public function getListViewLinks($linkParams) {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$moduleModel = $this->getModule();

		$linkTypes = array('LISTVIEWBASIC', 'LISTVIEW', 'LISTVIEWSETTING');
		$links = Vtiger_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);

		$basicLinks = $this->getBasicLinks();

		foreach($basicLinks as $basicLink) {
			$links['LISTVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicLink);
		}

		$advancedLinks = $this->getAdvancedLinks();

		foreach($advancedLinks as $advancedLink) {
			$links['LISTVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($advancedLink);
		}

		if($currentUserModel->isAdminUser()) {

			$settingsLinks = $this->getSettingLinks();
			foreach($settingsLinks as $settingsLink) {
				$links['LISTVIEWSETTING'][] = Vtiger_Link_Model::getInstanceFromValues($settingsLink);
			}
		}

		return $links;
	}

	/**
	 * Function to get the list of Mass actions for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associative array of Link type to List of  Vtiger_Link_Model instances for Mass Actions
	 */
	public function getListViewMassActions($linkParams) {
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$moduleModel = $this->getModule();

		$linkTypes = array('LISTVIEWMASSACTION');
		$links = Vtiger_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);


		$massActionLinks = array();
		if($currentUserModel->hasModuleActionPermission($moduleModel->getId(), 'EditView')) {
			$massActionLinks[] = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_EDIT',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerMassEdit("index.php?module='.$moduleModel->get('name').'&view=MassActionAjax&mode=showMassEditForm");',
				'linkicon' => ''
			);
		}
		if($currentUserModel->hasModuleActionPermission($moduleModel->getId(), 'Delete')) {
			$massActionLinks[] = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_DELETE',
				'linkurl' => 'javascript:Vtiger_List_Js.massDeleteRecords("index.php?module='.$moduleModel->get('name').'&action=MassDelete");',
				'linkicon' => ''
			);
		}

		if($moduleModel->isCommentEnabled()) {
			$massActionLinks[] = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_ADD_COMMENT',
				'linkurl' => 'index.php?module='.$moduleModel->get('name').'&view=MassActionAjax&mode=showAddCommentForm',
				'linkicon' => ''
			);
		}

		foreach($massActionLinks as $massActionLink) {
			$links['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}

		return $links;
	}

	/**
	 * Function to get the list view header
	 * @return <Array> - List of Vtiger_Field_Model instances
	 */
	public function getListViewHeaders() {
		$listViewController = $this->get('listview_controller');
		$module = $this->getModule();
		$headerFieldModels = array();
		$headerFields = $listViewController->getListViewHeaderFields();
		foreach($headerFields as $fieldName => $webserviceField) {
			if($webserviceField && !in_array($webserviceField->getPresence(), array(0,2))) continue;
			$headerFieldModels[$fieldName] = Vtiger_Field_Model::getInstance($fieldName,$module);
		}
		return $this->initListViewHeadersFilters($headerFieldModels);
	}

	/** ED150414
	 * Function to init fields as list view header filters
	 * @return <Array> - List of Vtiger_Field_Model instances
	 */
	private function initListViewHeadersFilters($listViewHeaders) {
		
		$search_fields = $this->get('search_key');
		$search_texts = $this->get('search_value');
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
				$listViewHeaders[$fieldName]->set('fieldvalue', $search_texts[$i]);
				$listViewHeaders[$fieldName]->set('filterOperator', $operators[$i]);
			}
		}
		return $listViewHeaders;
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

		$searchKey = $this->get('search_key');
		$searchValue = $this->get('search_value');
		$operator = $this->get('operator');
		if(!empty($searchKey)) {
			$queryGenerator->addUserSearchConditions(array('search_field' => $searchKey, 'search_text' => $searchValue, 'operator' => $operator));
		}
		$orderBy = $this->getForSql('orderby');
		$sortOrder = $this->getForSql('sortorder');

		//List view will be displayed on recently created/modified records
		if(empty($orderBy) && empty($sortOrder)){
			switch($moduleName){
			case "Users":
				break;
			case "RSNMediaRelations":
				$orderBy = 'daterelation';
				$sortOrder = 'DESC';
				break;
			default:
				$orderBy = 'modifiedtime';
				$sortOrder = 'DESC';
				break;
			}
		}

		if(!empty($orderBy)){
		    $columnFieldMapping = $moduleModel->getColumnFieldMapping();
		    $orderByFieldName = $columnFieldMapping[$orderBy];
		    $orderByFieldModel = $moduleModel->getField($orderByFieldName);
		    if($orderByFieldModel &&
				(	$orderByFieldModel->getFieldDataType() == Vtiger_Field_Model::REFERENCE_TYPE
				||	preg_match('/cf$/', $orderByFieldModel->table)//ED150622 TODO more than *cf
				)
			){
				//IF it is reference add it in the where fields so that from clause will be having join of the table
				$queryGenerator = $this->get('query_generator');
				$queryGenerator->addWhereField($orderByFieldName);
				//$queryGenerator->whereFields[] = $orderByFieldName;
		    }
		}
		$listQuery = $this->getQuery();
	//echo("<p style=\"margin-top:6em\"> ICICICI getListViewEntries $listQuery </p>");
	
		$sourceModule = $this->get('src_module');
		if(!empty($sourceModule)) {
			if(method_exists($moduleModel, 'getQueryByModuleField')) {
				$overrideQuery = $moduleModel->getQueryByModuleField($sourceModule, $this->get('src_field'), $this->get('src_record'), $listQuery);
				if(!empty($overrideQuery)) {
					$listQuery = $overrideQuery;
				}
			}
		}


	//var_dump("22222222222222222 getListViewEntries $listQuery ");
	
		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();

		if(!empty($orderBy)) {
		    if($orderByFieldModel && $orderByFieldModel->isReferenceField()){
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
//var_dump($listViewEntries);
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
		foreach($listViewEntries as $recordId => $record) {
			$rawData = $db->query_result_rowdata($listResult, $index++);
			$record['id'] = $recordId;
			$listViewRecordModels[$recordId] = $moduleModel->getRecordFromArray($record, $rawData);
		}
		//var_dump($listViewRecordModels);
		return $listViewRecordModels;
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
		//ED150507 : ' from ' is not correct if FROM is preceded with \t
		//$position = stripos($listQuery, ' from ');
		//if ($position) {
		//	$split = spliti(' from ', $listQuery);
		//	$splitCount = count($split);
		//	$listQuery = 'SELECT count(*) AS count ';
		//	for ($i=1; $i<$splitCount; $i++) {
		//		$listQuery = $listQuery. ' FROM ' .$split[$i];
		//	}
		//}

		if($this->getModule()->get('name') == 'Calendar'){
			$listQuery .= ' AND activitytype <> "Emails"';
		}

		//ED150507 : cou
		$listQuery = 'SELECT count(*) AS count FROM (' . $listQuery . ') q';
		
		$listResult = $db->pquery($listQuery, array());
		return $db->query_result($listResult, 0, 'count');
	}

	function getQuery() {
		$queryGenerator = $this->get('query_generator');
		$listQuery = $queryGenerator->getQuery();
		return $listQuery;
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
		$db = PearDatabase::getInstance();
		$currentUser = vglobal('current_user');

		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'ListView', $moduleName);
		$instance = new $modelClassName();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$queryGenerator = new QueryGenerator($moduleModel->get('name'), $currentUser);
		$customView = new CustomView();
		
		if($moreFilters)
			$queryGenerator->setAdvFilterListMore($moreFilters);
		
		if (!empty($viewId) && $viewId != "0") {
			$queryGenerator->initForCustomViewById($viewId);

			//Used to set the viewid into the session which will be used to load the same filter when you refresh the page
			$viewId = $customView->getViewId($moduleName);
		} else {
			$viewId = $customView->getViewId($moduleName);	
			if(!empty($viewId) && $viewId != 0) {
				$queryGenerator->initForDefaultCustomView();
			} else {
				$entityInstance = CRMEntity::getInstance($moduleName);
				$listFields = $entityInstance->list_fields_name;
				//debug_var_dump($listFields);
				$listFields[] = 'id';
				$queryGenerator->setFields($listFields);
			}
		}
		$controller = new ListViewController($db, $currentUser, $queryGenerator);

		return $instance->set('module', $moduleModel)->set('query_generator', $queryGenerator)->set('listview_controller', $controller);
	}

    /**
	 * Static Function to get the Instance of Vtiger ListView model for a given module and custom view
	 * @param <String> $value - Module Name
	 * @param <Number> $viewId - Custom View Id
	 * @return Vtiger_ListView_Model instance
	 */
	public static function getInstanceForPopup($value) {
		$db = PearDatabase::getInstance();
		$currentUser = vglobal('current_user');

		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'ListView', $value);
		$instance = new $modelClassName();
		$moduleModel = Vtiger_Module_Model::getInstance($value);

		$queryGenerator = new QueryGenerator($moduleModel->get('name'), $currentUser);

		$listFields = $moduleModel->getPopupFields();
		//var_dump('$listFields');
		//var_dump(get_class($moduleModel));
		//var_dump($listFields);
		$listFields[] = 'id';
		$queryGenerator->setFields($listFields);
		//var_dump('$queryGenerator');
		//var_dump(get_class($queryGenerator));
		//var_dump($queryGenerator->getFields());

		$controller = new ListViewController($db, $currentUser, $queryGenerator);

		return $instance->set('module', $moduleModel)->set('query_generator', $queryGenerator)->set('listview_controller', $controller);
	}

	/*
	 * Function to give advance links of a module
	 *	@RETURN array of advanced links
	 */
	public function getAdvancedLinks(){
		$moduleModel = $this->getModule();
		$createPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'EditView');
		$advancedLinks = array();
		$importPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'Import');
		if($importPermission && $createPermission) {
			$advancedLinks[] = array(
							'linktype' => 'LISTVIEW',
							'linklabel' => 'LBL_IMPORT',
							'linkurl' => $moduleModel->getImportUrl(),
							'linkicon' => ''
			);

			// For RSN Import Module
			$advancedLinks[] = array(
							'linktype' => 'LISTVIEW',
							'linklabel' => 'LBL_RSN_IMPORT',
							'linkurl' => $moduleModel->getRSNImportSourcesUrl(),
							'linkicon' => ''
			);
		}

		$exportPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'Export');
		if($exportPermission) {
			$advancedLinks[] = array(
					'linktype' => 'LISTVIEW',
					'linklabel' => 'LBL_EXPORT',
					'linkurl' => 'javascript:Vtiger_List_Js.triggerExportAction("'.$this->getModule()->getExportUrl().'")',
					'linkicon' => ''
				);
		}

		$duplicatePermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'DuplicatesHandling');
		if($duplicatePermission) {
			$advancedLinks[] = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_FIND_DUPLICATES',
				'linkurl' => 'Javascript:Vtiger_List_Js.showDuplicateSearchForm("index.php?module='.$moduleModel->getName().
								'&view=MassActionAjax&mode=showDuplicatesSearchForm")',
				'linkicon' => ''
			);
		}

		return $advancedLinks;
	}

	/*
	 * Function to get Setting links
	 * @return array of setting links
	 */
	public function getSettingLinks() {
		return $this->getModule()->getSettingLinks();
	}

	/*
	 * Function to get Basic links
	 * @return array of Basic links
	 */
	public function getBasicLinks(){
		$basicLinks = array();
		$moduleModel = $this->getModule();
		$createPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'EditView');
		if($createPermission) {
			$basicLinks[] = array(
					'linktype' => 'LISTVIEWBASIC',
					'linklabel' => 'LBL_ADD_RECORD',
					'linkurl' => $moduleModel->getCreateRecordUrl(),
					'linkicon' => ''
			);
		}
		return $basicLinks;
	}

	public function extendPopupFields($fieldsList) {
		$moduleModel = $this->get('module');
		$queryGenerator = $this->get('query_generator');
		$listFields = $moduleModel->getPopupFields();
		$listFields[] = 'id';
		$listFields = array_merge($listFields, $fieldsList);
		$queryGenerator->setFields($listFields);
		$this->get('query_generator', $queryGenerator);
	}
}
