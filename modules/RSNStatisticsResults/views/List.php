<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class RSNStatisticsResults_List_View extends Vtiger_List_View {
	
	function preProcess(Vtiger_Request $request, $display=true) {
		parent::preProcess($request, $display);
	}
	function initializeListViewContents (Vtiger_Request $request, $viewer) {
		
		if(!($listViewModel = $this->listViewModel)){
			$moduleName = $request->getModule();
			$cvId = $this->viewName;
		
			$listViewModel = $this->listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $cvId);
		
			
		
			$searchKey = $request->get('search_key');
			$searchValue = $request->get('search_value');
			$searchInput = $request->get('search_input');
			$operator = $request->get('operator');
			if(!empty($searchKey) && (!empty($searchValue) || ($searchValue == '0'))) {
				if($searchKey === 'statisticsid'){
					$statisticsIds = array($searchValue);
				} elseif( is_array($searchKey) && in_array('rsnstatisticsid', $searchKey)){
					$statisticsIds = $searchValue[array_search('rsnstatisticsid', $searchKey)] ;
				}
				$listViewModel->set('rsnstatisticsid', $statisticsIds);
			}
			if(!empty($searchKey)){
				$listViewModel->set('search_key', $searchKey);
				$listViewModel->set('search_value', $searchValue);
				$listViewModel->set('search_input', $searchInput);
			}
		}
		
		return parent::initializeListViewContents ($request, $viewer);
		
	}
}