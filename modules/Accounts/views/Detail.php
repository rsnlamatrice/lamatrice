<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */
/* ED140921
 * note : hérité par Contacts
 */
class Accounts_Detail_View extends Vtiger_Detail_View {

	/**
	 * Function to get activities
	 * @param Vtiger_Request $request
	 * @return <List of activity models>
	 */
	public function getActivities(Vtiger_Request $request) {
		$moduleName = 'Calendar';
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if($currentUserPriviligesModel->hasModulePermission($moduleModel->getId())) {
			$moduleName = $request->getModule();
			$recordId = $request->get('record');

			$pageNumber = $request->get('page');
			if(empty ($pageNumber)) {
				$pageNumber = 1;
			}
			$pagingModel = new Vtiger_Paging_Model();
			$pagingModel->set('page', $pageNumber);
			$pagingModel->set('limit', 10);

			if(!$this->record) {
				$this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
			}
			$recordModel = $this->record->getRecord();
			$moduleModel = $recordModel->getModule();

			$relatedActivities = $moduleModel->getCalendarActivities('', $pagingModel, 'all', $recordId);

			$viewer = $this->getViewer($request);
			$viewer->assign('RECORD', $recordModel);
			$viewer->assign('MODULE_NAME', $moduleName);
			$viewer->assign('PAGING_MODEL', $pagingModel);
			$viewer->assign('PAGE_NUMBER', $pageNumber);
			$viewer->assign('ACTIVITIES', $relatedActivities);

			return $viewer->view('RelatedActivities.tpl', $moduleName, true);
		}
	}


	
	/**
	 * ED141210
	 * en ajoutant NO_ACTIVITIES_WIDGET on dŽsactive le chargement des Events
	 * en fait, a permet, dans le SummaryView d'inverser les widgets de gauche ˆ droite 
	 */
	public function process(Vtiger_Request $request) {
		
		$viewer = $this->getViewer($request);
		/* ED141210 court-circuite les activitŽs */
		$viewer->assign('NO_ACTIVITIES_WIDGET', true);
		/* ED150102 compte de référence */
		$viewer->assign('ACCOUNT_ID', $request->get('record'));

		return parent::process($request);
	}


	/**
	 * Function returns related records based on related moduleName
	 * @param Vtiger_Request $request
	 * @return <type>
	 */
	function showRelatedRecords(Vtiger_Request $request) {
		//default order
		if(!$request->get('orderby')){
			switch($request->get('relatedModule')){
			 case 'RSNAboRevues':
				$request->set('orderby', 'debutabo');
				$request->set('sortorder', 'DESC');
				break;
			}
		}
		return parent::showRelatedRecords($request);
	}
}
