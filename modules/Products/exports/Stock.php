<?php
/*+***********************************************************************************
	AV1511
 *************************************************************************************/

//code produit; désignation; prix d'achat; quantité en stock; quantité en dépôt vente.
class Products_Stock_Export extends Products_ProductsAll_Export {
	function getExportStructure() {
		return array(
			"Part Number" => "productcode",
			"Product Name" => "productname",
			"Purchase price" => function($row) { return Products_ProductsAll_Export::formatFloatVal($row["purchaseprice"]); },
			"Sell price" => function($row) { return Products_ProductsAll_Export::formatFloatVal( round($row["unit_price"], 2) ); },
			"Sell price TTC" => function($row) { return Products_ProductsAll_Export::formatFloatVal( round($row["unit_price"] * (1 + $row["taxpercentage"]/100), 2) ); },
			"tax" => "taxpercentage",
			"Qty In Stock" => function($row) { return intval($row["qtyinstock"]); },
			"Qty In Demand" => "qtyindemand",
		);
	}
	
	function displayHeaderLine() {
		return true;
	}

	function getExportFileName($request) {
		$moduleName = $request->get('source_module');
		return "export_produits_pour_stock";
	}
}