<?php

define('MAX_QUERY_ROWS', 3000); //DEBUG

class RSNImportSources_ImportFromDB_View extends RSNImportSources_ImportFromFile_View {

	
	/**
	 * Method to show the configuration template of the import for the first step.
	 *  It display the select db template.
	 * @param Vtiger_Request $request: the curent request.
	 */
	function showConfiguration(Vtiger_Request $request) {
		$viewer = $this->initConfiguration($request);

		return $viewer->view('ImportSelectDBStep.tpl', 'RSNImportSources');
	}

	/**
	 * Method to initialize the configuration template of the import for the first step.
	 *  It display the select file template.
	 * @param Vtiger_Request $request: the curent request.
	 * @return viewer
	 */
	function initConfiguration(Vtiger_Request $request) {
		$viewer = parent::initConfiguration($request);
		$viewer->assign('SUPPORTED_DB_TYPES', $this->getSupportedDBTypes());//tmp function of import module !!!
		$viewer->assign('IMPORT_ULPOAD_DB_TYPE', $this->getDefaultDBType());
		$viewer->assign('IMPORT_ULPOAD_DB_SERVER', $this->getDefaultDBServer());
		$viewer->assign('IMPORT_ULPOAD_DB_PORT', $this->getDefaultDBPort());
		$viewer->assign('IMPORT_ULPOAD_DB_NAME', $this->getDefaultDBName());
		$viewer->assign('IMPORT_ULPOAD_DB_USER', $this->getDefaultDBUser());
		$viewer->assign('IMPORT_ULPOAD_DB_PWD', '');
		$viewer->assign('IMPORT_ULPOAD_DB_CX', str_replace($this->getDefaultDBPwd(), '***', $this->getDBConnexionString()));
		return $viewer;
	}

	/**
	 * Method to default db type for this import.
	 *  This method should be overload in the child class.
	 * @return string - the default db type.
	 */
	public function getDefaultDBType() {
		return 'mysql';
	}

	/**
	 * Method to default db server for this import.
	 *  This method should be overload in the child class.
	 * @return string - the default db server.
	 */
	public function getDefaultDBServer() {
		return 'localhost';
	}

	/**
	 * Method to default db ip port for this import.
	 *  This method should be overload in the child class.
	 * @return string - the default db port.
	 */
	public function getDefaultDBPort() {
		return '3306';
	}

	/**
	 * Method to default db name for this import.
	 *  This method should be overload in the child class.
	 * @return string - the default db name.
	 */
	public function getDefaultDBName() {
		return '';
	}

	/**
	 * Method to default db user for this import.
	 *  This method should be overload in the child class.
	 * @return string - the default db user.
	 */
	public function getDefaultDBUser() {
		return '';
	}

	/**
	 * Method to default db password for this import.
	 *  This method should be overload in the child class.
	 * @return string - the default db password.
	 */
	public function getDefaultDBPwd() {
		return '';
	}

	/**
	 * Method to get the suported DB types for this import.
	 *  This method should be overload in the child class.
	 * @return array - an array of string containing the supported DB types.
	 */
	public function getSupportedDBTypes() {
		return RSNImportSources_Utils_Helper::getSupportedDBTypes();
	}

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
	 * Method to upload the file in the temporary location.
	 */
	function uploadFile() {
		$dbRows = $this->getDBRows();
		if(!$dbRows)
			return false;
		$importDirectory = RSNImportSources_Utils_Helper::getImportDirectory();
		$current_user = Users_Record_Model::getCurrentUserModel();
		$temporaryFileName = RSNImportSources_Utils_Helper::getImportFilePath($current_user, $this->request->get("for_module"));
			
		$delimiter = $this->getDefaultFileDelimiter();
		$enclosure = '"';
		$escape_char = '\\';
		
		$fp = fopen($temporaryFileName	, 'w');

		//1ère ligne
		$nRow = 0;
		$fields = $this->col;
		fputcsv($fp, $fields, $delimiter, $enclosure, $escape_char);
		foreach ($dbRows as $row) {
			if($nRow++ === 0){
				$fields = array_keys($row);
			}
			
			fputcsv($fp, RSNImportSources_Utils_Helper::escapeValuesForCSV($row), $delimiter, $enclosure, $escape_char);
		}
		
		fclose($fp);
		return true;
	}
	
	/**
	 * Method to upload the file in the temporary location.
	 * abstract
	 */
	function getDBQuery() {
		return false;
	}

	/**
	 * Method to upload the file in the temporary location.
	 * abstract
	 */
	function getDBRows() {
		return false;
	}
}