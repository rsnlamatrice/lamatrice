<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Invoice_TooltipAjax_View extends Vtiger_TooltipAjax_View {

	function process (Vtiger_Request $request) {
		switch($request->get('mode')){
		 case 'ProductList':
			
			$viewer = $this->getViewer ($request);
			$moduleName = $request->getModule();
	
			$this->initializeProductListViewContents($request, $viewer);
	
			echo $viewer->view('ProductList.tpl', $moduleName, true);
			
			break;
		 default:
		 	return parent::process($request);
		}
	}
	
	public function initializeProductListViewContents(Vtiger_Request $request, Vtiger_Viewer $viewer) {
		$moduleName = $this->getModule($request);
		
		$recordId = $request->get('record');
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);

		$relatedProducts = getAssociatedProducts($moduleName, $recordModel->getEntity());
		
		$viewer->assign('MODULE', $moduleName);

		$viewer->assign('PARENT_RECORD', $recordModel);

		$viewer->assign('LISTVIEW_ENTRIES', $relatedProducts);
	}

}