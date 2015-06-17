<?php

class RSNImportSources_Queue_Action extends Import_Queue_Action {
	static $importQueueTable = 'vtiger_rsnimport_queue';

	/**
	 * Methode to add an import in the queue table.
	 *  It create the table if it dos not exist.
	 * @param Vtiger_Request $request: the curent request.
	 * @param $user : the curent user.
	 * @param string $module : the import module name.
	 * @param array $fieldMapping : the field mapping.
	 * @param array $defaultValues : the default field values.
	 */
	public static function add($request, $user, $module, $fieldMapping, $defaultValues = null) {
		if(!$defaultValues)
			$defaultValues = null;
		$db = PearDatabase::getInstance();

		if (!Vtiger_Utils::CheckTable(self::$importQueueTable)) {
			Vtiger_Utils::CreateTable(
				self::$importQueueTable,
				"(importid INT NOT NULL PRIMARY KEY,
					userid INT NOT NULL,
					tabid INT NOT NULL,
					importsourceclass VARCHAR(64) NOT NULL,
					field_mapping TEXT,
					default_values TEXT,
					merge_type INT,
					merge_fields TEXT,
					status INT default 0)",
				true);
		}

		if($request->get('is_scheduled')) {
			$status = self::$IMPORT_STATUS_SCHEDULED;
		} else {
			$status = self::$IMPORT_STATUS_NONE;
		}

