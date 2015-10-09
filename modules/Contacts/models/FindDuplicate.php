<?php
/*+**********************************************************************************
 * ED151008
 ************************************************************************************/

class Contacts_FindDuplicate_Model extends Vtiger_FindDuplicate_Model {
	
	
	/* Fields to find duplicates
	 * @returns $tableColumns
	 */
	public function getFindDuplicateFields(){
		return array('email');
	}
	
	/**
	 * Retourne une simple requête sur tous les enregistrements de la table.
	 * Cette requête est utilisée pour effectuer la recherche de doublons.
	 */
	function getScheduledSearchBasicQuery($moduleName, $tableColumns){
		$moduleModel = $this->getModule();
		$moduleName = $moduleModel->getName();
		$focus = CRMEntity::getInstance($moduleName);
		$currentUser = Users_Record_Model::getCurrentUserModel();
		
		$queryGenerator = new QueryGenerator($moduleName, $currentUser);
		$queryGenerator->initForAllCustomView();
		
		$moduleFields = $moduleModel->getFields();
		
		$fields = $tableColumns;
		$fields[] = 'id';
		$queryGenerator->setFields($fields);
		
		$query = $queryGenerator->getQuery();
		
		//Pas tous vide
		$query .= " AND NOT (";
		foreach($tableColumns as $n => $tableColumn){
			$columnTable = $moduleFields[$tableColumn]->table;
			if($n) $query .= " AND ";
			$query .= "IFNULL($columnTable.$tableColumn,'') = ''";
		}
		$query .= ")";
		
		if(in_array('email', $tableColumns)){
			/*TODO NULL pour les autres colonnes de $tableColumns */
			$query .= '
			UNION
			SELECT email, contactid
			FROM vtiger_contactemails
			JOIN vtiger_crmentity
				ON vtiger_contactemails.contactemailsid = vtiger_crmentity.crmid
			WHERE vtiger_crmentity.deleted = 0
			';
		}
		
		return $query;
	}
}