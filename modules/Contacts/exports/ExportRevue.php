<?php
/*+***********************************************************************************
	AV1511
 *************************************************************************************/

class Contacts_ExportRevue_Export extends Export_ExportData_Action {

	function getExportStructure() {
		return array(
			"Contact ID" => "contact_no",
			"Empty" => "",
			"Nom" => "lastname",
			"Prenom" => "firstname",
			"structure name" => "",
			"adresse 1" => "mailingstreet3",
			"adresse 2" => "mailingstreet",
			"adresse 3" => "mailingstreet2",
			"zipcode" => "mailingzip",
			"ville" => "mailingcity",
			"pays" => "mailingcountry",
			"message personalisé" => function($row) { return "Merci de vous réabonner (bulletin en p.9)"; },//tmp check witch message must be displayed ...
			"bonjour" => function ($row) { return "Bonjour " . $row["firstname"] . ","; }//tmp check if structure or person ...
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
}