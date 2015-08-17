<?php
/*+***********************************************************************************
 * ED150817
 *************************************************************************************/

class RSNSysControls_Detail_View extends Vtiger_Detail_View {
	protected $record = false;

	function __construct() {
		parent::__construct();
		$this->exposeMethod('showSysControlsResult');
	}

	/** ED150817
	 * Function returns syscontrol result records
	 * @param Vtiger_Request $request
	 * @return <type>
	 */
	function showSysControlsResult(Vtiger_Request $request) {
	      
		$moduleName = $request->getModule();
		$relatedModuleName = $request->get('relatedModule');
		$parentId = $request->get('record');
		$label = $request->get('tab_label');
		$requestedPage = $request->get('page');
		if(empty($requestedPage)) {
			$requestedPage = 1;
		}
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page',$requestedPage);
		if($request->get('limit')) /* ED140921 */
			$pagingModel->set('limit',$request->get('limit'));
		
		$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName, $label);

		$viewer = $this->getViewer($request);
		
		$orderBy = $request->get('orderby');
		$sortOrder = $request->get('sortorder');
		if($sortOrder == 'ASC') {
			$nextSortOrder = 'DESC';
			$sortImage = 'icon-chevron-down';
		} else {
			$nextSortOrder = 'ASC';
			$sortImage = 'icon-chevron-up';
		}
		if(!empty($orderBy)) {
			$relationListView->set('orderby', $orderBy);
			$relationListView->set('sortorder',$sortOrder);
		}
		
		//ED150701
		$searchKey = $request->get('search_key');
		$searchValue = $request->get('search_value');
		$operator = $request->get('operator');
			
		if(!empty($operator)) {
			$relationListView->set('operator', $operator);
			$viewer->assign('OPERATOR',is_array($operator) ? htmlspecialchars(json_encode($operator)) : $operator);
			$viewer->assign('ALPHABET_VALUE',is_array($searchValue) ? htmlspecialchars(json_encode($searchValue)) : $searchValue);
		}
		//ED150414 $searchValue == 0 is acceptable
		if(!empty($searchKey) && (!empty($searchValue) || ($searchValue == '0'))) {
			$relationListView->set('search_key', $searchKey);
			$relationListView->set('search_value', $searchValue);
		}
		
		$models = $relationListView->getEntries($pagingModel);
		
		$links = $relationListView->getLinks();
		$header = $relationListView->getHeaders();
		$noOfEntries = count($models);
		
		/*ED140907*/
		$unknown_field_returns_value = true; //$relatedModuleName == "Critere4D" || $relatedModuleName == "Contacts";

		$relatedModuleModel = $relationListView->getRelatedModuleModel();
		
		$viewer->assign('RELATED_RECORDS' , $models);
		$viewer->assign('PARENT_RECORD', $parentRecordModel);
		$viewer->assign('RELATED_LIST_LINKS', $links);
		$viewer->assign('RELATED_HEADERS', $header);
		$viewer->assign('RELATED_MODULE', $relatedModuleModel);
		$viewer->assign('RELATED_ENTIRES_COUNT', $noOfEntries);
		
		/*ED140926*/
		$viewer->assign('FROM_TAB_LABEL', $label);
		
		$viewer->assign('UNKNOWN_FIELD_RETURNS_VALUE', $unknown_field_returns_value); /*ED140907*/

		if (PerformancePrefs::getBoolean('LISTVIEW_COMPUTE_PAGE_COUNT', false)) {
			$totalCount = $relationListView->getRelatedEntriesCount();
			$pageLimit = $pagingModel->getPageLimit();
			$pageCount = ceil((int) $totalCount / (int) $pageLimit);

			if($pageCount == 0){
				$pageCount = 1;
			}
			$viewer->assign('PAGE_COUNT', $pageCount);
			$viewer->assign('TOTAL_ENTRIES', $totalCount);
			$viewer->assign('PERFORMANCE', true);
		}

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('PAGING', $pagingModel);

		$viewer->assign('ORDER_BY',$orderBy);
		$viewer->assign('SORT_ORDER',$sortOrder);
		$viewer->assign('NEXT_SORT_ORDER',$nextSortOrder);
		$viewer->assign('SORT_IMAGE',$sortImage);
		$viewer->assign('COLUMN_NAME',$orderBy);

		$viewer->assign('IS_EDITABLE', true);
		$viewer->assign('IS_DELETABLE', false);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('VIEW', $request->get('view'));

		/*ED140907*/
		$viewer->assign('WIDGET_INSIDE', $request->get('widget_inside'));
		
		$tpl = 'RelatedList.tpl';
		
		return $viewer->view($tpl, $moduleName, 'true');
	}
}
