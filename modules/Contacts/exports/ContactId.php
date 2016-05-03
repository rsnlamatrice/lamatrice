<?php
/*+***********************************************************************************
	AV1511
 *************************************************************************************/

class Contacts_ContactId_Export extends Export_ExportData_Action {

	// function getExportQuery(Vtiger_Request $request) {//depand of the export type...

	// }

	function getExportStructure() {
		return array(
			"Contact ID" => function($row) { return $row['contactid']; }
		);
	}

	function displayHeaderLine() {
		return false;
	}

	function getExportFileName($request) {
		$moduleName = $request->get('source_module');
		return str_replace(' ','_',vtranslate($moduleName, $moduleName)) . "_id_export";
	}

	function getExportQuery($request) {//tmp ...
		$parentQuery = parent::getExportQuery($request);

		$fromPos = strpos($parentQuery, 'FROM');//tmp attention si il y a plusieurs clauses FROM
		$wherePos = strpos($parentQuery, 'WHERE');//tmp attention si il y a plusieurs clauses WHERE
		$query = substr($parentQuery, 0, $fromPos) . ", vtiger_contactdetails.contactid " .
				 substr($parentQuery, $fromPos, ($wherePos - $fromPos)) .
				 substr($parentQuery, $wherePos);

		//echo '<br/><br/><br/>' . $query;

		return $query;
	}
}
