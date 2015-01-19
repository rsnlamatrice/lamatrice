<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Critere4D_Relation_Model extends Vtiger_Relation_Model {

	/**
	 * Function to get Critere4D enabled modules list for detail view of record
	 * @return <array> List of modules
	 * ED140906
	 */
	public function getModulesInfoForDetailView() {
		return array(
			'Contacts' => array('fieldName' => 'contactid'
					    , 'tableName' => 'vtiger_critere4dcontrel'
				),
		);
	}

	
	
	/** 
	 * Function to update the data of relation
	 * @param <Number> Campaign record id
	 * @param <array> $values
	 * 	$values = array(relatedRecordId => array(
					'dateapplication' => date en table,
					'value' => valeur))
	 */
	public function updateRelatedField($sourceRecordId, $data = array(), $fieldToUpdate = 'dateapplication|data') {
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
				//$updateQuery = "UPDATE $tableName SET $fieldToUpdate = CASE $fieldName ";
				//foreach ($data as $relatedRecordId => $datum) {
				//	$updateQuery .= " WHEN $relatedRecordId THEN ? ";
				//	$params[] = $datum['value'];
				//}
				//$updateQuery .= "ELSE $fieldToUpdate END WHERE critere4did = ?";
				//$params[] = $sourceRecordId;
				
				foreach ($data as $relatedRecordId => $datum) {
					$params = array();
					$updateQuery = "UPDATE $tableName
						SET $fieldToUpdate = ?
						WHERE $fieldName = ?
						AND critere4did = ?
						AND dateapplication = ?";
					$params[] = $datum['value'];
					$params[] = $relatedRecordId;
					$params[] = $sourceRecordId;
					$params[] = $datum['dateapplication'];
					//var_dump($updateQuery, $params);
					if($db->pquery($updateQuery, $params) === FALSE)
						return false;
				}
				return true;
			}
		}
	}
	
	/* Suppression d'une relation Critere4D / Contact / Date */
	public function deleteRelation($sourceRecordId, $relatedRecordId, $relatedRecordDateApplicationList = FALSE){
		/*$sourceModule = $this->getParentModuleModel();
		$sourceModuleName = $sourceModule->get('name');
		$destinationModuleName = $this->getRelationModuleModel()->get('name');
		$destinationModuleFocus = CRMEntity::getInstance($destinationModuleName);*/
		$tableName = 'vtiger_critere4dcontrel';
		$fieldName = 'contactid';
		$db = PearDatabase::getInstance();
		$params = array();
		$deleteQuery = "DELETE FROM $tableName
			WHERE $fieldName = ?
			AND critere4did = ?"
			. ($relatedRecordDateApplicationList === FALSE ? '' : "AND dateapplication = ?");
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
	

	public function addRelation($sourceRecordId, $relatedRecordId){
		$tableName = 'vtiger_critere4dcontrel';
		$fieldName = 'contactid';
		$dateapplication = date("Y-m-d H:i:s");
		$params[] = $sourceRecordId;
		$db = PearDatabase::getInstance();
		$params = array();
		$addQuery = "INSERT INTO $tableName (critere4did, $fieldName, dateapplication)
			VALUES(?, ?, ?)";
		$params[] = $sourceRecordId;
		$params[] = $relatedRecordId;
		$params[] = $dateapplication;
		if($db->pquery($addQuery, $params) === FALSE)
			return false;
		return true;
	}
}