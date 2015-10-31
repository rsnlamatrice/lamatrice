<?php
/*+***********************************************************************************
 * Affiche la page d'un outil
 * La liste est affichée dans le bandeau vertical de gauche,
 * 	initialisée dans Module.php::getSideBarLinks()
 *************************************************************************************/

class RSN_GrandePurge_View extends Vtiger_Index_View {
	
	/* les url doivent contenir le paramètre sub désignant l'outil à afficher */
	public function process(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		
		$allModuleModels = Vtiger_Module_Model::getAll();
		$moduleModels = array();
		foreach($allModuleModels as $moduleModel)
			$moduleModels[$moduleModel->getName()] = $moduleModel;
		
		$purgeables = array(
			'Contacts' => true,
			'ContactAddresses' => true,
			'ContactEmails' => true,
			'Accounts' => true,
			'Invoice' => true,
			'SalesOrder' => true,
			'Quotes' => true,
			'Vendors' => true,
			'PurchaseOrder' => true,
			'Potentials' => false,
			'Products' => false,
			'Services' => false,
			
			'Critere4D' => false,
			'RsnPrelevements' => true,
			'RsnPrelVirement' => true,
			'RsnReglements' => true,
			//never 'RSNMedias' => false,
			//never 'RSNMediaContacts' => false,
			//never 'RSNMediaRelations' => false,
			//never 'RSNContactsPanels' => false,
			//never 'RSNPanelsVariables' => false,
			'RSNBanques' => false,
			'RSNBanqAgences' => false,
			'RSNAboRevues' => true,
			'RSNDonateursWeb' => true,
			
			'Calendar' => false,
			'Emails' => false,
			//never 'Faq' => false,
			'Events' => false,
			'Leads' => false,
			'PriceBooks' => false,
			//never 'Campaigns' => false,
			'ServiceContracts' => false,
			'Assets' => false,
			'ProjectMilestone' => false,
			'ProjectTask' => false,
			'Project' => false,
			'ModComments' => true,
			
			'ModTracker' => true,//not standard 
			'RSNStatisticsResults' => true,//not standard 
		);
		if($request->get('doAction')){
			foreach($purgeables as $module_name=>$value)
				$purgeables[$module_name] = $request->get('module-' . $module_name);
			$this->doPurgeModules($purgeables, $request->get('doAction'));
		}
		$orderedModuleModels = array();
		foreach($purgeables as $module_name=>$value)
			$orderedModuleModels[$module_name] = $moduleModels[$module_name];
		
		$viewer->assign('MODULES', $orderedModuleModels);
		$viewer->assign('PURGEABLES', $purgeables);
		$viewer->view('Outils/GrandPurge.tpl', $request->getModule());
	}
	
	function doPurgeModules($purgeableNames, $action){
		
		$moduleModels = Vtiger_Module_Model::getAll();
		foreach($moduleModels as $moduleModel)
			if($purgeableNames[$moduleModel->getName()])
				switch($action){
				 case 'Count':
					if(!$this->doPurgeModule($moduleModel, false)){
						return;
					}
					break;
				 case 'PurgeModules':
					if(!$this->doPurgeModule($moduleModel, true)){
						return;
					}
					break;
				}
		switch($action){
		 case 'Count':
			if(!$this->doPurgeModule(false, false)){
				return;
			}
			break;
		 case 'PurgeModules':
			if(!$this->doPurgeModule(false, true)){
				return;
			}
			break;
		}
	}
	
