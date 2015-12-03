<?php

define('ASSIGNEDTO_ALL', '7');

define('CURRENCY_ID', 1);
define('CONVERSION_RATE', 1);

define('IMPORTCHECKER_CACHE_MAX', 1024);

class RSNImportSources_Import_View extends Vtiger_View_Controller{

	var $request;
	var $user;
	/*ED150826*/
	var $scheduledId;
	/*ED150831*/
	var $recordModel;

	/* Etats de validation des données.
	 * Utilisé dans l'importation des pétitions pour la reconnaissance des contacts, avant importation
	*/
	static $RECORDID_STATUS_NONE = 0;
	static $RECORDID_STATUS_SELECT = 1;
	static $RECORDID_STATUS_CREATE = 2;
	static $RECORDID_STATUS_UPDATE = 3;
	static $RECORDID_STATUS_SKIP = 4;
	static $RECORDID_STATUS_LATER = 5;
	static $RECORDID_STATUS_CHECK = 10;
	static $RECORDID_STATUS_SINGLE = 11;
	static $RECORDID_STATUS_MULTI = 12;
	
	
	public function __construct($request = FALSE, $user = FALSE) {
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
		if(!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'EditView')) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function getHeaderCss(Vtiger_Request $request) {
		$headerCssInstances = parent::getHeaderCss($request);

		$cssFileNames = array(
			'~/layouts/vlayout/modules/RSNImportSources/resources/css/style.css',
		);
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($headerCssInstances, $cssInstances);

		return $headerCssInstances;
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
		$viewer = $this->initConfiguration($request);
		return $viewer->view('NothingToConfigure.tpl', 'RSNImportSources');
	}

	/**
	 * Method to initialize the configuration template of the import for the first step.
	 *  It display the select file template.
	 * @param Vtiger_Request $request: the curent request.
	 * @return viewer
	 */
	function initConfiguration(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$viewer->assign('MODULE', $request->getModule());
		$viewer->assign('CONTROLLER_NAME', $this->getRecordModel()->get('title'));
		return $viewer;
	}
		
