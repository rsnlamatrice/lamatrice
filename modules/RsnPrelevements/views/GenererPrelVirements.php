<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

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
		$query = 'SELECT IFNULL(vtiger_rsnprelvirement.is_first, 0) AS is_first, COUNT(*) AS nombre, SUM(vtiger_rsnprelvirement.montant) AS `montant`
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
		
		$query = 'SELECT IF(vtiger_rsnprelevements.dejapreleve IS NULL OR vtiger_rsnprelevements.dejapreleve = \'0000-00-00\', 0, 1) AS dejapreleve, COUNT(*) AS nombre, SUM(montant) AS `montant`
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
}