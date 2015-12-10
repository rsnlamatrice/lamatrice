<?php
/*+**********************************************************************************
 * ED151009
 ************************************************************************************/

class Vtiger_DuplicatesList_View extends Vtiger_List_View {
	
	function preProcess(Vtiger_Request $request, $display=true) {
		parent::preProcess($request, false);

		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();
		
		$listViewModel = Vtiger_DuplicatesListView_Model::getInstance($moduleName);
		$linkParams = array('MODULE'=>$moduleName, 'ACTION'=>$request->get('view'));
		$viewer->assign('CUSTOM_VIEWS', CustomView_Record_Model::getAllByGroup($moduleName));
		
		//ED151109
		if(true){
			$customView = CustomView_Record_Model::getAllFilterByModule($moduleName);
			$this->viewName = $customView->getId();
			$request->set('viewname', $this->viewName);
		}
		else
			$this->viewName = $request->get('viewname');
		
		if(empty($this->viewName)){
			//If not view name exits then get it from custom view
			//This can return default view id or view id present in session
			$customView = new CustomView();
			$this->viewName = $customView->getViewId($moduleName);
		}

		$quickLinkModels = $listViewModel->getSideBarLinks($linkParams);
		$viewer->assign('QUICK_LINKS', $quickLinkModels);
		$this->initializeListViewContents($request, $viewer);
		$viewer->assign('VIEWID', $this->viewName);
		if($display) {
			$this->preProcessDisplay($request);
		}
	}

	function process (Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		
		$this->viewName = $request->get('viewname');

		$this->initializeListViewContents($request, $viewer);
		$viewer->assign('VIEW', $request->get('view'));
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
		
		$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		$viewer->assign('RECORD_MODEL', $recordModel);
		$viewer->view('DuplicatesListViewContents.tpl', $moduleName);
		
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
			'modules.Vtiger.resources.DuplicatesList',
			"modules.$moduleName.resources.DuplicatesList",
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	/*
	 * Function to initialize the required data in smarty to display the List View Contents
	 */
	public function initializeListViewContents(Vtiger_Request $request, Vtiger_Viewer $viewer) {
		$moduleName = $request->getModule();
		$cvId = $this->viewName;
		$pageNumber = $request->get('page');
		$orderBy = $request->get('orderby');
		$sortOrder = $request->get('sortorder');
		if($sortOrder == "ASC"){
			$nextSortOrder = "DESC";
			$sortImage = "icon-chevron-down";
		}else{
			$nextSortOrder = "ASC";
			$sortImage = "icon-chevron-up";
		}

		if(empty ($pageNumber)){
			$pageNumber = '1';
		}

		$listViewModel = Vtiger_DuplicatesListView_Model::getInstance($moduleName, $cvId);
		$linkParams = array('MODULE'=>$moduleName, 'ACTION'=>$request->get('view'), 'CVID'=>$cvId);
		$linkModels = $listViewModel->getListViewMassActions($linkParams);

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);

		//ED150622
		if(empty($orderBy) && $cvId) {
			$customView = CustomView_Record_Model::getInstanceById($cvId);
			list($orderBy, $sortOrder) = $customView->getOrderByFieldsInfos();
		}
		
		if(!empty($orderBy)) {
			$listViewModel->set('orderby', $orderBy);
			$listViewModel->set('sortorder', $sortOrder);
		}

		$searchKey = $request->get('search_key');
		$searchValue = $request->get('search_value');
		$searchInput = $request->get('search_input');
		$operator = $request->get('operator');
			
		if(!empty($operator)) {
			$listViewModel->set('operator', $operator);
			$viewer->assign('OPERATOR',is_array($operator) ? htmlspecialchars(json_encode($operator)) : $operator);
			$viewer->assign('ALPHABET_VALUE',is_array($searchValue) ? htmlspecialchars(json_encode($searchValue)) : $searchValue);
		}
		//ED150414 $searchValue == 0 is acceptable
		if(!empty($searchKey) && (!empty($searchValue) || ($searchValue === '0'))) {
			$listViewModel->set('search_key', $searchKey);
			$listViewModel->set('search_value', $searchValue);
			$listViewModel->set('search_input', $searchInput);
		}
		
		if(!$this->listViewHeaders){
			$this->listViewHeaders = $listViewModel->getListViewHeaders();
		}
		
		//ED150904
		$alphabetFields = $listViewModel->getAlphabetFields($this->listViewHeaders);
		
		if(!$this->listViewEntries){
			$this->listViewEntries = $listViewModel->getListViewEntries($pagingModel);
		}
		
		$noOfEntries = count($this->listViewEntries);
		//ED151210 on triche pour avoir la navigation car on a pas le nbre exacte d'entrée
		if($noOfEntries > 0)
			$pagingModel->set('limit', $noOfEntries);
		
		$viewer->assign('MODULE', $moduleName);

		if(!$this->listViewLinks){
			$this->listViewLinks = $listViewModel->getListViewLinks($linkParams);
		}
		$viewer->assign('LISTVIEW_LINKS', $this->listViewLinks);

		$viewer->assign('LISTVIEW_MASSACTIONS', $linkModels['LISTVIEWMASSACTION']);

		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('PAGE_NUMBER',$pageNumber);

		$viewer->assign('ORDER_BY',$orderBy);
		$viewer->assign('SORT_ORDER',$sortOrder);
		$viewer->assign('NEXT_SORT_ORDER',$nextSortOrder);
		$viewer->assign('SORT_IMAGE',$sortImage);
		$viewer->assign('COLUMN_NAME',$orderBy);

		$viewer->assign('LISTVIEW_ENTIRES_COUNT',$noOfEntries);
		$viewer->assign('LISTVIEW_HEADERS', $this->listViewHeaders);
		$viewer->assign('LISTVIEW_ENTRIES', $this->listViewEntries);
		/*ED150904*/
		$viewer->assign('ALPHABET_FIELDS', $alphabetFields);
		
		if (PerformancePrefs::getBoolean('LISTVIEW_COMPUTE_PAGE_COUNT', false)) {
			if(!$this->listViewCount){
				$this->listViewCount = $listViewModel->getListViewCount();
			}
			
			$totalCount = $this->listViewCount;
			$pageLimit = $pagingModel->getPageLimit();
			$pageCount = ceil((int) $totalCount / (int) $pageLimit);

			if($pageCount == 0){
				$pageCount = 1;
			}
			$viewer->assign('PAGE_COUNT', $pageCount);
			$viewer->assign('LISTVIEW_COUNT', $totalCount);
		}

		$viewer->assign('IS_MODULE_DUPLICATABLE', $listViewModel->getModule()->isPermitted('Duplicate'));
		$viewer->assign('IS_MODULE_EDITABLE', $listViewModel->getModule()->isPermitted('EditView'));
		$viewer->assign('IS_MODULE_DELETABLE', $listViewModel->getModule()->isPermitted('Delete'));
	}

