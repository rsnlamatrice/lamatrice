<?php

class RSNStatistics_Utils_Helper {

	public static function getStatsTableNameFromId($id) {
		$sql = "SELECT * FROM `vtiger_rsnstatistics`
				WHERE `vtiger_rsnstatistics`.`rsnstatisticsid` = ?
				AND `vtiger_rsnstatistics`.`disabled` = 0";
		$db = PearDatabase::getInstance();
		$result = $db->pquery($sql, array($id));
		$numberOfRecords = $db->num_rows($result);

		if ($numberOfRecords <= 0) {
			echo "nothing...<br/>";//tmp
			return "";
		}

		$row = $db->raw_query_result_rowdata($result, 0);
		
		return self::getStatsTableName($id, $row);
	}

	public static function getRelatedStatistics($moduleName) {
		$sql = "SELECT * FROM `vtiger_rsnstatistics`
				INNER JOIN `vtiger_crmentity`
					ON `vtiger_crmentity`.`crmid` = `vtiger_rsnstatistics`.`rsnstatisticsid`
				WHERE `vtiger_crmentity`.`deleted` = 0
				AND `vtiger_rsnstatistics`.`relmodule` = ?
				AND `vtiger_rsnstatistics`.`disabled` = 0";
		$db = PearDatabase::getInstance();
		$result = $db->pquery($sql, array($moduleName));
		$numberOfRecords = $db->num_rows($result);
		$relatedStatistics = array();

		for ($i = 0; $i < $numberOfRecords; ++$i) {
			$row = $db->raw_query_result_rowdata($result, $i);
			$relatedStatistics[] = $row;
		}

		return $relatedStatistics;
	}

	public static function getRelatedStatsFields($statisticId) {//statistics 306963 tmp
		$sql = "SELECT `vtiger_rsnstatisticsfields`.* FROM `vtiger_rsnstatisticsfields`
				INNER JOIN `vtiger_crmentity`
					ON `vtiger_crmentity`.`crmid` = `vtiger_rsnstatisticsfields`.`rsnstatisticsfieldsid`
				INNER JOIN `vtiger_crmentityrel`
					ON `vtiger_crmentityrel`.`relcrmid` = `vtiger_crmentity`.`crmid`
				WHERE `vtiger_crmentity`.`deleted` = 0
				AND `vtiger_crmentityrel`.`crmid` = ?";

		$db = PearDatabase::getInstance();
		$result = $db->pquery($sql, array($statisticId));
		$numberOfRecords = $db->num_rows($result);
		$relatedStatFields = array();

		for ($i = 0; $i < $numberOfRecords; ++$i) {
			$row = $db->raw_query_result_rowdata($result, $i);
			$relatedStatFields[] = $row;
		}

		return $relatedStatFields;
	}

	public static function getRelatedStatsValues($statistic, $crmid) {//statistics 306963 tmp
		$tableName = self::getStatsTableName($statistic['rsnstatisticsid'], $statistic);
		$sql = "SELECT `" . $tableName . "`.* FROM `" . $tableName . "`
				WHERE `" . $tableName . "`.`crmid` = ?";

		$db = PearDatabase::getInstance();
		$result = $db->pquery($sql, array($crmid));
		$numberOfRecords = $db->num_rows($result);
		$relatedStatValues = array();

		for ($i = 0; $i < $numberOfRecords; ++$i) {
			$row = $db->raw_query_result_rowdata($result, $i);
			$relatedStatValues[] = $row;
		}

		return $relatedStatValues;
	}

	// //Vtiger_Record_Model::getInstanceById($id);
	// public static function getRelatedStatsFieldsRecords($statisticId) {//statistics 306963 tmp
	// 	$relatedStatFields = self::getRelatedStatsFields($statisticId);
	// 	$relatedStatsFieldsRecords = array();

	// 	foreach ($relatedStatFields as $relatedStatField) {
	// 		$relatedStatsFieldsRecords[] = Vtiger_Record_Model::getInstanceById($relatedStatField['rsnstatisticsfieldsid']);
	// 	}

	// 	return $relatedStatsFieldsRecords;
	// }

	public static function getStatFieldFromUniquecode($uniquecode) {
		$sql = "SELECT `vtiger_rsnstatisticsfields`.* FROM `vtiger_rsnstatisticsfields`
				INNER JOIN `vtiger_crmentity`
					ON `vtiger_crmentity`.`crmid` = `vtiger_rsnstatisticsfields`.`rsnstatisticsfieldsid`
				WHERE `vtiger_crmentity`.`deleted` = 0
				AND `vtiger_rsnstatisticsfields`.`uniquecode` = ?";

		$db = PearDatabase::getInstance();
		$result = $db->pquery($sql, array($uniquecode));
		$numberOfRecords = $db->num_rows($result);

		if ($numberOfRecords > 0) {
			return $db->raw_query_result_rowdata($result, 0);
		}
		
		return false;
	}

