<?php
/*+***********************************************************************************
 * ED151120
 *************************************************************************************/

class RsnPrelVirement_DetailView_Model extends Vtiger_DetailView_Model {

	/**
	 * Function to get the detail view links (links and widgets)
	 * @param <array> $linkParams - parameters which will be used to calicaulate the params
	 * @param boolean $countRelatedEntity - AV150619
	 * @return <array> - array of link models in the format as below
	 *                   array('linktype'=>list of link models);
	 */
	public function getDetailViewLinks($linkParams, $countRelatedEntity = false) {
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$recordModel = $this->getRecord();

		$linkModelList = parent::getDetailViewLinks($linkParams, $countRelatedEntity);
		$moduleModel = $this->getModule();
		if($currentUserModel->hasModuleActionPermission($moduleModel->getId(), 'EditView')) {
			//ED151105 impression du reçu fiscal
			$massActionLink = array(
				'linktype' => 'DETAILVIEW',
				'linklabel' => 'LBL_PRINT_REJET_PRELVNT',
				'linkurl' => $recordModel->getExportRejetPrelvntPDFURL(),
				'linkicon' => ''
			);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
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
		
		/* ED150000 Retire RecentsActivities pour le mettre en dernier */
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
			
			$rsnAboRevuesInstance = Vtiger_Module_Model::getInstance('RSNAboRevues');
			$relatedField = $this->getModuleName() == 'Contacts' ? 'contact_id' : 'account_id';
			$widgets[] = array(
					'linktype' => 'DETAILVIEWWIDGET',
					'linklabel' => 'LBL_RSNABOREVUES',
					'linkName'	=> $rsnAboRevuesInstance->getName(),
					'linkurl' => 'module='.$this->getModuleName().'&view=Detail&record='.$this->getRecord()->getId().
							'&relatedModule=RSNAboRevues&mode=showRelatedRecords&page=1&limit=15',
					'action'	=> array('Add'),
					'actionlabel'	=> array('Créer'),
					'actionURL' =>	$rsnAboRevuesInstance->getCreateRecordUrl() . '&sourceModule='.$this->getModuleName().'&sourceRecord='.$this->getRecord()->getId()
						. '&relationOperation=true&' . $relatedField .'='.$this->getRecord()->getId(),
			);
		}

		foreach ($widgets as $widgetDetails) {
			$widgetLinks[] = Vtiger_Link_Model::getInstanceFromValues($widgetDetails);
		}

		return array_merge($widgetLinks, $last_widgets);
	}

}
