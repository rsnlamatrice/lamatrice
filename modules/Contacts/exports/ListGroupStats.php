<?php
/*+***********************************************************************************
	AV1511
 *************************************************************************************/

class Contacts_ListGroupStats_Export extends Export_ExportData_Action {

	//tmp mailing address ?? ...
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
			"email" => "email",
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
			"DateStatutNPAI" => function($row) { return ($row["rsnnpaidate"]) ? DateTime::createFromFormat('Y-m-d', $row["rsnnpaidate"])->format('d/m/y') : "00/00/00"; },
			"DateModification" => function($row) { return DateTime::createFromFormat('Y-m-d H:i:s', $row["modifiedtime"])->format('d/m/y'); },
			"DateModifAdresse" => function($row) { return DateTime::createFromFormat('Y-m-d', $row["mailingmodifiedtime"])->format('d/m/y'); },
		);
	}

	function getAutresAnneeAdhesion($row) {
		$adhs = explode(";", $row["adh_list"]);
		$adh_size = sizeof($adhs);

		if ($adh_size > 0 && $adhs[0] != "") {
			sort($adhs);
			$current_year = date("Y");//tmp dernière adhésion et non année en cours ?
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

	function getExportFileName($request) {
		$moduleName = $request->get('source_module');
		return "liste_groupes_pour_stats";
	}

	//tmp order by zip ??
	function getQueryOrderBy($moduleName) {
		return ' ORDER BY mailingcountry ASC, mailingzip ASC';
	}

	function getExportQuery($request) {//tmp ...
		$parentQuery = parent::getExportQuery($request);

		$fromPos = strpos($parentQuery, 'FROM');//tmp attention si il y a plusieurs clauses FROM
		$wherePos = strpos($parentQuery, 'WHERE');//tmp attention si il y a plusieurs clauses WHERE
		$query = substr($parentQuery, 0, $fromPos) . ", mc.department mailingdepartment, mc.region mailingregion, oc.department otherdepartment, oc.region otherregion, adh.adh_list, adh.max_adh " .
				 substr($parentQuery, $fromPos, ($wherePos - $fromPos)) . " LEFT JOIN (SELECT department, region, rsnzipcode FROM vtiger_rsncity GROUP BY rsnzipcode) AS mc ON vtiger_contactaddress.mailingzip = mc.rsnzipcode LEFT JOIN (SELECT department, region, rsnzipcode FROM vtiger_rsncity GROUP BY rsnzipcode) AS oc ON vtiger_contactaddress.otherzip = oc.rsnzipcode " .
				 	"LEFT JOIN (SELECT vtiger_invoice.accountid, GROUP_CONCAT(vtiger_service.productcode SEPARATOR ';') AS adh_list, MAX(vtiger_service.productcode) AS max_adh FROM  vtiger_invoice
						LEFT JOIN vtiger_inventoryproductrel ON vtiger_invoice.invoiceid = vtiger_inventoryproductrel.id
						LEFT JOIN vtiger_service ON vtiger_service.serviceid = vtiger_inventoryproductrel.productid
						WHERE productcode IS NOT NULL
						AND vtiger_service.servicecategory = 'Adhésion'
						GROUP BY vtiger_invoice.accountid) AS adh ON adh.accountid = vtiger_contactdetails.accountid " .
				 substr($parentQuery, $wherePos);

		//echo '<br/><br/><br/>' . $query;

		return $query;
	}
}