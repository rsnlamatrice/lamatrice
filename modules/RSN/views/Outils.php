<?php
/*+***********************************************************************************
 * Affiche la page d'un outil
 * La liste est affichée dans le bandeau vertical de gauche,
 * 	initialisée dans Module.php::getSideBarLinks()
 *************************************************************************************/
//require_once('modules/RSN/models/ImportCogilogFactures.php');
//require_once('modules/RSN/models/ImportCogilogAffaires.php');
//require_once('modules/RSN/models/ImportCogilogProduitsEtServices.php');
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
		
		case 'resetPicklistValuesRights' :
			$this->resetPicklistValuesRights();
			break;
		
		case 'freeDebug' :
			$this->freeDebug();
			exit;
			break;
		
		case 'PicklistValuesTransfer' :
			//Transfert des noms des entités d'un module dans un picklist
			
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
		//$this->updateGroupesFromDonnes4D();
	}
	
	
	//Post-Migration : Création d'un abonnement pour les contacts ayant le critère Prochaine_Revue_Offerte_?
	function createAboSurCritere(){
		global $adb;
		
		$query = "SELECT crmid, contact_no
			FROM vtiger_contactdetails
			JOIN vtiger_crmentity
				ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
			JOIN vtiger_critere4dcontrel
				ON vtiger_contactdetails.contactid = vtiger_critere4dcontrel.contactid
			JOIN vtiger_critere4d
				ON vtiger_critere4dcontrel.critere4did = vtiger_critere4d.critere4did
			WHERE vtiger_crmentity.deleted = 0
			AND vtiger_critere4dcontrel.dateapplication > ?
			AND vtiger_critere4d.nom = ?
		";
		$params = array('2015-10-29', 'prochaine_revue_offerte_?');
		$result = $adb->pquery($query, $params);
		if(!$result){
			echo "<pre>$query</pre>";
			$adb->echoError();
			return;
		}
		$aboRevueModuleModel = Vtiger_Module_Model::getInstance('RSNAboRevues');
		$models = array();
		while($contact = $adb->getNextRow($result)){
			$recordModel = Vtiger_Record_Model::getInstanceById($contact['crmid']);
			$aboRevues = $recordModel->getRSNAboRevues();
			$doNotAbo = false;
			$aboRevue = false;
			if($aboRevues)
				foreach($aboRevues as $aboRevue){
					if($aboRevue->isTypeNePasAbonner()){
						$doNotAbo = true;
						break;
					}
					if($aboRevue->isAbonne()){
						$doNotAbo = true;
						break;
					}
					break;
				}
			if(!$doNotAbo){
				$models[$contact['crmid']] = $recordModel;
				echo "<li>" . $contact['crmid'] . " " . $contact['contact_no'];
				if($aboRevue)
					echo " - dernier abo : " . $aboRevue->getName() . ', ' . $aboRevue->getTypeAbo() . ' du ' . $aboRevue->get('debutabo') . ' au ' . $aboRevue->get('finabo');
				$aboRevue = $aboRevueModuleModel->ensureAboRevue($recordModel->getAccountRecordModel(), 3);
					echo "<br> - nouvel abo : " . $aboRevue->getName() . ', ' . $aboRevue->getTypeAbo() . ' du ' . $aboRevue->get('debutabo') . ' au ' . $aboRevue->get('finabo');
				//break;
			}
		}
		echo "<BR>Total : " . count($models);
	}
	
	//Post-Migration : met à jour des données dans la Matrice à partir des données présentes dans le commentaire initial des contacts importés
	function updateGroupeDescriptifFromDonnes4D(){
		global $adb;
		
		$contactId = 0  ;
		$limit = 1000;
		$loop = 100;
		$maxLoop = 1;
		$testCounter = 0;
		$updCounter = 0;
		//Groupes venant de 4D avec leurs descriptifs initiaux (attention, il y en a de deux types : contact et groupe)
		$query = 'SELECT crmid, vtiger_modcomments.commentcontent, vtiger_contactscf.grpdescriptif
			FROM vtiger_contactscf
			JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_contactscf.contactid
			JOIN vtiger_contactdetails
				ON vtiger_contactdetails.contactid = vtiger_contactscf.contactid
			JOIN vtiger_modcomments
				ON vtiger_contactdetails.contactid = vtiger_modcomments.related_to
			WHERE vtiger_crmentity.deleted = 0
			AND vtiger_crmentity.crmid > ?
			AND vtiger_contactdetails.ref4d IS NOT NULL
			AND vtiger_modcomments.userid = 1
			AND vtiger_modcomments.commentcontent LIKE "Donn%associationcourt%descriptifgroupe%"
			AND vtiger_contactscf.grpdescriptif IS NOT NULL AND vtiger_contactscf.grpdescriptif <> ""
			ORDER BY crmid
			LIMIT '. $limit;
		
		$queryUpdate = 'UPDATE vtiger_contactscf
			SET grpdescriptif = ?
			WHERE contactid = ?';
		do {
			$result = $adb->pquery($query, array($contactId));
			if(!$result){
				$adb->echoError();
				return;
			}
			$contactsCount = $adb->getRowCount($result);
				echo "<br>Query : " . $contactsCount;
			
			while($row = $adb->getNextRow($result, false)){
				$testCounter++;
				$contactId = $row['crmid'];
				$associationCourt = preg_replace('/^([\s\S]+associationcourt\s=\s)([^\n]*)(\n[\s\S]+)$/', '$2', $row['commentcontent']);
				$descriptif = preg_replace('/^([\s\S]+descriptifgroupe\s=\s)([\s\S]*)$/', '$2', $row['commentcontent']);
				if($associationCourt && !preg_match('/^Donn.*es 4D/', $associationCourt)){
					$updateDescriptif = false;
					$associationCourt = trim($associationCourt, " \t\n\r\0\x0B");
					if(preg_match('/^Donn.*es 4D/', $descriptif))
						$descriptif = '';
					$descriptif = preg_split('/\n-\s\w+\s=\s/', $descriptif)[0];
					$descriptif = trim($descriptif, " \t\n\r\0\x0B");
					$grpdescriptif = trim($row['grpdescriptif'], " \t\n\r\0\x0B");
					if($grpdescriptif == $associationCourt){
						echo "<br><br>$contactId : \"$associationCourt\" <pre>$descriptif</pre>";
						echo 'REPLACED !';
						$grpdescriptif = '';
						$updateDescriptif = true;
						$updCounter++;
					}
					elseif(substr($grpdescriptif, 0, strlen($associationCourt)) == $associationCourt
					&& substr($grpdescriptif, strlen($grpdescriptif) - strlen($descriptif)) == $descriptif){
						echo "<br><br>$contactId : \"$associationCourt\" <pre>$descriptif</pre>";
						echo 'CONCATENATED !';
						$grpdescriptif = $descriptif;
						$updateDescriptif = true;
						$updCounter++;
					}
					if($updateDescriptif ){
						$resultUpd = $adb->pquery($queryUpdate, array($grpdescriptif, $row['crmid']));
						if(!$resultUpd){
							$adb->echoError('UPDATE');
							return;
						}
					}
					//break;
					
				}
			}
			
			if($contactsCount < $limit){
				echo "<br>TERMINE $limit > " . $contactsCount;
				break;
			}
			if(++$loop >= $maxLoop){
				break;
			}
			
		} while(true);
		echo "<br>Fin au bout de $loop boucles / $limit contacts";
		echo "<br>Tests : $testCounter, Mises à jour : $updCounter";
	}
	//Post-Migration : met à jour des données dans la Matrice à partir des données présentes dans le commentaire initial des contacts importés
	function updateContactsFromDonnes4D(){
		global $adb;
		
		$contactId = 0;
		$limit = 10000;
		$loop = 0;
		$maxLoop = 100;
		$testCounter = 0;
		$updCounter = 0;
		//Contacts venant de 4D avec leurs commentaires initiaux (attention, il y en a de deux types : contact et groupe)
		$query = 'SELECT crmid, vtiger_modcomments.commentcontent
			FROM vtiger_contactaddress
			JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_contactaddress.contactaddressid
			JOIN vtiger_contactdetails
				ON vtiger_contactdetails.contactid = vtiger_contactaddress.contactaddressid
			JOIN vtiger_modcomments
				ON vtiger_contactdetails.contactid = vtiger_modcomments.related_to
			WHERE vtiger_crmentity.deleted = 0
			AND vtiger_crmentity.crmid > ?
			AND vtiger_contactdetails.ref4d IS NOT NULL
			AND vtiger_modcomments.userid = 1
			AND vtiger_modcomments.commentcontent LIKE "Donn%"
			ORDER BY crmid
			LIMIT '. $limit;
		
		$queryUpdate = 'UPDATE vtiger_contactaddress
			SET mailingrnvpeval = ?
			, mailingrnvpcharade = ?
			WHERE contactaddressid = ?';
		do {
			$result = $adb->pquery($query, array($contactId));
			if(!$result){
				$adb->echoError();
				return;
			}
			$contactsCount = $adb->getRowCount($result);
				echo "<br>Query : " . $contactsCount;
			
			while($row = $adb->getNextRow($result)){
				$testCounter++;
				$contactId = $row['crmid'];
				$evalAdr = preg_replace('/^([\s\S]+evaladr\s=\s)(\d*)([\s\S]+)$/', '$2', $row['commentcontent']);
				$charade = preg_replace('/^([\s\S]+charade\s=\s)(\d*)([\s\S]+)$/', '$2', $row['commentcontent']);
				if(is_numeric($evalAdr) || is_numeric($charade)){
					if(!is_numeric($evalAdr))
						$evalAdr = null;
					if(!is_numeric($charade))
						$charade = null;
					//echo "<br>$contactId : $evalAdr, $charade";
					$updCounter++;
					$resultUpd = $adb->pquery($queryUpdate, array($evalAdr, $charade, $contactId));
					if(!$resultUpd){
						$adb->echoError('UPDATE');
						return;
					}
					
				}
			}
			
			if($contactsCount < $limit){
				echo "<br>TERMINE $limit > " . $contactsCount;
				break;
			}
			if(++$loop >= $maxLoop){
				break;
			}
			
		} while(true);
		echo "<br>Fin au bout de $loop boucles / $limit contacts";
		echo "<br>Tests : $testCounter, Mises à jour : $updCounter";
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
		global $php_max_memory_limit;
		ini_set("memory_limit", empty($php_max_memory_limit) ? "1G" : $php_max_memory_limit);

		global $php_max_execution_time;
		set_time_limit($php_max_execution_time);

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
			AND vtiger_rsnprelevements.periodicite NOT LIKE "Trimestriel%"/* correctement marque dans 4D */
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
		
		$query = "UPDATE `vtiger_rsnprelevements` 
JOIN (SELECT `rsnprelevementsid`, MIN(`dateexport`) as dateexport
FROM `vtiger_rsnprelvirement`
GROUP BY `rsnprelevementsid`) vir
ON vir.`rsnprelevementsid` = vtiger_rsnprelevements.`rsnprelevementsid`
JOIN vtiger_rsnprelvirement
ON vtiger_rsnprelvirement.`rsnprelevementsid` = vtiger_rsnprelevements.`rsnprelevementsid`
AND vtiger_rsnprelvirement.dateexport = vir.dateexport
SET  `vtiger_rsnprelevements`.`dejapreleve` = vir.dateexport
, `is_first` = 1";
		$db->pquery($query);
		
	}
	
	
	/** Remise à plat des droits sur les valeurs de picklists
	 *
	 *	Supprime toutes les limitations
	 */
	private function resetPicklistValuesRights(){
		$db = PearDatabase::getInstance();
		
		$sql = "SELECT MAX(picklistvalueid) as maxi
			FROM vtiger_role2picklist
		";
		$result = $db->query($sql);
		$currentPicklistvalueid = $db->query_result($result,0,0);
		$picklistvalueid = $currentPicklistvalueid + 1;


		$sql = "SELECT vtiger_picklist.picklistid, vtiger_field.fieldid, vtiger_field.tabid, vtiger_field.fieldname, vtiger_field.columnname, vtiger_field.uitype
			FROM vtiger_field
			JOIN vtiger_picklist
				ON vtiger_picklist.name = vtiger_field.fieldname
			WHERE vtiger_field.uitype IN (15,16)
			
			/*AND vtiger_field.fieldname = 'receivedmoderegl'*/
			
			ORDER BY vtiger_field.fieldname
		";
		$result = $db->query($sql);
		$picklists = array();
		while($row = $db->getNextRow($result, false))
			$picklists[$row['fieldname']] = $row;
		
		
		$picklists_ok = array();
		foreach($picklists as &$picklist){
			$sql = 'SHOW COLUMNS IN vtiger_' . $picklist['fieldname'];
			
			$result = $db->query($sql);
			if(!$result){
				echo "<br>".$picklist['fieldname']." n'est pas une picklist";
				continue;
			}
			
			$columns = array();
			while($row = $db->getNextRow($result, false))
				$columns[] = $row;
			
			$picklist['picklist_table'] = 'vtiger_' . $picklist['fieldname'];
			$fields = '';
			foreach($columns as $column)
				$fields .= $column['field'] . ', ';
			$picklist['picklist_valueid'] = strpos($fields, 'picklist_valueid') > 0;
			$picklist['columns'] = $fields;
			$picklist['newIds'] = '';
			
			//Si la table contient un champ picklist_valueid
			if($picklist['picklist_valueid']){
				//Elements de la table avec un picklist_valueid vide
				$sql = 'SELECT `'. $columns[0]['field'] . '` AS rowid, picklist_valueid
					FROM ' . $picklist['picklist_table'] .'
					WHERE picklist_valueid = 0 OR picklist_valueid IS NULL';
			
				$result = $db->query($sql);
				if(!$result){
					$db->echoError($sql);
					continue;
				}
				$missings = array();
				while($row = $db->getNextRow($result, false))
					$missings[] = $row;
				
				//UPDATE
				//Attribution d'une valeur aux manquants
				$params = array();
				$sql = "UPDATE " . $picklist['picklist_table'] ."
					SET picklist_valueid = CASE `". $columns[0]['field'] . "` 
				";
				foreach($missings as $missing){
					$sql .= " WHEN ? THEN ?";
					$params[] = $missing['rowid'];
					$params[] = $picklistvalueid++;
				}
				$sql .= " ELSE `picklist_valueid` END";
				$sql .= " WHERE picklist_valueid = 0 OR picklist_valueid IS NULL";
					
				if($params){
					echo "<br>Mises à jour de ".$picklist['fieldname']." : ". count($params)/2;
					//$picklist['newIds'] = print_r($sql, true);
					$result = $db->pquery($sql, $params);
					if(!$result){
						$db->echoError($sql);
						continue;
					}
					$picklist['newIds'] = $result;	
				}
				
				//INSERT INTO vtiger_role2picklist
				// Création des droits pour le rôle H1
				$sql = "INSERT INTO `vtiger_role2picklist`(`roleid`, `picklistvalueid`, `picklistid`, `sortid`)  
						SELECT 'H1', `picklist_valueid`, ?, `sortorderid`
						FROM " . $picklist['picklist_table'] . "
						ON DUPLICATE KEY UPDATE vtiger_role2picklist.sortid = vtiger_role2picklist.sortid";
				$params = array($picklist['picklistid']);
				$result = $db->pquery($sql, $params);
				if(!$result){
					$db->echoError($sql);
					continue;
				}
				
			}
			else
				echo "<br>Pas de champ picklist_valueid dans ".$picklist['fieldname'];
			
			$picklists_ok[] = $picklist;
		}
		
		$sql = "UPDATE `vtiger_picklistvalues_seq` SET `id`= 
							(SELECT MAX( `picklistvalueid`)
							FROM vtiger_role2picklist)";
		$db->query($sql);
		
		$sql = "INSERT INTO `vtiger_role2picklist`(`roleid`, `picklistvalueid`, `picklistid`, `sortid`)  
							SELECT 'H1', `picklistvalueid`, `picklistid`, `sortid`
							FROM vtiger_role2picklist a
					ON DUPLICATE KEY UPDATE vtiger_role2picklist.sortid = vtiger_role2picklist.sortid";
		$db->query($sql);
		
		
		//Purge
		$sql = "DELETE FROM `vtiger_role2picklist`
			WHERE `roleid` != 'H1'";
		$db->query($sql);
		
		//Recréation pour tous les rôles
		$sql = "INSERT INTO `vtiger_role2picklist`(`roleid`, `picklistvalueid`, `picklistid`, `sortid`)
		SELECT vtiger_role.roleid, vtiger_role2picklist.`picklistvalueid`, vtiger_role2picklist.`picklistid`, vtiger_role2picklist.`sortid`
		FROM `vtiger_role2picklist`
		JOIN vtiger_role
			ON vtiger_role.roleid != vtiger_role2picklist.roleid
		WHERE vtiger_role2picklist.roleid = 'H1'";
		$db->query($sql);


	}
}
