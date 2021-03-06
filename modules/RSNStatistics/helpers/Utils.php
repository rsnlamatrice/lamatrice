<?php

class RSNStatistics_Utils_Helper {

	public static function getStatsTableNameFromId($id) {
		$tableName = Vtiger_Cache::get('getStatsTableName', $id);
		if($tableName)
			return $tableName;
		$sql = "SELECT * FROM `vtiger_rsnstatistics`
				WHERE `vtiger_rsnstatistics`.`rsnstatisticsid` = ?
				AND `vtiger_rsnstatistics`.`disabled` = 0";
		$db = PearDatabase::getInstance();
		$result = $db->pquery($sql, array($id));
		if(!$result)
			$db->echoError('Error in getStatsTableNameFromId');
		$numberOfRecords = $db->num_rows($result);

		if ($numberOfRecords <= 0) {
			//echo_callstack();
			//echo "$id : nothing...<br/>";//tmp
			return "";
		}

		$row = $db->raw_query_result_rowdata($result, 0);
		
		$tableName = self::getStatsTableName($id, $row);
		Vtiger_Cache::set('getStatsTableName', $id, $tableName);
		return $tableName;
	}

	public static function getRelatedStatistics($moduleNames, $includeDisabled = false) {
		$sql = "SELECT *
				FROM `vtiger_rsnstatistics`
				INNER JOIN `vtiger_crmentity`
					ON `vtiger_crmentity`.`crmid` = `vtiger_rsnstatistics`.`rsnstatisticsid`
				WHERE `vtiger_crmentity`.`deleted` = 0";
		if(!$includeDisabled)
				$sql .= " AND `vtiger_rsnstatistics`.`disabled` = 0";
		if($moduleNames){
			if(!is_array($moduleNames))
				$moduleNames = array($moduleNames);
			$sql .= "
				AND `vtiger_rsnstatistics`.`relmodule` IN (".generateQuestionMarks($moduleNames).")";
		}
		$db = PearDatabase::getInstance();
		$result = $db->pquery($sql, $moduleNames);
		if(!$result)
			$db->echoError();
		$numberOfRecords = $db->num_rows($result);
		$relatedStatistics = array();

		for ($i = 0; $i < $numberOfRecords; ++$i) {
			$row = $db->raw_query_result_rowdata($result, $i);
			$row['id'] = $row['crmid'];
			$relatedStatistics[] = $row;
		}

		return $relatedStatistics;
	}
	public static function getRelatedStatisticsRecordModels($moduleNames) {
		$relatedStats = self::getRelatedStatistics($moduleNames);
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', 'RSNStatistics');
		$moduleModel = Vtiger_Module_Model::getInstance('RSNStatistics');
		foreach($relatedStats as $index => $rowData){
			$recordInstance = new $modelClassName();
			$relatedStats[$index] = $recordInstance->setData($rowData)->setModuleFromInstance($moduleModel);
		}
		return $relatedStats;
	}

