<?php
/*+**********************************************************************************
 * ED151008
 ************************************************************************************/

class Contacts_FindDuplicate_Model extends Vtiger_FindDuplicate_Model {
	
	
	
	/* Fields to find duplicates
	 * @returns $tableColumns
	 * 	May be an array of column names or an array of array of column names for multiple search
	 */
	public function getFindDuplicateFields(){
		//2 searches
		return array(  array('email')
				, array('lastname', 'firstname', 'mailingzip'));
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
		
		//Exclu les supprimés
		$queryGenerator->startGroup('');
		$queryGenerator->addCondition('lastname'
					, "[SUPPRIM"
					, 'k');
		$queryGenerator->endGroup();
		
		
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
	/**
	 * Retourne un filtre sur la requête de recherche de doublon.
	 * Cette requête est utilisée pour effectuer la recherche de doublons.
	 */
	function getScheduledSearchJoinQuery($moduleName, $tableColumns){
		$focus = CRMEntity::getInstance($moduleName);
		return ' LEFT JOIN vtiger_contactscontrel
			ON (crm1.'.$focus->table_index . ' = vtiger_contactscontrel.contactid
				AND crm2.'.$focus->table_index . ' = vtiger_contactscontrel.relcontid
			) OR (
				crm2.'.$focus->table_index . ' = vtiger_contactscontrel.contactid
				AND crm1.'.$focus->table_index . ' = vtiger_contactscontrel.relcontid
			)';
	}
	
	/**
	 * Retourne un filtre sur la requête de recherche de doublon.
	 * Cette requête est utilisée pour effectuer la recherche de doublons.
	 */
	function getScheduledSearchWhereQuery($moduleName, $tableColumns){
		return 'vtiger_contactscontrel.contactid IS NULL';
	}



	/* nettoie la table des duplicates
	 */
	public function runCleanDuplicateTable(){
		$duplicateTableName = $this->getDuplicateEntitiesTable();
		$query = "DELETE $duplicateTableName
			FROM $duplicateTableName
			JOIN vtiger_contactdetails c1
				ON $duplicateTableName.crmid1 = c1.contactid
			JOIN vtiger_contactdetails c2
				ON $duplicateTableName.crmid2 = c2.contactid
			JOIN vtiger_crmentity crm1
				ON $duplicateTableName.crmid1 = crm1.crmid
			JOIN vtiger_crmentity crm2
				ON $duplicateTableName.crmid1 = crm2.crmid
			LEFT JOIN vtiger_contactscontrel
			ON (crm1.crmid = vtiger_contactscontrel.contactid
				AND crm2.crmid = vtiger_contactscontrel.relcontid
			) OR (
				crm2.crmid = vtiger_contactscontrel.contactid
				AND crm1.crmid = vtiger_contactscontrel.relcontid
			)
			WHERE c1.lastname LIKE '%[SUPPRIM%%'
			OR c2.lastname LIKE '%[SUPPRIM%%'
			OR crm1.deleted = 1
			OR crm2.deleted = 1
			OR vtiger_contactscontrel.contactid IS NOT NULL
		";
		$db = PearDatabase::getInstance();
		$result = $db->query($query);
		if(!$result){
			$db->echoError();
		}
	}	
}