<?php
/*+***********************************************************************************
	AV1511
 *************************************************************************************/

class Contacts_ExportWebMailing_Export extends Export_ExportData_Action {

	//tmp mailing address ?? ...
	function getExportStructure() {
		return array(
			"ref" => "contact_no",
			"email" => "export_email"
		);
	}

	function getAutresAnneeAdhesion($row) {
		$adhs = explode(";", $row["adh_list"]);
		$adh_size = sizeof($adhs);

		if ($adh_size > 0 && $adhs[0] != "") {
			rsort($adhs);
			$current_year = date("Y");//tmp dernière adhésion et non année en cours ? -> utiliser la date de l'AG !! -> ne pas afficher les adhésion pour l'année après AG (pas afficher ADH16 avant ag 16)
			$curent_adh = false;
			$precedement_adhs = [];

			for ($i = 0; $i < $adh_size; ++$i) {
				$year = intval(str_replace("ADH", "20", $adhs[$i]));

				if ($year == $current_year) {
					$curent_adh = true;
				} else {
					array_push($precedement_adhs, $year);
				}
			}

			$return_value = "";

			if ($curent_adh) {
				$return_value .= "Adhérent " . $current_year . " ";
			}
			if (sizeof($precedement_adhs) > 0) {
				$return_value .= ($curent_adh) ? "( et " : "( précédemment en ";
				for ($i = 0; $i < sizeof($precedement_adhs); ++$i) {
					$return_value .= $precedement_adhs[$i] . " ";
				}
				$return_value .= ")";
			}

			return $return_value;
		}

		return "";
	}
	
	function displayHeaderLine() {
		return true;
	}

	function getNbAdhenrents($row) {
		return ($row["grpnbremembres"] > 0) ? $row["grpnbremembres"] : "";
	}

	function getWebSite($row) {
		return (strlen($row["websiteurl"]) < 3 || strrpos($row["websiteurl"], "http") === 0) ? $row["websiteurl"] : "http://" . $row["websiteurl"];
	}

	function getExportFileName($request) {
		$moduleName = $request->get('source_module');
		return "export_emails";
	}

	//tmp order by zip ??
	function getQueryOrderBy($moduleName) {
		return ' ORDER BY mailingcountry ASC, mailingzip ASC';
	}

	function getMainEmail($row) {
		return ($row['emailoptout']) ? "" : $row['email'];
	}

	function getExportQuery($request) {//tmp ...
		$parentQuery = parent::getExportQuery($request);

		$fromPos = strpos($parentQuery, 'FROM');//tmp attention si il y a plusieurs clauses FROM
		$wherePos = strpos($parentQuery, 'WHERE');//tmp attention si il y a plusieurs clauses WHERE
		$query = substr($parentQuery, 0, $fromPos) . ", export_emails.email as export_email " .
				 substr($parentQuery, $fromPos, ($wherePos - $fromPos)) . 
					 "JOIN (
					/* adresse email principale */
					SELECT DISTINCT `vtiger_contactemails`.contactid, `vtiger_contactemails`.email
					FROM `vtiger_contactemails`
					JOIN `vtiger_contactdetails`
					    ON `vtiger_contactemails`.contactid = `vtiger_contactdetails`.contactid
					JOIN vtiger_crmentity vtiger_crmentity_contacts
					    ON vtiger_crmentity_contacts.crmid = vtiger_contactdetails.contactid
					JOIN vtiger_crmentity vtiger_crmentity_emails
					    ON vtiger_crmentity_emails.crmid = vtiger_contactemails.`contactemailsid`
					 WHERE  vtiger_crmentity_contacts.deleted = 0
					AND vtiger_crmentity_emails.deleted = 0
					)AS export_emails ON export_emails.contactid = vtiger_contactdetails.contactid
					" .
				 substr($parentQuery, $wherePos);

		//echo '<br/><br/><br/>' . $query;

		return $query;
	}
}