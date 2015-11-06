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
			
			$request->set('message', $documentRecordModel->getName() . ' : aucun élement à imprimer.');
			$viewModel = new Vtiger_InfoMessage_View();
			$viewModel->preProcess($request);
			$viewModel->process($request);
			$viewModel->postProcess($request);
			exit();
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
				
			//Supprime les fichiers temporaires
			if(count($files) > 1)
				foreach($files as $fileName)
					if(file_exists($fileName))
						unlink($fileName);
					
			Vtiger_PDF_Generator::downloadFile($outputFileName, true);
		}
	}
	
}
