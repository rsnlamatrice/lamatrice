<?php
/*+***********************************************************************************
	AV1511
 *************************************************************************************/

class Invoice_Lite_Export extends Export_ExportData_Action {
	//tmp plusieur ligne par facture (une par produit...)

	// function getExportQuery(Vtiger_Request $request) {//depand of the export type...

	// }

	function getExportStructure() {
		return array(
			"Numero facture" => "invoice_no",
			"Libellé" => "subject",
			"Total" => "total",
		);
	}

	function displayHeaderLine() {
		return true;
	}

	function getExportFileName($request) {
		$moduleName = $request->get('source_module');
		return str_replace(' ','_',vtranslate($moduleName, $moduleName)) . "_Export_Test";
	}

	function getExportQuery($request) {
		$parentQuery = parent::getExportQuery($request);
		$orderByPos = strpos($parentQuery, 'ORDER BY');
		$query = substr($parentQuery, 0, $orderByPos) . " GROUP BY invoice_no " .
				 substr($parentQuery, $orderByPos);

		return $query;
	}
}
