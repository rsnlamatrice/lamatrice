<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

include_once('modules/RSN/models/DBCogilog.php');
 
class Invoice_GestionVSComptaCA_View extends Invoice_GestionVSCompta_View {


	public function process(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		
		$this->initFormData($request);
		$this->initComptesEntries($request);
		
		$viewer->assign('CURRENT_USER', $currentUserModel);

		$viewer->view('GestionVSCompta.tpl', $request->getModule());
	}
	
	
	public function initFormData(Vtiger_Request $request) {
		parent::initFormData($request);
		
		$viewer = $this->getViewer($request);
		
		$viewer->assign('FORM_VIEW', 'GestionVSComptaCA');
		$viewer->assign('ROWS_URL', 'index.php?module='.$request->get('module').'&view=GestionVSComptaCARows');
	}
	
	public function initComptesEntries(Vtiger_Request $request) {
		
		$viewer = $this->getViewer($request);
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		
		list($dateDebut, $dateFin) = $this->getDates($request);
		
		$ecartMontants = $request->get('ecartMontants');
		if($ecartMontants)
			$ecartMontants = (float)str_replace(',', '.', $ecartMontants);
		else
			$ecartMontants = 0.02;
		
		$laMatEntries = $this->getLaMatriceComptesEntries($dateDebut->format('Y-m-d'), $dateFin->format('Y-m-d'));
		$cogEntries = $this->getCogilogComptesEntries($dateDebut->format('Y-m-d'), $dateFin->format('Y-m-d'));
		
		$entries = array();
		foreach($laMatEntries as $date => $comptes){
			if(!$entries[$date])
				$entries[$date] = array();
			foreach($comptes as $compte => $montant)
				if($montant != 0)
					$entries[$date][$compte] = array('LAM' => $montant);
		}
		foreach($cogEntries as $date => $comptes){
			if(!$entries[$date])
				$entries[$date] = array();
			foreach($comptes as $compte => $montant){
				if(!$entries[$date][$compte]){
					if($montant != 0)
						$entries[$date][$compte] = array('COG' => $montant);
				}
				//elseif(abs($entries[$date][$compte]['LAM'] - $montant) < $ecartMontants)
				//	unset($entries[$date][$compte]);
				else
					$entries[$date][$compte]['COG'] = $montant;
			}
			if(count($entries[$date]) === 0)
				unset($entries[$date]);
		}
		$allComptes = array();
		foreach($entries as $date => $comptes){
			foreach($comptes as $compte => $data){
				if(count($data) === 0)
					unset($entries[$date][$compte]);
				else
					$allComptes[$compte] = true;
			}
			if(count($entries[$date]) === 0)
				unset($entries[$date]);
		}
		$totauxGlobaux = array('TOTAUX' => array('LAM'=>0.0,'COG'=>0.0,));
		foreach($entries as $date => $comptes){
			$totaux = array('LAM'=>0.0,'COG'=>0.0,);
			foreach($comptes as $compte => $data){
				$totaux['LAM'] += $data['LAM'];
				$totaux['COG'] += $data['COG'];
				if(!$totauxGlobaux[$compte])
					$totauxGlobaux[$compte] = array('LAM'=>0.0,'COG'=>0.0,);
				$totauxGlobaux[$compte]['LAM'] += $data['LAM'];
				$totauxGlobaux[$compte]['COG'] += $data['COG'];
			}
			$totauxGlobaux['TOTAUX']['LAM'] += $totaux['LAM'];
			$totauxGlobaux['TOTAUX']['COG'] += $totaux['COG'];
			
			$entries[$date] = array('TOTAUX' => $totaux) + $entries[$date];
		}
		
		$label = 'Totaux du mois de '. $dateDebut->format('M Y');
		$entries = array_merge(array($label => $totauxGlobaux), $entries);
		
		$viewer->assign('COMPTES', $allComptes);
		$viewer->assign('ENTRIES', $entries);
		$viewer->assign('ALL_SOURCES', array('COG' => 'Compta', 'LAM' => 'Gestion'));
	}
	
	function getComptesString(){
//511101	Encaissements Paybox Boutique en ligne
//511102	CB Achats en dŽbit diffŽrŽ
//511103	Encaissements espces gestion
//511104	Encaissements Paybox Dons ŽtalŽs
//511105	Encaissements des dons NEF
//511106	Virements reus
//511200	CCP - Chques ˆ encaisser
//511300	Encaissements  Paypal Dons et dŽcaissements
//511400	Encaissements Paybox Dons
//511600	RŽaffectation Bilan dons
//511700	RŽaffectation Bilan encaissement dons Nef
//511BOU	Encaissements boutique en ligne
//511CB	CB en dŽbit diffŽrŽ
//511ETAL	Encaissement des dons ŽtalŽs
//511NEF	Encaissements des dons Nef
//511VIR	Virements reus (dons)
		//NOT USED
		return "'411000', '411DEP', '511101', '511104', '511105', '511300', '511103', '511102', '511106', '511200', '511400', '511BOU', '511CB', '511ETAL', '511NEF', '511VIR'";
	}
	
