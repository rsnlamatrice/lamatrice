<?php
/*+***********************************************************************************
	AV1511
 *************************************************************************************/

class Products_ProductsAll_Export extends Export_ExportData_Action {
	function getExportStructure() {
		return array(
			"Product Name" => "productname",
			"Product Active" => "discontinued",
			"Sales Start Date" => "sales_start_date",
			"Part Number" => "productcode",
			"Sales End Date" => "sales_end_date",
			"Product Category" => "productcategory",
			"Compte Achat" => "compteachat",
			"Section analytique" => "sectionanal",
			"GL Account" => "glacct",
			"Last Modified By" => "modifiedby",//mettre un id à la place du nom?
			"Modified Time" => "modifiedtime",
			"Created Time" => "createdtime",
			"Vendor Name" => "vendor_id",
			"Vendor PartNo" => "vendor_part_no",
			"Support Expiry Date" => "expiry_date",
			"Support Start Date" => "start_date",
			"Manufacturer" => "manufacturer",
			"Website" => "website",
			"Product Sheet" => "productsheet",
			"Product No" => "product_no",
			"Handler" => "smownerid",//mettre un id à la place du nom?
			"Unit Price" => function($row) { return Products_ProductsAll_Export::formatFloatVal( round($row["unit_price"], 2) ); },
			"Unit Price TTC" => function($row) { return Products_ProductsAll_Export::formatFloatVal( round($row["unit_price"] * (1 + $row["taxpercentage"]/100), 2) ); },
			"Tax Class" => "taxclass",
			"Taux Class" => "taxpercentage",
			"Tax ID" => "taxid",
			"Purchase price" => function($row) { return Products_ProductsAll_Export::formatFloatVal($row["purchaseprice"]); },
			"Usage Unit" => "usageunit",
			"Qty/Unit" => function($row) { return intval($row["qty_per_unit"]); },
			"Qty In Stock" => function($row) { return intval($row["qtyinstock"]); },
			"Reorder Level" => "reorderlevel",
			"Qty In Demand" => "qtyindemand",
			"Description" => "description",
		);
	}

	function formatFloatVal($val) {
		return str_replace(".", ",", strval(floatval($val)));
	}

	function displayHeaderLine() {
		return true;
	}

	function getExportFileName($request) {
		$moduleName = $request->get('source_module');
		return "export_produits";
	}

	function getCSVSeparator(){
		return ";";
	}

	function getExportQuery($request) {//tmp ...
		$parentQuery = parent::getExportQuery($request);

		$fromPos = strpos($parentQuery, 'FROM');//tmp attention si il y a plusieurs clauses FROM
		$wherePos = strpos($parentQuery, 'WHERE');//tmp attention si il y a plusieurs clauses WHERE
		$query = substr($parentQuery, 0, $fromPos) . ", vtiger_producttaxrel.taxpercentage AS taxpercentage, vtiger_producttaxrel.taxid AS taxid " .
				 substr($parentQuery, $fromPos, ($wherePos - $fromPos)) .
					 "JOIN vtiger_producttaxrel ON vtiger_producttaxrel.productid = vtiger_products.productid
					" .
				 substr($parentQuery, $wherePos);

		return $query;
	}
}
