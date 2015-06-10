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
	public function finishImport() {
		RSNImportSources_Lock_Action::unLock($this->user, $this->module);
		RSNImportSources_Queue_Action::remove($this->id);
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
			$importController->doImport($importDataController, $module);//tmp get module name !!
			$importStatusCount = $importDataController->getImportStatusCount();

			// tmp mail
			/*$emailSubject = 'vtiger CRM - Scheduled Import Report for '.$importDataController->module;
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
			$vtigerMailer->Send();*/

			$importDataController->finishImport();//tmp how to check the import result without mail ???
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
	public static function getImportDetails($user, $moduleName){
        $adb = PearDatabase::getInstance();
        $tableName = Import_Utils_Helper::getDbTableName($user, $moduleName);
		$result = $adb->pquery("SELECT * FROM $tableName where status IN (?,?)",array(self::$IMPORT_RECORD_SKIPPED,self::$IMPORT_RECORD_FAILED));
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

                if ($row['status'] == self::$IMPORT_RECORD_SKIPPED) {
                    $importRecords['skipped'][] = $record;
                } else {
                    $importRecords['failed'][] = $record;
                }
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
