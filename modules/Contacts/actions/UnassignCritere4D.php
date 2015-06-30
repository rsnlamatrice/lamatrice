<?php
/*+***********************************************************************************
 * ED150628
 *************************************************************************************/

class Contacts_UnassignCritere4D_Action extends Vtiger_Mass_Action {
	
	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		if(!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'Save')) {
			throw new AppException(vtranslate($moduleName).' '.vtranslate('LBL_NOT_ACCESSIBLE'));
		}
	}

	public function process(Vtiger_Request $request) {
		global $adb;
		//var_dump($request);	die();
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$asColumnName = 'contactid';
		$sourceIdsQuery = $this->getRecordsQueryFromRequest($request, $asColumnName);
		
		$related_ids = $request->get('related_ids');
		
		//Count
		$query = 'SELECT COUNT(*) FROM `vtiger_critere4dcontrel`
		WHERE critere4did IN (' . generateQuestionMarks($related_ids) . ')
		AND contactid IN (' . $sourceIdsQuery . ')';
		$params = $related_ids;
		$sourceCounter = $adb->getOne($query);
		
		//Delete
		$query = 'DELETE FROM `vtiger_critere4dcontrel`
		WHERE critere4did IN (' . generateQuestionMarks($related_ids) . ')
		AND contactid IN ( SELECT ' . $asColumnName . ' FROM (' . $sourceIdsQuery . ') source_records )';
		$params = $related_ids;
		
		/*echo '<pre>'; print_r($query); echo '</pre>'; 
		var_dump($params);*/
		
		$result = $adb->pquery($query, $params);
		$response = new Vtiger_Response();
		if(!$result){
			$response->setError($adb->echoError('Erreur lors de la suppression des relations', true));
		}
		else{
			$result = sprintf('Suppression de %s relation', $sourceCounter);
			if($sourceCounter > 1) $result .= 's';
			$response->setResult($result);
		}
		$response->emit();
	}
}
