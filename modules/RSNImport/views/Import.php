<?php

class RSNImport_Import_View extends Vtiger_View_Controller{

	var $request;
	var $user;

	public function  __construct($request, $user) {
		parent::__construct();
		$this->request = $request;
		$this->user = $user;

		$this->exposeMethod('showConfiguration');
	}

	function checkPermission(Vtiger_Request $request) {//tmp use rsnimport permission
		$moduleName = $request->get('for_module');
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'Import')) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}
	
	function process(Vtiger_Request $request) {
		$mode = $request->getMode();
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
		}

		return;
	}

	function showConfiguration(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $request->getModule());
		return $viewer->view('NothingToConfigure.tpl', 'RSNImport');
	}

	function getImportModules() {
		return array($this->request->get('for_module'));
	}

	function getMainImportModule() {
		return $this->request->get('for_module');
	}

	function getFieldsFor($module) {
		return array();
	}

	function getMappingFor($module) {
		$fields = $this->getFieldsFor($module);
		$maping = array();
		for ($i = 0; $i < sizeof($fields); ++$i) {
			$maping[$fields[$i]] = $i;
		}

		return $maping;
	}

	function preImportData(Vtiger_Request $request) {
		//child class must overload this function
		return false;
	}

	public function getPreviewData() {
		//child class must overload this function???????????? i think not usefull !!
		$adb = PearDatabase::getInstance();
		$importModules = $this->getImportModules();
		$previewData = array();

		foreach($importModules as $module) {// tmp do not do that here
			$previewData[$module] = array();
			$fields = $this->getFieldsFor($module);

			$tableName = RSNImport_Utils_Helper::getDbTableName($this->user, $module);
			$sql = 'SELECT ';

			for($i = 0; $i < sizeof($fields); ++$i) {
				if ($i != 0) {
					$sql .= ', ';
				}

				$sql .= $fields[$i];
			}

			$sql .= ' FROM ' . $tableName . ' WHERE status = '. RSNImport_Data_Action::$IMPORT_RECORD_NONE . ' LIMIT 12';//tmp do not hardcode limiot !!

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

	function getNumberOfRecords() {
		$numberOfRecords = 0;
		$importModules = $this->getImportModules();

		foreach($importModules as $module) {
			$numberOfRecords += RSNImport_Utils_Helper::getNumberOfRecords($this->user, $module);
		}

		return $numberOfRecords;
	}

	function checkImportIsScheduled() {
		$configReader = new RSNImport_Config_Model();
		$immediateImportRecordLimit = $configReader->get('immediateImportLimit');

		$numberOfRecordsToImport = $this->getNumberOfRecords();

		if($numberOfRecordsToImport > $immediateImportRecordLimit) {
			$this->request->set('is_scheduled', true);
		}
	}

	public function queueDataImport($module) {
		RSNImport_Queue_Action::add($this->request, $this->user, $module, $this->getMappingFor($module), $this->getDefaultValuesFor($module));
	}

	public function triggerImport($module, $batchImport=false) {
		$importInfo = RSNImport_Queue_Action::getImportInfo($module, $this->user);
		$importDataController = new RSNImport_Data_Action($importInfo, $this->user);

		if(!$batchImport) {
			if(!$importDataController->initializeImport()) {
				RSNImport_Utils_Helper::showErrorPage(vtranslate('ERR_FAILED_TO_LOCK_MODULE', 'Import'));
				exit;
			}
		}

		$this->doImport($importDataController, $module);
		RSNImport_Queue_Action::updateStatus($importInfo['id'], RSNImport_Queue_Action::$IMPORT_STATUS_HALTED);
	}

	public function doImport($importDataController, $module) {
		$importDataController->importData();
	}

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

		$importInfos = RSNImport_Queue_Action::getUserCurrentImportInfos($this->user);
		RSNImport_Import_View::showImportStatus($importInfos, $this->user, $this->request->get("for_module"));
	}

	public static function showImportStatus($importInfos, $user, $moduleName = "") {//tmp manage multiple import infos !!!
		if($importInfos == null || sizeof($importInfos) == 0) {
			RSNImport_Utils_Helper::showErrorPage(vtranslate('ERR_IMPORT_INTERRUPTED', 'RSNImport'));
			exit;
		}

		$viewer = new Vtiger_Viewer();
		$viewer->assign('FOR_MODULE', $moduleName);//tmp
		$viewer->assign('MODULE', 'RSNImport');//tmp
		$viewer->view('header.tpl', 'RSNImport');

		$importEnded = true;

		foreach($importInfos as $importInfo) {

			$importDataController = new RSNImport_Data_Action($importInfo, $user);
			if($importInfo['status'] == RSNImport_Queue_Action::$IMPORT_STATUS_HALTED ||
					$importInfo['status'] == RSNImport_Queue_Action::$IMPORT_STATUS_NONE) {
				$continueImport = true;
			} else {
				$continueImport = false;
			}
			
			/*$focus = CRMEntity::getInstance($importInfo['module']);
			if(method_exists($focus, 'getImportStatusCount')) {
				var_dump($importDataController->get('module'));
				$importStatusCount = $focus->getImportStatusCount($importDataController);
			} else {*/
				$importStatusCount = $importDataController->getImportStatusCount();
			//}
			$totalRecords = $importStatusCount['TOTAL'];
			if($totalRecords > ($importStatusCount['IMPORTED'] + $importStatusCount['FAILED'])) {
				$importEnded = false;
				if($importInfo['status'] == Import_Queue_Action::$IMPORT_STATUS_SCHEDULED) { //tmp
					self::showScheduledStatus($importInfo);
					continue;//tmp exit
				}
				self::showCurrentStatus($importInfo, $importStatusCount, $continueImport);
				continue;//tmp exit
			} else {
				$importDataController->finishImport();
				self::showResult($importInfo, $importStatusCount);
			}
		}

		if ($importEnded) {
			$viewer->view('EndImportButtons.tpl', 'RSNImport');
		} else {
			$viewer->assign('IMPORT_SOURCE', $importInfos[0]['importsourceclass']);
			$viewer->view('ImportDoneButtons.tpl', 'RSNImport');
		}

		$viewer->view('footer.tpl', 'RSNImport');
	}

	// public static function showImportStatus_($importInfos, $user) {//tmp manage multiple import infos !!!
	// 	if($importInfos == null || sizeof($importInfos) == 0) {
	// 		RSNImport_Utils_Helper::showErrorPage(vtranslate('ERR_IMPORT_INTERRUPTED', 'RSNImport'));
	// 		exit;
	// 	}
	// 	$importInfo = $importInfos[0];//TMP !!!!!!

	// 	$importDataController = new RSNImport_Data_Action($importInfo, $user);
	// 	if($importInfo['status'] == RSNImport_Queue_Action::$IMPORT_STATUS_HALTED ||
	// 			$importInfo['status'] == RSNImport_Queue_Action::$IMPORT_STATUS_NONE) {
	// 		$continueImport = true;
	// 	} else {
	// 		$continueImport = false;
	// 	}
		
	// 	$focus = CRMEntity::getInstance($importInfo['module']);
	// 	if(method_exists($focus, 'getImportStatusCount')) {
	// 		var_dump($importDataController->get('module'));
	// 		$importStatusCount = $focus->getImportStatusCount($importDataController);
	// 	} else {
	// 		$importStatusCount = $importDataController->getImportStatusCount();
	// 	//}
	// 	$totalRecords = $importStatusCount['TOTAL'];
	// 	if($totalRecords > ($importStatusCount['IMPORTED'] + $importStatusCount['FAILED'])) {
	// 		if($importInfo['status'] == Import_Queue_Action::$IMPORT_STATUS_SCHEDULED) { //tmp
	// 			self::showScheduledStatus($importInfo);
	// 			exit;//tmp exit !!!
	// 		}
	// 		self::showCurrentStatus($importInfo, $importStatusCount, $continueImport);
	// 		exit;//tmp exit !!!
	// 	} else {
	// 		$importDataController->finishImport();
	// 		self::showResult($importInfo, $importStatusCount);
	// 	}
	// }

	public function getPreviewTemplateName() {
		return 'ImportPreview.tpl';
	}

	public static function showCurrentStatus($importInfo, $importStatusCount, $continueImport) {// tmp template !!!//tmp manage multiple import infos !!!
		$moduleName = $importInfo['module'];
		$importId = $importInfo['id'];
		$viewer = new Vtiger_Viewer();

		$viewer->assign('FOR_MODULE', $moduleName);
		$viewer->assign('MODULE', 'RSNImport');
		$viewer->assign('IMPORT_ID', $importId);
		$viewer->assign('IMPORT_RESULT', $importStatusCount);
		$viewer->assign('INVENTORY_MODULES', getInventoryModules());
		$viewer->assign('CONTINUE_IMPORT', $continueImport);

		$viewer->view('ImportStatus.tpl', 'RSNImport');
	}

	public static function showResult($importInfo, $importStatusCount) {// tmp template ???//tmp manage multiple import infos !!!
		$moduleName = $importInfo['module'];
		$ownerId = $importInfo['user_id'];
        $viewer = new Vtiger_Viewer();
        
		$viewer->assign('SKIPPED_RECORDS',$skippedRecords);
        $viewer->assign('FOR_MODULE', $moduleName);
		$viewer->assign('MODULE', 'RSNImport');
		$viewer->assign('OWNER_ID', $ownerId);
		$viewer->assign('IMPORT_RESULT', $importStatusCount);
		$viewer->assign('INVENTORY_MODULES', getInventoryModules());
		$viewer->assign('MERGE_ENABLED', $importInfo['merge_type']);

		$viewer->view('ImportResult.tpl', 'RSNImport');
	}

	public static function showScheduledStatus($importInfo) {// tmp template !!!//tmp manage multiple import infos !!!
		$moduleName = $importInfo['module'];//tmp must be the current main module !!
		$importId = $importInfo['id'];
		$viewer = new Vtiger_Viewer();

		$viewer->assign('FOR_MODULE', $moduleName);
		$viewer->assign('MODULE', 'RSNImport');
		$viewer->assign('IMPORT_ID', $importId);

		$viewer->view('ImportSchedule.tpl', 'RSNImport');
	}
}

?>
