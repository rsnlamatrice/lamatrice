<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class RSNMediaRelations_DetailView_Model extends Vtiger_DetailView_Model {

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
		$widgets = array();
		
		$relatedInstance = Vtiger_Module_Model::getInstance('Documents');
		$widgets[] = array(
				'linktype' => 'DETAILVIEWWIDGET',
				'linklabel' => 'Documents',
				'linkName'	=> $relatedInstance->getName(),
				'linkurl' => 'module='.$this->getModuleName().'&view=Detail&record='.$this->getRecord()->getId().
						'&relatedModule=Documents&mode=showRelatedRecords&page=1&limit=15',
				'action'	=> array('Select','Add'),
				'actionlabel'	=> array('Sélectionner', 'Créer'),
				'actionURL' =>	$relatedInstance->getListViewUrl()
		);
		
		foreach ($widgets as $widgetDetails) {
			$widgetLinks[] = Vtiger_Link_Model::getInstanceFromValues($widgetDetails);
		}

		return $widgetLinks;
	}

}
