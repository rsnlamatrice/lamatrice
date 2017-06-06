<?php
/*+***********************************************************************************
	AV1511
 *************************************************************************************/

class Contacts_CourrierCampagne_Export extends Export_ExportData_Action { // TMP Fields !!!!!

	//tmp check mailing ou other address !!!!!!!
	function getExportStructure() {
		return array(
			"Ref" => function ($row) { return $row["contact_no"]; },
			"Salutation" => function ($row) { return 'Bonjour'.($row['isgroup']==0 ?  ' '.$row["firstname"] : '').','; },
			"Ligne1" => function ($row) { return $row["firstname"] . " " . $row["lastname"]; },
			"Ligne2" => function ($row) { return Contacts_CourrierCampagne_Export::getAddressField($row, "street2"); },
			"Complément" => function ($row) { return Contacts_CourrierCampagne_Export::getAddressField($row, "street3"); },
			"Adresse4" => function ($row) { return Contacts_CourrierCampagne_Export::getAddressField($row, "street"); },
			"Adresse5" => function ($row) { return Contacts_CourrierCampagne_Export::getAddressField($row, "pobox"); },
			"CodePostal" => function ($row) { return Contacts_CourrierCampagne_Export::getAddressField($row, "zip"); },
			"Ville" => function ($row) { return Contacts_CourrierCampagne_Export::getAddressField($row, "city"); },
			"Pays" => function ($row) { return Contacts_CourrierCampagne_Export::getAddressField($row, "country"); },
		);
	}

	function displayHeaderLine() {
		return true;
	}

	function getExportFileName($request) {
		$moduleName = $request->get('source_module');
		return str_replace(' ','_',vtranslate($moduleName, $moduleName)) . "_CourrierCampagne";
	}

        function getAddressField($row, $field) {
		return $row[$this->getAddressToUse($row) . $field];
	}

        function getAddressToUse($row) {
		return "mailing";
	}

        function getExportQuery($request) {//tmp ...
		$parentQuery = parent::getExportQuery($request);

		$fromPos = strpos($parentQuery, 'FROM');//tmp attention si il y a plusieurs clauses FROM
		$wherePos = strpos($parentQuery, 'WHERE');//tmp attention si il y a plusieurs clauses WHERE
		$query = substr($parentQuery, 0, $fromPos) . ", vtiger_contactdetails.contactid, vtiger_contactdetails.contact_no, vtiger_contactdetails.isgroup, vtiger_contactdetails.firstname, vtiger_contactdetails.lastname " .
				 substr($parentQuery, $fromPos, ($wherePos - $fromPos)) .
				 substr($parentQuery, $wherePos);

		return $query;
	}
}