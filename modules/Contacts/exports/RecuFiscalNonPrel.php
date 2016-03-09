<?php
/*+***********************************************************************************
	AV1511
 *************************************************************************************/

include("Numbers/Words.php");

class Contacts_RecuFiscalNonPrel_Export extends Export_ExportData_Action { // TMP € -> check encodage ....
	//tmp check mailing ou other address !!!!!!!
	function getExportStructure() {
		return array(
			"Numero Ordre" => "", //Reçu n° 2014 / 010375
			"Ref" => "contact_no",
			"Ligne2" => "grpnomllong",
			"Prenom-Nom" => function ($row) { return $row["firstname"] . " " . $row["lastname"]; },
			"Ligne3" => function ($row) { return Contacts_RecuFiscalNonPrel_Export::getAddressField($row, "street2"); },
			"Ligne4" => function ($row) { return Contacts_RecuFiscalNonPrel_Export::getAddressField($row, "street3"); },
			"Ligne5" => function ($row) { return Contacts_RecuFiscalNonPrel_Export::getAddressField($row, "street"); },
			"BP" => function ($row) { return Contacts_RecuFiscalNonPrel_Export::getAddressField($row, "pobox"); },
			"CP-Ville" => function ($row) { return Contacts_RecuFiscalNonPrel_Export::getAddressField($row, "zip") . " " .
													Contacts_RecuFiscalNonPrel_Export::getAddressField($row, "city"); },
			"Pays" => function ($row) { return Contacts_RecuFiscalNonPrel_Export::getAddressField($row, "country"); },
			"Salutations" => function ($row) { return Contacts_RecuFiscalNonPrel_Export::getSalutation($row); },
			"Dons" => function ($row) { return Contacts_RecuFiscalNonPrel_Export::getTotalDons($row) . " euros" /*. " €"*/; },
			"Dons en lettres" => function ($row) { return Contacts_RecuFiscalNonPrel_Export::getTotalDonsLetter($row). " euros"; },
			"Dons après déduction" => function ($row) { return Contacts_RecuFiscalNonPrel_Export::getRealDons($row) . " euros" /*. " €"*/; },
		);
	}
	
	function displayHeaderLine() {
		return true;
	}

	function getExportEncoding(Vtiger_Request $request) {
		return 'ISO-8859-1';
	}

	function getExportFileName($request) {
		$moduleName = $request->get('source_module');
		return str_replace(' ','_',vtranslate($moduleName, $moduleName)) . "_Recu_fiscaux_sans_prel";
	}

	function getAddressToUse($row) {
		if ($row["use_address2_for_recu_fiscal"]) {
			return "other";
		}

		return "mailing";
	}

	function getAddressField($row, $field) {
		return $row[$this->getAddressToUse($row) . $field];
	}

	function getSalutation($row) {
		// var_dump($row);

		if ($row["firstname"] != "") {
			$return_value .= " " . $row["firstname"];
		}

		$return_value .= ",";

		return $return_value;
	}

	function getDons($row) {//tmp requete executé pour chaque contact ...
		$db = PearDatabase::getInstance();
		$current_year = date("Y") - 1;
		$date_debut = $current_year . "-01-01";
		$date_fin = $current_year . "-12-31";
		$query = "SELECT vtiger_crmentity.crmid , SUM(vtiger_inventoryproductrel.listprice * vtiger_inventoryproductrel.quantity) total_dons
				FROM vtiger_invoice
				JOIN vtiger_contactdetails ON vtiger_contactdetails.accountid = vtiger_invoice.accountid
				JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid
				JOIN vtiger_crmentity vtiger_invoice_crmentity ON vtiger_invoice_crmentity.crmid = vtiger_invoice.invoiceid 
				JOIN vtiger_inventoryproductrel ON vtiger_inventoryproductrel.id = vtiger_invoice.invoiceid 
				JOIN vtiger_service ON vtiger_inventoryproductrel.productid = vtiger_service.serviceid
				JOIN vtiger_crmentity vtiger_service_crmentity ON vtiger_service_crmentity.crmid = vtiger_service.serviceid
				WHERE vtiger_crmentity.deleted = 0
				AND vtiger_invoice_crmentity.deleted = 0
				AND vtiger_service_crmentity.deleted = 0
				AND vtiger_inventoryproductrel.quantity != 0 
				AND vtiger_inventoryproductrel.listprice != 0
				AND vtiger_invoice.invoicedate BETWEEN '" . $date_debut . "' AND '" . $date_fin . "'
				AND vtiger_service.servicecategory = 'Dons'
				AND vtiger_contactdetails.contact_no = '" . $row["contact_no"] . "' " .
				"GROUP BY vtiger_crmentity.crmid";
				// echo $query;
				// exit;
		$result = $db->pquery($query, array());
		return (int) $db->fetchByAssoc($result, 0)["total_dons"];
	}

