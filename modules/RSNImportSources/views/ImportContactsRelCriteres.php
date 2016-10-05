<?php

/* Phase de migration
 * Importation de relation entre contacts et critères depuis le fichier de saisie
 */
class RSNImportSources_ImportContactsRelCriteres_View extends RSNImportSources_ImportFromFile_View {
        
	/**
	 * Method to get the source import label to display.
	 * @return string - The label.
	 */
	public function getSource() {
		return 'LBL_CONTACTSRELCRITERES_WEB';
	}

	/**
	 * Method to get the modules that are concerned by the import.
	 * @return array - An array containing concerned module names.
	 */
	public function getImportModules() {
		return array('Contacts');
	}
	
	/**
	 * Method to default file enconding for this import.
	 * @return string - the default file encoding.
	 */
	public function getDefaultFileEncoding() {
		return 'UTF8';
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
	 * Method to get the imported fields for the contacts module.
	 * @return array - the imported fields for the contacts module.
	 */
	function getContactsFieldsMapping() {
		//laisser exactement les colonnes du fichier, dans l'ordre 
		return array (
			//pour la pré-validation des données des contacts, il faut que les noms des champs soient les mêmes
			"contact_no" => "contact_no",
			"critere4d" => "nom",
			"dateapplication" => "dateapplication",
			
			//champ supplémentaire
			'_contactid' => '', //Contact Id. May be many. Massively updated after preImport
			'_critere4did' => '', //Critere4D
		);
	}
	
	function getContactsDateFields(){
		return array("dateapplication");
	}
	
	/**
	 * Method to get the imported fields for the contacts module.
	 * @return array - the imported fields for the contacts module.
	 */
	function getContactsFields() {
		//laisser exactement les colonnes du fichier
		return array_keys($this->getContactsFieldsMapping());
	}

	/**
	 * Method to process to the import of the Contacts module.
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importContacts($importDataController) {
		
		$config = new RSNImportSources_Config_Model();
		
		$this->ignoreCancelledContactsOnImport();
		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($this->user, 'Contacts');
		$sql = 'SELECT * FROM ' . $tableName . '
			WHERE status = '. RSNImportSources_Data_Action::$IMPORT_RECORD_NONE . '
			ORDER BY id';

		$result = $adb->query($sql);
		$numberOfRecords = $adb->num_rows($result);

		if ($numberOfRecords <= 0) {
			return;
		}

		for ($i = 0; $i < $numberOfRecords; ++$i) {
			$row = $adb->raw_query_result_rowdata($result, $i);
			$this->importOneContacts(array($row), $importDataController);
		}

	}

	/**
	 * Method to process to the import of a one contact.
	 * @param $contactsData : the data of the contact to import
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importOneContacts($contactsData, $importDataController) {
					
		global $log;
		
		$contactId = $contactsData[0]['_contactid']; // initialisé dans le postPreImportData
		$critere4DId = $contactsData[0]['_critere4did']; // initialisé dans le postPreImportData
		if(!$contactId || !$critere4DId){
			//failed
			foreach ($contactsData as $contactsLine) {
				$entityInfo = array(
					'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
				);
				
				//TODO update all with array
				$importDataController->updateImportStatus($contactsLine[id], $entityInfo);
			}
		}
		else {
			
			//Relations 
			$this->createContactRelatedCritere($contactId, $critere4DId, $contactsData[0]['dateapplication']);
			
			$entryId = $contactId;
			foreach ($contactsData as $contactsLine) {
				$entityInfo = array(
					'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_CREATED,
					'id'		=> $entryId
				);
				$importDataController->updateImportStatus($contactsLine[id], $entityInfo);
			}
		}

		return true;
	}
	
	//Mise à jour des données du record model nouvellement créé à partir des données d'importation
	private function createContactRelatedCritere($contactId, $critere4DId, $dateapplication){
		
		$db = PearDatabase::getInstance();
		
		$query = "INSERT INTO `vtiger_critere4dcontrel` (`critere4did`, `contactid`, `dateapplication`, `data`) 
			VALUES(?, ?, ?, NULL)
			ON DUPLICATE KEY UPDATE data = data
		";
		$params = array(
			$critere4DId, $contactId, $dateapplication,
		);
		$result = $db->pquery($query, $params);
		if(!$result){
			$db->echoError($query);
			die();
		}
	}
	
	/**
	 * Method that pre import a contact.
	 *  It adds one row in the temporary pre-import table by contact line.
	 * @param $contactsData : the data of the contact to import.
	 */
	function preImportContact($contactsData) {
		
		$contactsValues = $this->getContactsValues($contactsData);
		
		$contacts = new RSNImportSources_Preimport_Model($contactsValues, $this->user, 'Contacts');
		$contacts->save();
	}
	
	/**
	 * Method to parse the uploaded file and save data to the temporary pre-import table.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - true if pre-import is ended successfully
	 */
	function parseAndSaveFile(RSNImportSources_FileReader_Reader $fileReader) {
		$this->clearPreImportTable();
		
		if($fileReader->open()) {
			if ($this->moveCursorToNextContact($fileReader)) {
				$i = 0;
				do {
					$contact = $this->getNextContact($fileReader);
					if ($contact != null) {
						$this->preImportContact($contact);
					}
				} while ($contact != null);

			}

			$fileReader->close(); 
			return true;
		} else {
			//TODO: manage error
			echo "<code>Erreur : le fichier n'a pas pu être ouvert...</code>";
		}
		return false;
	}

	/**
	 * Method called after the file is processed.
	 *  This method must be overload in the child class.
	 */
	function postPreImportData() {
		$db = PearDatabase::getInstance();
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'Contacts');
		
		/* création d'un index */
		$query = "ALTER TABLE `$tableName` ADD INDEX(`critere4d`)";
		$db->pquery($query);
		
		/* création d'un index */
		$query = "ALTER TABLE `$tableName` ADD INDEX(`contact_no`)";
		$db->pquery($query);
		
		/* Affecte l'id du critere */
		$query = "UPDATE $tableName
			JOIN vtiger_critere4d
				ON vtiger_critere4d.nom = `$tableName`.critere4d
			JOIN vtiger_crmentity
				ON vtiger_critere4d.critere4did = vtiger_crmentity.crmid
		";
		$query .= " SET `_critere4did` = vtiger_crmentity.crmid";
		$query .= "
			WHERE `$tableName`.status = ".RSNImportSources_Data_Action::$IMPORT_RECORD_NONE."
			AND vtiger_crmentity.deleted = 0
		";
		$result = $db->pquery($query, array());
		if(!$result){
			echo '<br><br><br><br>';
			$db->echoError($query);
			echo("<pre>$query</pre>");
			die();
		}
		
		/* Affecte l'id du critere */
		$query = "UPDATE $tableName
			JOIN vtiger_contactdetails
				ON vtiger_contactdetails.contact_no = `$tableName`.contact_no
			JOIN vtiger_crmentity
				ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
		";
		$query .= " SET `_contactid` = vtiger_crmentity.crmid";
		$query .= "
			WHERE `$tableName`.status = ".RSNImportSources_Data_Action::$IMPORT_RECORD_NONE."
			AND vtiger_crmentity.deleted = 0
		";
		$result = $db->pquery($query, array());
		if(!$result){
			echo '<br><br><br><br>';
			$db->echoError($query);
			echo("<pre>$query</pre>");
			die();
		}
		
		
		/* FAILED pour les inconnus */
		$query = "UPDATE $tableName
		";
		$query .= " SET `status` = ?";
		$query .= "
			WHERE `$tableName`.status = ".RSNImportSources_Data_Action::$IMPORT_RECORD_NONE."
			AND (_contactid IS NULL OR _critere4did IS NULL)
		";
		$result = $db->pquery($query, array(RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED));
		if(!$result){
			echo '<br><br><br><br>';
			$db->echoError($query);
			echo("<pre>$query</pre>");
			die();
		}
		
		return true;
	}
        
