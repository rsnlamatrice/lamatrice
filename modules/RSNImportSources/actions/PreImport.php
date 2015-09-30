<?php

class RSNImportSources_PreImport_Action extends Vtiger_SaveAjax_Action {
	
	public function __construct() {
		parent::__construct();
		$this->exposeMethod('validateRows');
	}
	
	public function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
		return parent::process($request);
	}
	
	/*
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
}

?>
