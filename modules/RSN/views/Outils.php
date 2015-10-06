<?php
/*+***********************************************************************************
 * Affiche la page d'un outil
 * La liste est affichée dans le bandeau vertical de gauche,
 * 	initialisée dans Module.php::getSideBarLinks()
 *************************************************************************************/
require_once('modules/RSN/models/ImportCogilogFactures.php');
require_once('modules/RSN/models/ImportCogilogAffaires.php');
require_once('modules/RSN/models/ImportCogilogProduitsEtServices.php');
class RSN_Outils_View extends Vtiger_Index_View {
	
	public function preProcess(Vtiger_Request $request, $display = true) {
		$viewer = $this->getViewer($request);
		$viewer->assign('CURRENT_SUB', $request->get('sub'));
		return parent::preProcess($request, $request);
	}
	
	/* les url doivent contenir le paramètre sub désignant l'outil à afficher */
	public function process(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$sub = $request->get('sub', 'List');
		$viewer->assign('CURRENT_SUB', $sub);
		
		$viewer->assign('VIEW_ID', $request->get('viewid') ? $request->get('viewid') : ($request->get('viewname') ? $request->get('viewname') : $request->get('record')));
		$viewer->assign('VIEW_MODULE', $request->get('viewmodule'));
		
		$viewer->assign('CURRENT_USER', $currentUserModel);
		
		$this->process_sub($request, $sub, $viewer);
		
		if($request->get('template'))
			$template = $request->get('template');
		else
			$template = $sub;
		$viewer->view('Outils/' . $template . '.tpl', $request->getModule());
	}
	
	public function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$jsFileNames = array(
			//"modules.Calendar.resources.SharedCalendarView",
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
	
	
	private function process_sub($request, $sub, $viewer){
		
		global $VTIGER_BULK_SAVE_MODE;
		
		$previousBulkSaveMode = $VTIGER_BULK_SAVE_MODE;
		$VTIGER_BULK_SAVE_MODE = false;
		
		
		switch($sub){
		case 'ImportCogilog/Factures':
			
			$this->process_ImportCogilog_Factures($request, $sub, $viewer);
			
			break;
		
		case 'ImportCogilog/Affaires':
			
			$this->process_ImportCogilog_Affaires($request, $sub, $viewer);
			
			break;
		
		case 'ImportCogilog/ProduitsEtServices':
			
			$this->process_ImportCogilog_ProduitsEtServices($request, $sub, $viewer);
			
			break;
		
		case 'ImportCogilog/Clients':
			$tablename = 'SELECT * FROM gclien00002 ORDER BY tssaisie DESC';
			$this->process_PG_DataRowsTable($request, $sub, $viewer, $tablename);
			$request->set('template', 'DataRowsTable');
			break;
		
		case 'DataRowsTable':
			
			$this->process_PG_DataRowsTable($request, $sub, $viewer);
			
			break;
		
		case 'DefineMissingLabels' :
			$this->defineMissingLabels();
			break;
		
		case 'Migration/DefinePrelevementsPeriodicites' :
			$this->definePrelevementsPeriodicites();
			break;
		
		case 'TestsED' :
			$this->freeDebug();
			break;
		
		case 'PicklistValuesTransfer' :
			
			var_dump("Liste des banques");
			$focus = CRMEntity::getInstance('RSNBanques');
			$focus::PicklistValuesTransfer();
			
			
			break;
		
		default:
			$viewer->assign('HTML_DATA', "Inconnu : \"$sub\"");
			break;
		}
		
		$VTIGER_BULK_SAVE_MODE = $previousBulkSaveMode;
	}
	
	/**
	 * 
	 */
	function freeDebug(){
		
		$module = Vtiger_Module_Model::getInstance('PurchaseOrder');
		foreach( $module->getBlocks() as $block1)
			if($block1->get('label') === 'LBL_ADDRESS_INFORMATION')
				break;
		var_dump($block1);
	}
	
	
	private function process_ImportCogilog_Factures($request, $sub, $viewer){
		
		$doneRows = RSN_CogilogFacturesRSN_Import::importNexts();
		
		//$this->process_PG_DataRowsTable($request, $sub, $viewer, $query);
		
		$viewer->assign('DATAROWS', $doneRows);
	}
	
	private function process_ImportCogilog_Affaires($request, $sub, $viewer){
		
		$doneRows = RSN_CogilogAffairesRSN_Import::importNexts();
		
		//$this->process_PG_DataRowsTable($request, $sub, $viewer, $query);
		
		$viewer->assign('DATAROWS', $doneRows);
	}
	
	private function process_ImportCogilog_ProduitsEtServices($request, $sub, $viewer){
		
		$doneRows = RSN_CogilogProduitsEtServices_Import::importNexts();
		
		//$this->process_PG_DataRowsTable($request, $sub, $viewer, $query);
		
		$viewer->assign('DATAROWS', $doneRows);
	}
	
	private function process_PG_DataRowsTable($request, $sub, $viewer, $table_name = FALSE){
		if(!$table_name)
			$table_name = $request->get('tablename');
		$rows = $this->getPGDataRows($table_name);
		if(!is_array($rows)){
			$viewer->assign('HTMLDATA', $rows);
			return;
		}
			
		//$viewer->assign('HTMLDATA', print_r($rows, true));
		$viewer->assign('DATAROWS', $rows);
	}
		
	private function getPGDataRows($table_name){
			
		$dbconn = $this->get_db_connect();
		
		if(stripos($table_name, 'SELECT ') === 0){
			$query = $table_name;
			if(!preg_match('/\sLIMIT\s/i', $query))
				$query .= ' LIMIT 99';
		}
		else
			$query = 'SELECT * FROM ' . $table_name . ' LIMIT 50';
		
		// Exécution de la requête SQL
		$result = pg_query($query);
		if(!$result){
			return '<code>Échec de la requête : ' . pg_last_error() .'</code>'
					. '<br>'.$query;
		}
		
		$rows = array();
		
		while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
		    $rows[] = $line;
		}
		
		// Libère le résultat
		pg_free_result($result);
		
		// Ferme la connexion
		pg_close($dbconn);
		
		return $rows;
	}
	