	/**
	 * Method that check if a string is a formatted date (DD/MM/YYYY).
	 * @param string $string : the string to check.
	 * @return boolean - true if the string is a date.
	 */
	function isDate($string) {
		//TODO do not put this function here ?
		//already mysql format
		return preg_match("/^[0-3]?[0-9][-\/][0-1]?[0-9][-\/](20)?[0-9][0-9]/", $string);//only true for french format
	}
	/**
	 * Method that returns a formatted date for mysql (Y-m-d).
	 * @param string $string : the string to format.
	 * @return string - formated date.
	 */
	function getMySQLDate($string) {
		if(!$string || $string === '00/00/00')
			return null;
		$dateArray = preg_split('/[-\/]/', $string);
		if(strlen($dateArray[2]) < 4)
		$dateArray[2] = 2000 + $dateArray[2];
		return $dateArray[2] . '-' . $dateArray[1] . '-' . $dateArray[0];
	}

	/**
	 * Method that check if a line of the file is a contact information line.
	 *  It assume that the line is a client information line only and only if the first data is a date.
	 * @param array $line : the data of the file line.
	 * @return boolean - true if the line is a contact information line.
	 */
	function isRecordHeaderInformationLine($line) {
		if (sizeof($line) > 0 && $line[0] && (is_numeric($line[0]) || is_numeric(substr($line[0],1))) && $line[1]) {
			return true;
		}

		return false;
	}

