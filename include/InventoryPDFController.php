<?php
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 ********************************************************************************/

include_once 'vtlib/Vtiger/PDF/models/Model.php';
include_once 'vtlib/Vtiger/PDF/inventory/HeaderViewer.php';
include_once 'vtlib/Vtiger/PDF/inventory/FooterViewer.php';
include_once 'vtlib/Vtiger/PDF/inventory/ContentViewer.php';
include_once 'vtlib/Vtiger/PDF/inventory/ContentViewer2.php';
include_once 'vtlib/Vtiger/PDF/viewers/PagerViewer.php';
include_once 'vtlib/Vtiger/PDF/PDFGenerator.php';
include_once 'data/CRMEntity.php';

class Vtiger_InventoryPDFController {

	protected $module;
	protected $focus = null;
	
	function __construct($module) {
		$this->moduleName = $module;
	}

	function loadRecord($id) {
		global $current_user;
		$this->focus = $focus = CRMEntity::getInstance($this->moduleName);
		$focus->retrieve_entity_info($id,$this->moduleName);
		$focus->apply_field_security();
		$focus->id = $id;
		$this->associated_products = getAssociatedProducts($this->moduleName,$focus);
	}

	function getPDFGenerator() {
		return new Vtiger_PDF_Generator();
	}

	function getContentViewer() {
		if($this->focusColumnValue('hdnTaxType') == "individual") {
			$contentViewer = new Vtiger_PDF_InventoryContentViewer();
		} else {
			$contentViewer = new Vtiger_PDF_InventoryTaxGroupContentViewer();
		}
		$contentViewer->setContentModels($this->buildContentModels());
		$contentViewer->setSummaryModel($this->buildSummaryModel());
		$contentViewer->setAfterSummaryModel($this->buildAfterSummaryModel());//ED151020
		$contentViewer->setLabelModel($this->buildContentLabelModel());
		$contentViewer->setWatermarkModel($this->buildWatermarkModel());
		return $contentViewer;
	}

	function getHeaderViewer() {
		$headerViewer = new Vtiger_PDF_InventoryHeaderViewer();
		$headerViewer->setModel($this->buildHeaderModel());
		return $headerViewer;
	}

	function getFooterViewer() {
		$footerViewer = new Vtiger_PDF_InventoryFooterViewer();
		$model = $this->buildFooterModel();
		if(!$model)
			return false;
		$footerViewer->setModel($model);
		$footerViewer->setLabelModel($this->buildFooterLabelModel());
		$footerViewer->setOnLastPage();
		return $footerViewer;
	}

	function getPagerViewer() {
		$pagerViewer = new Vtiger_PDF_PagerViewer();
		$pagerViewer->setModel($this->buildPagermodel());
		return $pagerViewer;
	}

	function Output($filename, $type) {
		if(is_null($this->focus)) return;

		$pdfgenerator = $this->getPDFGenerator();

		$pdfgenerator->setPagerViewer($this->getPagerViewer());
		$pdfgenerator->setHeaderViewer($this->getHeaderViewer());
		$pdfgenerator->setFooterViewer($this->getFooterViewer());
		$pdfgenerator->setContentViewer($this->getContentViewer());

		$pdfgenerator->generate($filename, $type);
	}


	// Helper methods
	
