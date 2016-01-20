<?php
/*+***********************************************************************************
	AV1511
 *************************************************************************************/

class Contacts_ListGroupMailing_Export extends Contacts_ListGroupStats_Export {

	//tmp mailing address ??...
	function getExportStructure() {
		return array(
			"Ref" => "contact_no",
			"Nom du groupe" => "grpnomllong",
			"Prenom" => "firstname",
			"Nom" => "lastname",
			"adresse 1" => "mailingstreet3",
			"adresse 2" => "mailingstreet",
			"adresse 3" => "mailingstreet2",
			"boite postale" => "mailingpobox",
			"zipcode" => "mailingzip",
			"ville" => "mailingcity",
			"pays" => "mailingcountry",
			"telephone" => "phone",
			"fax" => "fax",
			"portable" => "mobile",
			"email" => function($row) { return Contacts_ListGroupStats_Export::getMainEmail($row); },
			"région" => "mailingregion",
			"département" => "mailingdepartment",//tmp dep number ??
			"Dernière Adhésion" =>"max_adh",
			"AutreAnnéeAdhésion" => function($row) { return Contacts_ListGroupStats_Export::getAutresAnneeAdhesion($row); },
			"site web" => "websiteurl",
			"type de groupe" => "grptypes",
			"Nb adhérents" => "grpnbremembres",
			"Descriptif" => "grpdescriptif",
			"Remarques" => "description",
			"StatutNPAI" => "rsnnpai",
			"DateStatutNPAI" => function($row) { return ($row["rsnnpaidate"]) ? DateTime::createFromFormat('Y-m-d', $row["rsnnpaidate"])->format('d/m/y') : ""; },
			"DateModification" => function($row) { return ($row["modifiedtime"]) ? DateTime::createFromFormat('Y-m-d H:i:s', $row["modifiedtime"])->format('d/m/y') : ""; },
			"DateModifAdresse" => function($row) { return ($row["mailingmodifiedtime"]) ? DateTime::createFromFormat('Y-m-d', $row["mailingmodifiedtime"])->format('d/m/y') : ""; },
		);
	}
	
	function displayHeaderLine() {
		return true;
	}

	function getExportFileName($request) {
		$moduleName = $request->get('source_module');
		return "liste_groupes_pour_web";
	}
}

/*
TMP todo 
Pour le courrier adhésion il faut :
La référence
L'adresse postale
Le nom du contact (si y a!)
les Tels (fixe et portable)
le Type de groupe
le nom complet
le site internet
le ou les E-mail
Nb d'adhérents
Le descriptif
l'info sur l'adhésion selon le courrier envoyé: dernière adhésion en ..., merci de réadhérer, merci d'adhérer...., adhésion à jour

*/