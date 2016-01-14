<?php
/*+***********************************************************************************
 * ED150628
 *************************************************************************************/

class Accounts_MassActionAjax_View extends Vtiger_MassActionAjax_View {
	function __construct() {
		parent::__construct();
		$this->exposeMethod('printRecuFiscal');
	}
	
	function printRecuFiscal(Vtiger_Request $request){
		//var_dump($request);
		
		$module = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($module);
		$relatedModuleName = 'Documents';
		$relatedRecords = Vtiger_Module_Model::getInstance($module);
		
		$cvId = $request->get('viewname');
		$recordId = $request->get('selected_ids');
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');
		//compte les contacts
		if(is_array($selectedIds))
			$idsCounter = count($selectedIds) - ($excludedIds ? count($excludedIds) : 0);
		else if($selectedIds == 'all'){
			$sourceListView = new Vtiger_List_View();
			$idsCounter = $sourceListView->getListViewCount($request);
			if($excludedIds)
				$idsCounter -= count($excludedIds);
		}
		//liste des reçus fiscaux
		$year = date('Y');
		$listViewModel = Vtiger_ListView_Model::getInstance($relatedModuleName, 0);
		$listViewModel->set('operator', array('e', array('c', 'OR', 'c', 'OR', 'c', 'OR', 'c')));
		$listViewModel->set('search_key', array('folderid', array('notes_title', null, 'notes_title', null, 'notes_title', null, 'notes_title')));
		$listViewModel->set('search_value', array('Reçus fiscaux', array($year, null, $year - 1, null, $year - 2, null, $year - 3)));
		
		$listViewModel->set('orderby', 'title');
		$listViewModel->set('sortorder','DESC');
		
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', 1);
		$listViewEntries = $listViewModel->getListViewEntries($pagingModel);
		
		if(count($recordId) === 1){
			//Add old reçus fiscaux
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId[0], $module);
			$recordModel->getRelatedRecusFiscaux($module === 'Contacts' ? $recordModel : false, $listViewEntries);
		}
		
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
		$viewer->assign('IS_EMAIL_SENDABLE', $idsCounter === 1);
		
		$viewer->assign('CURRENT_DATE', DateTimeField::convertToUserFormat(date('Y-m-d')));
		
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('PrintRecuFiscal.tpl', 'Accounts');
	}
}
