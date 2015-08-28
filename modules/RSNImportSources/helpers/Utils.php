<?php

class RSNImportSources_Utils_Helper extends  Import_Utils_Helper {

	static $AUTO_MERGE_NONE = 0;
	static $AUTO_MERGE_IGNORE = 1;
	static $AUTO_MERGE_OVERWRITE = 2;
	static $AUTO_MERGE_MERGEFIELDS = 3;

	static $supportedFileEncoding = array('UTF-8'=>'UTF-8', 'ISO-8859-1'=>'ISO-8859-1', 'macintosh'=> 'macintosh');
	static $supportedDelimiters = array(','=>'LBL_COMMA', ';'=>'LBL_SEMICOLON', '	'=>'LBL_TAB');
	static $supportedFileExtensions = array('csv','xml','json');
	static $supportedDBTypes = array('postgresql','mysql','mysqli');

	/**
	 * Method to get the default supported file extentions.
	 * @return array - the file extensions.
	 */
	public function getSupportedFileExtensions() {
		return self::$supportedFileExtensions;
	}

	/**
	 * Method to get the default supported db types.
	 * @return array - the db types.
	 */
	public function getSupportedDBTypes() {
		return self::$supportedDBTypes;
	}

	/**
	 * Method to get the default suported file encodings.
	 * @return array - the file encodings.
	 */
	public function getSupportedFileEncoding() {
		return self::$supportedFileEncoding;
	}

	/**
	 * Method to get the default suported file delimiters.
	 * @return array - the file delimiters.
	 */
	public function getSupportedDelimiters() {
		return self::$supportedDelimiters;
	}

	/**
	 * Method to get information about file readers in the config model file for a specific file type.
	 * @param string $type : the file type.
	 * @return array - the filereader informations.
	 */
	public static function getFileReaderInfo($type) {
		$configReader = new RSNImportSources_Config_Model();
		$importTypeConfig = $configReader->get('importTypes');
		if(isset($importTypeConfig[$type])) {
			return $importTypeConfig[$type];
		}
		return null;
	}

	/**
	 * Method to get the needed file reader according to the request parameters.
	 * @param Vtiger_Request $request: the curent request.
	 * @param $user : the current user.
	 * @return RSNImportSources_FileReader_Reader - the filereader.
	 */
	public static function getFileReader($request, $user) {
		$fileReaderInfo = self::getFileReaderInfo($request->get('file_type'));
		if(!empty($fileReaderInfo)) {
			require_once $fileReaderInfo['classpath'];
			$fileReader = new $fileReaderInfo['reader'] ($request, $user);
		} else {
			$fileReader = null;
		}
		return $fileReader;
	}

	/**
	 * Method to validate uploaded file and to move it to the temporary location.
	 * @param Vtiger_Request $request: the curent request.
	 * @return boolean - true if success.
	 */
	static function validateFileUpload($request) {
		$current_user = Users_Record_Model::getCurrentUserModel();

		$uploadMaxSize = self::getMaxUploadSize();
		$importDirectory = self::getImportDirectory();
		$temporaryFileName = self::getImportFilePath($current_user, $request->get("for_module"));

		if($_FILES['import_file']['error']) {
			$request->set('error_message', self::fileUploadErrorMessage($_FILES['import_file']['error']));
			return false;
		}
		
		if(!is_uploaded_file($_FILES['import_file']['tmp_name'])) {
			$request->set('error_message', vtranslate('LBL_FILE_UPLOAD_FAILED', 'Import'));
			return false;
		}
		if ($_FILES['import_file']['size'] > $uploadMaxSize) {
			$request->set('error_message', vtranslate('LBL_IMPORT_ERROR_LARGE_FILE', 'Import').
			$uploadMaxSize.' '.vtranslate('LBL_IMPORT_CHANGE_UPLOAD_SIZE', 'Import'));
			return false;
		}
		if(!is_writable($importDirectory)) {
			$request->set('error_message', vtranslate('LBL_IMPORT_DIRECTORY_NOT_WRITABLE', 'Import'));
			return false;
		}

		//ED150827
		$request->set('import_file_name', $_FILES['import_file']['name']);

		$fileCopied = move_uploaded_file($_FILES['import_file']['tmp_name'], $temporaryFileName);
		if(!$fileCopied) {
			$request->set('error_message', vtranslate('LBL_IMPORT_FILE_COPY_FAILED', 'Import'));
			return false;
		}

		return true;
	}

