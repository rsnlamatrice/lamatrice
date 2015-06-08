<?php

//tmp Index ??
class RSNImport_Index_View extends Vtiger_Index_View {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('continueImport');//tmp continueImport ?? where is methode and what it is used for ???
		$this->exposeMethod('preImport');
		$this->exposeMethod('uploadAndParse');
		$this->exposeMethod('selectImportSource');
		$this->exposeMethod('import');
		$this->exposeMethod('undoImport');
		$this->exposeMethod('lastImportedRecords');
		$this->exposeMethod('deleteMap');
		$this->exposeMethod('clearCorruptedData');
		$this->exposeMethod('cancelImport');
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
		global $VTIGER_BULK_SAVE_MODE;//tmp use for what ??
		$previousBulkSaveMode = $VTIGER_BULK_SAVE_MODE;
		$VTIGER_BULK_SAVE_MODE = true;
		$moduleName = $request->get('for_module');
		$mode = $request->getMode();
		if(!empty($mode)) {//tmp use new mode !
			// Added to check the status of import
			if($mode == 'continueImport' || $mode == 'preImport' || $mode == 'selectImportSource') {//|| $mode == 'import' -> probleme if F5 on import result page -> multiple import !!
				$this->checkImportStatus($request);
			}
			$this->invokeExposedMethod($mode, $request);
		} else {
			$this->checkImportStatus($request);
			$this->selectImportSource($request);
		}
		
