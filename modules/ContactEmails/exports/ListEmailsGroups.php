<?php
/*+***********************************************************************************
	AV1511
 *************************************************************************************/

class ContactEmails_ListEmailsGroups_Export extends Export_ExportData_Action {

	//tmp mailing address ?? ...
	function getExportStructure() {
		return array(
			"email_groupes" => "email",
			"code_postal" => "mailingzip",
			"RefFiche" => "contact_no",
			"DateExport" => function($row) { return date ("Y-m-d"); },
		);
	}
	
	function displayHeaderLine() {
		return false;
	}

	function getExportFileName($request) {
		$moduleName = $request->get('source_module');
		return "list_emails_groups";
	}

	//tmp order by zip ??
	function getQueryOrderBy($moduleName) {
		return ' ORDER BY mailingzip ASC';
	}

	function getExportQuery($request) {//tmp ...
		$parentQuery = parent::getExportQuery($request);

		$fromPos = strpos($parentQuery, 'FROM');//tmp attention si il y a plusieurs clauses FROM
		$wherePos = strpos($parentQuery, 'WHERE');//tmp attention si il y a plusieurs clauses WHERE
		$query = substr($parentQuery, 0, $fromPos) . ", vtiger_contactdetails.contact_no, vtiger_contactaddress.mailingzip " .
				 substr($parentQuery, $fromPos, ($wherePos - $fromPos)) . " LEFT JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_contactemails.contactid
				 												LEFT JOIN vtiger_contactaddress ON vtiger_contactaddress.contactaddressid = vtiger_contactdetails.contactid " .
				 substr($parentQuery, $wherePos);

		//echo '<br/><br/><br/>' . $query;

		return $query;
	}
}