	/**
	 * Function to get statistic fields properties
	 * @param $statisticIds : id or array of ids
	 * @return array of array of statistic fields properties
	 */
	public static function getRelatedStatsFields($statisticIds, $moduleNames = false, $context = false) {//statistics 306963 tmp
		if($statisticIds && !is_array($statisticIds))
			$statisticIds = array($statisticIds);
		$params = array();
		//Pas sûr que le `vtiger_rsnstatistics`.`rsnstatisticsid` ne fasse pas foirer le DISTINCT dans le cas où un champ apparait dans plusieurs stats lié au même module
		$sql = "SELECT DISTINCT `vtiger_rsnstatisticsfields`.*, `vtiger_crmentity`.*, `vtiger_rsnstatistics`.`rsnstatisticsid`
				FROM `vtiger_rsnstatisticsfields`
				INNER JOIN `vtiger_crmentity`
					ON `vtiger_crmentity`.`crmid` = `vtiger_rsnstatisticsfields`.`rsnstatisticsfieldsid`
				INNER JOIN `vtiger_crmentityrel`
					ON `vtiger_crmentityrel`.`relcrmid` = `vtiger_crmentity`.`crmid`
				INNER JOIN `vtiger_rsnstatistics`
					ON `vtiger_crmentityrel`.`crmid` = `vtiger_rsnstatistics`.`rsnstatisticsid`
				INNER JOIN `vtiger_crmentity` AS `vtiger_crmentity_stats`
					ON `vtiger_rsnstatistics`.`rsnstatisticsid` = `vtiger_crmentity_stats`.`crmid`
				WHERE `vtiger_crmentity`.`deleted` = 0
				AND `vtiger_crmentity_stats`.`deleted` = 0";
			if($statisticIds){
				$sql .= "
					AND `vtiger_crmentityrel`.`crmid` IN (".generateQuestionMarks($statisticIds).")";
				$params = array_merge($params, $statisticIds);
			}
			else{
				$sql .= "
					AND `vtiger_rsnstatistics`.`disabled` = 0";
				if($moduleNames){
					if(!is_array($moduleNames))
						$moduleNames = array($moduleNames);
					$sql .= "
						AND `vtiger_rsnstatistics`.`relmodule` IN (".generateQuestionMarks($moduleNames).")";
					$params = array_merge($params, $moduleNames);
				}
			}
			if($context === 'moduleheader')
				$sql .= "
					AND IFNULL(`vtiger_rsnstatisticsfields`.moduleheadersequence, 0) > 0
					ORDER BY `vtiger_rsnstatisticsfields`.moduleheadersequence";
			else
				$sql .= "
					ORDER BY `vtiger_rsnstatisticsfields`.sequence";

		$db = PearDatabase::getInstance();
		$result = $db->pquery($sql, $params);
		if(!$result)
			$db->echoError();
		$numberOfRecords = $db->num_rows($result);
		
		$relatedStatFields = array();
		for ($i = 0; $i < $numberOfRecords; ++$i) {
			$row = $db->raw_query_result_rowdata($result, $i);
			$row['id'] = $row['crmid'];
			$relatedStatFields[] = $row;
		}
		return $relatedStatFields;
	}

	/**
	 * Function to get sfilters of a statistic
	 * @param $statisticIds : id or array of ids
	 * @return array of array of statistic fields properties
	 */
	public static function getStatisticFilters($statisticIds, $moduleNames = false, $context = false) {//statistics 306963 tmp
		if($statisticIds && !is_array($statisticIds))
			$statisticIds = array($statisticIds);
		$params = array();
		//Pas sûr que le `vtiger_rsnstatistics`.`rsnstatisticsid` ne fasse pas foirer le DISTINCT dans le cas où un champ apparait dans plusieurs stats lié au même module
		$sql = "SELECT DISTINCT `vtiger_rsnfiltrestatistique`.*, `vtiger_crmentity`.*, `vtiger_rsnstatistics`.`rsnstatisticsid`
				FROM `vtiger_rsnfiltrestatistique`
				INNER JOIN `vtiger_crmentity`
					ON `vtiger_crmentity`.`crmid` = `vtiger_rsnfiltrestatistique`.`rsnfiltrestatistiqueid`
				INNER JOIN `vtiger_crmentityrel`
					ON `vtiger_crmentityrel`.`relcrmid` = `vtiger_crmentity`.`crmid`
				INNER JOIN `vtiger_rsnstatistics`
					ON `vtiger_crmentityrel`.`crmid` = `vtiger_rsnstatistics`.`rsnstatisticsid`
				INNER JOIN `vtiger_crmentity` AS `vtiger_crmentity_stats`
					ON `vtiger_rsnstatistics`.`rsnstatisticsid` = `vtiger_crmentity_stats`.`crmid`
				WHERE `vtiger_crmentity`.`deleted` = 0
				AND `vtiger_crmentity_stats`.`deleted` = 0";
		if($statisticIds){
			$sql .= "
				AND `vtiger_crmentityrel`.`crmid` IN (".generateQuestionMarks($statisticIds).")";
			$params = array_merge($params, $statisticIds);
		}
		else{
			$sql .= "
				AND `vtiger_rsnstatistics`.`disabled` = 0";
			if($moduleNames){
				if(!is_array($moduleNames))
					$moduleNames = array($moduleNames);
				$sql .= "
					AND `vtiger_rsnstatistics`.`relmodule` IN (".generateQuestionMarks($moduleNames).")";
				$params = array_merge($params, $moduleNames);
			}
		}

		$sql .= " ORDER BY `vtiger_rsnfiltrestatistique`.name";

		$db = PearDatabase::getInstance();
		$result = $db->pquery($sql, $params);
		if(!$result)
			$db->echoError();
		$numberOfRecords = $db->num_rows($result);
		
		$statisticFilters = array();
		for ($i = 0; $i < $numberOfRecords; ++$i) {
			$row = $db->raw_query_result_rowdata($result, $i);
			$row['id'] = $row['crmid'];
			$statisticFilters[] = $row;
		}
		return $statisticFilters;
	}

