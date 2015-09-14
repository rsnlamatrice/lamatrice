<?php

/* Phase de migration
 * Importation des prelevements Web depuis le fichier provenant de 4D
 */
class RSNImportSources_ImportPetitionsWeb_View extends RSNImportSources_ImportFromFile_View {
        
	var $relatedDocumentId = false;
	var $relatedDocumentName = false;
		
	/**
	 * Method to get the source import label to display.
	 * @return string - The label.
	 */
	public function getSource() {
		return 'LBL_PETITIONS_WEB';
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
		return 'ISO-8859-1';
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
			
			"prenom" => "firstname",
			"nom" => "lastname",
			"adresse1" => "mailingstreet",
			"adresse2" => "mailingstreet3",
			"code" => "mailingzip",
			"ville" => "mailingcity",
			"pays" => "mailingcountry",
			"mails" => "email",
			"listesn" => "listesn",
			"listesd" => "listesd",
			"date" => "dateapplication",//format MySQL
			"ip" => "address_ip",
			"x" => "_no_use_of_x",
			
			//champ supplémentaire
			'_contactid' => '', //Contact Id. May be many. Massively updated after preImport
			'_contactid_status' => '', //Type de reconnaissance automatique. Massively updated after preImport
			'_notesid' => '', //Document Pétition. Massively updated after preImport
		);
	}
	
	function getContactsDateFields(){
		return array(
		);
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
		
		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($this->user, 'Contacts');
		$sql = 'SELECT * FROM ' . $tableName . ' WHERE status = '. RSNImportSources_Data_Action::$IMPORT_RECORD_NONE . ' ORDER BY id';

		$result = $adb->query($sql);
		$numberOfRecords = $adb->num_rows($result);

		if ($numberOfRecords <= 0) {
			return;
		}
		if($numberOfRecords == $config->get('importBatchLimit')){
			$this->keepScheduledImport = true;
		}

		$perf = new RSNImportSources_Utils_Performance($numberOfRecords);
		for ($i = 0; $i < $numberOfRecords; ++$i) {
			$row = $adb->raw_query_result_rowdata($result, $i);
			$this->importOneContacts(array($row), $importDataController);
			$perf->tick();
			if(Import_Utils_Helper::isMemoryUsageToHigh()){
				$this->skipNextScheduledImports = true;
				$keepScheduledImport = true;
				$size = RSNImportSources_Utils_Performance::getMemoryUsage();
				echo '
<pre>
	<b> '.vtranslate('LBL_MEMORY_IS_OVER', 'Import').' : '.$size.' </b>
</pre>
';
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
	 * @param $contactsData : the data of the prelevement to import
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importOneContacts($contactsData, $importDataController) {
					
		global $log;
		
		$entryId = $contactsData[0]['_contactid']; // initialisé dans le postPreImportData
		if($entryId){
			//clean up concatened ids
			$entryId = preg_replace('/(^,+|,+$|,(,+))/', '$2', $entryId);
			if(strpos($entryId, ',')){
				//Contacts multiples : non validable
				return false;
			}
		}
		if(is_numeric($entryId)){
			$record = Vtiger_Record_Model::getInstanceById($entryId, 'Contacts');
			//Relations  only
			$this->createContactRelatedDocument($record, $contactsData);
			
			//already imported !!
			foreach ($contactsData as $contactsLine) {
				$entityInfo = array(
					'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_UPDATED,
					'id'		=> $entryId
				);
				
				//TODO update all with array
				$importDataController->updateImportStatus($contactsLine[id], $entityInfo);
			}
		}
		else {
			$record = Vtiger_Record_Model::getCleanInstance('Contacts');
			$record->set('mode', 'create');
			
			$this->updateContactRecordModelFromData($record, $contactsData);
			
			//$db->setDebug(true);
			$record->save();
			$contactId = $record->getId();
			
			if(!$contactId){
				//TODO: manage error
				echo "<pre><code>Impossible d'enregistrer le contact</code></pre>";
				foreach ($contactsData as $contactsLine) {
					$entityInfo = array(
						'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
					);
					
					//TODO update all with array
					$importDataController->updateImportStatus($contactsLine[id], $entityInfo);
				}

				return false;
			}
			
			$entryId = $this->getEntryId("Contacts", $contactId);
			foreach ($contactsData as $contactsLine) {
				$entityInfo = array(
					'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_CREATED,
					'id'		=> $entryId
				);
				$importDataController->updateImportStatus($contactsLine[id], $entityInfo);
			}
			
			$record->set('mode','edit');
			$db = PearDatabase::getInstance();
			$query = "UPDATE vtiger_crmentity
				SET smownerid = ?
				, createdtime = ?
				WHERE vtiger_crmentity.crmid = ?
			";
			$result = $db->pquery($query, array(ASSIGNEDTO_ALL
								, $contactsData[0]['date']
								, $contactId));
			
			$log->debug("" . basename(__FILE__) . " update imported contacts (id=" . $record->getId() . ", Ref 4D=$sourceId , date=" . $contactsData[0]['datecreation']
					. ", result=" . ($result ? " true" : "false"). " )");
			if( ! $result)
				$db->echoError();
			else {
				//Relations 
				$this->createContactRelatedDocument($record, $contactsData);
			}
			return $record;
		}

		return true;
	}

	//Mise à jour des données du record model nouvellement créé à partir des données d'importation
	private function updateContactRecordModelFromData($record, $contactsData){
		
		$fieldsMapping = $this->getContactsFieldsMapping();
		foreach($contactsData[0] as $fieldName => $value)
			if(!is_numeric($fieldName) && $fieldName != 'id'){
				$vField = $fieldsMapping[$fieldName];
				if($vField)
					$record->set($vField, $value);
			}
					
		//cast des DateTime
		foreach($this->getContactsDateFields() as $fieldName){
			$value = $record->get($fieldName);
			if( is_object($value) )
				$record->set($fieldsMapping[$fieldName], $value->format('Y-m-d'));
		}
		
		$fieldName = 'isgroup';
		$record->set('isgroup', 0);
		$record->set('leadsource', 'PETITION');//TODO
		
		// copie depuis tout en haut
		//
		//
			
		//"prenom" => "firstname",
		//"nom" => "lastname",
		//"adresse1" => "mailingstreet",
		//"adresse2" => "mailingstreet3",
		//"code" => "mailingzip",
		//"ville" => "mailingcity",
		//"pays" => "mailingcountry",
		//"mails" => "mails",
		//"listesn" => "listesn",
		//"listesd" => "listesd",
		//"date" => "dateapplication",//format MySQL
		//"ip" => "address_ip",
		//"x" => "_no_use_of_x",
		//
		
	}
	
	//Mise à jour des données du record model nouvellement créé à partir des données d'importation
	private function createContactRelatedDocument($record, $contactsData){
		
		$db = PearDatabase::getInstance();
		
		/* Affecte l'id de la pétition de cet import */
		$query = "INSERT INTO `vtiger_senotesrel` (`crmid`, `notesid`, `dateapplication`, `data`) 
			VALUES(?, ?, ?, ?)
			ON DUPLICATE KEY UPDATE data = data
		";
		$params = array(
			$record->getId(),
			$contactsData[0]['_notesid'],
			$contactsData[0]['date'],
			$contactsData[0]['ip'],
			
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
		if($this->relatedDocumentId){
			$db = PearDatabase::getInstance();
			$tableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'Contacts');
			
			/* Affecte l'id de la pétition de cet import */
			$query = "UPDATE $tableName
			";
			$query .= " SET `_notesid` = ?";
			$query .= "
				WHERE `$tableName`.status = ".RSNImportSources_Data_Action::$IMPORT_RECORD_NONE."
				AND (`$tableName`.`_notesid` IS NULL OR `$tableName`.`_notesid` = '')
			";
			$result = $db->pquery($query, array($this->relatedDocumentId));
			if(!$result){
				echo '<br><br><br><br>';
				$db->echoError($query);
				echo("<pre>$query</pre>");
				die();
			}
		}
		
		// Pré-identifie les contacts
		
		$fields = array_flip($this->getContactsFieldsMapping());//Remplace les clés par les valeurs, et les valeurs par les clés
		unset($fields['']);
		
		
		RSNImportSources_Utils_Helper::setPreImportDataContactIdByFields(
			$this->user,
			'Contacts',
			'_contactid',
			$fields,
			'_contactid_status',
			'Tout'
		);

		$partialFields = $fields;
		unset($partialFields['mailingstreet']);
		unset($partialFields['mailingstreet2']);
		RSNImportSources_Utils_Helper::setPreImportDataContactIdByFields(
			$this->user,
			'Contacts',
			'_contactid',
			$partialFields,
			'_contactid_status',
			'Tout sauf adresse1 et adresse2'
		);
		
		$partialFields = array('email' => $fields['email', 'lastname' => $fields['lastname'], 'firstname' => $fields['firstname'], 'mailingzip' => $fields['mailingzip']);
		RSNImportSources_Utils_Helper::setPreImportDataContactIdByFields(
			$this->user,
			'Contacts',
			'_contactid',
			$partialFields,
			'_contactid_status',
			'Email, nom, prénom et code postal'
		);
		
		$partialFields = array('email' => $fields['email', 'lastname' => $fields['lastname'], 'firstname' => $fields['firstname']);
		RSNImportSources_Utils_Helper::setPreImportDataContactIdByFields(
			$this->user,
			'Contacts',
			'_contactid',
			$partialFields,
			'_contactid_status',
			'Email, nom et prénom'
		);
		
		$partialFields = array('lastname' => $fields['lastname'], 'firstname' => $fields['firstname'], 'mailingzip' => $fields['mailingzip']);
		RSNImportSources_Utils_Helper::setPreImportDataContactIdByFields(
			$this->user,
			'Contacts',
			'_contactid',
			$partialFields,
			'_contactid_status',
			'Nom, prénom et code postal'
		);
		
		$partialFields = array('email' => $fields['email']);
		RSNImportSources_Utils_Helper::setPreImportDataContactIdByFields(
			$this->user,
			'Contacts',
			'_contactid',
			$partialFields,
			'_contactid_status',
			'Email seul'
		);
		
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
		return preg_match("/^(20)?[0-9][0-9][-\/][0-1]?[0-9][-\/][0-3]?[0-9]/", $string);//only true for french format
	}
	/**
	 * Method that returns a formatted date for mysql (Y-m-d).
	 * @param string $string : the string to format.
	 * @return string - formated date.
	 */
	function getMySQLDate($string) {
		if(!$string)
			return null;
		return $string;
	}

	/**
	 * Method that check if a line of the file is a contact information line.
	 *  It assume that the line is a client information line only and only if the first data is a date.
	 * @param array $line : the data of the file line.
	 * @return boolean - true if the line is a contact information line.
	 */
	function isRecordHeaderInformationLine($line) {
		if (sizeof($line) > 0 && $line[7] && $this->isDate($line[10])) {
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
		
		return $contactsHeader;
	}
	
	
	/**
	 * Method to show the configuration template of the import for the first step.
	 *  It display the select file template.
	 * @param Vtiger_Request $request: the curent request.
	 */
	function showConfiguration(Vtiger_Request $request) {
		parent::showConfiguration($request);
	
		$viewer = $this->initConfigurationToSelectRelatedModule($request, 'Documents', 'folderid', 'Pétitions');
		return $viewer->view('ImportSelectRelatedRecordStep.tpl', 'RSNImportSources');
	}
	
	/**
	 * Method to process to the first step (pre-importing data).
	 *  It calls the parseAndSave methode that must be implemented in the child class.
	 */
	public function preImportData(Vtiger_Request $request) {
		$fieldName = $request->get('related_record_fieldname');
		$this->relatedDocumentId = $request->get($fieldName);
		$this->relatedDocumentName = $request->get($fieldName . '_display');
		return parent::preImportData($request);
	}
}