	function doPurgeModule($moduleModel, $delete_records = false){
		global $adb;
		
		$queriesParams = array();
		
		if($moduleModel){
			$moduleName = $moduleModel->getName();
			$focus = CRMEntity::getInstance($moduleName);
			echo '<li><h4>' . vtranslate($moduleName, $moduleName) . ' ( ' . $moduleName . ' )</h4>';
			echo '<ul>';
			$queries = $this->getModuleQueries($moduleModel, $focus, $queriesParams);
		}
		else {
			$queries = array();
			echo '<li><h4>Relations</h4>';
			echo '<ul>';
		}
		$relatedQueries = $this->getRelationsQueries($moduleModel, $queriesParams);
		$queries = array_merge($queries, $relatedQueries);
		$nTable = 0;
		foreach($queries as $tab_name => $query){
			$params = $queriesParams[$tab_name];
			$query = 'SELECT COUNT(*) FROM (' . $query . ') _subq_';
			$result = $adb->pquery($query, $params);
			if(!$result){
				$adb->echoError($query);
				return false;
			}
			$nRowsCount = $adb->getRowCount($result);
			if($nRowsCount){
				$counter = $adb->query_result($result, 0);
				if($counter
				|| !$delete_records
				|| !array_key_exists($tab_name, $relatedQueries))
					echo '<li>' . $tab_name . ' : ' . $counter;
			}
			
			if($delete_records){
				//Pour de vrai, dans la base !
				if($tab_name == 'vtiger_' . strtolower($moduleName)
				|| $tab_name == 'vtiger_' . strtolower($moduleName) . 'cf'){
					$params = array();
					$query = 'DELETE FROM '.$tab_name;
				}
				elseif($tab_name == 'vtiger_crmentity'){
					$params = array($moduleName);
					$query = 'DELETE FROM '.$tab_name
						. ' WHERE setype = ?';
				}
				elseif($moduleName == 'ModTracker'
					|| array_key_exists($tab_name, $relatedQueries)){
					$params = array();
					$query = preg_replace('/^[\s\S]+\sFROM\s/i', 'DELETE '.$tab_name.' FROM ', $queries[$tab_name]);
				}
				elseif($moduleName == 'RSNStatisticsResults'){
					$params = array();
					$query = 'DELETE FROM '.$tab_name;
				}
				else {
					$params = $queriesParams['vtiger_crmentity'];
					$query = $queries['vtiger_crmentity'];
					if($tab_name != 'vtiger_crmentity'){
						$query = 'DELETE FROM '.$tab_name.'
							WHERE ' . $tab_name . '.' . $focus->tab_name_index[$tab_name] . '
								IN (' . $query . ')';
					}
				}
				//echo '<li>SQL : ' . $query;
				if($delete_records){
					$result = $adb->pquery($query, $params);
					if(!$result){
						$adb->echoError($query);
						return false;
					}
					echo '<ul><li>Supprimées : ' . $adb->getAffectedRowCount($result).'</ul>';
				}
			}
			
			$nTable++;
		}
		echo '</ul>';
		echo '</li>';
		return true;
	}
	function getModuleQueries($moduleModel, $focus, &$queriesParams){
		
		$moduleName = $moduleModel->getName();
		
		$methodName = 'get'.$moduleName.'Queries';
		if(method_exists($this, $methodName))
			return $this->$methodName($moduleModel, $focus, $queriesParams);
		
		$queries = array();
		
		//reverse order for deleting to finish with vtiger_crmentity
		for($nTable = count($focus->tab_name) - 1; $nTable >= 0; $nTable--){
			$tab_name = $focus->tab_name[$nTable];
			
			$params = array();
			$query = 'SELECT ' . $tab_name . '.' . $focus->tab_name_index[$tab_name] . ' as crmid
				FROM ' . $tab_name;
			if($tab_name == 'vtiger_crmentity'){
				$query .= ' WHERE vtiger_crmentity.setype = ?';
				$params[] = $moduleName;
			}
			elseif($nTable > 1) {
				$query .= ' JOIN ' . $focus->tab_name[1] . '
					ON ' . $focus->tab_name[1] . '.' . $focus->tab_name_index[$focus->tab_name[1]] . ' = ' . $tab_name . '.' . $focus->tab_name_index[$tab_name] . '
					';
			}
			
			if($moduleName == 'Vendors'){
				if(preg_match('/\sWHERE\s/i', $query))
					$query .= ' AND ';
				else
					$query .= ' WHERE ';
				$query .= '' . $tab_name . '.' . $focus->tab_name_index[$tab_name] . ' <> 203019'; //RSdN
			}
			
			$queries[$tab_name] = $query;
			$queriesParams[$tab_name] = $params;
			
		}
		return $queries;
	}
	
