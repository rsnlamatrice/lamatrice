<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

//ED150826 both for preImport and import
//Vu le 150929, sur mon PC n'ayant que 6G de RAM : '8G' fait planter sans vraiment le dire (sauf log de php)
global $php_max_memory_limit;
ini_set("memory_limit", empty($php_max_memory_limit) ? "8G" : $php_max_memory_limit);

class Import_Utils_Helper {

	static $AUTO_MERGE_NONE = 0;
	static $AUTO_MERGE_IGNORE = 1;
	static $AUTO_MERGE_OVERWRITE = 2;
	static $AUTO_MERGE_MERGEFIELDS = 3;

	static $supportedFileEncoding = array('UTF-8'=>'UTF-8', 'ISO-8859-1'=>'ISO-8859-1');
	static $supportedDelimiters = array(','=>'LBL_COMMA', ';'=>'LBL_SEMICOLON', '	'=>'LBL_TAB');
	static $supportedFileExtensions = array('csv','vcf');

	public function getSupportedFileExtensions() {
		return self::$supportedFileExtensions;
	}

	public function getSupportedFileEncoding() {
		return self::$supportedFileEncoding;
	}

	public function getSupportedDelimiters() {
		return self::$supportedDelimiters;
	}

	public static function getAutoMergeTypes() {
		return array(
			self::$AUTO_MERGE_IGNORE => 'Skip',
			self::$AUTO_MERGE_OVERWRITE => 'Overwrite',
			self::$AUTO_MERGE_MERGEFIELDS => 'Merge');
	}

	public static function getMaxUploadSize() {
		global $upload_maxsize;
		return $upload_maxsize;
	}

	public static function getImportDirectory() {
		global $import_dir;
		/* ED140824
		*/ if($import_dir && !file_exists($import_dir))
			mkdir($import_dir);
		return $import_dir;
	}

	public static function getImportFilePath($user, $moduleName, $nFile = 0) {
		$importDirectory = self::getImportDirectory();
		return $importDirectory. "IMPORT_".$user->id."_".$moduleName
			. ($nFile ? '['.str_pad($nFile, 2, '0', STR_PAD_LEFT).']' : '');
	}


	public static function getFileReaderInfo($type) {
		$configReader = new Import_Config_Model();
		$importTypeConfig = $configReader->get('importTypes');
		if(isset($importTypeConfig[$type])) {
			return $importTypeConfig[$type];
		}
		return null;
	}

	public static function getFileReader($request, $user) {
		$fileReaderInfo = self::getFileReaderInfo($request->get('type'));
		if(!empty($fileReaderInfo)) {
			require_once $fileReaderInfo['classpath'];
			$fileReader = new $fileReaderInfo['reader'] ($request, $user);
		} else {
			$fileReader = null;
		}
		return $fileReader;
	}

	public static function getDbTableName($user, $moduleName) {
		$configReader = new Import_Config_Model();
		$userImportTablePrefix = $configReader->get('userImportTablePrefix');

        $tableName = $userImportTablePrefix;
        if(is_string($user)){//ED150905
            $tableName .= $user;
        } elseif(method_exists($user, 'getId')){
            $tableName .= $user->getId();
        } else {
            $tableName .= $user->id;
        }

        if ($moduleName) {
        	$tableName .= '_' . $moduleName;
        }

        return $tableName;
	}

	public static function showErrorPage($errorMessage, $errorDetails=false, $customActions=false) {
		$viewer = new Vtiger_Viewer();

		$viewer->assign('ERROR_MESSAGE', $errorMessage);
		$viewer->assign('ERROR_DETAILS', $errorDetails);
		$viewer->assign('CUSTOM_ACTIONS', $customActions);
		$viewer->assign('MODULE','Import');

		$viewer->view('ImportError.tpl', 'Import');
	}

	public static function showImportLockedError($lockInfo) {

		$errorMessage = vtranslate('ERR_MODULE_IMPORT_LOCKED', 'Import');
		$errorDetails = array(vtranslate('LBL_MODULE_NAME', 'Import') => getTabModuleName($lockInfo['tabid']),
							vtranslate('LBL_USER_NAME', 'Import') => getUserFullName($lockInfo['userid']),
							vtranslate('LBL_LOCKED_TIME', 'Import') => $lockInfo['locked_since']);

		self::showErrorPage($errorMessage, $errorDetails);
	}

	public static function showImportTableBlockedError($moduleName, $user) {

		$errorMessage = vtranslate('ERR_UNIMPORTED_RECORDS_EXIST', 'Import');
		$customActions = array('LBL_CLEAR_DATA' => "location.href='index.php?module={$moduleName}&view=Import&mode=clearCorruptedData'");

		self::showErrorPage($errorMessage, '', $customActions);
	}

	public static function isUserImportBlocked($user, $moduleName) {
		$adb = PearDatabase::getInstance();
		$tableName = self::getDbTableName($user, $moduleName);

		if(Vtiger_Utils::CheckTable($tableName)) {
			$result = $adb->query('SELECT 1 FROM '.$tableName.' WHERE status = '.  Import_Data_Action::$IMPORT_RECORD_NONE . ' LIMIT 1');
			if($adb->num_rows($result) > 0) {
				return true;
			}
		}
		return false;
	}

