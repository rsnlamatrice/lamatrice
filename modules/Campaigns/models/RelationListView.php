<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Campaigns_RelationListView_Model extends Vtiger_RelationListView_Model {

	
	/* Retourne les en-têtes des colonnes des contacts
	 * Ajoute les champs de la relation
	 * */
	public function getHeaders() {
		$headerFields = array();
		
		$relationModel = $this->getRelationModel();
		$relatedModuleModel = $relationModel->getRelationModuleModel();
		
		//var_dump($relatedModuleModel->name);
		
		switch($relatedModuleModel->name){
		  case "Contacts": //TODO regrouper avec Contacts/models/RelationListView.php
		    
		      $headerFieldNames = array(
			  'isgroup', 'firstname', 'lastname', 'accounttype', 'mailingzip', 'mailingcity', 'mailingcountry'
		      );
		      foreach($headerFieldNames as $fieldName) {
			  $headerFields[$fieldName] = $relatedModuleModel->getField($fieldName);
		      }
		      
		    break;
		  
		  default:
		    return parent::getHeaders();
		}

		return $headerFields;
	}
	
	
	/**
	 * Function to get the links for related list
	 * @return <Array> List of action models <Vtiger_Link_Model>
	 */
	public function getLinks() {
		$relatedLinks = parent::getLinks();
		$relationModel = $this->getRelationModel();
		$relatedModuleName = $relationModel->getRelationModuleModel()->getName();

		if (array_key_exists($relatedModuleName, $relationModel->getEmailEnabledModulesInfoForDetailView())) {
			$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
			if ($currentUserPriviligesModel->hasModulePermission(getTabid('Emails'))) {
				$emailLink = Vtiger_Link_Model::getInstanceFromValues(array(
						'linktype' => 'LISTVIEWBASIC',
						'linklabel' => vtranslate('LBL_SEND_EMAIL', $relatedModuleName),
						'linkurl' => "javascript:Campaigns_RelatedList_Js.triggerSendEmail('index.php?module=$relatedModuleName&view=MassActionAjax&mode=showComposeEmailForm&step=step1','Emails');",
						'linkicon' => ''
				));
				$emailLink->set('_sendEmail',true);
				$relatedLinks['LISTVIEWBASIC'][] = $emailLink;
			}
		}
		return $relatedLinks;
	}

	/**
	 * Function to get list of record models in this relation
	 * @param <Vtiger_Paging_Model> $pagingModel
	 * @return <array> List of record models <Vtiger_Record_Model>
	 */
	public function getEntries($pagingModel) {
		$relationModel = $this->getRelationModel();
		$parentRecordModel = $this->getParentRecordModel();
		$relatedModuleName = $relationModel->getRelationModuleModel()->getName();

		$relatedRecordModelsList = parent::getEntries($pagingModel);
		$emailEnabledModulesInfo = $relationModel->getEmailEnabledModulesInfoForDetailView();

		if (array_key_exists($relatedModuleName, $emailEnabledModulesInfo) && $relatedRecordModelsList) {
			$fieldName = $emailEnabledModulesInfo[$relatedModuleName]['fieldName'];
			$tableName = $emailEnabledModulesInfo[$relatedModuleName]['tableName'];

			$db = PearDatabase::getInstance();
			$relatedRecordIdsList = array_keys($relatedRecordModelsList);

			$query = "SELECT campaignrelstatus, $fieldName FROM $tableName
				INNER JOIN vtiger_campaignrelstatus ON vtiger_campaignrelstatus.campaignrelstatusid = $tableName.campaignrelstatusid
				WHERE $fieldName IN (". generateQuestionMarks($relatedRecordIdsList).") AND campaignid = ?";
			array_push($relatedRecordIdsList, $parentRecordModel->getId());

			$result = $db->pquery($query, $relatedRecordIdsList);
			$numOfrows = $db->num_rows($result);

			for($i=0; $i<$numOfrows; $i++) {
				$recordId = $db->query_result($result, $i, $fieldName);
				$relatedRecordModel = $relatedRecordModelsList[$recordId];

				$relatedRecordModel->set('status', $db->query_result($result, $i, 'campaignrelstatus'));
				$relatedRecordModelsList[$recordId] = $relatedRecordModel;
			}
		}
		return $relatedRecordModelsList;
	}
	
	/* Retourne les en-têtes des colonnes des contacts
	 * Ajoute les champs de la relation
	 * ED140907
	 * TODO Fonction à refaire
	 * */
	public static function get_related_fields($headerFields = false, $relationModel = false) {
		if(!$headerFields)
		    $headerFields = array();
		    
		//Added to support dateapplication
		if(!isset($headerFields['dateapplication'])){
			$field = new Vtiger_Field_Model();
			$field->set('name', 'dateapplication');
			$field->set('column', strtolower( 'dateapplication' ));
			$field->set('label', 'Date d\'affectation');
			$field->set('typeofdata', 'DATETIME');
			$field->set('uitype', 6);
			
			array_push($headerFields, $field);
		}
		
		if(!isset($headerFields['rel_data'])){
			//Added to support data
			$field = new Vtiger_Field_Model();
			$field->set('name', 'rel_data');
			$field->set('column', 'data');
			$field->set('label', 'Information');
			$field->set('typeofdata', 'VARCHAR(255)');
			$field->set('uitype', 2);
				
			array_push($headerFields, $field);
		}
		return $headerFields;
	}
}
