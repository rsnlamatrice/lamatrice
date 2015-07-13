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
		      
			break;
		  
		  default:
			$headerFields = parent::getHeaders();
		}

		/* related fields */
		$headerFields = $this->get_related_fields($headerFields);
		return $headerFields;
	}
	
	/* Retourne les en-têtes des colonnes des contacts
	 * Ajoute les champs de la relation
	 * ED140907
	 * */
	public static function get_related_fields($headerFields = false) {
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