	private function get_db_connect(){
		// Connexion, sélection de la base de données
		include_once('config.cogilog.php');
		global $cogilog_config;
		$cxString = 'host='.$cogilog_config['db_server'].' port='.$cogilog_config['db_port'].' dbname='.$cogilog_config['db_name'].' user='.$cogilog_config['db_username'].' password='.$cogilog_config['db_password'];
		//echo str_repeat('<br>',5);
		return pg_connect($cxString)
		    or die('Connexion impossible : ' . pg_last_error());
			
	}
	
	
	private function defineMissingLabels(){
		$db = PearDatabase::getInstance();
		
		$query = 'SELECT vtiger_crmentity.crmid, vtiger_crmentity.setype
			FROM vtiger_crmentity
			JOIN vtiger_entityname
				ON vtiger_entityname.modulename = vtiger_crmentity.setype
			WHERE label IS NULL
			AND deleted = 0';

		$result = $db->pquery($query);
		$noOfRows = $db->num_rows($result);
		$updated = array();
		for($i=0; $i<$noOfRows; ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			
			$labelInfo = getEntityName($row['setype'], $row['crmid'], true);
			if ($labelInfo) {
				$label = decode_html($labelInfo[$row['crmid']]);
				$db->pquery('UPDATE vtiger_crmentity SET label=? WHERE crmid=?', array($label, $row['crmid']));
			
				$updated[$row['crmid']] = $label;
			}
			else {
				//par exemple, le crmentity existe mais pas le contactdetails...
			}
		}
		var_dump($updated);
	}
	
	private function definePrelevementsPeriodicites(){
		
		$db = PearDatabase::getInstance();
		
		$query = 'SELECT vtiger_crmentity.crmid, vtiger_rsnprelevements.periodicite
			, EXTRACT(MONTH FROM MAX(vtiger_rsnprelvirement.dateexport)) AS dateexport
			FROM vtiger_rsnprelevements
			JOIN vtiger_rsnprelvirement
				ON vtiger_rsnprelvirement.rsnprelevementsid = vtiger_rsnprelevements.rsnprelevementsid
			JOIN vtiger_crmentity
				ON vtiger_rsnprelvirement.rsnprelevementsid = vtiger_crmentity.crmid 
			WHERE deleted = 0
			AND vtiger_rsnprelevements.periodicite <> "Mensuel"
			AND vtiger_rsnprelevements.periodicite NOT LIKE "Trimestriel%"
			GROUP BY vtiger_crmentity.crmid, vtiger_rsnprelevements.periodicite';
		
		//TODO contrôler les Trimestriel
		
		$result = $db->pquery($query);
		$noOfRows = $db->num_rows($result);
		$updated = array();
		for($i=0; $i<$noOfRows; ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			
			$periodicite = $row['periodicite'];
			$lastMonth = $row['dateexport'];
			$periodName = explode(' ', $periodicite)[0];
			$nbMonths = ($periodName == 'Trimestriel' ? 3 : ($periodName == 'Bimestriel' ? 2 : ($periodName == 'Semestre' ? 6 : ($periodName == 'Semestriel' ? 6 : ($periodName == 'Annuel' ? 12 : 0)))));
			if ($nbMonths) {
				$nMonth = fmod($lastMonth, $nbMonths);
				if($nMonth == 0) $nMonth = $nbMonths;
				if($periodicite == "$periodName $nMonth")
					continue;
				$periodicite = "$periodName $nMonth";
				$update = $db->pquery('UPDATE vtiger_rsnprelevements SET periodicite=? WHERE rsnprelevementsid=?', array($periodicite, $row['crmid']));
				if(!$update){
					$db->echoError();
					var_dump('Erreur : ', $row['periodicite'], $periodicite);
				}
				else
					$updated[$row['crmid']] = $row['periodicite'] . " -> " . $periodicite;
			}
			else {
				var_dump('Erreur : ', $periodicite, $periodName);
			}
		}
		echo "<pre>";
		print_r($updated);
		echo "</pre>";
		
	}
}
