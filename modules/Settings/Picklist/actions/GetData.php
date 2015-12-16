<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Picklist_GetData_Action extends Vtiger_GetData_Action {

    function __construct() {
        $this->exposeMethod('getSettingFields');
    }
	
	public function process(Vtiger_Request $request) {
        $mode = $request->get('mode');
		if($mode)
			return $this->invokeExposedMethod($mode, $request);
		return parent::process($request);
	}
	
	//Retourne les valeurs des champs complémentaires pour les valeurs spécifiées d'un picklist
	public function getSettingFields(Vtiger_Request $request) {
		$record = $request->get('record');
		$sourceModule = $request->get('source_module');
        $pickFieldId = $request->get('pickListFieldId');
        $pickListValues = $request->get('picklistValues');
        $fieldModel = Settings_Picklist_Field_Model::getInstance($pickFieldId);
		
		$response = new Vtiger_Response();
		$data = array();
		foreach($pickListValues as $pickListValue){
			$data[$pickListValue] = $fieldModel->getSettingFieldValue($pickListValue);
		}
		$result = array('success'=>true, 'data'=>$data);
		$response->setResult($result);
	
		$response->emit();
	}
}