	/**
	 * Function returns the number of records for the current filter
	 * @param Vtiger_Request $request
	 */
	function getRecordsCount(Vtiger_Request $request) {
		
		$moduleName = $request->getModule();
		$cvId = $request->get('viewname');
		$count = $this->getListViewCount($request);

		$result = array();
		$result['module'] = $moduleName;
		$result['viewname'] = $cvId;
		$result['count'] = $count;

		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}

	/**
	 * Function to get listView count
	 * @param Vtiger_Request $request
	 */
	function getListViewCount(Vtiger_Request $request, &$calculatedTotals = false){
		$moduleName = $request->getModule();
		$cvId = $request->get('viewname');
		if(empty($cvId)) {
			$cvId = '0';
		}

		$listViewModel = Vtiger_DuplicatesListView_Model::getInstance($moduleName, $cvId);

		$count = $listViewModel->getListViewCount($calculatedTotals);

		return $count;
	}



	/**
	 * Function to get the page count for list
	 * @return total number of pages
	 */
	function getPageCount(Vtiger_Request $request){	
		$listViewCount = $this->getListViewCount($request);	
		$pagingModel = new Vtiger_Paging_Model();
		$pageLimit = $pagingModel->getPageLimit();
		$pageCount = ceil((int) $listViewCount / (int) $pageLimit);

		if($pageCount == 0){
			$pageCount = 1;
		}
		$result = array();
		$result['page'] = $pageCount;
		$result['numberOfRecords'] = $listViewCount;
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}