	function buildContentModels() {
		$associated_products = $this->associated_products;
		$contentModels = array();
		$productLineItemIndex = 0;
		$totaltaxes = 0;
		$totalPerTax = array();
		$no_of_decimal_places = getCurrencyDecimalPlacesForOutput();
		foreach($associated_products as $productLineItem) {
			++$productLineItemIndex;

			$contentModel = new Vtiger_PDF_Model();

			$discountPercentage  = 0.00;
			$total_tax_percent = 0.00;
			$producttotal_taxes = 0.00;
			$quantity = ''; $listPrice = ''; $discount = ''; $taxable_total = '';
			$tax_amount = ''; $producttotal = '';


			$quantity			= $productLineItem["qty{$productLineItemIndex}"];
			$listPrice			= $productLineItem["listPrice{$productLineItemIndex}"];
			$discount			= $productLineItem["discountTotal{$productLineItemIndex}"];
			$discountPercentage = $productLineItem["discount_percent{$productLineItemIndex}"];
			$taxed_discount 	= $discount;
			$taxable_total 		= ($quantity * $listPrice) * (1 - $discountPercentage/100);
			$producttotal 		= $taxable_total;

			if($this->focus->column_fields["hdnTaxType"] == "individual") {
				$taxQuantity = count($productLineItem['taxes']);
				if ($taxQuantity == 1) {
					$total_tax_percent += $tax_percent = $productLineItem['taxes'][0]['percentage'];
					$producttotal = $taxable_total * (1 + $tax_percent/100);
					$taxed_discount *= (1 + $tax_percent/100);
					$producttotal_taxes += $tax_amount = round($producttotal, 2) - round($taxable_total, 2);

					//ED151019
					if($tax_amount){
						$tax_name = $productLineItem['taxes'][0]['taxname'];
						if(!$totalPerTax[$tax_name])
							$totalPerTax[$tax_name] = 0.0;
						$totalPerTax[$tax_name] += $tax_amount;
					}

				} else {
					for($tax_count=0;$tax_count<$taxQuantity;$tax_count++) {
						$tax_percent = $productLineItem['taxes'][$tax_count]['percentage'];
						$total_tax_percent += $tax_percent;
						$tax_amount = (($taxable_total*$tax_percent)/100);
						$taxed_discount *= (1 + $tax_percent/100);
						$producttotal_taxes += $tax_amount;
						//ED151019
						if($tax_amount){
							$tax_name = $productLineItem['taxes'][$tax_count]['taxname'];
							if(!$totalPerTax[$tax_name])
								$totalPerTax[$tax_name] = 0.0;
							$totalPerTax[$tax_name] += $tax_amount;
						}
					}
					$producttotal = $taxable_total+$producttotal_taxes;
				}
			}

			$producttotal_taxes = number_format(round($producttotal_taxes, $no_of_decimal_places), $no_of_decimal_places,'.','');
			$unitTaxedPrice = $quantity ? (($producttotal + $taxed_discount) / $quantity) : ($listPrice);
			$taxable_total = number_format(round($taxable_total, $no_of_decimal_places), $no_of_decimal_places,'.','');
			$producttotal = number_format(round($producttotal, $no_of_decimal_places), $no_of_decimal_places,'.','');
			$tax = $producttotal_taxes;
			$totaltaxes += $tax;
			
			//$discountPercentage = $productLineItem["discount_percent{$productLineItemIndex}"];
			$productName = decode_html($productLineItem["productName{$productLineItemIndex}"]);
			//get the sub product
			$subProducts = $productLineItem["subProductArray{$productLineItemIndex}"];
			if($subProducts != '') {
				foreach($subProducts as $subProduct) {
					$productName .="\n"." - ".decode_html($subProduct);
				}
			}
			$contentModel->set('Name', $productName);
			$contentModel->set('Code', decode_html($productLineItem["hdnProductcode{$productLineItemIndex}"]));
			$contentModel->set('Quantity', $quantity);
			//ED151020 tarif unitaire TTC ˆ a place de $listPrice
			$contentModel->set('Price',     $this->formatPrice($unitTaxedPrice));
			if((float)$taxed_discount)
				$contentModel->set('Discount', "$discountPercentage %" /*$this->formatPrice($taxed_discount)."\n ($discountPercentage %)"*/);
				
			//ED151019
			$contentModel->set('Tax',       "$total_tax_percent %");
			//$contentModel->set('Tax',       $this->formatPrice($tax)."\n ($total_tax_percent%)");
			
			$contentModel->set('Total',     $this->formatPrice($producttotal));
			// Do not display comment on invoices anymore
			//$contentModel->set('Comment',   decode_html($productLineItem["comment{$productLineItemIndex}"]));

			$contentModels[] = $contentModel;
		}
		$totaltaxes = number_format(round($totaltaxes, $no_of_decimal_places), $no_of_decimal_places,'.','');
		$this->totaltaxes = $totaltaxes; //will be used to add it to the net total
		$this->totalPerTax = $totalPerTax;
		return $contentModels;
	}

