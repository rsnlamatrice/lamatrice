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

class Accounts_RelationListView_Model extends Vtiger_RelationListView_Model {

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
		
		  case "Invoice":
		    
			$headerFields = array();
		
		      $headerFieldNames = $relatedModuleModel->getRelatedListFields();

		      foreach($headerFieldNames as $fieldName) {
			  $headerFields[$fieldName] = $relatedModuleModel->getField($fieldName);
		      }
		      
		    break;
		  
		case "Documents":
			// ajoute dateapplication
			$headerFields = array_merge(parent::getHeaders(), $relatedModuleModel->getRelationHeaders());
			break;
	
		  default:
			return parent::getHeaders();
		}

		return $headerFields;
	}
	
	
	/**
	 * Function to get list of record models in this relation
	 * @param <Vtiger_Paging_Model> $pagingModel
	 * @return <array> List of record models <Vtiger_Record_Model>
	 * ED140906 : complète les enregistrements de contacts par les champs de la relation
	 */
	public function getEntries($pagingModel) {
		$relationModel = $this->getRelationModel();
		$parentRecordModel = $this->getParentRecordModel();
		$relatedModuleName = $relationModel->getRelationModuleModel()->getName();
	
		//$db = PearDatabase::getInstance();
		//$db->setDebug(true);
		
		$relatedRecordModelsList = parent::getEntries($pagingModel);
		$relatedModulesInfo = $relationModel->getModulesInfoForDetailView();

		if($relatedModuleName == 'Contacts'){
			$accountId = $parentRecordModel->getId();
			foreach($relatedRecordModelsList as $relatedId => $relatedRecord)
				if($relatedRecord->get('account_id') != $accountId)
					$relatedRecord->set('reference', 0);
			//relation 1->n
			if (array_key_exists($relatedModuleName, $relatedModulesInfo))
				unset($relatedModulesInfo[$relatedModuleName]);
		}
		
		if (array_key_exists($relatedModuleName, $relatedModulesInfo) && $relatedRecordModelsList) {
			//TODO exclusive critere4D
			$fieldName = $relatedModulesInfo[$relatedModuleName]['fieldName'];
			$tableName = $relatedModulesInfo[$relatedModuleName]['tableName'];

			$db = PearDatabase::getInstance();
			//$db->setDebug(true);
			$relatedRecordIdsList = array_keys($relatedRecordModelsList);

			$query = "SELECT dateapplication,
				data AS rel_data, $fieldName
				FROM $tableName
				WHERE $fieldName IN (". generateQuestionMarks($relatedRecordIdsList).")
				AND contactid = ?";
			array_push($relatedRecordIdsList, $parentRecordModel->getId());
			$result = $db->pquery($query, $relatedRecordIdsList);

			$numOfrows = $db->num_rows($result);
			
			switch($relatedModuleName){
				case "Critere4D":
					$fieldRels = Critere4D_RelationListView_Model::get_related_fields();
					break;
				case "Contacts":
					$fieldRels = Contacts_RelationListView_Model::get_related_contacts_fields();
					break;
				default:
					$fieldRels = Vtiger_RelationListView_Model::get_related_fields();
					break;
			}
			
			for($i=0; $i<$numOfrows; $i++) {
				$recordId = $db->query_result($result, $i, $fieldName);
				foreach($fieldRels as $fieldRel){
				  $relatedRecordModel = $relatedRecordModelsList[$recordId];
				  
				  $fieldRelType = $fieldRel->get('typeofdata');
				  $fieldRel = $fieldRel->get('name');
				  
				  $value = $db->query_result($result, $i, strtolower( $fieldRel ));
				    switch($fieldRelType){
				    case "DATETIME":
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