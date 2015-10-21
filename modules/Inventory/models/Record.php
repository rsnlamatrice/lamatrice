<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Inventory Record Model Class
 */
class Inventory_Record_Model extends Vtiger_Record_Model {

	private static $currencies_cache;

	public static function getInstanceById($record, $moduleName){
		$instance = parent::getInstanceById($record, $moduleName);
		$instance->calculateBalance();
		return $instance;
	}

	function getCurrencyInfo() {
		$moduleName = $this->getModuleName();
		//ED150529 : use static cache
		$currencyId = $this->get('currency_id');
		if($currencyId && !is_array(self::$currencies_cache))
			self::$currencies_cache = array();
		$currencyInfo = getInventoryCurrencyInfo($moduleName, $this->getId(), $currencyId, self::$currencies_cache);
		return $currencyInfo;
	}

	function getProductTaxes() {
		$taxDetails = $this->get('taxDetails');
		if ($taxDetails) {
			return $taxDetails;
		}
		
		$record = $this->getId();
		if ($record) {
			$relatedProducts = getAssociatedProducts($this->getModuleName(), $this->getEntity());
			$taxDetails = $relatedProducts[1]['final_details']['taxes'];
		} else {
			$taxDetails = getAllTaxes('available', '', $this->getEntity()->mode, $this->getId());
		}

		$this->set('taxDetails', $taxDetails);
		return $taxDetails;
	}

	function getShippingTaxes() {
		$shippingTaxDetails = $this->get('shippingTaxDetails');
		if ($shippingTaxDetails) {
			return $shippingTaxDetails;
		}

		$record = $this->getId();
		if ($record) {
			$relatedProducts = getAssociatedProducts($this->getModuleName(), $this->getEntity());
			$shippingTaxDetails = $relatedProducts[1]['final_details']['sh_taxes'];
		} else {
			$shippingTaxDetails = getAllTaxes('available', 'sh', 'edit', $this->getId());
		}

		$this->set('shippingTaxDetails', $shippingTaxDetails);
		return $shippingTaxDetails;
	}
	
	function getProducts() {
		$no_of_decimal_places = 2;//getCurrencyDecimalPlaces();
		
		$relatedProducts = getAssociatedProducts($this->getModuleName(), $this->getEntity());
		$productsCount = count($relatedProducts);

		//Updating Pre tax total
		$preTaxTotal = (float)$relatedProducts[1]['final_details']['hdnSubTotal']
						+ (float)$relatedProducts[1]['final_details']['shipping_handling_charge']
						- (float)$relatedProducts[1]['final_details']['discountTotal_final'];

		$relatedProducts[1]['final_details']['preTaxTotal'] = number_format($preTaxTotal, $no_of_decimal_places,'.','');
		
		//Updating Total After Discount
		$totalAfterDiscount = (float)$relatedProducts[1]['final_details']['hdnSubTotal']
								- (float)$relatedProducts[1]['final_details']['discountTotal_final'];
		
		$relatedProducts[1]['final_details']['totalAfterDiscount'] = number_format($totalAfterDiscount, $no_of_decimal_places,'.','');
		
		//Updating Tax details
		$taxtype = $relatedProducts[1]['final_details']['taxtype'];

		for ($i=1;$i<=$productsCount; $i++) {
			$product = $relatedProducts[$i];
			$productId = $product['hdnProductId'.$i];
			$totalAfterDiscount = $product['totalAfterDiscount'.$i];

			if ($taxtype == 'individual') {
				$taxDetails = getTaxDetailsForProduct($productId, 'all');
				$taxCount = count($taxDetails);
				$taxTotal = '0.00';

				for($j=0; $j<$taxCount; $j++) {
					$taxValue = $product['taxes'][$j]['percentage'];

					$taxAmount = $totalAfterDiscount * $taxValue / 100;
					$taxTotal = $taxTotal + $taxAmount;

					$relatedProducts[$i]['taxes'][$j]['amount'] = number_format($taxAmount, $no_of_decimal_places,'.','');
					$relatedProducts[$i]['taxTotal'.$i] = number_format($taxTotal, $no_of_decimal_places,'.','');
				}
				$netPrice = $totalAfterDiscount + $taxTotal;
				$relatedProducts[$i]['netPrice'.$i] = number_format($netPrice, $no_of_decimal_places,'.','');
			}
			
			// ED151019 2 decimals
			foreach(array('productTotal', 'discountTotal', 'totalAfterDiscount', 'taxTotal') as $fieldName)
				if(!array_key_exists($fieldName.$i, $relatedProducts[$i]))
					var_dump("Le champ $fieldName.$i n'existe pas");
				else
					$relatedProducts[$i][$fieldName.$i] = number_format($relatedProducts[$i][$fieldName.$i], $no_of_decimal_places,'.','');
		}
		
		//ED150129
		$relatedProducts[1]['final_details']['balance'] = number_format($this->get('balance'), $no_of_decimal_places,'.','');
		
	
		// ED151019 2 decimals
		foreach(array('hdnSubTotal', 'discountTotal_final', 'shipping_handling_charge', 'adjustment', 'grandTotal') as $fieldName)
			if(!array_key_exists($fieldName, $relatedProducts[1]['final_details']))
				var_dump("Le champ $fieldName n'existe pas");
			else
				$relatedProducts[1]['final_details'][$fieldName] = number_format($relatedProducts[1]['final_details'][$fieldName], $no_of_decimal_places,'.','');
		
		return $relatedProducts;
	}

