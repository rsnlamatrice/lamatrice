<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Contacts_PopupAjax_View extends Contacts_Popup_View {
	
	function __construct() {
		parent::__construct();
		$this->exposeMethod('getListViewCount');
		$this->exposeMethod('getRecordsCount');
		$this->exposeMethod('getPageCount');
	}

	public function preProcess(Vtiger_Request $request) {
		return true;
	}

	public function postProcess(Vtiger_Request $request) {
		return true;
	}

	public function process (Vtiger_Request $request) {
		$mode = $request->get('mode');
		
		//ED150922 : si on cherche par code alors qu'on est positionné en recherche par nom, change le champ de recherche
		$search_key = $request->get('search_key');
		$search_value = $request->get('search_value');
		if((empty($mode) || $mode == 'getPageCount')){
			if(is_string($search_key)){
				$search_key = array($search_key);
				$search_value = array($search_value);
				$request->set('search_value', $search_value);
			}
			if(is_array($search_key) && count($search_key) === 1 && ($search_key[0] == 'lastname')){
				if(preg_match('/^C?\d+/i', $search_value[0])){
					$request->set('search_key', array('contact_no'));
				}
				elseif(strpos($search_value[0], ',') !== FALSE){
					$searchValues = preg_split('/\s*,\s*/', $search_value[0]);
					$request->set('search_key', 	array(array('lastname', 'firstname'), 				null, 	array('lastname', 'firstname')));
					$request->set('search_value', 	array(array($searchValues[0], $searchValues[1]), 	null, 	array($searchValues[1], $searchValues[0])));
					$request->set('operator', 		array(array('s', 's'), 								'OR', 	array('s', 's')));
				}
			}
		}
		//var_dump($request);
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();
		$this->initializeListViewContents($request, $viewer);

		echo $viewer->view('PopupContents.tpl', $moduleName, true);
	}
}