<?php
/*+***********************************************************************************
	AV1511
 *************************************************************************************/

class Contacts_ListGroupMailing_Export extends Contacts_ListGroupStats_Export {

	function getExportStructure() {
		return array(
			"Ref" => "contact_no",
			"Ligne1" => function($row) { return $row["firstname"] . " " . $row["lastname"]; },
			"Ligne2" => "mailingstreet2",
			"Ligne3" => "mailingstreet3",
			"Ligne4" => "mailingstreet",
			"Ligne5" => "mailingpobox",
			"CodePostal" => "mailingzip",
			"Ville" => "mailingcity",
			"Pays" => function($row) { return (strtoupper($row["mailingcountry"]) == "FRANCE") ? "" : $row["mailingcountry"]; },
			"NomLongGroupe" => "grpnomllong",
			"Telephone" => "phone",
			"Fax" => "fax",
			"Portable" => "mobile",
			"e-mail" => function($row) { return Contacts_ListGroupMailing_Export::getEmailInfos($row); },
			"Région" => "mailingregion",
			"Département" => "mailingdepartment",//tmp dep number ??
			"Dernière Adhésion" => "max_adh",
			"InfoAdhésion" => function($row) { return Contacts_ListGroupMailing_Export::getInfoAdhesion($row); },
			"Site Web" => "websiteurl",
			"Type Groupe" => "grptypes",
			"Nb adhérents" => function($row) { return Contacts_ListGroupStats_Export::getNbAdhenrents($row); },
			"Descriptif" => function($row) { return Contacts_ListGroupMailing_Export::getTruncatedDescription($row); },
		);
	}

	function getInfoAdhesion($row) {
		$last_adh_year = str_replace ("ADH", "20", $row["max_adh"]);
		$curent_year = date("Y");

		if (! $last_adh_year) {
			return "MERCI D" . utf8_encode(chr(39)) . "ADHÉRER !";
		}
		
		if ($last_adh_year == $curent_year) {
			return "ADHÉSION À JOUR - Nous avons déjà reçu votre cotisation $curent_year. Merci !";
		}

		return "DERNIÈRE ADHÉSION EN $last_adh_year - Merci de faire le nécessaire.";
	}

	function getTruncatedDescription($row) {//TMP BUG IN DESCRIPTION LENGTH ??
		if (strlen($row["grpdescriptif"]) > 299) {
			return substr ( $row["grpdescriptif"], 0, 299) . "...";
		}

		return $row["grpdescriptif"];
	}

	function getRelatedsEmails($row) {
		global $adb;
		$query = "SELECT `vtiger_contactemails`.*
				FROM `vtiger_contactemails`
				JOIN `vtiger_contactdetails`
				    ON `vtiger_contactemails`.contactid = `vtiger_contactdetails`.contactid
				JOIN vtiger_crmentity vtiger_crmentity_contacts
				    ON vtiger_crmentity_contacts.crmid = vtiger_contactdetails.contactid
				JOIN vtiger_crmentity vtiger_crmentity_emails
				    ON vtiger_crmentity_emails.crmid = vtiger_contactemails.`contactemailsid`
				 WHERE  vtiger_crmentity_contacts.deleted = 0
				AND vtiger_crmentity_emails.deleted = 0
				AND `vtiger_contactemails`.`contactid` = ?";

		$params = array($row["contactid"]);

		$result = $adb->pquery($query, $params);

		if($adb->num_rows($result) === 0){
			// Error !!!
		}

		$emails = [];

		while($row = $adb->fetch_row($result)){
			$email = $row['email'];
			$emails[$email] = $row;
		}

		return $emails;
	}

	function isInListeGroupe($email_infos) {
		return strstr($email_infos["rsnmediadocuments"], "Liste Groupes") !== false;
	}

	function getEmailInfos($row) {
		$main_email = Contacts_ListGroupStats_Export::getMainEmail($row);
		$related_emails = $this->getRelatedsEmails($row);
		$in_error = False;

		$main_email_infos = $related_emails[$main_email];
		if (!$main_email_infos) {
			return $main_email;//tmp ??
		}

		$in_error = $main_email_infos["emailoptout"] != 0;
		$in_list_groupe = $this->isInListeGroupe($main_email_infos);

		$other_emails = [];

		foreach ($related_emails as $email => $infos){
		    if ($email != $main_email && $this->isInListeGroupe($infos)) {
		    	array_push($other_emails, $email);
		    }
		}

		$return_string = "";

		if ($in_error) {
			$return_string .= "en erreur ! ";
		}

		$return_string .= $main_email;

		if (!$in_list_groupe) {
			$return_string .= " (pas sur la liste des groupes)";
		}

		if (sizeof($other_emails) > 0) {
			$return_string .= " autres sur liste groupe: ";

			foreach($other_emails as $email) {
				$return_string .= $email . " ";
			}
		}

		return trim($return_string);
	}

	function getExportEncoding(Vtiger_Request $request) {
		return 'ISO-8859-1';
		//return 'ASCII';
	}
	
	function displayHeaderLine() {
		return true;
	}

	function getExportFileName($request) {
		$moduleName = $request->get('source_module');
		return "liste_groupes_pour_courrier";
	}
}