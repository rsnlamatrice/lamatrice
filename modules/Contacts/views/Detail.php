<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Contacts_Detail_View extends Accounts_Detail_View {

	
	public function showModuleDetailView(Vtiger_Request $request) {
		$recordId = $request->get('record');
		$moduleName = $request->getModule();

		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		
		/* ED141005
		ne fonctionne pas pour que la valeur soit récupérée dans les .tpl 
		$values = $recordModel->getPicklistValuesDetails('isgroup');
		$key = $recordModel->get('isgroup');
		$recordModel->set('isgroup', $values[$key]['label']);*/
		
		$viewer = $this->getViewer($request);
		$viewer->assign('IMAGE_DETAILS', $recordModel->getImageDetails());

		return parent::showModuleDetailView($request);
	}
	
	/**
	 * ED141210
	 * en ajoutant NO_ACTIVITIES_WIDGET on désactive le chargement des Events
	 */
	public function process(Vtiger_Request $request) {
		
		$viewer = $this->getViewer($request);
		/* ED141210 court-circuite les activités
		TODO : mettre en paramètre */
		$viewer->assign('NO_ACTIVITIES_WIDGET', true);

		return parent::process($request);
	}
	
	function preProcessDisplay(Vtiger_Request $request) {
		
		$statsFields = array(
			'Dons 2015' => 'xxxx €',
			'Prélèvements 2015' => 'xxxx €',
			'Boutique 2015' => 'xxxx €',
			'Dons 2014' => 'xxxx €',
			'Prélèvements 2014' => 'xxxx €',
			'Boutique 2014' => 'xxxx €',
			'Dons 2013' => 'xxxx €',
			'Prélèvements 2013' => 'xxxx €',
			'Boutique 2013' => 'xxxx €',
		);
		
		$viewer = $this->getViewer($request);
		$viewer->assign('STATISTICS_FIELDS', $statsFields);
		
		parent::preProcessDisplay($request);
	}
}
