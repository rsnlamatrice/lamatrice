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
	 * Function to get the detail view widgets
	 * @return <Array> - List of widgets , where each widget is an Vtiger_Link_Model
	 *
	 * Ajout des blocks Widgets
	 * La table _Links ne semble pas ?tre utilis?e pour initialiser le tableau
	 * n?écessite l'existence des fichiers Vtiger/%RelatedModule%SummaryWidgetContents.tpl (tout attach?)
	 */
	public function getWidgets() {
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$widgetLinks = parent::getWidgets();
		
		foreach($widgetLinks as $index => &$widgetLink)
			if($widgetLink->get('linklabel') === 'LBL_UPDATES'){
				unset($widgetLinks[$index]);
				$widgetUpdatesLink = $widgetLink;
				break;
			}
			
		$productsInstance = Vtiger_Module_Model::getInstance('Products');
		if($userPrivilegesModel->hasModuleActionPermission($productsInstance->getId(), 'DetailView')) {
			$widgets[] = array(
					'linktype' => 'DETAILVIEWWIDGET',
					'linklabel' => 'Products',
					'linkName'	=> $productsInstance->getName(),
					'linkurl' => 'module='.$this->getModuleName().'&view=Detail&record='.$this->getRecord()->getId().
							'&relatedModule='.$productsInstance->getName().'&mode=showRelatedRecords&page=1&limit=20',
					'action'	=> array('Select'),
					'actionlabel'	=> array('Sélectionner'),
					'actionURL' =>	$productsInstance->getListViewUrl()
			);
		}
		
		if($widgetUpdatesLink)
			$widgets[] = $widgetUpdatesLink;
			
		$servicesInstance = Vtiger_Module_Model::getInstance('Services');
		if($userPrivilegesModel->hasModuleActionPermission($servicesInstance->getId(), 'DetailView')) {
			$widgets[] = array(
					'linktype' => 'DETAILVIEWWIDGET',
					'linklabel' => 'Services',
					'linkName'	=> $servicesInstance->getName(),
					'linkurl' => 'module='.$this->getModuleName().'&view=Detail&record='.$this->getRecord()->getId().
							'&relatedModule='.$servicesInstance->getName().'&mode=showRelatedRecords&page=1&limit=10',
					'action'	=> array('Select'),
					'actionlabel'	=> array('Sélectionner'),
					'actionURL' =>	$servicesInstance->getListViewUrl()
			);
		}
			
		foreach ($widgets as $widgetDetails) {
			if(is_array($widgetDetails))
				$widgetLinks[] = Vtiger_Link_Model::getInstanceFromValues($widgetDetails);
			else
				$widgetLinks[] = $widgetDetails;
		}
			
		return $widgetLinks;
	}

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

		if($moduleName === 'Invoice'
		&& $recordModel->isDuplicatable()
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
