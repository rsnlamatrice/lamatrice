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
	 * @param boolean $countRelatedEntity - AV150619
	 * @return <array> - array of link models in the format as below
	 *                   array('linktype'=>list of link models);
	 */
	public function getDetailViewLinks($linkParams, $countRelatedEntity = false) {
		$linkModelList = parent::getDetailViewLinks($linkParams, $countRelatedEntity);
		$recordModel = $this->getRecord();
		$moduleName = $recordModel->getmoduleName();

		if($recordModel->isDuplicatable()
		&& $recordModel->get('typedossier') !== 'Avoir'
		&& $recordModel->get('typedossier') !== 'Remboursement') {
			//ED151026
			$duplicateLinkModel = array(
						'linktype' => 'DETAILVIEWBASIC',
						'linklabel' => 'LBL_DUPLICATE_AS_CREDIT_INVOICE',
						'linkurl' => $recordModel->getDuplicateRecordUrl() . '&typedossier=Avoir',
						'linkicon' => ''
				);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($duplicateLinkModel);
			//ED151026
			$duplicateLinkModel = array(
						'linktype' => 'DETAILVIEWBASIC',
						'linklabel' => 'LBL_DUPLICATE_AS_REFUND',
						'linkurl' => $recordModel->getDuplicateRecordUrl() . '&typedossier=Remboursement',
						'linkicon' => ''
				);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($duplicateLinkModel);
		}

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
