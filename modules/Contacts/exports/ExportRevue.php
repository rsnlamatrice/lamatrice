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
			"Nb exemplaires" => function ($row) { return Contacts_ExportRevue_Export::getNbExemplaires($row); },
			"Mode envoi" => "",//tmp to check ??
			"info lib 1" => function ($row) { return Contacts_ExportRevue_Export::getMessage1($row); },
			"info lib 2" => function ($row) { return Contacts_ExportRevue_Export::getMessage2($row); },
			"info lib 3" => function ($row) { return Contacts_ExportRevue_Export::getMessage3($row); },
			"info lib 4" => function ($row) { return Contacts_ExportRevue_Export::getMessage4($row); },
			"fin abo" => "finabo",
			"abo type" => "rsnabotype",
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
		if (strpos($row["rsnabotype"], "découverte") || strpos($row["rsnabotype"], "remerciement")) {
			return "Numéro offert. Merci !";
		} else if (!strpos($row["rsnabotype"], "Ne pas abonner") &&  !strpos($row["rsnabotype"], "Non abonné") && !$this->isAbo()) {
			return "Merci de vous réabonner.";
		}

		return "";
	}

	function isAbo($row) {
		$today = time() - 31 * 24 * 60 * 60;//aujourd'hui - 1 mois
		$finabo = strtotime($row["finabo"]);
		return $finabo >= $today;//$finabo >= $today;
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

	function getNbExemplaires($row) {
		//tmp Attention -> checker si le dernier abonnement est encore en cours ou si la derniere revue à été recu, sinon mettre 1 (exemple ancien abonné avec une revue de remerciement (ex: C286786)...)...
		//tmp Attention, ne pas se fier au champs "is_abo" (pas necessairement à jour...)
		
		return ($row["nbexemplaires"] /*&& $this->isAbo()*/) ? $row["nbexemplaires"] : 1;
	}

	function getExportQuery($request) {//tmp ...
		$parentQuery = parent::getExportQuery($request);

		$fromPos = strpos($parentQuery, 'FROM');//tmp attention si il y a plusieurs clauses FROM
		$wherePos = strpos($parentQuery, 'WHERE');//tmp attention si il y a plusieurs clauses WHERE
		$query = substr($parentQuery, 0, $fromPos) . ", vtiger_rsnaborevues.nbexemplaires, vtiger_rsnaborevues.debutabo, vtiger_rsnaborevues.finabo, vtiger_rsnaborevues.rsnabotype " .
				 substr($parentQuery, $fromPos, ($wherePos - $fromPos)) . " LEFT JOIN (SELECT accountid, MAX(debutabo) as debutabo
														    FROM vtiger_rsnaborevues
														    JOIN vtiger_crmentity vtiger_rsnaborevues_crmentity ON vtiger_rsnaborevues_crmentity.crmid = vtiger_rsnaborevues.rsnaborevuesid
														    WHERE vtiger_rsnaborevues_crmentity.deleted = 0
														    GROUP BY accountid
														) vtiger_rsnaborevues_max 
														    ON vtiger_rsnaborevues_max.accountid = vtiger_contactdetails.accountid
														LEFT JOIN vtiger_rsnaborevues
														    ON vtiger_rsnaborevues.accountid = vtiger_contactdetails.accountid
														    AND vtiger_rsnaborevues.debutabo =  vtiger_rsnaborevues_max.debutabo
														LEFT JOIN vtiger_rsnabotype
														    ON vtiger_rsnabotype.rsnabotype = vtiger_rsnaborevues.rsnabotype " .
				 substr($parentQuery, $wherePos);

//		echo '<br/><br/><br/>' . $query;

		return $query;
	}
}