	/**
	 * Method to create the temporary pre-import table for a specific user and a specific module.
	 * @param array $fields : the needed field.
	 * @param $user : the current user.
	 * @param $module : the current module.
	 * @return boolean - true if success.
	 */
	static function createTable($fields, $user, $module) {
		$moduleModel = Vtiger_Module_Model::getInstance($module);
		$db = PearDatabase::getInstance();
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($user, $moduleModel->get('name'));
        $moduleFields = $moduleModel->getFields();
        $columnsListQuery = 'id INT PRIMARY KEY AUTO_INCREMENT, status INT DEFAULT 0, recordid INT';
		$fieldTypes = RSNImportSources_Utils_Helper::getModuleFieldDBColumnType($moduleModel);
		foreach($fields as $fieldName) {
            $fieldObject = $moduleFields[$fieldName];
            if (is_object($fieldObject)) {
            	$columnsListQuery .= RSNImportSources_Utils_Helper::getDBColumnType($fieldObject, $fieldTypes);
            } else {
            	$columnsListQuery .= ','.$fieldName.' varchar(250)';
            }
		}
		$createTableQuery = 'CREATE TABLE '. $tableName . ' ('.$columnsListQuery.') ENGINE=MyISAM ';
		$db->query($createTableQuery);
		return true;
	}

	/**
	 * Method to get the type of fields in the table.
	 * @param Vtiger_Module_Model $moduleModel
	 * @return array - the field types.
	 */
	static function getModuleFieldDBColumnType($moduleModel) {
        $db = PearDatabase::getInstance();
        $result = $db->pquery('SELECT tablename FROM vtiger_field WHERE tabid=? GROUP BY tablename', array($moduleModel->getId()));
        $tables = array();
        if ($result && $db->num_rows($result) > 0) {
            while ($row = $db->fetch_array($result)) {
                $tables[] = $row['tablename'];
            }
        }
        $fieldTypes = array();
        foreach ($tables as $table) {
            $result = $db->pquery("DESC $table", array());
            if ($result && $db->num_rows($result) > 0) {
                while ($row = $db->fetch_array($result)) {
                    $fieldTypes[$row['field']] = $row['type'];
                }
            }
        }
        return $fieldTypes;
    }

    /**
	 * Method to get the type of a column in the table.
	 * @param  $fieldObject
	 * @param  $fieldTypes
	 * @return string - the field type.
	 */
    static function getDBColumnType($fieldObject,$fieldTypes) {
        $columnsListQuery = '';
        $fieldName = $fieldObject->getName();
        $dataType = $fieldObject->getFieldDataType();
        if($dataType == 'reference' || $dataType == 'owner' || $dataType == 'currencyList'){
            $columnsListQuery .= ','.$fieldName.' varchar(250)';
        } else {
            $columnsListQuery .= ','.$fieldName.' '.$fieldTypes[$fieldObject->get('column')];
        }
        
        return $columnsListQuery;
    }

    /**
	 * Method to clear the last import informations for thecurent user.
	 * @param  $user : the current user.
	 * @param  string $moduleName : the module name.
	 */
    public static function clearUserImportInfo($user, $moduleName) {
		$adb = PearDatabase::getInstance();
		$tableName = self::getDbTableName($user, $moduleName);

		$adb->query('DROP TABLE IF EXISTS '.$tableName);
		RSNImportSources_Lock_Action::unLock($user);
		RSNImportSources_Queue_Action::removeForUserAndModule($user, $moduleName);
	}

	/**
	 * Method to import cource list for a specific module.
	 * @param  string $moduleName : the module name.
	 * @return array - the source list.
	 */
	public static function getSourceList($moduleName) {
		$db = PearDatabase::getInstance();
		$return_values = array();

		$query = 	'SELECT vtiger_crmentity.crmid/*, tab.name*/, ris.class, ris.title
				, ris.description, ris.lastimport
			FROM vtiger_rsnimportsources ris
			JOIN vtiger_crmentity ON ris.rsnimportsourcesid = vtiger_crmentity.crmid
			/*JOIN vtiger_tab tab ON ris.tabid = tab.tabid*/
			WHERE ris.disabled = FALSE
			AND vtiger_crmentity.deleted = FALSE';

		$params = array();
		//if ($moduleName) {
			$query .= ' AND (ris.modules LIKE CONCAT(\'%\', ?, \'%\')';
			$query .= ' OR ris.modules LIKE CONCAT(\'%\', ?, \'%\'))';
			$params[] = $moduleName;
			$params[] = vtranslate($moduleName);
		//}
		$query .= ' ORDER BY sortorderid';
		$result = $db->pquery($query, $params);

		$noOfRecords = $db->num_rows($result);
		for($i=0; $i<$noOfRecords; $i++) {
			$class = self::getClassFromName($db->query_result($result, $i, 'class'));
			array_push($return_values, array(
				id 		=> $db->query_result($result, $i, 'crmid'),
				module 		=> $moduleName, //$db->query_result($result, $i, 'name'),
				classname 	=> $db->query_result($result, $i, 'class'),
				sourcename 	=> $db->query_result($result, $i, 'title'),//$class::getSource(),
				description 	=> $db->query_result($result, $i, 'description'),
				lastimport 	=> $db->query_result($result, $i, 'lastimport'),
				sourcetype 	=> $class::getSourceType()
			));
		}
        
        return $return_values;
	}

