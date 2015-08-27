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
			'Potentials' => true,
			'Products' => false,
			'Services' => false,
			
			'Critere4D' => true,
			'RsnPrelevements' => true,
			'RsnPrelVirement' => true,
			'RsnReglements' => true,
			//never 'RSNMedias' => false,
			//never 'RSNMediaContacts' => false,
			//never 'RSNMediaRelations' => false,
			//never 'RSNContactsPanels' => false,
			//never 'RSNPanelsVariables' => false,
			'RSNBanques' => true,
			'RSNBanqAgences' => true,
			'RSNAboRevues' => true,
			'RSNDonateursWeb' => true,
			
			'Calendar' => false,
			'Emails' => false,
			//never 'Faq' => false,
			'Events' => false,
			'Leads' => false,
			'PriceBooks' => false,
			//never 'Campaigns' => false,
			//not standard 'ModTracker' => true,
			'ServiceContracts' => false,
			'Assets' => false,
			'ProjectMilestone' => false,
			'ProjectTask' => false,
			'Project' => false,
			'ModComments' => false,
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
		
	}
	
	function doPurgeModule($moduleModel, $delete_records = false){
		global $adb;
		
		$moduleName = $moduleModel->getName();
		$focus = CRMEntity::getInstance($moduleName);
		echo '<li><h4>' . vtranslate($moduleName, $moduleName) . ' ( ' . $moduleName . ' )</h4>';
		echo '<ul>';
		$queriesParams = array();
		$queries = $this->getModuleQueries($moduleModel, $focus, $queriesParams);
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
		$queries = array();
		
		$moduleName = $moduleModel->getName();
		
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
}