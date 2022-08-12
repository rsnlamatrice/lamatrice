<?php

class Invoice_AnalyseResultatFinancier_Export extends Invoice_SaleManagement_Export {

	function getExportStructure() {
		return array(
			"Numero facture" => "invoice_no",
			"Libellé" => "subject",
			"Total TTC" => function ($row) { return Invoice_AnalyseResultatFinancier_Export::formatFloatVal(Invoice_AnalyseResultatFinancier_Export::get_total_ttc($row)); },
			"Total Produit" => function ($row) { return Invoice_AnalyseResultatFinancier_Export::formatFloatVal(Invoice_AnalyseResultatFinancier_Export::get_total_produit($row)); },
			"Total Service" => function ($row) { return Invoice_AnalyseResultatFinancier_Export::formatFloatVal(Invoice_AnalyseResultatFinancier_Export::get_total_service($row)); },
			"Total Don" => function ($row) { return Invoice_AnalyseResultatFinancier_Export::formatFloatVal(Invoice_AnalyseResultatFinancier_Export::get_total_don($row)); },
		);
	}

	/*
	* Le total TTC
	*/
	function get_total_ttc($row){
		return $row['total'];
	}

	/*
	* Le total correspondant uniquement aux produits
	*/
	function get_total_produit($row){
		return $row['total_product'];
	}

	/*
	* Le total correspondant uniquement aux dons
	*/
	function get_total_don($row){
		return $row['total_don'];
	}

	/*
	* Le total correspondant uniquement aux services : comprend les dons et les frais de portq
	*/
	function get_total_service($row){
		return $row['total_service'];
	}

	function displayHeaderLine() {
		return true;
	}

	function getExportFileName($request) {
		$moduleName = $request->get('source_module');
		return str_replace(' ','_',vtranslate($moduleName, $moduleName)) . "_Export_Analyse_Resultat_Financier";
	}

	
}
