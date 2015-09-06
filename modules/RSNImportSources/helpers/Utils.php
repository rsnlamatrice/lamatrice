<?php

require_once('modules/RSNImportSources/helpers/Performance.php');

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
		
		if(!is_writable($importDirectory)) {
			$request->set('error_message', vtranslate('LBL_IMPORT_DIRECTORY_NOT_WRITABLE', 'Import'));
			return false;
		}
		
		if($request->get('import_file_src_mode') == 'localpath'){
	
			//ED150827
			$srcFiles = explode(';', $request->get('import_file_localpath'));
			for($nFile = 0; $nFile < count($srcFiles); $nFile++){
				$srcFile = trim($srcFiles[$nFile]);
				if($srcFile && !file_exists($srcFile)) {
					$request->set('error_message', vtranslate('LBL_FILE_UPLOAD_FAILED', 'Import'));
					return false;
				}
			}
			
			$request->set('import_file_name', $request->get('import_file_localpath'));
			
			for($nFile = 0; $nFile < count($srcFiles); $nFile++){
				$srcFile = trim($srcFiles[$nFile]);
				if(!$srcFile)
					continue;
				if($nFile > 0)
					$temporaryFileName = self::getImportFilePath($current_user, $request->get("for_module"), $nFile);
				copy($srcFile, $temporaryFileName);
				$fileCopied = $temporaryFileName;
				if(!file_exists($fileCopied)) {
					$request->set('error_message', vtranslate('LBL_IMPORT_FILE_COPY_FAILED', 'Import') . ' : ' . $fileCopied);
					return false;
				}
			}
		}
		else {

			//ED150830 fichiers multiples : chaque propriété de $files devient un tableau
			$files = $_FILES['import_file'];
			//multiple ou pas ?
			if(!is_array($files['tmp_name'])){
				foreach($files as $prop=>$value)
					$files[$prop] = array($value);
			}
			$nbFiles = count($files['tmp_name']);
			
			for($nFile = 0; $nFile < $nbFiles; $nFile++){
				if($files['error'][$nFile]) {
					$request->set('error_message', self::fileUploadErrorMessage($files['error'][$nFile]));
					return false;
				}
			
				if(!is_uploaded_file($files['tmp_name'][$nFile])) {
					$request->set('error_message', vtranslate('LBL_FILE_UPLOAD_FAILED', 'Import'));
					return false;
				}
				if ($files['size'][$nFile] > $uploadMaxSize) {
					$request->set('error_message', vtranslate('LBL_IMPORT_ERROR_LARGE_FILE', 'Import').
					$uploadMaxSize.' '.vtranslate('LBL_IMPORT_CHANGE_UPLOAD_SIZE', 'Import'));
					return false;
				}
			}

			//ED150827
			$request->set('import_file_name', implode(', ', $files['name']));
	
			for($nFile = 0; $nFile < $nbFiles; $nFile++){
				$temporaryFileName = self::getImportFilePath($current_user, $request->get("for_module"), $nFile );
				$fileCopied = move_uploaded_file($files['tmp_name'][$nFile], $temporaryFileName);
				if(!$fileCopied) {
					$request->set('error_message', vtranslate('LBL_IMPORT_FILE_COPY_FAILED', 'Import').' : '.$files['name'][$nFile] . ' -> ' . $temporaryFileName);
					return false;
				}
				//Suppression du fichier du prochain index
				if(file_exists($temporaryFileName . "-". ($nFile+1)))
					unlink($temporaryFileName . "-". ($nFile+1));
			}
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
		foreach($fields as $fieldIndex => $fieldName) {
            $fieldObject = $moduleFields[$fieldName];
            if (is_object($fieldObject)) {
            	$columnsListQuery .= RSNImportSources_Utils_Helper::getDBColumnType($fieldObject, $fieldTypes);
            } elseif($fieldName == 'remarque' || $fieldName == 'description') {
            	$columnsListQuery .= ','.$fieldName.' TEXT';
			}
			else {
            	$columnsListQuery .= ','.$fieldName.' varchar(255)';
            }
		}
		$createTableQuery = 'CREATE TABLE '. $tableName . ' ('.$columnsListQuery.') ENGINE=MyISAM ';
		$result = $db->query($createTableQuery);
		if(!$result){
			$db->echoError($createTableQuery);
			echo_callstack();
			die('Impossible de creer la table temporaire');
		}
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

		$query = 'SELECT vtiger_crmentity.crmid/*, tab.name*/, ris.class, ris.title
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
			$params[] = vtranslate($moduleName, $moduleName);
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
	 * @param $status : record status checked. False means all. Import_Data_Action::$IMPORT_RECORD_NONE === 0.
	 * @return int - the number of record.
	 */
	public static function getNumberOfRecords($user, $moduleName, $status = 0) {
		$tableName = self::getDbTableName($user, $moduleName);
		$db = PearDatabase::getInstance();

		$query = 	'SELECT COUNT(*) FROM ' . $tableName;
		if($status !== false)
			$query .= ' WHERE status = ' . $status;

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
	
	static $checkPickListValueCache;
	public static function checkPickListValue($moduleName, $fieldName, $pickListName, $fieldValue, $createIfMissing = true){
		if(!$fieldValue)
			return true;
		
		if(self::$checkPickListValueCache && array_key_exists("$moduleName:$fieldName:$fieldValue", self::$checkPickListValueCache))
			return true;
		
		if(!self::$checkPickListValueCache)
			self::$checkPickListValueCache = array();
		
		global $adb;
		$query = "SELECT 1
			FROM `vtiger_$pickListName`
			WHERE `$pickListName` = ?
			LIMIT 1";
		$result = $adb->pquery($query, array($fieldValue));
		if(!$result){
			//$adb->echoError("checkPickListValue : $query");
			return false; //bad table name ?
		}
		if($adb->getRowCount($result))
			$exists = true;
		elseif($createIfMissing
		&& self::checkPickListTableSequence($moduleName, $pickListName)){
			
			//TODO notify administrator
			
			$exists = self::addPickListValues($moduleName, $fieldName, $fieldValue);
		}
		else
			$exists = false;
		self::$checkPickListValueCache["$moduleName:$pickListName:$fieldValue"] = $exists;
		return $exists;
	}
	
	public static function addPickListValues($moduleName, $fieldName, $fieldValue){
		$moduleModel = Settings_Picklist_Module_Model::getInstance($moduleName);
		$fieldModel = Settings_Picklist_Field_Model::getInstance($fieldName, $moduleModel);
		//var_dump('getPickListName', $fieldName, $fieldModel->getPickListName());
		$rolesSelected = array();
		if($fieldModel->isRoleBased()) {
			$userSelectedRoles = $request->get('rolesSelected',array());
			//selected all roles option
			if(in_array('all',$userSelectedRoles)) {
				$roleRecordList = Settings_Roles_Record_Model::getAll();
				foreach($roleRecordList as $roleRecord) {
					$rolesSelected[] = $roleRecord->getId();
				}
			}else{
				$rolesSelected = $userSelectedRoles;
			}
		}
		return $moduleModel->addPickListValues($fieldModel, $fieldValue, $rolesSelected);
			
	}
	
	public static function checkPickListTableSequence($moduleName, $pickListName){
		global $adb;
		$query = "UPDATE `vtiger_".$pickListName."_seq`
			SET id = (SELECT MAX(`".$pickListName."id`) FROM `vtiger_$pickListName`)";
		$result = $adb->query($query);
		if(!$result){
			//$adb->echoError("checkPickListTable : $query");
			return false; //bad table name ?
		}
		return !!$result; 
	}
	
	
	/** METHODES POUR POST-PREIMPORT **/
	
	/**
	 * Méthode qui affecte le contactid pour tous ceux qu'on trouve d'après leur Ref4D
	 */
	public static function setPreImportDataContactIdByRef4D($user, $moduleName, $ref4dFieldName, $contactIdField, $changeStatus = true) {
		$db = PearDatabase::getInstance();
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($user, $moduleName);
		
		if($changeStatus === true)
			$changeStatus = RSNImportSources_Data_Action::$IMPORT_RECORD_SKIPPED;
			
		// Pré-identifie les contacts
		
		/* Affecte la réf du contact d'après la ref 4D */
		$query = "UPDATE $tableName
			JOIN vtiger_contactdetails
				ON vtiger_contactdetails.ref4d = `$tableName`.`$ref4dFieldName`
			JOIN vtiger_crmentity
				ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
		";
		
		$query .= " SET `$tableName`.`$contactIdField` = vtiger_crmentity.crmid";
		
		if($changeStatus !== false)
			$query .= ", `$tableName`.status = ".$changeStatus;
			
		$query .= "
			WHERE vtiger_crmentity.deleted = 0
			AND `$tableName`.status = ".RSNImportSources_Data_Action::$IMPORT_RECORD_NONE."
		";
		$result = $db->query($query);
		if(!$result)
			$db->echoError($query);
			
		return !!$result;
	}

	/**
	 * Méthode qui court-circuite tous les contacts qui existent déjà d'après leur Ref4D
	 */
	public static function skipPreImportDataForExistingContactsByRef4D($user, $moduleName, $ref4dFieldName, $updateContactIdField = false) {
		$db = PearDatabase::getInstance();
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($user, $moduleName);
		
		// Pré-identifie les contacts
		
		/* Affecte la réf du contact d'après la ref 4D */
		$query = "UPDATE $tableName
			JOIN vtiger_contactdetails
				ON vtiger_contactdetails.ref4d = `$tableName`.`$ref4dFieldName`
			JOIN vtiger_crmentity
				ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
		";
		$query .= " SET ";
		if($updateContactIdField){
			$query .= "`$tableName`.`$updateContactIdField` = vtiger_crmentity.crmid, ";
		}
		$query .= "`$tableName`.status = ".RSNImportSources_Data_Action::$IMPORT_RECORD_SKIPPED;
		$query .= "
			WHERE vtiger_crmentity.deleted = 0
			AND `$tableName`.status = ".RSNImportSources_Data_Action::$IMPORT_RECORD_NONE."
		";
		$result = $db->query($query);
		if(!$result)
			$db->echoError($query);
			
		return !!$result;
	}
	
	/**
	 * Méthode qui court-circuite tout enregistrement des contacts qui n'existent pas d'après leur Ref4D
	 * `$contactIdField` IS NULL OR `$contactIdField` = ''
	 */
	public static function skipPreImportDataForMissingContactsByRef4D($user, $moduleName, $contactIdField) {
		$db = PearDatabase::getInstance();
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($user, $moduleName);
		
		/* Le contact n'existe pas, on skippe */
		
		$query = "UPDATE $tableName
			SET status = ".RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED."
			WHERE status = ".RSNImportSources_Data_Action::$IMPORT_RECORD_NONE."
			AND (`$contactIdField` IS NULL OR `$contactIdField` = '')
		";
		$result = $db->query($query);
		if(!$result)
			$db->echoError($query);
			
		return !!$result;
	}
}