<?php
/*+***********************************************************************************
  *************************************************************************************/
/* ED141013
 */

class Documents_RelationListView_Model extends Vtiger_RelationListView_Model {
	

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
			/* related fields */
			$headerFields = $this->get_related_fields($headerFields, $relationModel);
		      
			break;
		  
		  default:
			$headerFields = parent::getHeaders();
			break;
		}

		return $headerFields;
	}
	
	/* Retourne les en-têtes des colonnes des contacts
	 * Ajoute les champs de la relation
	 * ED140907
	 * TODO Fonction à refaire
	 * */
	public static function get_related_fields($headerFields = false, $relationModel = false) {
		
		if(!$headerFields)
		    $headerFields = array();
		
		if($relationModel){
			$relatedModuleName = $relationModel->getRelationModuleModel()->getName();
			switch($relatedModuleName){
			  case "Campaigns":
			  case "Invoice":
				return $headerFields;
			  default:
				$parentModuleName = $relationModel->getPModuleModel()->getName();
				switch($parentModuleName){
				  case "Campaigns":
				  case "Invoice":
					return $headerFields;
				  default:
				  
				}
			}
		}
		
		//Added to support dateapplication
		if(!isset($headerFields['dateapplication'])){
			$field = new Vtiger_Field_Model();
			$field->set('name', 'dateapplication');
			$field->set('table', 'vtiger_senotesrel');
			$field->set('column', strtolower( 'dateapplication' ));
			$field->set('label', 'Date d\'affectation');
			$field->set('typeofdata', 'DATETIME');
			$field->set('uitype', 6);
			
			if(is_associative($headerFields))
				$headerFields[$field->getName()] = $field;
			else
				array_push($headerFields, $field);
		}
		
		if(!isset($headerFields['rel_data'])){
			//Added to support data
			$field = new Vtiger_Field_Model();
			$field->set('name', 'rel_data');
			$field->set('table', 'vtiger_senotesrel');
			$field->set('column', 'data');
			$field->set('label', 'Information');
			$field->set('typeofdata', 'VARCHAR(255)');
			$field->set('uitype', 2);
			
			if(is_associative($headerFields))
				$headerFields[$field->getName()] = $field;
			else
				array_push($headerFields, $field);
		}
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
		$modulesInfo = $relationModel->getModulesInfoForDetailView();
		if($modulesInfo[$parentRecordModel->getName()] && $modulesInfo[$parentRecordModel->getName()]['tableName'] == 'vtiger_senotesrel'){
			$orderBy = $this->get('orderby');
			if(!$orderBy) {
				$this->set('orderby', 'dateapplication');
				$this->set('sortorder', 'DESC');
			}
		}
		$relatedRecordModelsList = parent::getEntries($pagingModel);
		if ($relatedRecordModelsList) {
			
			$notesId = $parentRecordModel->getId();
			
			$fieldName = 'crmid';
			$tableName = 'vtiger_senotesrel';

			$db = PearDatabase::getInstance();
			//$db->setDebug(true);
			$relatedRecordIdsList = array_keys($relatedRecordModelsList);
			$query = "SELECT dateapplication,
				data AS rel_data, $fieldName
				FROM $tableName
				WHERE $fieldName IN (". generateQuestionMarks($relatedRecordIdsList).")
				AND notesid = ?";
			
			$fieldRels = self::get_related_fields();
			
			array_push($relatedRecordIdsList, $notesId);
			$result = $db->pquery($query, $relatedRecordIdsList);
			if(!$result)
				$db->echoError();
			$numOfrows = $db->num_rows($result);
			for($i=0; $i<$numOfrows; $i++) {
				$recordId = $db->query_result($result, $i, $fieldName);
				$relatedRecordModel = $relatedRecordModelsList[$recordId];
				if($relatedRecordModel)
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
		return $relatedRecordModelsList;
	}
}