	function buildContentLabelModel() {
		$labelModel = new Vtiger_PDF_Model();
		$labelModel->set('Code',	  getTranslatedString('Product Code',$this->moduleName));
		$labelModel->set('Name',	  getTranslatedString('Product Name',$this->moduleName));
		$labelModel->set('Quantity',  getTranslatedString('Quantity',$this->moduleName));
		$labelModel->set('Price',     getTranslatedString('Unit price',$this->moduleName));
		$labelModel->set('Discount',  getTranslatedString('Discount',$this->moduleName));
		$labelModel->set('Tax',       getTranslatedString('VAT',$this->moduleName));
		$labelModel->set('Total',     getTranslatedString('Total',$this->moduleName));
		$labelModel->set('Comment',   getTranslatedString('Comment'),$this->moduleName);
		return $labelModel;
	}

	function buildSummaryModel() {
		$no_of_decimal_places = getCurrencyDecimalPlacesForOutput();
		$associated_products = $this->associated_products;
		$final_details = $associated_products[1]['final_details'];

		$summaryModel = new Vtiger_PDF_Model();

		$netTotal = $discount = $handlingCharges =  $handlingTaxes = 0;
		$adjustment = 0;
		$grandTotal = $final_details['grandTotal'];
		
		$productLineItemIndex = 0;
		$sh_tax_percent = 0;
		foreach($associated_products as $productLineItem) {
			++$productLineItemIndex;
			$netTotal += $productLineItem["netPrice{$productLineItemIndex}"];
		}
		//$netTotal = round($netTotal, 2); //ED151019 round 2
		$netTotal = number_format(round($netTotal + $this->totaltaxes, 2), $no_of_decimal_places,'.', '');
		// if($netTotal != $grandTotal) // TMP ??
		// 	$summaryModel->set(getTranslatedString("Net Total", $this->moduleName), $this->formatPrice($netTotal));

		$discount_amount = $final_details["discount_amount_final"];
		$discount_percent = $final_details["discount_percentage_final"];

		$discount = 0.0;
		$discount_final_percent = '0.00';
		if($final_details['discount_type_final'] == 'amount') {
			$discount = $discount_amount;
		} else if($final_details['discount_type_final'] == 'percentage') {
			$discount_final_percent = $discount_percent;
			$discount = (($discount_percent*$final_details["hdnSubTotal"])/100);
		}
		if((float)$discount)
			$summaryModel->set(getTranslatedString("Discount", $this->moduleName)." ($discount_final_percent %)", $this->formatPrice($discount));

		if($grandTotal && $this->totaltaxes);
			$summaryModel->set(getTranslatedString("Total HT", $this->moduleName), $this->formatPrice($grandTotal - $this->totaltaxes));

		$group_total_tax_percent = '0.00';
		//To calculate the group tax amount
		if($final_details['taxtype'] == 'group') {
			$group_tax_details = $final_details['taxes'];
			for($i=0;$i<count($group_tax_details);$i++) {
				$group_total_tax_percent += $group_tax_details[$i]['percentage'];
			}
			$summaryModel->set(getTranslatedString("Tax:", $this->moduleName)." ($group_total_tax_percent %)", $this->formatPrice($final_details['tax_totalamount']));
		} else {
			//ED151019
			//Cumuls par taux de TVA
			$group_tax_details = $final_details['taxes'];
			$taxNames = array();
			$taxValues = array();
			for($i=0;$i<count($group_tax_details);$i++){
				$tax_name = $group_tax_details[$i]['taxname'];
				if($this->totalPerTax[$tax_name]){
					$taxNames[] = decode_html($group_tax_details[$i]['taxlabel']) . ' ' . (float)$group_tax_details[$i]['percentage'] . ' %';
					$taxValues[] = $this->formatPrice(number_format(round($this->totalPerTax[$tax_name], $no_of_decimal_places), $no_of_decimal_places,'.', ''));
				}
			}
			//var_dump($group_tax_details, $this->totalPerTax, implode("\r\n", $taxNames), implode("\r\n", $taxValues));
			$summaryModel->set(implode("\r\n", $taxNames), implode("\r\n", $taxValues));
			
		}
		//Shipping & Handling taxes
		$sh_tax_details = $final_details['sh_taxes'];
		for($i=0;$i<count($sh_tax_details);$i++) {
			$sh_tax_percent = $sh_tax_percent + $sh_tax_details[$i]['percentage'];
		}
		//obtain the Currency Symbol
		$currencySymbol = $this->buildCurrencySymbol();

		if((float)$final_details['shipping_handling_charge'])
			$summaryModel->set(getTranslatedString("Shipping & Handling Charges", $this->moduleName), $this->formatPrice($final_details['shipping_handling_charge']));
		if((float)$final_details['shtax_totalamount'])
			$summaryModel->set(getTranslatedString("Shipping & Handling Tax:", $this->moduleName)." ($sh_tax_percent%)", $this->formatPrice($final_details['shtax_totalamount']));
		if((float)$final_details['adjustment'])
			$summaryModel->set(getTranslatedString("Adjustment", $this->moduleName), $this->formatPrice($final_details['adjustment']));
		//$summaryModel->set(getTranslatedString("Total TVA", $this->moduleName), $this->formatPrice($this->totaltaxes)); // TODO add currency string
		$summaryModel->set(getTranslatedString("Total", $this->moduleName), $this->formatPrice($grandTotal)); // TODO add currency string

		if ($this->moduleName == 'Invoice') {
			$receivedLabel = getTranslatedString("Received", $this->moduleName);
			$receivedVal = $this->focusColumnValue("received");
			if (!$receivedVal) {
				$this->focus->column_fields["received"] = 0;
			}
			else {
				$receivedLabel .= ' (' . getTranslatedString($this->focusColumnValue("receivedmoderegl"), $this->moduleName) . ')';
			}
			//If Received value is exist then only Recieved, Balance details should present in PDF
			if ($this->formatPrice($this->focusColumnValue("received")) > 0) {
				$summaryModel->set($receivedLabel, $this->formatPrice($this->focusColumnValue("received")));
				$summaryModel->set(getTranslatedString("Balance", $this->moduleName), $this->formatPrice($this->focusColumnValue("balance")));
			}
		}
		return $summaryModel;
	}

