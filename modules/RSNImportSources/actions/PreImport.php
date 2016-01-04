<?php

class RSNImportSources_PreImport_Action extends Vtiger_SaveAjax_Action {
	
	public function __construct() {
		parent::__construct();
		$this->exposeMethod('validateRows');
		$this->exposeMethod('addContactRelation');
	}
	
	public function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->get('for_module');
		$record = $request->get('record');
		if($moduleName && !Users_Privileges_Model::isPermitted($moduleName, 'Save', $record)) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}
	
	public function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
		return parent::process($request);
	}
	
	/* Validation des lignes de pré-imports
	 */
	public function validateRows(Vtiger_Request $request) {
		
		$db = PearDatabase::getInstance();
		
		global $current_user;
		
		//$importController = $this->getImportController($request);
			
		$rows = $request->get('rows');
		$moduleName = $request->get('for_module');
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($current_user, $moduleName);
		
		foreach($rows as $rowId => $rowData){
			$params = array();
			$query = 'UPDATE '.$tableName;
			$nParam = 0;
			foreach($rowData['update'] as $fieldName => $fieldValue){
				if($nParam++)
					$query .= ', ';
				else
					$query .= ' SET ';
				$query .= $fieldName . ' = ?';
				$params[] = $fieldValue;
			}
			
			if($nParam > 0){
					
				$query .= ' WHERE id = ?';
				$params[] = $rowId;
				
				$result = $db->pquery($query, $params);
				if(!$result){
					$db->echoError();
					var_dump($query, $params);
					break;
				}	
			}
		}
		//Mise à jour de l'enregistrement final associé
		if($rowData['update'.$moduleName]){
			$recordId = $rowData['update'.$moduleName]['id'];
			
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			
			//Archive l'ancienne adresse
			switch($moduleName){
			case 'Contacts' :
				if(array_key_exists('mailingstreet', $rowData['update'.$moduleName])
				|| array_key_exists('mailingstreet2', $rowData['update'.$moduleName])
				|| array_key_exists('mailingstreet3', $rowData['update'.$moduleName])
				|| array_key_exists('mailingpobox', $rowData['update'.$moduleName])
				|| array_key_exists('mailingzip', $rowData['update'.$moduleName])
				|| array_key_exists('mailingcity', $rowData['update'.$moduleName])
				){
					$recordModel->createContactAddressesRecord('mailing', true);
				}
				
				//email
				$fieldName = 'email';
				if($rowData['update'.$moduleName][$fieldName]
				&& $rowData['update'.$moduleName][$fieldName] != $recordModel->get($fieldName)){
					$recordModel->createContactEmailsRecord(true);
				}
				break;
			default:
				break;
			}//switch($moduleName)
			
			$recordModel->set('mode', 'edit');
			foreach($rowData['update'.$moduleName] as $fieldName => $fieldValue){
				if($fieldName != 'id')
					$recordModel->set($fieldName, $fieldValue);
			}
			$recordModel->save();
		}

		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
	

	/**
	 * Method to get an instance of the import controller. It use the ImportSource parameter to retrive the name of the import class.
	 * @param Vtiger_Request $request: the curent request.
	 * @return Import - An instance of the import controller, or null.
	 */
	function getImportController(Vtiger_Request $request) {
		$className = $request->get('ImportSource');
		
		if (!$className) {
			
			//Id de la queue
			$importId = $request->get('import_id');
			if($importId){
				$recordModel = RSNImportSources_Record_Model::getInstanceByQueueId($importId);
				if($recordModel)
					return $recordModel->getImportController($request);
			}
			
			$forModule = $request->get('for_module');
			$user = Users_Record_Model::getCurrentUserModel();
			//teste si une table d'importation existe pour ce module
			$className = RSNImportSources_Queue_Action::getImportClassName($forModule, $user);
			$request->set('ImportSource', $className);
		}
		
		if ($className) {
			$importClass = RSNImportSources_Utils_Helper::getClassFromName($className);
			$user = Users_Record_Model::getCurrentUserModel();
			$importController = new $importClass($request, $user);

			return $importController;
		}
		return null;
	}

	/* Complète le champ _contactid avec une nouvelle proposition */
	public function addContactRelation(Vtiger_Request $request){
		
		$db = PearDatabase::getInstance();
		
		global $current_user;
		
		//$importController = $this->getImportController($request);
			
		$moduleName = $request->get('for_module');
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($current_user, $moduleName);
		$importRowId = $request->get('importRowId');
		$relatedIdList = $request->get('relatedIdList');
		
		$params = array();
		$query = 'UPDATE '.$tableName.'
				SET _contactid = CONCAT(IFNULL(_contactid, ""), ",", ?)
				, _contactid_status = IF(_contactid LIKE "%_,_%", ?, ?)
				/*, _contactid_source = "Recherche manuelle"*/
				WHERE id = ?';
		$params[] = implode(',', $relatedIdList);
		$params[] = RSNImportSources_Import_View::$RECORDID_STATUS_MULTI;
		$params[] = RSNImportSources_Import_View::$RECORDID_STATUS_SINGLE;
		$params[] = $importRowId;
			
		$result = $db->pquery($query, $params);
		if(!$result){
			//var_dump($query, $params);
			$response = new Vtiger_Response();
			$response->setError($db->echoError('Erreur de mise à jour', true));
			$response->emit();
			break;
		}	
		
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
}

?>