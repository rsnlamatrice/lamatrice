<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Documents_DetailView_Model extends Vtiger_DetailView_Model {

	/**
	 * Function to get the detail view links (links and widgets)
	 * @param <array> $linkParams - parameters which will be used to calicaulate the params
	 * @param boolean $countRelatedEntity - AV150619
	 * @return <array> - array of link models in the format as below
	 *                   array('linktype'=>list of link models);
	 */
	public function getDetailViewLinks($linkParams, $countRelatedEntity = false) {
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		$linkModelList = parent::getDetailViewLinks($linkParams, $countRelatedEntity);
		$recordModel = $this->getRecord();

		if ($recordModel->get('filestatus') && $recordModel->get('filename') && $recordModel->get('filelocationtype') === 'I') {
			$basicActionLink = array(
					'linktype' => 'DETAILVIEW',
					'linklabel' => 'LBL_DOWNLOAD_FILE',
					'linkurl' => $recordModel->getDownloadFileURL(),
					'linkicon' => ''
			);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		}
		$basicActionLink = array(
				'linktype' => 'DETAILVIEW',
				'linklabel' => 'LBL_CHECK_FILE_INTEGRITY',
				'linkurl' => $recordModel->checkFileIntegrityURL(),
				'linkicon' => ''
		);
		$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);

		if ($recordModel->get('filestatus') && $recordModel->get('filename') && $recordModel->get('filelocationtype') === 'I') {
			$emailModuleModel = Vtiger_Module_Model::getInstance('Emails');

			if($currentUserModel->hasModulePermission($emailModuleModel->getId())) {
				$basicActionLink = array(
						'linktype' => 'DETAILVIEW',
						'linklabel' => 'LBL_EMAIL_FILE_AS_ATTACHMENT',
						'linkurl' => "javascript:Documents_Detail_Js.triggerSendEmail('". ZEND_JSON::encode(array($recordModel->getId())) ."')",
						'linkicon' => ''
				);
				$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
			}
		}

		return $linkModelList;
	}

	/**
	 * Function to get the detail view widgets
	 * @return <Array> - List of widgets , where each widget is an Vtiger_Link_Model
	 *
	 * Ajout des blocks Widgets
	 * La table _Links ne semble pas tre utilise pour initialiser le tableau
	 * nécessite l'existence des fichiers Vtiger/%RelatedModule%SummaryWidgetContents.tpl (tout attach)
	 */
	public function getWidgets() {
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$widgetLinks = parent::getWidgets();
		
		foreach($widgetLinks as $index => &$widgetLink)
			if($widgetLink->get('linklabel') === 'LBL_UPDATES'){
				unset($widgetLinks[$index]);
				$widgetUpdatesLink = $widgetLink;
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

		$invoicesInstance = Vtiger_Module_Model::getInstance('Invoice');
		if($userPrivilegesModel->hasModuleActionPermission($invoicesInstance->getId(), 'DetailView')) {
			$relatedField = 'notesid';
			$widgets[] = array(
					'linktype' => 'DETAILVIEWWIDGET',
					'linklabel' => 'Invoice',
					'linkName'	=> $invoicesInstance->getName(),
					'linkurl' => 'module='.$this->getModuleName().'&view=Detail&record='.$this->getRecord()->getId().
							'&relatedModule=Invoice&mode=showRelatedRecords&page=1&limit=10',
					'action'	=> array('Add'),
					'actionlabel'	=> array('Créer'),
					'actionURL' =>	$invoicesInstance->getCreateRecordUrl() . '&sourceModule='.$this->getModuleName().'&sourceRecord='.$this->getRecord()->getId()
						. '&relationOperation=true&' . $relatedField .'='.$this->getRecord()->getId(),
			);
		}
			
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
			$widgetLinks[] = Vtiger_Link_Model::getInstanceFromValues($widgetDetails);
		}
		if($widgetUpdatesLink)
			$widgetLinks[] = $widgetUpdatesLink;
			
		return $widgetLinks;
	}
}
