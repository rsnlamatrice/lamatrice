<?php

define('ASSIGNEDTO_ALL', '7');
define('COUPON_FOLDERID', '9');

define('CURRENCY_ID', 1);
define('CONVERSION_RATE', 1);

class RSNImportSources_Import_View extends Vtiger_View_Controller{

	var $request;
	var $user;

	public function  __construct($request = FALSE, $user = FALSE) {
		parent::__construct();
		$this->request = $request;
		$this->user = $user;
		$this->exposeMethod('showConfiguration');
	}

	/**
	 * Method to check if current user has permission to import in the concerned module.
	 * @param Vtiger_Request $request: the current request.
	 */
	function checkPermission(Vtiger_Request $request) {
		// TODO: use rsnimport permission
		$moduleName = $request->get('for_module');
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'Import')) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}
	
	/**
	 * Method to catch request and redirect to right method.
	 * @param Vtiger_Request $request: the current request.
	 */
	function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
		}

		return;
	}

	/**
	 * Method to show the configuration template of the import for the first step.
	 *  By default it show the nothingToConfigure template.
	 *  If you want same configuration parameter, you need to overload this method in the child class.
	 * @param Vtiger_Request $request: the current request.
	 */
	function showConfiguration(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $request->getModule());
		return $viewer->view('NothingToConfigure.tpl', 'RSNImportSources');
	}

	/**
	 * Method to get the modules that are concerned by the import.
	 *  By default it return only the current module.
	 * @return array - An array containing concerned module names.
	 */
	function getImportModules() {
		return array($this->request->get('for_module'));
	}

	/**
	 * Method to get the main module for this import.
	 * @return string - the name of the main import module.
	 */
	function getMainImportModule() {
		return $this->request->get('for_module');
	}

	/**
	 * Method to get the imported fields of a specific module.
	 *  It call the get<<Module>>Fields method (These methodes must be implemented in the child class).
	 * @param string $module: the module name.
	 * @return array - the imported fields for the specified module.
	 */
	function getFieldsFor($module) {
		$methode = "get" . ucfirst($module) . "Fields";
		if (method_exists($this, $methode)) {
			return $this->$methode();
		}

		return array();
	}

	/**
	 * Method to get the mapping of the fields for a specific module in order to retrieve them from the pre-import table.
	 * @param string $module: the module name.
	 * @return array - the fields mapping.
	 */
	function getMappingFor($module) {
		$fields = $this->getFieldsFor($module);
		$maping = array();
		for ($i = 0; $i < sizeof($fields); ++$i) {
			$maping[$fields[$i]] = $i;
		}

		return $maping;
	}

	/**
	 * Method to pre-import data in the temporary table.
	 *  By default, this method do nothing. The child class must overload this method.
	 * @return bool - false if the preimport failed.
	 */
	function preImportData() {
		return false;
	}

	/**
	 * Method to get the pre Imported data in order to preview them.
	 *  By default, it return the values in the pre-imported table.
	 *  This method can be overload in the child class.
	 * @return array - the pre-imported values group by module.
	 */
	public function getPreviewData() {
		$adb = PearDatabase::getInstance();
		$importModules = $this->getImportModules();
		$previewData = array();

		foreach($importModules as $module) {
			$previewData[$module] = array();
			$fields = $this->getFieldsFor($module);

			$tableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, $module);
			$sql = 'SELECT ';

			for($i = 0; $i < sizeof($fields); ++$i) {
				if ($i != 0) {
					$sql .= ', ';
				}

				$sql .= $fields[$i];
			}

			// TODO: do not hardcode display limit ?
			$sql .= ' FROM ' . $tableName . ' WHERE status = '. RSNImportSources_Data_Action::$IMPORT_RECORD_NONE . ' LIMIT 12';
			$result = $adb->query($sql);
			$numberOfRecords = $adb->num_rows($result);

			for ($i = 0; $i < $numberOfRecords; ++$i) {
				$data = array();
				for($j = 0; $j < sizeof($fields); ++$j) {
					$data[$fields[$j]] = $adb->query_result($result, $i, $fields[$j]);
				}

				array_push($previewData[$module], $data);
			}
		}

		return $previewData;
	}

	/**
	 * Method to get the number of pre-imported records.
	 * @return int - the number of pre-imported records.
	 */
	function getNumberOfRecords() {
		$numberOfRecords = 0;
		$importModules = $this->getImportModules();

		foreach($importModules as $module) {
			$numberOfRecords += RSNImportSources_Utils_Helper::getNumberOfRecords($this->user, $module);
		}

		return $numberOfRecords;
	}

	/**
	 * Method to display the preview of the preimported data.
	 *  this method can be overload in the child class.
	 */
	function displayDataPreview() {
		$viewer = $this->getViewer($this->request);
		$moduleName = $this->request->get('for_module');
		$viewer->assign('FOR_MODULE', $moduleName);
		$viewer->assign('MODULE', 'RSNImportSources');
		$viewer->assign('PREVIEW_DATA', $this->getPreviewData());
		$viewer->assign('IMPORT_SOURCE', $this->request->get('ImportSource'));
		$viewer->assign('ERROR_MESSAGE', $this->request->get('error_message'));

		return $viewer->view('ImportPreview.tpl', 'RSNImportSources');
	}

	/**
	 * Method to check if the import must be scheduled.
	 *  It schedule import if the number of pre-imported record is greater than the imediat import limit (in the config model file).
	 */
	function checkImportIsScheduled() {
		$configReader = new RSNImportSources_Config_Model();
		$immediateImportRecordLimit = $configReader->get('immediateImportLimit');

		$numberOfRecordsToImport = $this->getNumberOfRecords();

		if($numberOfRecordsToImport > $immediateImportRecordLimit) {
			$this->request->set('is_scheduled', true);
		}
	}

	/**
	 * Method to clear and re-create temporary pre-import tables.
	 */
	function clearPreImportTable() {
		$modules = $this->getImportModules();

		foreach ($modules as $module) {
			RSNImportSources_Utils_Helper::clearUserImportInfo($this->user, $module);
			RSNImportSources_Utils_Helper::createTable($this->getFieldsFor($module), $this->user, $module);
		}
	}

	/**
	 * Method to add a import in the import queue table for a specific module.
	 * @param string $module : the module name.
	 */
	public function queueDataImport($module) {
		RSNImportSources_Queue_Action::add($this->request, $this->user, $module, $this->getMappingFor($module));
	}

	/**
	 * Method to begin the import from the temporary pre-import table for a specific module.
	 * @param string $module : the module name.
	 * @param boolean $batchImport
	 */
	public function triggerImport($module, $batchImport=false) {
		$importInfo = RSNImportSources_Queue_Action::getImportInfo($module, $this->user);
		$importDataController = new RSNImportSources_Data_Action($importInfo, $this->user);

		if(!$batchImport) {
			if(!$importDataController->initializeImport()) {
				RSNImportSources_Utils_Helper::showErrorPage(vtranslate('ERR_FAILED_TO_LOCK_MODULE', 'Import'));
				exit;
			}
		}

		$this->doImport($importDataController, $module);
		RSNImportSources_Queue_Action::updateStatus($importInfo['id'], RSNImportSources_Queue_Action::$IMPORT_STATUS_HALTED);
	}

	/**
	 * Method to process to the import of a specific module.
	 *  It call the import<<Module>> method if exist. Else it call the default import method.
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 * @param string $module: the module name
	 */
	public function doImport($importDataController, $module) {
		$methode = "import" . ucfirst($module);
		if (method_exists($this, $methode)) {
			$this->$methode($importDataController);
		} else {
			$importDataController->importData();
		}
	}

	/**
	 * Method to process to the third step (the import step).
	 *  It check if the import must be scheduled. If not, it trigger the import.
	 */
	public function import() {
		$importModules = $this->getImportModules();
		$this->checkImportIsScheduled();
		$isImportScheduled = $this->request->get('is_scheduled');

		foreach($importModules as $module) {
			$this->queueDataImport($module);

			if(!$isImportScheduled) {
				$this->triggerImport($module);
			}
		}

		$importInfos = RSNImportSources_Queue_Action::getUserCurrentImportInfos($this->user);
		RSNImportSources_Import_View::showImportStatus($importInfos, $this->user, $this->request->get("for_module"));
	}

	/**
	 * Method to undo the last import for a specific user.
	 *  this method call the doUndoImport method for each module to manage.
	 * @param int $userId : the id of the user who made the import to undo.
	 */
	public function undoImport($userId) {
		$user = Users_Record_Model::getInstanceById($userId, 'Users');
		$previousBulkSaveMode = $VTIGER_BULK_SAVE_MODE;
		$VTIGER_BULK_SAVE_MODE = false;
		$modules = $this->getImportModules();
		
		$viewer = new Vtiger_Viewer();
		$viewer->view('ImportHeader.tpl', 'RSNImportSources');
		
		for ($i = sizeof($modules)-1; $i >=0; --$i) {
			$noOfRecords = $this->doUndoImport($modules[$i], $user);//tmp noOfrecord !!
		}
		
		$viewer->assign('MODULE', $this->getMainImportModule());
		$viewer->view('okButton.tpl', 'RSNImportSources');
			$viewer->view('ImportFooter.tpl', 'RSNImportSources');
		
		$VTIGER_BULK_SAVE_MODE = $previousBulkSaveMode;
	}

	/**
	 * Method to undo the last import for a specific user and a specific module.
	 *  It call the undo<<Module>>Import method if exist. Else it undo import using the default way.
	 * @param string $module : the module name.
	 * @param int $userId : the id of the user who made the import to undo.
	 */
	function doUndoImport($module, $user) {
		$methode = "undo" . ucfirst($module) . "Import";
		if (method_exists($this, $methode)) {
			$this->$methode($user);
		} else {
			$db = PearDatabase::getInstance();
			$tableName = RSNImportSources_Utils_Helper::getDbTableName($user, $module);
			$query = "SELECT recordid FROM " . $tableName . " WHERE status = " . RSNImportSources_Data_Action::$IMPORT_RECORD_CREATED . " AND recordid IS NOT NULL";

			//For inventory modules
			$inventoryModules = getInventoryModules();
			if(in_array($module, $inventoryModules)){
				$query .=' GROUP BY subject';
			}

			$result = $db->pquery($query, array());

			$noOfRecords = $db->num_rows($result);
			$noOfRecordsDeleted = 0;
			$entityData = array();

			for($i=0; $i<$noOfRecords; $i++) {
				$recordId = $db->query_result($result, $i, 'recordid');
				if(isRecordExists($recordId) && isPermitted($module, 'Delete', $recordId) == 'yes') {
					$recordModel = Vtiger_Record_Model::getCleanInstance($module);
	                $recordModel->setId($recordId);
	                $recordModel->delete();
	                $focus = $recordModel->getEntity();
	                $focus->id = $recordId;
	                $entityData[] = VTEntityData::fromCRMEntity($focus);
					$noOfRecordsDeleted++;
				}
			}

			//TODO: Check for what is used commented line ???
			// $entity = new VTEventsManager($db);        
			// $entity->triggerEvent('vtiger.batchevent.delete',$entityData);
			$viewer = new Vtiger_Viewer();
			$viewer->assign('FOR_MODULE', $module);
			$viewer->assign('MODULE', 'RSNImportSources');
			$viewer->assign('TOTAL_RECORDS', $noOfRecords);
			$viewer->assign('DELETED_RECORDS_COUNT', $noOfRecordsDeleted);
			$viewer->view('ImportUndoResult.tpl', 'RSNImportSources');
		}
	}

	public function getEntryId($moduleName, $recordId) {
		$moduleHandler = vtws_getModuleHandlerFromName($moduleName, $this->user);
		$moduleMeta = $moduleHandler->getMeta();
		$moduleObjectId = $moduleMeta->getEntityId();
		$moduleFields = $moduleMeta->getModuleFields();

		return vtws_getId($moduleObjectId, $recordId);
	}

	/**
	 * Method to display the current import status for a specific user.
	 * @param $importInfos : the informations of the import.
	 * @param $user : the user.
	 * @param string $module : the main import module name.
	 */
	public static function showImportStatus($importInfos, $user, $moduleName = "") {
		if($importInfos == null || sizeof($importInfos) == 0) {
			RSNImportSources_Utils_Helper::showErrorPage(vtranslate('ERR_IMPORT_INTERRUPTED', 'RSNImportSources'));
			exit;
		}

		$viewer = new Vtiger_Viewer();
		$viewer->assign('FOR_MODULE', $moduleName);
		$viewer->assign('MODULE', 'RSNImportSources');
		$viewer->assign('IMPORT_SOURCE', $importInfos[0]['importsourceclass']);
		$viewer->view('ImportHeader.tpl', 'RSNImportSources');
		$importEnded = true;

		foreach($importInfos as $importInfo) {

			$importDataController = new RSNImportSources_Data_Action($importInfo, $user);
			if($importInfo['status'] == RSNImportSources_Queue_Action::$IMPORT_STATUS_HALTED ||
					$importInfo['status'] == RSNImportSources_Queue_Action::$IMPORT_STATUS_NONE) {
				$continueImport = true;
			} else {
				$continueImport = false;
			}
			
			$importStatusCount = $importDataController->getImportStatusCount();
			$totalRecords = $importStatusCount['TOTAL'];

			if($totalRecords > ($importStatusCount['IMPORTED'] + $importStatusCount['FAILED'])) {
				$importEnded = false;
				if($importInfo['status'] == Import_Queue_Action::$IMPORT_STATUS_SCHEDULED) {
					self::showScheduledStatus($importInfo);
					continue;
				}
				self::showCurrentStatus($importInfo, $importStatusCount, $continueImport);
				continue;
			} else {
				$viewer->assign('OWNER_ID', $importInfo['user_id']);
				$importDataController->finishImport();
				self::showResult($importInfo, $importStatusCount);
				continue;
			}
		}

		if ($importEnded) {
			$viewer->view('EndedImportButtons.tpl', 'RSNImportSources');
		} else {
			$viewer->assign('IMPORT_SOURCE', $importInfos[0]['importsourceclass']);
			$viewer->view('ImportDoneButtons.tpl', 'RSNImportSources');
		}

		$viewer->view('ImportFooter.tpl', 'RSNImportSources');
	}

	/**
	 * Method called by the showImportStatus method.
	 */
	public static function showCurrentStatus($importInfo, $importStatusCount, $continueImport) {
		$moduleName = $importInfo['module'];
		$importId = $importInfo['id'];
		$viewer = new Vtiger_Viewer();

		$viewer->assign('FOR_MODULE', $moduleName);
		$viewer->assign('MODULE', 'RSNImportSources');
		$viewer->assign('IMPORT_ID', $importId);
		$viewer->assign('IMPORT_RESULT', $importStatusCount);
		$viewer->assign('INVENTORY_MODULES', getInventoryModules());
		$viewer->assign('CONTINUE_IMPORT', $continueImport);

		$viewer->view('ImportStatus.tpl', 'RSNImportSources');
	}

	/**
	 * Method called by the showImportStatus method.
	 */
	public static function showResult($importInfo, $importStatusCount) {
		$moduleName = $importInfo['module'];
		$ownerId = $importInfo['user_id'];
		$viewer = new Vtiger_Viewer();
        
		$viewer->assign('SKIPPED_RECORDS',$skippedRecords);
		$viewer->assign('FOR_MODULE', $moduleName);
		$viewer->assign('MODULE', 'RSNImportSources');
		$viewer->assign('OWNER_ID', $ownerId);
		$viewer->assign('IMPORT_RESULT', $importStatusCount);
		$viewer->assign('INVENTORY_MODULES', getInventoryModules());
		$viewer->assign('MERGE_ENABLED', $importInfo['merge_type']);

		$viewer->view('ImportResult.tpl', 'RSNImportSources');
	}

	/**
	 * Method called by the showImportStatus method.
	 */
	public static function showScheduledStatus($importInfo) {
		// TODO: $importInfo['module'] should be the current main module !!
		$moduleName = $importInfo['module'];
		$importId = $importInfo['id'];

		$viewer = new Vtiger_Viewer();
		$viewer->assign('FOR_MODULE', $moduleName);
		$viewer->assign('MODULE', 'RSNImportSources');
		$viewer->assign('IMPORT_ID', $importId);
		$viewer->view('ImportSchedule.tpl', 'RSNImportSources');
	}
	
	
	static $allTaxes;
	
	static function getTax($rate){
		if(!$rate)
			return false;
		$rate = self::str_to_float($rate);
		if(!self::$allTaxes)
			self::$allTaxes = getAllTaxes();
		foreach(self::$allTaxes as $tax)
			if($tax['percentage'] == $rate)
				return $tax;
		return false;
	}
	
	/**
	 * Method that return the product id using his code.
	 * @param $productcode : the code of the product.
	 * @return int - the product id | null.
	 */
	function getProductId($productcode, &$isProduct = NULL, &$name = NULL) {
        //TODO cache
        
		$db = PearDatabase::getInstance();
		if($isProduct !== TRUE){
			$query = 'SELECT serviceid, label
				FROM vtiger_service s
				JOIN vtiger_crmentity e
					ON s.serviceid = e.crmid
				WHERE s.productcode = ?
				AND e.deleted = FALSE
				AND discontinued = 1
				LIMIT 1';
			$result = $db->pquery($query, array($productcode));
	
			if ($db->num_rows($result) == 1) {
				$row = $db->fetch_row($result, 0);
				$isProduct = false;
				$name = $row['label'];
				return $row['serviceid'];
			}
		}
		//produits
		if($isProduct !== FALSE){
			$query = 'SELECT productid, label
				FROM vtiger_products p
				JOIN vtiger_crmentity e
					ON p.productid = e.crmid
				WHERE p.productcode = ?
				AND e.deleted = FALSE
				AND discontinued = 1
				LIMIT 1';
			$result = $db->pquery($query, array($productcode));
	
			if ($db->num_rows($result) == 1) {
				$row = $db->fetch_row($result, 0);
				$isProduct = true;
				$name = $row['label'];
				return $row['productid'];
			}
		}

		return null;
	}
	
	static function str_to_float($str){
		if(!is_string($str))
			return $str;
		return (float)str_replace(',', '.', $str);
	}
}

?>
