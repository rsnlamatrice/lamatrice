<?php

class RSNImportSources_ImportFromFile_View extends RSNImportSources_Import_View {

	var $columnName_indexes = false;//tableau nom => index
	
	/**
	 * Method to show the configuration template of the import for the first step.
	 *  It display the select file template.
	 * @param Vtiger_Request $request: the curent request.
	 */
	function showConfiguration(Vtiger_Request $request) {
		$viewer = $this->initConfiguration($request);

		return $viewer->view('ImportSelectFileStep.tpl', 'RSNImportSources');
	}

	/**
	 * Method to initialize the configuration template of the import for the first step.
	 *  It display the select file template.
	 * @param Vtiger_Request $request: the curent request.
	 * @return viewer
	 */
	function initConfiguration(Vtiger_Request $request) {
		$viewer = parent::initConfiguration($request);
		$viewer->assign('SUPPORTED_FILE_TYPES', $this->getSupportedFileExtentions());//tmp function of import module !!!
		$viewer->assign('SUPPORTED_FILE_ENCODING', RSNImportSources_Utils_Helper::getSupportedFileEncoding());
		$viewer->assign('SUPPORTED_DELIMITERS', RSNImportSources_Utils_Helper::getSupportedDelimiters());
		$viewer->assign('IMPORT_ULPOAD_FILE_TYPE', $this->getDefaultFileType());
		$viewer->assign('IMPORT_ULPOAD_FILE_ENCODING', $this->getDefaultFileEncoding());
		$viewer->assign('IMPORT_ULPOAD_FILE_DELIMITER', $this->getDefaultFileDelimiter());
		$viewer->assign('IMPORT_UPLOAD_SIZE', $this->getImportUploadSize());
		
		$viewer->assign('IMPORT_FILE_LOCALPATH', '');//Chemin du dernier fichier local au serveur
		
		return $viewer;
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
		global $upload_maxsize;
		return $upload_maxsize;
		//return '3145728';
	}

	/**
	 * Method to get the suported file extentions for this import.
	 *  This method should be overload in the child class.
	 * @return array - an array of string containing the supported file extentions.
	 */
	public function getSupportedFileExtentions() {
		return RSNImportSources_Utils_Helper::getSupportedFileExtensions();
	}

	/**
	 * Method to get temporary file path.
	 * @return string - the file path.
	 */
	public function getFilePath($nFile = 0) {
		return RSNImportSources_Utils_Helper::getImportFilePath($this->user, $this->request->get('for_module'), $nFile);
	}

	/**
	 * Method to get the maximum files number to preImport in an automatic context.
	 * @return integer - the maximum files number to preImport in an automatic context.
	 */
	public function getDefaultAutoFilesMax(){
		return 1;
	}
	
	/**
	 * Method to upload the file in the temporary location.
	 */
	function uploadFile() {
		return RSNImportSources_Utils_Helper::validateFileUpload($this->request);
	}

	/**
	 * Method to delete temporary file.
	 */
	public function deleteFile() {
		$nFile = 0;
		while(file_exists($this->getFilePath($nFile)))
			@unlink($this->getFilePath($nFile++));
	}
	
	/**
	 * Method to process to the first step (pre-importing data).
	 *  It calls the parseAndSave methode that must be implemented in the child class.
	 */
	public function preImportData() {
		if ($this->uploadFile()) {
		
			$fileReader = RSNImportSources_Utils_Helper::getFileReader($this->request, $this->user);
			if ($fileReader != null) {
				
				//ED150827
				$this->updateLastImportField();
				
				$returnValue = $this->parseAndSaveFile($fileReader);
				if($returnValue)
					$this->postPreImportData();
				$this->deleteFile();
				return $returnValue;
			} else {
				var_dump('error_message', vtranslate('LBL_INVALID_FILE', 'Import'));
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
	function parseAndSaveFile(RSNImportSources_FileReader_Reader $fileReader) {
		return false;
	}
		
	/** Prépare les données pour un pré-import automatique
	 *
	 */
	function prepareAutoPreImportData(){
		parent::prepareAutoPreImportData();
	
		$legalExtension = $this->getDefaultFileType();
		$this->request->set('file_type', $legalExtension);
		$this->request->set('file_encoding', $this->getDefaultFileEncoding());
		$this->request->set('delimiter', $this->getDefaultFileDelimiter());
		$this->request->set('auto_files_max', $this->getDefaultAutoFilesMax());
		
		if(!$this->recordModel->get('autoenabled')
		|| !$this->recordModel->get('autosourcedata'))
			return false;
		
		$autoFilesMax = $this->getDefaultAutoFilesMax();
		
		$files = array();
		foreach( explode(';',$this->recordModel->get('autosourcedata')) as $path){
			try {
				$pathFiles = glob($path, GLOB_MARK | GLOB_ERR);
				//Tri par nom
				asort($files);
				/*//Tri par date
				usort($files, function($a, $b) {
					return filemtime($a) < filemtime($b);
				});*/
				//var_dump(__FILE__, 'prepareAutoPreImportData $pathFiles', $pathFiles);
				foreach($pathFiles as $fileName){
					// Contrôle qu'on a bien un .csv qui n'est pas déjà été traité
					if(!($fileName[strlen($fileName)-1] === '/' || $fileName[strlen($fileName)-1] === '\\')
					&& !(strcasecmp(pathinfo($fileName, PATHINFO_EXTENSION), $legalExtension) !== 0)
					&& !(file_exists($fileName . ".done"))
					&& !(file_exists($fileName . ".error"))
					){
						$files[] = $fileName;
						
						/* Un seul fichier à la fois */
						if(count($files) >= $autoFilesMax)
							break;
					}
				}
			}
			catch(Exception $ex){
				echo "
<pre>Erreur dans prepareAutoPreImportData
	$ex->getMessage()
	$ex->getTraceAsString()
</pre>
";
				return false;
			}
		}
		if(!$files)
			return false;
		$this->request->set('import_file_src_mode', 'localpath');
		$this->request->set('import_file_localpath', implode(';', $files));
		return true;
	}
		
	/** Méthode appelée après un pré-import automatique
	 *
	 */
	function postAutoPreImportData(){
		parent::postAutoPreImportData();
	
		$this->request->set('delimiter', $this->getDefaultFileDelimiter());
		
		if(!$this->recordModel->get('autoenabled')
		|| !$this->recordModel->get('autosourcedata'))
			return false;
		
		$files = explode(';', $this->request->get('import_file_localpath'));
		foreach( $files as $fileName){
			file_put_contents($fileName . '.done', date('Y-m-d H:i:s'));
		}
		return true;
	}
}