	/**
	 * Function to set record module field values
	 * @param parent record model
	 * @return <Model> returns Vtiger_Record_Model
	 */
	function setRecordFieldValues($parentRecordModel) {
		$currentUser = Users_Record_Model::getCurrentUserModel();

		$fieldsList = array_keys($this->getModule()->getFields());
		$parentFieldsList = array_keys($parentRecordModel->getModule()->getFields());

		$commonFields = array_intersect($fieldsList, $parentFieldsList);
		foreach ($commonFields as $fieldName) {
			if (getFieldVisibilityPermission($parentRecordModel->getModuleName(), $currentUser->getId(), $fieldName) == 0) {
				$this->set($fieldName, $parentRecordModel->get($fieldName));
			}
		}

		//ED150708
		if($parentRecordModel->getModuleName() === 'SalesOrder'){
			$subject = 'Dépôt-vente';
			if($parentRecordModel->get('contact_id')){
				$contact = Vtiger_Record_Model::getInstanceById($parentRecordModel->get('contact_id'), 'Contacts');
				if($contact){
					$subject .= ' ' . trim($contact->get('firstname') . ' ' . $contact->get('lastname')
						. '/' . $contact->get('mailingzip')
						. ' ' . $contact->get('contact_no'));
				}
			}
			$this->set('subject', $subject);
			$this->set('typedossier', 'Facture de dépôt-vente');
		}
		
		return $recordModel;
	}

	/**
	 * Function to get inventoy terms and conditions
	 * @return <String>
	 */
	function getInventoryTermsandConditions() {
		return getTermsandConditions();
	}

	/**
	 * Function to set data of parent record model to this record
	 * @param Vtiger_Record_Model $parentRecordModel
	 * @return Inventory_Record_Model
	 */
	public function setParentRecordData(Vtiger_Record_Model $parentRecordModel) {
		$userModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$moduleName = $parentRecordModel->getModuleName();

		$data = array();
		
		//echo('<pre>');var_dump($parentRecordModel);echo('</pre>');
		//ED150605
		if($moduleName === 'Accounts'){
			
			$mainContactRecordModel = $parentRecordModel->getRelatedMainContacts();
			if($mainContactRecordModel)
				foreach($mainContactRecordModel as $contactId=>$contact){
					$data['contact_id'] = $contactId;
					$data['subject'] = trim($contact['label']
								. '/' . $contact['mailingzip']
								. ' ' . $contact['contact_no']);
					break;
				}
		}
		//ED150500
		if($moduleName === 'Contacts'){
			$subject = trim($parentRecordModel->get('firstname') . ' ' . $parentRecordModel->get('lastname')
						. '/' . $parentRecordModel->get('mailingzip')
						. ' ' . $parentRecordModel->get('contact_no'));
			/* ED141016 génération du compte du contact si manquant */
			$accountRecordModel = $parentRecordModel->getAccountRecordModel();
			$moduleName = $accountRecordModel->getModuleName();
			$parentRecordModel = $accountRecordModel;
			$data['account_id'] = $accountRecordModel->getId();
			$data['subject'] = $subject;
		}
		$fieldMappingList = $parentRecordModel->getInventoryMappingFields();

		foreach ($fieldMappingList as $fieldMapping) {
			$parentField = $fieldMapping['parentField'];
			$inventoryField = $fieldMapping['inventoryField'];
			if(!$parentField){
				$data[$inventoryField] = $fieldMapping['defaultValue'];
			}
			else {
				$fieldModel = Vtiger_Field_Model::getInstance($parentField,  Vtiger_Module_Model::getInstance($moduleName));
				if (!$fieldModel) {
					echo sprintf('! Champ %s manquant dans le module %s !', $parentField, $moduleName);
					//$data[$inventoryField] = sprintf('! Champ %s manquant dans le module %s !', $parentField, $moduleName);
				} elseif ($fieldModel->getPermissions()) {
					$data[$inventoryField] = $parentRecordModel->get($parentField);
				} else {
					$data[$inventoryField] = $fieldMapping['defaultValue'];
				}
			}
		}
		
		if($moduleName === 'Accounts'){
			/* ED150529 initialisation de la remise */
			$discountpc = $parentRecordModel->get('discountpc');
			//$inventoryField = 'discount_percent'; //'hdnDiscountPercent';
			$inventoryFieldMapping = 'discountpc';
			if(is_numeric($discountpc))
				$data[$inventoryFieldMapping] = $discountpc;
		
			//ED150529
			$data['accountdiscounttype'] = $parentRecordModel->get('discounttype');
		}
		elseif($moduleName === 'Documents'){
			//Coupon : load related products && services
		}
		return $this->setData($data);
	}