	/**
	 * Method to get the class using its name.
	 * @param  string $className : the class name.
	 * @return the class.
	 */
	public static function getClassFromName($className) {
		return Vtiger_Loader::getComponentClassName('View', $className, 'RSNImportSources');;
	}

	/**
	 * Method to get the import source class name.
	 * @param  int $importSourcesId : the import source id.
	 * @return string: the import class name.
	 */
	public static function getImportSourceClassName($importSourcesId) {
		$db = PearDatabase::getInstance();
		$return_values = array();

		$query = 	'SELECT class FROM vtiger_rsnimportsources ris' .
					' JOIN vtiger_tab tab on ris.tabid = tab.tabid' .
					' WHERE ris.disabled = FALSE AND ris.importsourcesid = ?' .
					' LIMIT 1';

		$result = $db->pquery($query, array($importSourcesId));


		if ($db->num_rows($result) == 1) {
			return$db->query_result($result, $i, 'class');
		}
		
        
        return '';
	}

	/**
	 * Method to get the import source class.
	 * @param  int $importSourcesId : the import source id.
	 * @return string: the import class.
	 */
	public static function getImportSourceClass($importSourcesId) {
		return self::getClassFromName(self::getImportSourceClassName($importSourcesId));
	}

	/**
	 * Method to display error when import table is blocked.
	 * @param  string $moduleName : the name of the concerned module.
	 * @param  $user : the name of the concerned user.
	 * @param  string $importSource : the import source class name.
	 */
	public static function showImportTableBlockedError($moduleName, $user, $importSource) {
		$errorMessage = vtranslate('ERR_UNIMPORTED_RECORDS_EXIST', 'RSNImportSources');
		$cancelUrl = 'index.php?module=RSNImportSources&for_module=' . $moduleName . '&view=Index&mode=clearCorruptedData';
		if ($importSource) {
			$cancelUrl .= '&ImportSource=' . $importSource;
		}

		$customActions = array('LBL_CLEAR_DATA' => "location.href='" . $cancelUrl . "'");

		self::showErrorPage($errorMessage, '', $customActions);
	}

	/**
	 * Method to get the number of record preimported for a specific module and a specific user.
	 * @param  $user : the name of the concerned user.
	 * @param  string $moduleName : the name of the concerned module.
	 * @return int - the number of record.
	 */
	public static function getNumberOfRecords($user, $moduleName) {
		$tableName = self::getDbTableName($user, $moduleName);
		$db = PearDatabase::getInstance();

		$query = 	'SELECT COUNT(*) FROM ' . $tableName .
					' WHERE status = 0';

		$result = $db->pquery($query, array());

		return $db->query_result($result, 0, 0);
	}

	/**
	 * Method to get an instance of the import controller using its class name.
	 * @param  string $className : the class name.
	 * @return RSNImportSources_Import_View - the instance of the import class.
	 */
	public static function getImportController($className) {
		if ($className) {
			$importClass = self::getClassFromName($className);
			$user = Users_Record_Model::getCurrentUserModel();
			$importController = new $importClass($request, $user);

			return $importController;
		}

		return null;
	}

	//échappe les caractères spéciaux avant écriture du csv
	public static function escapeValuesForCSV($row){
		static $chars = ";\r\n\"";
		foreach ($row as $index=>$value) {
			if(strtok($value,$chars) !== $value){
				$row[$index] = preg_replace('/([\r\n])/', " ", preg_replace('/([;"])/', '\\$1', $value));
			}
		}
		return $row;
	}
}

class ImportPerformance {
	var $startTime;
	var $maxItems = 0;
	var $prevPercent = 0;
	var $tickCounter = 0;
	
	public function __construct($maxItems = 0){
		$this->maxItems = $maxItems;
		$this->startTime = new DateTime();
	}
	
	public function tick(){
		$perfPC = (int)($this->tickCounter/$this->maxItems * 100);
		if($this->prevPercent != $perfPC){
			$perfNow = new DateTime();
			$perfElapsed = date_diff($this->startTime, $perfNow)->format('%H:%i:%S');
			echo "\n import $this->tickCounter/$this->maxItems "
				."( $perfPC %, $perfElapsed, "
				. self::getMemoryUsage()
				." ) ";
			$this->prevPercent = $perfPC;
		}
		$this->tickCounter++;
	}
	public function terminate(){
		$perfPC = (int)($this->tickCounter/$this->maxItems * 100);
		$perfNow = new DateTime();
		$perfElapsed = date_diff($this->startTime, $perfNow)->format('%H:%i:%S');
		echo "\n IMPORT TERMINATED $this->tickCounter/$this->maxItems "
			."( $perfPC %, $perfElapsed, "
			. self::getMemoryUsage()
			." ) ";
	}
	static function getMemoryUsage(){
		$size = memory_get_usage();
		$unit=array('b','kb','mb','gb','tb','pb');
		return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
	}
}
