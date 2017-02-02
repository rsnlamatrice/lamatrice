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

	function getExportQuery($request) {//tmp ...
		$parentQuery = parent::getExportQuery($request);

		$fromPos = strpos($parentQuery, 'FROM');//tmp attention si il y a plusieurs clauses FROM
		$wherePos = strpos($parentQuery, 'WHERE');//tmp attention si il y a plusieurs clauses WHERE
		$query = substr($parentQuery, 0, $fromPos) . ", vtiger_producttaxrel.taxpercentage AS taxpercentage " .
				 substr($parentQuery, $fromPos, ($wherePos - $fromPos)) . 
					 "JOIN vtiger_producttaxrel ON vtiger_producttaxrel.productid = vtiger_products.productid 
					" .
				 substr($parentQuery, $wherePos);

		//echo '<br/><br/><br/>' . $query;

		return $query;
	}
}