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
			$moduleModel = Vtiger_Module_Model::getInstance('RsnPrelevements');
			$dateVir = $moduleModel->getNextDateToGenerateVirnts($request->get('date_virements'));
			$prelVirements = $moduleModel->getExistingPrelVirements( $dateVir, 'FIRST' );
			$files = array();
			$filesPath = sys_get_temp_dir();
			foreach($prelVirements as $prelVirement){
				$recordId = $prelVirement->get('rsnprelevementsid');
				$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleModel->getName());
				$files[] = $recordModel->getPDF($filesPath);
				
				
				break;//debug
			}
			$outputFileName = $filesPath . '/' . 'Prelevements FIRST du ' . $dateVir->format('Y-m-d') . '.pdf';
			$outputFileName = Vtiger_PDF_Generator::mergeFiles($files, $outputFileName);
			//clear temp files
			//foreach($files as $fileName)
			//	unlink($fileName);
			var_dump($outputFileName);
		}
	}
}
