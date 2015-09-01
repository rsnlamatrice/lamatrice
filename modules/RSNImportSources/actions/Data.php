<?php

class RSNImportSources_Data_Action extends Import_Data_Action {
	public function __construct($importInfo, $user) {
		parent::__construct($importInfo, $user);
		$this->importSource = $importInfo['importsourceclass'];
	}

	/**
	 * Method to ended an import.
	 *  It unlock the module for new import and remove the concerned import of the table.
	 */
	public function finishImport($remove = true) {
		RSNImportSources_Lock_Action::unLock($this->user, $this->module);
		if($remove){
			RSNImportSources_Queue_Action::remove($this->id);
		}
	}

	/**
	 * Method to update the status of an import on cancel.
	 */
	public function updateImportStatusForCancel() {
		$adb = PearDatabase::getInstance();
		$adb->pquery('UPDATE ' . Import_Utils_Helper::getDbTableName($this->user, $this->module) . ' SET status=' . self::$IMPORT_RECORD_FAILED . ' WHERE status=' . self::$IMPORT_RECORD_NONE,
			array());
	}

	/**
	 * Method called by the cron when there is scheduled import.
	 *  It process to the import of all scheduled pre import.
	 */
	public static function runScheduledPreImport() {
	//TODO email or log when schedule import is running and ended !!
		global $current_user;
		
		$importControllers = self::getScheduledPreImport();
		
		foreach ($importControllers as $importId => $importDataController) {
			
			//TODO ? Tester si des données existent déjà en pré-import
			
			$importDataController->preImportData();
			
			$importDataController->recordModel->set('mode', 'edit');
			$importDataController->recordModel->set('autolasttime', date('Y-m-d H:i:s'));
			$importDataController->recordModel->set('autolastresult', 'Ok');
			$importDataController->recordModel->save();
		}
	}
	
	/**
	 * Method called by the cron when there is scheduled import.
	 *  It process to the import of all scheduled import.
	 */
	public static function runScheduledImport() {
	//TODO email or log when schedule import is running and ended !!
		global $current_user;
		
		$scheduledImports = self::getScheduledImport();
		$vtigerMailer = new Vtiger_Mailer();
		$vtigerMailer->IsHTML(true);
		foreach ($scheduledImports as $scheduledId => $importDataController) {
			$current_user = $importDataController->user;
			$importDataController->batchImport = false;

			if(!$importDataController->initializeImport()) {
				continue;
			}

			$module = $importDataController->get('module');
			$className = $importDataController->get('importSource');//tmp getImport source
			$importClass = RSNImportSources_Utils_Helper::getClassFromName($className);
			$importController = new $importClass($request, $current_user);
			$importController->scheduledId = $scheduledId;
			$importController->doImport($importDataController, $module);//tmp get module name !!
			
			/*if(!$importController->keepScheduledImport){
				$importStatusCount = $importDataController->getImportStatusCount();
	
				// tmp mail
				$emailSubject = 'vtiger CRM - Scheduled Import Report for '.$importDataController->module;
				vimport('~~/modules/Import/ui/Viewer.php');
				$viewer = new Import_UI_Viewer();
				$viewer->assign('FOR_MODULE', $importDataController->module);
				$viewer->assign('INVENTORY_MODULES', getInventoryModules());
				$viewer->assign('IMPORT_RESULT', $importStatusCount);
				$importResult = $viewer->fetch('Import_Result_Details.tpl');
				$importResult = str_replace('align="center"', '', $importResult);
				$emailData = 'vtiger CRM has just completed your import process. <br/><br/>' .
								$importResult . '<br/><br/>'.
								'We recommend you to login to the CRM and check few records to confirm that the import has been successful.';
	
				$userName = getFullNameFromArray('Users', $importDataController->user->column_fields);
				$userEmail = $importDataController->user->email1;
				$vtigerMailer->to = array( array($userEmail, $userName));
				$vtigerMailer->Subject = $emailSubject;
				$vtigerMailer->Body    = $emailData;
				$vtigerMailer->Send();
			}*/
			
			$importDataController->finishImport(!$importController->keepScheduledImport);//tmp how to check the import result without mail ???
		}
		//tmp mail
		//Vtiger_Mailer::dispatchQueue(null);
	}

	/**
	 * Methode to get the detail of an import by user and module.
	 * @param $user : the concerned user
	 * @param string $moduleName : the concerned module name.
	 * @return array - the detail of the import.
	 */
	public static function getImportDetails($user, $moduleName, $statusType = FALSE){
        $adb = PearDatabase::getInstance();
        $tableName = Import_Utils_Helper::getDbTableName($user, $moduleName);
		$params = array();
		if(!$statusType)
			$statusType = array(self::$IMPORT_RECORD_SKIPPED,self::$IMPORT_RECORD_FAILED);
		else {
			if(!is_array($statusType))
				$statusType = array($statusType);
			
			for ($i=0; $i<count($statusType); ++$i) {
				if(!is_numeric($statusType[$i]))
					$statusType[$i] = Import_Data_Action::getImportRecordStatus($statusType[$i]);
			}
		}
		$params = array_merge($params, $statusType);
		$result = $adb->pquery("SELECT * FROM $tableName where status IN (" . generateQuestionMarks($statusType) . ")", $params);
        $importRecords = array();

        if($result) {
            $headers = $adb->getColumnNames($tableName);
			$numOfHeaders = count($headers);

            for ($i=3; $i<$numOfHeaders; ++$i) {
                $importRecords['headers'][] = $headers[$i];
            }

			$noOfRows = $adb->num_rows($result);

			for ($i=0; $i<$noOfRows; ++$i) {
                $row = $adb->fetchByAssoc($result,$i);
                $record= new Vtiger_Base_Model();

                foreach ($importRecords['headers'] as $header) {
                    $record->set($header,$row[$header]);
                }

                $importRecords[Import_Data_Action::getImportRecordStatusLabel($row['status'])][] = $record;
            }

        	return $importRecords;
        }
    }
	
    /**
	 * Methode to get all the scheduled import in the queue table.
	 * @return array - the scheduled import.
	 */
	public static function getScheduledImport() {

		$scheduledImports = array();
		$importQueue = RSNImportSources_Queue_Action::getAll(RSNImportSources_Queue_Action::$IMPORT_STATUS_SCHEDULED);
		foreach($importQueue as $importId => $importInfo) {
			$userId = $importInfo['user_id'];
			$user = new Users();
			$user->id = $userId;
			$user->retrieve_entity_info($userId, 'Users');

			$scheduledImports[$importId] = new RSNImportSources_Data_Action($importInfo, $user);
		}
		return $scheduledImports;
	}
	
    /**
	 * Methode to get all the scheduled pre import for RSNImportSources WHERE autoenabled = true AND disabled = false.
	 * @return array - the scheduled pre-import.
	 */
	public static function getScheduledPreImport() {

		$moduleModel = Vtiger_Module_Model::getInstance('RSNImportSources');
		$recordModels = $moduleModel->getPreImportRecords(true);
		$importControllers = array();
		foreach($recordModels as $crmId => $recordModel) {
			$importControllers[$crmId] = $recordModel->getImportController();
		}
		return $importControllers;
	}

	/**
	 * Generic getter.
	 * @param string $propertyName.
	 * @return the value of the property specified in parameter.
	 */
	public function get($propertyName) {
		if(property_exists($this,$propertyName)) {
			return $this->$propertyName;
		}
		return null;
	}
}

?>
