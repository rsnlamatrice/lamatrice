<?php

class RSNImportSources_Index_View extends Vtiger_Index_View {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('continueImport');
		$this->exposeMethod('preImport');
		$this->exposeMethod('uploadAndParse');
		$this->exposeMethod('selectImportSource');
		$this->exposeMethod('import');
		$this->exposeMethod('undoImport');
		$this->exposeMethod('lastImportedRecords');
		$this->exposeMethod('deleteMap');
		$this->exposeMethod('clearCorruptedData');
		$this->exposeMethod('cancelImport');
		$this->exposeMethod('continueHaltedImport');
		$this->exposeMethod('validatePreImportData');
		$this->exposeMethod('getPreviewData');
	}

	/**
	 * Method to check if curent user has permission to import in the concerned module.
	 * @param Vtiger_Request $request: the curent request.
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
	 * Method to catch rsnimport request and redirect to the current step method.
	 * @param Vtiger_Request $request: the curent request.
	 */
	function process(Vtiger_Request $request) {
		// TODO:  $mode == 'import' -> probleme if F5 on import result page -> many time the same import.
		global $VTIGER_BULK_SAVE_MODE;
		$previousBulkSaveMode = $VTIGER_BULK_SAVE_MODE;
		$VTIGER_BULK_SAVE_MODE = false;
		$moduleName = $request->get('for_module');
		$mode = $request->getMode();
		if(!empty($mode)) {
			if($mode == 'continueImport' || $mode == 'preImport' || $mode == 'selectImportSource') {
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
	 * Method to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);

		$jsFileNames = array(
			'modules.RSNImportSources.resources.RSNImportSources'
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);

		return $headerScriptInstances;
	}

	/**
	 * Method to display the firt step of the import (the select import source step).
	 * @param Vtiger_Request $request: the curent request.
	 */
	function selectImportSource(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->get('for_module');
		$defaultSource = $request->get('defaultsource');

		$viewer->assign('FOR_MODULE', $moduleName);
		$viewer->assign('MODULE', 'RSNImportSources');
		$sources = RSNImportSources_Utils_Helper::getSourceList($moduleName);
		$viewer->assign('SOURCES', $sources);
		$viewer->assign('DEFAULT_SOURCE', $defaultSource);
		$viewer->assign('ERROR_MESSAGE', $request->get('error_message'));

		return $viewer->view('SelectImportSource.tpl', 'RSNImportSources');
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
	
	/**
	 * Method to process the second step of the import (the pre-import data step).
	 *  This method pre-import and display data.
	 *  If pre-import failed, it display the fisrtstep view.
	 * @param Vtiger_Request $request: the curent request.
	 */
	function preImport(Vtiger_Request $request) {
		
		set_time_limit(5 * 60);
		
		$importController = $this->getImportController($request);

		if ($importController->preImportData($request)) {
			$importController->displayDataPreview();
		} else {
			$this->selectImportSource($request);
		}
	}

	/** ED150906
	 * get more preview data
	 */
	function getPreviewData(Vtiger_Request $request) {
		
		$importController = $this->getImportController($request);

		$importController->displayDataPreview();
	}


	/**
	 * Method to process the third step of the import (the import step).
	 * @param Vtiger_Request $request: the curent request.
	 */
	function import(Vtiger_Request $request) {
		$importController = $this->getImportController($request);
		$importController->import();
	}

	/**
	 * Method to undo the last import for a specific user and a specific import type.
	 * @param Vtiger_Request $request: the curent request.
	 */
	function undoImport(Vtiger_Request $request) {
		$ownerId = $request->get('foruser');
		$user = Users_Record_Model::getCurrentUserModel();

		if(!$user->isAdminUser() && $user->id != $ownerId) {
			$viewer->assign('MESSAGE', 'LBL_PERMISSION_DENIED');
			$viewer->view('OperationNotPermitted.tpl', 'Vtiger');
			exit;
		}

		$importController = $this->getImportController($request);
		$importController->undoImport($ownerId);
	}

	/**
	 * Method to get the last imported records.
	 *  (this method is curently not used ...)
	 * @param Vtiger_Request $request: the curent request.
	 */
	function lastImportedRecords(Vtiger_Request $request) {
		// TODO : do not call an Import module methode ?
		Vtiger_Import_View::lastImportedRecords($request);
	}

	/**
	 * Method to clear the pre-import tables and display the first step template.
	 * @param Vtiger_Request $request: the curent request.
	 */
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
			Import_Utils_Helper::clearUserImportInfo($user, $moduleName);
		}

		$this->selectImportSource($request);
	}

	/**
	 * Method to cancel the current running import and display the status of the import.
	 * @param Vtiger_Request $request: the curent request.
	 */
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
			//TOTO : Allow admin to cancel import !
			$importInfo = RSNImportSources_Queue_Action::getImportInfo($moduleName, $user);
			if($importInfo != null) {
				array_push($importInfos, $importInfo);
				$importUser  =Users_Record_Model::getInstanceById($importInfo['user_id'], 'Users');
				$importDataController = new RSNImportSources_Data_Action($importInfo, $importUser);
				$importDataController->updateImportStatusForCancel();
				$importStatusCount = $importDataController->getImportStatusCount();
				$importDataController->finishImport();
			}
		}

		RSNImportSources_Import_View::showImportStatus($importInfos, $user, $forModule);
	}

	/** ED150826
	 * Method to reset halted import to scheduled status and display the status of the import.
	 * @param Vtiger_Request $request: the curent request.
	 */
	function continueHaltedImport(Vtiger_Request $request) {
		$importController = $this->getImportController($request);
		if (!$importController) {	
			echo "<pre>Impossible de trouver le controller.</pre>";
			$this->checkImportStatus($request);
			return;
		}
		$importId = $request->get('import_id');
		if ($importController->needValidatingStep())
			RSNImportSources_Queue_Action::updateStatus($importId, Import_Queue_Action::$IMPORT_STATUS_VALIDATING);
		else
			RSNImportSources_Queue_Action::updateStatus($importId, Import_Queue_Action::$IMPORT_STATUS_SCHEDULED);

		$request->set('mode', false);
		$this->checkImportStatus($request);
	}

	/** ED150914
	 * Method to show and edit pre-import data.
	 * @param Vtiger_Request $request: the curent request.
	 */
	function validatePreImportData(Vtiger_Request $request) {
		$importController = $this->getImportController($request);
		if (!$importController) {	
			echo "<pre>Impossible de trouver le controller.</pre>";
			$this->checkImportStatus($request);
			return;
		}
		$importController->displayDataPreview();
	}

	/**
	 * Method to check if there is no import currently running or cortrupted data in the temporary import table.
	 *  It display the right template ifthere is corrupted data or locked table.
	 *  If there is no probleme, it clear the informations of the last import.
	 * @param Vtiger_Request $request: the curent request.
	 *
	 * TODO : il faudrait que les imports en cours apparaissent si le for_module est lié à n'importe quel RSNImportSources d'après vtiger_rsnimportsources.modules, càd si l'import apparait dans la liste de sélection.
	 * Pour l'instant, on ne se base que sur la queue et une table d'import de ce module.
	 * Or, un import disponible depuis Contacts peut ne faire d'importe que dans une autre table, $importController->getImportModules()
	 * 	il faudrait pouvoir utiliser $importController->getLockModules()
	 */
	function checkImportStatus(Vtiger_Request $request) {
		$forModule = $request->get('for_module');
		$user = Users_Record_Model::getCurrentUserModel();
		$mode = $request->getMode();
		//teste si une table d'importation existe pour ce module
		$importSource = RSNImportSources_Queue_Action::getImportClassName($forModule, $user);

		if ($importSource) {
			$request->set('ImportSource', $importSource);
			$importController = $this->getImportController($request);
			$modules = $importController->getImportModules();
		} else {
			//tmp get only currentmodule name, or get preconfigured list by module ??
			$modules = array($forModule);
		}

		$statusOk = true;
		$showStatus = false;

		foreach($modules as $moduleName) {
			// Check if import on the module is locked
			$lockInfo = RSNImportSources_Lock_Action::isLockedForModule($moduleName);
			if($lockInfo != null) {
				$lockedBy = $lockInfo['userid'];
				$statusOk = false;
				if($user->id != $lockedBy && !$user->isAdminUser()) {
					RSNImportSources_Utils_Helper::showImportLockedError($lockInfo);
					exit;
				} else {
					if($mode == 'continueImport' && $user->id == $lockedBy) {
						if($importController)
							$importController->triggerImport(true);
						else
							throw new Exception('Missing controller');
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

			if(RSNImportSources_Utils_Helper::isUserImportBlocked($user, $moduleName)) {
				$importInfo = RSNImportSources_Queue_Action::getImportInfo($moduleName, $user);
				$statusOk = false;
				if($importInfo != null) {
					$showStatus = true;
					continue;
				} else {
					if ($importSource) {
						RSNImportSources_Utils_Helper::showImportTableBlockedError($forModule, $user, $importSource);
					} else {
						RSNImportSources_Utils_Helper::showImportTableBlockedError($moduleName, $user, $importSource);
					}

					exit;
				}
			}
		}

		if (!$statusOk) {
			if ($showStatus) {
				$importInfos = RSNImportSources_Queue_Action::getUserCurrentImportInfos($user);
				RSNImportSources_Import_View::showImportStatus($importInfos, $user, $forModule);
			}

			exit;
		} else {
			foreach($modules as $moduleName) {
				RSNImportSources_Utils_Helper::clearUserImportInfo($user, $moduleName);
			}
		}
	}
}
