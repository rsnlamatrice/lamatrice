<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Accounts_DetailView_Model extends Vtiger_DetailView_Model {

	/**
	 * Function to get the detail view links (links and widgets)
	 * @param <array> $linkParams - parameters which will be used to calicaulate the params
	 * @return <array> - array of link models in the format as below
	 *                   array('linktype'=>list of link models);
	 */
	public function getDetailViewLinks($linkParams) {
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$emailModuleModel = Vtiger_Module_Model::getInstance('Emails');
		$recordModel = $this->getRecord();

		$linkModelList = parent::getDetailViewLinks($linkParams);

		if($currentUserModel->hasModulePermission($emailModuleModel->getId())) {
			$basicActionLink = array(
				'linktype' => 'DETAILVIEWBASIC',
				'linklabel' => 'LBL_SEND_EMAIL',
				'linkurl' => 'javascript:Vtiger_Detail_Js.triggerSendEmail("index.php?module='.$this->getModule()->getName().
								'&view=MassActionAjax&mode=showComposeEmailForm&step=step1","Emails");',
				'linkicon' => ''
			);
			$linkModelList['DETAILVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		}

		//TODO: update the database so that these separate handlings are not required
		$index=0;
		foreach($linkModelList['DETAILVIEW'] as $link) {
			if($link->linklabel == 'View History' || $link->linklabel == 'Send SMS') {
				unset($linkModelList['DETAILVIEW'][$index]);
			} else if($link->linklabel == 'LBL_SHOW_ACCOUNT_HIERARCHY') {
				$linkURL = 'index.php?module=Accounts&view=AccountHierarchy&record='.$recordModel->getId();
				$link->linkurl = 'javascript:Accounts_Detail_Js.triggerAccountHierarchy("'.$linkURL.'");';
				unset($linkModelList['DETAILVIEW'][$index]);
				$linkModelList['DETAILVIEW'][$index] = $link;
			}
			$index++;
		}
		
		$CalendarActionLinks = array();
		$CalendarModuleModel = Vtiger_Module_Model::getInstance('Calendar');
		if($currentUserModel->hasModuleActionPermission($CalendarModuleModel->getId(), 'EditView')) {
			$CalendarActionLinks[] = array(
					'linktype' => 'DETAILVIEW',
					'linklabel' => 'LBL_ADD_EVENT',
					'linkurl' => $recordModel->getCreateEventUrl(),
					'linkicon' => ''
			);

			$CalendarActionLinks[] = array(
					'linktype' => 'DETAILVIEW',
					'linklabel' => 'LBL_ADD_TASK',
					'linkurl' => $recordModel->getCreateTaskUrl(),
					'linkicon' => ''
			);
		}

		$SMSNotifierModuleModel = Vtiger_Module_Model::getInstance('SMSNotifier');
		if($currentUserModel->hasModulePermission($SMSNotifierModuleModel->getId())) {
			$basicActionLink = array(
				'linktype' => 'DETAILVIEWBASIC',
				'linklabel' => 'LBL_SEND_SMS',
				'linkurl' => 'javascript:Vtiger_Detail_Js.triggerSendSms("index.php?module='.$this->getModule()->getName().
								'&view=MassActionAjax&mode=showSendSMSForm","SMSNotifier");',
				'linkicon' => ''
			);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicActionLink);
		}
		
		$moduleModel = $this->getModule();
		if($currentUserModel->hasModuleActionPermission($moduleModel->getId(), 'EditView')) {
			$massActionLink = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_TRANSFER_OWNERSHIP',
				'linkurl' => 'javascript:Vtiger_Detail_Js.triggerTransferOwnership("index.php?module='.$moduleModel->getName().'&view=MassActionAjax&mode=transferOwnership")',
				'linkicon' => ''
			);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}

        foreach($CalendarActionLinks as $basicLink) {
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($basicLink);
		}

		return $linkModelList;
	}

	/**
	 * Function to get the detail view widgets
	 * @return <Array> - List of widgets , where each widget is an Vtiger_Link_Model
	 *
	 * Ajout des blocks Widgets
	 * La table _Links ne semble pas tre utilise pour initialiser le tableau
	 * ncessite l'existence des fichiers Vtiger/%RelatedModule%SummaryWidgetContents.tpl (tout attach)
	 */
	public function getWidgets() {
		
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$widgetLinks = parent::getWidgets();
		
		/* Retire RecentsActivities pour le mettre en dernier */
		$last_widgets = array();
		for ($i = 0; $i < count($widgetLinks); $i++) {
			$widget = $widgetLinks[$i];
			if($widget->get('linklabel') == 'LBL_UPDATES'){
				$last_widgets[] = $widget;
				unset($widgetLinks[$i--]);
				break;
			}
		}
		
		$widgets = array();
		
		////inherited also by Contacts
		//if($this->getModuleName() == 'Contacts')
		//	return parent::getWidgets();
		
		$contactsInstance = Vtiger_Module_Model::getInstance('Contacts');
		if($userPrivilegesModel->hasModuleActionPermission($contactsInstance->getId(), 'DetailView')) {
			// il existe aussi Criteres4D dans SELECT * FROM `vtiger_links` WHERE `linktype` LIKE 'DETAILVIEWWIDGET' 
			
			
			$widgets[] = array(
					'linktype' => 'DETAILVIEWWIDGET',
					'linklabel' => 'Contacts',
					'linkName'	=> $contactsInstance->getName(),
					'linkurl' => 'module='.$this->getModuleName().'&view=Detail&record='.$this->getRecord()->getId().
							'&relatedModule=Contacts&mode=showRelatedRecords&page=1&limit=15',
					'action'	=> array('Select','Add'),
					'actionlabel'	=> array('Sélectionner', 'Créer'),
					'actionURL' =>	$contactsInstance->getListViewUrl()
			);
			
			$invoicesInstance = Vtiger_Module_Model::getInstance('Invoice');
			$relatedField = $this->getModuleName() == 'Contacts' ? 'contact_id' : 'account_id';
			$widgets[] = array(
					'linktype' => 'DETAILVIEWWIDGET',
					'linklabel' => 'Invoice',
					'linkName'	=> $invoicesInstance->getName(),
					'linkurl' => 'module='.$this->getModuleName().'&view=Detail&record='.$this->getRecord()->getId().
							'&relatedModule=Invoice&mode=showRelatedRecords&page=1&limit=15',
					'action'	=> array('Add'),
					'actionlabel'	=> array('Créer'),
					'actionURL' =>	$invoicesInstance->getCreateRecordUrl() . '&sourceModule='.$this->getModuleName().'&sourceRecord='.$this->getRecord()->getId()
						. '&relationOperation=true&' . $relatedField .'='.$this->getRecord()->getId(),
			);
			$documentsInstance = Vtiger_Module_Model::getInstance('Documents');
			$widgets[] = array(
					'linktype' => 'DETAILVIEWWIDGET',
					'linklabel' => 'Documents',
					'linkName'	=> $documentsInstance->getName(),
					'linkurl' => 'module='.$this->getModuleName().'&view=Detail&record='.$this->getRecord()->getId().
							'&relatedModule=Documents&mode=showRelatedRecords&page=1&limit=15',
					'action'	=> array('Select'),
					'actionlabel'	=> array('Sélectionner'),
					'actionURL' =>	$documentsInstance->getListViewUrl()
			);
		}

		foreach ($widgets as $widgetDetails) {
			$widgetLinks[] = Vtiger_Link_Model::getInstanceFromValues($widgetDetails);
		}

		return array_merge($widgetLinks, $last_widgets);
	}

}
