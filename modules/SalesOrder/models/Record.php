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
class SalesOrder_Record_Model extends Inventory_Record_Model {

	function getCreateInvoiceUrl() {
		$invoiceModuleModel = Vtiger_Module_Model::getInstance('Invoice');

		return "index.php?module=".$invoiceModuleModel->getName()."&view=".$invoiceModuleModel->getEditViewName()."&salesorder_id=".$this->getId();
	}

	
	/**
	 * ED150629
	 * getPicklistValuesDetails
	 */
	public function getPicklistValuesDetails($fieldname){
		switch($fieldname){
			case 'sostatus'://status du document : dépend du type de document
				return array(
					'Created' => array( 'label' => 'Créé', 'icon' => 'ui-icon ui-icon-check' ),
					'Approved' => array( 'label' => 'Validé', 'icon' => 'ui-icon ui-icon-check darkgreen' ),
					'Delivered' => array( 'label' => 'Livré', 'icon' => 'ui-icon ui-icon-locked darkgreen' ),
					'Archived' => array( 'label' => 'Archivé', 'icon' => 'ui-icon ui-icon-close blue' ),
					'Cancelled' => array( 'label' => 'Annulé', 'icon' => 'ui-icon ui-icon-close darkred' ),
				);
				break;
			default:
				return parent::getPicklistValuesDetails($fieldname);
		}
	}
}