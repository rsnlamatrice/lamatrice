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
 
class Invoice_GestionVSComptaTVA_View extends Invoice_GestionVSComptaCA_View {

	
	
	public function initFormData(Vtiger_Request $request) {
		parent::initFormData($request);
		
		$viewer = $this->getViewer($request);
		
		$viewer->assign('FORM_VIEW', 'GestionVSComptaTVA');
		$viewer->assign('ROWS_URL', 'index.php?module='.$request->get('module').'&view=GestionVSComptaTVARows');
	}
		
	function getComptesString(){
		//TODO select account from vtiger_inventorytaxinfo where deleted = 0
		return "'445715', '445713', '445712'";
	}
	
	public function getLaMatriceComptesEntries($dateDebut, $dateFin){
		global $adb;
		$params = array();
		$query = '';
		for($TAXID = 1; $TAXID <= 6; $TAXID++){
			if($query !== '')
				$query .= "
					UNION
				";
			
			$query .= "SELECT `vtiger_invoice`.`invoicedate` AS `Date`
				, `vtiger_inventorytaxinfo`.`account` AS `Compte`
				, SUM( ROUND( `vtiger_inventoryproductrel`.`quantity` * `vtiger_inventoryproductrel`.`listprice` * ( 1 - `vtiger_inventoryproductrel`.`discount_percent` / 100 ) - `vtiger_inventoryproductrel`.`discount_amount`, 2 )
					* `vtiger_inventoryproductrel`.tax$TAXID / 100
				) AS `Montant`
				FROM `vtiger_invoice`
				INNER JOIN `vtiger_crmentity` AS `vtiger_crmentity_invoice`
					ON `vtiger_invoice`.`invoiceid` = `vtiger_crmentity_invoice`.`crmid`
				INNER JOIN `vtiger_invoicecf`
					ON `vtiger_invoicecf`.`invoiceid` = `vtiger_crmentity_invoice`.`crmid`
				INNER JOIN `vtiger_inventoryproductrel`
					ON `vtiger_inventoryproductrel`.`id` = `vtiger_crmentity_invoice`.`crmid`
				INNER JOIN `vtiger_inventorytaxinfo`
					ON `vtiger_inventorytaxinfo`.`taxid` = $TAXID
					AND `vtiger_inventoryproductrel`.tax$TAXID IS NOT NULL
				WHERE `vtiger_crmentity_invoice`.`deleted` = FALSE
				
				AND `vtiger_invoice`.`invoicedate` >= ?
				AND `vtiger_invoice`.`invoicedate` < ?
				
				GROUP BY `vtiger_invoice`.`invoicedate`, `vtiger_inventorytaxinfo`.`account`
			";
		
			array_push($params, $dateDebut, $dateFin);
		}
		$query .= "
			ORDER BY `Date`, `Compte`
		";
		
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
	public function getCogilogComptesEntries($dateDebut, $dateFin){
		
		$query = '
		SELECT "ligne"."ladate" AS "Date"
		, "ligne"."compte" AS "Compte"
		, SUM( "ligne"."credit" - "ligne"."debit" ) AS "Montant"
		FROM "cligne00002" "ligne"
		INNER JOIN "ccompt00002" "compte"
			ON "ligne"."compte" = "compte"."compte"
		WHERE ( "ligne"."compte" IN ( '.$this->getComptesString().' ) )
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
		
		if($rows === false){
			echo "<pre>$query</pre>";
			echo('<code> ERREUR dans getCogilogComptesEntries</code>');
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