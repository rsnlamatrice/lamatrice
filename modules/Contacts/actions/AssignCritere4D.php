<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Contacts_AssignCritere4D_Action extends Vtiger_Mass_Action {
	
	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		if(!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'Save')) {
			throw new AppException(vtranslate($moduleName).' '.vtranslate('LBL_NOT_ACCESSIBLE'));
		}
	}

	public function process(Vtiger_Request $request) {
		//var_dump($request);	die();
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$asColumnName = 'contactid';
		$sourceIdsQuery = $this->getRecordsQueryFromRequest($request, $asColumnName);
		
		$related_ids = $request->get('related_ids');
		$dateapplication = $request->get('dateapplication');
		$reldata = $request->get('reldata');
		
		$params = array();
		$query = 'INSERT INTO `vtiger_critere4dcontrel` (`critere4did`, `contactid`, `dateapplication`, `data`)
			SELECT critere4did, source_records.' . $asColumnName;
		//dateapplication
		$query .= '
		, CASE critere4did';
		for($i=0; $i < count($related_ids); $i++){
			$query .= ' WHEN ? THEN ?';
			$params[] = $related_ids[$i];
			$params[] = DateTimeField::convertToDBFormat($dateapplication[$i]);
		}
		$query .= ' END';
		
		//data
		$query .= '
		, CASE critere4did';
		for($i=0; $i < count($related_ids); $i++){
			$query .= ' WHEN ? THEN ?';
			$params[] = $related_ids[$i];
			$params[] = $reldata[$i];
		}
		$query .= ' END';
		
		$query .= '
		FROM vtiger_critere4d
		, (' . $sourceIdsQuery . ') source_records
		WHERE critere4did IN (' . generateQuestionMarks($related_ids) . ')';
		
		$query .= '
		ON DUPLICATE KEY UPDATE data = data
		';
		$params = array_merge($params, $related_ids);
		
		//echo '<pre>'; print_r($query); echo '</pre>'; 
		//var_dump($params);
		
		global $adb;
		$result = $adb->pquery($query, $params);
		$response = new Vtiger_Response();
		if(!$result){
			$response->setError($adb->echoError('Erreur lors de l\'affectation', true));
		}
		else{
			if (count($related_ids) === 1)
				$result = '1 critère affecté';
			else
				$result = sprintf('%s critères affectés', count($related_ids));
				
			//count source
			$query = 'SELECT COUNT(*) FROM (' . $sourceIdsQuery . ') source_records';
			$sourceCounter = $adb->getOne($query);
			
			if($sourceCounter <= 1)
				$result .= sprintf(' à %s contact', $sourceCounter);
			else
				$result .= sprintf(' à %s contacts', $sourceCounter);
			$response->setResult($result);
		}
		$response->emit();
	}
}