	/* ED150905
	 * Contrôle si un module est verrouillé par un import, quelque soit l'utilisateur
	*/
	public static function isModuleImportBlocked($moduleName) {
		$db = PearDatabase::getInstance();
		$tableNamePattern = self::getDbTableName('%', $moduleName);
		$query = 'SHOW TABLES LIKE \''.$tableNamePattern.'\'';
		
		$result = $db->query($query);

		$noOfRecords = $db->num_rows($result);
		for($i=0; $i<$noOfRecords; $i++) {
			$tableName = $db->query_result($result, $i, 0);
			$query = 'SELECT 1 FROM `'.$tableName.'` WHERE status = '.  Import_Data_Action::$IMPORT_RECORD_NONE . ' LIMIT 1';
			$result = $db->query($query);
			if($db->num_rows($result) > 0) {
				return true;
			}
		}
		return false;
	}

	public static function clearUserImportInfo($user, $moduleName) {
		$adb = PearDatabase::getInstance();
		$tableName = self::getDbTableName($user, $moduleName);

		$adb->query('DROP TABLE IF EXISTS '.$tableName);
		Import_Lock_Action::unLock($user);
		Import_Queue_Action::removeForUser($user);
	}

	public static function getAssignedToUserList($module) {
		$cache = Vtiger_Cache::getInstance();
		if($cache->getUserList($module,$current_user->id)){
			return $cache->getUserList($module,$current_user->id);
		} else {
			$userList = get_user_array(FALSE, "Active", $current_user->id);
			$cache->setUserList($module,$userList,$current_user->id);
			return $userList;
		}
	}

	public static function getAssignedToGroupList($module) {
		$cache = Vtiger_Cache::getInstance();
		if($cache->getGroupList($module,$current_user->id)){
			return $cache->getGroupList($module,$current_user->id);
		} else {
			$groupList = get_group_array(FALSE, "Active", $current_user->id);
			$cache->setGroupList($module,$groupList,$current_user->id);
			return $groupList;
		}
	}

	public static function hasAssignPrivilege($moduleName, $assignToUserId) {
		$assignableUsersList = self::getAssignedToUserList($moduleName);
		if(array_key_exists($assignToUserId, $assignableUsersList)) {
			return true;
		}
		$assignableGroupsList = self::getAssignedToGroupList($moduleName);
		if(array_key_exists($assignToUserId, $assignableGroupsList)) {
			return true;
		}
		return false;
	}

	public static function validateFileUpload($request) {
		$current_user = Users_Record_Model::getCurrentUserModel();

		$uploadMaxSize = self::getMaxUploadSize();
		$importDirectory = self::getImportDirectory();
		$temporaryFileName = self::getImportFilePath($current_user, $request->get("module"));
		
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
	
			////ED150827
			//$srcFile = $request->get('import_file_localpath');
			//if(!file_exists($srcFile)) {
			//	$request->set('error_message', vtranslate('LBL_FILE_UPLOAD_FAILED', 'Import'));
			//	return false;
			//}
			//$request->set('import_file_name', $srcFile);
			//copy($srcFile, $temporaryFileName);
			//$fileCopied = $temporaryFileName;
			//if(!file_exists($fileCopied)) {
			//	$request->set('error_message', vtranslate('LBL_IMPORT_FILE_COPY_FAILED', 'Import'));
			//	return false;
			//}
			
		}
		else {
			
			/* ED150830 TODO fichiers multiples : chaque propriété de $files devient un tableau
			 * cf RSNImportSources/helpers/Utils.php
			 */ 
			$files = $_FILES['import_file'];
			//multiple ou pas ?
			if(is_array($files['tmp_name'])){
				throw new Exception('Les fichiers multiples ne sont pas gérés. cf RSNImportSources/helpers/Utils.php');
			}
			
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
		}
		
		$fileReader = Import_Utils_Helper::getFileReader($request, $current_user);

		if($fileReader == null) {
			$request->set('error_message', vtranslate('LBL_INVALID_FILE', 'Import'));
			return false;
		}

		$hasHeader = $fileReader->hasHeader();
		$firstRow = $fileReader->getFirstRowData($hasHeader);
		if($firstRow === false) {
			$request->set('error_message', vtranslate('LBL_NO_ROWS_FOUND', 'Import'));
			return false;
		}
		return true;
	}

	static function fileUploadErrorMessage($error_code) {
		switch ($error_code) {
			case 1:
				return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
			case 2:
				return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
			case 3:
				return 'The uploaded file was only partially uploaded';
			case 4:
				return 'No file was uploaded';
			case 6:
				return 'Missing a temporary folder';
			case 7:
				return 'Failed to write file to disk';
			case 8:
				return 'File upload stopped by extension';
			default:
				return 'Unknown upload error';
		}
	}
	
	static $php_memory_limit;
	/** ED150829
	 * Teste si l'utilisation mémoire s'approche du plantage
	 */
	static function isMemoryUsageToHigh($percentMax = 90){
		if(!self::$php_memory_limit)
			self::getPhpMermoryLimit();
		
		if($percentMax > 1)
			$percentMax /= 100;
		return memory_get_usage() > self::$php_memory_limit * $percentMax;
	}
	/** ED150829
	 * Retourne la limite d'utilisation mémoire paramétrée dans php
	 */
	static function getPhpMermoryLimit(){
		if(!self::$php_memory_limit){
			function return_bytes($val) {
				$val = trim($val);
				$last = strtolower($val[strlen($val)-1]);
				switch($last) {
					// The 'G' modifier is available since PHP 5.1.0
					case 'g':
						$val *= 1024;
					case 'm':
						$val *= 1024;
					case 'k':
						$val *= 1024;
				}
			
				return $val;
			}
			
			self::$php_memory_limit = return_bytes(ini_get('memory_limit'));
		}
		return self::$php_memory_limit;
	}
}
