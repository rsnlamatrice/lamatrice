<?php

//abstract for DB connexion
class RSN_DBConnector_Module {

	var $db_config;

	/**
	 * Method to get DB connexion string.
	 * @return string - the DB connexion string.
	 */
	public function getDBConnexionString() {
		switch($this->getDefaultDBType()){
		case 'postgresql' :
			$cxString = 'host='.$this->getDefaultDBServer()
			.' port='.$this->getDefaultDBPort()
			.' dbname='.$this->getDefaultDBName()
			.' user='.$this->getDefaultDBUser()
			.' password='.$this->getDefaultDBPwd();
			break;
		case 'mysql':
		case 'mysqli':
			//TODO
			$cxString = 'host='.$this->getDefaultDBServer()
			.' port='.$this->getDefaultDBPort()
			.' dbname='.$this->getDefaultDBName()
			.' user='.$this->getDefaultDBUser()
			.' password='.$this->getDefaultDBPwd();
			break;
		}
		return $cxString;
	}


	/**
	 * Method to get DB connexion.
	 * @return string - the DB connexion.
	 */
	public function getDBConnexion() {
		$cxString = $this->getDBConnexionString();
		switch($this->getDefaultDBType()){
		case 'postgresql' :
			return pg_connect($cxString)
			    or die('Connexion impossible : ' . pg_last_error());
		case 'mysql' :
		case 'mysqli' :
			//TODO
			return pg_connect($cxString)
			    or die('Connexion impossible : ' . pg_last_error());
		}
	}
	
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
		return 'abstract';
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
		if(!empty($this->db_config)){
			return $this->db_config[$parameter];
		}
	}
	

	/**
	 * Method to get db data
	 */
	function getDBRows() {
		
		return false;
		
	}
}