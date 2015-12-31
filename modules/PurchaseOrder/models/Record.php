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
 * PurchaseOrder Record Model Class
 */
class PurchaseOrder_Record_Model extends Inventory_Record_Model {
	
	/**
	 * This Function adds the specified product quantity to the Product Quantity in Stock
	 * @param type $recordId
	 */
	function addStockToProducts($recordId) {
		$db = PearDatabase::getInstance();

		$recordModel = Inventory_Record_Model::getInstanceById($recordId);
		$relatedProducts = $recordModel->getProducts();

		foreach ($relatedProducts as $key => $relatedProduct) {
			if($relatedProduct['qty'.$key]){
				$productId = $relatedProduct['hdnProductId'.$key];
				$result = $db->pquery("SELECT qtyinstock FROM vtiger_products WHERE productid=?", array($productId));
				$qty = $db->query_result($result,0,"qtyinstock");
				$stock = $qty + $relatedProduct['qty'.$key];
				$db->pquery("UPDATE vtiger_products SET qtyinstock=? WHERE productid=?", array($stock, $productId));
			}
		}
	}
	
	/**
	 * This Function returns the current status of the specified Purchase Order.
	 * @param type $purchaseOrderId
	 * @return <String> PurchaseOrderStatus
	 */
	function getPurchaseOrderStatus($purchaseOrderId){
			$db = PearDatabase::getInstance();
			$sql = "SELECT postatus FROM vtiger_purchaseorder WHERE purchaseorderid=?";
			$result = $db->pquery($sql, array($purchaseOrderId));
			$purchaseOrderStatus = $db->query_result($result,0,"postatus");
			return $purchaseOrderStatus;
	}
	
	function setDefaultStatus(){
		switch($this->get('potype')){
		case 'order':
			$this->set('postatus', 'Created');
			break;
		case 'invoice':
			$this->set('postatus', 'Created');
			break;
		case 'receipt':
			$this->set('postatus', 'Created');
			break;
		default:
			$this->set('postatus', 'Created');
			break;
		}
	}
	
	/**
	 * ED150629
	 * getPicklistValuesDetails
	 */
	public function getPicklistValuesDetails($fieldname){
		switch($fieldname){
			case 'potype'://type de document
				return array(
					'order' => array( 'label' => 'Commande', 'icon' => 'ui-icon potype order' ),
					'receipt' => array( 'label' => 'Bon de réception', 'icon' => 'ui-icon potype receipt' ),
					'invoice' => array( 'label' => 'Facture', 'icon' => 'ui-icon potype invoice' )
				);
				break;
			case 'postatus'://status du document : dépend du type de document
				switch($this->get('potype')){
				case 'order':
				case 'invoice':
					return array(
						'Created' => array( 'label' => 'Créée', 'icon' => 'ui-icon ui-icon-check' ),
						'Approved' => array( 'label' => 'Validée', 'icon' => 'ui-icon ui-icon-check green' ),
						'Compta' => array( 'label' => 'Comptabilisée', 'icon' => 'ui-icon ui-icon-check blue' ),
						'Cancelled' => array( 'label' => 'Annulée', 'icon' => 'ui-icon ui-icon-close darkred' )
					);
					break;
				case 'receipt':
					return array(
						'Created' => array( 'label' => 'Créé', 'icon' => 'ui-icon ui-icon-check' ),
						'Received Shipment' => array( 'label' => 'Commande reçue', 'icon' => 'ui-icon ui-icon-locked darkgreen' ),
						'Cancelled' => array( 'label' => 'Annulé', 'icon' => 'ui-icon ui-icon-close darkred' )
					);
					break;
				default:
					return array(
						'Created' => array( 'label' => 'Créé-e', 'icon' => 'ui-icon ui-icon-check' ),
						'Approved' => array( 'label' => 'Validé-e', 'icon' => 'ui-icon ui-icon-check green' ),
						'Received Shipment' => array( 'label' => 'Commande reçue', 'icon' => 'ui-icon ui-icon-locked darkgreen' ),
						'Compta' => array( 'label' => 'Comptabilisée', 'icon' => 'ui-icon ui-icon-check blue' ),
						'Cancelled' => array( 'label' => 'Annulé-e', 'icon' => 'ui-icon ui-icon-close darkred' )
					);
					break;
				}
				break;
			default:
				return parent::getPicklistValuesDetails($fieldname);
		}
	}

	/**
	 * Funtion to get Duplicate Record Url
	 * @return <String>
	 */
	public function getDuplicateRecordAsPOTypeUrl($new_potype) {
		$module = $this->getModule();
		return 'index.php?module='.$this->getModuleName().'&view='.$module->getEditViewName().'&record='.$this->getId().'&isDuplicate=true&potype='.$new_potype;

	}
}