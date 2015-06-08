<?php

class RSNImport_Reader_Reader {//tmp add datasource ??

	//tmp witch attribute are usefull ??
	var $status='success';
	var $numberOfRecordsRead = 0;
	var $errorMessage='';
	var $user;
	var $request;
    var $moduleModel;

	public function  __construct($request, $user) {//tmp 
		$this->request = $request;
		$this->user = $user;
        //$this->moduleModel = Vtiger_Module_Model::getInstance($this->request->get('for_module'));
	}

	public function getStatus() {
		return $this->status;
	}

	public function getErrorMessage() {
		return $this->errorMessage;
	}

	public function getNumberOfRecordsRead() {
		return $this->numberOfRecordsRead;
	}

	public function convertCharacterEncoding($value, $fromCharset, $toCharset) {
		if (function_exists("mb_convert_encoding")) {
			$value = mb_convert_encoding($value, $toCharset, $fromCharset);
		} else {
			$value = iconv($fromCharset, $toCharset, $value);
		}
		return $value;
	}

	public function readNextRecord() {
		// Sub-class need to implement this
	}
}
?>