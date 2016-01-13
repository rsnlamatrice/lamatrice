<?php
/*+**********************************************************************************
 * ED150906
 ************************************************************************************/

class RsnReglements_ListAjax_View extends Vtiger_ListAjax_View {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('getRecordsCount');
	}

	function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}
	
	/**
	 * Function returns the number of records for the current filter
	 * @param Vtiger_Request $request
	 */
	function getRecordsCount(Vtiger_Request $request) {
		
		$moduleName = $request->getModule();
		$cvId = $request->get('viewname');
		$calculatedTotals = array();
		$count = $this->getListViewCount($request, $calculatedTotals);

		$result = array();
		$result['module'] = $moduleName;
		$result['viewname'] = $cvId;
		
		//ED150904 : complete le nombre par le cumul ttc
		if($calculatedTotals['total'])
			$result['count'] = $count . ' : ' . CurrencyField::convertToUserFormat($calculatedTotals['total']) . ' €'; //TODO $USER_MODEL->get('currency_symbol')
		else
			$result['count'] = $count;

		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}
}