<?php

class RSNImport_ImportFromFile_View extends RSNImport_Import_View {

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

	public function getDefaultFileType() {
		return 'csv';
	}

	public function getDefaultFileEncoding() {
		return 'UTF-8';
	}

	public function getDefaultFileDelimiter() {
		return ';';
	}

	public function getImportUploadSize() {
		return '3145728';
	}

	public function getSupportedFileExtentions() {
		return RSNImport_Utils_Helper::getSupportedFileExtensions();
	}

	public function getFilePath() {
		return RSNImport_Utils_Helper::getImportFilePath($this->user, $this->request->get('for_module'));
	}

	public function deleteFile() {
		@unlink($this->getFilePath());
	}

	public function preImportData(Vtiger_Request $request) {
		if ($this->uploadFile($request)) {
		
			$fileReader = RSNImport_Utils_Helper::getFileReader($request, $this->user);
			if ($fileReader != null) {
				$returnValue = $this->parseAndSaveFile($fileReader);
				$this->deleteFile();
				return $returnValue;
			} else {
				$request->set('error_message', vtranslate('LBL_INVALID_FILE', 'Import'));
				$this->deleteFile();
				return false;
			}
		}

		return false;
	}

	function parseAndSaveFile(RSNImport_FileReader_Reader $fileReader) {
		//child class need to reimplement this function
		return false;
	}

	function uploadFile(Vtiger_Request $request) {
		if(RSNImport_Utils_Helper::validateFileUpload($request)) {
			return true;
		}

		return false;
	}
}