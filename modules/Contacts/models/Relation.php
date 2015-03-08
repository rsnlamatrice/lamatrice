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

	
	/* Returns fields defining relation between modules
	 * used in custom view editor
	 * ED150212
	 */
	public function getRelationFields() {
		$fields = array();
		switch($this->getRelationModuleName()){
		case "Contacts":
			//$fieldNames = array('dateapplication', 'data');
			//Added to support data
			$field = new Vtiger_Field_Model();
			$field->set('name', 'contreltype');
			$field->set('column', 'vtiger_contactscontrel:contreltype');
			$field->set('label', 'Désignation');
			$field->set('typeofdata', 'V~O');
			$field->set('uitype', 1);
			array_push($fields, $field);
			    
			//Added to support dateapplication
			$field = new Vtiger_Field_Model();
			$field->set('name', 'dateapplication');
			$field->set('column', 'vtiger_contactscontrel:dateapplication');
			$field->set('label', 'Date d\'application');
			/*ED140906 tests*/
			$field->set('typeofdata', 'D~O');
			$field->set('uitype', 6);
			array_push($fields, $field);
			
			$field = new Vtiger_Field_Model();
			$field->set('name', 'data');
			$field->set('column', 'vtiger_contactscontrel:data');
			$field->set('label', 'Information');
			$field->set('typeofdata', 'V~O');
			$field->set('uitype', 21);
			array_push($fields, $field);
			break;
		
		case "Critere4D":
			//$fieldNames = array('dateapplication', 'data');
			//Added to support data
			$field = new Vtiger_Field_Model();
			$field->set('name', 'data');
			$field->set('column', 'vtiger_critere4dcontrel:data');
			$field->set('label', 'Information');
			$field->set('typeofdata', 'V~O');
			$field->set('uitype', 21);
			array_push($fields, $field);
			    
			//Added to support dateapplication
			$field = new Vtiger_Field_Model();
			$field->set('name', 'dateapplication');
			$field->set('column', 'vtiger_critere4dcontrel:dateapplication');
			$field->set('label', 'Date d\'application');
			/*ED140906 tests*/
			$field->set('typeofdata', 'D~O');
			$field->set('uitype', 6);
			array_push($fields, $field);
			break;
		case "Invoice":
			    
			//Added to support dateapplication
			$field = new Vtiger_Field_Model();
			$field->set('name', 'invoicedate');
			$field->set('column', 'vtiger_invoice:invoicedate');
			$field->set('label', 'Date de facture');
			/*ED140906 tests*/
			$field->set('typeofdata', 'D~O');
			$field->set('uitype', 6);
			array_push($fields, $field);
			    
			//Added to support dateapplication
			$field = new Vtiger_Field_Model();
			$field->set('name', 'typedossier');
			$field->set('column', 'vtiger_invoicecf:typedossier');
			$field->set('label', 'Type de facture');
			/*ED140906 tests*/
			$field->set('typeofdata', 'V~O');
			$field->set('uitype', 15);
			array_push($fields, $field);
			
			break;
		default:
			return parent::getRelationFields();
		}
		return $fields;
	}

	/**
	 * Function to get Critere4D enabled modules list for detail view of record
	 * @return <array> List of modules
	 * ED140906
	 */
	public function getModulesInfoForDetailView() {
		return array(
			'Critere4D' => array('fieldName' => 'critere4did', 'tableName' => 'vtiger_critere4dcontrel'),
			'Contacts' => array('fieldName' => 'relcontid', 'tableName' => 'vtiger_contactscontrel'),
			'Invoice' => array('fieldName' => 'accountid', 'tableName' => 'vtiger_invoice'
					   , 'sourceFieldName' => 'vtiger_contactdetails.accountid'),
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