	public static function getModuleRelatedStatsFieldsCodes($moduleName) {
		$relatedStatsFields = array();
		$relatedStatistics = RSNStatistics_Utils_Helper::getRelatedStatistics($moduleName);
		foreach ($relatedStatistics as $relatedStatistic) {
			$relatedStatFields = RSNStatistics_Utils_Helper::getRelatedStatsFields($relatedStatistic['rsnstatisticsid']);//tmp
			foreach ($relatedStatFields as $statField) {
				$relatedStatsFields[] = $statField['uniquecode'];
			}
		}

		return $relatedStatsFields;
	}

	public static function getRelatedStatsTablesNames($moduleName) {//tmp use getRelatedStatistics method !
		$relatedStatistics = self::getRelatedStatistics($moduleName);

		$tableNames = array();

		foreach ($relatedStatistics as $relatedStatistic) {
			$tableNames[] = self::getStatsTableName($relatedStatistic['rsnstatisticsid'], $relatedStatistic);
		}

		return $tableNames;
	}

	public static function getStatsTableName($id, $data) {//tmp do not put that here !!
		return "vtiger_stats_" . str_replace(" ", "_", $data['stat_name']) . "_" . $data['relmodule'] . "_" . $id;
	}

	public static function getFieldUniqueCodeFromId($id) {//tmp do not put that here !!
		$sql = "SELECT `vtiger_rsnstatisticsfields`.`uniquecode` FROM `vtiger_rsnstatisticsfields`
				WHERE `vtiger_rsnstatisticsfields`.`rsnstatisticsfieldsid` = ?";
		$db = PearDatabase::getInstance();
		$result = $db->pquery($sql, array($id));
		$numberOfRecords = $db->num_rows($result);

		if ($numberOfRecords <= 0) {
			echo "nothing...<br/>";//tmp
			return "";
		}

		$row = $db->raw_query_result_rowdata($result, 0);
		return $row['uniquecode'];
	}

	public static function getIdFromUniquecode($uniquecode) {
		$sql = "SELECT `vtiger_rsnstatisticsfields`.`rsnstatisticsfieldsid` FROM `vtiger_rsnstatisticsfields`
				WHERE `vtiger_rsnstatisticsfields`.`uniquecode` = ?";
		$db = PearDatabase::getInstance();
		$result = $db->pquery($sql, array($uniquecode));
		$numberOfRecords = $db->num_rows($result);

		if ($numberOfRecords <= 0) {
			echo "nothing...<br/>";//tmp
			return 0;
		}

		$row = $db->raw_query_result_rowdata($result, 0);
		return $row['rsnstatisticsfieldsid'];
	}

	public static function getRelationQuery($parentModuleName, $crmid) {
		$rows = "";
		$first = true;
		$relatedStatsFieldsCodes = self::getModuleRelatedStatsFieldsCodes($parentModuleName);

		foreach($relatedStatsFieldsCodes as $code) {
			if ($first) {
				$rows .= $code;
				$first = false;
			} else {
				$rows .= ', ' . $code;
			}
		}

		$query = "";
		$first = true;
		$firstTableName = "";
		$relatedStatsTablesNames = self::getRelatedStatsTablesNames($parentModuleName);

		foreach($relatedStatsTablesNames as $mainTable) {
			if ($first) {
				$first = false;
			} else {
				$query .= " UNION ";
			}

			$query .= "SELECT " .
					"`" . $mainTable . "`.name, `" . $mainTable . "`.begin_date, `" . $mainTable . "`.end_date, " . $rows .
					" FROM `" . $mainTable . "`";

			foreach($relatedStatsTablesNames as $relatedStatsTableName) {
				if ($relatedStatsTableName != $mainTable) {
					$query .= " LEFT OUTER JOIN `" . $relatedStatsTableName .
								"` ON `" . $relatedStatsTableName . "`.`name` = `" . $mainTable . "`.`name`" . 
								" AND `" . $relatedStatsTableName . "`.`crmid` = `" . $mainTable . "`.`crmid`";
				}
			}

			$query .= " WHERE `" . $mainTable . "`.crmid=" . $crmid;
		}

		$query .= " ORDER BY `end_date` DESC";

		return $query;
	}
}