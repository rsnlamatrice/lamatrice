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
			//Fusion en un seul PDF (ou un zip si ghostscript n'est pas installé)
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
					default: //zip
						header("Content-type: application/octet-stream");
						header("Content-Disposition: filename=\"".$path_parts["basename"]."\"");
						break;
					}
					header("Content-length: $fsize");
					header("Cache-control: private"); //use this to open files directly
					while(!feof($fd)) {
						echo fread($fd, 4096);
					}
				}
				fclose ($fd);
				
				//Supprime les fichiers temporaires
				foreach($files as $fileName)
					unlink($fileName);
					
				unlink($outputFileName);
				
				exit;
			}
			echo "<code>Désolé, le fichier n'a pas pu être généré</code>";
		}
	}
}
