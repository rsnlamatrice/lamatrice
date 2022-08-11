<?php
/*+**********************************************************************************
 * ED150906
 ************************************************************************************/

class Invoice_ListAjax_View extends Vtiger_ListAjax_View {
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
		if($calculatedTotals['total']){
			$result['count'] = $count . ' : '; //TODO $USER_MODEL->get('currency_symbol')
			$result['count'] .= 'Total TTC : '.CurrencyField::convertToUserFormat($calculatedTotals['total']) . ' €; ';
			$result['count'] .= 'Total Produit : '.CurrencyField::convertToUserFormat($calculatedTotals['total_product']) . ' €; ';
			$result['count'] .= 'Total Don : '.CurrencyField::convertToUserFormat($calculatedTotals['total_don']) . ' €; ';
			$result['count'] .= 'Total Service : '.CurrencyField::convertToUserFormat($calculatedTotals['total_service']) . ' €; ';
		}
			
		else
			$result['count'] = $count;

		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}
}