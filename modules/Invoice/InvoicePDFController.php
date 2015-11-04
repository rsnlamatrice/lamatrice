<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

include_once 'include/InventoryPDFController.php';

class Vtiger_InvoicePDFController extends Vtiger_InventoryPDFController{
	function buildHeaderModelTitle() {
		$singularModuleNameKey = 'SINGLE_'.$this->moduleName;
		$translatedSingularModuleLabel = getTranslatedString($singularModuleNameKey, $this->moduleName);
		if($translatedSingularModuleLabel == $singularModuleNameKey) {
			$translatedSingularModuleLabel = getTranslatedString($this->moduleName, $this->moduleName);
		}
		$singularContactsNameKey = 'SINGLE_Contacts';
		$translatedSingularContactsLabel = getTranslatedString($singularContactsNameKey, 'Contacts');
		$contactNo = $this->resolveReferenceFieldValue($this->focusColumnValue('contact_id'), 'Contacts', 'contact_no');
		return sprintf("%s : %s\n%s : %s"
			       , $translatedSingularModuleLabel, $this->focusColumnValue('invoice_no')
			       , $translatedSingularContactsLabel, $contactNo
			       );
	}

	function buildHeaderModelColumnCenter() {
		//$customerName = $this->resolveReferenceLabel($this->focusColumnValue('account_id'), 'Accounts');
		$contactName = $this->resolveReferenceLabel($this->focusColumnValue('contact_id'), 'Contacts');
		$purchaseOrder = $this->focusColumnValue('vtiger_purchaseorder');
		$salesOrder	= $this->resolveReferenceLabel($this->focusColumnValue('salesorder_id'));

		$customerNameLabel = getTranslatedString('Customer Name', $this->moduleName);
		$contactNameLabel = getTranslatedString('Contact Name', $this->moduleName);
		$purchaseOrderLabel = getTranslatedString('Purchase Order', $this->moduleName);
		$salesOrderLabel = getTranslatedString('Sales Order', $this->moduleName);
		
		$billingAddressLabel = getTranslatedString('Billing Address', $this->moduleName);

		$modelColumnCenter = array(
				//$customerNameLabel	=>	$customerName,
				$purchaseOrderLabel 	=>	$purchaseOrder,
				//$contactNameLabel	=>	$contactName,
				$salesOrderLabel	=>	$salesOrder,
				//ED151020 passŽ en 'destinationAddress' $billingAddressLabel  	=> 	$this->buildHeaderBillingAddress($contactName),
			);
		return $modelColumnCenter;
	}

	function buildHeaderModelColumnRight() {
		$issueDateLabel = getTranslatedString('Issued Date', $this->moduleName);
		$invoiceDateLabel = getTranslatedString('Invoice Date', $this->moduleName);
		$validDateLabel = getTranslatedString('Due Date', $this->moduleName);
		// set on center side $billingAddressLabel = getTranslatedString('Billing Address', $this->moduleName);
		// set on left side $shippingAddressLabel = getTranslatedString('Shipping Address', $this->moduleName);

		$modelColumnRight = array(
				'dates' => array(
					$invoiceDateLabel => $this->formatDate($this->focusColumnValue('invoicedate')),
				),
			);
		/*Date d'Ždition : masquŽe
		 if($this->formatDate(date("Y-m-d")) != $this->formatDate($this->focusColumnValue('invoicedate')))
			$modelColumnRight['dates'][$issueDateLabel] = $this->formatDate(date("Y-m-d"));*/
		if($this->focusColumnValue('duedate'))
			$modelColumnRight['dates'][$validDateLabel] = $this->formatDate($this->focusColumnValue('duedate'));
		return $modelColumnRight;
	}

	function getWatermarkContent() {
		//ED151006 switch et vtranslate
		switch($this->focusColumnValue('invoicestatus')){
		case 'Credit Invoice':
		case 'Cancelled':
			return vtranslate($this->focusColumnValue('invoicestatus'), $this->moduleName);	
		default:
			return '';
		}
	}
}
?>