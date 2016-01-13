<?php
/*+***********************************************************************************
	AV1511
 *************************************************************************************/

class Contacts_ExportRevue_Export extends Export_ExportData_Action {

	//tmp check mailing ou other address !!!!!!!
	function getExportStructure() {
		return array(
			"Code client" => "contact_no",
			"Civilité" => "",//tmp ??
			"Nom" => "lastname",
			"Prenom" => "firstname",
			"Société" => function ($row) { return Contacts_ExportRevue_Export::getAddressField($row, "street2"); },
			"adresse 1" => function ($row) { return Contacts_ExportRevue_Export::getAddressField($row, "street3"); },
			"adresse 2" => function ($row) { return Contacts_ExportRevue_Export::getAddressField($row, "street"); },
			"adresse 3" => function ($row) { return Contacts_ExportRevue_Export::getPoBox($row); },
			"Code Postal" => function ($row) { return Contacts_ExportRevue_Export::getZipCode($row); },
			"Ville (ou Pays)" => function ($row) { return Contacts_ExportRevue_Export::getCityOrCountry($row); },
			"Nb exemplaires" => function ($row) { return ($row["nbexemplaires"] == 0) ? 1 : $row["nbexemplaires"]; },//"nbexemplaires",//tmp
			"Mode envoi" => "",//tmp to check ??
			"info lib 1" => function ($row) { return Contacts_ExportRevue_Export::getMessage1($row); },
			"info lib 2" => function ($row) { return Contacts_ExportRevue_Export::getMessage2($row); },
			"info lib 3" => function ($row) { return Contacts_ExportRevue_Export::getMessage3($row); },
			"info lib 4" => function ($row) { return Contacts_ExportRevue_Export::getMessage4($row); }
		);
	}
	
	function displayHeaderLine() {
		return true;
	}

	function getExportFileName($request) {
		$moduleName = $request->get('source_module');
		return str_replace(' ','_',vtranslate($moduleName, $moduleName)) . "_Export_Revue";
	}

	function getQueryOrderBy($moduleName) {
		return ' ORDER BY mailingcountry ASC, mailingzip ASC';
	}

	function getAddressToUse($row) {
		if ($row["use_address2_for_revue"]) {
			return "other";
		}

		return "mailing";
	}

	function getAddressField($row, $field) {
		return $row[$this->getAddressToUse($row) . $field];
	}

	function isForeign($row) {
		$country = $this->getAddressField($row, "country");
		return $country != "" && strtoupper($country) != "FRANCE";
	}

	function getPoBox($row) {
		//tmp display pobox from foreign mailing ??
		return ($this->isForeign($row)) ? $this->getAddressField($row, "pobox") . " " . $this->getAddressField($row, "zip") . " " . $this->getAddressField($row, "city")
			: $this->getAddressField($row, "pobox");
	}

	function getZipCode($row) {
		return ($this->isForeign($row)) ? "" : $this->getAddressField($row, "zip");
	}

	function getCityOrCountry($row) {
		return ($this->isForeign($row)) ? $this->getAddressField($row, "country") : $this->getAddressField($row, "city");
	}

	function getMessage1($row) {
		return "";//"Merci de vous réabonner (bulletin en p.9)";//tmp
	}

	function getMessage2($row) {
		return "";//"Bonjour " . $row["firstname"] . ",";//tmp
	}

	function getMessage3($row) {
		return "";
	}

	function getMessage4($row) {
		return "";
	}

	function getExportQuery($request) {//tmp ...
		$parentQuery = parent::getExportQuery($request);

		$fromPos = strpos($parentQuery, 'FROM');//tmp attention si il y a plusieurs clauses FROM
		$wherePos = strpos($parentQuery, 'WHERE');//tmp attention si il y a plusieurs clauses WHERE
		$query = substr($parentQuery, 0, $fromPos) . ", vtiger_rsnaborevues.nbexemplaires " .
				 substr($parentQuery, $fromPos, ($wherePos - $fromPos)) . " JOIN (SELECT accountid, MAX(debutabo) as debutabo
														    FROM vtiger_rsnaborevues
														    JOIN vtiger_crmentity vtiger_rsnaborevues_crmentity ON vtiger_rsnaborevues_crmentity.crmid = vtiger_rsnaborevues.rsnaborevuesid
														    WHERE vtiger_rsnaborevues_crmentity.deleted = 0
														    GROUP BY accountid
														) vtiger_rsnaborevues_max 
														    ON vtiger_rsnaborevues_max.accountid = vtiger_contactdetails.accountid
														JOIN vtiger_rsnaborevues
														    ON vtiger_rsnaborevues.accountid = vtiger_contactdetails.accountid
														    AND vtiger_rsnaborevues.debutabo =  vtiger_rsnaborevues_max.debutabo
														JOIN vtiger_rsnabotype
														    ON vtiger_rsnabotype.rsnabotype = vtiger_rsnaborevues.rsnabotype " .
				 substr($parentQuery, $wherePos);

//		echo '<br/><br/><br/>' . $query;

		return $query;
	}
}