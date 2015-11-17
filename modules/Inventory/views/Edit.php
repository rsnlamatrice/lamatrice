<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Class Inventory_Edit_View extends Vtiger_Edit_View {

	public function process(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$record = $request->get('record');
		$sourceRecord = $request->get('sourceRecord');
		$sourceModule = $request->get('sourceModule');

		if(!empty($record)  && $request->get('isDuplicate') == true) {
			$recordModel = Inventory_Record_Model::getInstanceById($record, $moduleName);
			
		
			//ED150630
			if($recordModel->get('sent2compta'))
				$recordModel->set('sent2compta', null);
		
			$currencyInfo = $recordModel->getCurrencyInfo();
			$taxes = $recordModel->getProductTaxes();
			$shippingTaxes = $recordModel->getShippingTaxes();
			$relatedProducts = $recordModel->getProducts();
			$viewer->assign('MODE', '');
			$viewer->assign('IS_DUPLICATE_FROM', $record);
			
			//ED151026
			if($request->get('typedossier') === 'Avoir'
			|| $request->get('typedossier') === 'Remboursement'){
				//Inverse les quantités et montants
				foreach($relatedProducts as $index => $relatedProduct){
					foreach(array('qty', 'discount_amount', 'discountTotal', 'totalAfterDiscount', 'taxTotal', 'netPrice') as $fieldName)
						$relatedProducts[$index][$fieldName.$index] = -1 * (float)$relatedProduct[$fieldName.$index];
						
					if($index == 1){
						foreach(array('hdnSubTotal', 'discount_amount_final', 'discountTotal_final', 'tax_totalamount', 'shipping_handling_charge'
							      , 'shtax_totalamount', 'adjustment', 'grandTotal', 'preTaxTotal', 'totalAfterDiscount') as $fieldName)
							$relatedProducts[$index]['final_details'][$fieldName] = -1 * (float)$relatedProduct['final_details'][$fieldName];
							
						foreach(array('received', 'receivedcomments', 'receivedmoderegl') as $fieldName)
							$relatedProducts[$index]['final_details'][$fieldName] = null;
					}
				}
				foreach(array('received', 'hdnGrandTotal', 'balance') as $fieldName)
					$recordModel->set($fieldName, -1 * $recordModel->get($fieldName));;
					
			}
			else {
				if($moduleName === 'Invoice'){
					$recordModel->set('invoicestatus', 'Approved');
					$recordModel->set('received', 0);
				} else {
					$recordModel->set('postatus', null);
					$recordModel->set('paid', 0);
				}
				$recordModel->set('balance', $recordModel->get('hdnGrandTotal'));
			}
		} elseif (!empty($record)) {
               
			$recordModel = Inventory_Record_Model::getInstanceById($record, $moduleName);
			$currencyInfo = $recordModel->getCurrencyInfo();
			$taxes = $recordModel->getProductTaxes();
			$shippingTaxes = $recordModel->getShippingTaxes();
			$relatedProducts = $recordModel->getProducts();
			$viewer->assign('RECORD_ID', $record);
			$viewer->assign('MODE', 'edit');
			
		} elseif ($request->get('salesorder_id') || $request->get('quote_id')) {
			if ($request->get('salesorder_id')) {
				$referenceId = $request->get('salesorder_id');
			} else {
				$referenceId = $request->get('quote_id');
			}

			$parentRecordModel = Inventory_Record_Model::getInstanceById($referenceId, 'SalesOrder');
			$currencyInfo = $parentRecordModel->getCurrencyInfo();
			$taxes = $parentRecordModel->getProductTaxes();
			$shippingTaxes = $parentRecordModel->getShippingTaxes();
			$relatedProducts = $parentRecordModel->getProducts();
			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			$recordModel->setRecordFieldValues($parentRecordModel);
			
		} else {  
			
			$taxes = Inventory_Module_Model::getAllProductTaxes();
			$shippingTaxes = Inventory_Module_Model::getAllShippingTaxes();
			$recordModel = Inventory_Record_Model::getCleanInstance($moduleName);/*ED141219 : Vtiger_Record_Model -> Inventory_Record_Model*/
			$viewer->assign('MODE', '');
				

			//$recordModel->set('invoicedate', date('j-n-Y'));
		
			//The creation of Inventory record from action and Related list of product/service detailview the product/service details will calculated by following code
			if ($request->get('product_id') || $sourceModule === 'Products') {
				if($sourceRecord) {
					$productRecordModel = Products_Record_Model::getInstanceById($sourceRecord);
				} else {
					$productRecordModel = Products_Record_Model::getInstanceById($request->get('product_id'));
				}
				$relatedProducts = $productRecordModel->getDetailsForInventoryModule($recordModel);
			} elseif ($request->get('service_id') || $sourceModule === 'Services') {
				if($sourceRecord) {
					$serviceRecordModel = Services_Record_Model::getInstanceById($sourceRecord);
				} else {
					$serviceRecordModel = Services_Record_Model::getInstanceById($request->get('service_id'));
				}
				$relatedProducts = $serviceRecordModel->getDetailsForInventoryModule($recordModel);
				
			} elseif ($sourceRecord && ($sourceModule === 'Accounts'
						|| $sourceModule === 'Contacts'
						|| $sourceModule === 'Potentials'
						|| $sourceModule === 'Documents'
						|| ($sourceModule === 'Vendors' && $moduleName === 'PurchaseOrder'))) {
				$parentRecordModel = Vtiger_Record_Model::getInstanceById($sourceRecord, $sourceModule);
				
				$recordModel->setParentRecordData($parentRecordModel);
				
				if($sourceModule === 'Documents')//Coupon
					$relatedProducts = $parentRecordModel->getRelatedProductsDetailsForInventoryModule($recordModel);
			}
			//ED150708 : 'coupon libre' par défaut
			if($moduleName === 'Invoice'
			&& !$recordModel->get('notesid')){
				$recordModel->set('notesid', COUPON_LIBRE_ID);//in Documents.php
			}
		}
		
		$moduleModel = $recordModel->getModule();
		$fieldList = $moduleModel->getFields();
		$requestFieldList = array_intersect_key($request->getAll(), $fieldList);

		//get the inventory terms and conditions
		//$inventoryRecordModel = Inventory_Record_Model::getCleanInstance($moduleName);
		$termsAndConditions = $recordModel->getInventoryTermsandConditions();
			
		foreach($requestFieldList as $fieldName=>$fieldValue) {
			$fieldModel = $fieldList[$fieldName];
			if($fieldModel->isEditable()) {
				$recordModel->set($fieldName, $fieldModel->getDBInsertValue($fieldValue));				
			}
		}
		//ED150529
		if($recordModel->get('discountpc')){
			$fieldName = 'discount_percent';
			$fieldModel = $fieldList[$fieldName];
			
			$recordModel->set($fieldName, $fieldModel->getDBInsertValue($recordModel->get('discountpc')));
			
			if(!is_array($relatedProducts))
				$relatedProducts = array(array('final_details' => array()));
				
			if(!is_array($relatedProducts[1]['final_details']))
				$relatedProducts[1]['final_details'] = array();
			
			$relatedProducts[1]['final_details'] = array_merge($relatedProducts[1]['final_details'], array(
				'discount_final_source' => 'Account',
				'discount_type_final' => 'percentage',
				'discount_percentage_final' => $recordModel->get($fieldName),
			));
		}
		
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel,
				Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_EDIT);
		
		$viewer->assign('VIEW_MODE', "fullForm");

		$isRelationOperation = $request->get('relationOperation');

		//if it is relation edit
		$viewer->assign('IS_RELATION_OPERATION', $isRelationOperation);
		if($isRelationOperation) {
			$viewer->assign('SOURCE_MODULE', $sourceModule);
			$viewer->assign('SOURCE_RECORD', $sourceRecord);
		}
		if(!empty($record)  && $request->get('isDuplicate') == true) {
			$viewer->assign('IS_DUPLICATE',true);
		} else {
			$viewer->assign('IS_DUPLICATE',false);
		}
		$currencies = Inventory_Module_Model::getAllCurrencies();
		$picklistDependencyDatasource = Vtiger_DependencyPicklist::getPicklistDependencyDatasource($moduleName);
		$viewer->assign('PICKIST_DEPENDENCY_DATASOURCE',Zend_Json::encode($picklistDependencyDatasource));
		$viewer->assign('RECORD',$recordModel);
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
        
		$viewer->assign('RELATED_PRODUCTS', $relatedProducts);
		$viewer->assign('SHIPPING_TAXES', $shippingTaxes);
		$viewer->assign('TAXES', $taxes);
		$viewer->assign('CURRENCINFO', $currencyInfo);
		$viewer->assign('CURRENCIES', $currencies);
		$viewer->assign('TERMSANDCONDITIONS', $termsAndConditions);

		$productModuleModel = Vtiger_Module_Model::getInstance('Products');
		$viewer->assign('PRODUCT_ACTIVE', $productModuleModel->isActive());

		$serviceModuleModel = Vtiger_Module_Model::getInstance('Services');
		$viewer->assign('SERVICE_ACTIVE', $serviceModuleModel->isActive());

		//ED150603
		if($recordModel->get('sent2compta'))
			$viewer->assign('NOT_EDITABLE', vtranslate('LBL_ALREADY_SENT_2_COMPTA', $moduleName) . ' (' . $recordModel->getDisplayValue('sent2compta') . ')');
		
		//ED150629
		if($moduleName === 'PurchaseOrder'){
			if($request->get('potype') && (empty($record) || $request->get('isDuplicate'))){
				$recordModel->set('potype', $request->get('potype'));
				$recordModel->setDefaultStatus();
			}
			$fieldList['potype']->set('fieldvalue', $recordModel->get('potype'));
			$fieldList['postatus']->set('fieldvalue', $recordModel->get('postatus'));
			$viewer->assign('POTYPE_FIELD_MODEL', $fieldList['potype']);
			
			if($recordModel->get('potype') !== 'invoice'){
				//suppression du champ sent2compta ailleurs qu'en facture
			}
			
		}
		$viewer->view('EditView.tpl', 'Inventory');
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);

		$moduleName = $request->getModule();
		$modulePopUpFile = 'modules.'.$moduleName.'.resources.Popup';
		$moduleEditFile = 'modules.'.$moduleName.'.resources.Edit';
		unset($headerScriptInstances[$modulePopUpFile]);
		unset($headerScriptInstances[$moduleEditFile]);


		$jsFileNames = array(
				'modules.Inventory.resources.Edit',
				'modules.Inventory.resources.Popup',
		);
		$jsFileNames[] = $moduleEditFile;
		$jsFileNames[] = $modulePopUpFile;
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

}