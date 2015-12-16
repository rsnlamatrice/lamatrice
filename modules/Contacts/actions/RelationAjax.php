<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Contacts_RelationAjax_Action extends Vtiger_RelationAjax_Action {

	public function __construct() {
		parent::__construct();
		$this->exposeMethod('updateDateApplicationContacts');
		$this->exposeMethod('updateRelDataContacts');
		$this->exposeMethod('deleteRelationContacts');
		$this->exposeMethod('addRelationContacts');
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
		switch( $request->get('related_module') ){
		 case 'Contacts' :
			return $this->addRelationContacts($request);
			break;
		 case 'Critere4D' :
		 case 'Documents' :
			return $this->addRelationMultiDates($request);
			break;
		 default :
			return parent::addRelation($request);
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
	 *
	 *		ED151216
	 *			SalesOrder : delete record et non la relation
	 */
	function deleteRelation($request) {

		$relatedModule = $request->get('related_module');
		switch($relatedModule){
		case "SalesOrder":
			/* Suppression complète du dépôt-vente */
			$relatedRecordIds = $request->get('related_record_list');
			foreach($relatedRecordIds as $relatedRecordId){
				$relatedRecordModel = Vtiger_Record_Model::getInstanceById($relatedRecordId, $relatedModule);
				$relatedRecordModel->delete();
			}
			break;
		default:
			parent::deleteRelation($request);
			break;
		}
	}

	/**
	 * Function to update Relation DateApplication
	 * @param Vtiger_Request $request
	 */
	public function updateDateApplicationContacts(Vtiger_Request $request) {
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
	public function updateRelDataContacts(Vtiger_Request $request) {
		$relatedModuleName = 'Contacts';//$request->get('relatedModule');
		$relatedRecordId = $request->get('relatedRecord');
		$fieldToUpdate = 'contreltype';
		$new_value = $request->get($fieldToUpdate);
		$dateapplication = $request->get('dateapplication');
		
		$response = new Vtiger_Response();

		if ($relatedRecordId && $relatedModuleName) {
			$sourceModuleModel = Vtiger_Module_Model::getInstance($request->getModule());
			$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModuleName);
			$relationModel = Vtiger_Relation_Model::getInstance($relatedModuleModel, $sourceModuleModel);
			/*echo('sourceRecord=');
			var_dump($request->get('sourceRecord'));*/
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
	function deleteRelationContacts($request) {
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
			$response = $relationModel->deleteRelationContacts($sourceRecordId, $relatedRecordId, $relatedRecordDateApplicationList[$i]); //ED140917 3eme argument
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
	function addRelationContacts($request) {
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
			$response = $relationModel->addRelation($sourceRecordId,$relatedRecordId); //ED140917 3eme argument
			$i++;
		}
		echo $response;
	}
	
}
