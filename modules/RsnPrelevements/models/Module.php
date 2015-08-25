<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ************************************************************************************/

class RsnPrelevements_Module_Model extends Vtiger_Module_Model {
	/**
	 * Function to check whether the entity has an quick create menu
	 * @return <Boolean> true/false
	 * ED141024
	 */
	public function isQuickCreateMenuVisible() {
		return false ;
	}

	function getGenererPrelVirementsUrl($dateVir = false){
		if($dateVir)
			$dateVir = $this->getNextDateToGenerateVirnts($dateVir);
		return 'index.php?module='.$this->getName() .'&view=GenererPrelVirements'
			. ($dateVir ? '&date_virements=' . $dateVir->format('d-m-Y') : '');
	}

	function getDownloadPrelVirementsUrl($dateVir = false){
		$dateVir = $this->getNextDateToGenerateVirnts($dateVir);
		return 'index.php?module='.$this->getName() .'&action=Download&date_virements=' . $dateVir->format('d-m-Y');
	}

	function getPrintRemerciementsUrl($dateVir = false){
		$dateVir = $this->getNextDateToGenerateVirnts($dateVir);
		return 'index.php?module='.$this->getName() .'&view=PrintRemerciements&date_virements=' . $dateVir->format('d-m-Y');
	}
	
	/**
	 * Function to get relation query for particular module with function name
	 * @param <record> $recordId
	 * @param <String> $functionName
	 * @param Vtiger_Module_Model $relatedModule
	 * @return <String>
	 */
	public function getRelationQuery($recordId, $functionName, $relatedModule) {
		if ($functionName === 'get_rsnprelvirements') {

			$userNameSql = getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');

			/*$query = "SELECT CASE WHEN (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END AS user_name,
						vtiger_crmentity.*, vtiger_activity.activitytype, vtiger_activity.subject, vtiger_activity.date_start, vtiger_activity.time_start,
						vtiger_activity.recurringtype, vtiger_activity.due_date, vtiger_activity.time_end, vtiger_seactivityrel.crmid AS parent_id,
						CASE WHEN (vtiger_activity.activitytype = 'Task') THEN (vtiger_activity.status) ELSE (vtiger_activity.eventstatus) END AS status
						FROM vtiger_activity
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_activity.activityid
						LEFT JOIN vtiger_seactivityrel ON vtiger_seactivityrel.activityid = vtiger_activity.activityid
						LEFT JOIN vtiger_cntactivityrel ON vtiger_cntactivityrel.activityid = vtiger_activity.activityid
						LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
						LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
						WHERE vtiger_crmentity.deleted = 0 AND vtiger_activity.activitytype <> 'Emails'
							AND (vtiger_seactivityrel.crmid = ".$recordId;*/
			$query = 'SELECT CASE WHEN (vtiger_users.user_name not like "") THEN '.$userNameSql.' ELSE vtiger_groups.groupname END AS user_name
				, vtiger_crmentity.crmid, vtiger_crmentity.createdtime, vtiger_crmentity.modifiedtime, vtiger_crmentity.label
				, vtiger_rsnprelvirement.*
				, vtiger_crmentity.smownerid AS assigned_user_id
				FROM `vtiger_rsnprelvirement`
				INNER JOIN `vtiger_crmentity`
					ON vtiger_crmentity.crmid = vtiger_rsnprelvirement.rsnprelvirementid
				LEFT JOIN vtiger_users
					ON vtiger_users.id = vtiger_crmentity.smownerid
				LEFT JOIN vtiger_groups
					ON vtiger_groups.groupid = vtiger_crmentity.smownerid
				WHERE vtiger_crmentity.deleted = 0
				AND vtiger_rsnprelvirement.rsnprelevementsid = '.$recordId;
			$relatedModuleName = $relatedModule->getName();
			$query .= $this->getSpecificRelationQuery($relatedModuleName);
			$nonAdminQuery = $this->getNonAdminAccessControlQueryForRelation($relatedModuleName);
			if ($nonAdminQuery) {
				$query = appendFromClauseToQuery($query, $nonAdminQuery);
			}
			//echo('<pre>'.$query . '</pre>');
			/*$db = PearDatabase::getInstance();
			$db->setDebug(true);*/
			
		} else {
			$query = parent::getRelationQuery($recordId, $functionName, $relatedModule);
			//echo('<pre>'.$query . '</pre>');
		}

		return $query;
	}
	
	
	/*
	 * Date du prochain prélèvement
	 */
	function getNextDateToGenerateVirnts($dateVir = false){
		if(!$dateVir){
			$dateVir = new DateTime('today');
			$day = (int)$dateVir->format('d') ;
			if($day > 15){
				$dateVir->modify( '+1 month' );
			}
			$dateVir->modify( 'first day of this month');
			$dateVir->modify( '+5 day' );//au 6 du mois
		}
		elseif(is_string($dateVir))
			$dateVir = new DateTime($dateVir);//TODO US format
			
		return $dateVir;
	}
	
