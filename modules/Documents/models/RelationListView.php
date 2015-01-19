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
		      
		      /* Champs issus de critere4dcontrel */
		      $headerFields = self::get_related_fields($headerFields);
		    break;
		  
		  default:
		    return parent::getHeaders();
		}

		return $headerFields;
	}
	
	/* Retourne les en-têtes des colonnes des contacts
	 * Ajoute les champs de la relation
	 * ED140907
	 * */
	public static function get_related_fields($headerFields = false) {
	    if(!$headerFields)
		$headerFields = array();
	/*	
	    //Added to support dateapplication
	    $field = new Vtiger_Field_Model();
	    $field->set('name', 'dateapplication');
	    $field->set('column', strtolower( 'dateapplication' ));
	    $field->set('label', 'Date d\'application');
	    $field->set('typeofdata', 'DATETIME');
	    $field->set('uitype', 6);
	    
	    array_push($headerFields, $field);

	    //Added to support data
	    $field = new Vtiger_Field_Model();
	    $field->set('name', 'rel_data');
	    $field->set('column', 'data');
	    $field->set('label', 'Information');
	    $field->set('typeofdata', 'VARCHAR(255)');
	    $field->set('uitype', 2);
		    
	    array_push($headerFields, $field);
	*/
	    return $headerFields;
	}
	
	/**
	 * Function to get list of record models in this relation
	 * @param <Vtiger_Paging_Model> $pagingModel
	 * @return <array> List of record models <Vtiger_Record_Model>
	 * ED140906 : complète les enregistrements de contacts par les champs de la relation
	 */
	public function getE________ntries($pagingModel) {
		$relationModel = $this->getRelationModel();
		$parentRecordModel = $this->getParentRecordModel();
		$relatedModuleName = $relationModel->getRelationModuleModel()->getName();

		$relatedRecordModelsList = parent::getEntries($pagingModel);
		
		$critere4DModulesInfo = $relationModel->getModulesInfoForDetailView();

		if (array_key_exists($relatedModuleName, $critere4DModulesInfo) && $relatedRecordModelsList) {
			$fieldName = $critere4DModulesInfo[$relatedModuleName]['fieldName'];
			$tableName = $critere4DModulesInfo[$relatedModuleName]['tableName'];

			$db = PearDatabase::getInstance();
			//$db->setDebug(true);
			$relatedRecordIdsList = array_keys($relatedRecordModelsList);

			$query = "SELECT dateapplication,
				data AS rel_data, $fieldName
				FROM $tableName
				WHERE $fieldName IN (". generateQuestionMarks($relatedRecordIdsList).")
				AND critere4did = ?
				ORDER BY $fieldName, dateapplication DESC";
			array_push($relatedRecordIdsList, $parentRecordModel->getId());
			$result = $db->pquery($query, $relatedRecordIdsList);

			$numOfrows = $db->num_rows($result);
			
			$fieldRels = self::get_related_fields();
			
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
				  $values = $relatedRecordModel->get($fieldRel);//valeur précédemment affectée
				  if($values === null)
				    $values = array($value);
				  else
				    $values[] = $value;
				    
				    /*$value = $relatedRecordModel->get($fieldRel);
				  if($value == null)
				    $value = '';
				  else
				    $value = preg_replace('/\\r\\n?/', '<br/>', $db->query_result($result, $i, strtolower( $fieldRel )));
				  if($i > 0)
				    $value .= "\r" . $value;
				  else
				    $value = preg_replace('/\\r\\n?/', '<br/>', $db->query_result($result, $i, strtolower( $fieldRel )));
				    */
				  $relatedRecordModel->set($fieldRel, $values);
				}
/*echo("dateapplication = ");
var_dump($db->query_result($result, $i, strtolower( 'dateapplication' )));	*/			
				$relatedRecordModelsList[$recordId] = $relatedRecordModel;
			}
		}
		return $relatedRecordModelsList;
	}
}