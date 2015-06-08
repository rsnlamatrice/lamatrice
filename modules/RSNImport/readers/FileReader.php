<?php

class RSNImport_FileReader_Reader extends RSNImport_Reader_Reader {

	var $fileHandler = null;

	public function open() {//tmp allready open ??
		if ($this->fileHandler == null) {
			$this->fileHandler = $this->getFileHandler();
			return ($this->fileHandler != null);
		}

		return false;
	}

	public function close() {
		unset($this->fileHandler);
	}

	public function getFilePath() {
		return Import_Utils_Helper::getImportFilePath($this->user, $this->request->get('for_module'));
	}

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

	public function deleteFile() {
		$filePath = $this->getFilePath();
		@unlink($filePath);
	}

	public function readnextLine() {//tmp check if file is opened !! // tmp take care of the endoffile 
		global $default_charset;
		$line = fgets($this->fileHandler);
		if($line != false && $fileEncoding != $default_charset) {
			$line = $this->convertCharacterEncoding($line, $fileEncoding, $default_charset);
		}

		return $line;
	}

	public function getCurentCursorPosition() {		
		return ftell($this->fileHandler);
	}

	public function moveCursorTo($offset) {
		fseek($this->fileHandler, $offset);
	}
}
?>