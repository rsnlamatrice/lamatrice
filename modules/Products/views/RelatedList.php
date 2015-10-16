<?php
/*+***********************************************************************************
 * 
 *************************************************************************************/

class Products_RelatedList_View extends Vtiger_RelatedList_View {
	function process(Vtiger_Request $request) {
		$relatedModuleName = $request->get('relatedModule');
		$orderBy = $request->get('orderby');
		$sortOrder = $request->get('sortorder');
		if(empty($orderBy)) {
			switch($relatedModuleName){
			case 'SalesOrder':
				$request->set('orderby', 'sostatus'); //TODO Trier par IF(sostatus = 'Approved' OR sostatus = 'Created', 0, 1)
				break;
			case 'Invoice':
				$request->set('orderby', 'invoicestatus'); 
				break;
			}
		}
		return parent::process($request);
	}
}
