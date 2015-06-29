<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class PurchaseOrder_ListView_Model extends Inventory_ListView_Model {
    
	
	/** ED510629
	 * Function to get the list of listview links for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associate array of Link Type to List of Vtiger_Link_Model instances
	 */
	public function getListViewLinks($linkParams) {
		$links = parent::getListViewLinks($linkParams);
                
                $currentUserModel = Users_Record_Model::getCurrentUserModel();
		$moduleModel = $this->getModule();
                
                $basicLinks = array();
                
		$createPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'EditView');
		if($createPermission) {

                    //Suppression du bouton Add par défaut
                    foreach($links['LISTVIEWBASIC'] as $index => $basicLink) 
                        if($basicLink->get('linklabel') === 'LBL_ADD_RECORD'){
                            unset($links['LISTVIEWBASIC'][$index]);
                            break;
                        }
                        
                    foreach(array('order','receipt','invoice') as $potype)
			$basicLinks[] = array(
					'linktype' => 'LISTVIEWBASIC',
					'linklabel' => 'LBL_POTYPE_' . $potype,
					'linkurl' => $moduleModel->getCreateRecordUrl() . '&potype=' . $potype,
					'linkicon' => ''
			);
		}

		foreach($basicLinks as $basicLink) {
			$links['LISTVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicLink);
		}
		return $links;
	}
}