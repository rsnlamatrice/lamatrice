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
		
		list($dateDebut, $dateFin) = $this->getDates($request, '+1 day');
		$dateRef = clone $dateDebut;
		$dateRef->modify('-1 month');
		
		$dates = array();
		for($d = 1; $d < 62; $d++){
			$dates[$dateRef->format('Y-m-d')] = $dateRef->format('d/m/Y');
			$dateRef->modify('+1 day');
		}
		
		$compte = $request->get('compte');
		if($compte === 'TOTAUX'){
			$request->set('compte', false);
			$compte = '';
		}
		
		$viewer->assign('SELECTED_COMPTE', $compte);
		
		$viewer->assign('SELECTED_DATE', $dateDebut->format('d/m/Y'));
		
		$viewer->assign('DATES', $dates);
		$viewer->assign('FORM_VIEW', 'GestionVSComptaRows');
		$viewer->assign('TITLE', 'Ecritures dans la Gestion et la Compta');
	}
	
	public function initRowsEntries(Vtiger_Request $request) {
		
		$viewer = $this->getViewer($request);
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		
		list($dateDebut, $dateFin) = $this->getDates($request, '+1 day');
		
		$compte = $request->get('compte');
		
		$ecartMontants = $request->get('ecartMontants');
		if($ecartMontants)
			$ecartMontants = (float)str_replace(',', '.', $ecartMontants);
		else
			$ecartMontants = 0.02;
		
		$laMatEntries = $this->getLaMatriceRowsEntries($dateDebut->format('Y-m-d'), $dateFin->format('Y-m-d'), $compte);
		$cogEntries = $this->getCogilogRowsEntries($dateDebut->format('Y-m-d'), $dateFin->format('Y-m-d'), $compte);
		//var_dump($laMatEntries);
		//var_dump($cogEntries);
		if(1){
			$entries = array();
			foreach($laMatEntries as $date => $ecritures){
				if(!$entries[$date])
					$entries[$date] = array('LAM'=>array());
				foreach($ecritures as $ecriture)
					if($ecriture['montant'] != 0)
						$entries[$date]['LAM'][] = $ecriture;
			}
			foreach($cogEntries as $date => $ecritures){
				if(!$entries[$date])
					$entries[$date] = array('COG'=>array());
				else
					$entries[$date]['COG'] = array();
				$laMatEntries = $entries[$date]['LAM'];
				foreach($ecritures as $ecriture)
					if($ecriture['montant'] != 0){
						
						if($laMatEntries){
							$found = false;
							foreach($laMatEntries as $laMatIndex => $laMatEntry){
								if(!$laMatEntry['pointee']
								&& $laMatEntry['compte'] === $ecriture['compte']
								&& $laMatEntry['montant'] == $ecriture['montant']){
									//unset($laMatEntries[$laMatIndex]);
									//unset($entries[$date]['LAM'][$laMatIndex]);
									$laMatEntries[$laMatIndex]['pointee'] = true;
									$entries[$date]['LAM'][$laMatIndex]['pointee'] = true;
									$ecriture['pointee'] = true;
									$found = true;
									break;
								}
								if($laMatEntry['montant'] > $ecriture['montant'])
									break;
							}
						}
						
						$entries[$date]['COG'][] = $ecriture;
					}
			}
		}
		$viewer->assign('ALL_SOURCES', array('COG'=>'Compta', 'LAM'=>'Gestion'));
		$viewer->assign('ENTRIES', $entries);
	}
	
	
	public function getLaMatriceRowsEntries($dateDebut, $dateFin, $compte){
		global $adb;
		if($compte)
			$compte = "'$compte'";
		else
			$compte = $this->getComptesString();
		$query = "SELECT `vtiger_invoice`.`invoicedate` AS `date`
		, CONCAT(`vtiger_invoice`.`subject`, ' - ', `vtiger_invoice`.`invoice_no`) AS `nomfacture`
		, IFNULL( `vtiger_products`.`glacct`, `vtiger_servicecf`.`glacct` ) AS `compte`
		, SUM(ROUND( `vtiger_inventoryproductrel`.`quantity` * `vtiger_inventoryproductrel`.`listprice` * ( 1 - `vtiger_inventoryproductrel`.`discount_percent` / 100 ) - `vtiger_inventoryproductrel`.`discount_amount`, 2 )) AS `montant`
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
		AND IFNULL( `vtiger_products`.`glacct`, `vtiger_servicecf`.`glacct` )
		IN ( ".$compte." )
		
		AND `vtiger_invoice`.`invoicedate` >= ?
		AND `vtiger_invoice`.`invoicedate` < ?
		
		GROUP BY `vtiger_invoice`.`invoicedate`
		, CONCAT(`vtiger_invoice`.`subject`, ' - ', `vtiger_invoice`.`invoice_no`)
		, IFNULL( `vtiger_products`.`glacct`, `vtiger_servicecf`.`glacct` )
		
		ORDER BY `date`, `montant`
		
		";
		
		$params = array($dateDebut, $dateFin);
		
		$result = $adb->pquery($query, $params);
		
		if(!$result){
			$adb->echoError('getLaMatriceRowsEntries');
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
		, "ligne"."libelle" || \' - \' || "ligne"."piece" AS "nomfacture"
		, "ligne"."compte" AS "compte"
		, SUM("ligne"."credit" - "ligne"."debit") AS "montant"
		FROM "cligne00002" "ligne"
		INNER JOIN "ccompt00002" "compte"
			ON "ligne"."compte" = "compte"."compte"
		WHERE ( "ligne"."compte" IN ( '.$compte.' ) )
		AND "compte"."desactive" = FALSE
		AND "compte"."nonsaisie" = FALSE
		AND "ligne"."id_cjourn" = 18
		
		AND "ligne"."ladate" >= \''.$dateDebut.'\'
		AND "ligne"."ladate" < \''.$dateFin.'\'
		
		GROUP BY "ligne"."ladate"
		, "ligne"."libelle" || \' - \' || "ligne"."piece"
		, "ligne"."compte"
		
		ORDER BY "ligne"."ladate", "montant"
		';
		
		
		$db = new RSN_DBCogilog_Module();
		$rows = $db->getDBRows($query);
		
		if($rows === false){
			echo('<code> ERREUR dans getCogilogEntries</code>');
			return;
		}
		
		$entries = array();
		foreach($rows as $row){
			if(!$entries[$row['date']])
				$entries[$row['date']] = array();
			$entries[$row['date']][] = $row;
		}
		return $entries;
	}
	
}