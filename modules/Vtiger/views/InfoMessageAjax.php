<?php
/*+**********************************************************************************
 * ED151106
 *
 * exemple :			
	$request->set('message', 'Aucun Žlement ˆ imprimer.');
	$viewModel = new Vtiger_InfoMessage_View();
	$viewModel->preProcess($request);
	$viewModel->process($request);
	$viewModel->postProcess($request);
	exit();
			
 ************************************************************************************/

class Vtiger_InfoMessageAjax_View extends Vtiger_InfoMessage_View {

	
	function preProcess(Vtiger_Request $request, $display = true) {}
	
	function postProcess(Vtiger_Request $request) {}
	
	function process(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		//Pas de bouton "Retour", mais "Fermer"
		$viewer->assign('BUTTON_CLOSE', true);
		parent::process($request);
	}
}