<?php
/*+***********************************************************************************
	AV1511
 *************************************************************************************/

//Textes complets 	ReffFiche 	Association 	AssociationCourt 	Prenom 	Nom 	AdresseLigne2 	Adresse1 	Adresse2 	Adresse3 	CodePostal 	Ville 	Pays 	Telephone 	Fax 	Portable 	Email 	Region 	Adhesion 	SiteWeb 	Type 	NbAdherents 	Descriptif 	NomAssoEnLigne1 	CacherMail 	CacherNomEtPrenom 	CacherAdressePostale 	CacherFax 	CacherPortable 	CacherTel 	Email_priv 

class Contacts_ListGroupWeb_Export extends Contacts_ListGroupStats_Export {

	
	//tmp mailing address ??...
	function getExportStructure() {
		return array(
			"ReffFiche" => function($row) { return (substr($row["contact_no"], 0, 1) == "C") ? substr($row["contact_no"], 1) : $row["contact_no"]; },
			"Association" => "grpnomllong",
			"AssociationCourt" => "grpnomcourt",//tmp nom court ??
			"Prenom" => "firstname",
			"Nom" => "lastname",
			"AdresseLigne2" => "mailingstreet2",
			"Adresse1" => "mailingstreet3",
			"Adresse2" => "mailingstreet",
			"Adresse3" => "mailingpobox",
			"CodePostal" => "mailingzip",
			"Ville" => "mailingcity",
			"Pays" => "mailingcountry",
			"Telephone" => "phone",
			"Fax" => "fax",
			"Portable" => "mobile",
			"Email" => "email",
			"Region" => "mailingregion",
			"Adhesion" => function($row) { return Contacts_ListGroupStats_Export::getAutresAnneeAdhesion($row); },
			"SiteWeb"=> "websiteurl",
			"Type" => "grptypes",
			"NbAdherents" => "grpnbremembres",
			"Descriptif" => "grpdescriptif",
			"NomAssoEnLigne1" => function($row) { return ($row["mailingaddressformat"] == "CN1") ? 1 : 0; },
			"CacherMail" => function($row) {return (Contacts_ListGroupWeb_Export::hideField($row, "Email")) ? 1 : 0; },
			"CacherNomEtPrenom" => function($row) {return (Contacts_ListGroupWeb_Export::hideField($row, "Nom et prénom")) ? 1 : 0; },
			"CacherAdressePostale" => function($row) {return (Contacts_ListGroupWeb_Export::hideField($row, "Adresse postale")) ? 1 : 0; },
			"CacherFax" => function($row) {return (Contacts_ListGroupWeb_Export::hideField($row, "Fax")) ? 1 : 0; },
			"CacherPortable" => function($row) {return (Contacts_ListGroupWeb_Export::hideField($row, "Portable")) ? 1 : 0; },
			"CacherTel" => function($row) {return (Contacts_ListGroupWeb_Export::hideField($row, "Téléphone")) ? 1 : 0; },
			"Email_priv" => "email",
		);
	}

	function hideField($row, $field) {
		$fieldsToHide = explode(" |##| ", html_entity_decode($row["webhide"]));//tmp cache??

		return in_array($field, $fieldsToHide);
	}

	function displayHeaderLine() {
		return false;
	}

	function getExportFileName($request) {
		$moduleName = $request->get('source_module');
		return "liste_groupes_pour_web";
	}

	function getCSVSeparator(){
		return ";";	
	}

	function cleanData($value) {
		return htmlspecialchars($value);
	}
}