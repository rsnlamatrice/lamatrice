<?php
/*+***********************************************************************************
	AV1511
 *************************************************************************************/

class Invoice_Lite_Export extends Invoice_SaleManagement_Export {
	//tmp plusieur ligne par facture (une par produit...)

	// function getExportQuery(Vtiger_Request $request) {//depand of the export type...

	// }

	function getExportStructure() {
		return array(
			"Numero facture" => "invoice_no",
			"Libellé" => "subject",
			"Total HT" => function ($row) { return Invoice_Lite_Export::formatFloatVal(Invoice_Lite_Export::get_total($row)); },
			"Total TTC" => function ($row) { return Invoice_Lite_Export::formatFloatVal(Invoice_Lite_Export::get_net_total($row)); } 
		);
	}

	function displayHeaderLine() {
		return true;
	}

	function getExportFileName($request) {
		$moduleName = $request->get('source_module');
		return str_replace(' ','_',vtranslate($moduleName, $moduleName)) . "_Export_Test";
	}

	/*function getExportQuery($request) {
		$parentQuery = parent::getExportQuery($request);
		$orderByPos = strpos($parentQuery, 'ORDER BY');
		$query = substr($parentQuery, 0, $orderByPos) . " GROUP BY invoice_no " .
				 substr($parentQuery, $orderByPos);

		return $query;
	}*/
}
