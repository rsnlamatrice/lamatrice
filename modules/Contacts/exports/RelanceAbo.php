<?php
/*+***********************************************************************************
	AV1511
 *************************************************************************************/

class Contacts_RelanceAbo_Export extends Contacts_ExportRevue_Export { // TMP Fields !!!!!

	//tmp check mailing ou other address !!!!!!!
	function getExportStructure() {
		return array(
			"Ref" => function ($row) { return "Ref : " . $row["contact_no"]; },
			"Ligne1" => function ($row) { return $row["firstname"] . " " . $row["lastname"]; },
			"Ligne2" => function ($row) { return Contacts_RelanceAbo_Export::getAddressField($row, "street2"); },
			"Complément" => function ($row) { return Contacts_RelanceAbo_Export::getAddressField($row, "street3"); },
			"Adresse4" => function ($row) { return Contacts_RelanceAbo_Export::getAddressField($row, "street"); },
			"Adresse5" => function ($row) { return Contacts_RelanceAbo_Export::getAddressField($row, "pobox"); },
			"CodePostal" => function ($row) { return Contacts_RelanceAbo_Export::getAddressField($row, "zip"); },
			"Ville" => function ($row) { return Contacts_RelanceAbo_Export::getAddressField($row, "city"); },
			"Pays" => function ($row) { return Contacts_RelanceAbo_Export::getAddressField($row, "country"); },
			"" => function ($row) { return "Bonjour " . $row["firstname"]; },
		);
	}
	
	function displayHeaderLine() {
		return true;
	}

	function getExportFileName($request) {
		$moduleName = $request->get('source_module');
		return str_replace(' ','_',vtranslate($moduleName, $moduleName)) . "_Relance_Abo";
	}
}