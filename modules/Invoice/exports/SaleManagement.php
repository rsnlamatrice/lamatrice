<?php
/*+***********************************************************************************
	AV1610
 *************************************************************************************/

class Invoice_SaleManagement_Export extends Export_ExportData_Action {
	var $tax_20 			= 0;
	var $tax_7 			= 0;
	var $tax_5_5 			= 0;
	var $tax_2_1 			= 0;
	var $tax_total 		= 0;
	var $total 				= 0;
	var $net_total 			= 0;
	var $curent_invoice_id 	= 0;

	var $focus;
	var $associated_products;
	var $moduleName = "Invoice";

	function getExportStructure() {
		return array(
			"Facture" => "invoice_no",//tmp enlever COG\Y\Y0 ou FAC\Y\Y0 !! ????
			"Date" => "invoicedate",
			"TVA 20,00 %" => function ($row) { return Invoice_SaleManagement_Export::formatFloatVal(Invoice_SaleManagement_Export::get_tax_20($row)); },
			"TVA 7,00 %" => function ($row) { return Invoice_SaleManagement_Export::formatFloatVal(Invoice_SaleManagement_Export::get_tax_7($row)); },
			"TVA 5,50 %" => function ($row) { return Invoice_SaleManagement_Export::formatFloatVal(Invoice_SaleManagement_Export::get_tax_5_5($row)); },
			"TVA 2,10 %" => function ($row) { return Invoice_SaleManagement_Export::formatFloatVal(Invoice_SaleManagement_Export::get_tax_2_1($row)); },
			"Total TVA" => function ($row) { return Invoice_SaleManagement_Export::formatFloatVal(Invoice_SaleManagement_Export::get_tax_total($row)); },
			"Total HT" => function ($row) { return Invoice_SaleManagement_Export::formatFloatVal(Invoice_SaleManagement_Export::get_total($row)); },
			"Total TTC" => function ($row) { return Invoice_SaleManagement_Export::formatFloatVal(Invoice_SaleManagement_Export::get_net_total($row)); },
		);
	}

	function loadRecord($invoice_id) {
		global $current_user;
		$this->focus = $focus = CRMEntity::getInstance($this->moduleName);
		$focus->retrieve_entity_info($invoice_id,$this->moduleName);
		$focus->apply_field_security();
		$focus->id = $invoice_id;
		$this->associated_products = getAssociatedProducts($this->moduleName,$focus);
	}

	function &get_tax($percent) {
		switch($percent) {
		case 20:
			return $this->tax_20;
		case 7:
			return $this->tax_7;
		case 5.5:
			return $this->tax_5_5;
		case 2.1:
			return $this->tax_2_1;
		}

		return null;
	}

	function update_data($row) {
		$invoice_id = $row["invoiceid"];

		if ($invoice_id !== $this->curent_invoice_id) {
			$this->loadRecord($invoice_id);
			$associated_products = $this->associated_products;

			$this->tax_20 		= 0;
			$this->tax_7 		= 0;
			$this->tax_5_5 		= 0;
			$this->tax_2_1 		= 0;
			$this->tax_total 	= 0;
			$this->total 		= 0;
			$this->net_total 	= 0;

			$no_of_decimal_places = getCurrencyDecimalPlacesForOutput();

			foreach($associated_products as $productLineItem) {
				++$productLineItemIndex;

				$discountPercentage  = 0.00;
				$quantity 		= 0;
				$listPrice 		= 0;
				$taxable_total 	= 0;
				$tax_amount 	= 0;
				$producttotal 	= 0;
				$quantity			= $productLineItem["qty{$productLineItemIndex}"];
				$listPrice			= $productLineItem["listPrice{$productLineItemIndex}"];
				$discountPercentage = $productLineItem["discount_percent{$productLineItemIndex}"];
				$taxable_total 		= ($quantity * $listPrice) * (1 - $discountPercentage/100);
				$producttotal 		= $taxable_total;

				if($row["taxtype"] == "individual") {
					$taxQuantity = count($productLineItem['taxes']);
					if ($taxQuantity == 1) {
						$tax_percent = $productLineItem['taxes'][0]['percentage'];
						$producttotal = $taxable_total * (1 + $tax_percent/100);
						$tax_amount = round($producttotal, 2) - round($taxable_total, 2);
						$tax = &$this->get_tax($tax_percent);
						$tax += $tax_amount;
						$this->tax_total += $tax_amount;
					} else if ($taxQuantity > 0) { 
						// tmp should not appends
					}

					$this->total += round($taxable_total, 2);
					$this->net_total += round($producttotal, 2);
				}
			}

			$this->curent_invoice_id = $invoice_id;
		}
	}

	function get_tax_20($row) {
		//return $row["invoice_no"] !== $this->curent_invoice_id;
		$this->update_data($row);

		return $this->tax_20;
	}

	function get_tax_7($row) {
		$this->update_data($row);
		
		return $this->tax_7;
	}
	function get_tax_5_5($row) {
		$this->update_data($row);
		
		return $this->tax_5_5;
	}
	function get_tax_2_1($row) {
		$this->update_data($row);
		
		return $this->tax_2_1;
	}
	function get_tax_total($row) {
		$this->update_data($row);
		
		return $this->tax_total;
	}
	function get_total($row) {
		$this->update_data($row);
		
		return $this->total;
	}
	function get_net_total($row) {
		$this->update_data($row);
		
		return $this->net_total;
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
		$fromPos = strpos($parentQuery, 'FROM');
		$orderByPos = strpos($parentQuery, 'ORDER BY');
		$query = substr($parentQuery, 0, $fromPos) . ", vtiger_invoice.invoiceid " .
				substr($parentQuery, $fromPos, ($orderByPos - $fromPos)) . " GROUP BY vtiger_invoice.invoiceid " .
				 substr($parentQuery, $orderByPos);
		//echo $query;exit();
		return $query;
	}

	function formatFloatVal($val) {
		return str_replace(".", ",", strval(floatval($val)));
	}
}
