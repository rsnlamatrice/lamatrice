<?php
/* AV15
 *
 */

/* ED150830
 * Ajout de la possibilité d'importer plusieurs fichiers qui se suivraient.
 * 	- ajout du paramètre $nFile et de la propriété $$current_nFile, affectée dans open($nFile = 0)
 * 	-> Import_Utils_Helper::getImportFilePath($user, $moduleName, $nFile = 0)
 * 	-> $this->getFilePath($nFile = 0)
 */

class RSNImportSources_FileReader_Reader extends RSNImportSources_Reader_Reader {

	var $fileHandler = null;
	var $fileEncoding;
	var $current_nFile = 0; //On peut traiter plusieurs fichiers consécutivement

	public function  __construct($request, $user) {
		parent::__construct($request, $user);
		$this->fileEncoding = $request->get('file_encoding');
	}

	/**
	 * Method to open the file.
	 * @return boolean - true if sucess.
	 */
	public function open($nFile = 0) {
		ini_set("auto_detect_line_endings", true);
		
		$this->current_nFile = $nFile;
		
		// check if file is already open.
		if ($this->fileHandler == null) {
			$this->fileHandler = $this->getFileHandler();
			return ($this->fileHandler != null);
		}

		return false;
	}

	/**
	 * Method to close the file.
	 */
	public function close() {
		if($this->fileHandler)
			@fclose($this->fileHandler);
		unset($this->fileHandler);
	}

	/**
	 * Method to get the file path.
	 * @param $nFile : file index in collection. 0 by default.
	 * @param $ifExists : if true, returns empty string if file does not exist
	 * @return string - the file path.
	 */
	public function getFilePath($nFile = 0, $ifExists = false) {
		$filePath = Import_Utils_Helper::getImportFilePath($this->user, $this->request->get('for_module'), $nFile);
		if(!$ifExists || file_exists($filePath))
			return $filePath;
	}

	/**
	 * Method to get the file handler.
	 * @return the file handler | null if error.
	 */
	public function getFileHandler() {
		$filePath = $this->getFilePath($this->current_nFile, true);
		if(!$filePath) {
			$this->status = 'failed';
			$this->errorMessage = "ERR_FILE_DOESNT_EXIST";
			return null;
		}

		$fileHandler = fopen($filePath, 'r');
		if(!$fileHandler) {
			$this->status = 'failed';
			$this->errorMessage = "ERR_CANT_OPEN_FILE";
			return null;
		}
		return $fileHandler;
	}

	/**
	 * Method to delete the file.
	 */
	public function deleteFile() {
		if($filePath = $this->getFilePath($this->current_nFile, true))
			@unlink($filePath);
	}

	/**
	 * Method to read the next line of the file (ED150830 may be multiple). It convert the read string encoding to the default-chartset.
	 * @return string - the read line.
	 */
	public function readnextLine() {
	// TODO: check if file is opened.
	// TODO: take care of the End-Of-File ?
		global $default_charset;
		
		if(!$this->fileHandler)
			throw new Exception('Le fichier '. $this->getFilePath($this->current_nFile) . ' n\'est pas ouvert.');
		
		$line = fgets($this->fileHandler);
		if($line === FALSE){
			//fichier suivant
			$filePath = $this->getFilePath($this->current_nFile + 1, true);
			if($filePath){
				//ferme
				$this->close();
				//ouvre le suivant
				$this->open($this->current_nFile + 1);
				return $this->readnextLine();//1ere ligne du fichier suivant
			}
		}
		elseif($this->fileEncoding != $default_charset) {
			$line = $this->convertCharacterEncoding($line, $this->fileEncoding, $default_charset);
		}

		return $line;
	}

	/**
	 * Method to get the current cursor position in the file.
	 * @return int - the cursor
	 */
	public function getCurentCursorPosition() {		
		return ftell($this->fileHandler);
	}

	/**
	 * Method to move the cursor in the file.
	 * @param int $offest - the new cursor position.
	 */
	public function moveCursorTo($offset) {
		fseek($this->fileHandler, $offset);
	}
}
?>