	/*
	 * Types de prélèvements concernés pour la génération des prochains virements
	 */
	function getTypesPrelvntsToGenerateVirnts(){
		return array('Virement périodique');
	}
	
	/*
	 * Périodicités concernées pour la génération des prochains virements
	 */
	function getPeriodicitesToGenerateVirnts($dateVir){
		$mois = (int)$dateVir->format('m');
		//Périodicités acceptables
		$periodicites = array(
				      'Mensuel',
				      'Bimestriel ' . ($mois % 2 + 1),
				      'Trimestriel ' . floor(($mois - 1) / 3 + 1),
				      'Semestriel ' . floor(($mois - 1) / 6 + 1),
				      'Annuel ' . $mois,
				);
		return $periodicites;
	}
	
	/*
	 * Requête retournant les prélèvements concernés pour la génération des prochains virements
	 */
	function getPrelevementsToGenerateVirntsQuery($dateVir = false, &$params = false){
		$dateVir = $this->getNextDateToGenerateVirnts($dateVir);
		
		$periodicites = $this->getPeriodicitesToGenerateVirnts($dateVir);
		$prelvtypes = $this->getTypesPrelvntsToGenerateVirnts();
		
		if(!$params)
			$params = array();
		$params = array_merge($params, $prelvtypes);
		$params = array_merge($params, $periodicites);
		$query = $this->getPrelevementsWithExistingVirntsQuery($dateVir, $params);
		
		$query = 'SELECT vtiger_crmentity.crmid
			FROM vtiger_rsnprelevements
			JOIN vtiger_crmentity
				ON vtiger_rsnprelevements.rsnprelevementsid = vtiger_crmentity.crmid
			WHERE vtiger_crmentity.deleted  = 0
			AND vtiger_rsnprelevements.etat = 0
			AND vtiger_rsnprelevements.prelvtype IN (' . generateQuestionMarks($prelvtypes) . ')
			AND vtiger_rsnprelevements.periodicite IN (' . generateQuestionMarks($periodicites) . ')
			AND vtiger_rsnprelevements.rsnprelevementsid NOT IN (' . $query . ')';
		return $query;
	}

	
	/*
	 * Prélèvements concernés pour la génération des prochains virements
	 */
	function getPrelevementsToGenerateVirnts($dateVir = false){
		$db = PearDatabase::getInstance();
		$params = array();
		$query = $this->getPrelevementsToGenerateVirntsQuery($dateVir, $params);
		$result = $db->pquery($query, $params);
		if(!$result){
			$db->echoError('getPrelevementsToGenerateVirnts');
			var_dump($query, 'Paramètres : ', $params);
			return;
		}
		$rows = array();
		$nbRows = $db->num_rows($result);
		if(!$nbRows)
			return false;
		
		$moduleName = $this->getName();
		for($nRow = 0; $nRow < $nbRows; $nRow++){
			$crmid = $db->query_result($result, $nRow, 0);
			$rows[$crmid] = Vtiger_Record_Model::getInstanceById($crmid, $moduleName);
		}
		return $rows;
	}

	
	/*
	 * Requête fournissant les prélèvements ayant déjà générés les prochains virements
	 */
	function getPrelevementsWithExistingVirntsQuery($dateVir, &$params = false){
		
		$prelvtypes = $this->getTypesPrelvntsToGenerateVirnts();
		$query = 'SELECT vtiger_rsnprelevements.rsnprelevementsid
			FROM vtiger_rsnprelvirement
			JOIN vtiger_crmentity
				ON vtiger_rsnprelvirement.rsnprelvirementid = vtiger_crmentity.crmid
			JOIN vtiger_rsnprelevements
				ON vtiger_rsnprelevements.rsnprelevementsid = vtiger_rsnprelvirement.rsnprelevementsid
			JOIN vtiger_crmentity rsnprelevements_crmentity
				ON vtiger_rsnprelevements.rsnprelevementsid = rsnprelevements_crmentity.crmid
			WHERE vtiger_crmentity.deleted  = 0
			AND rsnprelevements_crmentity.deleted  = 0
			AND vtiger_rsnprelevements.prelvtype IN (' . generateQuestionMarks($prelvtypes) . ')
			AND vtiger_rsnprelvirement.dateexport
				BETWEEN DATE_SUB(?, INTERVAL 7 DAY)
				AND DATE_ADD(?, INTERVAL 7 DAY)
			';
		if(!$params)
			$params = array();
		$params = array_merge($params, $prelvtypes);
		$params[] = $dateVir->format('Y-m-d');
		$params[] = $dateVir->format('Y-m-d');
		//echo "<pre>$query</pre>";	
		return $query;
	}
	
