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

	public function getAddRelationLinks() {
		$relationModel = $this->getRelationModel();
		$addLinkModel = array();

		if(!$relationModel->isAddActionSupported()) {
			return $addLinkModel;
		}
		$relatedModel = $relationModel->getRelationModuleModel();
		
		if($relatedModel->get('label') == 'SalesOrder'){
			$addLinkList = array(
				array(
					'linktype' => 'LISTVIEWBASIC',
					'linklabel' => 'Ajouter une commande',
					'linkurl' => $this->getCreateViewUrl() . "&typedossier=Variation",
					'linkicon' => '',
				),
				array(
					'linktype' => 'LISTVIEWBASIC',
					'linklabel' => 'Ajouter une facture',
					'linkurl' => $this->getCreateViewUrl() . "&typedossier=Facture",
					'linkicon' => '',
				),
				array(
					'linktype' => 'LISTVIEWBASIC',
					'linklabel' => 'Ajouter un inventaire',
					'linkurl' => $this->getCreateViewUrl() . "&typedossier=Inventaire",
					'linkicon' => '',
				),
			);
		}
		else
			return parent::getAddRelationLinks();
		
		foreach($addLinkList as $addLink) {
			$addLinkModel[] = Vtiger_Link_Model::getInstanceFromValues($addLink);
		}
		return $addLinkModel;
	}
	
	/* Retourne les en-têtes des colonnes des tables liÈes
	 * Ajoute les champs de la relation
	 * */
	public function getHeaders() {
			
		$relationModel = $this->getRelationModel();
		$relatedModuleModel = $relationModel->getRelationModuleModel();
		
		switch($relatedModuleModel->name){
		  case "Critere4D":
		    
		      /* Champs issus de critere4dcontrel */
		      $headerFields = Critere4D_RelationListView_Model::get_related_fields(parent::getHeaders());
		      
		    break;
		  case "Documents":
		    
				$headerFields = array();//parent::getHeaders()
				$headerFieldNames = array(
					'notes_title', 'folderid'
				);
				foreach($headerFieldNames as $fieldName) {
					$headerFields[$fieldName] = $relatedModuleModel->getField($fieldName);
				}
		    
				/* Champs issus de critere4dcontrel */
				$headerFields = Documents_RelationListView_Model::get_related_fields($headerFields);
				
		    break;
		  case "Campaigns":
				$headerFields = array();//parent::getHeaders()
				$headerFieldNames = array(
					'campaignname', 'campaigntype'
				);
				foreach($headerFieldNames as $fieldName) {
					$headerFields[$fieldName] = $relatedModuleModel->getField($fieldName);
				}
		    
				/* Champs issus de critere4dcontrel */
				$headerFields = Documents_RelationListView_Model::get_related_fields($headerFields);
				
		    break;
		
		  case "Contacts":
		    
			$headerFields = array();
		
		      //$headerFieldNames = $relatedModuleModel->getRelatedListFields();
			$headerFieldNames = array(
				'isgroup', 'firstname', 'lastname', 'contacttype', 'mailingstreet2',
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
	
	/* Retourne les en-têtes des colonnes des contacts
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
	    $field->set('typeofdata', 'V~0');
	    $field->set('uitype', 402);
		    
	    array_push($headerFields, $field);
		
	    //Added to support dateapplication
	    $field = new Vtiger_Field_Model();
	    $field->set('name', 'dateapplication');
	    $field->set('column', 'dateapplication');
	    $field->set('label', 'Date d\'application');
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

		$orderBy = $this->get('orderby');
		if(!$orderBy) {
			switch($relatedModuleName){
			 case "RSNAboRevues":
				$this->set('orderby', 'debutabo');
				$this->set('sortorder', 'DESC');
				break;
			 case "Invoice":
				$this->set('orderby', 'invoicedate');
				$this->set('sortorder', 'DESC');
				break;
			 case "SalesOrder":
				$this->set('orderby', 'createdtime');
				$this->set('sortorder', 'DESC');
				break;
			 case "RsnPrelevements":
				$this->set('orderby', 'createdtime');
				$this->set('sortorder', 'DESC');
				break;
			 case "Campaigns":
			 case "Contacts":
			 case "Documents":
			 case "Critere4D":
				$this->set('orderby', 'dateapplication');
				$this->set('sortorder', 'DESC');
				break;
			 default:
				break;
			}
		}
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
			  case "Invoice":
			  case "RSNAboRevues":
			  case "SalesOrder":
				return $relatedRecordModelsList;
			
			  case "Critere4D":
				$query = "SELECT dateapplication,
					data AS rel_data, $fieldName
					FROM $tableName
					WHERE $fieldName IN (". generateQuestionMarks($relatedRecordIdsList).")
					AND contactid = ?
					ORDER BY dateapplication desc";
			
				$fieldRels = Critere4D_RelationListView_Model::get_related_fields();
				break;
			
			  case "Documents":
				$query = "SELECT dateapplication,
					data AS rel_data, $fieldName
					FROM $tableName
					WHERE $fieldName IN (". generateQuestionMarks($relatedRecordIdsList).")
					AND crmid IN (?, ?)
					ORDER BY dateapplication desc";//contactid ou accountid
				$where_accountId = $parentRecordModel->get('account_id');
			
				$fieldRels = Documents_RelationListView_Model::get_related_fields();
				break;
			  case "Campaigns":
				$query = "SELECT dateapplication,
					data AS rel_data, $fieldName
					FROM $tableName
					WHERE $fieldName IN (". generateQuestionMarks($relatedRecordIdsList).")
					AND contactid = ?";
			
				$fieldRels = Campaigns_RelationListView_Model::get_related_fields();
				break;
			  case "Contacts":
				$query = "SELECT $tableName.dateapplication,
					$tableName.contreltype, $tableName.$fieldName, $tableName.contactid
					FROM $tableName
					JOIN vtiger_crmentity
						ON $tableName.contactid = vtiger_crmentity.crmid
					WHERE (($tableName.$fieldName IN (". generateQuestionMarks($relatedRecordIdsList).")
					  AND $tableName.contactid = ?)
					OR ($tableName.contactid IN (". generateQuestionMarks($relatedRecordIdsList).")
					  AND $tableName.$fieldName = ?)
					)
					AND vtiger_crmentity.deleted = 0
					  
					UNION
					/* compte commun */
					SELECT account.createdtime, 'Compte commun' AS contreltype
					, vtiger_contactdetails.contactid, c1.contactid
					FROM vtiger_contactdetails c1
					JOIN vtiger_crmentity account
						ON c1.accountid = account.crmid
					JOIN vtiger_contactdetails
						ON vtiger_contactdetails.accountid = account.crmid
					JOIN vtiger_crmentity
						ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid
					WHERE c1.contactid = ".$contactId."
					AND vtiger_contactdetails.contactid <> ".$contactId."
					AND account.deleted = 0
					AND vtiger_crmentity.deleted = 0
				
					UNION
					
					/* Transférer revue */
					SELECT vtiger_crmentity.modifiedtime, '[Transférer la revue vers]'
					, vtiger_contactscf.transfererrevue, vtiger_contactscf.contactid
					FROM vtiger_contactscf
					JOIN vtiger_crmentity
						ON vtiger_crmentity.crmid = vtiger_contactscf.contactid
					WHERE vtiger_crmentity.deleted = 0
					AND  vtiger_contactscf.contactid = ".$contactId."
					AND vtiger_contactscf.transfererrevue IS NOT NULL
					
					UNION
					
					/* Transférer dons */
					SELECT vtiger_crmentity.modifiedtime, '[Transférer les dons vers]'
					, vtiger_contactscf.transfererdons, vtiger_contactscf.contactid
					FROM vtiger_contactscf
					JOIN vtiger_crmentity
						ON vtiger_crmentity.crmid = vtiger_contactscf.contactid
					WHERE vtiger_crmentity.deleted = 0
					AND vtiger_contactscf.contactid = ".$contactId."
					AND vtiger_contactscf.transfererdons IS NOT NULL
				
					UNION
					
					/* Transférer revue depuis */
					SELECT vtiger_crmentity.modifiedtime, '[Transférer la revue depuis]'
					, vtiger_contactscf.contactid, vtiger_contactscf.contactid
					FROM vtiger_contactscf
					JOIN vtiger_crmentity
						ON vtiger_crmentity.crmid = vtiger_contactscf.contactid
					WHERE vtiger_crmentity.deleted = 0
					AND vtiger_contactscf.transfererrevue = ".$contactId."
					
					UNION
					
					/* Transférer dons */
					SELECT vtiger_crmentity.modifiedtime, '[Transférer les dons depuis]'
					, vtiger_contactscf.contactid, vtiger_contactscf.contactid
					FROM vtiger_contactscf
					JOIN vtiger_crmentity
						ON vtiger_crmentity.crmid = vtiger_contactscf.contactid
					WHERE vtiger_crmentity.deleted = 0
					AND vtiger_contactscf.transfererdons = ".$contactId."
					
					ORDER BY dateapplication desc
					";
					  
				array_push($relatedRecordIdsList, $contactId);
				$relatedRecordIdsList = array_merge($relatedRecordIdsList, array_keys($relatedRecordModelsList));
							
				$fieldRels = self::get_related_contacts_fields();
				break;
			
			  default:
				return $relatedRecordModelsList;
				//die(__FILE__ . ' getEntries : module ' . $relatedModuleName . ' non traité');
				//break;
			}
			if($query){
				array_push($relatedRecordIdsList, $contactId);
				if(isset($where_accountId))
					array_push($relatedRecordIdsList, $where_accountId);
				$result = $db->pquery($query, $relatedRecordIdsList);
				if(!$result)
					$db->echoError();
				$numOfrows = $db->num_rows($result);
				
				for($i=0; $i<$numOfrows; $i++) {
					$recordId = $db->query_result($result, $i, $fieldName);
					if($relatedModuleName == "Contacts"
					&& $recordId == $contactId) 
						$recordId = $db->query_result($result, $i, "contactid");
				
					$relatedRecordModel = $relatedRecordModelsList[$recordId];
					if($relatedRecordModel){
						foreach($fieldRels as $fieldRel){
							
							$fieldRelType = $fieldRel->get('typeofdata');
							$fieldRel = $fieldRel->get('name');
							$value = $db->query_result($result, $i, strtolower( $fieldRel ));
							switch($fieldRelType){
							  case "D":
							  case "DATETIME":
								  if($value)
									  $value = new DateTime($value);//preg_match('/0{1,4}[-\/]0{1,2}[-\/]0{1,4}/', $value) ? '0000-00-00' : (new DateTime($value))->format('Y-m-d H:i:s');
								break;
							  default:
								$value = preg_replace('/\\r\\n?/', '<br/>', $value);
								break;
							}
							$values = $relatedRecordModel->get($fieldRel);//valeur précédemment affectée
							if(!is_array($values))//1er tour
								$values = array($value);
							else
								$values[] = $value;
							$relatedRecordModel->set($fieldRel, $values);
						}
						$relatedRecordModelsList[$recordId] = $relatedRecordModel;
					}
				}
			}
		}
		return $relatedRecordModelsList;
	}
}