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
	 * Function returns query execution result widget
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
		
		$paramsValues = array();
		$paramsDetails = array();
		
		$sql = "SELECT COUNT(*) FROM (
			" . $recordModel->getExecutionQuery($paramsValues, $paramsDetails) . "
		) _panel_";
		
		$viewer->assign('QUERY' , $sql);
		$viewer->assign('QUERY_PARAMS' , $paramsDetails);
		
		$db = PearDatabase::getInstance();
		//$db->setDebug(true);
		$result = $db->query($sql);
		//print_r('<pre>' .$sql .  '</pre>');
		if(is_object($result))
			$viewer->assign('RESULT' , $result->fields[0]);
		else
			$viewer->assign('ERROR' , 'Erreur dans la requête ' . $db->database->ErrorMsg());
				
		
		return $viewer->view('ExecutionSummaryWidget.tpl', $moduleName, 'true');
	}
	
	/**
	 * Function returns variables widget
	 * @param Vtiger_Request $request
	 * @return <type>
	 */
	function getVariables(Vtiger_Request $request, &$parentRecordModel = FALSE) {
		//return parent::showRelatedRecords($request);
	
		if(!$parentRecordModel){
			$parentId = $request->get('record');
			$moduleName = $request->getModule();
			$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
		}
		return $parentRecordModel->getRelatedVariables($request);
	}
	
	/**
	 * Function returns variables widget
	 * @param Vtiger_Request $request
	 * @return <type>
	 */
	function showVariables(Vtiger_Request $request, $parentId = FALSE, $paramsPriorValues = FALSE) {
		//return parent::showRelatedRecords($request);
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

		if(is_object($parentId))
			$parentRecordModel = $parentId;
		else {
			if(!$parentId)
				$parentId = $request->get('record');
			$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
		}
		//Contrôle de récursivité infinie
		if(!RSNContactsPanelsExecutionController::stack($parentRecordModel))// attention à bien faire le unstask
			return null;
		
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName);
		
		$relationListView->set('orderby', 'sequence');
		$relationListView->set('sortorder', 'ASC');
		
		/* TODO get variables where rsnpanelid plutot que related list */
		$models = $relationListView->getEntries($pagingModel);
		
		//Affectation des valeurs prioritaires à celle enregistrée dans defaultvalue
		if($paramsPriorValues){
			if(!is_array($paramsPriorValues))
				$paramsPriorValues = RSNContactsPanels_Record_Model::queryParams_decode($paramsPriorValues);
			//var_dump('$paramsPriorValues', $paramsPriorValues);
			$followParamsPriorValues = array();
			foreach($paramsPriorValues as $paramPriorValue)
			{
				//var_dump('$paramPriorValue 1', $paramPriorValue, strpos($paramsPriorValue['name'], '/'));
				if(strpos($paramPriorValue['name'], '/') > 0){
					$followParamPriorValue = array_merge(array(), $paramPriorValue, array(
						'parent' => trim(substr($paramPriorValue['name'], 0, strpos($paramPriorValue['name'], '/'))),
						'name' => trim(substr($paramPriorValue['name'], strpos($paramPriorValue['name'], '/')+1)),
					));
					//var_dump($followParamPriorValue);
					$followParamsPriorValues[] = $followParamPriorValue;
				}
				else {
					//var_dump('$models',$models);
					foreach($models as $variable){
						if($variable->get('name') == $paramPriorValue['name']){
							$variable->set('defaultvalue', $paramPriorValue['value']);
						}
					}
				}
			}
		}
		
		$header = $relationListView->getHeaders();

		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE' , $moduleName);
		$viewer->assign('RELATED_RECORDS' , $models);
		$viewer->assign('RELATED_HEADERS', $header);
		$viewer->assign('RELATED_MODULE' , $relatedModuleName);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		
		/* ED150102*/
		$relatedRecordModel = Vtiger_Record_Model::getCleanInstance($relatedModuleName);//TODO Faire mieux que getCleanInstance
		$viewer->assign('RELATED_RECORD_MODEL', $relatedRecordModel);

		$viewer->assign('REQUEST_OBJECT' , $request);
		$viewer->assign('VIEW_MODEL' , $this);
		//var_dump('FOLLOW_PARAMS_PRIOR_VALUES:',$followParamsPriorValues);
		$viewer->assign('FOLLOW_PARAMS_PRIOR_VALUES' , $followParamsPriorValues);
		
		
		//$db = PearDatabase::getInstance();
		//$db->setDebug(true);
			
		$result = $viewer->view('SummaryWidgets.tpl', $moduleName, 'true');
		
		//$db->setDebug(false);
		RSNContactsPanelsExecutionController::unstack($parentRecordModel);
		
		return $result;
	}
	
	public function showSubPanelVariables($request, $variableRecordModel, $followParamsPriorValues = FALSE){
		$recordModel = RSNContactsPanels_Record_Model::getInstanceByNamePath($variableRecordModel->get('fieldid'));
		if(!$recordModel){
			echo '<code># panel "'.$variableRecordModel->get('fieldid').'" introuvable #</code>';
			return;
		}
		$paramsPriorValues = RSNContactsPanels_Record_Model::queryParams_decode($variableRecordModel->get('defaultvalue'));
		if($followParamsPriorValues){
			//var_dump('$followParamsPriorValues:',$followParamsPriorValues);
			$name = $variableRecordModel->get('name');
			foreach($followParamsPriorValues as $followParamPriorValue)
				if($followParamPriorValue['parent'] == $name)
					$paramsPriorValues[] = $followParamPriorValue;
		}
		//var_dump($paramsPriorValues, RSNContactsPanels_Record_Model::queryParams_decode($variableRecordModel->get('defaultvalue')));
		//var_dump($paramsPriorValues);
		return $this->showVariables($request, $recordModel, $paramsPriorValues);
	}
}
