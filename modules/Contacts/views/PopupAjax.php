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
		if((empty($mode) || $mode == 'getPageCount')
		&& $search_key == 'lastname'
		&& preg_match('/^C?\d+/i', $search_value)){
			$request->set('search_key', 'contact_no');
		}
		
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