<?php
/*+***********************************************************************************
	AV1511
 *************************************************************************************/

class Contacts_RecuFiscalExportTest_Export extends Contacts_RecuFiscalNonPrel_Export { // TMP € -> check encodage ....
	//tmp check mailing ou other address !!!!!!!
	function getExportStructure() {
		return array(
			/*"Numero Ordre" => function ($row) { return Contacts_RecuFiscalPrel_Export::getRecuFiscalDisplayNumber($row); }, //Reçu n° 2014 / 010375*/
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

	function getExportFileName($request) {
		$moduleName = $request->get('source_module');
		return str_replace(' ','_',vtranslate($moduleName, $moduleName)) . "_Recu_fiscaux_pour verification";
	}
}