	public function getFiltersAvailable($filtervaluequeryid, $crmids, $isAllEntities) {//tmp to check !!
		$db = PearDatabase::getInstance();
		$filterValuesQueryRecord = Vtiger_Record_Model::getInstanceById($filtervaluequeryid);
					$filtersQuery = $filterValuesQueryRecord->getExecutionQuery(array('crmid'=>$crmids, 'all_filters'=>$isAllEntities));
		$result = $db->pquery($filtersQuery["sql"], $filtersQuery["params"]);

		if(!$result)
			$db->echoError();
		$numberOfRecords = $db->num_rows($result);
		
		$filters_available = array();
		for ($i = 0; $i < $numberOfRecords; ++$i) {
			$row = $db->raw_query_result_rowdata($result, $i);
			$filters_available[] = $row;
		}

		return $filters_available;
	}
	
	public static function getRelatedStatsFieldsRecordModels($statisticIds, $moduleName = false) {
		$relatedStatFields = self::getRelatedStatsFields($statisticIds, $moduleName);
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', 'RSNStatisticsFields');
		$moduleModel = Vtiger_Module_Model::getInstance('RSNStatisticsFields');
		foreach($relatedStatFields as $index => $rowData){
			$recordInstance = new $modelClassName();
			$relatedStatFields[$index] =$recordInstance->setData($rowData)->setModuleFromInstance($moduleModel);
		}
		return $relatedStatFields;
	}
	
	//public static function getRelatedStatsFieldsWebserviceFields($statisticIds, $moduleName = false) {
	//	$relatedStatFields = self::getRelatedStatsFields($statisticIds, $moduleName);
	//	foreach($relatedStatFields as $index => $rowData){
	//		$tableNames = array();
	//		$row['columnname'] = $row['uniquecode'];
	//		$row['fieldlabel'] = $row['fieldname'];
	//		if(!array_key_exists($row['rsnstatisticsid'], $tableNames))
	//			$tableNames[$row['rsnstatisticsid']] = self::getStatsTableNameFromId($row['rsnstatisticsid']);
	//		$row['tablename'] = $tableNames[$row['rsnstatisticsid']];
	//		$relatedStatFields[$index] = RSNStatisticsFields_Field_Model::getInstanceFromRowData(null, $rowData);
	//	}
	//	return $relatedStatFields;
	//}
	
