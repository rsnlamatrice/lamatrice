<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Invoice_MassActionAjax_View extends Vtiger_MassActionAjax_View {
	function __construct() {
		parent::__construct();
		$this->exposeMethod('showSend2ComptaForm');
	}

	/**
	 * Function returns the mass edit form
	 * @param Vtiger_Request $request
	 */
	function showSend2ComptaForm (Vtiger_Request $request){
		$moduleName = $request->getModule();
		$cvId = $request->get('viewname');
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');

		$viewer = $this->getViewer($request);

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$this->initSend2ComptaForm ($request);

		$viewer->assign('PICKIST_DEPENDENCY_DATASOURCE',Zend_Json::encode($picklistDependencyDatasource));
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('MODE', 'send2compta');
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('CVID', $cvId);
		$viewer->assign('SELECTED_IDS', $selectedIds);
		$viewer->assign('EXCLUDED_IDS', $excludedIds);
		$viewer->assign('MODULE_MODEL',$moduleModel); 
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('MODULE_MODEL', $moduleModel);
		$searchKey = $request->get('search_key');
		$searchValue = $request->get('search_value');
		$operator = $request->get('operator');
		if(!empty($operator)) {
			$viewer->assign('OPERATOR',is_array($operator) ? htmlspecialchars(json_encode($operator)) : $operator);
			$viewer->assign('ALPHABET_VALUE',is_array($searchValue) ? htmlspecialchars(json_encode($searchValue)) : $searchValue);
			$viewer->assign('SEARCH_KEY',is_array($searchKey) ? htmlspecialchars(json_encode($searchKey)) : $searchKey);
		}
	
		echo $viewer->view('Send2ComptaForm.tpl',$moduleName,true);
	}
	
	function initSend2ComptaForm (Vtiger_Request $request){
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		
		$controler = new Vtiger_MassSave_Action();
		$query = $controler->getRecordsQueryFromRequest($request);
		
		$query = 'SELECT COUNT(*) AS `count`, SUM(total) AS `total`
			FROM ('.$query.') _source_
			JOIN vtiger_invoicecf
				ON vtiger_invoicecf.invoiceid = _source_.invoiceid
			WHERE sent2compta IS NULL
			AND invoicestatus IN (?)
		';
		$params = array('Paid');
		
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, $params);
		if(!$result)
			$db->echoError();
		$viewer->assign('VALUES', $db->fetch_array($result));
	}
	
	
}
