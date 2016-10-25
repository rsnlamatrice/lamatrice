<?php
/*+***********************************************************************************
	AV1511
 *************************************************************************************/

class Invoice_SaleManagement_Export extends Export_ExportData_Action {
	//tmp plusieur ligne par facture (une par produit...)

	// function getExportQuery(Vtiger_Request $request) {//depand of the export type...

	// }
	static $taxe_20 			= 0;
	static $taxe_7 				= 0;
	static $taxe_5_5 			= 0;
	static $taxe_2_1 			= 0;
	static $taxe_total 			= 0;
	static $total 				= 0;
	static $net_total 			= 0;
	static $curent_invoice_id 	= 0;

	function getExportStructure() {
		return array(
			"Facture" => "invoice_no",//tmp enlever COG\Y\Y0 ou FAC\Y\Y0 !! ????
			"Date" => "invoicedate",
			"TVA 20,00 %" => function ($row) { return Invoice_SaleManagement_Export::get_taxe_20($row); },
			"TVA 7,00 %" => function ($row) { return Invoice_SaleManagement_Export::get_taxe_7($row); },
			"TVA 5,50 %" => function ($row) { return Invoice_SaleManagement_Export::get_taxe_5_5($row); },
			"TVA 2,10 %" => function ($row) { return Invoice_SaleManagement_Export::get_taxe_2_1($row); },
			"Total TVA" => function ($row) { return Invoice_SaleManagement_Export::get_taxe_total($row); },
			"Total HT" => function ($row) { return Invoice_SaleManagement_Export::get_total($row); },
			"Total TTC" => function ($row) { return Invoice_SaleManagement_Export::get_net_total($row); },
		);
	}

	function update_data($invoice_id) {
		if ($invoice_id !== self::$curent_invoice_id) {
			//do update

			self::$taxe_20 = 42;
			self::$curent_invoice_id = $invoice_id;
		}
	}

	function get_taxe_20($row) {
		//return $row["invoice_no"] !== self::$curent_invoice_id;
		$this->update_data($row["invoice_no"]);

		return self::$taxe_20;
	}

	function get_taxe_7($row) {
		$this->update_data($row["invoice_no"]);
		
		return self::$taxe_7;
	}
	function get_taxe_5_5($row) {
		$this->update_data($row["invoice_no"]);
		
		return self::$taxe_5_5;
	}
	function get_taxe_2_1($row) {
		$this->update_data($row["invoice_no"]);
		
		return self::$taxe_2_1;
	}
	function get_taxe_total($row) {
		$this->update_data($row["invoice_no"]);
		
		return self::$taxe_total;
	}
	function get_total($row) {
		$this->update_data($row["invoice_no"]);
		
		return self::$total;
	}
	function get_net_total($row) {
		$this->update_data($row["invoice_no"]);
		
		return self::$net_total;
	}

	function displayHeaderLine() {
		return true;
	}

	function getExportFileName($request) {
		$moduleName = $request->get('source_module');
		return str_replace(' ','_',vtranslate($moduleName, $moduleName)) . "_Export_Ventes_Gestion";
	}

	function getExportQuery($request) {
		$parentQuery = parent::getExportQuery($request);
		$orderByPos = strpos($parentQuery, 'ORDER BY');
		$query = substr($parentQuery, 0, $orderByPos) . " GROUP BY invoice_no " .
				 substr($parentQuery, $orderByPos);

		return $query;
	}
}
