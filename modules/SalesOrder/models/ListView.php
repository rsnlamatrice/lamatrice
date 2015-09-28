<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class SalesOrder_ListView_Model extends Inventory_ListView_Model {
	/*
	 * Function to give advance links of a module
	 *	@RETURN array of advanced links
	 */
	public function getAdvancedLinks(){
		$advancedLinks = parent::getAdvancedLinks();
		
		//ED150928 Recalcule des quantités en commande pour tous les produits
		$moduleModel = $this->getModule();
		$createPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'EditView');
		if($createPermission) {
			//Quantité en demande
			$advancedLink = array(
				'linktype' => 'LISTVIEW',
				'linklabel' => '---',
				'linkurl' => '',
				'linkicon' => ''
			);
			$advancedLinks[] = $advancedLink;
		
			$advancedLink = array(
				'linktype' => 'LISTVIEW',
				'linklabel' => 'Recalcul des quantités',
				'linkurl' => $moduleModel->getRefreshQtyInDemandUrl(),
				'linkicon' => ''
			);
			$advancedLinks[] = $advancedLink;
		
		}

		return $advancedLinks;
	}
}