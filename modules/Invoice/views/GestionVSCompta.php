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
 
class Invoice_GestionVSCompta_View extends Vtiger_Index_View {

	public function preProcess(Vtiger_Request $request, $display = true) {
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE_NAME', $request->getModule());

		parent::preProcess($request, false);
		if($display) {
			$this->preProcessDisplay($request);
		}
	}

	public function process(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		
		$this->initFormData($request);
		$this->initComptesEntries($request);
		
		$viewer->assign('CURRENT_USER', $currentUserModel);

		$viewer->view('GestionVSCompta.tpl', $request->getModule());
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
	
	public function initComptesEntries(Vtiger_Request $request) {
		
		$viewer = $this->getViewer($request);
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		
		$dateDebut = $request->get('date');
		if(!$dateDebut)
			$dateDebut = date('Y-m-01');
		$dateDebut = new DateTime($dateDebut);
		$dateFin = clone $dateDebut;
		$dateFin->modify('+1 month');
		
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
	
	function getComptesString(){
		return "'467500', '467600', '467900', '701010', '701110', '701120', '701130', '701140', '701200', '701400', '701500', '707100', '707200', '707300', '707500', '707510', '707600', '707610', '707620', '707630', '707640', '707650', '707800', '708000', '741000', '756200', '758010', '758020', '758030', '758050', '758070', '758100', '758110', '758400', '758840', '791200'";
	}
	
	public function getLaMatriceComptesEntries($dateDebut, $dateFin){
		global $adb;
		$query = "SELECT `vtiger_invoice`.`invoicedate` AS `Date`
		, IFNULL( `vtiger_products`.`glacct`, `vtiger_servicecf`.`glacct` ) AS `Compte`
		, SUM( ROUND( `vtiger_inventoryproductrel`.`quantity` * `vtiger_inventoryproductrel`.`listprice` * ( 1 - `vtiger_inventoryproductrel`.`discount_percent` / 100 ) - `vtiger_inventoryproductrel`.`discount_amount`, 2 ) ) AS `Montant`
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
		IN ( ".$this->getComptesString()." )
		
		AND `vtiger_invoice`.`invoicedate` >= ?
		AND `vtiger_invoice`.`invoicedate` < ?
		
		GROUP BY `vtiger_invoice`.`invoicedate`, IFNULL( `vtiger_products`.`glacct`, `vtiger_servicecf`.`glacct` )
		
		ORDER BY `Date`, `Compte`
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
			$entries[$row['date']][$row['compte']] = $row['montant'];
		}
		return $entries;
	}
	public function getCogilogComptesEntries($dateDebut, $dateFin){
		
		$query = '
		SELECT "ligne"."ladate" AS "Date"
		, "ligne"."compte" AS "Compte"
		, SUM( "ligne"."credit" - "ligne"."debit" ) AS "Montant"
		FROM "cligne00002" "ligne"
		INNER JOIN "ccompt00002" "compte"
			ON "ligne"."compte" = "compte"."compte"
		WHERE ( "ligne"."compte" IN ( '.$this->getComptesString().' ) )
		AND ( "ligne"."ladate" BETWEEN CAST( ( CASE WHEN DATE_PART( \'month\', CURRENT_DATE ) < 10 THEN EXTRACT( YEAR FROM CURRENT_DATE ) - 2 ELSE EXTRACT( YEAR FROM CURRENT_DATE ) - 1 END || \'-09-01\' ) AS DATE ) AND CAST( ( CASE WHEN DATE_PART( \'month\', CURRENT_DATE ) < 10 THEN EXTRACT( YEAR FROM CURRENT_DATE ) ELSE EXTRACT( YEAR FROM CURRENT_DATE ) + 1 END || \'-08-31\' ) AS DATE ) )
		AND "compte"."desactive" = FALSE
		AND "compte"."nonsaisie" = FALSE
		AND "ligne"."id_cjourn" = 18
		
		AND "ligne"."ladate" >= \''.$dateDebut.'\'
		AND "ligne"."ladate" < \''.$dateFin.'\'
		
		GROUP BY "ligne"."ladate", "ligne"."compte"
		
		ORDER BY "ligne"."ladate", "ligne"."compte"
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
			$entries[$row['Date']][$row['Compte']] = $row['Montant'];
		}
		return $entries;
	}
	
}