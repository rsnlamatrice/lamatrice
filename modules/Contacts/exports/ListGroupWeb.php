<?php
/*+***********************************************************************************
	AV1511
 *************************************************************************************/

//Textes complets 	ReffFiche 	Association 	AssociationCourt 	Prenom 	Nom 	AdresseLigne2 	Adresse1 	Adresse2 	Adresse3 	CodePostal 	Ville 	Pays 	Telephone 	Fax 	Portable 	Email 	Region 	Adhesion 	SiteWeb 	Type 	NbAdherents 	Descriptif 	NomAssoEnLigne1 	CacherMail 	CacherNomEtPrenom 	CacherAdressePostale 	CacherFax 	CacherPortable 	CacherTel 	Email_priv 

class Contacts_ListGroupWeb_Export extends Contacts_ListGroupStats_Export {

	
	//tmp mailing address ??...
	function getExportStructure() {
		return array(
			"ReffFiche" =>"contact_no",
			"Association" => "grpnomllong",
			"AssociationCourt" => "grpnomllong",//tmp nom court !!!!
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
			"NomAssoEnLigne1" => "",//tmp => adresse format= CN1 => non groupe avant, sinon NC1
			"CacherMail" => "",//tmp rsnwebhide
			"CacherNomEtPrenom" => "",//tmp rsnwebhide
			"CacherAdressePostale" => "",//tmp rsnwebhide
			"CacherFax" => "",//tmp rsnwebhide
			"CacherPortable" => "",//tmp rsnwebhide
			"CacherTel" => "",//tmp rsnwebhide
			"Email_priv" => "email",//tmp ???
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