	/**
	 * Method that move the cursor of the file reader to the beginning of the next found invoice.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - false if error or if no invoice found.
	 */
	function moveCursorToNextContact(RSNImportSources_FileReader_Reader $fileReader) {
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
	function getNextContact(RSNImportSources_FileReader_Reader $fileReader) {
		$nextLine = $fileReader->readNextDataLine($fileReader);
		if ($nextLine != false) {
			$contact = array(
				'header' => $nextLine,
				'detail' => array());
			do {
				$cursorPosition = $fileReader->getCurentCursorPosition();
				$nextLine = $fileReader->readNextDataLine($fileReader);

				if (!$this->isRecordHeaderInformationLine($nextLine)) {
					if ($nextLine[1] != null && $nextLine[1] != '') {
						//impossible ici array_push($contact['detail'], $nextLine);
					}
				} else {
					break;
				}

			} while ($nextLine != false);

			if ($nextLine != false) {
				$fileReader->moveCursorTo($cursorPosition);
			}
			return $contact;
		}

		return null;
	}
	
	/**
	 * Method that return the formated information of an invoice found in the file.
	 * @param $contacts : the invoice data found in the file.
	 * @return array : the formated data of the invoice.
	 */
	function getContactsValues($contacts) {
		$fields = $this->getContactsFields();
		
		//trim de tous les champs
		RSNImportSources_Utils_Helper::array_map_trim($contacts['header']);
		
		// contrôle l'égalité des tailles de tableaux
		if(count($fields) != count($contacts['header'])){
			if(count($fields) > count($contacts['header']))
				$contacts['header'] = array_merge($contacts['header'], array_fill (0, count($fields) - count($contacts['header']), null));
			else
				$contacts['header'] = array_slice($contacts['header'], 0, count($fields));
		}
		//tableau associatif dans l'ordre fourni
		$contactsHeader = array_combine($fields, $contacts['header']);
		
		//Parse dates
		foreach($this->getContactsDateFields() as $fieldName)
			$contactsHeader[$fieldName] = $this->getMySQLDate($contactsHeader[$fieldName]);
		
		//numérique
		if(is_numeric($contactsHeader['contact_no']))
			$contactsHeader['contact_no'] = 'C' . $contactsHeader['contact_no'];
		
		if(!$contactsHeader['dateapplication'])
			$contactsHeader['dateapplication'] = date('Y-m-d');
			
		return $contactsHeader;
	}
	
}