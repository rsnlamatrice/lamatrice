<?php
/*+***********************************************************************************
	AV1511
 *************************************************************************************/

class Contacts_RecuFiscalPrel_Export extends Contacts_RecuFiscalNonPrel_Export { // TMP Adresse !!! -> Should Be recu fiscal et non pas Abo !!!!!!!!!!
	//tmp check mailing ou other address !!!!!!!
	function getExportStructure() {
		return array(
			"Numero Ordre" => "", //Reçu n° 2014 / 010375
			"Ref" => "contact_no",
			"Ligne2" => "grpnomllong",
			"Prenom-Nom" => function ($row) { return $row["firstname"] . " " . $row["lastname"]; },
			"Ligne3" => function ($row) { return Contacts_RecuFiscalPrel_Export::getAddressField($row, "street2"); },
			"Ligne4" => function ($row) { return Contacts_RecuFiscalPrel_Export::getAddressField($row, "street3"); },
			"Ligne5" => function ($row) { return Contacts_RecuFiscalPrel_Export::getAddressField($row, "street"); },
			"BP" => function ($row) { return Contacts_RecuFiscalPrel_Export::getAddressField($row, "pobox"); },
			"CP-Ville" => function ($row) { return Contacts_RecuFiscalPrel_Export::getAddressField($row, "zip") . " " .
													Contacts_RecuFiscalPrel_Export::getAddressField($row, "city"); },
			"Pays" => function ($row) { return Contacts_RecuFiscalPrel_Export::getAddressField($row, "country"); },
			"Salutations" => function ($row) { return Contacts_RecuFiscalPrel_Export::getSalutation($row); },
			"Dons" => function ($row) { return Contacts_RecuFiscalPrel_Export::getTotalDons($row) /*. " €"*/; },
			"Dons sans prel" => function ($row) { return Contacts_RecuFiscalPrel_Export::getDons($row) /*. " €"*/; },//tmp
			"Dons prel" => function ($row) { return Contacts_RecuFiscalPrel_Export::getPrel($row) /*. " €"*/; },//tmp
			"Dons en lettres" => function ($row) { return Contacts_RecuFiscalPrel_Export::getTotalDonsLetter($row). " euros"; },
			"Dons après déduction" => function ($row) { return Contacts_RecuFiscalPrel_Export::getRealDons($row) /*. " €"*/; },
			"pvt_actuel" => function ($row) { return Contacts_RecuFiscalPrel_Export::getPrelevementSetence($row) /*. " €"*/; },//je suis actuellement en prélèvements de 10 €  par mois,
			"oui augmentation" => function ($row) { return "Je souhaite augmenter mon prélèvement de:"; },// 
			"suggestion augmentation" => function ($row) { return Contacts_RecuFiscalPrel_Export::getSuggestionSentence($row); },// TMP check value ... // O 1 €    O 2 €    O 3 €    O Autres:    .... € 
		);
	}

	function getPrelevementSetence($row) {
		$periodicite = "";

		if (strpos($row["prel_periode"], "Mensuel") !== false) {
			$periodicite = "mois";
		} else if (strpos($row["prel_periode"], "Bimestriel") !== false) {
			$periodicite = "bimestre";
		} else if (strpos($row["prel_periode"], "Trimestriel") !== false) {
			$periodicite = "trimestre";
		} else if (strpos($row["prel_periode"], "Semestriel") !== false) {
			$periodicite = "semestre";
		} else if (strpos($row["prel_periode"], "Annuel") !== false) {
			$periodicite = "an";
		} else {
			$periodicite = " ? ";
		}

		return "Je suis actuellement en prélèvements de " . $row["prel_amount"] . " euros  par " . $periodicite; // tmp périodicity ...
	}

	function getSuggestionSentence($row) {
		$value1 = (int) $row["prel_amount"] * 0.1;//tmp ammount ...
		$value2 = (int) $row["prel_amount"] * 0.2;//tmp amount ...
		$value3 = (int) $row["prel_amount"] * 0.3;//tmp amount ...
		return "O $value1 €    O $value2 €    O $value3 €    O Autres:    .... € ";
	}
	
	function displayHeaderLine() {
		return true;
	}

	function getExportFileName($request) {
		$moduleName = $request->get('source_module');
		return str_replace(' ','_',vtranslate($moduleName, $moduleName)) . "_Relance_Abo";
	}

	function getSalutation($row) {
		$return_value = "Bonjour";

		if ($row["firstname"] != "") {
			$return_value .= " " . $row["firstname"];
		}

		$return_value .= ",";

		return $return_value;
	}

	function getExportQuery($request) {//tmp ...
		$query = $parentQuery = parent::getExportQuery($request);
		$current_year = date("Y") - 1;
		$date_debut = $current_year . "-01-01";
		$date_fin = $current_year . "-12-31";
		// echo $query;
		$fromPos = strpos($parentQuery, 'FROM');//tmp attention si il y a plusieurs clauses FROM
		$wherePos = strpos($parentQuery, 'WHERE');//tmp attention si il y a plusieurs clauses WHERE
		$orderbyPos = strrpos($parentQuery, 'ORDER BY');//tmp attention si il y a plusieurs clauses ORDER BY

		$query = substr($parentQuery, 0, $fromPos) . ", vtiger_rsnprelevements.montant AS prel_amount, vtiger_rsnprelevements.periodicite AS prel_periode " .
				 substr($parentQuery, $fromPos, ($wherePos - $fromPos)) . " INNER JOIN vtiger_rsnprelevements ON vtiger_contactdetails.accountid = vtiger_rsnprelevements.accountid
														INNER JOIN vtiger_crmentity vtiger_rsnprelevements_crmentity ON vtiger_rsnprelevements_crmentity.crmid = vtiger_rsnprelevements.rsnprelevementsid AND vtiger_rsnprelevements.etat = 0 " .
				 substr($parentQuery, $wherePos, ($orderbyPos - $wherePos)) . " AND vtiger_rsnprelevements_crmentity.deleted = 0 " .
														" GROUP BY vtiger_crmentity.crmid " .
				 substr($parentQuery, $orderbyPos);

		// echo '<br/><br/><br/>' . $query;

		return $query;
	}
}