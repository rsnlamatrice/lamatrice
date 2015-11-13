<?php
/*+***********************************************************************************
 * ED151113
 *************************************************************************************/

class Vtiger_FieldValidatorAjax_Action extends Vtiger_BasicAjax_Action {

	public function __construct() {
		parent::__construct();
		$this->exposeMethod('getEmailDomainValidation');
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	/**
	 * Function to check if an email domain is known as an error
	 * @param <Vtiger_Request> $request
	 * @return <Object> Error code and a valid domain in replacement
	 */
	public function getEmailDomainValidation(Vtiger_Request $request) {
		
		$domain = $request->get('domain');

		global $adb;
		$query = 'SELECT error, validdomain
			FROM vtiger_emaildomains
			WHERE domain = ?
			AND error != 0
			LIMIT 1
		';
		$result = $adb->pquery($query, array( $domain ));
		if(!$result){
			$adb->echoError();
			die();
		}
		if($row = $adb->fetch_row($result)){
			$result = $row;
		}
		else
			$result = array();
			
		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}
}