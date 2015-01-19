<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/* ED140906
	 */
class Contacts_Relation_Model extends Vtiger_Relation_Model {

	/**
	 * Function to get Critere4D enabled modules list for detail view of record
	 * @return <array> List of modules
	 * ED140906
	 */
	public function getModulesInfoForDetailView() {
		return array(
			'Critere4D' => array('fieldName' => 'critere4did', 'tableName' => 'vtiger_critere4dcontrel'),
			'Contacts' => array('fieldName' => 'relcontid', 'tableName' => 'vtiger_contactscontrel'),
		);
	}

	/**
	 * Function to get Campaigns Relation status values
	 * @return <array> List of status values
	 */
	public function getCampaignRelationStatusValues() {
		$statusValues = array();
		$db = PearDatabase::getInstance();
		$result = $db->pquery("SELECT campaignrelstatusid, campaignrelstatus FROM vtiger_campaignrelstatus", array());
		$numOfRows = $db->num_rows($result);

		for ($i=0; $i<$numOfRows; $i++) {
			$statusValues[$db->query_result($result, $i, 'campaignrelstatusid')] = $db->query_result($result, $i, 'campaignrelstatus');
		}
		return $statusValues;
	}

	/**
	 * Function to update the status of relation
	 * @param <Number> Campaign record id
	 * @param <array> $statusDetails
	 */
	public function updateStatus($sourceRecordId, $statusDetails = array()) {
		if ($sourceRecordId && $statusDetails) {
			$relatedModuleName = $this->getRelationModuleModel()->getName();
			$emailEnabledModulesInfo = $this->getEmailEnabledModulesInfoForDetailView();

			if (array_key_exists($relatedModuleName, $emailEnabledModulesInfo)) {
				$fieldName = $emailEnabledModulesInfo[$relatedModuleName]['fieldName'];
				$tableName = $emailEnabledModulesInfo[$relatedModuleName]['tableName'];
				$db = PearDatabase::getInstance();

				$updateQuery = "UPDATE $tableName SET campaignrelstatusid = CASE $fieldName ";
				foreach ($statusDetails as $relatedRecordId => $status) {
					$updateQuery .= " WHEN $relatedRecordId THEN $status ";
				}
				$updateQuery .= "ELSE campaignrelstatusid END WHERE campaignid = ?";
				$db->pquery($updateQuery, array($sourceRecordId));
			}
		}
	}
	
	/* Suppression d'une relation Critere4D / Contact / Date */

	public function deleteRelationCritere4D($sourceRecordId, $relatedRecordId, $relatedRecordDateApplicationList = FALSE){
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
		$params[] = $sourceRecordId;
		$params[] = $relatedRecordId;
		if($relatedRecordDateApplicationList !== FALSE )
			$params[] = $relatedRecordDateApplicationList;
		//var_dump($deleteQuery, $params);
		if($db->pquery($deleteQuery, $params) === FALSE)
			return false;
		//DeleteEntity($destinationModuleName, $sourceModuleName, $destinationModuleFocus, $relatedRecordId, $sourceRecordId);
		return true;
	}
	
	/* Suppression d'une relation Contacts / Contact / Date */

	public function deleteRelationContacts($sourceRecordId, $relatedRecordId, $relatedRecordDateApplicationList = FALSE){
		/*$sourceModule = $this->getParentModuleModel();
		$sourceModuleName = $sourceModule->get('name');
		$destinationModuleName = $this->getRelationModuleModel()->get('name');
		$destinationModuleFocus = CRMEntity::getInstance($destinationModuleName);*/
		$tableName = 'vtiger_contactscontrel';
		$fieldName = 'relcontid';
		$db = PearDatabase::getInstance();
		$params = array();
		$deleteQuery = "DELETE FROM $tableName
			WHERE (($fieldName = ?
			AND contactid = ?)
			OR ($fieldName = ?
			AND contactid = ?)
			)"
			//. ($relatedRecordDateApplicationList === FALSE ? '' : "AND dateapplication = ?")
		;
		$params[] = $sourceRecordId;
		$params[] = $relatedRecordId;
		$params[] = $relatedRecordId;
		$params[] = $sourceRecordId;
		/*if($relatedRecordDateApplicationList !== FALSE )
			$params[] = $relatedRecordDateApplicationList;
		*/
		if($db->pquery($deleteQuery, $params) === FALSE)
			return false;
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
						WHERE ($fieldName = ?
						AND contactid = ?)"
						//AND dateapplication = ?
					;
					if($relatedModuleName == "Contacts")
						$updateQuery .= " OR ($fieldName = ?
								AND contactid = ?)";
					$params[] = $datum['value'];
					$params[] = $relatedRecordId;
					$params[] = $sourceRecordId;
					
					//$params[] = $datum['dateapplication'];
					
					if($relatedModuleName == "Contacts"){
						$params[] = $sourceRecordId;
						$params[] = $relatedRecordId;
					}
					//var_dump($updateQuery, $params);
					if($db->pquery($updateQuery, $params) === FALSE)
						return false;
				}
				return true;
			}
		}
	}

}