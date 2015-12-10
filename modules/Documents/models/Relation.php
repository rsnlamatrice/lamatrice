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
			'Invoice' => array('fieldName' => 'invoiceid', 'tableName' => 'vtiger_invoice'
							   , 'relatedFieldName' => 'notesid' //  JOIN %sub ON relationTableName.%s = %sub.relatedSourceFieldName
					   ),
		);
	}
	
	/* Suppression d'une relation Contacts */

	public function deleteRelation($sourceRecordId, $relatedRecordId, $relatedRecordDateApplicationList = FALSE){
		$destinationModuleName = $this->getRelationModuleModel()->get('name');
		
		$tableName = 'vtiger_senotesrel';
		$fieldName = 'crmid';
		$db = PearDatabase::getInstance();
		$params = array();
		$deleteQuery = "DELETE FROM $tableName
			WHERE (($fieldName = ? AND notesid = ?)";
		$params[] = $relatedRecordId;
		$params[] = $sourceRecordId;
		if($destinationModuleName === 'Documents'){
			$deleteQuery .= " OR ($fieldName = ? AND notesid = ?)";
			$params[] = $sourceRecordId;
			$params[] = $relatedRecordId;
		}
		$deleteQuery .= ")";
		
		if($relatedRecordDateApplicationList !== FALSE ){
			$deleteQuery .= " AND dateapplication = ?";
			$params[] = $relatedRecordDateApplicationList;
		}
		if($db->pquery($deleteQuery, $params) === FALSE){
			$db->echoError();
			return false;
		}
		//DeleteEntity($destinationModuleName, $sourceModuleName, $destinationModuleFocus, $relatedRecordId, $sourceRecordId);
		return true;
	}



	
	/* Suppression d'une relation Critere4D ou Documents / Contact / Date */

	public function deleteRelationMultiDates($sourceRecordId, $relatedRecordId, $relatedRecordDateApplicationList = FALSE){
		$destinationModuleName = $this->getRelationModuleModel()->get('name');
		
		$tableName = 'vtiger_senotesrel';
		$fieldName = 'crmid';
		$db = PearDatabase::getInstance();
		$params = array();
		$deleteQuery = "DELETE FROM $tableName
			WHERE (($fieldName = ? AND notesid = ?)";
		$params[] = $relatedRecordId;
		$params[] = $sourceRecordId;
		if($destinationModuleName === 'Documents'){
			$deleteQuery .= " OR ($fieldName = ? AND notesid = ?)";
			$params[] = $sourceRecordId;
			$params[] = $relatedRecordId;
		}
		$deleteQuery .= ")";
		
		if($relatedRecordDateApplicationList !== FALSE ){
			$deleteQuery .= " AND dateapplication = ?";
			$params[] = $relatedRecordDateApplicationList;
		}
		if($db->pquery($deleteQuery, $params) === FALSE){
			$db->echoError();
			return false;
		}
		//DeleteEntity($destinationModuleName, $sourceModuleName, $destinationModuleFocus, $relatedRecordId, $sourceRecordId);
		return true;
	}

}