	function buildHeaderModel() {
		$headerModel = new Vtiger_PDF_Model();
		$headerModel->set('title', $this->buildHeaderModelTitle());
		$modelColumns = array($this->buildHeaderModelColumnLeft(), $this->buildHeaderModelColumnCenter(), $this->buildHeaderModelColumnRight());
		$headerModel->set('columns', $modelColumns);
		
		$modelAddress = $this->buildHeaderDestinationAddress();
		$headerModel->set('destinationAddress', $modelAddress);

		return $headerModel;
	}

	function buildHeaderModelTitle() {
		return $this->moduleName;
	}

	function buildHeaderModelColumnLeft() {
		global $adb;

		// Company information
		$result = $adb->pquery("SELECT * FROM vtiger_organizationdetails", array());
		$num_rows = $adb->num_rows($result);
		if($num_rows) {
			$resultrow = $adb->fetch_array($result);
			//ED151020
			$this->organizationDetails = $resultrow;

			$addressValues = array();
			$addressValues[] = $resultrow['address'];
			if(!empty($resultrow['code'])) $addressValues[]= "\n".$resultrow['code'];
			if(!empty($resultrow['city'])) $addressValues[]= $resultrow['city'];
			//if(!empty($resultrow['state'])) $addressValues[]= ",".$resultrow['state'];
			//if(!empty($resultrow['country'])) $addressValues[]= "\n".$resultrow['country'];


			if(strtoupper($this->type) == "ADH" && !empty($resultrow['adh_header_text'])) {
				$additionalCompanyInfo[]= "\n\n".$resultrow['adh_header_text'];
			} else if(!empty($resultrow['inventory_header_text'])) {
				$additionalCompanyInfo[]= "\n\n".$resultrow['inventory_header_text'];
			}
			
			if(!empty($resultrow['phone']))		$additionalCompanyInfo[]= "\n".getTranslatedString("Phone: ", $this->moduleName). $resultrow['phone'];
			if(!empty($resultrow['fax']))		$additionalCompanyInfo[]= "\n".getTranslatedString("Fax: ", $this->moduleName). $resultrow['fax'];
			if(!empty($resultrow['website']))	$additionalCompanyInfo[]= "\n".getTranslatedString("Website: ", $this->moduleName). $resultrow['website'];

			//ED151020 Test a file name with -print pre-extension
			/*$logoFile = "test/logo/". pathinfo($resultrow['logoname'], PATHINFO_FILENAME) . '-print.' . pathinfo($resultrow['logoname'], PATHINFO_EXTENSION);
			global $root_directory;
			if(!file_exists($root_directory . $logoFile))
				$logoFile = "test/logo/".$resultrow['logoname'];*/
			if($resultrow['print_logoname'])
				$logoFile = "test/logo/".$resultrow['print_logoname'];
			else
				$logoFile = "test/logo/".$resultrow['logoname'];
			$modelColumnLeft = array(
					'logo' => $logoFile,
					'summary' => decode_html($resultrow['organizationname']),
					'content' => decode_html($this->joinValues($addressValues, ' '). $this->joinValues($additionalCompanyInfo, ' '))
			);
			
		}
		else
			$modelColumnLeft = array();
		if($this->hasShippingAddress()){
			$shippingAddressLabel = getTranslatedString('Shipping Address', $this->moduleName);
			$modelColumnLeft[$shippingAddressLabel] = $this->buildHeaderShippingAddress();
		}
		return $modelColumnLeft;
	}

