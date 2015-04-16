<?php
/*+***********************************************************************************
 * AV150415
 *************************************************************************************/

class Vtiger_GetFieldData_Action extends Vtiger_Action_Controller {

	function checkPermission(Vtiger_Request $request) {
		return;
	}

	public function process(Vtiger_Request $request) {
		$searchValue 	= $request->get('search_value');
		$searchField 	= $request->get('search_field');
		$relatedModule = $request->get('module');

		$picklistValues = Vtiger_Util_Helper::getPickListValuesAsync($searchField, $searchValue);

		$response = new Vtiger_Response();
		$response->setResult($picklistValues);
		$response->emit();
	}
}
