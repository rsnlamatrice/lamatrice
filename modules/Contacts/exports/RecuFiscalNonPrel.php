<?php
/*+***********************************************************************************
	AV1511
 *************************************************************************************/

include("Numbers/Words.php");

class Contacts_RecuFiscalNonPrel_Export extends Export_ExportData_Action { // TMP € -> check encodage ....
	//tmp check mailing ou other address !!!!!!!
	function getExportStructure() {
		return array(
			"Numero Ordre" => function ($row) { return Contacts_RecuFiscalPrel_Export::getRecuFiscalDisplayNumber($row); }, //Reçu n° 2014 / 010375
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
			"Dons" => function ($row) { return Contacts_RecuFiscalNonPrel_Export::getTotalDons($row) . " " . utf8_encode(chr(128)); },
			"Dons en lettres" => function ($row) { return Contacts_RecuFiscalNonPrel_Export::getTotalDonsLetter($row). " euros"; },
			"Dons après déduction" => function ($row) { return Contacts_RecuFiscalNonPrel_Export::getRealDons($row) . " " . utf8_encode(chr(128)); },
		);
	}
	
	function displayHeaderLine() {
		return true;
	}

	function getExportEncoding(Vtiger_Request $request) {
		return 'ISO-8859-1';
		//return 'ASCII';
	}

	function getExportFileName($request) {
		$moduleName = $request->get('source_module');
		return str_replace(' ','_',vtranslate($moduleName, $moduleName)) . "_Recu_fiscaux_donateurs";
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
		$return_value = "Bonjour";

		if ($row["firstname"] != "") {
			$return_value .= " " . $row["firstname"];
		}

		$return_value .= ",";

		return $return_value;
	}

	function getRecuFiscalDisplayNumber($row) {
		$current_year = date("Y") - 1;//TMP Date !!!
		$number = $this->getRecuFiscalNumber($row, $current_year);//TMP ....

		return "Reçu n° " . $current_year . " / " . $number;
	}

	function getDons($row) {//tmp requete executé pour chaque contact ...
		$db = PearDatabase::getInstance();
		$current_year = date("Y") - 1;
		$date_debut = $current_year . "-01-01";
		$date_fin = $current_year . "-12-31";
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
		$date_debut = $current_year . "-01-01";
		$date_fin = $current_year . "-12-31";
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

	function getTotalDons($row) {
		$total_dons = $this->getDons($row) + $this->getPrel($row);

		return round($total_dons, 0);
	}

	function getTotalDonsLetter($row) {
		$total_dons = $this->getTotalDons($row);

		$nw = new Numbers_Words();
		 
		return $nw->toWords($total_dons, 'fr');
	}

	function getRealDons($row) {
		$rate = 0.34;
		$total_dons = $this->getDons($row) + $this->getPrel($row);

		return round(($total_dons * $rate), 0);
	}

	function getRecuFiscalDocumentRecordModel($current_year) {
		global $adb;
		$query = "SELECT notesid
			FROM vtiger_notes
			JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_notes.notesid
			WHERE vtiger_crmentity.deleted = 0
			AND folderid = 17
			AND title LIKE '%2016'
			LIMIT 1";

		$result = $adb->pquery($query, $params);
		if($adb->num_rows($result) === 0){
			// Error !!!
		}
		$row = $adb->fetchByAssoc($result, 0, false);

		return Vtiger_Record_Model::getInstanceById($row['notesid'], 'Documents');
	}

	function getExportQuery($request) {//tmp ...
		$query = $parentQuery = parent::getExportQuery($request);

		$fromPos = strpos($parentQuery, 'FROM');//tmp attention si il y a plusieurs clauses FROM
		$wherePos = strpos($parentQuery, 'WHERE');//tmp attention si il y a plusieurs clauses WHERE
		$orderbyPos = strrpos($parentQuery, 'ORDER BY');//tmp attention si il y a plusieurs clauses ORDER BY

		$query = substr($parentQuery, 0, $fromPos) . ", vtiger_contactdetails.contactid " .
				 substr($parentQuery, $fromPos, ($wherePos - $fromPos)) . 
				 substr($parentQuery, $wherePos, ($orderbyPos - $wherePos)) . 
				 substr($parentQuery, $orderbyPos);

		// echo '<br/><br/><br/>' . $query;

		return $query;
	}

	function getRecuFiscalNumber($row, $year){
		$documentRecordModel = $this->getRecuFiscalDocumentRecordModel($year);
		$accountRecordModel = Vtiger_Record_Model::getInstanceById($row['accountid'], 'Accounts');;
		$contactRecordModel = Vtiger_Record_Model::getInstanceById($row['contactid'], 'Contacts');;

		$infos = $accountRecordModel->getInfosRecuFiscal($year, $documentRecordModel, $contactRecordModel);

		//tmp check if total dons = info total don !!!
		// var_dump($infos);
		// exit;

		// if ($infos["montant"] != $this->getTotalDons($row)) {
		// 	var_dump($infos["montant"]);
		// 	var_dump($this->getTotalDons($row));
		// 	var_dump($infos);
		// 	echo $row["contact_no"] . "=>" . $infos["montant"] . "!=" . $this->getTotalDons($row) . "<br/>";
		// 	exit();
		// }

		return $infos["recu_fiscal_num"];
	}
}