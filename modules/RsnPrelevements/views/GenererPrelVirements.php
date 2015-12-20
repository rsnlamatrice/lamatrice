<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

 /*
UPDATE `vtiger_rsnprelevements` 
JOIN (
	SELECT `rsnprelevementsid`, MIN(dateexport) AS dateexport
	FROM `vtiger_rsnprelvirement`
	GROUP BY `rsnprelevementsid`
	) `vtiger_rsnprelvirement`
	ON `vtiger_rsnprelevements`.`rsnprelevementsid` = `vtiger_rsnprelvirement`.`rsnprelevementsid`
SET dejapreleve = dateexport
WHERE `etat` = 0
AND (vtiger_rsnprelevements.dejapreleve IS NULL OR vtiger_rsnprelevements.dejapreleve = '0000-00-00')
  
  
  SELECT `vtiger_rsnprelevements`.*
FROM `vtiger_rsnprelevements` 
LEFT JOIN `vtiger_rsnprelvirement` 
	ON `vtiger_rsnprelevements`.`rsnprelevementsid` = `vtiger_rsnprelvirement`.`rsnprelevementsid`
WHERE `etat` = 0
AND (vtiger_rsnprelevements.dejapreleve IS NULL OR vtiger_rsnprelevements.dejapreleve = '0000-00-00')
AND `vtiger_rsnprelvirement`.`rsnprelevementsid` IS NULL
*/
class RsnPrelevements_GenererPrelVirements_View extends Vtiger_Index_View {

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		
		$dateVir= $moduleModel->getNextDateToGenerateVirnts($request->get('date_virements'));
		
		$msgVirements = $request->get('msg_virements');
		
		$viewer = $this->getViewer($request);
		
		$viewer->assign('DATE_VIREMENTS', $dateVir);
		$viewer->assign('MSG_VIREMENTS', $msgVirements);
		$viewer->assign('EXISTING_PRELVIREMENTS', $this->getExistingPrelVirements($moduleModel, $dateVir));
		$viewer->assign('AVAILABLE_VIREMENTS', $this->getPrelevementsSources($request, $moduleModel, $dateVir));
		$viewer->assign('DUPLICATES_VIREMENTS', $this->getDoublonsInPrelevementsToGenerateVirnts($moduleModel, $dateVir));
		$viewer->assign('DUPLICATES_PRELVIREMENTS', $this->getDoublonsInExistingPrelVirements($moduleModel, $dateVir));
		$viewer->assign('MODULE_NAME', $moduleName);
		
		$loadUrl = $moduleModel->getDefaultUrl();
		$viewer->assign('CANCEL_URL', $loadUrl);
		
		$loadUrl = $moduleModel->getGenererPrelVirementsUrl();
		$viewer->assign('RELOAD_URL', $loadUrl);
		
		$loadUrl = $moduleModel->getDownloadPrelVirementsUrl($dateVir);
		$viewer->assign('DOWNLOAD_URL', $loadUrl);
		
		$loadUrl = $moduleModel->getPrintRemerciementsUrl($dateVir);
		$viewer->assign('PRINT_FIRSTS_URL', $loadUrl);
		
		$viewer->view('GenererPrelVirements.tpl', $moduleName);
	}
	
	function getExistingPrelVirements($moduleModel, $dateVir){
		$db = PearDatabase::getInstance();
		
		$params = array();
		$query = $moduleModel->getExistingPrelVirementsQuery($dateVir, false, $params);
		$query = 'SELECT IFNULL(vtiger_rsnprelvirement.is_first, 0) AS is_first
			, COUNT(*) AS nombre
			, ROUND(SUM(vtiger_rsnprelvirement.montant),2) AS `montant`
			FROM vtiger_rsnprelvirement
			WHERE vtiger_rsnprelvirement.rsnprelvirementid IN (' . $query . ')
			GROUP BY IFNULL(vtiger_rsnprelvirement.is_first, 0)';
		
		$result = $db->pquery($query, $params);
		if(!$result){
			$db->echoError($query);
			return;
		}
		$rows = array();
		$nbRows = $db->num_rows($result);
		if(!$nbRows)
			return false;
		for($nRow = 0; $nRow < $nbRows; $nRow++){
			$row = $db->fetchByAssoc($result, $nRow);
			$rows[$row['is_first'] ? 'FIRST' : 'RECUR'] = $row;
		}
		return $rows;
	}
	
	function getPrelevementsSources(Vtiger_Request $request, $moduleModel, $dateVir){
		$viewer = $this->getViewer($request);
		
		$db = PearDatabase::getInstance();
		
		$prelvtypes = $moduleModel->getTypesPrelvntsToGenerateVirnts();
		
		$periodicites = $moduleModel->getPeriodicitesToGenerateVirnts($dateVir);
		
		$viewer->assign('TYPES_PRLVNTS', $prelvtypes);
		$viewer->assign('PERIODICITES', $periodicites);
		
		$params = array();
		$query = $moduleModel->getPrelevementsToGenerateVirntsQuery($dateVir, $params);
		
		$query = 'SELECT IF(vtiger_rsnprelevements.dejapreleve IS NULL OR vtiger_rsnprelevements.dejapreleve = \'0000-00-00\', 0, 1) AS dejapreleve
			, COUNT(*) AS nombre
			, ROUND(SUM(montant),2) AS `montant`
			FROM vtiger_rsnprelevements
			WHERE vtiger_rsnprelevements.rsnprelevementsid IN (' . $query . ')
			GROUP BY IF(vtiger_rsnprelevements.dejapreleve IS NULL OR vtiger_rsnprelevements.dejapreleve = \'0000-00-00\', 0, 1)';
		
		$result = $db->pquery($query, $params);
		if(!$result){
			$db->echoError($query);
			return;
		}
		$rows = array();
		$nbRows = $db->num_rows($result);
		if(!$nbRows)
			return false;
		for($nRow = 0; $nRow < $nbRows; $nRow++){
			$row = $db->fetchByAssoc($result, $nRow);
			$rows[$row['dejapreleve'] ? 'RECUR' : 'FIRST'] = $row;
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
}