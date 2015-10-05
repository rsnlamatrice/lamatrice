<?php

class RSNStatistics_Update_Action extends Vtiger_Action_Controller {
	
	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'Export')) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		$result = array();

		 if ($mode == 'all') {
		 	$this->updateAll($request);
		 } else {
		 	$this->updateOne($request);
		 }
	}

	function initStatsRows($relatedModuleName, $crmid, $beginDate) {
		$db = PearDatabase::getInstance();
		$relatedStatistics = RSNStatistics_Utils_Helper::getRelatedStatistics($relatedModuleName);
		foreach ($relatedStatistics as $relatedStatistic) {
			$statTableName = RSNStatistics_Utils_Helper::getStatsTableName($relatedStatistic['rsnstatisticsid'], $relatedStatistic);
			$existingStatsRows = RSNStatistics_Utils_Helper::getRelatedStatsValues($relatedStatistic, $crmid);

			//check existing
			$existingStatsRowsCodes = array();
			foreach ($existingStatsRows as $existingStatsRow) {
				$code = explode('-', $existingStatsRow['code']);
				$existingStatsRowsCodes[$code[0]] = ($existingStatsRowsCodes[$code[0]]) ? $existingStatsRowsCodes[$code[0]] : array();
				$existingStatsRowsCodes[$code[0]][] = $code[1];
			}

			$periodicities = explode(' |##| ', $relatedStatistic['stats_periodicite']);//Annuelle | Mensuelle | Exercice // tmp hardcode !!

			$newRows = array();
			foreach ($periodicities as $periodicity) {
				if ($periodicity == 'Annuelle') {
					//tmp begin date !!!!!!
					$beginYears = (int) date("Y", $beginDate);
					$endYears = (int) date("Y");

					for ($years = $beginYears; $years <= $endYears; ++$years) {
						if (! in_array($years, $existingStatsRowsCodes[$periodicity])) {
							//echo 'unknow ' . $years . '<br/>';
							$newRows[] = $this->createRow($crmid, $years, 'Annuelle-' . $years, $years . "-01-01 00:00:00", ($years + 1) . "-01-01 00:00:00");
						}
					}
				} else if ($periodicity == 'Mensuelle') {
					$beginYears = (int) date("Y", $beginDate);
					$endYears = (int) date("Y");
					$months = ['Janvier', 'Fevrier', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Decembre'];//tmp hardcode !!!!
					for ($years = $beginYears; $years <= $endYears; ++$years) {
						foreach ($months as $monthId => $month) {
							if (! in_array($month . $years, $existingStatsRowsCodes[$periodicity])) {
								//echo 'unknow ' . $month . $years . '<br/>';
								$end_date = ($monthId == 11) ? ($years + 1) . "-" . "01-01 00:00:00" : $years . "-" . ($monthId + 2) . "-01 00:00:00";
								$newRows[] = $this->createRow($crmid, $month . ' ' . $years, 'Mensuelle-'. $month . $years, $years . "-" . ($monthId+ 1) . "-01 00:00:00", $end_date);
							}
						}
					}
				} else if ($periodicity == 'Exercice') {
					//tmp todo !!
				}
			}

			//insert new rows !!
			$first = true;
			if (sizeof($newRows) > 0) {
				$insertQuery = 'INSERT INTO ' . $statTableName . '(`crmid`, `name`, `code`, `begin_date`, `end_date`) VALUES ';//'INSERT INTO t2 (b, c) VALUES ((SELECT a FROM t1 WHERE b='Chip'), 'shoulder'),'
				$queryParams = array();
				foreach ($newRows as $newRow) {
					if (!$first) {
						$insertQuery .= ', ';
					} else {
						$first = false;
					}

					$insertQuery .= '(?, ?, ?, ?, ?)';
					$queryParams[] = $newRow['crmid'];
					$queryParams[] = $newRow['name'];
					$queryParams[] = $newRow['code'];
					$queryParams[] = $newRow['begin_date'];
					$queryParams[] = $newRow['end_date'];
				}

				$db->pquery($insertQuery, $queryParams);
			}
		}

		//exit;
	}

	function createRow($crmid, $name, $code, $begin_date, $end_date) {
		return array('crmid'=> $crmid, 'name' => $name, 'code' => $code, 'begin_date' => $begin_date, 'end_date' => $end_date);
	}

	function updateOne(Vtiger_Request $request) {//tmp update for date between - after - ... / existing row / current / ...
		$db = PearDatabase::getInstance();
		$crmid = $request->get('crmid');
		$relatedModuleName = $request->get('relatedmodule');
		$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModuleName);
		$relatedModule = CRMEntity::getInstance($relatedModuleName);
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		
		$this->initStatsRows($relatedModuleName, $crmid, mktime(0, 0, 0, 1, 1, 2009));//TMP !!!
		
		$relatedStatistics = RSNStatistics_Utils_Helper::getRelatedStatistics($relatedModuleName);
		foreach ($relatedStatistics as $relatedStatistic) {
			$existingStatsRows = RSNStatistics_Utils_Helper::getRelatedStatsValues($relatedStatistic, $crmid);

			$relatedStatisticsFields = RSNStatistics_Utils_Helper::getRelatedStatsFields($relatedStatistic['rsnstatisticsid']);// tmp get fileds of oll related stats !!
			$statsFieldsValues = array();

			foreach ($relatedStatisticsFields as $relatedStatisticsField) {
				$sqlquery = Vtiger_Record_Model::getInstanceById($relatedStatisticsField['rsnsqlqueriesid']);
				foreach ($existingStatsRows as $existingStatsRow) {
					$executionQuery = $sqlquery->getExecutionQuery(['crmid'=>$crmid, 'begin_date'=>$existingStatsRow['begin_date'], 'end_date'=>$existingStatsRow['end_date']]);
					$db = PearDatabase::getInstance();
					$result = $db->pquery($executionQuery['sql'], $executionQuery['params']);
					$num_rows = $db->num_rows($result);

					if ($num_rows == 1) {
						$statValue = $db->query_result($result, 0);
						$statsFieldsValues[$existingStatsRow['id']] = ($statsFieldsValues[$existingStatsRow['id']]) ? array() : $statsFieldsValues[$existingStatsRow['id']];
						$statsFieldsValues[$existingStatsRow['id']][$relatedStatisticsField['uniquecode']] = $statValue;
					} else {
						//error tmp (too much or no row)
					}
				}

				//persist update for one row
				$statTableName = RSNStatistics_Utils_Helper::getStatsTableName($relatedStatistic['rsnstatisticsid'], $relatedStatistic);
				foreach($statsFieldsValues as $statRowId => $rowStatsFieldsValues) {
					$sqlUpdateQuery = "UPDATE " . $statTableName . " SET `last_update` = NOW()";
					$queryParams = array();

					foreach ($rowStatsFieldsValues as $uniquecode => $value) {
						$sqlUpdateQuery .= ", `" . $uniquecode . "` = ?";
						$queryParams[] = $value;
					}

					$sqlUpdateQuery .= " WHERE `" . $statTableName . "`.`id` = ?;";
					$queryParams[] = $statRowId;
					$db->pquery($sqlUpdateQuery, $queryParams);
				}
			}
		}
//		exit;
		header('Location: ' . "index.php?module=" . $relatedModuleName . "&relatedModule=" . $moduleName . "&view=Detail&record=" . $crmid . "&mode=showRelatedList&tab_label=" . $moduleName);
	}

	function updateAll() {

	}
}