	public function getLaMatriceComptesEntries($dateDebut, $dateFin){
		global $adb;
		$query = "SELECT `vtiger_invoice`.`invoicedate` AS `Date`
		, IF(`vtiger_account`.account_type = 'Depot-vente', '411DEP', IFNULL(vtiger_receivedmoderegl.comptevente, vtiger_invoicecf.receivedmoderegl)) AS `Compte`
		, SUM( ROUND( `vtiger_invoice`.`total`, 2 ) ) AS `Montant`
		FROM `vtiger_invoice`
		INNER JOIN `vtiger_crmentity` AS `vtiger_crmentity_invoice`
			ON `vtiger_invoice`.`invoiceid` = `vtiger_crmentity_invoice`.`crmid`
		INNER JOIN `vtiger_invoicecf`
			ON `vtiger_invoicecf`.`invoiceid` = `vtiger_crmentity_invoice`.`crmid`
		INNER JOIN `vtiger_account`
			ON `vtiger_account`.`accountid` = `vtiger_invoice`.`accountid`
		LEFT JOIN `vtiger_notescf`
			ON `vtiger_notescf`.`notesid` = `vtiger_invoicecf`.`notesid`
		LEFT JOIN `vtiger_campaignscf`
			ON `vtiger_campaignscf`.`campaignid` = `vtiger_invoicecf`.`campaign_no`
		LEFT JOIN `vtiger_receivedmoderegl`
			ON `vtiger_receivedmoderegl`.`receivedmoderegl` = `vtiger_invoicecf`.`receivedmoderegl`
		WHERE `vtiger_crmentity_invoice`.`deleted` = FALSE
		AND vtiger_invoice.invoicestatus != 'Cancelled'
		
		AND `vtiger_invoice`.`invoicedate` >= ?
		AND `vtiger_invoice`.`invoicedate` < ?
		
		GROUP BY `vtiger_invoice`.`invoicedate`, `Compte`
		
		ORDER BY `Date`, `Compte`
		";
		
		$params = array($dateDebut, $dateFin);
		
		$result = $adb->pquery($query, $params);
		
		if(!$result){
			$adb->echoError('getLaMatriceComptesEntries');
			return;
		}
		$nRow = 0;
		$entries = array();
		while($row = $adb->fetch_row($result, $nRow++)){
			if(!$entries[$row['date']])
				$entries[$row['date']] = array();
			$entries[$row['date']][$row['compte']] = $row['montant'];
		}
		return $entries;
	}
	
	/* A noter 
		AND "ligne"."id_cjourn" IN (18) VT */
	public function getCogilogComptesEntries($dateDebut, $dateFin){
		$whereComptes = '( "ligne"."compte" LIKE \'411%\' OR "ligne"."compte" LIKE \'511%\' )';
		
		$query = '
		SELECT "ligne"."ladate" AS "date"
		, "ligne"."compte" AS "compte"
		, SUM("ligne"."debit" - "ligne"."credit") AS "montant"
		FROM "cligne00002" "ligne"
		INNER JOIN "ccompt00002" "compte"
			ON "ligne"."compte" = "compte"."compte"
		WHERE '.$whereComptes.'
		AND "compte"."desactive" = FALSE
		AND "compte"."nonsaisie" = FALSE
		AND "ligne"."id_cjourn" IN (18)
		
		AND "ligne"."ladate" >= \''.$dateDebut.'\'
		AND "ligne"."ladate" < \''.$dateFin.'\'
		
		GROUP BY "ligne"."ladate", "ligne"."compte"
		
		ORDER BY "ligne"."ladate", "ligne"."compte"
		';
		
		//echo  "<pre>$query</pre>";
		
		$db = new RSN_DBCogilog_Module();
		$rows = $db->getDBRows($query);
		
		if($rows === false){
			echo "<pre>$query</pre>";
			echo('<code> ERREUR dans getCogilogComptesEntries</code>');
			return;
		}
		
		$entries = array();
		foreach($rows as $row){
			if(!$entries[$row['date']])
				$entries[$row['date']] = array();
			$entries[$row['date']][$row['compte']] = $row['montant'];
		}
		return $entries;
	}
	
}