<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
class RSNContactsPanels_Detail_View extends Vtiger_Detail_View {
	/**
	 * Function returns related records based on related moduleName
	 * @param Vtiger_Request $request
	 * @return <type>
	 */
	function showRelatedRecords(Vtiger_Request $request) {
		switch($request->get('relatedModule')){
		case 'RSNPanelsVariables':
			return $this->showVariables($request);
		case 'Execution':
			return $this->showExecution($request);
		default:
			return parent::showRelatedRecords($request);
		}
	}
	/**
	 * Function returns execution
	 * @param Vtiger_Request $request
	 * @return <type>
	 */
	function showExecution(Vtiger_Request $request) {
		$parentId = $request->get('record');
		$moduleName = $request->getModule();
		$recordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
		
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE' , $moduleName);
		$viewer->assign('RECORD_MODEL' , $recordModel);
		
		$sql = "SELECT COUNT(*) FROM (" . $recordModel->getPanelQuery() . ") _execution_query_";
		$db = PearDatabase::getInstance();
		//$db->setDebug(true);
		$result = $db->pquery($sql);
		//var_dump($result);
		if(is_object($result))
			$viewer->assign('RESULT' , $result->fields[0]);
		else
			$viewer->assign('ERROR' , 'Erreur dans la requête ' . $db->database->ErrorMsg());
				
		
		return $viewer->view('ExecutionSummaryWidget.tpl', $moduleName, 'true');
	}
	
	/**
	 * Function returns variables
	 * @param Vtiger_Request $request
	 * @return <type>
	 */
	function showVariables(Vtiger_Request $request) {
		//return parent::showRelatedRecords($request);
	
		$parentId = $request->get('record');
		$pageNumber = $request->get('page');
		$limit = $request->get('limit');
		$relatedModuleName = $request->get('relatedModule');
		$moduleName = $request->getModule();

		if(empty($pageNumber)) {
			$pageNumber = 1;
		}

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		if(!empty($limit)) {
			$pagingModel->set('limit', $limit);
		}

		$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName);
		
		$relationListView->set('orderby', 'sequence');
		$relationListView->set('sortorder', 'ASC');
		
		/* TODO get variables where rsnpanelid plutot que related list */
		$models = $relationListView->getEntries($pagingModel);
		
		$header = $relationListView->getHeaders();

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE' , $moduleName);
		$viewer->assign('RELATED_RECORDS' , $models);
		$viewer->assign('RELATED_HEADERS', $header);
		$viewer->assign('RELATED_MODULE' , $relatedModuleName);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		
		/* ED150102*/
		$relatedRecordModel = Vtiger_Record_Model::getCleanInstance($relatedModuleName);//TODO Faire mieux que getCleanInstance
		$viewer->assign('RELATED_RECORD_MODEL', $relatedRecordModel);

		return $viewer->view('SummaryWidgets.tpl', $moduleName, 'true');
	}
}
