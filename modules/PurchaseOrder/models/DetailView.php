<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class PurchaseOrder_DetailView_Model extends Inventory_DetailView_Model {
    	/**
	 * Function to get the detail view links (links and widgets)
	 * @param <array> $linkParams - parameters which will be used to calicaulate the params
	 * @param boolean $countRelatedEntity - AV150619
	 * @return <array> - array of link models in the format as below
	 *                   array('linktype'=>list of link models);
	 */
	public function getDetailViewLinks($linkParams, $countRelatedEntity = false) {
		$linkModelList = parent::getDetailViewLinks($linkParams, $countRelatedEntity);
		$recordModel = $this->getRecord();
		$moduleName = $recordModel->getmoduleName();

		if(Users_Privileges_Model::isPermitted($moduleName, 'DetailView', $recordModel->getId())) {
                    switch($recordModel->get('potype')){
                    case 'order':
			$detailViewLinks = array(
					'linklabel' => vtranslate('LBL_DUPLICATE_AS_INVOICE', $moduleName),
					'linkurl' => $recordModel->getDuplicateRecordAsPOTypeUrl('invoice'),
					'linkicon' => ''
			);
                        $this->insertDetailViewLinkAfterDuplicate($linkModelList, Vtiger_Link_Model::getInstanceFromValues($detailViewLinks));
                        
			$detailViewLinks = array(
					'linklabel' => vtranslate('LBL_DUPLICATE_AS_RECEIPT', $moduleName),
					'linkurl' => $recordModel->getDuplicateRecordAsPOTypeUrl('receipt'),
					'linkicon' => ''
			);
                        $this->insertDetailViewLinkAfterDuplicate($linkModelList, Vtiger_Link_Model::getInstanceFromValues($detailViewLinks));
                        break;
                    case 'receipt':
			$detailViewLinks = array(
					'linklabel' => vtranslate('LBL_DUPLICATE_AS_INVOICE', $moduleName),
					'linkurl' => $recordModel->getDuplicateRecordAsPOTypeUrl('invoice'),
					'linkicon' => ''
			);
                        $this->insertDetailViewLinkAfterDuplicate($linkModelList, Vtiger_Link_Model::getInstanceFromValues($detailViewLinks));
                        break;
                    }
		}
		$this->renameDetailViewLinkDuplicate($linkModelList, 'LBL_DUPLICATE_AS_' . strtoupper($recordModel->get('potype')));
		return $linkModelList;
	}
        
        private function insertDetailViewLinkAfterDuplicate(&$linkModelList, &$newLink){
            foreach($linkModelList['DETAILVIEW'] as $index => $link){
                    if($link->get('linklabel') == 'LBL_DUPLICATE'){
                            array_splice($linkModelList['DETAILVIEW'], $index + 1, 0, array($newLink));
                            break;
                    }
            }
        }
        
        private function renameDetailViewLinkDuplicate(&$linkModelList, $label){
            foreach($linkModelList['DETAILVIEW'] as $index => $link){
                    if($link->get('linklabel') == 'LBL_DUPLICATE'){
                            $link->set('linklabel', $label);
                            break;
                    }
            }
        }
}
