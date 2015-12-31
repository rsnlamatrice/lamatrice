<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vendors_RelatedList_View extends Vtiger_RelatedList_View {
    
	function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$relatedModuleName = $request->get('relatedModule');
		$orderBy = $request->get('orderby');
		$sortOrder = $request->get('sortorder');
		if(empty($orderBy)) {
			switch($relatedModuleName){
			case 'PurchaseOrder':
				$request->set('orderby', 'duedate');
				$request->set('sortorder', 'desc');
				break;
			}
		}
		return parent::process($request);
        }
}
?>