	function getPrel($row) {//tmp requete executé pour chaque contact ...
		$db = PearDatabase::getInstance();
		$current_year = date("Y") - 1;
		$date_debut = $current_year . "-01-01";
		$date_fin = $current_year . "-12-31";
		$query = "SELECT DISTINCT SUM(vtiger_rsnprelvirement.montant) total_prelevements 
				FROM vtiger_crmentity 
				JOIN vtiger_contactdetails ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid
				JOIN vtiger_rsnprelevements ON vtiger_contactdetails.accountid = vtiger_rsnprelevements.accountid
				JOIN vtiger_crmentity vtiger_rsnprelevements_crmentity ON vtiger_rsnprelevements_crmentity.crmid = vtiger_rsnprelevements.rsnprelevementsid 
				JOIN vtiger_rsnprelvirement ON vtiger_rsnprelvirement.rsnprelevementsid = vtiger_rsnprelevements.rsnprelevementsid
				JOIN vtiger_crmentity vtiger_rsnprelvirement_crmentity ON vtiger_rsnprelvirement_crmentity.crmid = vtiger_rsnprelvirement.rsnprelvirementid 
				WHERE vtiger_crmentity.deleted = 0
				AND vtiger_rsnprelevements_crmentity.deleted = 0
				AND vtiger_rsnprelvirement_crmentity.deleted = 0
				AND vtiger_rsnprelvirement.dateexport BETWEEN '" . $date_debut . "' AND '" . $date_fin . "' " .
				"AND vtiger_contactdetails.contact_no = '" . $row["contact_no"] . "' " .
				"GROUP BY vtiger_crmentity.crmid";
				// echo $query;
				// exit;
		$result = $db->pquery($query, array());
		return (int) $db->fetchByAssoc($result, 0)["total_prelevements"];
	}

	function getTotalDons($row) {
		$total_dons = $this->getDons($row) + $this->getPrel($row);

		return $total_dons;
	}

	function getTotalDonsLetter($row) {
		$total_dons = $this->getTotalDons($row);
		
 
		// créer l'objet
		$nw = new Numbers_Words();
		 
		// convertir en chaîne
		//echo "600 en lettres donne " . $nw->toWords(600);
		return $nw->toWords($total_dons, 'fr');
	}

	function getRealDons($row) {
		$rate = 0.34;
		$total_dons = $this->getDons($row) + $this->getPrel($row);

		return (int) ($total_dons * $rate);
	}

	/*function getExportQuery($request) {//tmp ...
		$query = $parentQuery = parent::getExportQuery($request);
		$current_year = date("Y") - 1;
		$date_debut = $current_year . "-01-01";
		$date_fin = $current_year . "-12-31";
		// echo $query;
		$fromPos = strpos($parentQuery, 'FROM');//tmp attention si il y a plusieurs clauses FROM
		$wherePos = strpos($parentQuery, 'WHERE');//tmp attention si il y a plusieurs clauses WHERE
		$orderbyPos = strrpos($parentQuery, 'ORDER BY');//tmp attention si il y a plusieurs clauses ORDER BY

		$query = substr($parentQuery, 0, $fromPos) . ", SUM(vtiger_inventoryproductrel.listprice * vtiger_inventoryproductrel.quantity) total_dons " .
													", SUM(vtiger_rsnprelvirement.montant) total_prelevements " .
				 substr($parentQuery, $fromPos, ($wherePos - $fromPos)) . " INNER JOIN vtiger_invoice ON vtiger_contactdetails.accountid = vtiger_invoice.accountid
														INNER JOIN vtiger_crmentity vtiger_invoice_crmentity ON vtiger_invoice_crmentity.crmid = vtiger_invoice.invoiceid 
														INNER JOIN vtiger_inventoryproductrel ON vtiger_inventoryproductrel.id = vtiger_invoice.invoiceid 
														INNER JOIN vtiger_service ON vtiger_inventoryproductrel.productid = vtiger_service.serviceid
														INNER JOIN vtiger_crmentity vtiger_service_crmentity ON vtiger_service_crmentity.crmid = vtiger_service.serviceid " .
														" INNER JOIN vtiger_rsnprelevements ON vtiger_contactdetails.accountid = vtiger_rsnprelevements.accountid
														INNER JOIN vtiger_crmentity vtiger_rsnprelevements_crmentity ON vtiger_rsnprelevements_crmentity.crmid = vtiger_rsnprelevements.rsnprelevementsid 
														INNER JOIN vtiger_rsnprelvirement ON vtiger_rsnprelvirement.rsnprelevementsid = vtiger_rsnprelevements.rsnprelevementsid
														INNER JOIN vtiger_crmentity vtiger_rsnprelvirement_crmentity ON vtiger_rsnprelvirement_crmentity.crmid = vtiger_rsnprelvirement.rsnprelvirementid " .
				 substr($parentQuery, $wherePos, ($orderbyPos - $wherePos)) . " AND vtiger_crmentity.deleted = 0
														AND vtiger_invoice_crmentity.deleted = 0
														AND vtiger_service_crmentity.deleted = 0
														AND vtiger_inventoryproductrel.quantity != 0 
														AND vtiger_inventoryproductrel.listprice != 0
														AND vtiger_invoice.invoicedate BETWEEN '" . $date_debut . "' AND '" . $date_fin . "'
														AND vtiger_service.servicecategory = 'Dons' ".
														"AND vtiger_rsnprelevements_crmentity.deleted = 0
														AND vtiger_rsnprelvirement_crmentity.deleted = 0
														AND vtiger_rsnprelvirement.dateexport BETWEEN '" . $date_debut . "' AND '" . $date_fin . "' " .
														" GROUP BY vtiger_rsnprelevements.rsnprelevementsid, vtiger_rsnprelvirement.rsnprelevementsid, vtiger_crmentity.crmid " .
				 substr($parentQuery, $orderbyPos);

		echo '<br/><br/><br/>' . $query;

		return $query;
	}*/
}