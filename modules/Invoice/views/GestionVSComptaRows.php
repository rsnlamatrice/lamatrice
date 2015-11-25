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
 
class Invoice_GestionVSComptaRows_View extends Invoice_GestionVSCompta_View {


	public function process(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		
		$this->initFormData($request);
		$this->initRowsEntries($request);
		
		$viewer->assign('CURRENT_USER', $currentUserModel);

		$viewer->view('GestionVSComptaRows.tpl', $request->getModule());
	}
	
	
	public function initFormData(Vtiger_Request $request) {
		
		$viewer = $this->getViewer($request);
		
		$dates = array();
		for($y = date('Y'); $y >= date('Y') - 2; $y--){
			for($m = $y == date('Y') ? date('n') : 12; $m >= 1; $m--){
				$dates[$y . '-' . $m . '-' . '01'] = date('M Y', strtotime($y . '-' . $m . '-' . '01'));
			}
		}
		
		$viewer->assign('DATES', $dates);
		$viewer->assign('FORM_URL', 'index.php?module='.$request->get('module').'&view=GestionVSCompta');
	}
	
	public function initRowsEntries(Vtiger_Request $request) {
		
		$viewer = $this->getViewer($request);
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		
		$dateDebut = $request->get('date');
		if(!$dateDebut)
			$dateDebut = date('Y-m-01');
		$dateDebut = new DateTime($dateDebut);
		$dateFin = clone $dateDebut;
		$dateFin->modify('+1 day');
		
		$compte = $request->get('compte');
		
		$ecartMontants = $request->get('ecartMontants');
		if($ecartMontants)
			$ecartMontants = (float)str_replace(',', '.', $ecartMontants);
		else
			$ecartMontants = 0.02;
		
		$laMatEntries = $this->getLaMatriceRowsEntries($dateDebut->format('Y-m-d'), $dateFin->format('Y-m-d'), $compte);
		$cogEntries = $this->getCogilogRowsEntries($dateDebut->format('Y-m-d'), $dateFin->format('Y-m-d'), $compte);
		
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
				elseif(abs($entries[$date][$compte]['LAM'] - $montant) < $ecartMontants)
					unset($entries[$date][$compte]);
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
		foreach($entries as $date => $comptes){
			$totaux = array('LAM'=>0.0,'COG'=>0.0,);
			foreach($comptes as $compte => $data){
				$totaux['LAM'] += $data['LAM'];
				$totaux['COG'] += $data['COG'];
			}
			$entries[$date] = array('TOTAUX' => $totaux) + $entries[$date];
		}
		
		
		$viewer->assign('COMPTES', $allComptes);
		$viewer->assign('ENTRIES', $entries);
	}
	
	
	public function getLaMatriceRowsEntries($dateDebut, $dateFin, $compte){
		global $adb;
		if($compte)
			$compte = "'$compte'";
		else
			$compte = $this->getComptesString();
		$query = "SELECT `vtiger_invoice`.`invoicedate` AS `Date`
		, `vtiger_invoice`.`invoice_no` AS `NumFacture`
		, `vtiger_invoice`.`subject` AS `NomFacture`
		, IFNULL( `vtiger_products`.`glacct`, `vtiger_servicecf`.`glacct` ) AS `Compte`
		, ROUND( `vtiger_inventoryproductrel`.`quantity` * `vtiger_inventoryproductrel`.`listprice` * ( 1 - `vtiger_inventoryproductrel`.`discount_percent` / 100 ) - `vtiger_inventoryproductrel`.`discount_amount`, 2 ) AS `Montant`
		FROM `vtiger_invoice`
		INNER JOIN `vtiger_crmentity` AS `vtiger_crmentity_invoice`
			ON `vtiger_invoice`.`invoiceid` = `vtiger_crmentity_invoice`.`crmid`
		INNER JOIN `vtiger_invoicecf`
			ON `vtiger_invoicecf`.`invoiceid` = `vtiger_crmentity_invoice`.`crmid`
		INNER JOIN `vtiger_inventoryproductrel`
			ON `vtiger_inventoryproductrel`.`id` = `vtiger_crmentity_invoice`.`crmid`
		LEFT JOIN `vtiger_products`
			ON `vtiger_inventoryproductrel`.`productid` = `vtiger_products`.`productid`
		LEFT JOIN `vtiger_servicecf`
			ON `vtiger_inventoryproductrel`.`productid` = `vtiger_servicecf`.`serviceid`
		LEFT JOIN `vtiger_notescf`
			ON `vtiger_notescf`.`notesid` = `vtiger_invoicecf`.`notesid`
		LEFT JOIN `vtiger_campaignscf`
			ON `vtiger_campaignscf`.`campaignid` = `vtiger_invoicecf`.`campaign_no`
		WHERE `vtiger_crmentity_invoice`.`deleted` = FALSE
		AND `vtiger_invoice`.`invoicedate` >= CAST( CONCAT( YEAR( CURRENT_DATE ) - IF( MONTH( CURRENT_DATE ) <= 9, 2, 1 ), '-09-01' ) AS DATE )
		AND IFNULL( `vtiger_products`.`glacct`, `vtiger_servicecf`.`glacct` )
		IN ( ".$compte." )
		
		AND `vtiger_invoice`.`invoicedate` >= ?
		AND `vtiger_invoice`.`invoicedate` < ?
		AND `vtiger_invoice`.`invoicedate` < ?
		
		ORDER BY `Date`, `Montant`
		";
		
		$params = array($dateDebut, $dateFin);
		
		$result = $adb->pquery($query, $params);
		
		if(!$result){
			$adb->echoError('getLaMatriceEntries');
			return;
		}
		$nRow = 0;
		$entries = array();
		while($row = $adb->fetch_row($result, $nRow++)){
			if(!$entries[$row['date']])
				$entries[$row['date']] = array();
			$entries[$row['date']][] = $row;
		}
		return $entries;
	}
	public function getCogilogRowsEntries($dateDebut, $dateFin, $compte){
		if($compte)
			$compte = "'$compte'";
		else
			$compte = $this->getComptesString();
		
		$query = '
		SELECT "ligne"."ladate" AS "date"
		, "ligne"."piece" AS "numfacture"
		, "ligne"."libelle" AS "nomfacture"
		, "ligne"."compte" AS "compte"
		, "ligne"."credit" - "ligne"."debit" AS "montant"
		FROM "cligne00002" "ligne"
		INNER JOIN "ccompt00002" "compte"
			ON "ligne"."compte" = "compte"."compte"
		WHERE ( "ligne"."compte" IN ( '.$compte.' ) )
		AND ( "ligne"."ladate" BETWEEN CAST( ( CASE WHEN DATE_PART( \'month\', CURRENT_DATE ) < 10 THEN EXTRACT( YEAR FROM CURRENT_DATE ) - 2 ELSE EXTRACT( YEAR FROM CURRENT_DATE ) - 1 END || \'-09-01\' ) AS DATE ) AND CAST( ( CASE WHEN DATE_PART( \'month\', CURRENT_DATE ) < 10 THEN EXTRACT( YEAR FROM CURRENT_DATE ) ELSE EXTRACT( YEAR FROM CURRENT_DATE ) + 1 END || \'-08-31\' ) AS DATE ) )
		AND "compte"."desactive" = FALSE
		AND "compte"."nonsaisie" = FALSE
		AND "ligne"."id_cjourn" = 18
		
		AND "ligne"."ladate" >= \''.$dateDebut.'\'
		AND "ligne"."ladate" < \''.$dateFin.'\'
		
		ORDER BY "ligne"."ladate", "montant"
		';
		
		
		$db = new RSN_DBCogilog_Module();
		$rows = $db->getDBRows($query);
		
		if(!$rows){
			echo('<code> ERREUR dans getCogilogEntries</code>');
			return;
		}
		
		$entries = array();
		foreach($rows as $row){
			if(!$entries[$row['Date']])
				$entries[$row['Date']] = array();
			$entries[$row['Date']][] = $row;
		}
		return $entries;
	}
	
}