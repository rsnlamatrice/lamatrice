<?php


/* Phase de migration
 * Importation des inscriptions aux listes de diffusion mail depuis le fichier provenant de 4D
 */
class RSNImportSources_ImportEmailsListeFrom4D_View extends RSNImportSources_ImportFromFile_View {
        
	/**
	 * Method to get the source import label to display.
	 * @return string - The label.
	 */
	public function getSource() {
		return 'LBL_EMAILSLISTES_4D';
	}

	/**
	 * Method to get the modules that are concerned by the import.
	 * @return array - An array containing concerned module names.
	 */
	public function getImportModules() {
		return array('RSNEmailListes');
	}

	/**
	 * Method to default file enconding for this import.
	 * @return string - the default file encoding.
	 */
	public function getDefaultFileEncoding() {
		return 'WINDOWS-1252';
	}

	/**
	 * Method to get the source type label to display.
	 * @return string - The label.
	 */
	public function getSourceType() {
		return 'LBL_CSV_FILE';
	}

	/**
	 * Method to get the suported file delimiters for this import.
	 * @return array - an array of string containing the supported file delimiters.
	 */
	public function getDefaultFileDelimiter() {
		return '	';
	}

	/**
	 * Method to get the suported file extentions for this import.
	 * @return array - an array of string containing the supported file extentions.
	 */
	public function getSupportedFileExtentions() {
		return array('csv');
	}

	/**
	 * Method to default file extention for this import.
	 * @return string - the default file extention.
	 */
	public function getDefaultFileType() {
		return 'csv';
	}
	
	/**
	 * Method to get the imported fields for the contact emails module.
	 * @return array - the imported fields for the contact emails module.
	 */
	function getRSNEmailListesFieldsMapping() {
		return array(
			'email' => 'email',
			'datecreation' => 'createdtime',
			'dateunsubscribe' => 'dateunsubscribe',
			'datesubscribe' => 'datesubscribe',
			'subscribed' => 'subscribed',
			'name' => 'name',
			
			/* post pré-import */
			'_rsnemaillistesid' => '',
			'_contactemailsid' => '',
		);
	}
	
	function getRSNEmailListesDateFields(){
		return array(
			'datecreation', 'dateunsubscribe', 'datesubscribe'
		);
	}
	/**
	 * Method to get the imported fields for the contact emails module.
	 * @return array - the imported fields for the contact emails module.
	 */
	function getRSNEmailListesFields() {
		//laisser exactement les colonnes du fichier
		return array_keys($this->getRSNEmailListesFieldsMapping());
	}

