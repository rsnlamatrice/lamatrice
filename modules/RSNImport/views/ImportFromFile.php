<?php

class RSNImport_ImportFromFile_View extends RSNImport_Import_View {

	/**
	 * Method to show the configuration template of the import for the first step.
	 *  It display the select file template.
	 * @param Vtiger_Request $request: the curent request.
	 */
	function showConfiguration(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $request->getModule());
		$viewer->assign('SUPPORTED_FILE_TYPES', $this->getSupportedFileExtentions());//tmp function of import module !!!
		$viewer->assign('SUPPORTED_FILE_ENCODING', RSNImport_Utils_Helper::getSupportedFileEncoding());
		$viewer->assign('SUPPORTED_DELIMITERS', RSNImport_Utils_Helper::getSupportedDelimiters());
		$viewer->assign('IMPORT_ULPOAD_FILE_TYPE', $this->getDefaultFileType());
		$viewer->assign('IMPORT_ULPOAD_FILE_ENCODING', $this->getDefaultFileEncoding());
		$viewer->assign('IMPORT_ULPOAD_FILE_DELIMITER', $this->getDefaultFileDelimiter());
		$viewer->assign('IMPORT_UPLOAD_SIZE', $this->getImportUploadSize());

		return $viewer->view('ImportSelectFileStep.tpl', 'RSNImport');
	}

	/**
	 * Method to default file extention for this import.
	 *  This method should be overload in the child class.
	 * @return string - the default file extention.
	 */
	public function getDefaultFileType() {
		return 'csv';
	}

	/**
	 * Method to default file enconding for this import.
	 *  This method should be overload in the child class.
	 * @return string - the default file encoding.
	 */
	public function getDefaultFileEncoding() {
		return 'UTF-8';
	}

	/**
	 * Method to default file delimiter for this import. It is usefull for csv file.
	 *  This method should be overload in the child class.
	 * @return string - the default file delimiter.
	 */
	public function getDefaultFileDelimiter() {
		return ';';
	}

	/**
	 * Method to get the max upload file size for an import.
	 * @return string - the max upload file size.
	 */
	public function getImportUploadSize() {
		return '3145728';
	}

	/**
	 * Method to get the suported file extentions for this import.
	 *  This method should be overload in the child class.
	 * @return array - an array of string containing the supported file extentions.
	 */
	public function getSupportedFileExtentions() {
		return RSNImport_Utils_Helper::getSupportedFileExtensions();
	}

	/**
	 * Method to get temporary file path.
	 * @return string - the file path.
	 */
	public function getFilePath() {
		return RSNImport_Utils_Helper::getImportFilePath($this->user, $this->request->get('for_module'));
	}

	/**
	 * Method to upload the file in the temporary location.
	 */
	function uploadFile() {
		return RSNImport_Utils_Helper::validateFileUpload($this->request);
	}

	/**
	 * Method to delete temporary file.
	 */
	public function deleteFile() {
		@unlink($this->getFilePath());
	}

	/**
	 * Method to process to the first step (pre-importing data).
	 *  It call the parseAndSave methode that must be implemented in the child class.
	 */
	public function preImportData() {
		if ($this->uploadFile()) {
		
			$fileReader = RSNImport_Utils_Helper::getFileReader($this->request, $this->user);
			if ($fileReader != null) {
				$returnValue = $this->parseAndSaveFile($fileReader);
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
	 * @param $filereader : the reader of the uploaded file.
	 */
	function parseAndSaveFile(RSNImport_FileReader_Reader $fileReader) {
		return false;
	}
}