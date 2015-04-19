<?php
/*+***********************************************************************************
 * ED150419
 *************************************************************************************/

class ContactAddresses_QuickCreateAjax_View extends Vtiger_QuickCreateAjax_View {

	public function process(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);

		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		
		//ED150419
		if($request->get('sourceModule') == 'Contacts'){
			$recordId = $request->get('sourceRecord');
			
			$sourceModule = Vtiger_Module_Model::getInstance($request->get('sourceModule'));
			$sourceRecord = Vtiger_Record_Model::getInstanceById($recordId, $sourceModule);
			//initialise avec les valeurs existantes
			$recordModel = $sourceRecord->createContactAddressesRecord('mailing', false);
			$this->record = $recordModel;
		}
		parent::process($request);
	}
}