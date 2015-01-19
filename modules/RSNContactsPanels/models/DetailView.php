<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class RSNContactsPanels_DetailView_Model extends Vtiger_DetailView_Model {

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

		$widgets[] = array(
				'linktype' 	=> 'DETAILVIEWWIDGET',
				'linklabel'	=> 'Exécution',
				'linkName'	=> 'execution',
				'linkField'	=> 'rsncontactspanelsid', 
				'linkurl' 	=> 'module='.$this->getModuleName().'&view=Detail&record='.$this->getRecord()->getId().
						   '&relatedModule=Execution&mode=showRelatedRecords'
						   .'&cancel-once=1', // cancel auto widget loading. User needs to use Refresh button.
				'action'	=> array('Refresh'),
				'actionlabel'	=> array('Calculer'),
				'actionURL' =>	''
		);

		$relatedInstance = Vtiger_Module_Model::getInstance('RSNPanelsVariables');
		$widgets[] = array(
				'linktype' => 'DETAILVIEWWIDGET',
				'linklabel' => 'Variables d\'entrée',
				'linkName'	=> $relatedInstance->getName(),
				'linkField'	=> 'mediacontactid', /*ED141126*/
				'linkurl' => 'module='.$this->getModuleName().'&view=Detail&record='.$this->getRecord()->getId().
						'&relatedModule=RSNPanelsVariables&mode=showRelatedRecords&page=1&limit=25',
				'action'	=>	array('Refresh'),
				'actionlabel'	=>	array(''),
				'actionURL' =>	''
		);
		
		foreach ($widgets as $widgetDetails) {
			$widgetLinks[] = Vtiger_Link_Model::getInstanceFromValues($widgetDetails);
		}

		return $widgetLinks;
	}

}