	/**
	 * Method to process to the import of the RSNEmailListes module.
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importRSNEmailListes($importDataController) {
		global $VTIGER_BULK_SAVE_MODE;
		$VTIGER_BULK_SAVE_MODE = true;
		$config = new RSNImportSources_Config_Model();
		
		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($this->user, 'RSNEmailListes');
		
		//Seules restent les lignes dont la liste mail n'existe pas
		$sql = 'SELECT name, MIN(datecreation) AS datecreation
			FROM ' . $tableName . '
			WHERE status = '. RSNImportSources_Data_Action::$IMPORT_RECORD_NONE . '
			GROUP BY name
			ORDER BY datecreation';

		$result = $adb->query($sql);
		if(!$result){
			$adb->echoError();
			die();
		}
		$numberOfRecords = $adb->num_rows($result);

		if ($numberOfRecords <= 0) {
			return;
		}
		if($numberOfRecords == $config->get('importBatchLimit')){
			$this->keepScheduledImport = true;
		}

		$perf = new RSN_Performance_Helper($numberOfRecords);
		for ($i = 0; $i < $numberOfRecords; ++$i) {
			$row = $adb->raw_query_result_rowdata($result, $i);
			$this->importOneRSNEmailListes(array($row), $importDataController);
			$perf->tick();
			if(Import_Utils_Helper::isMemoryUsageToHigh()){
				$this->skipNextScheduledImports = true;
				$keepScheduledImport = true;
				break;
			}
		}
		$perf->terminate();
		
		if(isset($keepScheduledImport))
			$this->keepScheduledImport = $keepScheduledImport;
		elseif($numberOfRecords == $config->get('importBatchLimit')){
			$this->keepScheduledImport = $this->getNumberOfRecords() > 0;
		}
	}

	/**
	 * Method to process to the import of a one prelevement.
	 * @param $rsnemaillistesData : the data of the prelevement to import
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importOneRSNEmailListes($rsnemaillistesData, $importDataController) {
					
		global $log;
		
		$listeName = $rsnemaillistesData[0]['name'];
		
		$record = Vtiger_Record_Model::getCleanInstance('RSNEmailListes');
		$record->set('mode', 'create');

		$record->set('name', $listeName);
		$record->set('enable', 1);
		
		$record->save();
		$rsnemaillistesId = $record->getId();
		
		global $adb;
		if(!$rsnemaillistesId){
			$tableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, "RSNEmailListes");
			$query = "UPDATE $tableName
				SET status	= ?
				WHERE name = ?
				AND status = ?
			";
			$adb->pquery($query, array(RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED, $listeName, RSNImportSources_Data_Action::$IMPORT_RECORD_NONE));
			
			return false;
		}
		
		$record->set('mode','edit');
		$query = "UPDATE vtiger_crmentity
			JOIN vtiger_rsnemaillistes
				ON vtiger_crmentity.crmid = vtiger_rsnemaillistes.rsnemaillistesid
			SET smownerid = ?
			, modifiedtime = ?
			, createdtime = ?
			, label = ?
			WHERE vtiger_crmentity.crmid = ?
		";
		$result = $adb->pquery($query, array(ASSIGNEDTO_ALL
							, $rsnemaillistesData[0]['datecreation']
							, $rsnemaillistesData[0]['datecreation']
							, $rsnemaillistesData[0]['name']
							, $rsnemaillistesId));
		
		//Met à jour la table d'import
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, "RSNEmailListes");
		$query = "UPDATE $tableName
			SET status	= ?
			, recordid = ?
			, _rsnemaillistesid = ?
			WHERE name = ?
			AND status = ?
		";
		$adb->pquery($query, array(RSNImportSources_Data_Action::$IMPORT_RECORD_CREATED, $rsnemaillistesId, $rsnemaillistesId, $listeName, RSNImportSources_Data_Action::$IMPORT_RECORD_NONE));
		
		$this->insertEmailsRelation($rsnemaillistesId);
		
		return $record;
	}

	/**
	 * Method that pre import an invoice.
	 *  It adds one row in the temporary pre-import table by invoice line.
	 * @param $rsnemaillistesData : the data of the invoice to import.
	 */
	function preImportRSNEmailListes($rsnemaillistesData) {
		
		$rsnemaillistesValues = $this->getRSNEmailListesValues($rsnemaillistesData);
		
		$rsnemaillistes = new RSNImportSources_Preimport_Model($rsnemaillistesValues, $this->user, 'RSNEmailListes');
		$rsnemaillistes->save();
	}
	
	/**
	 * Method to parse the uploaded file and save data to the temporary pre-import table.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - true if pre-import is ended successfully
	 */
	function parseAndSaveFile(RSNImportSources_FileReader_Reader $fileReader) {
		$this->clearPreImportTable();
		
		if($fileReader->open()) {
			if ($this->moveCursorToNextRSNEmailListes($fileReader)) {
				$i = 0;
				do {
					$rsnemaillistes = $this->getNextRSNEmailListes($fileReader);
					if ($rsnemaillistes != null) {
						$this->preImportRSNEmailListes($rsnemaillistes);
					}
				} while ($rsnemaillistes != null);

			}

			$fileReader->close(); 
			return true;
		} else {
			//TODO: manage error
			echo "<code>le fichier n'a pas pu être ouvert...</code>";
		}
		return false;
	}

	/**
	 * Method called after the file is processed.
	 *  This method must be overload in the child class.
	 */
	function postPreImportData() {
		global $adb;
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'RSNEmailListes');
		
		//Création des index
		$query = "ALTER TABLE `$tableName` ADD INDEX(`email`)";
		$adb->query($query);
		$query = "ALTER TABLE `$tableName` ADD INDEX(`name`)";
		$adb->query($query);
		
		
		// Pré-identifie les emails
		$query = "UPDATE `$tableName`
			JOIN vtiger_contactemails
				ON vtiger_contactemails.email = `$tableName`.email
			JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_contactemails.contactemailsid
			JOIN vtiger_crmentity vtiger_crmentity_contacts
				ON vtiger_crmentity_contacts.crmid = vtiger_contactemails.contactid
			SET _contactemailsid = vtiger_contactemails.contactemailsid
			WHERE vtiger_crmentity_contacts.deleted = 0
			AND vtiger_crmentity.deleted = 0
			AND `$tableName`.status = ?
		";
		$params = array(RSNImportSources_Data_Action::$IMPORT_RECORD_NONE);
		$result = $adb->pquery($query, $params);
		if(!$result){
			$adb->echoError($query);
			die();
		}
		