	function buildHeaderModelColumnCenter() {
		$customerName = $this->resolveReferenceLabel($this->focusColumnValue('account_id'), 'Accounts');
		$contactName = $this->resolveReferenceLabel($this->focusColumnValue('contact_id'), 'Contacts');

		$customerNameLabel = getTranslatedString('Customer Name', $this->moduleName);
		$contactNameLabel = getTranslatedString('Contact Name', $this->moduleName);
		$modelColumnCenter = array(
				$customerNameLabel => $customerName,
				$contactNameLabel  => $contactName,
		);
		return $modelColumnCenter;
	}

	function buildHeaderModelColumnRight() {
		$issueDateLabel = getTranslatedString('Issued Date', $this->moduleName);
		$validDateLabel = getTranslatedString('Valid Date', $this->moduleName);

		$modelColumnRight = array(
				'dates' => array(
						$issueDateLabel  => $this->formatDate(date("Y-m-d")),
						$validDateLabel  => $this->formatDate($this->focusColumnValue('validtill')),
				),
		);
		return $modelColumnRight;
	}
	
	/** ED151020
	 * Bloc fixe contenant l'adresse de destination
	 */
	function buildHeaderDestinationAddress(){
		$billingAddressLabel = getTranslatedString('Billing Address', $this->moduleName);
		$model = array(
			$billingAddressLabel  => $this->buildHeaderBillingAddress(),
		);
		return $model;
	}

