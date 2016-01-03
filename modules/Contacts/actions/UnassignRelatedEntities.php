<?php
/*+***********************************************************************************
 * ED150628
 *************************************************************************************/

class Contacts_UnassignRelatedEntities_Action extends Vtiger_Mass_Action {
	
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
		
		$relatedModuleName = $request->get('relatedmodule');
		switch($relatedModuleName){
		 case 'Critere4D':
			$tableName = 'vtiger_critere4dcontrel';
			$relatedColumnName = 'critere4did';
			$parentColumnName = 'contactid';
			$dateColumnName = 'dateapplication';
			break;
		 case 'Documents':
			$tableName = 'vtiger_senotesrel';
			$relatedColumnName = 'notesid';
			$parentColumnName = 'crmid';
			$dateColumnName = 'dateapplication';
			break;
		}
		
		$asColumnName = 'contactid';
		$sourceIdsQuery = $this->getRecordsQueryFromRequest($request, $asColumnName);
		
		$related_ids = $request->get('related_ids');
		
		$dateApplications = array();
		
		//Regroupe les id par date d'application
		foreach($related_ids as $index => $related_id){
			if($request->get('use_dateapplication_'.$related_id)){
				$date = $request->get('dateapplication')[$index];
				if($date)
					$date = DateTimeField::convertToDBFormat($date);
			}
			else
				$date = '';
			if(!$dateApplications[$date])
				$dateApplications[$date] = array();
			$dateApplications[$date][] = $related_id;
		}
		$sourceCounter = 0;
		foreach($dateApplications as $date => $related_ids){
			
			$queryFromWhere = 'FROM `'.$tableName.'`
				WHERE '.$relatedColumnName.' IN (' . generateQuestionMarks($related_ids) . ')
				AND '.$parentColumnName.' IN ( SELECT ' . $asColumnName . ' FROM (' . $sourceIdsQuery . ') source_records )';
			$params = $related_ids;
			if($date){
				$queryFromWhere .= ' AND '.$dateColumnName.' = ?';
				$params[] = $date;
			}
			
			//Count
			$query = 'SELECT COUNT(*) ' . $queryFromWhere;
			$result = $adb->pquery($query, $params);
			$sourceCounter += $adb->query_result($result, 0, 0);
			
			//Delete
			$query = 'DELETE ' . $queryFromWhere;
			$result = $adb->pquery($query, $params);
			if(!$result)
				break;
		}
		
		$response = new Vtiger_Response();
		if(!$result){
			$response->setError($adb->echoError('Erreur lors de la suppression des relations', true));
		}
		elseif($sourceCounter === 0)
			$response->setError('Aucune suppression !');
		else {
			$result = sprintf('Suppression de %s relation', $sourceCounter);
			if($sourceCounter > 1)
				$result .= 's';
			$response->setResult($result);
		}
		$response->emit();
	}
}
