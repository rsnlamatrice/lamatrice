<?php
/*+*******************************************************************************
 *  The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 *********************************************************************************/

/**
 * Description of RelatedModuleMeta
 * TODO to add and extend a way to track many-many and many-one relationships.
 * @author MAK
 *
 *
 * ED150227
 * Extension
 */
class RelatedModuleMeta {
	private $module;
	private $relatedModule;
	private $CAMPAIGNCONTACTREL = 1;
	private $PRODUCTQUOTESREL = 2;
	private $PRODUCTINVOICEREL = 3;
	private $PRODUCTPURCHASEORDERREL = 4;
	//ED150227
	private $INVOICECONTACTREL = 5;
	private $CONTACTCONTACTSREL = 6;
	private $CAMPAIGNDOCUMENTSREL = 7;
	private $CONTACTDOCUMENTSREL = 8;
	
	private function  __construct($module, $relatedModule) {
		$this->module = $module;
		$this->relatedModule = $relatedModule;
	}

	/**
	 *
	 * @param <type> $module
	 * @param <type> $relatedModule
	 * @return RelatedModuleMeta 
	 */
	public static function getInstance($module, $relatedModule) {
		return new RelatedModuleMeta($module, $relatedModule);
	}

	/*
	 *
	 * ED150227 : switch, completed
	 */
	public function getRelationMeta() {
		switch($this->module){
		case 'Contacts':
			switch($this->relatedModule){
			case 'Contacts':
				return $this->getRelationMetaInfo($this->CONTACTCONTACTSREL);
			
			case 'Campaigns':
				return $this->getRelationMetaInfo($this->CAMPAIGNCONTACTREL);
			
			case 'Invoice':
				return $this->getRelationMetaInfo($this->INVOICECONTACTREL);
			
			case 'Documents':
				return $this->getRelationMetaInfo($this->CONTACTDOCUMENTSREL);
			
			case 'Quotes':
				break;
			
			case 'PurchaseOrder':
				break;
			
			default:
				break;
			}
			break;
		
		case 'Campaigns':
			switch($this->relatedModule){
			case 'Contacts':
				return $this->getRelationMetaInfo($this->CAMPAIGNCONTACTREL);
			
			case 'Documents':
				return $this->getRelationMetaInfo($this->CAMPAIGNDOCUMENTSREL);
			
			default:
				break;
			}
			break;
		
		case 'Documents':
			switch($this->relatedModule){
			case 'Contacts':
				return $this->getRelationMetaInfo($this->CONTACTDOCUMENTSREL);
			
			case 'Campaigns':
				return $this->getRelationMetaInfo($this->CAMPAIGNDOCUMENTSREL);
			
			default:
				break;
			}
			break;
		
		case 'Products':
			switch($this->relatedModule){
			case 'Invoice':
				return $this->getRelationMetaInfo($this->PRODUCTINVOICEREL);
			
			case 'PurchaseOrder':
				return $this->getRelationMetaInfo($this->PRODUCTPURCHASEORDERREL);
			
			case 'Quotes':
				return $this->getRelationMetaInfo($this->PRODUCTQUOTESREL);
			
			default:
				break;
			}
			break;
		
		case 'Invoice':
			switch($this->relatedModule){
			case 'Contacts':
				return $this->getRelationMetaInfo($this->INVOICECONTACTREL);
			
			case 'Products':
				return $this->getRelationMetaInfo($this->PRODUCTINVOICEREL);
			
			default:
				break;
			}
			break;
		
		case 'Quotes':
			switch($this->relatedModule){
			case 'Products':
				return $this->getRelationMetaInfo($this->PRODUCTQUOTESREL);
			
			default:
				break;
			}
			break;
		
		case 'PurchaseOrder':
			switch($this->relatedModule){
			case 'Products':
				return $this->getRelationMetaInfo($this->PRODUCTPURCHASEORDERREL);
			
			default:
				break;
			}
			break;
		
		default:
			break;
		}
		//$campaignContactRel = array('Campaigns','Contacts');
		//$productInvoiceRel = array('Products','Invoice');
		//$productQuotesRel = array('Products','Quotes');
		//$productPurchaseOrder = array('Products','PurchaseOrder');
		//$invoiceContactRel = array('Invoice','Contacts');
		//if(in_array($this->module, $campaignContactRel) && in_array($this->relatedModule,
		//		$campaignContactRel)) {
		//	return $this->getRelationMetaInfo($this->CAMPAIGNCONTACTREL);
		//}
		//if(in_array($this->module, $productInvoiceRel) && in_array($this->relatedModule,
		//		$productInvoiceRel)) {
		//	return $this->getRelationMetaInfo($this->PRODUCTINVOICEREL);
		//}
		//if(in_array($this->module, $productQuotesRel) && in_array($this->relatedModule,
		//		$productQuotesRel)) {
		//	return $this->getRelationMetaInfo($this->PRODUCTQUOTESREL);
		//}
		//if(in_array($this->module, $productPurchaseOrder) && in_array($this->relatedModule,
		//		$productPurchaseOrder)) {
		//	return $this->getRelationMetaInfo($this->PRODUCTPURCHASEORDERREL);
		//}
	}

	/*
	 *
	 * ED150227 : completed
	 */
	private function getRelationMetaInfo($relationId) {
		switch($relationId) {
		case $this->CAMPAIGNCONTACTREL: return array(
				'relationTable' => 'vtiger_campaigncontrel',
				'Campaigns' => 'campaignid',
				'Contacts' => 'contactid'
			);
		case $this->PRODUCTINVOICEREL: return array(
				'relationTable' => 'vtiger_inventoryproductrel',
				'Products' => 'productid',
				'Invoice' => 'id'
			);
		case $this->PRODUCTQUOTESREL: return array(
				'relationTable' => 'vtiger_inventoryproductrel',
				'Products' => 'productid',
				'Quotes' => 'id'
			);
		case $this->PRODUCTPURCHASEORDERREL: return array(
				'relationTable' => 'vtiger_inventoryproductrel',
				'Products' => 'productid',
				'PurchaseOrder' => 'id'
			);
		case $this->INVOICECONTACTREL: return array(
				'relationTable' => 'vtiger_invoice',
				'Contacts' => 'contactid',
				'Invoice' => 'invoiceid'
			);
		case $this->CONTACTCONTACTSREL: return array(
				'relationTable' => 'vtiger_contactscontrel',
				'Contacts' => 'contactid',
				'relatedField' => 'relcontid' //relatedField because same module
			);
		case $this->CAMPAIGNDOCUMENTSREL: return array(
				'relationTable' => 'vtiger_senotesrel',
				'Documents' => 'notesid',
				'Campaigns' => 'crmid'
			);
		case $this->CONTACTDOCUMENTSREL: return array(
				'relationTable' => 'vtiger_senotesrel',
				'Documents' => 'notesid',
				'Contacts' => 'crmid',
			);
		}
	}
}
?>