	function getExistingPrelVirementsQuery($dateVir, $recur_first = false, &$params = false){
		$prelvtypes = $this->getTypesPrelvntsToGenerateVirnts();
		$query = 'SELECT vtiger_crmentity.crmid
			FROM vtiger_rsnprelvirement
			JOIN vtiger_crmentity
				ON vtiger_rsnprelvirement.rsnprelvirementid = vtiger_crmentity.crmid
			JOIN vtiger_rsnprelevements
				ON vtiger_rsnprelevements.rsnprelevementsid = vtiger_rsnprelvirement.rsnprelevementsid
			JOIN vtiger_crmentity rsnprelevements_crmentity
				ON vtiger_rsnprelevements.rsnprelevementsid = rsnprelevements_crmentity.crmid
			WHERE vtiger_crmentity.deleted  = 0
			AND rsnprelevements_crmentity.deleted  = 0
			AND vtiger_rsnprelevements.prelvtype IN (' . generateQuestionMarks($prelvtypes) . ')
			AND vtiger_rsnprelvirement.dateexport 
				BETWEEN DATE_SUB( ?, INTERVAL 7 DAY)
				AND DATE_ADD( ?, INTERVAL 7 DAY)
		';
		if($recur_first)
			$query .= ' AND IFNULL(vtiger_rsnprelvirement.is_first, 0) = ?';
			
		if(!$params)
			$params = array();
		$params = array_merge($params, $prelvtypes);
		$params[] = $dateVir->format('Y-m-d');
		$params[] = $dateVir->format('Y-m-d');
		if($recur_first)
			$params[] = $recur_first == 'FIRST' ? 1 : 0;
		return $query;
	}
	
	/**
	 * Retourne les ordres de virement générés pour la date donnée
	 *
	 */
	function getExistingPrelVirements($dateVir, $recur_first = false){
		$db = PearDatabase::getInstance();
		$params = array();
		$query = $this->getExistingPrelVirementsQuery($dateVir, $recur_first, $params);
		
		$result = $db->pquery($query, $params);
		if(!$result){
			$db->echoError($query);
			return;
		}
		$rows = array();
		$nbRows = $db->num_rows($result);
		$moduleName = 'RsnPrelVirement';
		for($nRow = 0; $nRow < $nbRows; $nRow++){
			$crmid = $db->query_result($result, $nRow, 0);
			$rows[$crmid] = Vtiger_Record_Model::getInstanceById($crmid, $moduleName);
		}
		//echo "<pre>$query</pre>";
		//var_dump($query, $params, $rows);
		return $rows;
	}

}