	function buildFooterModel() {
		$description = $this->focusColumnValue('description');
		$terms_conditions = $this->focusColumnValue('terms_conditions');
		if(!$description && !$terms_conditions)
			return false;
		$footerModel = new Vtiger_PDF_Model();
		$footerModel->set(Vtiger_PDF_InventoryFooterViewer::$DESCRIPTION_DATA_KEY, from_html($description));
		$footerModel->set(Vtiger_PDF_InventoryFooterViewer::$TERMSANDCONDITION_DATA_KEY, from_html($this->focusColumnValue('terms_conditions')));
		return $footerModel;
	}

	function buildFooterLabelModel() {
		$labelModel = new Vtiger_PDF_Model();
		$labelModel->set(Vtiger_PDF_InventoryFooterViewer::$DESCRIPTION_LABEL_KEY, getTranslatedString('Description',$this->moduleName));
		$labelModel->set(Vtiger_PDF_InventoryFooterViewer::$TERMSANDCONDITION_LABEL_KEY, getTranslatedString('Terms & Conditions',$this->moduleName));
		return $labelModel;
	}

	function buildPagerModel() {
		$footerModel = new Vtiger_PDF_Model();
		$footerModel->set('format', '-%s-');
		return $footerModel;
	}

	function getWatermarkContent() {
		return '';
	}

	function buildWatermarkModel() {
		$watermarkModel = new Vtiger_PDF_Model();
		$watermarkModel->set('content', $this->getWatermarkContent());
		return $watermarkModel;
	}
	//ED151020
	function getAfterSummaryContent(){
		return $this->organizationDetails['inventory_lastpage_footer_text'];
	}
	function buildAfterSummaryModel() {
		$model = new Vtiger_PDF_Model();
		$model->set('content', $this->getAfterSummaryContent());
		return $model;
	}
	
	function buildHeaderBillingAddress() {
		$contactName = $this->resolveReferenceLabel($this->focusColumnValue('contact_id'), 'Contacts');
		$street2 = $this->focusColumnValues(array('bill_street2'));
		$addressFormat = $this->focusColumnValues(array('bill_addressformat'));
		$pobox	= $this->focusColumnValues(array('bill_pobox'));
		$street = $this->focusColumnValues(array('bill_street'));
		$street3 = $this->focusColumnValues(array('bill_street3'));
		$zipCode =  $this->focusColumnValues(array('bill_code')); 
		$city	= $this->focusColumnValues(array('bill_city'));
		$state	= $this->focusColumnValues(array('bill_state'));
		$country = $this->focusColumnValues(array('bill_country'));   
		
		return $this->buildAddress($contactName, $street2, $street, $street3, $pobox, $zipCode, $city, $state, $country, $formatAddress);
	}

	//ED151020
	function buildAddress($contactName, $street2, $street, $street3, $pobox, $zipCode, $city, $state, $country, $formatAddress) {
		$address = '';
		switch($formatAddress){
		case 'CN1' : //street2 + name
			$address = $street2 . "\n" . $contactName;
			break;
		case 'C1' : //street2 without name
			$address = $street2;
			break;
		case 'N1' : //name without street2
			$address = $contactName;
			break;
		case 'NC1' ://name + street2
		default:
			$address = $contactName . "\n" . $street2;
			break;
		}
		//ED151006
		if($street3 && $pobox)
			$street3 .= $this->joinValues(array($street3, $pobox), ', ');
		elseif(!$street3 && $pobox)
			$street3 .= $this->joinValues(array($street3, $pobox), ', ');
		
		$address .= "\n$street3\n$street\n$zipCode $city";
		if($country && $country != 'France')//ED151006 // TODO France en constante	
			$address .= "\n".$country;
		return preg_replace('/^\s+|\s*(\n)\s*|\s+$/', "\n", $address);
	}

