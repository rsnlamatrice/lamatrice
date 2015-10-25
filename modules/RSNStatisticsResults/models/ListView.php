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
class RSNStatisticsResults_ListView_Model extends Vtiger_ListView_Model {

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

		return $links;
	}
	/*
	 * Function to give advance links of a module
	 *	@RETURN array of advanced links
	 */
	public function getAdvancedLinks(){
		$moduleModel = $this->getModule();
		$createPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'EditView');
		$advancedLinks = array();

		$exportPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'Export');
		if($exportPermission) {
			$advancedLinks[] = array(
					'linktype' => 'LISTVIEW',
					'linklabel' => 'LBL_EXPORT',
					'linkurl' => 'javascript:Vtiger_List_Js.triggerExportAction("'.$this->getModule()->getExportUrl().'")',
					'linkicon' => ''
				);
		}
		
		$moduleModel = Vtiger_Module_Model::getInstance('RSNStatistics');
		$moduleName = $moduleModel->getName();

		$advancedLinks[] = array(
			'linklabel' => vtranslate('LBL_CALCULATE_FOR_ALL_RELATED', $moduleName),
			'linkurl' => $moduleModel->getUpdateValuesUrl('*', false, false),
			'linkicon' => 'ui-icon-refresh'
		);
		
		$advancedLinks[] = array(
			'linklabel' => vtranslate('LBL_CALCULATE_FOR_ALL_RELATED_THIS_YEAR', $moduleName),
			'linkurl' => $moduleModel->getUpdateValuesUrl('*', false, false, 'year'),
			'linkicon' => 'refresh'
		);
		
		return $advancedLinks;
	}
	/*
	 * Function to get Basic links
	 * @return array of Basic links
	 */
	public function getBasicLinks(){
		$basicLinks = array();
		
		return $basicLinks;
	}
	
	/**
	 * Function to get the list view header
	 * @return <Array> - List of Vtiger_Field_Model instances
	 */
	public function getListViewHeaders() {
		$headerFieldModels = array();
		
		$fieldName = 'rsnstatisticsfieldsid';
		$fieldModel = RSNStatisticsResults_Field_Model::getInstanceForStatisticsFieldsIdField();
		$headerFieldModels[$fieldName] = $fieldModel;
		
		$relatedModuleName = $this->get('relmodule');
		if(!$relatedModuleName
		&& $this->get('search_key') && in_array('relmodule', $this->get('search_key'))){
			$search_value = $this->get('search_value');
			$relatedModuleName = $search_value[array_search('relmodule', $this->get('search_key'))];
			$this->set('relmodule', $relatedModuleName);
		}
		
		if(!$this->get('aggregate') && !$this->get('parentid')){
			$fieldName = 'crmid';
			$fieldModel = RSNStatisticsResults_Field_Model::getInstanceForRelatedIdField();
			$fieldModel->set('hide', true);
			$headerFieldModels[$fieldName] = $fieldModel;
		}
		
		$fieldName = 'relmodule';
		$fieldModel = RSNStatisticsResults_Field_Model::getInstanceForRelatedModuleField();
		if( $relatedModuleName
		|| !$this->get('aggregate') && !$this->get('parentid')){
			$fieldModel->set('hide', true);
		}
		$headerFieldModels[$fieldName] = $fieldModel;
		
		
		$statRecords = RSNStatistics_Utils_Helper::getRelatedStatisticsRecordModels($relatedModuleName);
		if($statRecords){
			$statsModuleModel = Vtiger_Module_Model::getInstance('RSNStatistics');
			$statsResultsModuleModel = Vtiger_Module_Model::getInstance('RSNStatisticsResults');
			$periodicityField = false;
			$periods = array();
			foreach($statRecords as $statRecord){
				$periods = array_merge($periods, $statsResultsModuleModel->getResultsPeriods($statRecord->getId()));
			}
			foreach($periods as $periodCode=>$periodName){
				if(!$periodicityField){
					$periodicityField = $statsModuleModel->getField('stats_periodicite');
					$periodicityField->set('uitype', 7);
					$periodicityField->set('typeofdata', 'N~O');
				}
				else
					$periodicityField = clone $periodicityField;
				$periodicityField = clone $periodicityField;
				$periodicityField->set('label', $periodName);
				$headerFieldModels[$periodCode] = $periodicityField;
			}
		}
		
		return $this->initListViewHeadersFilters($headerFieldModels);
	}


	/**
	 * Function to get the list view entries
	 * @param Vtiger_Paging_Model $pagingModel
	 * @return <Array> - Associative array of record id mapped to Vtiger_Record_Model instance.
	 */
	public function getListViewEntries($pagingModel) {
		$db = PearDatabase::getInstance();
		$listHeaders = $this->getListViewHeaders();
		$listViewRecordModels = array();

		$moduleName = $this->getModule()->get('name');
		$moduleFocus = CRMEntity::getInstance($moduleName);
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		
		$listViewContoller = $this->get('listview_controller');
	
		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();
		
		global $adb;
		$listQuery = $this->getQuery();
		$listQuery .= " LIMIT $startIndex,".($pageLimit+1);
		
		$listResult = $db->pquery($listQuery, array());
		if(!$listResult){
			echo $db->echoError() . '<pre>' . $listQuery . '</pre>';
		}
		
		$listViewRecordModels = array();
		//ne fonctionne pas $listViewEntries =  $listViewContoller->getListViewRecords($moduleFocus, $moduleName, $listResult);
		$listViewEntries = array();
		$rowCount = $db->num_rows($listResult);
		for($i = 0; $i < $rowCount; $i++){
			$rawData = $db->fetch_row($listResult, $i);
			$listViewEntries[$rawData['id']] = $rawData;
		}
		
		$pagingModel->calculatePageRange($listViewEntries);

		if($db->num_rows($listResult) > $pageLimit){
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
		//var_dump($listViewRecordModels);
		return $listViewRecordModels;
	}

	function getQuery() {
		$queries = array();
		$listHeaders = $this->getListViewHeaders();
		
		$relatedModuleName = $this->get('relmodule');
		$statRecords = RSNStatistics_Utils_Helper::getRelatedStatisticsRecordModels($relatedModuleName);
		foreach($statRecords as $statisticId => $statRecord){
			if(!$relatedModuleName || in_array( $relatedModuleName, $statRecord->getResultsModuleNames())){
				
				$queries[] = RSNStatistics_Utils_Helper::getRelationQuery($relatedModuleName ? $relatedModuleName : $statRecord->getResultsModuleNames(), false, $statisticId);
			}
		}
		return $queries[0];//TODO
	}
}