	/**
	 * Function to get URL for Export the record as PDF
	 * @return <type>
	 */
	public function getExportPDFUrl() {
		return "index.php?module=".$this->getModuleName()."&action=ExportPDF&record=".$this->getId();
	}

	/**
	  * Function to get the send email pdf url
	  * @return <string>
	  */
	public function getSendEmailPDFUrl() {
	    return 'module='.$this->getModuleName().'&view=SendEmail&mode=composeMailData&record='.$this->getId();
	}

	/**
	 * Function to get this record and details as PDF
	 */
	public function getPDF() {
		$recordId = $this->getId();
		$moduleName = $this->getModuleName();

		$controllerClassName = "Vtiger_". $moduleName ."PDFController";

		$controller = new $controllerClassName($moduleName);
		$controller->loadRecord($recordId);

		$fileName = vtranslate('SINGLE_'.$moduleName, $moduleName).'_'.getModuleSequenceNumber($moduleName, $recordId);
		$controller->Output($fileName.'.pdf', 'D');
	}

    /**
     * Function to get the pdf file name . This will conver the invoice in to pdf and saves the file
     * @return <String>
     *
     */
    public function getPDFFileName() {
	$moduleName = $this->getModuleName();
	if ($moduleName == 'Quotes') {
		vimport("~~/modules/$moduleName/QuotePDFController.php");
		$controllerClassName = "Vtiger_QuotePDFController";
	} else {
		vimport("~~/modules/$moduleName/$moduleName" . "PDFController.php");
		$controllerClassName = "Vtiger_" . $moduleName . "PDFController";
	}

	$recordId = $this->getId();
	$controller = new $controllerClassName($moduleName);
	$controller->loadRecord($recordId);

	$sequenceNo = getModuleSequenceNumber($moduleName,$recordId);
	$translatedName = vtranslate($moduleName, $moduleName);
	$filePath = "storage/$translatedName"."_".$sequenceNo.".pdf";
	 //added file name to make it work in IE, also forces the download giving the user the option to save
        $controller->Output($filePath,'F');
        return $filePath;
    }
    
    	
	/* ED141219
	 * date au format FR
	*/
	public static function getCleanInstance($moduleName){
		$instance = parent::getCleanInstance($moduleName);
		$instance->set('invoicedate', date('Y-m-d'));
		
		//ED150708
		if($moduleName === 'Invoice'){
			$instance->set('notesid', COUPON_LIBRE_ID);
		}
		return $instance;
	}
	
	/* ED150129
	 * le champ balance n'est géré nulle part
	*/
	public function calculateBalance(){
		$moduleName = $this->getModuleName();
		if ($moduleName == 'Invoice')
			$this->set('balance', $this->get('hdnGrandTotal') - $this->get('received'));
		else
			$this->set('balance', $this->get('hdnGrandTotal') - $this->get('paid'));
		return $this->get('balance');
	}
	
	/* ED150906
	 * @return : Campaigns_Record_Model
	*/
	public function getCampaign(){
		return Vtiger_Cache::getRecordModel('Campaigns', 'campaign_no', $this->get('campaign_no'));
	}
	
	/* ED150906
	 * @return : Documents_Record_Model
	*/
	public function getCoupon(){
		return Vtiger_Cache::getRecordModel('Documents', 'notesid', $this->get('notesid'));
	}
}
