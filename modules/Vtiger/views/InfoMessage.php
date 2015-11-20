<?php
/*+**********************************************************************************
 * ED151106
 *
 * exemple :			
	$request->set('message', 'Aucun élément ˆ imprimer.');
	$viewModel = new Vtiger_InfoMessage_View();
	$viewModel->preProcess($request);
	$viewModel->process($request);
	$viewModel->postProcess($request);
	exit();
			
 ************************************************************************************/

class Vtiger_InfoMessage_View extends Vtiger_View_Controller {

	function __construct() {
		parent::__construct();
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
	
	function postProcess(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$viewer->view('IndexPostProcess.tpl');
	}
	
	public function process(Vtiger_Request $request) {
		
		$module = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($module);
		
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE',$module);
		
		$viewer->assign('MESSAGE', $request->get('message'));
		
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->view('InfoMessage.tpl', $moduleName);
	}
}