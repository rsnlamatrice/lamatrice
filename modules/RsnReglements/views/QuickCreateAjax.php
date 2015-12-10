<?php
/*+***********************************************************************************
 * ED151002
 *************************************************************************************/

class RsnReglements_QuickCreateAjax_View extends Vtiger_QuickCreateAjax_View {

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();

		//ED150419 (ContactAddresses_QuickCreateAjax_View initializes $this->record )
		if(isset($this->record))
			$recordModel = $this->record;
		else
			$this->record = $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			
		//ED151002
		if($request->get('sourceRecord')) {
			$parentRecordModel = Vtiger_Record_Model::getInstanceById($request->get('sourceRecord'), $request->get('sourceModule'));
			$recordModel->setParentRecordData($parentRecordModel);
		}
		
		//ED151210
		$this->record->set('dateregl', date('d-m-Y'));
		$this->record->set('dateoperation', date('d-m-Y'));
		
		parent::process($request);
	}
}