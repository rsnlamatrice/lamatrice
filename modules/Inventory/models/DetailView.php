<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Inventory_DetailView_Model extends Vtiger_DetailView_Model {

	/**
	 * Function to get the detail view links (links and widgets)
	 * @param <array> $linkParams - parameters which will be used to calicaulate the params
	 * @return <array> - array of link models in the format as below
	 *                   array('linktype'=>list of link models);
	 */
	public function getDetailViewLinks($linkParams) {
		$linkModelList = parent::getDetailViewLinks($linkParams);
		$recordModel = $this->getRecord();
		$moduleName = $recordModel->getmoduleName();

		if(Users_Privileges_Model::isPermitted($moduleName, 'DetailView', $recordModel->getId())) {
			$detailViewLinks = array(
					'linklabel' => vtranslate('LBL_EXPORT_TO_PDF', $moduleName),
					'linkurl' => $recordModel->getExportPDFURL(),
					'linkicon' => ''
			);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($detailViewLinks);

			$sendEmailLink = array(
			    'linklabel' => vtranslate('LBL_SEND_MAIL_PDF', $moduleName),
			    'linkurl' => 'javascript:Inventory_Detail_Js.sendEmailPDFClickHandler(\''.$recordModel->getSendEmailPDFUrl().'\')',
			    'linkicon' => ''
			);
	    
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($sendEmailLink);
		}

		//ED150603 : remove "delete" menu if sent2compta
		if($recordModel->get('sent2compta'))
			foreach($linkModelList['DETAILVIEW'] as $index => $link)
				if(strpos($link->get('linkurl'), '.deleteRecord(') > 0){
					unset($linkModelList['DETAILVIEW'][$index]);
					break;
				}
		
		return $linkModelList;
	}

}
