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
			}
			$outputFileName = $filesPath . '/' . 'Prelevements FIRST du ' . $dateVir->format('Y-m-d') . '.pdf';
			$outputFileName = Vtiger_PDF_Generator::mergeFiles($files, $outputFileName);
			if(file_exists($outputFileName)){
				if ($fd = fopen ($outputFileName, "r")) {
					$fsize = filesize($outputFileName);
					$path_parts = pathinfo($outputFileName);
					$ext = strtolower($path_parts["extension"]);
					switch ($ext) {
					case "pdf":
						header("Content-type: application/pdf");
						header("Content-Disposition: attachment; filename=\"".$path_parts["basename"]."\""); // use 'attachment' to force a file download
						break;
						// add more headers for other content types here
					default;
						header("Content-type: application/octet-stream");
						header("Content-Disposition: filename=\"".$path_parts["basename"]."\"");
						break;
					}
					header("Content-length: $fsize");
					header("Cache-control: private"); //use this to open files directly
					while(!feof($fd)) {
						$buffer = fread($fd, 2048);
						echo $buffer;
					}
				}
				fclose ($fd);
				
				unlink($outputFileName);
				
				exit;
			}
			echo "<code>Désolé, le fichier n'a pas pu être généré</code>";
		}
	}
}
