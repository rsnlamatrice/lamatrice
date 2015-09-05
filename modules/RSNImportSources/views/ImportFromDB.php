<?php

define('MAX_QUERY_ROWS', 10000); //DEBUG

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
		$viewer->assign('IMPORT_ULPOAD_MAX_QUERY_ROWS', $this->getDefaultMaxQueryRows());
		$viewer->assign('IMPORT_ULPOAD_DB_PWD', '');
		$viewer->assign('IMPORT_ULPOAD_DB_CX', str_replace($this->getDefaultDBPwd(), '***', $this->getDBConnexionString()));
		return $viewer;
	}


	/**
	 * Method to default max query rows for this import.
	 *  This method should be overload in the child class.
	 * @return string - the default db port.
	 */
	public function getDefaultMaxQueryRows() {
		return MAX_QUERY_ROWS;
	}
	
	/**
	 * Method to max query rows for this import.
	 *  This method should be overload in the child class.
	 * @return string - the default db port.
	 */
	public function getMaxQueryRows() {
		return $this->request && $this->request->get('db_max_query_rows') ? $this->request->get('db_max_query_rows') : $this->getDefaultMaxQueryRows();
	}
	
	private $queryStartLimit = 0;
	/**
	 * Method to get the query start limit for this import.
	 *  This method should be overload in the child class.
	 * @return string - the queryLimitStart.
	 */
	public function getQueryLimitStart() {
		return $this->queryStartLimit;
	}
	/**
	 * Method to set the query start limit for this import.
	 *  This method should be overload in the child class.
	 * @return string - this.
	 */
	public function setQueryLimitStart($value) {
		$this->queryStartLimit = $value;
		return $this;
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
	 * Method to transfer DBRows to file in the temporary location.
	 */
	function uploadFile() {
		$dbRows = $this->getDBRows();
		if(!$dbRows)
			return false;
		
		//Valeur mis à jour dans le champ lastimport de la table, pour affichage et historique immédiat
		$this->request->set('import_file_name', $this->getDefaultDBServer().':'.$this->getDefaultDBName());
		
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
		
	/** Prépare les données pour un pré-import automatique
	 *
	 */
	function prepareAutoPreImportData(){
		$this->request->set('auto_preimport', true);
		if(!$this->request->get('for_module'))
			$this->request->set('for_module', $this->getImportModules()[0]);
		
		//TODO Initialiser les paramètres comme si on venait du formulaire web
		
		if(!$this->request->get('db_max_query_rows'))
			$this->request->set('db_max_query_rows', $this->getDefaultMaxQueryRows());
			
		return true;
	}
		
	/** Méthode appelée après un pré-import automatique
	 *
	 */
	function postAutoPreImportData(){
		$this->request->set('auto_preimport', 'done');
	}
}
