<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/* ED141013
	 */
class Documents_Relation_Model extends Vtiger_Relation_Model {

	
	/**
	 * Function to get Documents enabled modules list for detail view of record
	 * @return <array> List of modules
	 * ED140906
	 */
	public function getModulesInfoForDetailView() {
		return array(
			'Contacts' => array('fieldName' => 'crmid', 'tableName' => 'vtiger_senotesrel'),
			'Campaigns' => array('fieldName' => 'crmid', 'tableName' => 'vtiger_senotesrel'),
		);
	}
	
	/* Suppression d'une relation Contacts */

	public function deleteRelation($sourceRecordId, $relatedRecordId, $relatedRecordDateApplicationList = FALSE){
		$destinationModuleName = $this->getRelationModuleModel()->get('name');
		/*if($destinationModuleName == "Campaigns")
			return parent::deleteRelation($sourceRecordId, $relatedRecordId);*/
		/*
		$sourceModule = $this->getParentModuleModel();
		$sourceModuleName = $sourceModule->get('name');
		$destinationModuleFocus = CRMEntity::getInstance($destinationModuleName);*/
		
		$tableName = 'vtiger_senotesrel';
		$fieldName = 'crmid';
		$db = PearDatabase::getInstance();
		$params = array();
		$deleteQuery = "DELETE FROM $tableName
			WHERE $fieldName = ?
			AND notesid = ?"
			. ($relatedRecordDateApplicationList === FALSE ? '' : " AND dateapplication = ?");
		$params[] = $relatedRecordId;
		$params[] = $sourceRecordId;
		if($relatedRecordDateApplicationList !== FALSE )
			$params[] = $relatedRecordDateApplicationList;
		//var_dump($deleteQuery, $params);
		if($db->pquery($deleteQuery, $params) === FALSE)
			return false;
		//DeleteEntity($destinationModuleName, $sourceModuleName, $destinationModuleFocus, $relatedRecordId, $sourceRecordId);
		return true;
	}


	/** 
	 * Function to update the data of relation
	 * @param <Number> Campaign record id
	 * @param <array> $values
	 * 	$values = array(relatedRecordId => array(
					'dateapplication' => date en table,
					'value' => valeur))
	 */
	public function updateRelatedField($sourceRecordId, $data = array(), $fieldToUpdate = 'dateapplication|contreltype') {
		//var_dump($sourceRecordId, $values, $fieldToUpdate);
		if ($sourceRecordId && $data) {
			
			$relatedModuleName = $this->getRelationModuleModel()->getName();
			$modulesInfo = $this->getModulesInfoForDetailView();

			if (array_key_exists($relatedModuleName, $modulesInfo)) {
				switch($fieldToUpdate){
					case 'rel_data': $fieldToUpdate = 'data'; break;
					default: break;
				}
				$fieldName = $modulesInfo[$relatedModuleName]['fieldName'];
				$tableName = $modulesInfo[$relatedModuleName]['tableName'];
				$db = PearDatabase::getInstance();

				$params = array();
				
				foreach ($data as $relatedRecordId => $datum) {
					$params = array();
					$updateQuery = "UPDATE $tableName
						SET $fieldToUpdate = ?
						WHERE ($fieldName = ?
						AND notesid = ?)"
						//AND dateapplication = ?
					;
					$params[] = $datum['value'];
					$params[] = $relatedRecordId;
					$params[] = $sourceRecordId;
					
					//$params[] = $datum['dateapplication'];
					//var_dump($updateQuery, $params);
					if($db->pquery($updateQuery, $params) === FALSE)
						return false;
				}
				return true;
			}
		}
	}

}