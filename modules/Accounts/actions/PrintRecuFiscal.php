<?php
/*+***********************************************************************************
 * ED151105
 *************************************************************************************/

class Accounts_PrintRecuFiscal_Action extends Vtiger_Mass_Action {
	
	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		if(!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'Save')) {
			throw new AppException(vtranslate($moduleName).' '.vtranslate('LBL_NOT_ACCESSIBLE'));
		}
	}

	public function process(Vtiger_Request $request) {
		$notes_ids = $request->get('related_ids');
		$asColumnName = 'contactid';
		$sourceIdsQuery = $this->getRecordsQueryFromRequest($request, $asColumnName);
		
		$documentRecordModel = Vtiger_Record_Model::getInstanceById($notes_ids[0], 'Documents');
		
		$this->generatePDF($request, $sourceIdsQuery, $documentRecordModel);
	}
	
	/**
	 * Crée un PDF pour les contacts donnés
	 *
	 */
	public function generatePDF($request, $sourceIdsQuery, $documentRecordModel){
		$moduleName = $request->getModule();
		global $adb;
		$result = $adb->query($sourceIdsQuery);
		
		$files = array();
		$filesPath = sys_get_temp_dir();
			
		while($row = $adb->fetch_row($result)){
			$recordId = $row[0];
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			if($moduleName === 'Contacts'){
				$contactRecordModel = $recordModel;
				$recordModel = $recordModel->getAccountRecordModel(false);
				if(!$recordModel)
					continue;
				$recordId = $recordModel->getId();
				if($files[$recordId])
					continue;
			}
			$fileName = $recordModel->getRecuFiscalPDF($filesPath, $documentRecordModel, $contactRecordModel);
			if($fileName)
				$files[$recordId] = $fileName;
		}
			
		if($request->get('sendEmail')){
			//TODO
		}
		if(!$files){
			return false;
		}
		elseif(count($files) === 1){
			foreach($files as $fileName){
				$outputFileName = $fileName;
				break;
			}
		}
		else{
			//Fusion en un seul PDF (ou un zip si ghostscript n'est pas installé)
			$outputFileName = $filesPath . '/' . $documentRecordModel->getName() . '.pdf';
			$outputFileName = Vtiger_PDF_Generator::mergeFiles($files, $outputFileName);
		}
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
