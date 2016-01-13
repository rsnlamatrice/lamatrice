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
			"Unit Price" => function($row) { return Products_ProductsAll_Export::formatFloatVal($row["unit_price"]); },
			"Tax Class" => "taxclass",
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
}