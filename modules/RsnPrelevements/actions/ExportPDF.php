<?php
/*+***********************************************************************************
 * ED151104
 *************************************************************************************/

class RsnPrelevements_ExportPDF_Action extends Vtiger_Action_Controller {

	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		if(!Users_Privileges_Model::isPermitted($moduleName, 'DetailView', $recordId)) {
			throw new AppException(vtranslate('LBL_PERMISSION_DENIED', $moduleName));
		}
	}

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		if($recordId){
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			$recordModel->getPDF();
		}
		else{
			//Prélèvements d'après la date
			$moduleModel = Vtiger_Module_Model::getInstance('RsnPrelevements');
			$dateVir = $moduleModel->getNextDateToGenerateVirnts($request->get('date_virements'));
			$prelVirements = $moduleModel->getExistingPrelVirements( $dateVir, 'FIRST' );
			$files = array();
			$filesPath = sys_get_temp_dir();
			//Génère chaque PDF
			foreach($prelVirements as $prelVirement){
				$recordId = $prelVirement->get('rsnprelevementsid');
				$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleModel->getName());
				$files[] = $recordModel->getPDF($filesPath);
			}
			if(!$files){
				$request->set('message', 'Prélèvements FIRST : aucun élement à imprimer.');
				$viewModel = new Vtiger_InfoMessage_View();
				$viewModel->preProcess($request);
				$viewModel->process($request);
				$viewModel->postProcess($request);
				exit();
			}
			else {
				//Fusion en un seul PDF (ou un zip si ghostscript n'est pas installé)
				$outputFileName = $filesPath . '/' . 'Prelevements FIRST du ' . $dateVir->format('Y-m-d') . '.pdf';
				$outputFileName = Vtiger_PDF_Generator::mergeFiles($files, $outputFileName);
				if(file_exists($outputFileName)){
					
					//Supprime les fichiers temporaires
					foreach($files as $fileName)
						if(file_exists($fileName))
							unlink($fileName);
					
					//ça bogue, parfois, sous Chrome
					
					Vtiger_PDF_Generator::downloadFile($outputFileName, true);
				}
			}
		}
	}
}
