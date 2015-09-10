<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Vtiger_FindDuplicates_View extends Vtiger_List_View {

	function preProcess(Vtiger_Request $request, $display = true) {
		$viewer = $this->getViewer ($request);
		$this->initializeListViewContents($request, $viewer);
		parent::preProcess($request, $display);
	}

	public function preProcessTplName(Vtiger_Request $request) {
		return 'FindDuplicatePreProcess.tpl';
	}

	function process (Vtiger_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$this->initializeListViewContents($request, $viewer);

		$viewer->assign('VIEW', $request->get('view'));
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('FindDuplicateContents.tpl', $moduleName);
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			'modules.Vtiger.resources.List',
			'modules.Vtiger.resources.FindDuplicates',
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	/*
	 * Function to initialize the required data in smarty to display the List View Contents
	 */
	public function initializeListViewContents(Vtiger_Request $request, Vtiger_Viewer $viewer) {
		$currentUser = vglobal('current_user');
		$viewer = $this->getViewer ($request);
		$module = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($module);

		$massActionLink = array(
			'linktype' => 'LISTVIEWBASIC',
			'linklabel' => 'LBL_DELETE',
			'linkurl' => 'Javascript:Vtiger_FindDuplicates_Js.massDeleteRecords("index.php?module='.$module.'&action=MassDelete");',
			'linkicon' => ''
		);
		$massActionLinks[] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		$viewer->assign('LISTVIEW_LINKS', $massActionLinks);
		$viewer->assign('MODULE_MODEL', $moduleModel);

		$pageNumber = $request->get('page');
		if(empty($pageNumber)){
			$pageNumber = '1';
		}
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		$pageLimit = $pagingModel->getPageLimit();

		$duplicateSearchFields = $request->get('fields');
		$dataModelInstance = Vtiger_FindDuplicate_Model::getInstance($module);
		$dataModelInstance->set('fields', $duplicateSearchFields);

		$ignoreEmpty = $request->get('ignoreEmpty');
		$ignoreEmptyValue = false;
		if($ignoreEmpty == 'on' || $ignoreEmpty == 'true' || $ignoreEmpty == '1') $ignoreEmptyValue = true;
		$dataModelInstance->set('ignoreEmpty', $ignoreEmptyValue);

		if(!$this->listViewEntries) {
			$dataModelInstance->set('source_query', $this->getFromQuery($request));
			$dataModelInstance->set('among_query', $this->getAmongQuery($request));
			
			$this->listViewEntries = $dataModelInstance->getListViewEntries($pagingModel);
		}

		if(!$this->listViewHeaders){
			$this->listViewHeaders = $dataModelInstance->getListViewHeaders();
		}
		if(!$this->rows) {
			$this->rows = $dataModelInstance->getRecordCount();
			$viewer->assign('TOTAL_COUNT', $this->rows);
		}

		$rowCount = 0;
		foreach($this->listViewEntries as $group) {
			foreach($group as $row) {
				$rowCount++;
			}
		}
		//for calculating the page range
		for($i=0; $i<$rowCount; $i++) $dummyListEntries[] = $i;
		$pagingModel->calculatePageRange($dummyListEntries);

		$viewer->assign('IGNORE_EMPTY', $ignoreEmpty);
		$viewer->assign('LISTVIEW_ENTIRES_COUNT', $rowCount);
		$viewer->assign('LISTVIEW_HEADERS', $this->listViewHeaders);
		$viewer->assign('LISTVIEW_ENTRIES', $this->listViewEntries);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('PAGE_NUMBER',$pageNumber);
		$viewer->assign('MODULE', $module);
		$viewer->assign('DUPLICATE_SEARCH_FIELDS', $duplicateSearchFields);

		$customViewModel = CustomView_Record_Model::getAllFilterByModule($module);
		$viewer->assign('VIEW_NAME', $customViewModel->getId());
	}

	/**
	 * Function returns the number of records for the current filter
	 * @param Vtiger_Request $request
	 */
	function getRecordsCount(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$duplicateSearchFields = $request->get('fields');
		$dataModelInstance = Vtiger_FindDuplicate_Model::getInstance($moduleName);
		$dataModelInstance->set('fields', $duplicateSearchFields);
		//ED150910
		$dataModelInstance->set('source_query', $this->getFromQuery($request));
		$dataModelInstance->set('among_query', $this->getAmongQuery($request));
		
		$count = $dataModelInstance->getRecordCount();

		$result = array();
		$result['module'] = $moduleName;
		$result['count'] = $count;

		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}

	/** ED150626
	 * Function that generates source records Query based on the mode
	 * @param Vtiger_Request $request
	 * @return <String> export query
	 */
	function getFromQuery(Vtiger_Request $request) {
		return $this->getRecordSelectionQuery($request, 'From');
	}
	
	/** ED150626
	 * Function that generates tested among records Query based on the mode
	 * @param Vtiger_Request $request
	 * @return <String> export query
	 */
	function getAmongQuery(Vtiger_Request $request) {
		return $this->getRecordSelectionQuery($request, 'Among');
	}
	
	/** ED150626
	 * Function that generates source or among records Query based on the mode
	 * @param Vtiger_Request $request
	 * @return <String> export query
	 */
	private function getRecordSelectionQuery(Vtiger_Request $request, $params_prefix) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$mode = $request->get($params_prefix === 'Among' ? 'among_ids' : 'source_ids');
		$cvId = $request->get('viewname');
		$moduleName = $request->get('module');
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$queryGenerator = new QueryGenerator($moduleName, $currentUser);
		$queryGenerator->initForCustomViewById($cvId);
		/*$fieldInstances = $moduleModel->getFields();
		
        $accessiblePresenceValue = array(0,2);
		foreach($fieldInstances as $field) {
            // Check added as querygenerator is not checking this for admin users
            $presence = $field->get('presence');
            if(in_array($presence, $accessiblePresenceValue)) {
                $fields[] = $field->getName();
            }
        }
		$queryGenerator->setFields($fields);*/
		$queryGenerator->setFields(array('id'));
		
		if($mode != $params_prefix.'SelectedRecords'){
			$searchKey = $request->get('search_key');
			$searchValue = $request->get('search_value');
			$operator = $request->get('operator');
			if(!empty($operator)) {
				$queryGenerator->addUserSearchConditions(array('search_field' => $searchKey, 'search_text' => $searchValue, 'operator' => $operator));
			}
		}
		
		$query = $queryGenerator->getQuery();

		//Inventory
		if(in_array($moduleName, getInventoryModules())){
			$query = $this->moduleInstance->getFindDuplicateFromQuery($this->focus, $query);
		}

		$this->accessibleFields = $queryGenerator->getFields();

		//source_ids=FromSelectedRecords&among_ids=AmongAllData
		switch($mode) {
			case $params_prefix.'AllData' :
				return $query;
				break;

			case $params_prefix.'CurrentPage' :
				$pagingModel = new Vtiger_Paging_Model();
				$limit = $pagingModel->getPageLimit();

				$currentPage = $request->get('page');
				if(empty($currentPage)) $currentPage = 1;

				$currentPageStart = ($currentPage - 1) * $limit;
				if ($currentPageStart < 0) $currentPageStart = 0;
				$query .= ' LIMIT '.$currentPageStart.','.$limit;

				return $query;

			case $params_prefix.'SelectedRecords' :
				$idList = $this->getRecordsListFromRequest($request);
				$baseTable = $moduleModel->get('basetable');
				$baseTableColumnId = $moduleModel->get('basetableid');
				if(!empty($idList)) {
					if(!empty($baseTable) && !empty($baseTableColumnId)) {
						$idList = implode(',' , $idList);
						$query .= ' AND '.$baseTable.'.'.$baseTableColumnId.' IN ('.$idList.')';
					}
				} else {
					$query .= ' AND '.$baseTable.'.'.$baseTableColumnId.' NOT IN ('.implode(',',$request->get('excluded_ids')).')';
				}
				return $query;


			default :
				return $query;
		}
	}
	
	

	/** ED150910 copied from views/MassActionAjax.php
	 * Function returns the record Ids selected in the current filter
	 * @param Vtiger_Request $request
	 * @return integer
	 */
	private function getRecordsListFromRequest(Vtiger_Request $request) {
		$cvId = $request->get('viewname');
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');

		if(!empty($selectedIds) && $selectedIds != 'all') {
			if(!empty($selectedIds) && count($selectedIds) > 0) {
				return $selectedIds;
			}
		}
		
		$customViewModel = CustomView_Record_Model::getInstanceById($cvId);
		if($customViewModel) {
			$searchKey = $request->get('search_key');
			$searchValue = $request->get('search_value');
			$operator = $request->get('operator');
			if(!empty($operator)) {
				$customViewModel->set('operator', $operator);
				$customViewModel->set('search_key', $searchKey);
				$customViewModel->set('search_value', $searchValue);
			}
			return $customViewModel->getRecordIds($excludedIds,$request->getModule());
		}
	}
}