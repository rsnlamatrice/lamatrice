<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class RSNMedias_DetailView_Model extends Vtiger_DetailView_Model {

	/**
	 * Function to get the detail view widgets
	 * @return <Array> - List of widgets , where each widget is an Vtiger_Link_Model
	 *
	 * Ajout des blocks Widgets
	 * La table _Links ne semble pas être utilisée pour initialiser le tableau
	 * nécessite l'existence des fichiers Vtiger/%RelatedModule%SummaryWidgetContents.tpl (tout attaché)
	 */
	public function getWidgets() {
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$widgetLinks = parent::getWidgets();
		$widgets = array();

		$relatedInstance = Vtiger_Module_Model::getInstance('RSNMediaRelations');
		$widgets[] = array(
				'linktype' => 'DETAILVIEWWIDGET',
				'linklabel' => 'Historique des relations avec le média',
				'linkName'	=> $relatedInstance->getName(),
				'linkField'	=> 'rsnmediaid', /*ED141126*/
				'linkurl' => 'module='.$this->getModuleName().'&view=Detail&record='.$this->getRecord()->getId().
						'&relatedModule=RSNMediaRelations&mode=showRelatedRecords&page=1&limit=15',
				'action'	=>	array('Add'),
				'actionlabel'	=>	array('Nouvelle relation'),
				'actionURL' =>	$relatedInstance->getListViewUrl()
		);
		

		$relatedInstance = Vtiger_Module_Model::getInstance('RSNMediaContacts');
		$widgets[] = array(
				'linktype' => 'DETAILVIEWWIDGET',
				'linklabel' => 'MEDIACONTACTS',
				'linkName'	=> $relatedInstance->getName(),
				'linkField'	=> 'rsnmediaid', /*ED141126*/
				'linkurl' => 'module='.$this->getModuleName().'&view=Detail&record='.$this->getRecord()->getId().
						'&relatedModule=RSNMediaContacts&mode=showRelatedRecords&page=1&limit=15',
				'action'	=>	array('Add', 'Select'),
				'actionlabel'	=>	array('Nouveau', 'Sélectionner'),
				'actionURL' =>	$relatedInstance->getListViewUrl()
		);
		
		foreach ($widgets as $widgetDetails) {
			$widgetLinks[] = Vtiger_Link_Model::getInstanceFromValues($widgetDetails);
		}

		return $widgetLinks;
	}

}