	/** ED150913
	 * Method to initialize the configuration template of the import for the first step.
	 *  It display the select related record template.
	 * @param Vtiger_Request $request: the curent request.
	 * @return viewer
	 */
	function initConfigurationToSelectRelatedModule(Vtiger_Request $request, $relatedModuleName, $searchKey = false, $searchValue = false) {
		
		//Model du champ de type reference au module
		//Cherche n'importe quel champ qui soit une référence au module lié
		switch($relatedModuleName){
			case 'Documents' :
				//Par exemple, dans la facture, le champ notesid
				$relatedModule = Vtiger_Module_Model::getInstance('Invoice');
				$relatedFieldModels = $relatedModule->getFields();
				$relatedFieldModel = $relatedFieldModels['notesid'];
				
				break;
			case 'Critere4D' :
				//TODO
				break;
			default:
				//TODO
				break;
		}
		
		$viewer = $this->initConfiguration($request);
		$viewer->assign('RELATED_MODULE', $relatedModuleName);
		$viewer->assign('RELATED_SEARCH_KEY', $searchKey);
		$viewer->assign('RELATED_SEARCH_VALUE', $searchValue);
		$viewer->assign('RELATED_FIELD_MODEL', $relatedFieldModel);
		
		$viewer->assign('SELECT_RELATED_RECORD', 'Documents');
		return $viewer;
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
	 * Method to get the modules that are concerned by the import.
	 * Used to lock any other import in other modules than the one where data will be insterted
	 * @return array - An array containing concerned module names.
	 */
	public function getLockModules() {
		return $this->getImportModules();
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
	 * Initialise les données de validation des Contacts
	 */
	function initDisplayPreviewContactsData() {
		$viewer = $this->getViewer($this->request);
		//$moduleName = $this->request->get('for_module');
		$moduleName = 'Contacts';
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$thisModuleName = $this->request->getModule();
		$thisModule = Vtiger_Module_Model::getInstance($thisModuleName);
		
		$viewer->assign('CONTACTS_FIELDS_MAPPING', $this->getContactsFieldsMappingForPreview());
		$viewer->assign('CONTACTS_MODULE_MODEL', $moduleModel);
		
		$viewer->assign('VALIDATE_PREIMPORT_URL', $thisModule->getValidatePreImportRowsUrl( $this->request->get('ImportSource'), $moduleName, 0, $limit));
		
		$viewer->assign('RSNNPAI_VALUES', $moduleModel->getPicklistValuesDetails( 'rsnnpai' ));
		
		//Décompte des statuts du contactid
		$counters = $this->getPreImportCountersByField('Contacts', '_contactid_status');
		$statusGroups = array(
			'' => array(
				'' => '(tous)',
			),
			'A vérifier' => array(
				RSNImportSources_Import_View::$RECORDID_STATUS_NONE => '',
				RSNImportSources_Import_View::$RECORDID_STATUS_CHECK => '',
				RSNImportSources_Import_View::$RECORDID_STATUS_SINGLE => '',
				RSNImportSources_Import_View::$RECORDID_STATUS_MULTI => '',
				RSNImportSources_Import_View::$RECORDID_STATUS_LATER => '',
			),
			'Prêts à importer' => array(
				RSNImportSources_Import_View::$RECORDID_STATUS_SELECT => '',
				RSNImportSources_Import_View::$RECORDID_STATUS_CREATE => '',
				RSNImportSources_Import_View::$RECORDID_STATUS_UPDATE => '',
				RSNImportSources_Import_View::$RECORDID_STATUS_SKIP => '',
			),
		);
		$total = 0;
		foreach($statusGroups as $statusGroupKey => $status)
			foreach($status as $statusKey => $statusValue){
				$counter = $counters[$statusKey] ? $counters[$statusKey] : 0;
				$total += $counter;
				$statusGroups[$statusGroupKey][$statusKey] = vtranslate('LBL_RECORDID_STATUS_'.$statusKey, $thisModuleName)
					. ' (' . $counter . ')';
			}
		$statusGroups[''][''] = '(tous) (' . $total . ')';
		$viewer->assign('CONTACTID_STATUS_GROUPS', $statusGroups);
		$viewer->assign('VALIDABLE_CONTACTS_COUNT', $total);
		
		//Décompte des sources
		$counters = $this->getPreImportCountersByField($moduleName, '_contactid_source');
		$sources = array( '' => '(toutes recherches)', '(null)' => '(introuvables)');
		$total = 0;
		foreach($counters as $sourceKey => $counter){
			$total += $counter;
			if($sourceKey === '(null)')
				$sourceLabel = '(introuvables)';
			else
				$sourceLabel = $sourceKey;
			$sources[$sourceKey] = $sourceLabel . ' (' . $counter . ')';
		}
		$sources[''] = '(toutes recherches) (' . $total . ')';
		$viewer->assign('CONTACTID_SOURCES', $sources);
	}
	
	/**
	 * Method to get the pre Imported data in order to preview them.
	 *  By default, it return the values in the pre-imported table.
	 *  This method can be overload in the child class.
	 * @return array - the pre-imported values group by module.
	 */
	public function getPreviewData($request, $offset = 0, $limit = 24, $importModules = false) {
		$adb = PearDatabase::getInstance();
		if(!$importModules)
			$importModules = $this->getImportModules();
		$previewData = array();

		foreach($importModules as $module) {
			$previewData[$module] = array();
			$params = array();
			$fields = $this->getFieldsFor($module);
			$fields[] = 'status';
			$fields[] = 'id';
			$tableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, $module);
			$sql = 'SELECT ';

			for($i = 0; $i < sizeof($fields); ++$i) {
				if ($i != 0) {
					$sql .= ', ';
				}

				$sql .= $fields[$i];
			}

			// TODO: do not hardcode display limit ?
			$sql .= '
				FROM ' . $tableName . '
			';
			$nCondition = 0;
			if($this->needValidatingStep()){
				$nCondition++;
				$sql .= ' WHERE status = '. RSNImportSources_Data_Action::$IMPORT_RECORD_NONE;
			}
			
			if($request->get('search_key')){
				$search_key = $request->get('search_key');
				$search_value = $request->get('search_value');
				if(!is_array($search_key)){
					$search_key = array($search_key);
					$search_value = array($search_value);
				}
				for($i = 0; $i < count($search_key); $i++)
					if($search_value[$i] || $search_value[$i] === '0'){
						if($nCondition++)
							$sql .= ' AND ';
						else
							$sql .= ' WHERE ';
						if($search_value[$i] === '(null)')
							$sql .= $search_key[$i] . ' IS NULL';
						else {
							$sql .= $search_key[$i] . ' = ?';
							$params[] = $search_value[$i];
						}
					}
			}
		
			$sql .= '
				LIMIT '.$offset.', '.$limit;
			$result = $adb->pquery($sql, $params);
			if(!$result){
				$adb->echoError('Erreur dans getPreviewData');
				die();
			}
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
	 * Compte le nombre de lignes de pré-imports par valeur distinct d'un champ
	 */
	function getPreImportCountersByField($module, $fieldName){
		$adb = PearDatabase::getInstance();
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, $module);
		$sql = "SELECT `$fieldName` `key`, COUNT(*)
			FROM $tableName
			WHERE status = " . RSNImportSources_Data_Action::$IMPORT_RECORD_NONE . "
			GROUP BY `$fieldName`
			ORDER BY `$fieldName`";
		$counters = array();
		$result = $adb->query($sql);
		if(!$result){
			echo "<pre>$sql</pre>";
			$adb->echoError('Erreur dans getPreImportCountersByField');
		}
		$numberOfRecords = $adb->num_rows($result);

		for ($i = 0; $i < $numberOfRecords; ++$i) {
			$key = $adb->query_result($result, $i, 0);
			if($key === null)
				$key = '(null)';
			$counters[$key] = $adb->query_result($result, $i, 1);
		}
		return $counters;
	}
	
	/**
	 * Method to get the number of pre-imported records.
	 * @param $status filter. False === all. Import_Data_Action::$IMPORT_RECORD_NONE === 0.
	 * @return int - the number of pre-imported records.
	 */
	function getNumberOfRecords($status = 0) {
		$numberOfRecords = 0;
		$importModules = $this->getImportModules();

		foreach($importModules as $module) {
			$numberOfRecords += RSNImportSources_Utils_Helper::getNumberOfRecords($this->user, $module, $status);
		}

		return $numberOfRecords;
	}

	function getImportPreviewTemplateName($moduleName){
		return 'ImportPreview.tpl';
	}
	
	/**
	 * Method to display the preview of the preimported data.
	 *  this method can be overload in the child class.
	 *
	 *  displayDataPreview est appelé après le pré-import et pendant la phase de validation du pré-import (des contacts)
	 */
	function displayDataPreview() {
		$this->initDisplayPreviewData();
		
		$viewer = $this->getViewer($this->request);
		$moduleName = $this->request->get('for_module');
		
		//ED150826 Show rows count
		$nbRecordsToImport = $this->getNumberOfRecords(false);
		$viewer->assign('SOURCE_ROWS_COUNT', $nbRecordsToImport);
		$viewer->assign('IMPORTABLE_ROWS_COUNT', $this->getNumberOfRecords(Import_Data_Action::$IMPORT_RECORD_NONE));
		
		//ED150906 get more data
		$offset = $this->request->get('page_offset');
		if(!$offset) $offset = 0;
		$limit = $this->request->get('page_limit');
		if(!$limit) $limit = min(200, max($offset, 24));
		
		$viewer->assign('FOR_MODULE', $moduleName);
		$viewer->assign('MODULE', 'RSNImportSources');
		$viewer->assign('PREVIEW_DATA', $this->getPreviewData($this->request, $offset, $limit));
		$viewer->assign('IMPORT_SOURCE', $this->request->get('ImportSource'));
		$viewer->assign('ERROR_MESSAGE', $this->request->get('error_message'));

		$viewer->assign('IS_SCHEDULED', $this->hasValidatingStep()
										|| $this->checkImportIsScheduled($nbRecordsToImport));
		
		//ED150906 get more data
		$viewer->assign('ROW_OFFSET', $offset);
		$thisModuleName = $this->request->getModule();
		$thisModule = Vtiger_Module_Model::getInstance($thisModuleName);
		$offset += $limit;
		$limit = min(200, max($offset, 24));
		$viewer->assign('PREVIEW_DATA_URL', $thisModule->getPreviewDataViewUrl( $this->request->get('ImportSource'), $moduleName, 0, $limit));
		$viewer->assign('MORE_DATA_URL', $thisModule->getPreviewDataViewUrl( $this->request->get('ImportSource'), $moduleName, $offset, $limit));
		
		if($this->hasValidatingStep){
			$viewer->assign('PREIMPORT_VALIDATING_FORM_URL', $thisModule->getPreImportValidatingFormUrl());
		}
		
		if($this->request->get('search_key')){
			$searches = array();
			$search_key = $this->request->get('search_key');
			$search_value = $this->request->get('search_value');
			if(!is_array($search_key)){
				$search_key = array($search_key);
				$search_value = array($search_value);
			}
			$nCondition = 0;
			for($i = 0; $i < count($search_key); $i++)
				if($search_value[$i] || $search_value[$i] === '0'){
					$searches[$search_key[$i]] = $search_value[$i];
				}
			$viewer->assign('HEADER_FILTERS', $searches);
		}
		return $viewer->view($this->getImportPreviewTemplateName($moduleName), $thisModuleName);
	}
	
	function initDisplayPreviewData() {
	}
	
	/**
	 * Method to check if the import must be scheduled.
	 *  It schedule import if the number of pre-imported record is greater than the imediat import limit (in the config model file).
	 */
	function checkImportIsScheduled($numberOfRecordsToImport = false) {
		$configReader = new RSNImportSources_Config_Model();
		$immediateImportRecordLimit = $configReader->get('immediateImportLimit');

		if($numberOfRecordsToImport === false)
			$numberOfRecordsToImport = $this->getNumberOfRecords();
		if($numberOfRecordsToImport > $immediateImportRecordLimit) {
			$this->request->set('is_scheduled', true);
		}
		return $this->request->get('is_scheduled');
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
	 * Function that returns if the import controller has a validating step of pre-import data
	 */
	public function hasValidatingStep(){
		return false;
	}
	
	/**
	 * After preImport validation, and before real import, does the controller need a validation step of pre-imported data ?
	 */
	public function needValidatingStep(){
		return false;
	}
	
	/**
	 * Method to add a import in the import queue table for a specific module.
	 * @param string $module : the module name.
	 */
	public function queueDataImport($module = false, $status = null) {
		if(!$module){
			foreach($this->getImportModules() as $module)
				$this->queueDataImport($module, $status);
		}
		else {
			$this->request->set('is_scheduled', true);
			if($status === null || $status === false){
				if($this->needValidatingStep())
					$status = RSNImportSources_Queue_Action::$IMPORT_STATUS_VALIDATING;
				else
					$status = RSNImportSources_Queue_Action::$IMPORT_STATUS_SCHEDULED;
			}
			RSNImportSources_Queue_Action::add($this->request, $this->user, $module, $this->getMappingFor($module), null, $status);
		}
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
		$this->updateStatus(Import_Queue_Action::$IMPORT_STATUS_RUNNING);
		try{
			$methode = "import" . ucfirst($module);
			if (method_exists($this, $methode)) {
				$this->$methode($importDataController);
			} else {
				$importDataController->importData();
			}
			if($this->needValidatingStep())
				$this->updateStatus(Import_Queue_Action::$IMPORT_STATUS_VALIDATING);
			else {
				$this->updateStatus(Import_Queue_Action::$IMPORT_STATUS_SCHEDULED);
				$this->postImportData($module);
			}
		
		}
		catch(Exception $ex){
			$this->updateStatus(Import_Queue_Action::$IMPORT_STATUS_HALTED);
			throw ($ex);
		}
	}
	
	/**
	 * Function de traitement post-import
	 * 	A ce moment, il peut rester des données à importer (memory_limit, par exemple)
	 */
	public function postImportData($moduleName){
		$this->logImportDataDone($moduleName);
	}
	
	/**
	 * Crée un commentaire pour l'import
	 * Archive les données qui dont l'import a échoués
	 */
	public function logImportDataDone($moduleName){
			
		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($this->user, $moduleName);
		$sql = 'SELECT id FROM ' . $tableName . ' WHERE status = '. RSNImportSources_Data_Action::$IMPORT_RECORD_NONE . ' LIMIT 1';

		$result = $adb->query($sql);
		if($result){
			$numberOfRecords = $adb->num_rows($result);
	
			if ($numberOfRecords > 0) {
				//Pas fini
				return;
			}
			
			$importRecordModel = $this->getRecordModel();
			if(!$importRecordModel){
				var_dump("Impossible de trouver le record");
				return;
			}
			
			$sql = 'SELECT COUNT(*) AS counter, status
				FROM ' . $tableName
				.' GROUP BY status
				HAVING COUNT(*) > 0
				ORDER BY status';
	
			$result = $adb->query($sql);
			
			$text = "Importation de [".vtranslate($moduleName, $moduleName)."] terminée depuis $tableName";
			$numberOfRecords = $adb->num_rows($result);
			
			$logFailedOrSkippedPreImport = false;
			
			if($numberOfRecords){
				while($row = $adb->fetch_row($result))
					if($row['counter']){
						$text .= "\r\n" . vtranslate('LBL_IMPORT_RECORD_'.$row['status'], 'Import') . ' : ' . $row['counter'];
						
						if($row['status'] == Import_Data_Action::$IMPORT_RECORD_FAILED || $row['status'] == Import_Data_Action::$IMPORT_RECORD_SKIPPED)
							$logFailedOrSkippedPreImport = true;
					}
			}
			else
				$text .= "\r\nAucune ligne.";
		}
		else {
			$text = "Importation de [".vtranslate($moduleName, $moduleName)."] terminée\r\nLa table $tableName n'existe plus.";
		}
		
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		
		$record = Vtiger_Record_Model::getCleanInstance('ModComments');
		$record->set('mode', 'create');
		
		$record->set('commentcontent', $text);
		$record->set('related_to', $importRecordModel->getId());
		
		$record->set('assigned_user_id', ASSIGNEDTO_ALL);
		$record->set('userid', $currentUserModel->getId());
		
		$record->save();
		
		
		if($logFailedOrSkippedPreImport){
			$this->logFailedData($tableName);
		}
		
		return $record;
	}
	/**
	 * Crée un commentaire pour l'import
	 * Archive les données qui dont l'import a échoués
	 */
	private function logFailedData($tableName){
		$adb = PearDatabase::getInstance();
		
		global $root_directory;
		$logFile = str_replace('\\', '/', $root_directory . 'logs/RSNImportSources_' . $tableName . '_' . date('Ymd_His') . '.log');
		
		$query = 'SELECT *
			'./*INTO OUTFILE \'' . $logFile . '\' ne fonctionne pas pour cause de droits d\'accès */'
			FROM ' . $tableName . '
			WHERE status IN ( '. RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED
					. ',  '. RSNImportSources_Data_Action::$IMPORT_RECORD_SKIPPED . ' )
			ORDER BY id';
			
		$result = $adb->query($query);
		if(!$result){
			var_dump($query);
			$adb->echoError();
		}
		
		$hFile = fopen($logFile, 'w');
		$nRow = 0;
		while($row = $adb->fetchByAssoc($result, $nRow, false)){
			if($nRow++ === 0)
				fputcsv($hFile, array_keys($row), ';');
			fputcsv($hFile, $row, ';');
		}
		fclose($hFile);
	}
	
	/**
	 * Method to process to the third step (the import step).
	 *  It check if the import must be scheduled. If not, it trigger the import.
	 */
	public function import() {
		$importModules = $this->getImportModules();
		if($this->request->get('is_scheduled') !== null && $this->request->get('is_scheduled') !== '')
			$this->checkImportIsScheduled();
		$isImportScheduled = $this->request->get('is_scheduled');

		foreach($importModules as $module) {
			$this->queueDataImport($module);

			if(!$isImportScheduled) {
				$this->triggerImport($module);
			}
			//ED150829
			if($this->skipNextScheduledImports)
				break;
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
		global $VTIGER_BULK_SAVE_MODE;
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
		
		/* ED150827 */
		$importController = self::getRecordModelByClassName($moduleName, $importInfos[0]['importsourceclass']);
		$viewer->assign('IMPORT_RECORD_MODEL', $importController);
		
		$viewer->assign('FOR_MODULE', $moduleName);
		$viewer->assign('MODULE', 'RSNImportSources');
		$viewer->assign('IMPORT_SOURCE', $importInfos[0]['importsourceclass']);
		$viewer->view('ImportHeader.tpl', 'RSNImportSources');
		$importEnded = true;

		foreach($importInfos as $importInfo) {

			$importDataController = new RSNImportSources_Data_Action($importInfo, $user);
			if($importInfo['status'] == RSNImportSources_Queue_Action::$IMPORT_STATUS_HALTED
			|| $importInfo['status'] == RSNImportSources_Queue_Action::$IMPORT_STATUS_VALIDATING
			|| $importInfo['status'] == RSNImportSources_Queue_Action::$IMPORT_STATUS_NONE) {
				$continueImport = true;
			} else {
				$continueImport = false;
			}
			
			$importStatusCount = $importDataController->getImportStatusCount();
			
			$totalRecords = $importStatusCount['TOTAL'];

			if($totalRecords > ($importStatusCount['IMPORTED'] + $importStatusCount['FAILED'])) {
				$importEnded = false;
				if($importInfo['status'] == Import_Queue_Action::$IMPORT_STATUS_SCHEDULED) {
					self::showScheduledStatus($importInfo, $importStatusCount);
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
		$viewer->assign('IMPORT_STATUS', $importInfo['status']);
		$viewer->assign('INVENTORY_MODULES', getInventoryModules());
		$viewer->assign('CONTINUE_IMPORT', $continueImport);
		$viewer->assign('IMPORT_SOURCE', $importInfo['importsourceclass']);
		$viewer->view('ImportStatus.tpl', 'RSNImportSources');
	}

	/**
	 * Method called by the showImportStatus method.
	 */
	public static function showResult($importInfo, $importStatusCount) {
		$viewer = new Vtiger_Viewer();
        self::prepareShowResult($importInfo, $importStatusCount, $viewer);

		$viewer->view('ImportResult.tpl', 'RSNImportSources');
	}
	
	/**
	 * Method called by the showImportStatus method.
	 */
	public static function prepareShowResult($importInfo, $importStatusCount, $viewer = false) {
		$moduleName = $importInfo['module'];
		$ownerId = $importInfo['user_id'];
		if(!$viewer)
			$viewer = new Vtiger_Viewer();
		
		$viewer->assign('SKIPPED_RECORDS',$skippedRecords);
		$viewer->assign('FOR_MODULE', $moduleName);
		$viewer->assign('MODULE', 'RSNImportSources');
		$viewer->assign('OWNER_ID', $ownerId);
		$viewer->assign('IMPORT_RESULT', $importStatusCount);
		$viewer->assign('INVENTORY_MODULES', getInventoryModules());
		$viewer->assign('MERGE_ENABLED', $importInfo['merge_type']);
	}

	/**
	 * Method called by the showImportStatus method.
	 */
	public static function showScheduledStatus($importInfo, $importStatusCount = false) {
		// TODO: $importInfo['module'] should be the current main module !!
		$moduleName = $importInfo['module'];
		$importId = $importInfo['id'];

		$viewer = new Vtiger_Viewer();
		
		if($importStatusCount){
			self::prepareShowResult($importInfo, $importStatusCount, $viewer);
			$viewer->assign('RESULT_DETAILS', true);
		}			
		
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
	function getProductId(&$productcode, &$isProduct = NULL, &$name = NULL) {
		$ini_productcode = $productcode;
		$data = Vtiger_Cache::get('ProductCode::Info', $productcode);
        if($data){
			$isProduct = $data['isProduct'];
			$productcode = $data['productcode'];
			return $data['productid'];
		}
		$db = PearDatabase::getInstance();
		if($isProduct !== TRUE){
			if($productcode){
				$searchKey = 'productcode';
				$searchValue = $productcode;
			} else {
				$searchKey = 'servicename';
				$searchValue = $name;
				if(!$searchValue){
					Vtiger_Cache::set('ProductCode::Info', $ini_productcode, array('productid'=>false, 'productcode'=>$productcode, 'isProduct'=>$isProduct));
					echo "\nProduit sans code ni nom !";
					return false;
				}
			}
			$query = 'SELECT serviceid, servicename AS label, productcode
				FROM vtiger_service s
				JOIN vtiger_crmentity e
					ON s.serviceid = e.crmid
				WHERE s.'.$searchKey.' = ?
				AND e.deleted = FALSE
				ORDER BY discontinued DESC
				LIMIT 1';
			$result = $db->pquery($query, array($searchValue));
			if(!$result)
				$db->echoError();
			elseif ($db->num_rows($result) === 1) {
				$row = $db->fetch_row($result, 0);
				$isProduct = false;
				$name = $row['label'];
				$productcode = $row['productcode'];
				Vtiger_Cache::set('ProductCode::Info', $ini_productcode, array('productid'=>$row['serviceid'], 'productcode'=>$productcode, 'isProduct'=>$isProduct));
				return $row['serviceid'];
			}
		}
		//produits
		if($isProduct !== FALSE){
			if($productcode){
				$searchKey = 'productcode';
				$searchValue = $productcode;
			} else {
				$searchKey = 'productname';
				$searchValue = $name;
				if(!$searchValue){
					Vtiger_Cache::set('ProductCode::Info', $ini_productcode, array('productid'=>false, 'productcode'=>$productcode, 'isProduct'=>$isProduct));
					echo "\nProduit sans code ni nom !";
					return false;
				}
			}
			$query = 'SELECT productid, productname AS label, productcode
				FROM vtiger_products p
				JOIN vtiger_crmentity e
					ON p.productid = e.crmid
				WHERE p.'.$searchKey.' = ?
				AND e.deleted = FALSE
				ORDER BY discontinued DESC
				LIMIT 1';
			$result = $db->pquery($query, array($searchValue));
			if(!$result)
				$db->echoError();
			elseif ($db->num_rows($result) === 1) {
				$row = $db->fetch_row($result, 0);
				$isProduct = true;
				$name = $row['label'];
				$productcode = $row['productcode'];
				Vtiger_Cache::set('ProductCode::Info', $ini_productcode, array('productid'=>$row['productid'], 'productcode'=>$productcode, 'isProduct'=>$isProduct));
				return $row['productid'];
			}
		}

		Vtiger_Cache::set('ProductCode::Info', $ini_productcode, array('productid'=>false, 'productcode'=>$productcode, 'isProduct'=>$isProduct));
		return null;
	}
	
	//TODO replace call with global function str_to_float defined in include/utils/CommonUtils.php
	static function str_to_float($str){
		return RSNImportSources_Utils_Helper::str_to_float($str);
	}
	
	/** ABSTRACT
	 * Method that returns a formatted date for mysql (Y-m-d).
	 * @param string $string : the string to format.
	 * @return string - formated date.
	 */
	function getMySQLDate($string) {
		if($string == '00/00/00')
			return '1999-12-31';
		$dateArray = preg_split('/[-\/]/', $string);
		return ($dateArray[2].length > 2 ? '' : '20').$dateArray[2] . '-' . $dateArray[1] . '-' . $dateArray[0];
	}
	
	/**
	 * Method that returns a DateTime object
	 * @param string $string : the string to format.
	 * @return string - formated date.
	 */
	function getDateTime($string) {
		return new DateTime($this->getMySQLDate($string));
	}
	
	/**
	 * Method that retrieve a contact id.
	 * @param string $firstname : the firstname of the contact.
	 * @param string $lastname : the lastname of the contact.
	 * @param string $email : the email of the contact.
	 * @return the id of the contact | null if the contact is not found.
	 */
	function getContactId($firstname, $lastname, $email) {
		
		if($this->checkPreImportInCache('Contacts', $firstname, $lastname, $email))
			return $this->checkPreImportInCache('Contacts', $firstname, $lastname, $email);
		
		$query = "SELECT crmid
			FROM vtiger_contactdetails
			JOIN vtiger_crmentity
				ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
			LEFT JOIN vtiger_contactemails
			    ON vtiger_contactemails.contactemailsid = vtiger_crmentity.crmid
			WHERE vtiger_crmentity.deleted = FALSE
			AND ((UPPER(firstname) = ?
				AND UPPER(lastname) = ?)
			    OR UPPER(lastname) IN (?,?)
			)
			AND (	LOWER(vtiger_contactdetails.email) = ?
				OR LOWER(vtiger_contactemails.email) = ?)
			ORDER BY crmid DESC
			LIMIT 1
		";
		$db = PearDatabase::getInstance();
		
		$fullName = strtoupper(trim(remove_accent($lastname) . ' ' . $firstname));
		if(!$firstname)
			$fullNameReverse = implode(' ', array_reverse(explode(' ', $fullName)));
		else
			$fullNameReverse = strtoupper(trim($firstname . ' ' . remove_accent($lastname)));
		
		if(!$fullNameReverse){
			echo_callstack();
			return false;
		}
		
		$result = $db->pquery($query, array(strtoupper($firstname), strtoupper(remove_accent($lastname))
						    , $fullName
						    , $fullNameReverse
						    , strtolower($email)
						    , strtolower($email)));

		//Recherche de l'email seul
		if(!$db->num_rows($result)){
			$query = "SELECT crmid
				FROM vtiger_contactdetails
				JOIN vtiger_crmentity
				    ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
				LEFT JOIN vtiger_contactemails
				    ON vtiger_contactemails.contactemailsid = vtiger_crmentity.crmid
				WHERE vtiger_crmentity.deleted = FALSE
				AND (	LOWER(vtiger_contactdetails.email) = ?
					OR LOWER(vtiger_contactemails.email) = ?)
				ORDER BY crmid DESC
				LIMIT 1
			";
			$result = $db->pquery($query, array(strtolower($email), strtolower($email)));
			
		}
		
		if($db->num_rows($result)){
			$id = $db->query_result($result, 0, 0);
			
			$this->setPreImportInCache($id, 'Contacts', $firstname, $lastname, $email);
		
			return $id;
		}

		return null;
	}
		/**
	 * Method that retrieve a contact id.
	 * @param string $ref4D : the reference in 4D
	 * @return the id of the contact | null if the contact is not found.
	 */
	function getContactIdFromRef4D($ref4D) {
		
		if($this->checkPreImportInCache('Contacts', '4d', $ref4D))
			return $this->checkPreImportInCache('Contacts', '4d', $ref4D);
		
		$query = "SELECT vtiger_crmentity.crmid
			FROM vtiger_contactdetails
			JOIN vtiger_crmentity
				ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
			WHERE vtiger_crmentity.deleted = 0
			AND vtiger_contactdetails.ref4d = ?
			ORDER BY crmid DESC
			LIMIT 1
		";
		$db = PearDatabase::getInstance();
				
		$result = $db->pquery($query, array($ref4D));

		if($db->num_rows($result)){
			$id = $db->query_result($result, 0, 0);
			
			$this->setPreImportInCache($id, 'Contacts', '4d', $ref4D);
		
			return $id;
		}

		return null;
	}
	
	

	/**
	 * Gestion du cache
	 * Teste si les paramètres de la fonction ont déjà fait l'objet d'une recherche
	 * @param $moduleName : module de la recherche
	 * @param suivants : autant de paramètres qu'on veut. On construit un identifiant unique à partir de l'ensemble des valeurs
	 */
	var $preImportChecker_cache = array();
	
	/**
	 * Teste si les paramètres de la fonction ont déjà fait l'objet d'une recherche
	 * @param $moduleName : module de la recherche
	 * @param suivants : autant de paramètres qu'on veut. On construit un identifiant unique à partir de l'ensemble des valeurs
	 */
	public function checkPreImportInCache($moduleName, $arg1, $arg2 = false){
		$parameters = func_get_args();
		$cacheKey = implode( "\tC:", $parameters);
		//var_dump('checkPreImportInCache', $cacheKey);
		if(array_key_exists( $cacheKey, $this->preImportChecker_cache)){
			return $this->preImportChecker_cache[$cacheKey];
		}
		return false;		
	}
	
	/**
	 * Complète le cache
	 * @param $value : valeur à stocker
	 * @param $moduleName : module de la recherche
	 * @param suivants : autant de paramètres qu'on veut. On construit un identifiant unique à partir de l'ensemble des valeurs
	 */
	public function setPreImportInCache($value, $moduleName, $arg1, $arg2 = false){
		$parameters = func_get_args();
		$value = array_shift( $parameters);
		$cacheKey = implode( "\tC:", $parameters);
		//var_dump('setPreImportInCache', $cacheKey);
		if(!$value)
			$value = true;
		
		if(count($this->preImportChecker_cache) > IMPORTCHECKER_CACHE_MAX)
			array_splice($this->preImportChecker_cache, 0, IMPORTCHECKER_CACHE_MAX / 2);
		$this->preImportChecker_cache[$cacheKey] = $value;
		return true;
	}

	
	public function updateStatus($status, $importId = false) {
		if(!$importId)
			$importId = $this->scheduledId;
		if($importId){
			//var_dump('updateStatus',$this->scheduledId, $status);
			RSNImportSources_Queue_Action::updateStatus($importId, $status);
		}
		else{
			//echo_callstack();
			//var_dump('updateStatus NO scheduledId ', $status);
		}
	}
	
	//ED150827
	public function getRecordModel(){
		if($this->recordModel)
			return $this->recordModel;
		
		if($this->request){
			$moduleName = $this->request->get('for_module');
			$className = $this->request->get('ImportSource');
		} else {
			$moduleName = '';
			$className = preg_replace('/^RSNImportSources_(.+)_View$/', '$1', get_class($this));
		}
		if($this->checkPreImportInCache($moduleName, 'getRecordModel', $className))
			return $this->checkPreImportInCache($moduleName, 'getRecordModel', $className);
		
		$recordModel = self::getRecordModelByClassName($moduleName, $className);
		if($recordModel){
			$this->recordModel = $recordModel;
			$this->setPreImportInCache($recordModel, $moduleName, 'getRecordModel', $className);
		}
		return $recordModel;
	}
	
	public static function getRecordModelByClassName($moduleName, $className){
		global $adb;
		$query = 'SELECT vtiger_crmentity.crmid
			FROM vtiger_crmentity
			JOIN vtiger_rsnimportsources
				ON vtiger_crmentity.crmid = vtiger_rsnimportsources.rsnimportsourcesid
			WHERE vtiger_crmentity.deleted = 0
			AND vtiger_rsnimportsources.`class` = ?
			AND vtiger_rsnimportsources.disabled = 0
			'/*AND (vtiger_rsnimportsources.`modules` LIKE CONCAT(\'%\', ?, \'%\') pblm de traduction, ou de changement de traduction
				OR vtiger_rsnimportsources.`modules` LIKE CONCAT(\'%\', ?, \'%\'))
			*/.' LIMIT 1';
		$result = $adb->pquery($query, array($className/*, vtranslate($moduleName, $moduleName), $moduleName*/));
		if(!$result){
			$adb->echoError($query);
			return false;
		}
		$id = $adb->query_result($result, 0);
		/*var_dump($query, array($className, vtranslate($moduleName, $moduleName), $moduleName));
		var_dump($moduleName, $id);
		var_dump($query, array($className, $moduleName));*/
		if(!$id)
			return false;
		$recordModel = Vtiger_Record_Model::getInstanceById($id, 'RSNImportSources');
		return $recordModel;
	}
	
	//ED150827
	public function updateLastImportField(){
		$fileName = $this->request->get('import_file_name');
		if($fileName){
			$recordModel = $this->getRecordModel();
			if(!$recordModel){
				echo 'updateLastImportField : $recordModel non defini';
				return false;
			}
			$recordModel->set('mode', 'edit');
			$recordModel->set('lastimport', $fileName . ' (' . date('d/m/Y H:i:s') . ')');
			$recordModel->save();
			//echo 'updateLastImportField : lastimport = '. $fileName . ' (' . date('d/m/Y') . ')';
		}
	}

	/**
	 * Teste si un import est disponible pour un pre-import automatique. La contrainte est qu'aucun utilisateur n'est un import en cours sur une des tables
	 * If there is no probleme, it clear the informations of the last import.
	 * @param Vtiger_Request $request : the curent request.
	 * @param $clearUserImportInfo : remove old tables. Default is false.
	 * @return true is no data is waiting for import
	 * 
	 * TODO : il faudrait que les imports en cours apparaissent si le for_module est lié à n'importe quel RSNImportSources d'après vtiger_rsnimportsources.modules, càd si l'import apparait dans la liste de sélection.
	 * Pour l'instant, on ne se base que sur la queue et une table d'import de ce module.
	 * Or, un import disponible depuis Contacts peut ne faire d'importe que dans une autre table, $importController->getImportModules()
	 * 	il faudrait pouvoir utiliser $importController->getLockModules()
	 */
	function isAutoPreImportAvailable($clearUserImportInfo = false) {
		$modules = $this->getImportModules();
		foreach($modules as $moduleName) {
			if(RSNImportSources_Utils_Helper::isModuleImportBlocked($moduleName)) {
				return false;
			}
		}
		if($clearUserImportInfo){
			$user = Users_Record_Model::getCurrentUserModel();
			foreach($modules as $moduleName) {
				RSNImportSources_Utils_Helper::clearUserImportInfo($user, $moduleName);
			}
		}
		return true;

	}

	/**
	 * Method called after the file is processed.
	 *  This method must be overload in the child class.
	 */
	function postPreImportData() {
		return true;
	}
	
	/** Prépare les données pour un pré-import automatique
	 *
	 */
	function prepareAutoPreImportData(){
		$this->request->set('auto_preimport', true);
		if(!$this->request->get('for_module')){
			$modules = $this->getImportModules();
			$this->request->set('for_module', $modules[0]);
		}
		return true;
	}
	/** Méthode appelée après un pré-import automatique
	 *
	 */
	function postAutoPreImportData(){
		$this->request->set('auto_preimport', 'done');
	}
	
	

	/* coupon d'après code affaire d'un document de type Coupon
	 */
	protected function getCoupon($codeAffaire){
		if(!$codeAffaire)
			return false;
		$couponId = $this->checkPreImportInCache("Coupon", 'codeAffaire', $codeAffaire);
		if($couponId === 'false')
			return false;
		if(is_numeric($couponId))
			return Vtiger_Record_Model::getInstanceById($couponId, 'Documents');;
		
		$query = "SELECT vtiger_crmentity.crmid
			FROM vtiger_notes
			JOIN vtiger_notescf
				ON vtiger_notescf.notesid = vtiger_notes.notesid
			JOIN vtiger_crmentity
				ON vtiger_notes.notesid = vtiger_crmentity.crmid
			WHERE codeaffaire = ?
			AND folderid  =?
			AND vtiger_crmentity.deleted = 0
			LIMIT 1
		";
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, array($codeAffaire, COUPON_FOLDERID));
		if(!$result)
			$db->echoError();
		if($db->num_rows($result)){
			$row = $db->fetch_row($result, 0);
			$coupon = Vtiger_Record_Model::getInstanceById($row['crmid'], 'Documents');
		}
		else
			$coupon = false;
		$this->setPreImportInCache($coupon ? $coupon->getId() : 'false', "Coupon", 'codeAffaire', $codeAffaire);
		return $coupon;
	}
	
	
	/* campagne d'après code affaire / Coupon
	 */
	protected function getCampaign($codeAffaire, $coupon){
		
		$campaign = $this->checkPreImportInCache("Campaigns", 'Coupon', $coupon ? $coupon->getId() : '' , 'codeAffaire', $codeAffaire);
		if($campaign)
			return $campaign === 'false' ? null : $campaign;
		
		if(!is_object($coupon)){
			
			$codeAffaire=$codeAffaire;
			if(!$codeAffaire){
				$this->setPreImportInCache('false', "Campaigns", 'Coupon', '' , 'codeAffaire', '');
				return false;
			}
			
			$query = "SELECT vtiger_crmentity.crmid
				FROM vtiger_campaignscf
				JOIN vtiger_crmentity
				    ON vtiger_campaignscf.campaignid = vtiger_crmentity.crmid
				WHERE codeaffaire = ?
				AND vtiger_crmentity.deleted = 0
				LIMIT 1
			";
			$db = PearDatabase::getInstance();
			$result = $db->pquery($query, array($codeAffaire));
			if(!$result)
				$db->echoError();
			if($db->num_rows($result)){
				$row = $db->fetch_row($result, 0);
				$campaign = Vtiger_Record_Model::getInstanceById($row['crmid'], 'Campaigns');
				$this->setPreImportInCache($campaign, "Campaigns", 'Coupon', '' , 'codeAffaire', $codeAffaire);
				return $campaign;
			}
			return;
		}
		//Campagnes liées au coupon
		$campaigns = $coupon->getRelatedCampaigns();
		
		//Pas de campagne, on cherche sans le coupon
		if(count($campaigns) === 0)
			return $this->getCampaign($srcRow, null);
		
		//Cherche celui qui aurait le même ode affaire
		foreach($campaigns as $campaign){
			if($codeAffaire == $campaign->get('affaire_code')){
				$this->setPreImportInCache($campaign, "Campagne", 'Coupon', $coupon->getId() , 'codeAffaire', $codeAffaire);
				return $campaign;
			}
		}
		
		//si il n'y en a qu'un, on prend celui-ci
		if(count($campaigns) === 1)
			foreach($campaigns as $campaign){		
				$this->setPreImportInCache($campaign, "Campagne", 'Coupon', $coupon->getId() , 'codeAffaire', $codeAffaire);
				return $campaign;
			}
			
		var_dump('Plusieurs campagnes correspondent au coupon', "Campaigns".', Coupon',$coupon->getId() .", 'codeAffaire' = ". $codeAffaire);
		$this->setPreImportInCache('false', "Campaigns", 'Coupon', $coupon ? $coupon->getId() : '' , 'codeAffaire', $codeAffaire);
	}
	
	/**
	 * Exécute des requêtes longues d'identification des contacts
	 * Doit être déclenché par le cron
	 * Seulement au premier appel du cron on ne devrait exécuter cette fonction.
	 * On peut réactiver la recherche sur certaines lignes en mettant à NULL _contactid_status
	 *
	 * Utilisé pour préparer les données de validation des pré-imports de Contacts (import Pétitions, Donateurs Web, ...)
	 */
	function identifyContacts() {
		
		//Teste si il y a des lignes à analyser
		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($this->user, 'Contacts');
		$sql = 'SELECT 1 FROM ' . $tableName . '
			WHERE status = '. RSNImportSources_Data_Action::$IMPORT_RECORD_NONE . '
			AND _contactid_status IS NULL
			LIMIT 1
		';
		$result = $adb->query($sql);
		$numberOfRecords = $adb->num_rows($result);
		if ($numberOfRecords === 0) {
			return;
		}
		if(true){//debug
			// Pré-identifie les contacts
			
			$fields = array_flip($this->getContactsFieldsMapping());//Remplace les clés par les valeurs, et les valeurs par les clés
			unset($fields['']);
			
			RSNImportSources_Utils_Helper::setPreImportDataContactIdByFields(
				$this->user,
				'Contacts',
				'_contactid',
				$fields,
				'_contactid_status',
				'IF(crmids LIKE "%,%,%", '.RSNImportSources_Import_View::$RECORDID_STATUS_MULTI.','.RSNImportSources_Import_View::$RECORDID_STATUS_SINGLE.')',
				'_contactid_source',
				'Tout pareil'
			);
	
			/* pas très intéressant et aussi long que le précédent */
			//$partialFields = $fields;
			//unset($partialFields['mailingstreet']);
			//unset($partialFields['mailingstreet2']);
			//RSNImportSources_Utils_Helper::setPreImportDataContactIdByFields(
			//	$this->user,
			//	'Contacts',
			//	'_contactid',
			//	$partialFields,
			//	'_contactid_status',
			//	'Tout sauf adresse1 et adresse2'
			//);
			
			$partialFields = array('email' => $fields['email'], 'lastname' => $fields['lastname'], 'firstname' => $fields['firstname'], 'mailingzip' => $fields['mailingzip']);
			RSNImportSources_Utils_Helper::setPreImportDataContactIdByFields(
				$this->user,
				'Contacts',
				'_contactid',
				$partialFields,
				'_contactid_status',
				'IF(crmids LIKE "%,%,%", '.RSNImportSources_Import_View::$RECORDID_STATUS_MULTI.','.RSNImportSources_Import_View::$RECORDID_STATUS_CHECK.')',
				'_contactid_source',
				'Email, nom, prénom et code postal'
			);
			
			unset($partialFields['mailingzip']);
			RSNImportSources_Utils_Helper::setPreImportDataContactIdByFields(
				$this->user,
				'Contacts',
				'_contactid',
				$partialFields,
				'_contactid_status',
				'IF(crmids LIKE "%,%,%", '.RSNImportSources_Import_View::$RECORDID_STATUS_MULTI.','.RSNImportSources_Import_View::$RECORDID_STATUS_CHECK.')',
				'_contactid_source',
				'Email, nom et prénom'
			);
			
			$partialFields = array('lastname' => $fields['lastname'], 'firstname' => $fields['firstname'], 'mailingzip' => $fields['mailingzip']);
			RSNImportSources_Utils_Helper::setPreImportDataContactIdByFields(
				$this->user,
				'Contacts',
				'_contactid',
				$partialFields,
				'_contactid_status',
				'IF(crmids LIKE "%,%,%", '.RSNImportSources_Import_View::$RECORDID_STATUS_MULTI.','.RSNImportSources_Import_View::$RECORDID_STATUS_CHECK.')',
				'_contactid_source',
				'Nom, prénom et code postal'
			);
			
			$partialFields = array('email' => $fields['email']);
			RSNImportSources_Utils_Helper::setPreImportDataContactIdByFields(
				$this->user,
				'Contacts',
				'_contactid',
				$partialFields,
				'_contactid_status',
				'IF(crmids LIKE "%,%,%", '.RSNImportSources_Import_View::$RECORDID_STATUS_MULTI.','.RSNImportSources_Import_View::$RECORDID_STATUS_CHECK.')',
				'_contactid_source',
				'Email seul'
			);
		}
		//Marque tous les autres enregistrements
		$sql = 'UPDATE ' . $tableName . '
			SET _contactid_status = '. RSNImportSources_Import_View::$RECORDID_STATUS_NONE . '
			WHERE status = '. RSNImportSources_Data_Action::$IMPORT_RECORD_NONE . '
			AND _contactid_status IS NULL
		';
		$result = $adb->query($sql);
		
		return true;
	}
	
	
	
	//Répertorie toutes les valeurs possibles et les ajoute à la picklist
	function addAllPicklistValues($moduleName, $fieldName, $pickListName){
		
		$db = PearDatabase::getInstance();
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, $moduleName);
		
		$query = 'SELECT DISTINCT '.$fieldName.'
			FROM '.$tableName.'
			WHERE IFNULL('.$fieldName.', "") != ""
			ORDER BY '.$fieldName.'
		';
		$result = $db->query($query);
		if(!$result){
			echo '<br><br><br><br>';
			$db->echoError($query);
			echo("<pre>$query</pre>");
			die();
		}
		while($row = $db->fetch_row($result, false)){
			RSNImportSources_Utils_Helper::checkPickListValue($moduleName, $pickListName, $pickListName, $row[$fieldName]);
		}
	}
}

?>