<?php
/*+***********************************************************************************
 * ED150628
 *************************************************************************************/

class Contacts_AssignRelatedEntities_Action extends Vtiger_Mass_Action {
	
	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();

		if(!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'Save')) {
			throw new AppException(vtranslate($moduleName).' '.vtranslate('LBL_NOT_ACCESSIBLE'));
		}
	}

	public function process(Vtiger_Request $request) {
		$related_ids = $request->get('related_ids');
		$asColumnName = 'contactid';
		$sourceIdsQuery = $this->getRecordsQueryFromRequest($request, $asColumnName);
		
		switch($request->get('relatedmodule')){
		 case 'Critere4D':
			$result = $this->assignRelatedCritere4D($request, $related_ids, $sourceIdsQuery, $asColumnName);
			break;
		 case 'Documents':
			$result = $this->assignRelatedDocuments($request, $related_ids, $sourceIdsQuery, $asColumnName);
			break;
		}
		
		global $adb;
		$response = new Vtiger_Response();
		if(!$result){
			$response->setError($adb->echoError('Erreur lors de l\'affectation', true));
		}
		else{
			if (count($related_ids) === 1)
				$result = '1 élément affecté';
			else
				$result = sprintf('%s éléments affectés', count($related_ids));
				
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
		
	/** ED150713
	* Affectation de critère à une liste de contacts
	*/	
	private function assignRelatedCritere4D(Vtiger_Request $request, $related_ids, $sourceIdsQuery, $asColumnName) {
		//var_dump($request);	die();
		$moduleName = $request->getModule();
		$relatedModuleName = $request->get('relatedmodule');
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
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
		$params = array_merge($params, $related_ids);
		
		//data
		$query .= '
		ON DUPLICATE KEY UPDATE data = CASE vtiger_critere4dcontrel.critere4did';
		for($i=0; $i < count($related_ids); $i++){
			$query .= ' WHEN ? THEN ?';
			$params[] = $related_ids[$i];
			$params[] = $reldata[$i];
		}
		$query .= ' END';
		
		//echo '<pre>'; print_r($query); echo '</pre>'; 
		//var_dump($params);
		
		global $adb;
		return $adb->pquery($query, $params);
	}
		
	
	/** ED150713
	* Affectation de document à une liste de contacts
	*/	
	private function assignRelatedDocuments(Vtiger_Request $request, $related_ids, $sourceIdsQuery, $asColumnName) {
		//var_dump($request);	die();
		$moduleName = $request->getModule();
		$relatedModuleName = $request->get('relatedmodule');
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$sourceIdsQuery = $this->getRecordsQueryFromRequest($request, $asColumnName);
		
		$related_ids = $request->get('related_ids');
		$dateapplication = $request->get('dateapplication');
		$reldata = $request->get('reldata');		
		
		$params = array();
		$query = 'INSERT INTO `vtiger_senotesrel` (`notesid`, `crmid`, `dateapplication`, `data`)
			SELECT notesid, source_records.' . $asColumnName;
		//dateapplication
		$query .= '
		, CASE notesid';
		for($i=0; $i < count($related_ids); $i++){
			$query .= ' WHEN ? THEN ?';
			$params[] = $related_ids[$i];
			if(!$dateapplication)
				$params[] = date('Y-m-d');
			else
				$params[] = DateTimeField::convertToDBFormat($dateapplication[$i]);
		}
		$query .= ' END';
		
		//data
		$query .= '
		, CASE notesid';
		for($i=0; $i < count($related_ids); $i++){
			$query .= ' WHEN ? THEN ?';
			$params[] = $related_ids[$i];
			if(!$reldata)
				$params[] = null;
			else
				$params[] = $reldata[$i];
		}
		$query .= ' END';
		
		$query .= '
		FROM vtiger_notes
		, (' . $sourceIdsQuery . ') source_records
		WHERE notesid IN (' . generateQuestionMarks($related_ids) . ')';
		$params = array_merge($params, $related_ids);
		
		//data
		$query .= '
		ON DUPLICATE KEY UPDATE data = CASE vtiger_senotesrel.notesid';
		for($i=0; $i < count($related_ids); $i++){
			$query .= ' WHEN ? THEN ?';
			$params[] = $related_ids[$i];
			if(!$reldata)
				$params[] = null;
			else
				$params[] = $reldata[$i];
		}
		$query .= ' END';
		
		//echo '<pre>'; print_r($query); echo '</pre>'; 
		//var_dump($params);
		
		global $adb;
		return $adb->pquery($query, $params);
	}
}
