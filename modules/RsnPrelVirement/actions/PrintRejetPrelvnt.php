<?php
/*+***********************************************************************************
 * ED151120
 *************************************************************************************/

class RsnPrelVirement_PrintRejetPrelvnt_Action extends Vtiger_Mass_Action {
	
	function checkPermission(Vtiger_Request $request) {
		return true;
	}

	public function process(Vtiger_Request $request) {
		
		$recordId = $request->get('record');
		$moduleName = $request->getModule();
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		
		if($recordModel->get('rsnprelvirstatus') === 'Ok'){
			$msg = 'Veuillez d\'abord définir l\'état du prélèvement en rejet.';
			$request->set('message', $msg);
			$viewModel = new Vtiger_InfoMessage_View();
			$viewModel->preProcess($request);
			$viewModel->process($request);
			$viewModel->postProcess($request);
			exit();
		}
		
		$this->generatePDF($request, $recordModel);
	}
	
	/**
	 * Crée un PDF 
	 *
	 */
	public function generatePDF($request, $recordModel){
		$filesPath = sys_get_temp_dir();
		$outputFileName = $recordModel->getRejetPrelvntPDF($filesPath);
		if(file_exists($outputFileName)){
			Vtiger_PDF_Generator::downloadFile($outputFileName, true);
		}
		else {
			$msg = 'Désolé, le PDF n\'a pas pu être généré.';
			$request->set('message', $msg);
			$viewModel = new Vtiger_InfoMessageAjax_View();
			$viewModel->process($request);
			exit();
		}
	}
	
}
