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
		$relatedModuleName = $request->get('relatedmodule');
		$moduleName = $request->getModule();
		$crmids = $request->get('crmid');
		$statId = $request->get('record');
		
		ob_start();
		if ($mode == 'all') {
		   $this->updateAll($request);
		} else {
		   $this->updateOne($request);
		}
		
		ob_end_clean();
		
		if(is_numeric($crmids) && $crmids != '0')
			$url = "index.php?module=" . $relatedModuleName . "&relatedModule=" . $moduleName . "&view=Detail&record=" . $crmids . "&mode=showRelatedList&tab_label=" . $moduleName;
		else
			$url = "index.php?module=" . $moduleName . "&relatedModule=" . $moduleName . "&view=Detail&record=" . $statId . "&mode=showRelatedList&tab_label=" . $moduleName;
		
		header('Location: ' . $url);
		
	}

	// AV150000
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
					$months = array('Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre');//tmp hardcode !!!!
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
	
	/** ED151017
	 * Retourne tous les éléments pour construire les requêtes de mise à jour
	 *
	 */
	function getStatsQueriesPeriods($relatedModuleName, $beginDate) {
		$db = PearDatabase::getInstance();
		$relatedStatistics = RSNStatistics_Utils_Helper::getRelatedStatisticsRecordModels($relatedModuleName);
		$statistics = array();
		foreach ($relatedStatistics as $relatedStatistic) {
			$statTableName = RSNStatistics_Utils_Helper::getStatsTableName($relatedStatistic->getId(), $relatedStatistic);
			//var_dump($relatedStatistic->getId(), $relatedStatistic->get('rsnstatisticsid'), $relatedStatistic, $statTableName);
			$periodicities = explode(' |##| ', $relatedStatistic->get('stats_periodicite'));//Annuelle | Mensuelle | Exercice // tmp hardcode !!

			$periods = array();
			foreach ($periodicities as $periodicity) {
				switch ($periodicity) {
				case 'Annuelle' :
					//Si on commence après le 1er janvier, on passe à l'année suivante (conséquence de Exercice)
					if (date("n", $beginDate) > 1 || date("d", $beginDate) > 1)
						$beginYears = (int) date("Y", $beginDate) + 1;
					else
						$beginYears = (int) date("Y", $beginDate);
					$endYears = (int) date("Y");

					for ($years = $beginYears; $years <= $endYears; ++$years) {
						$periods[] = $this->createPeriodParam( $years, 'Annuelle-' . $years, $years . "-01-01 00:00:00", ($years + 1) . "-01-01 00:00:00");
					}
					break;
				case 'Mensuelle' :
					//Si on commence après le 1er janvier, on passe à l'année suivante (conséquence de Exercice)
					if (date("n", $beginDate) > 1 || date("d", $beginDate) > 1)
						$beginYears = (int) date("Y", $beginDate) + 1;
					else
						$beginYears = (int) date("Y", $beginDate);
					$endYears = (int) date("Y");
					$months = array('Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre');//tmp hardcode !!!!
					for ($years = $beginYears; $years <= $endYears; ++$years) {
						foreach ($months as $monthId => $month) {
							$end_date = ($monthId == 11) ? ($years + 1) . "-" . "01-01 00:00:00" : $years . "-" . ($monthId + 2) . "-01 00:00:00";
							$periods[] = $this->createPeriodParam( $month . ' ' . $years, 'Mensuelle-'. $month . $years, $years . "-" . ($monthId+ 1) . "-01 00:00:00", $end_date);
						}
					}
					break;
				case 'Exercice' :
					$exerciceMonth = $this->getExerciceFirstMonth();
					//Si on commence après le 1er jour de l'exercice, on passe à l'année suivante (conséquence de Exercice)
					if (date("n", $beginDate) > $exerciceMonth || (date("n", $beginDate) == $exerciceMonth && date("d", $beginDate) > 1))
						$beginYears = (int) date("Y", $beginDate) + 1;
					else
						$beginYears = (int) date("Y", $beginDate);
					$beginYears = (int) date("Y", $beginDate);
					$endYears = (int) date("Y");

					for ($years = $beginYears; $years <= $endYears; ++$years) {
						$periods[] = $this->createPeriodParam( $years .'-'.($years+1), 'Exercice-' . $years, $years . "-09-01 00:00:00", ($years + 1) . "-09-01 00:00:00");
					}
					break;
				}
			}

			if (sizeof($periods) > 0) {
				//Queries
				$queries = array();
				$relatedStatisticsFields = RSNStatistics_Utils_Helper::getRelatedStatsFields($relatedStatistic->getId());// tmp get fileds of oll related stats !!
				//Chaque Champ de stat
				foreach ($relatedStatisticsFields as $relatedStatisticsField) {
					$queryId = $relatedStatisticsField['rsnsqlqueriesid'];
					if(!array_key_exists($queryId, $queries)){
						$queries[$queryId] = array(
												   'record' => Vtiger_Record_Model::getInstanceById($queryId),
													'periods' => $periods,
													'fields' => array($relatedStatisticsField),
											);
					}
					else
						$queries[$queryId]['fields'][] = $relatedStatisticsField;
				}
				
				$statistics[] = array(
					'record' => $relatedStatistic,
					'table' => $statTableName,
					'queries' => $queries
				);
			}
		}

		//exit;
		return $statistics;
	}
	
	// Mois du début d'un exercice
	function getExerciceFirstMonth(){
		return 9; //Septembre TODO param
	}
	function getDefaultBeginYear(){
		return date('Y') - 5; //TODO param
	}

	//AV
	function createRow($crmid, $name, $code, $begin_date, $end_date) {
		return array('crmid'=> $crmid, 'name' => $name, 'code' => $code, 'begin_date' => $begin_date, 'end_date' => $end_date);
	}

	//ED
	function createPeriodParam($name, $code, $begin_date, $end_date) {
		return array('name' => $name, 'code' => $code, 'begin_date' => $begin_date, 'end_date' => $end_date);
	}

	function updateOne(Vtiger_Request $request) {//tmp update for date between - after - ... / existing row / current / ...
		
		set_time_limit(2 * 60 * 60); //TODO
		
		$db = PearDatabase::getInstance();
		$crmids = $request->get('crmid');
		$relatedModuleName = $request->get('relatedmodule');
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		
		$beginDate = $request->get('begin_date');
		switch($beginDate){
		case 'this year':
		case 'this_year':
		case 'year':
			$exerciceMonth = $this->getExerciceFirstMonth();
			$beginDate = mktime(0, 0, 0, 1, $exerciceMonth, date('Y')-1);
			break;
		default :
			$beginDate = mktime(0, 0, 0, 1, 1, $this->getDefaultBeginYear()); 
			break;
		}
		
		$statistics = $this->getStatsQueriesPeriods($relatedModuleName, $beginDate);
		$isAllEntities = $crmids === '*';
		if($isAllEntities){
			$crmids = "SELECT crmid FROM vtiger_crmentity WHERE vtiger_crmentity.deleted=0";
			if($relatedModuleName)
				$crmids .= " AND vtiger_crmentity.setype='$relatedModuleName'";
			else {
				$crmids .= " AND vtiger_crmentity.setype IN (";
				$relatedModuleNames = array();
				$nStat = 0;
				foreach ($statistics as $statistic){
					$relatedStatistic = $statistic['record'];
					$relatedModuleName = $relatedStatistic->get('relmodule');
					if(!array_key_exists($relatedModuleName, $relatedModuleNames)){
						$relatedModuleNames[$relatedModuleName] = true;
						if($nStat++ > 0)
							$crmids .= ", ";
						$crmids .= "'$relatedModuleName'";
					}
				}
				$crmids .= ")";
				$relatedModuleName = array_keys($relatedModuleNames);
				
			}
			//var_dump($statistics, $relatedModuleName, $crmids);
		}
		
		if($perf = !is_numeric($crmids))
			$perf = new RSN_Performance_Helper();
		
		
		$db = PearDatabase::getInstance();
		
		//$this->initStatsRows($relatedModuleName, $crmid, mktime(0, 0, 0, 1, 1, 2009));//TMP !!!
		//var_dump($relatedModuleName, $statistics);
		//exit();
		//Chaque stat
		foreach ($statistics as $statistic){
			$relatedStatistic = $statistic['record'];
			$statTableName = $statistic['table'];
			
			$cleanedPeriods = array();

			if($isAllEntities)
				$this->cleanStatTable($statTableName);
			
			//Insert
			$statisticResults = array();
			foreach ($statistic['queries'] as $statQuery){
				$relatedStatisticsFields = $statQuery['fields'];
				$sqlqueryRecord = $statQuery['record'];
						
				//Chaque période
				foreach ($statQuery['periods'] as $statPeriod){
					$name = $statPeriod['name'];
					$code = $statPeriod['code'];
					$end_date = $statPeriod['end_date'];
					$begin_date = $statPeriod['begin_date'];
					
					if(!array_key_exists($code, $cleanedPeriods)){
						$this->clearStatTableData($statTableName, $code, $isAllEntities ? false : $crmids);
						$cleanedPeriods[$code] = true;
					}
					
					$executionQuery = $sqlqueryRecord->getExecutionQuery(array('crmid'=>$crmids, 'begin_date'=>$begin_date, 'end_date'=>$end_date));
					
					//Insert
					$params = array();
					$insertQuery = "INSERT INTO $statTableName ( crmid, name, code, begin_date, end_date, last_update";
					
					//Stat Fields
					//Chaque Champ de stat
					foreach ($relatedStatisticsFields as $relatedStatisticsField) {
						$insertQuery .= ", `".$relatedStatisticsField['uniquecode']."`";
					}
					
					//Select Données source
					$insertQuery .= ")
					/* ".$relatedStatistic->getName()." -> $name */
					SELECT source.crmid, ?, ?, ?, ?, NOW()";
					$params[] = $name;
					$params[] = $code;
					$params[] = $begin_date;
					$params[] = $end_date;
					
					//Stat Fields
					//Chaque Champ de stat
					foreach ($relatedStatisticsFields as $relatedStatisticsField) {
						$insertQuery .= ", source.`".$relatedStatisticsField['uniquecode']."`";
					}
					
					//From source
					$insertQuery .= "
						FROM (" . $executionQuery['sql'] . ") source";
					$params = array_merge($params, $executionQuery['params']);
					
					//ON DUPLICATE (rappel : index unique sur crmid, code)
					$insertQuery .= "
					ON DUPLICATE KEY UPDATE last_update = NOW()";
					//Stat Fields
					//Chaque Champ de stat
					foreach ($relatedStatisticsFields as $relatedStatisticsField) {
						$insertQuery .= ", `".$relatedStatisticsField['uniquecode']."` = source.`".$relatedStatisticsField['uniquecode']."`";
					}
					
					if($perf) $perf->tick();
					$result = $db->pquery($insertQuery, $params);
					if($perf) $perf->tick();
					if(!$result){
						$db->echoError();
						echo "<pre>$insertQuery</pre>";
						var_dump($params);
						die("ERREUR D'EXECUTION DE LA REQUETE DE STATISTIQUE");
					}
					else{
						echo "<pre>$insertQuery</pre>";
						var_dump($params);
						$affectedRows = $db->getAffectedRowCount($result);
						var_dump("OK : ".$relatedStatistic->get('name')." -> $name : $affectedRows modification(s).");
					}
				}
			}
		}
		if($perf) $perf->terminate();
		//exit;
	}

	function updateAll(Vtiger_Request $request) {
		$request->set('crmid', '*');
		return $this->updateOne($request);
	}
	
	// from cron task
	public static function runScheduledUpdate(){
		$controller = new self();
		$request = new Vtiger_Request();
		$request->set('crmid', '*');
		$controller->updateOne($request);
	}
	
	//Supprime les éléments de stats relatifs à des entités qui n'existent plus
	function cleanStatTable($tableName){
		//Delete
		$deleteQuery = "DELETE $tableName
			FROM $tableName
			LEFT JOIN vtiger_crmentity
				ON $tableName.crmid = vtiger_crmentity.crmid
			WHERE IFNULL(vtiger_crmentity.deleted, 1) = 1";
		
		$db = PearDatabase::getInstance();
		$result = $db->query($deleteQuery);
		if(!$result){
			$db->echoError();
			echo "<pre>$deleteQuery</pre>";
			die("ERREUR D'EXECUTION DE LA REQUETE DE NETTOYAGE DE STATISTIQUE");
		}
	}
	
	//Supprime les enregistrements de stats pour une période donnée
	function clearStatTableData($statTableName, $periodCode, $crmids){
		$db = PearDatabase::getInstance();
		
		//Delete
		$deleteParams = array();
		$deleteQuery = "DELETE $statTableName
			FROM $statTableName
			WHERE code = ?";
		
		if($crmids)
			$deleteQuery .= " AND crmid IN (" . $crmids . ")";
			
		$deleteParams = array($periodCode);
	
		$result = $db->pquery($deleteQuery, $deleteParams);
		if(!$result){
			$db->echoError();
			echo "<pre>$deleteQuery</pre>";
			var_dump($deleteParams);
			die("ERREUR D'EXECUTION DE LA REQUETE DE PURGE DE STATISTIQUE");
		}
	}
}
