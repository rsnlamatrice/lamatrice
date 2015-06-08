<?php

//tmp do not use the import queue table but it's own -> like that we can manage hour own import !
class RSNImport_Queue_Action extends Import_Queue_Action {
	static $importQueueTable = 'vtiger_rsnimport_queue';

	public static function add($request, $user, $module, $fieldMapping, $defaultValues) {
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
						$request->get('ImportSource'),//tmp importSourceId to check 
						Zend_Json::encode((object) $fieldMapping),// tmp !!!!!!!
						Zend_Json::encode((object) $defaultValues),// tmp !!!!!
						$request->get('merge_type'),
						Zend_Json::encode($request->get('merge_fields')),
						$status));
	}


	public static function remove($importId) {
		$db = PearDatabase::getInstance();
		if(Vtiger_Utils::CheckTable(self::$importQueueTable)) {
			$db->pquery('DELETE FROM ' . self::$importQueueTable . ' WHERE importid=?', array($importId));
		}
	}

	public static function removeForUser($user) {
		$db = PearDatabase::getInstance();
		if(Vtiger_Utils::CheckTable(self::$importQueueTable)) {
			$db->pquery('DELETE FROM ' . self::$importQueueTable . ' WHERE userid=?', array($user->id));
		}
	}

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

	static function updateStatus($importId, $status) {
		$db = PearDatabase::getInstance();
		$db->pquery('UPDATE ' . self::$importQueueTable . ' SET status=? WHERE importid=?', array($status, $importId));
	}

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