<?php

class RSNImport_Reader_Reader {
	var $errorMessage='';
	var $user;
	var $request;

	public function  __construct($request, $user) {
		$this->request = $request;
		$this->user = $user;
	}

	/**
	 * Method to get the error message.
	 * @return string - the error message.
	 */
	public function getErrorMessage() {
		return $this->errorMessage;
	}

	/**
	 * Method to convert a string to another charset.
	 * @param string $value : the string to convert.
	 * @param string $fromCharset : the $value string curent charset.
	 * @param string $toEncoding : the new charset.
	 * @return string - the encoded string.
	 */
	public function convertCharacterEncoding($value, $fromCharset, $toCharset) {
		if (function_exists("mb_convert_encoding") && $fromCharset != "macintosh" && $toCharset != "macintosh") {
			$value = mb_convert_encoding($value, $toCharset, $fromCharset);
		} else {
			$value = iconv($fromCharset, $toCharset, $value);
		}
		return $value;
	}
}
?>