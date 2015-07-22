<?php

//abstract for cogilog db / postgresql
class RSNImportSources_ImportFromCogilog_View extends RSNImportSources_ImportFromDB_View {

	var $cogilog_config;
	
	/**
	 * Method to get the source type label to display.
	 * @return string - The label.
	 */
	public function getSourceType() {
		return 'LBL_DATABASE';
	}
	
	/**
	 * Method to default db type for this import.
	 *  This method should be overload in the child class.
	 * @return string - the default db type.
	 */
	public function getDefaultDBType() {
		return 'postgresql';
	}

	/**
	 * Method to default db server for this import.
	 *  This method should be overload in the child class.
	 * @return string - the default db server.
	 */
	public function getDefaultDBServer() {
		return $this->getConfigValue('db_server');
	}

	/**
	 * Method to default db ip port for this import.
	 *  This method should be overload in the child class.
	 * @return string - the default db port.
	 */
	public function getDefaultDBPort() {
		return $this->getConfigValue('db_port');
	}

	/**
	 * Method to default db name for this import.
	 *  This method should be overload in the child class.
	 * @return string - the default db name.
	 */
	public function getDefaultDBName() {
		return $this->getConfigValue('db_name');
	}

	/**
	 * Method to default db user for this import.
	 *  This method should be overload in the child class.
	 * @return string - the default db user.
	 */
	public function getDefaultDBUser() {
		return $this->getConfigValue('db_username');
	}

	/**
	 * Method to default db password for this import.
	 *  This method should be overload in the child class.
	 * @return string - the default db password.
	 */
	public function getDefaultDBPwd() {
		return $this->getConfigValue('db_password');
	}

	/**
	 * Method to get config value.
	 *  This method should be overload in the child class.
	 * @return string - value
	 */
	protected function getConfigValue($parameter) {
		if(empty($this->cogilog_config)){
			include_once('config.cogilog.php');
			global $cogilog_config;
			$this->cogilog_config = $cogilog_config;
		}
		return $this->cogilog_config[$parameter];
	}
	

	/**
	 * Method to get db data
	 */
	function getDBRows() {
		$cx = $this->getDBConnexion();
		$query = $this->getDBQuery();
		
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
		while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {//ED150722 attention, entre PGSQL_ASSOC et PGSQL_NUM j'ai constaté un écart d'une colonne
			//var_dump($line);
			if($nLine++ === 0){
				$this->columnName_indexes = array_keys($line);
				$this->columnName_indexes = array_combine(array_keys($line), array_keys(array_keys($line)));//tableau nom => index
			}
		    $rows[] = array_values($line);
		}
		
		// Libère le résultat
		pg_free_result($result);
		
		// Ferme la connexion
		pg_close($dbconn);
		return $rows;
		
	}
}