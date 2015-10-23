<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_Export_View extends Vtiger_Index_View {

	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'Export')) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED'));
		}
	}

	function process(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		
		$source_module = $request->getModule();
		$viewId = $request->get('viewname');
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');

		$page = $request->get('page');

		$viewer->assign('SELECTED_IDS', $selectedIds);
		$viewer->assign('EXCLUDED_IDS', $excludedIds);
		$viewer->assign('VIEWID', $viewId);
		$viewer->assign('PAGE', $page);
		$viewer->assign('SOURCE_MODULE', $source_module);
		$viewer->assign('MODULE','Export');
        
        $searchKey = $request->get('search_key');
        $searchValue = $request->get('search_value');
		$operator = $request->get('operator');
		if(!empty($operator)) {
			$viewer->assign('OPERATOR',is_array($operator) ? htmlspecialchars(json_encode($operator)) : $operator);
			$viewer->assign('ALPHABET_VALUE',is_array($searchValue) ? htmlspecialchars(json_encode($searchValue)) : $searchValue);
			$viewer->assign('SEARCH_KEY',is_array($searchKey) ? htmlspecialchars(json_encode($searchKey)) : $searchKey);
		}

		//AV151023 : Count the number of rows
		if(empty($viewId)) {
			$viewId = '0';
		}
		$listViewModel = Vtiger_ListView_Model::getInstance($source_module, $viewId);
		$listViewModel->set('search_key', $searchKey);
		$listViewModel->set('search_value', $searchValue);
		$listViewModel->set('operator', $operator);

		$count_all = $listViewModel->getListViewCount($calculatedTotals);

		$viewer->assign('COUNT_ALL', $count_all);
		if (empty($selectedIds)) {
			$viewer->assign('COUNT_SELECTED', 0);
		} else if ($selectedIds == 'all') {
			$viewer->assign('COUNT_SELECTED', $count_all - count($excludedIds));
		} else {
			$viewer->assign('COUNT_SELECTED', count($selectedIds));
		}

		$pagingModel = new Vtiger_Paging_Model();
		$limit = $pagingModel->getPageLimit();
		$currentPage = $request->get('page');
		if(empty($currentPage)) $currentPage = 1;
		$currentPageStart = ($currentPage - 1) * $limit;
		if ($currentPageStart < 0) $currentPageStart = 0;
		if ($currentPageStart + $limit > $count_all) $limit = ($count_all - $currentPageStart);
		
		$viewer->assign('COUNT_CURRENT_PAGE', $limit);//TMP !!!get real number an take care of the last page !!
		
		$viewer->view('Export.tpl', $source_module);
	}
}