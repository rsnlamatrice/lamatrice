<?php

/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Class RsnPrelevements_Edit_View extends Vtiger_Edit_View {
	public function process(Vtiger_Request $request) {
		
		if($request->get('sourceModule') == 'Contacts'
		   && !$request->get('accountid')){
			/* ED141016 generation du compte du contact si manquant */
			$sourceRecordModel = Vtiger_Record_Model::getInstanceById($request->get('sourceRecord'), $request->get('sourceModule'));
			$accountRecordModel = $sourceRecordModel->getAccountRecordModel();
			$request->setGlobal('accountid', $accountRecordModel->getId());     
		}
		
		parent::process($request);

	}
}