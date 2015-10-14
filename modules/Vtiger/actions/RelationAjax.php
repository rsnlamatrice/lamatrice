<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_RelationAjax_Action extends Vtiger_Action_Controller {
	function __construct() {
		parent::__construct();
		$this->exposeMethod('addRelation');
		$this->exposeMethod('deleteRelation');
		$this->exposeMethod('getRelatedListPageCount');
		$this->exposeMethod('updateDateApplicationMultiDates');
		$this->exposeMethod('updateRelDataMultiDates');
		$this->exposeMethod('deleteRelationMultiDates');
		$this->exposeMethod('addRelationMultiDates');
	}

	function checkPermission(Vtiger_Request $request) { }

	function preProcess(Vtiger_Request $request) {
		return true;
	}

	function postProcess(Vtiger_Request $request) {
		return true;
	}

	function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	/*
	 * Function to add relation for specified source record id and related record id list
	 * @param <array> $request
	 *		keys					Content
	 *		src_module				source module name
	 *		src_record				source record id
	 *		related_module			related module name
	 *		related_record_list		json encoded of list of related record ids
	 */
	function addRelation($request) {
		$sourceModule = $request->getModule();
		$sourceRecordId = $request->get('src_record');

		$relatedModule = $request->get('related_module');
		$relatedRecordIdList = $request->get('related_record_list');

		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
		$relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
		foreach($relatedRecordIdList as $relatedRecordId) {
			$relationModel->addRelation($sourceRecordId,$relatedRecordId);
		}
	}

	/**
	 * Function to delete the relation for specified source record id and related record id list
	 * @param <array> $request
	 *		keys					Content
	 *		src_module				source module name
	 *		src_record				source record id
	 *		related_module			related module name
	 *		related_record_list		json encoded of list of related record ids
	 */
	function deleteRelation($request) {
		$sourceModule = $request->getModule();
		$sourceRecordId = $request->get('src_record');

		$relatedModule = $request->get('related_module');
		/* ED141211 */
		switch($relatedModule){
		case "RsnDons":
		case "RsnAbonnements":
		case "RsnAdhesions":
			$relatedModule = 'Invoice';
			break;
		default:
			break;
		}
		$relatedRecordIdList = $request->get('related_record_list');

		//Setting related module as current module to delete the relation
		vglobal('currentModule', $relatedModule);

		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
		$relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
		foreach($relatedRecordIdList as $relatedRecordId) {
			$response = $relationModel->deleteRelation($sourceRecordId, $relatedRecordId);
		}
		
		//ED150124, deleted : echo $response;
	}
	
	/**
	 * Function to get the page count for reltedlist
	 * @return total number of pages
	 */
	function getRelatedListPageCount(Vtiger_Request $request){
		$moduleName = $request->getModule();
		$relatedModuleName = $request->get('relatedModule');
		$parentId = $request->get('record');
		$label = $request->get('tab_label');
		$pagingModel = new Vtiger_Paging_Model();
		$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName, $label);
		$totalCount = $relationListView->getRelatedEntriesCount();
		$pageLimit = $pagingModel->getPageLimit();
		$pageCount = ceil((int) $totalCount / (int) $pageLimit);

		if($pageCount == 0){
			$pageCount = 1;
		}
		$result = array();
		$result['numberOfRecords'] = $totalCount;
		$result['page'] = $pageCount;
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
	
	

	/**
	 * Function to update Relation DateApplication
	 * @param Vtiger_Request $request
	 */
	public function updateDateApplicationMultiDates(Vtiger_Request $request) {
		$relatedModuleName = $request->get('relatedModule');
		$relatedRecordId = $request->get('relatedRecord');
		$fieldToUpdate = 'dateapplication';
		$new_value = (new DateTime($request->get('dateapplication')))->format("Y-m-d H:i:s");
		$prev_value = (new DateTime($request->get('prevdateapplication')))->format("Y-m-d H:i:s");
		//var_dump($prev_value);
		$response = new Vtiger_Response();

		if ($relatedRecordId && preg_match('/^\d{1,4}[-\/]\d{1,2}[-\/]\d{1,4}/', $new_value) !== FALSE) {
			$sourceModuleModel = Vtiger_Module_Model::getInstance($request->getModule());
			$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModuleName);

			$relationModel = Vtiger_Relation_Model::getInstance($relatedModuleModel, $sourceModuleModel);
			/*echo('sourceRecord=');
			var_dump($request->get('sourceRecord'));*/
			$ok = $relationModel->updateRelatedField($relatedRecordId, 
							   array($request->get('sourceRecord') => array(
											     'dateapplication' => $prev_value,
											     'value' => $new_value)
								), 
							   $fieldToUpdate
							);


			$response->setResult(array($ok));
		} else {
			$response->setError($code);
		}
		$response->emit();
	}

	/**
	 * Function to update Relation rel_data
	 * @param Vtiger_Request $request
	 */
	public function updateRelDataMultiDates(Vtiger_Request $request) {
		$relatedModuleName = $request->get('relatedModule');
		$relatedRecordId = $request->get('relatedRecord');
		$fieldToUpdate = 'rel_data';
		$new_value = $request->get($fieldToUpdate);
		$dateapplication = $request->get('dateapplication');
		
		$response = new Vtiger_Response();
		if ($relatedRecordId && $relatedModuleName) {
			$sourceModuleModel = Vtiger_Module_Model::getInstance($request->getModule());
			$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModuleName);
			$relationModel = Vtiger_Relation_Model::getInstance($relatedModuleModel, $sourceModuleModel);

			/*echo('sourceRecord=');
			var_dump($request->get('sourceRecord'));
			var_dump(get_class($relationModel));*/
			$relationModel->updateRelatedField($relatedRecordId, 
							   array($request->get('sourceRecord') => array(
											     'dateapplication' => $dateapplication,
											     'value' => $new_value)
								), 
							   $fieldToUpdate
							);

			$response->setResult(array(true));
		} else {
			$response->setError($code);
		}
		$response->emit();
	}

	/**
	 * Function to delete the relation for specified source record id and related record id list
	 * @param <array> $request
	 *		keys					Content
	 *		src_module				source module name
	 *		src_record				source record id
	 *		related_module			related module name
	 *		related_record_list		json encoded of list of related record ids
	 *		related_dateapplication_list		json encoded of list of related record dateapplication
	 */
	function deleteRelationMultiDates($request) {
		$sourceModule = $request->getModule();
		$sourceRecordId = $request->get('src_record');

		$relatedModule = $request->get('related_module');
		$relatedRecordIdList = $request->get('related_record_list');
		$relatedRecordDateApplicationList = $request->get('related_dateapplication_list');//ED140917 
		if(!is_array($relatedRecordDateApplicationList))
			$relatedRecordDateApplicationList = array_fill(0, count($relatedRecordIdList), null);

		//Setting related module as current module to delete the relation
		vglobal('currentModule', $relatedModule);

		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
		$relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
		
		$i = 0;
		foreach($relatedRecordIdList as $relatedRecordId) {
			$response = $relationModel->deleteRelationMultiDates($sourceRecordId, $relatedRecordId, $relatedRecordDateApplicationList[$i]); //ED140917 3eme argument
			$i++;
		}
		echo $response;
	}
	/**
	 * Function to add a new relation for specified source record id and related record id list
	 * @param <array> $request
	 *		keys					Content
	 *		src_module				source module name
	 *		src_record				source record id
	 *		related_module			related module name
	 *		related_record_list		json encoded of list of related record ids
	 *		related_dateapplication_list		json encoded of list of related record dateapplication
	 */
	function addRelationMultiDates($request) {
		$sourceModule = $request->getModule();
		$sourceRecordId = $request->get('src_record');

		$relatedModule = $request->get('related_module');
		$relatedRecordIdList = $request->get('related_record_list');

		//Setting related module as current module to delete the relation
		vglobal('currentModule', $relatedModule);

		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
		$relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
		
		$i = 0;
		foreach($relatedRecordIdList as $relatedRecordId) {
			$response = $relationModel->addRelation($sourceRecordId, $relatedRecordId); //ED140917 3eme argument
			$i++;
		}
		echo $response;
	}
}
