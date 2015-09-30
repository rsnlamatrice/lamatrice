<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

abstract class Vtiger_Mass_Action extends Vtiger_Action_Controller {

	protected function getRecordsListFromRequest(Vtiger_Request $request) {
		$selectedIds = $request->get('selected_ids');
		if(!empty($selectedIds) && $selectedIds != 'all') {
			if(!empty($selectedIds) && count($selectedIds) > 0) {
				return $selectedIds;
			}
		}
		
		$customViewModel = $this->getCustomViewToGetRecordsListFromRequest($request);
		if($customViewModel) {
			$excludedIds = $request->get('excluded_ids');
			return $customViewModel->getRecordIds($excludedIds,$module);
		}
	}

	//ED150628
	public function getRecordsQueryFromRequest(Vtiger_Request $request, &$asColumnName = FALSE) {
		$selectedIds = $request->get('selected_ids');

		if(!empty($selectedIds) && $selectedIds != 'all') {
			if(!empty($selectedIds) && count($selectedIds) > 0) {
				
				if(!$asColumnName){
					$moduleModel = $this->getModule();
					$asColumnName = $moduleModel->get('basetableid');
				}
				$query = '';
				for($i = 0; $i < count($selectedIds); $i++){
					if($i) $query .= ' UNION SELECT ' . $selectedIds[$i];
					else $query = 'SELECT ' . $selectedIds[$i] . ' AS ' . $asColumnName;
				}
				
				return $query;
			}
		}
		$customViewModel = $this->getCustomViewToGetRecordsListFromRequest($request);
		if($customViewModel) {
			$excludedIds = $request->get('excluded_ids');
			$module = $request->get('module');
			return $customViewModel->getRecordIdsQuery($excludedIds, $module, false, $asColumnName);
		}
	}
	
	//ED150628
	private function getCustomViewToGetRecordsListFromRequest(Vtiger_Request $request) {
		$cvId = $request->get('viewname');
		$module = $request->get('module');
		if(!empty($cvId) && $cvId=="undefined"){
			$sourceModule = $request->get('sourceModule');
			$cvRecord = CustomView_Record_Model::getAllFilterByModule($sourceModule);
			if(!$cvRecord){
				var_dump("Impossible de trouver la vue par défaut, nommé 'All' pour le module $sourceModule.");
				return;
			}
			$cvId = $cvRecord->getId();
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
			return $customViewModel;
		}
	}
}
