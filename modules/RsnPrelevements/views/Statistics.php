<?php
/*+**********************************************************************************
 *
 * hérité par RsnPrelevements_GenererPrelVirements_View
 ************************************************************************************/

class RsnPrelevements_Statistics_View extends Vtiger_Index_View {

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		
		
		$viewer = $this->getViewer($request);

		$dateVir = $moduleModel->getNextDateToGenerateVirnts();
		$periodicites = $moduleModel->getPeriodicitesToGenerateVirnts($dateVir);

		$prelevementsActifs = $this->getPrelevementsActifs($moduleModel, $periodicites, $dateVir);
		
		$viewer->assign('DATE_PRELEVEMENTS', $dateVir);
		$viewer->assign('PERIODICITES', $periodicites);
		$viewer->assign('PRELEVEMENTS_ACTIFS', $prelevementsActifs);
		$viewer->assign('FIRST_RECUR_LIST', array('first', 'recur', 'total'));
		
		$viewer->assign('DUPLICATES_VIREMENTS', $this->getDoublonsInPrelevementsToGenerateVirnts($moduleModel, $dateVir));
		$viewer->assign('DUPLICATES_PRELVIREMENTS', $this->getDoublonsInExistingPrelVirements($moduleModel, $dateVir));
	
		echo $viewer->view('Statistics.tpl',$moduleName,true);
		
	}
	
	function getPrelevementsActifs($moduleModel, $periodicitesDateVir, $dateVir){
		$db = PearDatabase::getInstance();
		
		$params = array();
		
		$prelvtypes = $moduleModel->getTypesPrelvntsToGenerateVirnts();
		
		$query = 'SELECT vtiger_rsnprelevements.periodicite
			, IF(vtiger_rsnprelevements.dejapreleve IS NULL OR vtiger_rsnprelevements.dejapreleve = \'0000-00-00\', 0,
				IF(dejapreleve 
					BETWEEN DATE_SUB( ?, INTERVAL 7 DAY)
					AND DATE_ADD( ?, INTERVAL 7 DAY), 0, 1)
				) AS is_recur
			, COUNT(*) AS nombre
			, SUM(montant) AS montant
			, IFNULL(vtiger_periodicite.sortorderid, 999) AS sortorderid
			FROM vtiger_rsnprelevements
			JOIN vtiger_crmentity
				ON vtiger_rsnprelevements.rsnprelevementsid = vtiger_crmentity.crmid
			LEFT JOIN vtiger_periodicite
				ON vtiger_periodicite.periodicite = vtiger_rsnprelevements.periodicite
			WHERE vtiger_crmentity.deleted  = 0
			AND vtiger_rsnprelevements.etat = 0
			AND vtiger_rsnprelevements.prelvtype IN (' . generateQuestionMarks($prelvtypes) . ')
			GROUP BY is_recur, vtiger_rsnprelevements.periodicite
			ORDER BY sortorderid, vtiger_rsnprelevements.periodicite
			';
		$params[] = $dateVir->format('Y-m-d');
		$params[] = $dateVir->format('Y-m-d');
		$params = array_merge($params, $prelvtypes);
		$result = $db->pquery($query, $params);
		if(!$result){
			$db->echoError($query);
			return;
		}
		$rows = array();
		$totals = array('first'=>array('nombre'=>0, 'montant'=>0), 'recur'=>array('nombre'=>0, 'montant'=>0), );
		$periodicitesDateVirTotals = array('first'=>array('nombre'=>0, 'montant'=>0), 'recur'=>array('nombre'=>0, 'montant'=>0), );
		$nbRows = $db->num_rows($result);
		if(!$nbRows)
			return false;
		for($nRow = 0; $nRow < $nbRows; $nRow++){
			$row = $db->fetchByAssoc($result, $nRow);
			$periodicite = $row['periodicite'];
			$first_recur = $row['is_recur'] ? 'recur' : 'first';
			if(!$rows[$periodicite])
				$rows[$periodicite] = array('first'=>false, 'recur'=>false);
			$rows[$periodicite][$first_recur] = $row;
			$totals[$first_recur]['nombre'] += $row['nombre'];
			$totals[$first_recur]['montant'] += $row['montant'];
			if(in_array($periodicite, $periodicitesDateVir)){
				$periodicitesDateVirTotals[$first_recur]['nombre'] += $row['nombre'];
				$periodicitesDateVirTotals[$first_recur]['montant'] += $row['montant'];
			}
			
			//if(preg_match('/\d$/', $periodicite)){
			//	$periodicite = preg_replace('/\d+$/', '', $periodicite);
			//	if(!$rows[$periodicite])
			//		$rows[$periodicite] = array('first'=>0, 'recur'=>0, )
			//	$rows[$periodicite][$row['is_first'] ? 'first' : 'recur'] = $row;
			//}
		}
		$rows['* Totaux pour le '.$dateVir->format('d/m/Y')] = $periodicitesDateVirTotals;
		$rows['Totaux'] = $totals;
		
		foreach($rows as $rowKey=>&$row){
			$row['total'] = array('nombre'=>0, 'montant'=>0);
			foreach(array('first', 'recur') as $first_recur){
				$row['total']['nombre'] += $row[$first_recur]['nombre'];
				$row['total']['montant'] += $row[$first_recur]['montant'];
			}
		}
		
		return $rows;
	}
	
	function getDoublonsInPrelevementsToGenerateVirnts($moduleModel, $dateVir){
		$db = PearDatabase::getInstance();
		
		$params = array();
		$query = $moduleModel->getPrelevementsToGenerateVirntsQuery($dateVir, $params);
		$query = 'SELECT vtiger_crmentity_contact.crmid as contactid, vtiger_crmentity_contact.label as contactname, vtiger_contactdetails.contact_no
			, COUNT(*) AS nombre
			FROM vtiger_rsnprelevements
			JOIN vtiger_crmentity AS vtiger_crmentity_prelevements
				ON vtiger_crmentity_prelevements.crmid = vtiger_rsnprelevements.rsnprelevementsid
			JOIN vtiger_account
				ON vtiger_account.accountid = vtiger_rsnprelevements.accountid
			JOIN vtiger_crmentity AS vtiger_crmentity_account
				ON vtiger_crmentity_account.crmid = vtiger_account.accountid
			JOIN vtiger_contactdetails
				ON vtiger_account.accountid = vtiger_contactdetails.accountid
				AND vtiger_contactdetails.reference = 1
			JOIN vtiger_crmentity AS vtiger_crmentity_contact
				ON vtiger_crmentity_contact.crmid = vtiger_contactdetails.contactid
			WHERE vtiger_rsnprelevements.rsnprelevementsid IN (' . $query . ')
			AND vtiger_crmentity_prelevements.deleted = FALSE
			AND vtiger_crmentity_account.deleted = FALSE
			AND vtiger_crmentity_contact.deleted = FALSE
			GROUP BY vtiger_crmentity_contact.crmid, vtiger_crmentity_contact.label, vtiger_contactdetails.contact_no
			HAVING COUNT(*) > 1';
		$result = $db->pquery($query, $params);
		if(!$result){
			$db->echoError($query);
			return;
		}
		$rows = array();
		$nbRows = $db->num_rows($result);
		if(!$nbRows)
			return false;
		$contactModule = Vtiger_Module_Model::getInstance('Contacts');
		for($nRow = 0; $nRow < $nbRows; $nRow++){
			$row = $db->fetchByAssoc($result, $nRow);
			$row['url'] = $contactModule->getDetailViewUrl($row['contactid']) . '&mode=showRelatedList&relatedModule=RsnPrelevements&tab_label=RsnPrelevements';
			$rows[$row['contactid']] = $row;
		}
		return $rows;
	}
	
	function getDoublonsInExistingPrelVirements($moduleModel, $dateVir){
		$db = PearDatabase::getInstance();
		
		$params = array();
		$query = $moduleModel->getExistingPrelVirementsQuery($dateVir, false, $params);
		$query = 'SELECT vtiger_crmentity_contact.crmid as contactid, vtiger_crmentity_contact.label as contactname, vtiger_contactdetails.contact_no
			, COUNT(*) AS nombre
			FROM vtiger_rsnprelvirement
			JOIN vtiger_crmentity AS vtiger_crmentity_prelvir
				ON vtiger_crmentity_prelvir.crmid = vtiger_rsnprelvirement.rsnprelvirementid
			JOIN vtiger_rsnprelevements
				ON vtiger_rsnprelevements.rsnprelevementsid = vtiger_rsnprelvirement.rsnprelevementsid
			JOIN vtiger_crmentity AS vtiger_crmentity_prelevements
				ON vtiger_crmentity_prelevements.crmid = vtiger_rsnprelevements.rsnprelevementsid
			JOIN vtiger_account
				ON vtiger_account.accountid = vtiger_rsnprelevements.accountid
			JOIN vtiger_crmentity AS vtiger_crmentity_account
				ON vtiger_crmentity_account.crmid = vtiger_account.accountid
			JOIN vtiger_contactdetails
				ON vtiger_account.accountid = vtiger_contactdetails.accountid
				AND vtiger_contactdetails.reference = 1
			JOIN vtiger_crmentity AS vtiger_crmentity_contact
				ON vtiger_crmentity_contact.crmid = vtiger_contactdetails.contactid
			WHERE vtiger_rsnprelvirement.rsnprelvirementid IN (' . $query . ')
			AND vtiger_crmentity_prelvir.deleted = FALSE
			AND vtiger_crmentity_prelevements.deleted = FALSE
			AND vtiger_crmentity_account.deleted = FALSE
			AND vtiger_crmentity_contact.deleted = FALSE
			GROUP BY vtiger_crmentity_contact.crmid, vtiger_crmentity_contact.label, vtiger_contactdetails.contact_no
			HAVING COUNT(*) > 1';
		
		$result = $db->pquery($query, $params);
		if(!$result){
			$db->echoError($query);
			return;
		}
		$rows = array();
		$nbRows = $db->num_rows($result);
		if(!$nbRows)
			return false;
		$contactModule = Vtiger_Module_Model::getInstance('Contacts');
		for($nRow = 0; $nRow < $nbRows; $nRow++){
			$row = $db->fetchByAssoc($result, $nRow);
			$row['url'] = $contactModule->getDetailViewUrl($row['contactid']) . '&mode=showRelatedList&relatedModule=RsnPrelevements&tab_label=RsnPrelevements';
			$rows[$row['contactid']] = $row;
		}
		return $rows;
	}
}