<?php
/*+***********************************************************************************
 * 
 *************************************************************************************/

class RsnPrelevements_QuickCreateAjax_View extends Vtiger_QuickCreateAjax_View {

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