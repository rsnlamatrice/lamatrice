<?php
/*
 * Les affaires importées depuis Cogilog se retrouvent en Documents de type Coupon 
 *
 *
 */

 
class RSN_CogilogAffairesRSN_Import {

	/* get postgresql data rows */
	private static function getPGDataRows($table_name){
			
		$dbconn = self::get_db_connect();
		
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
	
	private static function get_db_connect(){
		// Connexion, sélection de la base de données
		include_once('config.cogilog.php');
		global $cogilog_config;
		$cxString = 'host='.$cogilog_config['db_server'].' port='.$cogilog_config['db_port'].' dbname='.$cogilog_config['db_name'].' user='.$cogilog_config['db_username'].' password='.$cogilog_config['db_password'];
		//echo str_repeat('<br>',5);
		return pg_connect($cxString)
		    or die('Connexion impossible : ' . pg_last_error());
			
	}
        
	// Importe les affaires qui n'ont pas encore été importées
	public static function importNexts(){
            
		$srcRows = self::getEntries();
		$doneRows = array();
		
		foreach($srcRows as $srcRow){
			
			if(self::importRow($srcRow)){
				$srcRow['LA MATRICE'] = 'Importée';
				
				//break;
			}
			else
				$srcRow['LA MATRICE'] = 'Non';
				
			$doneRows[] = $srcRow;
		}
                return $doneRows;
	}
        
	/*
	 * Les entrées contiennent autant de lignes que de lignes de factures
	 */
	private static function getEntries(){
            
		$query = 'SELECT "id", "code", "nom", "responsable", "correspondant", "intervenant", "etat", "notes", "avancement", "type", "budget", "debut", "fin", "archive", "id_usersaisie", "tssaisie", "id_usermod", "tsmod"
			FROM "gaffai00002" AS "gaffai00002"
			WHERE code <> \'\'
		';
			
		
		$query .= ' ORDER BY id';
		//var_dump($query);
		return self::getPGDataRows($query);
    }
        
	/* Import de l'enregistrement
	 * @returns note record
	 */
	private static function importRow($row){
		
		//Imports Document
		$record = self::importDocument($row);
		if(!$record) return false;
		
		return $record;
	}
	
	
        /* Import du doc
	 * @returns contact record
	 */
	private static function importDocument($srcRow){
		
		$query = "SELECT notesid, vtiger_crmentity.deleted
			FROM vtiger_notes
                        JOIN vtiger_crmentity
                            ON vtiger_notes.notesid = vtiger_crmentity.crmid
                        JOIN vtiger_notescf
                            ON vtiger_notescf.notesid = vtiger_crmentity.crmid
			WHERE codeaffaire = ?
			AND deleted = 0
			ORDER BY deleted ASC
		";
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, array($srcRow['id']));
		if($db->num_rows($result)){
			$row = $db->fetch_row($result, 0);
                        if($srcRow['archive'] == 'f' && $row['deleted'] == 1){
                            //restore from bin
                            
                            $recordIds = array($srcRow['id']);
                            $recycleBinModule = new RecycleBin_Module_Model();
                            $recycleBinModule->deleteRecords($recordIds);
                        }
			$record = Vtiger_Record_Model::getInstanceById($row['notesid'], 'Documents');
		}
		else {
			$record = Vtiger_Record_Model::getCleanInstance('Documents');
			$record->set('MODE', 'create');
			$record->set('notes_title', $srcRow['nom']);
			$record->set('codeaffaire', $srcRow['code']);
			$record->set('notecontent', $srcRow['notes']);
			$record->set('folderid', COUPON_FOLDERID);
			$record->set('filestatus', $srcRow['archive'] == 'f' ? 1 : 0);
                    
			//$db->setDebug(true);
			$record->save();
			if(!$record->getId()){
                            echo "<pre><code>Impossible d'enregistrer le nouveau coupon</code></pre>";
                            return false;
                        }
			$record->set('MODE', '');
			$query = "UPDATE vtiger_crmentity
				SET modifiedtime = ?
				, createdtime = ?
				, smownerid = ?
				WHERE crmid = ?
				LIMIT 1
			";
			$result = $db->pquery($query, array(substr($srcRow['tsmod'], 0, 19), substr($srcRow['tssaisie'], 0, 19), ASSIGNEDTO_ALL, $record->getId()));
		}
		
		return $record;		
	}
}