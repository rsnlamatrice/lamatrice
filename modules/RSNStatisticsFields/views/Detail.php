<?php
/*+***********************************************************************************
 * 
 *************************************************************************************/
class RSNStatisticsFields_Detail_View extends Vtiger_Detail_View {
	
	
	/**
	 * ED151017
	 */
	public function process(Vtiger_Request $request) {
		
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
	
		if(!$this->record) {
			$this->record = Vtiger_DetailView_Model::getInstance($moduleName, $recordId);
		}
		$recordModel = $this->record->getRecord();
		$viewer->assign('RECORD_ERRORS', $recordModel->getErrors());

		return parent::process($request);
	}
}