		// Failed pour tous les emails inconnus
		$query = "UPDATE `$tableName`
			SET status = ?
			WHERE (_contactemailsid IS NULL OR _contactemailsid = '')
			AND `$tableName`.status = ?
		";
		$params = array(RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED, RSNImportSources_Data_Action::$IMPORT_RECORD_NONE);
		$result = $adb->pquery($query, $params);
		if(!$result){
			$adb->echoError($query);
			die();
		}
		
		
		// Pré-identifie les listes
		$query = "UPDATE `$tableName`
			JOIN vtiger_rsnemaillistes
				ON vtiger_rsnemaillistes.name = `$tableName`.name
			JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_rsnemaillistes.rsnemaillistesid
			SET recordid = vtiger_rsnemaillistes.rsnemaillistesid
			, _rsnemaillistesid = vtiger_rsnemaillistes.rsnemaillistesid
			WHERE vtiger_crmentity.deleted = 0
			AND `$tableName`.status = ?
		";
		$params = array(RSNImportSources_Data_Action::$IMPORT_RECORD_NONE);
		$result = $adb->pquery($query, $params);
		if(!$result){
			$adb->echoError($query);
			die();
		}
		
		$this->ignoreKnownRelations();
		
		//Crée les relations dont on connait les paramètres
		$this->insertEmailsRelation();
	}
    
	function ignoreKnownRelations(){
		
		global $adb;
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'RSNEmailListes');
		
		// Ignore les relations déjà connues
		$query = "UPDATE `$tableName`
			JOIN vtiger_rsnemaillistesrel
				ON vtiger_rsnemaillistesrel.rsnemaillistesid = `$tableName`._rsnemaillistesid
				AND vtiger_rsnemaillistesrel.contactemailsid = `$tableName`._contactemailsid
			SET `$tableName`.status = ?
			WHERE `$tableName`._rsnemaillistesid IS NOT NULL AND `$tableName`._rsnemaillistesid != ''
			AND `$tableName`._contactemailsid IS NOT NULL AND `$tableName`._contactemailsid != ''
			AND `$tableName`.status = ?
		";
		$params = array(RSNImportSources_Data_Action::$IMPORT_RECORD_SKIPPED, RSNImportSources_Data_Action::$IMPORT_RECORD_NONE);
		$result = $adb->pquery($query, $params);
		if(!$result){
			$adb->echoError($query);
			die();
		}
	}
	
	//Crée la relation entre les listes et les emails
	function insertEmailsRelation($rsnemaillistesId = false){
		
		global $adb;
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'RSNEmailListes');
		
		$query = "INSERT INTO vtiger_rsnemaillistesrel (`rsnemaillistesid`, `contactemailsid`, `datesubscribe`, `dateunsubscribe`, `data`)
			SELECT `_rsnemaillistesid`, vtiger_contactemails.`contactemailsid`, `datesubscribe`, `dateunsubscribe`, '4D'
			FROM `$tableName`
			JOIN vtiger_contactemails vtiger_contactemails_main
				ON vtiger_contactemails_main.contactemailsid = `$tableName`._contactemailsid
			JOIN vtiger_contactemails 
				ON vtiger_contactemails.email = vtiger_contactemails_main.email
			JOIN vtiger_crmentity
				ON vtiger_contactemails.contactemailsid = vtiger_crmentity.crmid
			WHERE vtiger_crmentity.deleted = 0
			AND `$tableName`._rsnemaillistesid IS NOT NULL AND `$tableName`._rsnemaillistesid != ''
			AND `$tableName`._contactemailsid IS NOT NULL AND `$tableName`._contactemailsid != ''
		";
		if($rsnemaillistesId){
			$query .= " AND _rsnemaillistesId = ?";
			$params = array($rsnemaillistesId);
		} else {
			$query .= " AND `$tableName`.status = ?";
			$params = array(RSNImportSources_Data_Action::$IMPORT_RECORD_NONE);
		}
		$query .= " ON DUPLICATE KEY
			UPDATE `datesubscribe` = `$tableName`.`datesubscribe`
			, `dateunsubscribe` = `$tableName`.`dateunsubscribe`
		";
		$result = $adb->pquery($query, $params);
		if(!$result){
			$adb->echoError($query);
			die();
		}
		
		$this->ignoreKnownRelations();
	}
	
	
	/**
	 * Method that check if a string is a formatted date (DD/MM/YYYY).
	 * @param string $string : the string to check.
	 * @return boolean - true if the string is a date.
	 */
	function isDate($string) {
		//TODO do not put this function here ?
		return preg_match("/^[0-3]?[0-9][-\/][0-1]?[0-9][-\/](20)?[0-9][0-9]/", $string);//only true for french format
	}
	/**
	 * Method that returns a formatted date for mysql (Y-m-d).
	 * @param string $string : the string to format.
	 * @return string - formated date.
	 */
	function getMySQLDate($string) {
		$dateArray = preg_split('/[-\/]/', $string);
		return '20'.$dateArray[2] . '-' . $dateArray[1] . '-' . $dateArray[0];
	}

	/**
	 * Method that check if a line of the file is a client information line.
	 *  It assume that the line is a client information line only and only if the first data is a date.
	 * @param array $line : the data of the file line.
	 * @return boolean - true if the line is a client information line.
	 */
	function isRecordHeaderInformationLine($line) {
		if (sizeof($line) > 0 && $line[0] && $this->isDate($line[1])) {
			return true;
		}

		return false;
	}

	/**
	 * Method that move the cursor of the file reader to the beginning of the next found invoice.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - false if error or if no invoice found.
	 */
	function moveCursorToNextRSNEmailListes(RSNImportSources_FileReader_Reader $fileReader) {
		do {
			$cursorPosition = $fileReader->getCurentCursorPosition();
			$nextLine = $fileReader->readNextDataLine($fileReader);
					
			if ($nextLine == false) {
				return false;
			}

		} while(!$this->isRecordHeaderInformationLine($nextLine));

		$fileReader->moveCursorTo($cursorPosition);

		return true;
	}

	/**
	 * Method that return the information of the next first invoice found in the file.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return the invoice information | null if no invoice found.
	 */
	function getNextRSNEmailListes(RSNImportSources_FileReader_Reader $fileReader) {
		$nextLine = $fileReader->readNextDataLine($fileReader);
		if ($nextLine != false) {
			$rsnemaillistes = array(
				'header' => $nextLine,
				'detail' => array());
			do {
				$cursorPosition = $fileReader->getCurentCursorPosition();
				$nextLine = $fileReader->readNextDataLine($fileReader);

				if (!$this->isRecordHeaderInformationLine($nextLine)) {
					if ($nextLine[1] != null && $nextLine[1] != '') {
						//impossible ici array_push($rsnemaillistes['detail'], $nextLine);
					}
				} else {
					break;
				}

			} while ($nextLine != false);

			if ($nextLine != false) {
				$fileReader->moveCursorTo($cursorPosition);
			}

			return $rsnemaillistes;
		}

		return null;
	}
	
	/**
	 * Method that return the formated information of an invoice found in the file.
	 * @param $rsnemaillistes : the invoice data found in the file.
	 * @return array : the formated data of the invoice.
	 */
	function getRSNEmailListesValues($rsnemaillistes) {
		
		$fields = $this->getRSNEmailListesFields();
		
		// contrôle l'égalité des tailles de tableaux
		if(count($fields) != count($rsnemaillistes['header'])){
			if(count($fields) > count($rsnemaillistes['header']))
				$rsnemaillistes['header'] = array_merge($rsnemaillistes['header'], array_fill (0, count($fields) - count($rsnemaillistes['header']), null));
			else
				$rsnemaillistes['header'] = array_slice($rsnemaillistes['header'], 0, count($fields));
		}
		//tableau associatif dans l'ordre fourni
		$rsnemaillistesHeader = array_combine($fields, $rsnemaillistes['header']);
		
		//Parse dates
		foreach($this->getRSNEmailListesDateFields() as $fieldName)
			$rsnemaillistesHeader[$fieldName] = $this->getMySQLDate($rsnemaillistesHeader[$fieldName]);
		
		$fieldName = 'email';
		$rsnemaillistesHeader[$fieldName] = strtolower(trim($rsnemaillistesHeader[$fieldName]));
		
		return $rsnemaillistesHeader;
	}
	
}