	function buildHeaderShippingAddress() {
		$shipPoBox	= $this->focusColumnValues(array('ship_pobox'));
		$shipStreet = $this->focusColumnValues(array('ship_street'));
		$shipStreet3 = $this->focusColumnValues(array('ship_street3'));
		$shipCity	= $this->focusColumnValues(array('ship_city'));
		//$shipState	= $this->focusColumnValues(array('ship_state'));
		$shipCountry = $this->focusColumnValues(array('ship_country'));
		$shipCode	=  $this->focusColumnValues(array('ship_code'));
		//ED151006
		if($shipStreet3){
			$address	= $shipStreet;
			$address .= "\n".$this->joinValues(array($shipStreet3, $shipPoBox), ', ');
		}
		else {
			$address	= $this->joinValues(array($shipStreet, $shipPoBox), ', ');
		}
		//$address .= "\n$shipCode ".$this->joinValues(array($shipCity, $shipState), ', ');
		if($shipCountry && $shipCountry!= 'France'){//ED151006 // TODO France en constante
			$address .= "\n ".$this->joinValues(array($shipCity, $shipState), ', ') . ' ' . $shipCode;
			$address .= "\n".$shipCountry;
		}
		else
			$address .= "\n$shipCode ".$this->joinValues(array($shipCity, $shipState), ', ');
		
		return $address;
	}

	//ED151020
	function hasShippingAddress() {
		$shipCode	=  $this->focusColumnValues(array('ship_code'));
		if(!$shipCode) return false;
		$shipCity	= $this->focusColumnValues(array('ship_city'));
		if(!$shipCity) return false;
		return true;
	}

	function buildCurrencySymbol() {
		global $adb;
		$currencyId = $this->focus->column_fields['currency_id'];
		if(!empty($currencyId)) {
			$result = $adb->pquery("SELECT currency_symbol FROM vtiger_currency_info WHERE id=?", array($currencyId));
			return decode_html($adb->query_result($result,0,'currency_symbol'));
		}
		return false;
	}

	function focusColumnValues($names, $delimeter="\n") {
		if(!is_array($names)) {
			$names = array($names);
		}
		$values = array();
		foreach($names as $name) {
			$value = $this->focusColumnValue($name, false);
			if($value !== false) {
				$values[] = $value;
			}
		}
		return $this->joinValues($values, $delimeter);
	}

	function focusColumnValue($key, $defvalue='') {
		$focus = $this->focus;
		if(isset($focus->column_fields[$key])) {
			return decode_html($focus->column_fields[$key]);
		}
		return $defvalue;
	}

	function resolveReferenceLabel($id, $module=false) {
		if(empty($id)) {
			return '';
		}
		if($module === false) {
			$module = getSalesEntityType($id);
		}
		$label = getEntityName($module, array($id));
		return decode_html($label[$id]);
	}

	//ED151020
	function resolveReferenceFieldValue($id, $module=false, $fieldName) {
		if(empty($id)) {
			return '';
		}
		if($module === false) {
			$module = getSalesEntityType($id);
		}
		$recordModel = Vtiger_Record_Model::getInstanceById($id, $module);
		return decode_html($recordModel->getDisplayValue($fieldName));
	}

	function joinValues($values, $delimeter= "\n") {
		$valueString = '';
		foreach($values as $value) {
			if(empty($value)) continue;
			$valueString .= $value . $delimeter;
		}
		return rtrim($valueString, $delimeter);
	}

	function formatNumber($value) {
		return number_format($value);
	}

	function formatPrice($value, $decimal=2) {
		global $current_user;
		return number_format(round((float)$value, $decimal), $decimal, $current_user->currency_decimal_separator, ' ') . ' ' . $this->buildCurrencySymbol();
		/*ED151019
		$currencyField = new CurrencyField($value);
		return $currencyField->getDisplayValue(null, true);*/
	}

	function formatDate($value) {
		return DateTimeField::convertToUserFormat($value);
	}

	public function setType($type) {
		$this->type = $type;
	}

}
?>