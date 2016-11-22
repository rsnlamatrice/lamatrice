<?php
/*+***********************************************************************************
	AV1511
 *************************************************************************************/

class Contacts_MontantDonTmp_Export extends Contacts_RecuFiscalNonPrel_Export { // TMP Adresse !!! -> Should Be recu fiscal et non pas Abo !!!!!!!!!!
	//tmp check mailing ou other address !!!!!!!
	function getExportStructure() {
		return array(
			"Ref" => "contact_no",
			"Ligne2" => "grpnomllong",
			"Prenom-Nom" => function ($row) { return $row["firstname"] . " " . $row["lastname"]; },
			"Ligne3" => function ($row) { return Contacts_MontantDonTmp_Export::getAddressField($row, "street2"); },
			"Ligne4" => function ($row) { return Contacts_MontantDonTmp_Export::getAddressField($row, "street3"); },
			"Ligne5" => function ($row) { return Contacts_MontantDonTmp_Export::getAddressField($row, "street"); },
			"BP" => function ($row) { return Contacts_MontantDonTmp_Export::getAddressField($row, "pobox"); },
			"CP-Ville" => function ($row) { return Contacts_MontantDonTmp_Export::getAddressField($row, "zip") . " " .
													Contacts_MontantDonTmp_Export::getAddressField($row, "city"); },
			"Pays" => function ($row) { return Contacts_MontantDonTmp_Export::getAddressField($row, "country"); },
			"Dons" => function ($row) { return Contacts_MontantDonTmp_Export::getTotalDons($row); },
			"Dons en lettres" => function ($row) { return Contacts_MontantDonTmp_Export::getTotalDonsLetter($row) . " euros"; },
			"Dons après déduction" => function ($row) { return Contacts_MontantDonTmp_Export::getRealDons($row); },
		);
	}

	function getDons($row) {//tmp requete executé pour chaque contact ...
		$db = PearDatabase::getInstance();
		$current_year = date("Y") - 1;
		$date_debut = "2015-09-01";
		$date_fin = "2016-08-31";
		$query = "SELECT SUM(vtiger_inventoryproductrel.listprice * vtiger_inventoryproductrel.quantity) total_dons
					FROM vtiger_inventoryproductrel
	                
					 
					JOIN vtiger_invoice ON vtiger_inventoryproductrel.id = vtiger_invoice.invoiceid 
					JOIN vtiger_service ON vtiger_inventoryproductrel.productid = vtiger_service.serviceid
					JOIN vtiger_crmentity vtiger_service_crmentity ON vtiger_service_crmentity.crmid = vtiger_service.serviceid
	                JOIN vtiger_crmentity vtiger_invoice_crmentity ON vtiger_invoice_crmentity.crmid = vtiger_invoice.invoiceid
	                
					WHERE vtiger_invoice_crmentity.deleted = false
					AND vtiger_service_crmentity.deleted = false
					AND vtiger_inventoryproductrel.quantity != 0 
					AND vtiger_inventoryproductrel.listprice != 0
					AND vtiger_invoice.invoicedate BETWEEN ? AND ?
					AND vtiger_service.servicecategory = 'Dons'
					AND vtiger_invoice.invoicestatus != 'Cancelled'
	                AND vtiger_invoice.accountid = ?";
				// echo $query;
				// exit;
	    $params = array($date_debut, $date_fin, $row["accountid"]);
		$result = $db->pquery($query, $params);
		return $db->fetchByAssoc($result, 0)["total_dons"];
	}

	function getPrel($row) {//tmp requete executé pour chaque contact ...
		$db = PearDatabase::getInstance();
		$current_year = date("Y") - 1;//TMP Year !
		$date_debut = "2015-09-01";
		$date_fin = "2016-08-31";
		$query = "SELECT DISTINCT SUM(vtiger_rsnprelvirement.montant) total_prelevements 
				FROM vtiger_rsnprelevements 
				JOIN vtiger_crmentity vtiger_rsnprelevements_crmentity ON vtiger_rsnprelevements_crmentity.crmid = vtiger_rsnprelevements.rsnprelevementsid 
				JOIN vtiger_rsnprelvirement ON vtiger_rsnprelvirement.rsnprelevementsid = vtiger_rsnprelevements.rsnprelevementsid
				JOIN vtiger_crmentity vtiger_rsnprelvirement_crmentity ON vtiger_rsnprelvirement_crmentity.crmid = vtiger_rsnprelvirement.rsnprelvirementid 
				WHERE vtiger_rsnprelevements_crmentity.deleted = 0
				AND vtiger_rsnprelvirement_crmentity.deleted = 0
				AND vtiger_rsnprelvirement.rsnprelvirstatus = 'Ok'
				AND vtiger_rsnprelvirement.dateexport BETWEEN ? AND ?
				AND vtiger_rsnprelevements.accountid = ?";

				// echo $query;
				// exit;
		$params = array($date_debut, $date_fin, $row["accountid"]);
		$result = $db->pquery($query, $params);
		return $db->fetchByAssoc($result, 0)["total_prelevements"];
	}
	
	function displayHeaderLine() {
		return true;
	}

	function getExportFileName($request) {
		$moduleName = $request->get('source_module');
		return str_replace(' ','_',vtranslate($moduleName, $moduleName)) . "_Recu_fiscaux_avec_prel";
	}

	// function getExportQuery($request) {//tmp ...
	// 	$query = $parentQuery = parent::getExportQuery($request);
	// 	$current_year = date("Y") - 1;//TMP Date
	// 	$date_debut = "2013-09-01";
	// 	$date_fin = "2014-08-31";
	// 	// echo $query;
	// 	$fromPos = strpos($parentQuery, 'FROM');//tmp attention si il y a plusieurs clauses FROM
	// 	$wherePos = strpos($parentQuery, 'WHERE');//tmp attention si il y a plusieurs clauses WHERE
	// 	$orderbyPos = strrpos($parentQuery, 'ORDER BY');//tmp attention si il y a plusieurs clauses ORDER BY

	// 	$query = substr($parentQuery, 0, $fromPos) . ", vtiger_rsnprelevements.montant AS prel_amount, vtiger_rsnprelevements.periodicite AS prel_periode " .
	// 			 substr($parentQuery, $fromPos, ($wherePos - $fromPos)) . " LEFT JOIN vtiger_rsnprelevements ON vtiger_contactdetails.accountid = vtiger_rsnprelevements.accountid AND vtiger_rsnprelevements.etat = 0
	// 													LEFT JOIN vtiger_crmentity vtiger_rsnprelevements_crmentity ON vtiger_rsnprelevements_crmentity.crmid = vtiger_rsnprelevements.rsnprelevementsid AND vtiger_rsnprelevements_crmentity.deleted != 0 " .
	// 			 substr($parentQuery, $wherePos, ($orderbyPos - $wherePos)) . " /*AND vtiger_rsnprelevements_crmentity.deleted != 0*/ " .//tmp ??? 
	// 													" GROUP BY vtiger_crmentity.crmid " .
	// 			 substr($parentQuery, $orderbyPos);

	// 	// echo '<br/><br/><br/>' . $query;

	// 	return $query;
	// }
}