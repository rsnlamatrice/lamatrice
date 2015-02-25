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

class Contacts_RelationListView_Model extends Vtiger_RelationListView_Model {

	/* Retourne les en-tÍtes des colonnes des tables liÈes
	 * Ajoute les champs de la relation
	 * */
	public function getHeaders() {
			
		$relationModel = $this->getRelationModel();
		$relatedModuleModel = $relationModel->getRelationModuleModel();
		
		switch($relatedModuleModel->name){
		  case "Critere4D":
		    
			$headerFields = array();
		
		      $headerFieldNames = $relatedModuleModel->getRelatedListFields();

		      foreach($headerFieldNames as $fieldName) {
			  $headerFields[$fieldName] = $relatedModuleModel->getField($fieldName);
		      }
		      
		      /* Champs issus de critere4dcontrel */
		      $headerFields = Critere4D_RelationListView_Model::get_related_fields($headerFields);
		      
		    break;
		
		  case "Contacts":
		    
			$headerFields = array();
		
		      //$headerFieldNames = $relatedModuleModel->getRelatedListFields();
			$headerFieldNames = array(
				'isgroup', 'firstname', 'lastname', 'accounttype'
			);
		      
			foreach($headerFieldNames as $fieldName) {
				$headerFields[$fieldName] = $relatedModuleModel->getField($fieldName);
			}
		      
		      /* Champs issus de critere4dcontrel */
		      $headerFields = self::get_related_contacts_fields($headerFields);
		      
		    break;
		  
		  default:
			
		    return parent::getHeaders();
		}

		return $headerFields;
	}
	
	/* Retourne les en-tÍtes des colonnes des contacts
	 * Ajoute les champs de la relation
	 * ED141012
	 * */
	public static function get_related_contacts_fields($headerFields = false) {
	    if(!$headerFields)
		$headerFields = array();

	    //Added to support data
	    $field = new Vtiger_Field_Model();
	    $field->set('name', 'contreltype');
	    $field->set('column', 'contreltype');
	    $field->set('label', 'Relation');
	    /*ED140906 tests*/
	    $field->set('typeofdata', 'V~0');
	    $field->set('uitype', 402);
		    
	    array_push($headerFields, $field);
		
	    //Added to support dateapplication
	    $field = new Vtiger_Field_Model();
	    $field->set('name', 'dateapplication');
	    $field->set('column', strtolower( 'dateapplication' ));
	    $field->set('label', 'Date d\'application');
	    /*ED140906 tests*/
	    $field->set('typeofdata', 'D');
	    $field->set('uitype', 6);
	    
	    array_push($headerFields, $field);
	    
	    return $headerFields;
	}
	
	
	/**
	 * Function to get list of record models in this relation
	 * @param <Vtiger_Paging_Model> $pagingModel
	 * @return <array> List of record models <Vtiger_Record_Model>
	 * ED140906 : complète les enregistrements de contacts par les champs de la relation
	 *
	 * Documents : vtiger_senotesrel
	 */
	public function getEntries($pagingModel) {
		$relationModel = $this->getRelationModel();
		$parentRecordModel = $this->getParentRecordModel();
		$relatedModuleName = $relationModel->getRelationModuleModel()->getName();

		$relatedRecordModelsList = parent::getEntries($pagingModel);
		$relatedModulesInfo = $relationModel->getModulesInfoForDetailView();
			
		if (array_key_exists($relatedModuleName, $relatedModulesInfo) && $relatedRecordModelsList) {
			
			$contactId = $parentRecordModel->getId();
			
			$fieldName = $relatedModulesInfo[$relatedModuleName]['fieldName'];
			$tableName = $relatedModulesInfo[$relatedModuleName]['tableName'];

			$db = PearDatabase::getInstance();
			//$db->setDebug(true);
			$relatedRecordIdsList = array_keys($relatedRecordModelsList);
			
			switch($relatedModuleName){
			  case "Critere4D":
				$query = "SELECT dateapplication,
					data AS rel_data, $fieldName
					FROM $tableName
					WHERE $fieldName IN (". generateQuestionMarks($relatedRecordIdsList).")
					AND contactid = ?";
			
				$fieldRels = Critere4D_RelationListView_Model::get_related_fields();
				break;
			  case "Contacts":
				$query = "SELECT dateapplication,
					contreltype, $fieldName, contactid
					FROM $tableName
					WHERE ($fieldName IN (". generateQuestionMarks($relatedRecordIdsList).")
					  AND contactid = ?)
					OR (contactid IN (". generateQuestionMarks($relatedRecordIdsList).")
					  AND $fieldName = ?)
					  
					UNION
					/* compte commun */
					SELECT account.createdtime, 'Compte commun' AS contreltype
					, vtiger_contactdetails.contactid, c1.contactid
					FROM vtiger_contactdetails c1
					JOIN vtiger_crmentity account
						ON c1.accountid = account.crmid
					JOIN vtiger_contactdetails
						ON vtiger_contactdetails.accountid = account.crmid
					WHERE c1.contactid = ".$contactId."
					AND  vtiger_contactdetails.contactid <> ".$contactId."
					";
					  
				array_push($relatedRecordIdsList, $contactId);
				$relatedRecordIdsList = array_merge($relatedRecordIdsList, array_keys($relatedRecordModelsList));
							
				$fieldRels = self::get_related_contacts_fields();
				break;
			  default:
				die(__FILE__ . ' getEntries : ' . $relatedModuleName . ' inconnu');
			}
			array_push($relatedRecordIdsList, $contactId);
			$result = $db->pquery($query, $relatedRecordIdsList);

			$numOfrows = $db->num_rows($result);
			
			for($i=0; $i<$numOfrows; $i++) {
				$recordId = $db->query_result($result, $i, $fieldName);
				if($relatedModuleName == "Contacts"
				&& $recordId == $contactId) 
					$recordId = $db->query_result($result, $i, "contactid");
				foreach($fieldRels as $fieldRel){
				  $relatedRecordModel = $relatedRecordModelsList[$recordId];
				  
				  $fieldRelType = $fieldRel->get('typeofdata');
				  $fieldRel = $fieldRel->get('name');
				  
				  $value = $db->query_result($result, $i, strtolower( $fieldRel ));
				    switch($fieldRelType){
				    case "DATETIME":
					if($value)
						$value = new DateTime($value);//preg_match('/0{1,4}[-\/]0{1,2}[-\/]0{1,4}/', $value) ? '0000-00-00' : (new DateTime($value))->format('Y-m-d H:i:s');
				      break;
				    default:
				      $value = preg_replace('/\\r\\n?/', '<br/>', $value);
				      break;
				  }
				  $values = $relatedRecordModel->get($fieldRel);//valeur prÈcÈdemment affectÈe
				  if($values === null)
				    $values = array($value);
				  else
				    $values[] = $value;
				  $relatedRecordModel->set($fieldRel, $values);
				}		
				$relatedRecordModelsList[$recordId] = $relatedRecordModel;
			}
		}
		return $relatedRecordModelsList;
	}
}