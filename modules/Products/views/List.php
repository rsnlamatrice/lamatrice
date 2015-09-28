<?php
/*+***********************************************************************************
 * ED150928
 *************************************************************************************/

class Products_List_View extends Vtiger_List_View {
    
	function preProcess(Vtiger_Request $request, $display=true) {
            if($request->get('mode') === 'refreshQtyInDemand'){
                $request->set('mode', null);
                $salesOrder = CRMEntity::getInstance('SalesOrder');
                $salesOrder->refreshQtyInDemand();
            }
	    parent::preProcess($request, $display);
        }
}