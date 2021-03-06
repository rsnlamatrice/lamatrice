<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class RSNQueriesVariables_SaveAjax_Action extends Vtiger_SaveAjax_Action {
	
	public function __construct() {
		parent::__construct();
		$this->exposeMethod('updateVariableValue');
	}
	public function process(Vtiger_Request $request) {
		switch($request->get('mode')){
		case 'updateVariableValue':
			$result = $this->updateVariableValue($request);
			break;
		default:
			
			//if(is_array($request->get('defaultvalue')))
			
			return parent::process($request);
		}

		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}
	/**
	 * Fonction de sauvegarde de la valeur d'une variable
	 * @param Vtiger_Request $request
	 */
	public function updateVariableValue(Vtiger_Request $request) {
		$recordModel = parent::getRecordModelFromRequest($request);

		if($request->get('operator'))
			$recordModel->set('rsnvariableoperator', $request->get('operator'));
		else {
			$value = $request->get('value');
			if(is_array($value)){
				if(is_array($value[0]))
					$value = json_encode($value);
				else
					$value = implode(' |##| ', $value);
			}
			$recordModel->set('defaultvalue', $value);
		
		}
		
		return $recordModel->save();
	}
}
