<?php

class RSNImport_Utils_Helper extends  Import_Utils_Helper {

	static $AUTO_MERGE_NONE = 0;
	static $AUTO_MERGE_IGNORE = 1;
	static $AUTO_MERGE_OVERWRITE = 2;
	static $AUTO_MERGE_MERGEFIELDS = 3;

	static $supportedFileEncoding = array('UTF-8'=>'UTF-8', 'ISO-8859-1'=>'ISO-8859-1');
	static $supportedDelimiters = array(','=>'LBL_COMMA', ';'=>'LBL_SEMICOLON', '	'=>'LBL_TAB');
	static $supportedFileExtensions = array('csv','xml','json');

	public static function getFileReaderInfo($type) {
		$configReader = new RSNImport_Config_Model();
		$importTypeConfig = $configReader->get('importTypes');
		if(isset($importTypeConfig[$type])) {
			return $importTypeConfig[$type];
		}
		return null;
	}

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

		$fileCopied = move_uploaded_file($_FILES['import_file']['tmp_name'], $temporaryFileName);
		if(!$fileCopied) {
			$request->set('error_message', vtranslate('LBL_IMPORT_FILE_COPY_FAILED', 'Import'));
			return false;
		}
		/*$fileReader = Import_Utils_Helper::getFileReader($request, $current_user); //tmp

		if($fileReader == null) {
			$request->set('error_message', vtranslate('LBL_INVALID_FILE', 'Import'));
			return false;
		}*/

		return true;
	}

	static function createTable($fields, $user, $module) {
		$moduleModel = Vtiger_Module_Model::getInstance($module);
		$db = PearDatabase::getInstance();
		$tableName = RSNImport_Utils_Helper::getDbTableName($user, $moduleModel->get('name'));
        $moduleFields = $moduleModel->getFields();
        $columnsListQuery = 'id INT PRIMARY KEY AUTO_INCREMENT, status INT DEFAULT 0, recordid INT';
		$fieldTypes = RSNImport_Utils_Helper::getModuleFieldDBColumnType($moduleModel);
		foreach($fields as $fieldName) {
            $fieldObject = $moduleFields[$fieldName];
            if (is_object($fieldObject)) {
            	$columnsListQuery .= RSNImport_Utils_Helper::getDBColumnType($fieldObject, $fieldTypes);
            } else {
            	$columnsListQuery .= ','.$fieldName.' varchar(250)';
            }
		}
		$createTableQuery = 'CREATE TABLE '. $tableName . ' ('.$columnsListQuery.') ENGINE=MyISAM ';
		$db->query($createTableQuery);
		return true;
	}

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

    static function getDBColumnType($fieldObject,$fieldTypes){
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

    public static function clearUserImportInfo($user, $moduleName) {
		$adb = PearDatabase::getInstance();
		$tableName = self::getDbTableName($user, $moduleName);

		$adb->query('DROP TABLE IF EXISTS '.$tableName);
		RSNImport_Lock_Action::unLock($user);
		RSNImport_Queue_Action::removeForUser($user);
	}

	public static function getSourceList($moduleName = false) {
		$db = PearDatabase::getInstance();
		$return_values = array();

		$query = 	'SELECT ris.importsourcesid, tab.name, ris.class FROM vtiger_rsnimportsources ris' .
					' JOIN vtiger_tab tab on ris.tabid = tab.tabid' .
					' WHERE ris.disabled = FALSE';

		if ($moduleName) {
			$query .= ' AND tab.name = ?';
			$result = $db->pquery($query, array($moduleName));
		} else {
			$result = $db->pquery($query);
		}

		$noOfRecords = $db->num_rows($result);
		for($i=0; $i<$noOfRecords; $i++) {
			$class = self::getClassFromName($db->query_result($result, $i, 'class'));
			array_push($return_values, array(
				id 			=> $db->query_result($result, $i, 'importsourcesid'),
				module 		=> $db->query_result($result, $i, 'name'),
				classname 	=> $db->query_result($result, $i, 'class'),
				sourcename => $class::getSource(),
				sourcetype => $class::getSourceType()
				));
		}
        
        return $return_values;
	}

	public static function getClassFromName($className) {
		return Vtiger_Loader::getComponentClassName('View', $className, 'RSNImport');;
	}

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

	public static function getImportSourceClass($importSourcesId) {
		return self::getClassFromName(self::getImportSourceClassName($importSourcesId));
	}

	public static function showImportTableBlockedError($moduleName, $user, $importSource) {
		$errorMessage = vtranslate('ERR_UNIMPORTED_RECORDS_EXIST', 'RSNImport');
		$cancelUrl = 'index.php?module=RSNImport&for_module=' . $moduleName . '&view=Index&mode=clearCorruptedData';
		if ($importSource) {
			$cancelUrl .= '&ImportSource=' . $importSource;
		}

		$customActions = array('LBL_CLEAR_DATA' => "location.href='" . $cancelUrl . "'");

		self::showErrorPage($errorMessage, '', $customActions);
	}

	public static function getNumberOfRecords($user, $moduleName) {
		$tableName = self::getDbTableName($user, $moduleName);
		$db = PearDatabase::getInstance();

		$query = 	'SELECT COUNT(*) FROM ' . $tableName .
					' WHERE status = 0';

		$result = $db->pquery($query, array());

		return $db->query_result($result, 0, 0);
	}

	public static function getImportController($className) {
		if ($className) {
			$importClass = self::getClassFromName($className);
			$user = Users_Record_Model::getCurrentUserModel();
			var_dump($user);
			$importController = new $importClass($request, $user);

			return $importController;
		}

		return null;
	}
}
