<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Products_ConvertToModule_View extends Vtiger_Save_Action {

	public function process(Vtiger_Request $request) {
		
                $srcModuleName = $request->get('module');
		$destModuleName = $request->get('destModule');
                
		$srcRecordModel = Vtiger_Record_Model::getInstanceById($request->get('record'), $srcModuleName);
                $destRecordModel = $srcRecordModel->convertAsModule($destModuleName);
                if(!$destRecordModel){
			throw new AppException(vtranslate($srcModuleName).' - '.vtranslate('LBL_ERROR'));
                }
                $loadUrl = $destRecordModel->getDetailViewUrl();
		header("Location: $loadUrl");
	}
}
