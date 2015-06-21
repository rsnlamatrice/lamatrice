<?php

class RSNImportSources_ImportFromDB_View extends RSNImportSources_Import_View {

	/**
	 * Method to show the configuration template of the import for the first step.
	 *  It display the select db template.
	 * @param Vtiger_Request $request: the curent request.
	 */
	function showConfiguration(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $request->getModule());
		$viewer->assign('SUPPORTED_DB_TYPES', $this->getSupportedDBTypes());//tmp function of import module !!!
		$viewer->assign('IMPORT_ULPOAD_DB_TYPE', $this->getDefaultDBType());

		return $viewer->view('ImportSelectFileStep.tpl', 'RSNImportSources');
	}

	/**
	 * Method to default db type for this import.
	 *  This method should be overload in the child class.
	 * @return string - the default db type.
	 */
	public function getDefaultDBType() {
		return 'mysql';
	}

	/**
	 * Method to get the suported DB types for this import.
	 *  This method should be overload in the child class.
	 * @return array - an array of string containing the supported DB types.
	 */
	public function getSupportedDBTypes() {
		return RSNImportSources_Utils_Helper::getSupportedDBTypes();
	}

	/**
	 * Method to get DB connexion string.
	 * @return string - the DB connexion string.
	 */
	public function getDBConnexionString() {
		return RSNImportSources_Utils_Helper::getImportDBConnexionString($this->user, $this->request->get('for_module'));
	}

	/**
	 * Method to process to the first step (pre-importing data).
	 *  It call the parseAndSave methode that must be implemented in the child class.
	 */
	public function preImportData() {
		if ($this->uploadFile()) {
		
			$dbReader = RSNImportSources_Utils_Helper::getDBReader($this->request, $this->user);
			if ($dbReader != null) {
				$returnValue = $this->parseAndSaveData($fileReader);
				$this->deleteFile();
				return $returnValue;
			} else {
				$this->request->set('error_message', vtranslate('LBL_INVALID_FILE', 'Import'));
				$this->deleteFile();
				return false;
			}
		}

		return false;
	}

	/**
	 * Method to parse the uploaded file and save data to the temporary pre-import table.
	 *  This method must be overload in the child class.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 */
	function parseAndSaveData(RSNImportSources_FileReader_Reader $fileReader) {
		return false;
	}
}