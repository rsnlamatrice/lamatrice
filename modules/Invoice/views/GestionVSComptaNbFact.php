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
 
class Invoice_GestionVSComptaNbFact_View extends Invoice_GestionVSCompta_View {


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
		
		$viewer->assign('FORM_VIEW', 'GestionVSComptaNbFact');
		$viewer->assign('ROWS_URL', 'index.php?module='.$request->get('module').'&view=GestionVSComptaNbFactRows');
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
		
		$viewer->assign('COMPTES', $allComptes);
		$viewer->assign('ENTRIES', $entries);
		$viewer->assign('ALL_SOURCES', array('COG' => 'Compta', 'LAM' => 'Gestion'));
	}
	
	public function getLaMatriceComptesEntries($dateDebut, $dateFin){
		global $adb;
		$query = "SELECT `vtiger_invoice`.`invoicedate` AS `Date`
		, 'Montants' AS `Compte`
		, SUM( ROUND( `vtiger_invoice`.`total`, 2 ) ) AS `Montant`
		FROM `vtiger_invoice`
		INNER JOIN `vtiger_crmentity` AS `vtiger_crmentity_invoice`
			ON `vtiger_invoice`.`invoiceid` = `vtiger_crmentity_invoice`.`crmid`
		INNER JOIN `vtiger_invoicecf`
			ON `vtiger_invoicecf`.`invoiceid` = `vtiger_crmentity_invoice`.`crmid`
		WHERE `vtiger_crmentity_invoice`.`deleted` = FALSE
		AND vtiger_invoice.invoicestatus != 'Cancelled'
		
		AND `vtiger_invoice`.`invoicedate` >= ?
		AND `vtiger_invoice`.`invoicedate` < ?
		
		GROUP BY `vtiger_invoice`.`invoicedate`
		
		UNION
		
		SELECT `vtiger_invoice`.`invoicedate` AS `Date`
		, 'Nombre' AS `Nombre`
		, COUNT( * ) AS `Montant`
		FROM `vtiger_invoice`
		INNER JOIN `vtiger_crmentity` AS `vtiger_crmentity_invoice`
			ON `vtiger_invoice`.`invoiceid` = `vtiger_crmentity_invoice`.`crmid`
		INNER JOIN `vtiger_invoicecf`
			ON `vtiger_invoicecf`.`invoiceid` = `vtiger_crmentity_invoice`.`crmid`
		WHERE `vtiger_crmentity_invoice`.`deleted` = FALSE
		AND vtiger_invoice.invoicestatus != 'Cancelled'
		
		AND `vtiger_invoice`.`invoicedate` >= ?
		AND `vtiger_invoice`.`invoicedate` < ?
		
		GROUP BY `vtiger_invoice`.`invoicedate`
		
		ORDER BY `Date`, `Compte`
		";
		
		$params = array($dateDebut, $dateFin, $dateDebut, $dateFin);
		
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
		//Gestion Cogilog
		$query = '
		SELECT "datepiece" AS "date"
		, \'Montants\' AS "compte"
		, SUM("netht" + "nettva") AS "montant"
		FROM "gfactu00002" "facture" 
		WHERE "datepiece" >= \''.$dateDebut.'\'
		AND "datepiece" < \''.$dateFin.'\'
		
		GROUP BY "datepiece"
		
		UNION
		
		SELECT "datepiece" AS "date"
		, \'Nombre\' AS "compte"
		, COUNT(*) AS "montant"
		FROM "gfactu00002" "facture" 
		WHERE "datepiece" >= \''.$dateDebut.'\'
		AND "datepiece" < \''.$dateFin.'\'
		
		GROUP BY "datepiece"
		
		UNION
		
		SELECT "datepiece" AS "date"
		, \'Nombre fact. vides\' AS "compte"
		, COUNT(*) AS "montant"
		FROM "gfactu00002" "facture" 
		WHERE "datepiece" >= \''.$dateDebut.'\'
		AND "datepiece" < \''.$dateFin.'\'
		AND "facture"."id" NOT IN (
			SELECT "gfactu00002"."id"
			FROM "gfactu00002"
			JOIN "glfact00002"
				ON "gfactu00002"."id" = "glfact00002"."id_piece"
			WHERE "datepiece" >= \''.$dateDebut.'\'
			AND "datepiece" < \''.$dateFin.'\'
			AND id_gprodu IS NOT NULL
		)
		GROUP BY "datepiece"
		
		ORDER BY "date", "compte"
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