		$db->pquery('INSERT INTO ' . self::$importQueueTable . ' VALUES(?,?,?,?,?,?,?,?,?)',
				array($db->getUniqueID(self::$importQueueTable),
						$user->id,
						getTabid($module),
						$request->get('ImportSource'),
						Zend_Json::encode((object) $fieldMapping),
						Zend_Json::encode((object) $defaultValues),// tmp usefull ??
						$request->get('merge_type'),
						Zend_Json::encode($request->get('merge_fields')),
						$status));
	}

	/** 
	 * Method to remove an import of the queue table.
	 * @param int $importId : the id of the import do remove.
	 */
	public static function remove($importId) {
		$db = PearDatabase::getInstance();
		if(Vtiger_Utils::CheckTable(self::$importQueueTable)) {
			$db->pquery('DELETE FROM ' . self::$importQueueTable . ' WHERE importid=?', array($importId));
		}
	}

	/** 
	 * Method to remove all import of the queue table for a specific user.
	 * @param $user : the concerned user.
	 */
	public static function removeForUser($user) {
		$db = PearDatabase::getInstance();
		if(Vtiger_Utils::CheckTable(self::$importQueueTable)) {
			$db->pquery('DELETE FROM ' . self::$importQueueTable . ' WHERE userid=?', array($user->id));
		}
	}

	/** 
	 * Method to get one import information for a specific user.
	 * @param $user : the concerned user.
	 * @return the information about one import.
	 */
	public static function getUserCurrentImportInfo($user) {
		$db = PearDatabase::getInstance();

		if(Vtiger_Utils::CheckTable(self::$importQueueTable)) {
			$queueResult = $db->pquery('SELECT * FROM ' . self::$importQueueTable . ' WHERE userid=? LIMIT 1', array($user->id));

			if($queueResult && $db->num_rows($queueResult) > 0) {
				$rowData = $db->raw_query_result_rowdata($queueResult, 0);
				return self::getImportInfoFromResult($rowData);
			}
		}
		return null;
	}

	/** 
	 * Method to get all import informations for a specific user.
	 * @param $user : the concerned user.
	 * @return array : the import informations.
	 */
	public static function getUserCurrentImportInfos($user) {
		$db = PearDatabase::getInstance();
		$result = array();

		if(Vtiger_Utils::CheckTable(self::$importQueueTable)) {
			$queueResult = $db->pquery('SELECT * FROM ' . self::$importQueueTable . ' WHERE userid=?', array($user->id));

			if($queueResult) {
				$resultNumber = $db->num_rows($queueResult);

				for ($i = 0;$i < $resultNumber; ++$i) {
					$rowData = $db->raw_query_result_rowdata($queueResult, $i);
					array_push($result, self::getImportInfoFromResult($rowData));
				}
			}
		}

		return $result;
	}

	/** 
	 * Method to get import information for specific user and a specific module.
	 * @param string $module : the concerned module name.
	 * @param $user : the concerned user.
	 * @return array : the import information.
	 */
	public static function getImportInfo($module, $user) {
		$db = PearDatabase::getInstance();
		
		if(Vtiger_Utils::CheckTable(self::$importQueueTable)) {
			$queueResult = $db->pquery('SELECT * FROM ' . self::$importQueueTable . ' WHERE tabid=? AND userid=?',
											array(getTabid($module), $user->id));

			if($queueResult && $db->num_rows($queueResult) > 0) {
				$rowData = $db->raw_query_result_rowdata($queueResult, 0);
				return self::getImportInfoFromResult($rowData);
			}
		}
		return null;
	}

	/** 
	 * Method to get import informations using id.
	 * @param int $importId : the id of the desired import.
	 * @return array : the import information.
	 */
	public static function getImportInfoById($importId) {
		$db = PearDatabase::getInstance();

		if(Vtiger_Utils::CheckTable(self::$importQueueTable)) {
			$queueResult = $db->pquery('SELECT * FROM ' . self::$importQueueTable . ' WHERE importid=?', array($importId));

			if($queueResult && $db->num_rows($queueResult) > 0) {
				$rowData = $db->raw_query_result_rowdata($queueResult, 0);
				return self::getImportInfoFromResult($rowData);
			}
		}
		return null;
	}

	/** 
	 * Method to get all import informations.
	 * @param boolean $status : if true, get only import info where import info = true. Else, get all import info.
	 * @return array : the import informations.
	 */
	public static function getAll($status=false) {
		$db = PearDatabase::getInstance();

		$query = 'SELECT * FROM ' . self::$importQueueTable . '';
		$params = array();
		if($status !== false) {
			$query .= ' WHERE status = ?';
			array_push($params, $status);
		}
		$result = $db->pquery($query, $params);

		$noOfImports = $db->num_rows($result);
		$scheduledImports = array();
		for ($i = 0; $i < $noOfImports; ++$i) {
			$rowData = $db->raw_query_result_rowdata($result, $i);
			$scheduledImports[$rowData['importid']] = self::getImportInfoFromResult($rowData);
		}
		return $scheduledImports;
	}

	/** 
	 * Method to organize import informations.
	 * @param $rowData: the data from db.
	 * @return array : the importorganized informations.
	 */
	static function getImportInfoFromResult($rowData) {
		return array(
			'id' => $rowData['importid'],
			'module' => getTabModuleName($rowData['tabid']),
			'field_mapping' => Zend_Json::decode($rowData['field_mapping']),
			'default_values' => Zend_Json::decode($rowData['default_values']),
			'merge_type' => $rowData['merge_type'],
			'merge_fields' => Zend_Json::decode($rowData['merge_fields']),
			'user_id' => $rowData['userid'],
			'status' => $rowData['status'],
			'importsourceclass' => $rowData['importsourceclass']
		);
	}

	/** 
	 * Method to update statues of an import.
	 * @param int $importId : the id of the import to update.
	 * @param $status: the new status of the import.
	 */
	static function updateStatus($importId, $status) {
		$db = PearDatabase::getInstance();
		$db->pquery('UPDATE ' . self::$importQueueTable . ' SET status=? WHERE importid=?', array($status, $importId));
	}

	/** 
	 * Method to get the name of the import source class name for a specific user and a specific module.
	 * @param string $module : the module name.
	 * @param $user : the current user.
	 * @return string : the import class name.
	 */
	public static function getImportClassName($module, $user) {
		$db = PearDatabase::getInstance();

		if(Vtiger_Utils::CheckTable(self::$importQueueTable)) {
			$queueResult = $db->pquery('SELECT importsourceclass FROM ' . self::$importQueueTable . ' WHERE tabid=? AND userid=? LIMIT 1',
											array(getTabid($module), $user->id));

			if($queueResult && $db->num_rows($queueResult) > 0) {
				$rowData = $db->raw_query_result_rowdata($queueResult, 0);
				return $rowData['importsourceclass'];
			}
		}
		return null;
	}
}