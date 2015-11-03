<?php


/* Phase de migration
 * Importation des prelevements Web depuis le fichier provenant de 4D
 */
class RSNImportSources_ImportLiensContactsFrom4D_View extends RSNImportSources_ImportFromFile_View {
        
	/**
	 * Method to get the source import label to display.
	 * @return string - The label.
	 */
	public function getSource() {
		return 'LBL_LIENSCONTACTS_4D';
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
	 * Method to get the imported fields for the contact emails module.
	 * @return array - the imported fields for the contact emails module.
	 */
	function getContactsFieldsMapping() {
		//laisser exactement les colonnes du fichier, dans l'ordre 
		return array(
			'ref4d' => 'ref4d',
			'ref4d2' => 'ref4d2',
			'typelien' => 'contreltype',
			'date_lien' => 'dateapplication',
			
			/* post pré-import */
			'_contactid' => '',
			'_relcontid' => '',//relcontid
		);
	}
	
	function getContactsDateFields(){
		return array(
			'date_lien'
		);
	}
	/**
	 * Method to get the imported fields for the contact emails module.
	 * @return array - the imported fields for the contact emails module.
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
		global $VTIGER_BULK_SAVE_MODE;
		$VTIGER_BULK_SAVE_MODE = true;
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

		$perf = new RSN_Performance_Helper($numberOfRecords);
		for ($i = 0; $i < $numberOfRecords; ++$i) {
			$row = $adb->raw_query_result_rowdata($result, $i);
			$this->importOneContacts(array($row), $importDataController);
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
	 * @param $relatedcontactsData : the data of the prelevement to import
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importOneContacts($relatedcontactsData, $importDataController) {
					
		global $log;
		
		//TODO check sizeof $relatedcontactsata
		$sourceId = $relatedcontactsData[0]['ref4d'];
		$contactId = $relatedcontactsData[0]['_contactid']; // initialisé dans le postPreImportData
		$relContactId = $relatedcontactsData[0]['_relcontid']; // initialisé dans le postPreImportData
		if ($contactId && $relContactId) {
			//test sur email == $sourceId
			$query = "SELECT 1
				FROM vtiger_contactscontrel
				JOIN vtiger_crmentity
					ON vtiger_contactscontrel.contactid = vtiger_crmentity.crmid
				JOIN vtiger_crmentity vtiger_crmentity_rel
					ON vtiger_contactscontrel.relcontid = vtiger_crmentity_rel.crmid
				WHERE ((contactid = ?
					AND relcontid = ?)
				OR (relcontid = ?
					AND contactid = ?)
				)
				AND vtiger_crmentity.deleted = FALSE
				AND vtiger_crmentity_rel.deleted = FALSE
				LIMIT 1
			";
			$db = PearDatabase::getInstance();
			$result = $db->pquery($query, array($contactId, $relContactId, $contactId, $relContactId));
			if($db->num_rows($result)){
				//already imported !!
				$row = $db->fetch_row($result, 0); 
				$entryId = $this->getEntryId("Contacts", $contactId);
				foreach ($relatedcontactsData as $relatedcontactsLine) {
					$entityInfo = array(
						'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_SKIPPED,
						'id'		=> $entryId
					);
					
					//TODO update all with array
					$importDataController->updateImportStatus($relatedcontactsLine[id], $entityInfo);
				}
			}
			else {
				
				$query = "INSERT INTO vtiger_contactscontrel (`contactid`, `relcontid`, `contreltype`, `dateapplication`, `data`)
					VALUES (?,?,?,?, NULL)
				";
				$result = $db->pquery($query, array($contactId
								, $relContactId
								, $relatedcontactsData[0]['typelien']
								, $relatedcontactsData[0]['date_lien']
								));
			
				$log->debug("" . basename(__FILE__) . " update imported contacts relation (id=" . $contactId . ", " . $relContactId
						. ", result=" . ($result ? " true" : "false"). " )");
				if( ! $result){
					$db->echoError();
					
					foreach ($relatedcontactsData as $relatedcontactsLine) {
						$entityInfo = array(
							'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
						);
						$importDataController->updateImportStatus($relatedcontactsLine[id], $entityInfo);
					}
				}
				else {
					$entryId = $this->getEntryId("Contacts", $contactId);
					foreach ($relatedcontactsData as $relatedcontactsLine) {
						$entityInfo = array(
							'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_CREATED,
							'id'		=> $entryId
						);
						$importDataController->updateImportStatus($relatedcontactsLine[id], $entityInfo);
					}
				}	
					
				
				return $record;
			}
		} else {
			foreach ($relatedcontactsData as $relatedcontactsLine) {//TODO: remove duplicated code
				$entityInfo = array(
					'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
				);
				
				$importDataController->updateImportStatus($relatedcontactsLine[id], $entityInfo);
			}

			return false;
		}

		return true;
	}	

	/**
	 * Method that pre import an invoice.
	 *  It adds one row in the temporary pre-import table by invoice line.
	 * @param $relatedcontactsData : the data of the invoice to import.
	 */
	function preImportContacts($relatedcontactsData) {
		
		$relatedcontactsValues = $this->getContactsValues($relatedcontactsData);
		
		$relatedcontacts = new RSNImportSources_Preimport_Model($relatedcontactsValues, $this->user, 'Contacts');
		$relatedcontacts->save();
	}

	/**
	 * Method that retrieve a contact.
	 * @param string $firstname : the firstname of the contact.
	 * @param string $lastname : the lastname of the contact.
	 * @param string $email : the mail of the contact.
	 * @return the row data of the contact | null if the contact is not found.
	 */
	function getContact($ref4d) {
		$id = false;
		if(is_array($ref4d)){
			//$ref4d is $rsnprelvirementsData
			if($ref4d[0]['_contactid'])
				$id = $ref4d[0]['_contactid'];
			else{
				$ref4d = $ref4d[0]['reffiche'];
			}
		}
		if(!$id)
			$id = $this->getContactIdFromRef4D($ref4d);
		if($id){
			return Vtiger_Record_Model::getInstanceById($id, 'Contacts');
		}

		return null;
	}
	
	/**
	 * Method to parse the uploaded file and save data to the temporary pre-import table.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - true if pre-import is ended successfully
	 */
	function parseAndSaveFile(RSNImportSources_FileReader_Reader $fileReader) {
		$this->clearPreImportTable();
		
		if($fileReader->open()) {
			if ($this->moveCursorToNextContacts($fileReader)) {
				$i = 0;
				do {
					$relatedcontacts = $this->getNextContacts($fileReader);
					if ($relatedcontacts != null) {
						$this->preImportContacts($relatedcontacts);
					}
				} while ($relatedcontacts != null);

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
		// Pré-identifie les contacts
		
		RSNImportSources_Utils_Helper::setPreImportDataContactIdByRef4D(
			$this->user,
			'Contacts',
			'ref4d',
			'_contactid',
			/*$changeStatus*/ false
		);
		
		RSNImportSources_Utils_Helper::setPreImportDataContactIdByRef4D(
			$this->user,
			'Contacts',
			'ref4d2',
			'_relcontid',
			/*$changeStatus*/ false
		);
	
		RSNImportSources_Utils_Helper::skipPreImportDataForMissingContactsByRef4D(
			$this->user,
			'Contacts',
			'_contactid'
		);
	
		RSNImportSources_Utils_Helper::skipPreImportDataForMissingContactsByRef4D(
			$this->user,
			'Contacts',
			'_relcontid'
		);
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
		if (sizeof($line) > 0 && is_numeric($line[0]) && $this->isDate($line[3])) {
			return true;
		}

		return false;
	}

	/**
	 * Method that move the cursor of the file reader to the beginning of the next found invoice.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - false if error or if no invoice found.
	 */
	function moveCursorToNextContacts(RSNImportSources_FileReader_Reader $fileReader) {
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
	function getNextContacts(RSNImportSources_FileReader_Reader $fileReader) {
		$nextLine = $fileReader->readNextDataLine($fileReader);
		if ($nextLine != false) {
			$relatedcontacts = array(
				'header' => $nextLine,
				'detail' => array());
			do {
				$cursorPosition = $fileReader->getCurentCursorPosition();
				$nextLine = $fileReader->readNextDataLine($fileReader);

				if (!$this->isRecordHeaderInformationLine($nextLine)) {
					if ($nextLine[1] != null && $nextLine[1] != '') {
						//impossible ici array_push($relatedcontacts['detail'], $nextLine);
					}
				} else {
					break;
				}

			} while ($nextLine != false);

			if ($nextLine != false) {
				$fileReader->moveCursorTo($cursorPosition);
			}

			return $relatedcontacts;
		}

		return null;
	}
	
	/**
	 * Method that return the formated information of an invoice found in the file.
	 * @param $relatedcontacts : the invoice data found in the file.
	 * @return array : the formated data of the invoice.
	 */
	function getContactsValues($relatedcontacts) {
		
		$fields = $this->getContactsFields();
		
		// contrôle l'égalité des tailles de tableaux
		if(count($fields) != count($relatedcontacts['header'])){
			if(count($fields) > count($relatedcontacts['header']))
				$relatedcontacts['header'] = array_merge($relatedcontacts['header'], array_fill (0, count($fields) - count($relatedcontacts['header']), null));
			else
				$relatedcontacts['header'] = array_slice($relatedcontacts['header'], 0, count($fields));
		}
		//tableau associatif dans l'ordre fourni
		$relatedcontactsHeader = array_combine($fields, $relatedcontacts['header']);
		
		//Parse dates
		foreach($this->getContactsDateFields() as $fieldName)
			$relatedcontactsHeader[$fieldName] = $this->getMySQLDate($relatedcontactsHeader[$fieldName]);
		
		$fieldName = 'typelien';
		$relatedcontactsHeader[$fieldName] = $this->getTypeLien($relatedcontactsHeader[$fieldName]);
		
		return $relatedcontactsHeader;
	}
	
	//translate origine
	function getTypeLien($typelien){
		switch($typelien){
			case 'MemeFamille' :
				return 'Famille';
			case 'MemeAdresse' :
				return 'Même adresse';
			case 'SansLiens' :
				return 'Sans lien';
			case 'TransférerRevueVers' :
				return 'Transférer la revue';
			case 'TransférerDonVers' :
				return 'Transférer les dons';
			case 'MemeFamilleTest' :
				return 'Test sur la famille';
			case 'ContactAsso' :
				return 'Contact de l\'association';
			default :
				return $typelien;
		}
	}
}