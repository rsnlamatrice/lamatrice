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
 
class Invoice_GestionVSComptaENCRows_View extends Invoice_GestionVSComptaENC_View {


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
		
		$dateDebut = $request->get('date');
		if(!$dateDebut)
			$dateDebut = date('Y-m-d');
		$dateRef = new DateTime($dateDebut);
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
		
		$viewer->assign('SELECTED_DATE', $dateDebut);
		
		$viewer->assign('DATES', $dates);
		$viewer->assign('FORM_VIEW', 'GestionVSComptaENCRows');
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
		$viewer->assign('SOURCES', array('COG'=>'Cogilog', 'LAM'=>'La Matrice'));
		$viewer->assign('ENTRIES', $entries);
	}
	
	
	public function getLaMatriceRowsEntries($dateDebut, $dateFin, $compte){
		global $adb;
		if($compte)
			$compte = "'$compte'";
		else
			$compte = false;
		$query = "SELECT `vtiger_invoice`.`invoicedate` AS `date`
		, CONCAT(`vtiger_invoice`.`subject`, ' - ', `vtiger_invoice`.`invoice_no`) AS `nomfacture`
		, vtiger_invoicecf.receivedmoderegl AS `compte`
		, SUM(ROUND( `vtiger_invoice`.`total`, 2 )) AS `montant`
		FROM `vtiger_invoice`
		INNER JOIN `vtiger_crmentity` AS `vtiger_crmentity_invoice`
			ON `vtiger_invoice`.`invoiceid` = `vtiger_crmentity_invoice`.`crmid`
		INNER JOIN `vtiger_invoicecf`
			ON `vtiger_invoicecf`.`invoiceid` = `vtiger_crmentity_invoice`.`crmid`
		LEFT JOIN `vtiger_notescf`
			ON `vtiger_notescf`.`notesid` = `vtiger_invoicecf`.`notesid`
		LEFT JOIN `vtiger_campaignscf`
			ON `vtiger_campaignscf`.`campaignid` = `vtiger_invoicecf`.`campaign_no`
		WHERE `vtiger_crmentity_invoice`.`deleted` = FALSE
		AND vtiger_invoice.invoicestatus != 'Cancelled'
		";
		if($compte)
			$query .= " AND vtiger_invoicecf.receivedmoderegl IN ( ".$compte." )
			";
		$query .= "
		AND `vtiger_invoice`.`invoicedate` >= ?
		AND `vtiger_invoice`.`invoicedate` < ?
		
		GROUP BY `vtiger_invoice`.`invoicedate`
		, CONCAT(`vtiger_invoice`.`subject`, ' - ', `vtiger_invoice`.`invoice_no`)
		, vtiger_invoicecf.receivedmoderegl
		
		ORDER BY `date`, `montant`
		
		";
		
		$params = array($dateDebut, $dateFin);
		
		$result = $adb->pquery($query, $params);
		
		if(!$result){
			echo "<pre>$query</pre>";
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
	
	/* A noter "ligne"."id_cjourn" = 18 */
	public function getCogilogRowsEntries($dateDebut, $dateFin, $compte){
		if($compte)
			$compte = "'$compte'";
		else
			$compte = $this->getComptesString();
		
		$query = '
		SELECT "ligne"."ladate" AS "date"
		, "ligne"."libelle" || \' - \' || "ligne"."piece" AS "nomfacture"
		, "ligne"."compte" AS "compte"
		, SUM("ligne"."credit" - "ligne"."debit") * -1 AS "montant"
		FROM "cligne00002" "ligne"
		INNER JOIN "ccompt00002" "compte"
			ON "ligne"."compte" = "compte"."compte"
		WHERE ( "ligne"."compte" LIKE \'411%\' OR "ligne"."compte" LIKE \'511%\' )
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