	/**
	 * Retourne les champs statistiques sous la forme de Vtiger_Field_Model
	 */
	public static function getRelatedStatsFieldsVtigerFieldModels($statisticIds, $moduleName = false) {
		$statFields = self::getRelatedStatsFields($statisticIds, $moduleName);
		$relatedStatFields = array();
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', 'RSNStatisticsFields');
		$moduleModel = Vtiger_Module_Model::getInstance('RSNStatisticsFields');
		foreach($statFields as $index => $rowData){
			$fieldInstance = RSNStatisticsFields_Field_Model::getInstanceFromRowData($rowData);
			$fieldInstance->setModule($moduleModel);
			$relatedStatFields[$fieldInstance->get('column')] = $fieldInstance;
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

	public static function getModuleRelatedStatsFieldsCodes($moduleNames) {
		$statsFields = array();
		$relatedStatistics = RSNStatistics_Utils_Helper::getRelatedStatistics($moduleNames);
		foreach ($relatedStatistics as $relatedStatistic) {
			$relatedStatFields = RSNStatistics_Utils_Helper::getRelatedStatsFields($relatedStatistic['rsnstatisticsid']);//tmp
			foreach ($relatedStatFields as $statField) {
				$statsFields[] = $statField['uniquecode'];
			}
		}
		return $statsFields;
	}

	public static function getRelatedStatsTablesNames($moduleName, $notDisabledOnly = false) {//tmp use getRelatedStatistics method !
		$relatedStatistics = self::getRelatedStatistics($moduleName);

		$tableNames = array();

		foreach ($relatedStatistics as $relatedStatistic)
			if(!$notDisabledOnly || $relatedStatistic['disabled'] != 1){
				$tableNames[] = self::getStatsTableName($relatedStatistic['rsnstatisticsid'], $relatedStatistic);
			}

		return $tableNames;
	}

	public static function getStatsTableName($id, $data) {//tmp do not put that here !!
		$stat_name = is_object($data) ? $data->getName() : $data['stat_name'];
		if(!$stat_name){
			var_dump($data);
			die(__FILE__.' getStatsTableName stat_name vide');
		}
		$relmodule = is_object($data) ? $data->get('relmodule') : $data['relmodule'];
		return "vtiger_stats_" . str_replace(" ", "_", $stat_name) . "_" . $relmodule . "_" . $id;
	}
	public static function getTableInfo($table_name){
		global $adb;
		$result = $adb->query("SHOW TABLES LIKE '$table_name'");
		return $adb->raw_query_result_rowdata($result, 0);
	}

	public static function getFieldUniqueCodeFromId($id) {//tmp do not put that here !!
		$sql = "SELECT `vtiger_rsnstatisticsfields`.`uniquecode` FROM `vtiger_rsnstatisticsfields`
				WHERE `vtiger_rsnstatisticsfields`.`rsnstatisticsfieldsid` = ?";
		$db = PearDatabase::getInstance();
		$result = $db->pquery($sql, array($id));
		$numberOfRecords = $db->num_rows($result);

		if ($numberOfRecords <= 0) {
			//echo_callstack();
			//echo "$id : nothing...<br/>";//tmp
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
			//echo_callstack();
			//echo "$id : nothing...<br/>";//tmp
			return 0;
		}

		$row = $db->raw_query_result_rowdata($result, 0);
		return $row['rsnstatisticsfieldsid'];
	}

	public static function getLastUpdate($parentModuleName, $crmid = false) {
		$db = PearDatabase::getInstance();
		$relatedStatsTablesNames = self::getRelatedStatsTablesNames($parentModuleName, true);
		$last_update = false;

		foreach($relatedStatsTablesNames as $mainTable) {
			$params = [];
			$sql = "SELECT MIN(`" . $mainTable . "`.`last_update`) last_update FROM `" . $mainTable . "`
			   WHERE ";

			if ($crmid) {
				$sql .= "`" . $mainTable . "`.`crmid` = ?";
				$params[] = $crmid;
			}

			$sql .= " LIMIT 1";

			$result = $db->pquery($sql, $params);
			$numberOfRecords = $db->num_rows($result);
			if ($numberOfRecords <= 0) {
				//tmp error !
				return false;
			}

			$update_date = $db->raw_query_result_rowdata($result, 0)["last_update"];

			if (!$last_update || $update_date < $last_update) {
				$last_update = $update_date;
			}
		}

		return $last_update;
	}

	public static function getRelationQuery($parentModuleName, $crmid, $statisticId = false) {
		$aggregate = !$crmid || !is_numeric($crmid);
		$rows = "";
		$first = true;
		$relatedStatsFieldsRecordModels = self::getRelatedStatsFieldsRecordModels($statisticId, $parentModuleName);
		
		foreach($relatedStatsFieldsRecordModels as $field) {
			$code = $field->get('uniquecode');
			if($aggregate){
				$aggregateFunction = $field->get('aggregatefunction');
				if(!$aggregateFunction)
					$aggregateFunction = 'SUM';
				$code = "$aggregateFunction(`$code`) AS `$code`";
			}
			if ($first) {
				$rows .= $code;
				$first = false;
			} else {
				$rows .= ', ' . $code;
			}
		}
		
		//$parentFocus = CRMEntity::getInstance($parentModuleName);

		$query = "";
		$first = true;
		$firstTableName = "";
		$relatedStatsTablesNames = self::getRelatedStatsTablesNames($parentModuleName, true);
		//foreach($relatedStatsTablesNames as $mainTable) {
		//	if ($first) {
		//		$first = false;
		//	} else {
		//		$query .= " UNION ";
		//	}
		foreach($relatedStatsTablesNames as $mainTable) {
			break;
		}
		if($mainTable){
			$query .= "SELECT " .
					"`" . $mainTable . "`.id, `" . $mainTable . "`.name, `" . $mainTable . "`.begin_date, `" . $mainTable . "`.end_date, `" . $mainTable . "`.rsnfiltrestatistiqueid, `" . $mainTable . "`.filterid, ". $rows;
					//" FROM `" . $mainTable . "`";
			//Se base sur les entités parentes car $mainTable ne fait pas forcément référence à toutes les entités
			// ce qui n'est pas nécessaire si !$aggregate ou si $crmid TODO
			$query .= " FROM vtiger_crmentity
				LEFT OUTER JOIN `" . $mainTable . "`
				ON `" . $mainTable . "`.crmid = vtiger_crmentity.crmid
			";
			foreach($relatedStatsTablesNames as $relatedStatsTableName) {
				if ($relatedStatsTableName != $mainTable) { 
					$query .= " LEFT OUTER JOIN `" . $relatedStatsTableName .
								"` ON `" . $relatedStatsTableName . "`.`code` = `" . $mainTable . "`.`code`" . 
								" AND `" . $relatedStatsTableName . "`.`crmid` = `" . $mainTable . "`.`crmid`";
				}
			}
			$queryWhere = " WHERE vtiger_crmentity.setype = '$parentModuleName'
				AND vtiger_crmentity.deleted = FALSE";
			if(!$aggregate){
				$queryWhere .= " AND `" . $mainTable . "`.crmid=" . $crmid;
				$queryGroupBy = '';
			}
			else {
				if($crmid){
					//provient du parametre &related_viewname= de la requête
					if(strcasecmp(substr($crmid,0,5), 'VIEW:') === 0){
						$viewId = substr($crmid,5);
						global $current_user;
						$queryGenerator = new QueryGenerator($parentModuleName, $current_user);
						$queryGenerator->initForCustomViewById($viewId);
						$queryGenerator->setFields(array('id'));
						$relatedFieldIdName = explode('.', $queryGenerator->getSQLColumn('id'))[1];
						$crmid = $queryGenerator->getQuery();
						//var_dump($crmid);
					}
					if(strcasecmp(substr($crmid,0,7), 'SELECT ') === 0){
						if(empty($relatedFieldIdName)){
							$relatedFieldIdName = 'crmid';
							//Teste l'existance de vtiger_crmentity.crmid : TODO non fiable si requête complexe
							if(!preg_match('/^\s*SELECT\s+[\s\S]*(vtiger_crmentity\.crmid)[\s\S]*\sFROM\s/i')){
								$crmid = preg_replace('/^\s*SELECT\s+/i', 'SELECT vtiger_crmentity.crmid, ', $crmid);
							}
						}
						$query .= " JOIN ($crmid) _related_source
							ON `" . $mainTable . "`.crmid = _related_source." . $relatedFieldIdName ;
					}
					elseif($crmid){
						//TODO
						$queryWhere .= " AND `" . $mainTable . "`.crmid=" . $crmid;
					}
					/*var_dump($query);
					die();*/
				}
				$queryGroupBy .= "GROUP BY `" . $mainTable . "`.name, `" . $mainTable . "`.begin_date, `" . $mainTable . "`.end_date";
			}
			$query .= "
				$queryWhere
				$queryGroupBy
			";
		}

		$query .= " ORDER BY `end_date` DESC";
		//echo "<pre>$query</pre>";
		//die();
		return $query;
	}
}