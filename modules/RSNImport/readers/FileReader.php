<?php

class RSNImport_FileReader_Reader extends RSNImport_Reader_Reader {

	var $fileHandler = null;
	var $fileEncoding;

	public function  __construct($request, $user) {
		parent::__construct($request, $user);
		$this->fileEncoding = $request->get('file_encoding');
	}

	/**
	 * Method to open the file.
	 * @return boolean - true if sucess.
	 */
	public function open() {
	// TODO: check if file is allready open.
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
		unset($this->fileHandler);
	}

	/**
	 * Method to get the file path.
	 * @return string - the fils path.
	 */
	public function getFilePath() {
		return Import_Utils_Helper::getImportFilePath($this->user, $this->request->get('for_module'));
	}

	/**
	 * Method to get the file handler.
	 * @return the file handler | null if error.
	 */
	public function getFileHandler() {
		$filePath = $this->getFilePath();
		if(!file_exists($filePath)) {
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
		$filePath = $this->getFilePath();
		@unlink($filePath);
	}

	/**
	 * Method to read the next line of the file. It convert the read string encoding to the default-chartset.
	 * @return string - the read line.
	 */
	public function readnextLine() {
	// TODO: check if file is opened.
	// TODO: take care of the End-Of-File ?
		global $default_charset;
		$line = fgets($this->fileHandler);
		if($line != false && $this->fileEncoding != $default_charset) {
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