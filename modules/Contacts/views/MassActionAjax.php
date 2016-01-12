<?php
/*+***********************************************************************************
 * ED150628
 *************************************************************************************/

class Contacts_MassActionAjax_View extends Accounts_MassActionAjax_View {
	function __construct() {
		parent::__construct();
		$this->exposeMethod('assignRelatedEntities');
		$this->exposeMethod('unassignRelatedEntities');
		$this->exposeMethod('showRecordInfosForCopy');
	}
	
	function assignRelatedEntities(Vtiger_Request $request){
		//var_dump($request);
		
		$module = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($module);
		$relatedModuleName = $request->get('relatedmodule');
		$relatedRecords = Vtiger_Module_Model::getInstance($module);
		
		$cvId = $request->get('viewname');
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');
		//compte les contacts
		if(is_array($selectedIds))
			$idsCounter = count($selectedIds) - count($excludedIds);
		else if($selectedIds == 'all'){
			$sourceListView = new Vtiger_List_View();
			$idsCounter = $sourceListView->getListViewCount($request);
			$idsCounter -= count($excludedIds);
		}
		//liste de critères
		$listViewModel = Vtiger_ListView_Model::getInstance($relatedModuleName, 0);
		$listViewModel->set('operator', 'IN');
		$listViewModel->set('search_key', 'id');
		$listViewModel->set('search_value', $request->get('related_ids'));
			
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', 1);
		$listViewEntries = $listViewModel->getListViewEntries($pagingModel);
		
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE',$module);
		$viewer->assign('RELATED_MODULE', $relatedModuleName);
		$viewer->assign('RELATED_ENTRIES', $listViewEntries);
		
		$viewer->assign('SELECTED_IDS', $selectedIds);
		$viewer->assign('EXCLUDED_IDS', $excludedIds);
		$viewer->assign('CVID', $cvId);
		
		$searchKey = $request->get('search_key');
		$searchValue = $request->get('search_value');
		$searchInput = $request->get('search_input');
		$operator = $request->get('operator');
		if(!empty($operator)) {
			$viewer->assign('OPERATOR',is_array($operator) ? htmlspecialchars(json_encode($operator)) : $operator);
			$viewer->assign('ALPHABET_VALUE',is_array($searchValue) ? htmlspecialchars(json_encode($searchValue)) : $searchValue);
			$viewer->assign('SEARCH_KEY',is_array($searchKey) ? htmlspecialchars(json_encode($searchKey)) : $searchKey);
			$viewer->assign('SEARCH_INPUT',is_array($searchInput) ? htmlspecialchars(json_encode($searchInput)) : $searchInput);
		}
		
		$viewer->assign('ASSIGNABLE_COUNTER', $idsCounter);
		
		$viewer->assign('CURRENT_DATE', DateTimeField::convertToUserFormat(date('Y-m-d')));
		
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('AssignRelatedEntities.tpl', $module);
	}
	
	/**
	 * Suppression en masse des relations
	 */
	function unassignRelatedEntities(Vtiger_Request $request){
		$module = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($module);
		$relatedModuleName = $request->get('relatedmodule');
		$relatedRecords = Vtiger_Module_Model::getInstance($module);
		
		$cvId = $request->get('viewname');
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');
		//compte les contacts
		if(is_array($selectedIds))
			$idsCounter = count($selectedIds) - count($excludedIds);
		else if($selectedIds == 'all'){
			$sourceListView = new Vtiger_List_View();
			$idsCounter = $sourceListView->getListViewCount($request);
			$idsCounter -= count($excludedIds);
		}
		//liste de critères
		$listViewModel = Vtiger_ListView_Model::getInstance($relatedModuleName, 0);
		$listViewModel->set('operator', 'IN');
		$listViewModel->set('search_key', 'id');
		$listViewModel->set('search_value', $request->get('related_ids'));
			
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', 1);
		$listViewEntries = $listViewModel->getListViewEntries($pagingModel);
		
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE',$module);
		$viewer->assign('RELATED_MODULE', $relatedModuleName);
		$viewer->assign('RELATED_ENTRIES', $listViewEntries);
		
		$viewer->assign('SELECTED_IDS', $selectedIds);
		$viewer->assign('EXCLUDED_IDS', $excludedIds);
		$viewer->assign('CVID', $cvId);
		
		$searchKey = $request->get('search_key');
		$searchValue = $request->get('search_value');
		$searchInput = $request->get('search_input');
		$operator = $request->get('operator');
		if(!empty($operator)) {
			$viewer->assign('OPERATOR',is_array($operator) ? htmlspecialchars(json_encode($operator)) : $operator);
			$viewer->assign('ALPHABET_VALUE',is_array($searchValue) ? htmlspecialchars(json_encode($searchValue)) : $searchValue);
			$viewer->assign('SEARCH_KEY',is_array($searchKey) ? htmlspecialchars(json_encode($searchKey)) : $searchKey);
			$viewer->assign('SEARCH_INPUT',is_array($searchInput) ? htmlspecialchars(json_encode($searchInput)) : $searchInput);
		}
		
		$viewer->assign('UNASSIGNABLE_COUNTER', $idsCounter);
		
		$viewer->assign('CURRENT_DATE', DateTimeField::convertToUserFormat(date('Y-m-d')));
		
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('UnassignRelatedEntities.tpl', $module);
	}


	/**
	 * Function returns the email list view
	 * @param Vtiger_Request $request
	 *
	 * ED150227 copy from showEmailList
	 */
	function showRecordInfosForCopy(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$records = $this->getRecordsListFromRequest( $request);
		$recordModels = array();
		foreach($records as $recordId){
			$recordModels[$recordId] = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			if(count($recordModels) >= 7)
				break;
		}
		
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('RECORDS', $recordModels);
		global $site_URL;
        $viewer->assign('URL_ROOT', $site_URL);
		echo $viewer->view('ShowInfosForCopy.tpl', $moduleName, true);
		exit;
	}
}