	function getRelationsQueries($moduleModel, &$queriesParams){
		$queries = array();
		
		if(!$moduleModel){
			$tab_name = 'vtiger_senotesrel';
			$params = array();
			$query = 'SELECT vtiger_senotesrel.crmid
				FROM vtiger_senotesrel
				LEFT JOIN vtiger_crmentity
					ON vtiger_senotesrel.crmid = vtiger_crmentity.crmid
				LEFT JOIN vtiger_crmentity AS vtiger_crmentity2
					ON vtiger_senotesrel.notesid = vtiger_crmentity2.crmid
				WHERE vtiger_crmentity.crmid IS NULL OR vtiger_crmentity.deleted = 1
				OR vtiger_crmentity2.crmid IS NULL OR vtiger_crmentity2.deleted = 1';
				
			$queries[$tab_name] = $query;
			$queriesParams[$tab_name] = $params;
		}
		if($moduleModel && ($moduleModel->getName() === 'Contacts' || $moduleModel->getName() === 'Critere4D')){
			$tab_name = 'vtiger_critere4dcontrel';
			$params = array();
			$query = 'SELECT vtiger_critere4dcontrel.contactid
				FROM vtiger_critere4dcontrel
				LEFT JOIN vtiger_crmentity
					ON vtiger_critere4dcontrel.contactid = vtiger_crmentity.crmid
				LEFT JOIN vtiger_crmentity AS vtiger_crmentity2
					ON vtiger_critere4dcontrel.critere4did = vtiger_crmentity2.crmid
				WHERE vtiger_crmentity.crmid IS NULL OR vtiger_crmentity.deleted = 1
				OR vtiger_crmentity2.crmid IS NULL OR vtiger_crmentity2.deleted = 1';
				
			$queries[$tab_name] = $query;
			$queriesParams[$tab_name] = $params;
		}
		
		if($moduleModel && ($moduleModel->getName() === 'Contacts' || $moduleModel->getName() === 'Critere4D')){
			$tab_name = 'vtiger_contactscontrel';
			$params = array();
			$query = 'SELECT vtiger_contactscontrel.contactid
				FROM vtiger_contactscontrel
				LEFT JOIN vtiger_crmentity
					ON vtiger_contactscontrel.contactid = vtiger_crmentity.crmid
				LEFT JOIN vtiger_crmentity AS vtiger_crmentity2
					ON vtiger_contactscontrel.relcontid = vtiger_crmentity2.crmid
				WHERE vtiger_crmentity.crmid IS NULL OR vtiger_crmentity.deleted = 1
				OR vtiger_crmentity2.crmid IS NULL OR vtiger_crmentity2.deleted = 1';
				
			$queries[$tab_name] = $query;
			$queriesParams[$tab_name] = $params;
		}
		if(!$moduleModel){		
			$tab_name = 'vtiger_crmentityrel';
			$params = array();
			$query = 'SELECT vtiger_crmentityrel.crmid
				FROM vtiger_crmentityrel
				LEFT JOIN vtiger_crmentity
					ON vtiger_crmentityrel.crmid = vtiger_crmentity.crmid
				LEFT JOIN vtiger_crmentity AS vtiger_crmentity2
					ON vtiger_crmentityrel.relcrmid = vtiger_crmentity2.crmid
				WHERE vtiger_crmentity.crmid IS NULL OR vtiger_crmentity.deleted = 1
				OR vtiger_crmentity2.crmid IS NULL OR vtiger_crmentity2.deleted = 1';
				
			$queries[$tab_name] = $query;
			$queriesParams[$tab_name] = $params;
		}
		return $queries;
	}
	
	function getModTrackerQueries($moduleModel, $focus, &$queriesParams){
		$queries = array();
		
		$moduleName = $moduleModel->getName();
		
		$tab_name = 'vtiger_modtracker_basic';
		$params = array();
		$query = 'SELECT vtiger_modtracker_basic.id
			FROM vtiger_modtracker_basic
			LEFT JOIN vtiger_crmentity
				ON vtiger_modtracker_basic.crmid = vtiger_crmentity.crmid
			WHERE vtiger_crmentity.crmid IS NULL OR vtiger_crmentity.deleted = 1';
			
		$queries[$tab_name] = $query;
		$queriesParams[$tab_name] = $params;
			
		$tab_name = 'vtiger_modtracker_relations';
		$params = array();
		$query = 'SELECT vtiger_modtracker_relations.id
			FROM vtiger_modtracker_relations
			LEFT JOIN vtiger_modtracker_basic
				ON vtiger_modtracker_relations.id = vtiger_modtracker_basic.id
			LEFT JOIN vtiger_crmentity
				ON vtiger_modtracker_relations.targetid = vtiger_crmentity.crmid
			WHERE vtiger_crmentity.crmid IS NULL OR vtiger_crmentity.deleted = 1
			OR vtiger_modtracker_basic.id IS NULL';
			
		$queries[$tab_name] = $query;
		$queriesParams[$tab_name] = $params;
			
		$tab_name = 'vtiger_modtracker_detail';
		$params = array();
		$query = 'SELECT vtiger_modtracker_detail.id
			FROM vtiger_modtracker_detail
			LEFT JOIN vtiger_modtracker_basic
				ON vtiger_modtracker_detail.id = vtiger_modtracker_basic.id
			LEFT JOIN vtiger_crmentity
				ON vtiger_modtracker_basic.id = vtiger_crmentity.crmid
			WHERE vtiger_crmentity.crmid IS NULL OR vtiger_crmentity.deleted = 1
			OR vtiger_modtracker_basic.id IS NULL';
			
		$queries[$tab_name] = $query;
		$queriesParams[$tab_name] = $params;
			
		
		return $queries;
	}
	function getRSNStatisticsResultsQueries($moduleModel, $focus, &$queriesParams){
		$queries = array();
		
		$statistics = RSNStatistics_Utils_Helper::getRelatedStatistics(false, true);
		foreach($statistics as $statistic){
			$tab_name = RSNStatistics_Utils_Helper::getStatsTableNameFromId($statistic['id']);
			if(!$tab_name || !RSNStatistics_Utils_Helper::getTableInfo($tab_name))
				continue;
			$query = 'SELECT id FROM '.$tab_name;
			$queries[$tab_name] = $query;
			$queriesParams[$tab_name] = array();
		}
		
		return $queries;
	}
}