		$VTIGER_BULK_SAVE_MODE = $previousBulkSaveMode;
	}

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);

		$jsFileNames = array(
			'modules.RSNImport.resources.RSNImport'
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

		return $headerScriptInstances;
	}

	function selectImportSource(Vtiger_Request $request) { // tmp
		$viewer = $this->getViewer($request);
		$moduleName = $request->get('for_module');

		$viewer->assign('FOR_MODULE', $moduleName);
		$viewer->assign('MODULE', 'RSNImport');
		$sources = RSNImport_Utils_Helper::getSourceList($moduleName);
		$viewer->assign('SOURCES', $sources);

		//$viewer->assign('ERROR_MESSAGE', $request->get('error_message')); // tmp ??

		return $viewer->view('SelectImportSource.tpl', 'RSNImport');
	}

	function getImportController(Vtiger_Request $request) {
		$className = $request->get('ImportSource');
		if ($className) {
			$importClass = RSNImport_Utils_Helper::getClassFromName($className);
			$user = Users_Record_Model::getCurrentUserModel();
			$importController = new $importClass($request, $user);

			return $importController;
		}

		return null;
	}
	
	function preImport(Vtiger_Request $request) { // tmp
		$importController = $this->getImportController($request);

		if ($importController->preImportData($request)) {
			$this->displayDataPreview($request, $importController->getPreviewData(), $importController);//tmp
		} else {
			$this->selectImportSource($request);
		}
	}

	function displayDataPreview(Vtiger_Request $request, $previewData, $importController) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->get('for_module');
		$viewer->assign('FOR_MODULE', $moduleName);
		$viewer->assign('MODULE', 'RSNImport');
		$viewer->assign('PREVIEW_DATA', $previewData);
		$viewer->assign('IMPORT_SOURCE', $request->get('ImportSource'));

		//$viewer->assign('ERROR_MESSAGE', $request->get('error_message')); // tmp ??

		return $viewer->view($importController->getPreviewTemplateName(), 'RSNImport');
	}

	function import(Vtiger_Request $request) {
		$importController = $this->getImportController($request);
		$importController->import();
	}

	function undoImport(Vtiger_Request $request) { // tmp: use function of import module ?? () // tmp manage multiple module !!
		$viewer = new Vtiger_Viewer();
		$db = PearDatabase::getInstance();

		$moduleName = $request->get('for_module');
		$ownerId = $request->get('foruser');

		$user = Users_Record_Model::getCurrentUserModel();
		$dbTableName = Import_Utils_Helper::getDbTableName($user, $moduleName);

		if(!$user->isAdminUser() && $user->id != $ownerId) {
			$viewer->assign('MESSAGE', 'LBL_PERMISSION_DENIED');
			$viewer->view('OperationNotPermitted.tpl', 'Vtiger');
			exit;
		}
        $previousBulkSaveMode = $VTIGER_BULK_SAVE_MODE;
        $VTIGER_BULK_SAVE_MODE = true;  
		$query = "SELECT recordid FROM $dbTableName WHERE status = ? AND recordid IS NOT NULL";
		//For inventory modules
		$inventoryModules = getInventoryModules();
		if(in_array($moduleName, $inventoryModules)){
			$query .=' GROUP BY subject';
		}
		//End
		$result = $db->pquery($query, array(Import_Data_Action::$IMPORT_RECORD_CREATED));
		$noOfRecords = $db->num_rows($result);
		$noOfRecordsDeleted = 0;
        $entityData = array();
		for($i=0; $i<$noOfRecords; $i++) {
			$recordId = $db->query_result($result, $i, 'recordid');
			if(isRecordExists($recordId) && isPermitted($moduleName, 'Delete', $recordId) == 'yes') {
				$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
                $recordModel->setId($recordId);
                $recordModel->delete();
                $focus = $recordModel->getEntity();
                $focus->id = $recordId;
                $entityData[] = VTEntityData::fromCRMEntity($focus);
				$noOfRecordsDeleted++;
			}
		}
        $entity = new VTEventsManager($db);        
        $entity->triggerEvent('vtiger.batchevent.delete',$entityData);
        $VTIGER_BULK_SAVE_MODE = $previousBulkSaveMode;
		$viewer->assign('FOR_MODULE', $moduleName);
		$viewer->assign('MODULE', 'Import');
		$viewer->assign('TOTAL_RECORDS', $noOfRecords);
		$viewer->assign('DELETED_RECORDS_COUNT', $noOfRecordsDeleted);
		$viewer->view('ImportUndoResult.tpl', 'Import');
	}

	function lastImportedRecords(Vtiger_Request $request) { // tmp : to managed !!
		Vtiger_Import_View::lastImportedRecords($request);
	}

	function clearCorruptedData(Vtiger_Request $request) {
		$user = Users_Record_Model::getCurrentUserModel();
		$importController = $this->getImportController($request);
		$forModule = $request->get('for_module');

		if ($importController) {
			$modules = $importController->getImportModules();
		} else {
			$modules = array($forModule);
		}

		foreach($modules as $moduleName) {
			Import_Utils_Helper::clearUserImportInfo($user, $moduleName);//tmp $user -> only one user can cancel import ...
		}

		$this->selectImportSource($request);
	}

	function cancelImport(Vtiger_Request $request) {
		$user = Users_Record_Model::getCurrentUserModel();
		$importController = $this->getImportController($request);
		$forModule = $request->get('for_module');
		$importInfos = array();

		if ($importController) {
			$modules = $importController->getImportModules();
		} else {
			$modules = array($forModule);
		}

		foreach($modules as $moduleName) {
			$importInfo = RSNImport_Queue_Action::getImportInfo($moduleName, $user);//tmp $user -> only one user can cancel import ...
			if($importInfo != null) {
				array_push($importInfos, $importInfo);
				$importUser  =Users_Record_Model::getInstanceById($importInfo['user_id'], 'Users');
				$importDataController = new RSNImport_Data_Action($importInfo, $importUser);
				$importDataController->updateImportStatusForCancel();
				$importStatusCount = $importDataController->getImportStatusCount();
				$importDataController->finishImport();
			}
		}

		RSNImport_Import_View::showImportStatus($importInfos, $user, $forModule);
	}

	function checkImportStatus(Vtiger_Request $request) { // tmp import template called ...
		$forModule = $request->get('for_module');
		$user = Users_Record_Model::getCurrentUserModel();
		$mode = $request->getMode();
		$importSource = RSNImport_Queue_Action::getImportClassName($forModule, $user);

		if ($importSource) {
			$request->set('ImportSource', $importSource);
			$importController = $this->getImportController($request);
			$modules = $importController->getImportModules();
		} else {//tmp get only currentmodule name, or get preconfigured list by module ??
			$modules = array($forModule);
		}

		$statusOk = true;
		$showStatus = false;

		foreach($modules as $moduleName) {
			// Check if import on the module is locked
			$lockInfo = RSNImport_Lock_Action::isLockedForModule($moduleName);
			if($lockInfo != null) {
				$lockedBy = $lockInfo['userid'];
				$statusOk = false;
				if($user->id != $lockedBy && !$user->isAdminUser()) {
					RSNImport_Utils_Helper::showImportLockedError($lockInfo);//tmp module blocked ??
					exit;//tmp exit ???
				} else {
					if($mode == 'continueImport' && $user->id == $lockedBy) {
						
						$importController->triggerImport(true);
					} else {
						$lockOwner = $user;
						if($user->id != $lockedBy) {
							$lockOwner = Users_Record_Model::getInstanceById($lockInfo['userid'], 'Users');
						}
						$showStatus = true;
					}
					
					continue;
				}
			}

			if(RSNImport_Utils_Helper::isUserImportBlocked($user, $moduleName)) {//tmp check all module !!!
				$importInfo = RSNImport_Queue_Action::getImportInfo($moduleName, $user);
				$statusOk = false;
				if($importInfo != null) {
					//var_dump($importInfo);
					$showStatus = true;
					continue;
				} else {
					if ($importSource) {
						RSNImport_Utils_Helper::showImportTableBlockedError($forModule, $user, $importSource);
					} else {
						RSNImport_Utils_Helper::showImportTableBlockedError($moduleName, $user, $importSource);
					}

					exit;//tmp exit ???
				}
			}
		}

		if (!$statusOk) {
			if ($showStatus) {
				$importInfos = RSNImport_Queue_Action::getUserCurrentImportInfos($user);
				RSNImport_Import_View::showImportStatus($importInfos, $user, $forModule);
			}

			exit;
		} else {
			foreach($modules as $moduleName) {
				RSNImport_Utils_Helper::clearUserImportInfo($user, $moduleName);
			}
		}
	}
}
