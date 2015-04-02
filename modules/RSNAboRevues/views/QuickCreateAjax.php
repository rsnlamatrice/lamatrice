<?php
/*+***********************************************************************************
 * ED150402
 *************************************************************************************/

class RSNAboRevues_QuickCreateAjax_View extends Vtiger_QuickCreateAjax_View {

	public function process(Vtiger_Request $request) {
		$sourceModule = $request->get('sourceModule');
		if($sourceModule == 'Contacts'){
			//set account id when we come from Contact
			$sourceId = $request->get('sourceRecord');
			if($sourceId){
				$sourceRecordModel = Vtiger_Record_Model::getInstanceById($sourceId, $sourceModule);
				if($sourceRecordModel)
					$request->set('account_id', $sourceRecordModel->get('account_id'));
			}
		}
		
		echo parent::process($request);

	}
}