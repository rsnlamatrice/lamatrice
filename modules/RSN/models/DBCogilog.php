<?php


include_once('modules/RSN/models/DBConnector.php');
class RSN_DBCogilog_Module extends RSN_DBConnector_Module {
	
	/**
	 * Method to default db type for this import.
	 *  This method should be overload in the child class.
	 * @return string - the default db type.
	 */
	public function getDefaultDBType() {
		return 'postgresql';
	}

	/**
	 * Method to get config value.
	 *  This method should be overload in the child class.
	 * @return string - value
	 */
	protected function getConfigValue($parameter) {
		if(empty($this->db_config)){
			include_once('config.cogilog.php');
			global $cogilog_config;
			$this->db_config = $cogilog_config;
		}
		return $this->db_config[$parameter];
	}
	

	/**
	 * Method to get db data
	 */
	function getDBRows($query) {
		$cx = $this->getDBConnexion();
		
		// Exécution de la requête SQL
		$result = pg_query($query);
		if(!$result){
			echo '<code>Échec de la requête : ' . pg_last_error() .'</code>'
					. '<br>'.$query;
			return;
		}
		
		$rows = array();
		
		$line = false;
		$nLine = 0;
		while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
			
		    $rows[] = $line;
		}
		
		// Libère le résultat
		pg_free_result($result);
		
		// Ferme la connexion
		pg_close($dbconn);
		return $rows;
		
	}
}