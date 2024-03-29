<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

/* ED140907 cache is session */
//echo 'Vtiger_Cache::clearSessionCache';
Vtiger_Cache::clearSessionCache();
 
class Users_Login_View extends Vtiger_View_Controller {

	function loginRequired() {
		return false;
	}
	
	function checkPermission(Vtiger_Request $request) {
		return true;
	}
	
	function preProcess(Vtiger_Request $request, $display = true) {
		$viewer = $this->getViewer($request);
		$viewer->assign('PAGETITLE', "La Matrice");
		$viewer->assign('SCRIPTS',$this->getHeaderScripts($request));
		$viewer->assign('STYLES',$this->getHeaderCss($request));
		$viewer->assign('CURRENT_VERSION', vglobal('vtiger_current_version'));
		if($display) {
			$this->preProcessDisplay($request);
		}
	}

	function process (Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		
		include('config.login.ui.php');
		
		$viewer->assign('HELP_LINKS', $LOGIN_LINKS);
		$viewer->assign('IMAGES', $LOGIN_IMAGES);
		
		$viewer->assign('MODULE', $moduleName);
		$viewer->view('Login.tpl', 'Users');
	}
	
	function postProcess(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$viewer->view('IndexPostProcess.tpl');
	}
}