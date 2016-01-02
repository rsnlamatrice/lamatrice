<?php
/**************************************************************************************
 * ED150910
 * Fusion des comptes des contacts
 * 
 **************************************************************************************/

class Contacts_MergeAccounts_View extends Vtiger_MergeRecord_View {
	
	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			"modules.$moduleName.resources.MergeAccounts",
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
	
	function process(Vtiger_Request $request) {
		
		//Regroupement familiale
		$records = $request->get('records');
		if(is_string($records))
			$records = explode(',', $records);
		$module = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($module);
		
		$allFieldModels =  $moduleModel->getFields();
		$fieldModels =  array(
			'reference' => $allFieldModels['reference'],
			'account_id' => $allFieldModels['account_id'],
		);

		foreach($records as $record) {
			$recordModels[] = Vtiger_Record_Model::getInstanceById($record);
		}
		
		//En premier, un référent
		for($nRecord = 0; $nRecord < count($recordModels); $nRecord++) {
			$record = $recordModels[$nRecord];
			if($record->get('account_id')
			&& $record->get('reference')){
				if($nRecord > 0){
					unset($recordModels[$nRecord]);
					$recordModels = array_merge(array($record), $recordModels);
				}
				break;
			}
			$record++;
		}
		//Compte les comptes différents
		$accounts = array();
		$nbEmpty = 0;
		$nbSame = 0;
		foreach($recordModels as $record) {
			if($record->get('account_id')){
				if($accounts[$record->get('account_id')]){
					$accounts[$record->get('account_id')]++;
					$nbSame++;
				}
				else
					$accounts[$record->get('account_id')] = 1;
			}
			else
				$nbEmpty++;
		}
		
		$viewer = $this->getViewer($request);
		
		if(count($accounts) === 1
		&& $nbSame == count($recordModels) - 1){
			$viewer->assign('ALREADY_COMPTE_COMMUN', true);
		}
		elseif(count($accounts) === 0){
			$viewer->assign('NO_ACCOUNT', true);
		}
		elseif(!$recordModels[0]->get('account_id')){
			$viewer->assign('NO_REFERENT', true);
		}
		
		
		$viewer->assign('RECORDS', $records);
		$viewer->assign('RECORDMODELS', $recordModels);
		$viewer->assign('FIELDS', $fieldModels);
		$viewer->assign('MODULE', $module);
		$viewer->view('MergeAccounts.tpl', $module);
	}
}
