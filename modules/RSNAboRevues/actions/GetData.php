<?php
/*+***********************************************************************************
 * ED151201
 *************************************************************************************/

class RSNAboRevues_GetData_Action extends Vtiger_GetData_Action {

	public function __construct() {
		parent::__construct();
		$this->exposeMethod('getTypeIsAbonnable');
	}
	
	public function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
		parent::process($request);
	}
	
	//Retourne la valeur du champ isabonnable du type d'abonnement fourni
	public function getTypeIsAbonnable(Vtiger_Request $request){
		global $adb;
		$rsnabotype = $request->get('rsnabotype');
		$query = "SELECT isabonnable
			FROM vtiger_rsnabotype
			WHERE rsnabotype = ?";
		$result = $adb->pquery($query, array($rsnabotype));
		$data = $adb->query_result($result, 0, 0);
		$response = new Vtiger_Response();
		$response->setResult($data);
		$response->emit();
	}
}
