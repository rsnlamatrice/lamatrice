<?php


/* Phase de migration
 * Importation des infos comlplémentaires de groupes depuis le fichier provenant de 4D
 */
class RSNImportSources_ImportGroupesFrom4D_View extends RSNImportSources_ImportFromFile_View {
        
	/**
	 * Method to get the source import label to display.
	 * @return string - The label.
	 */
	public function getSource() {
		return 'LBL_GROUPES_4D';
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
			'reffiche' => '',//contact_no
			'pasderelanceadhesion' => 'donotrelanceadh',
			'pasdecourrierag' => 'donotcourrierag',
			'affichagesurlewebspecifique' => 'webscriptspecifique',
			'groupesignataire' => 'signcharte',
			'datevalidationsignaturecharte' => '',//toujours vide
			'cachernometprenom' => '',// rsnwebhide .= 'Nom et prénom'
			'cacheradressepostale' => '',// rsnwebhide .= 'Adresse postale'
			'cachertel' => '',// rsnwebhide .= 'Téléphone'
			'cacherfax' => '',// rsnwebhide .= 'Fax'
			'cacherportable' => '',// rsnwebhide .= 'Portable'
			'cachermail' => '',// rsnwebhide .= 'Email'
			'webinutilise1' => '',//toujours vide
			'webinutilise2' => '',//toujours vide
			'contactweb' => '',//toujours vide
			'nomlongdugroupe' => '',//grpnomlong //TODO risque d'écrasement de AssociationNomCourt qui est dans grpnomlong
			'cacheradhesion' => '',// rsnwebhide .= 'Adhésion'
		);
	}
	
	function getContactsDateFields(){
		return array(
			'datevalidationsignaturecharte'
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
		
		$sourceId = $contactsData[0]['reffiche'];
		$contactId = $this->getContactIdFromRef4D($sourceId);
		if($contactId){
			$record = Vtiger_Record_Model::getInstanceById($contactId, 'Contacts');
			$modifiedtime = $record->get('modifiedtime');
			
			$record->set('mode', 'edit');
			//update fields
			$this->updateContactRecordModelFromData($record, $contactsData);
			$record->save();
			
			$this->createInitialModComment($record, $contactsData);
			
			//restore modifiedtime
			$db = PearDatabase::getInstance();
			$query = "UPDATE vtiger_crmentity
				SET modifiedtime = ?
				WHERE vtiger_crmentity.crmid = ?
			";
			$result = $db->pquery($query, array(
								$modifiedtime
								, $contactId));
			
			$entryId = $this->getEntryId("Contacts", $contactId);
			foreach ($contactsData as $contactsLine) {
				$entityInfo = array(
					'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_UPDATED,
					'id'		=> $entryId
				);
				$importDataController->updateImportStatus($contactsLine[id], $entityInfo);
			}
		}
		else {
		
			//TODO: manage error
			echo "\r<pre><code>Contact C$sourceId inconnu</code></pre>";
			foreach ($contactsData as $contactsLine) {
				$entityInfo = array(
					'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
				);
				
				//TODO update all with array
				$importDataController->updateImportStatus($contactsLine[id], $entityInfo);
			}

			return false;
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
		
		// Note TODO
		// l'import précédent des contacts contenait l'info NomAssoAvant
		// see 'nomassoentreprisealaplacedenomp' => 'mailingaddressformat',
		// dans le traitement, 'mailingaddressformat' a pu devenir autre chose
		
		//'nomlongdugroupe' => '',//grpnomlong //TODO risque d'écrasement de AssociationNomCourt qui est dans grpnomlong
		$fieldName = 'grpnomlong';
		$value = $contactsData[0]['nomlongdugroupe'];
		if( $value ) {
			if( $value == $record->get('grpnomllong')
			||  $value == $record->get('mailingstreet2')
			||  $value == $record->get('lastname')){
				//nada
				$fieldName = '';
			}
			elseif($record->get('grpnomllong')){
				if($record->get('mailingstreet2')){
					//TODO Où mettre l'info ?
					//Stock la valeur précédente (issue de AssociationNomCourt)
					if($record->get('grpdescriptif'))
						$record->set('grpdescriptif', $record->get('grpnomllong') . "\r" . $record->get('grpdescriptif'));
					else
						$record->set('grpdescriptif', $record->get('grpnomllong'));
					//let update 'grpnomllong'
				}
				else {
					$record->set('mailingstreet2', $record->get('grpnomllong'));
					//let update 'grpnomllong'
				}
			}
			else {
				//let update 'grpnomllong'
			}
			if($fieldName)
				$record->set($fieldName, $value);
		}
		
		
		$fieldName = 'rsnwebhide';
		$webHide = '';
		//'cachernometprenom' => '',// rsnwebhide .= 'Nom et prénom'
		if($contactsData[0]['cachernometprenom'])
			$webHide .= ($webHide ? ' |##| ' : '') . 'Nom et prénom';
		//'cacheradressepostale' => '',// rsnwebhide .= 'Adresse postale'
		if($contactsData[0]['cacheradressepostale'])
			$webHide .= ($webHide ? ' |##| ' : '') . 'Adresse postale';
		//'cachertel' => '',// rsnwebhide .= 'Téléphone'
		if($contactsData[0]['cachertel'])
			$webHide .= ($webHide ? ' |##| ' : '') . 'Téléphone';
		//'cacherfax' => '',// rsnwebhide .= 'Fax'
		if($contactsData[0]['cacherfax'])
			$webHide .= ($webHide ? ' |##| ' : '') . 'Fax';
		//'cacherportable' => '',// rsnwebhide .= 'Portable'
		if($contactsData[0]['cacherportable'])
			$webHide .= ($webHide ? ' |##| ' : '') . 'Portable';
		//'cachermail' => '',// rsnwebhide .= 'Email'
		if($contactsData[0]['cachermail'])
			$webHide .= ($webHide ? ' |##| ' : '') . 'Email';
		//'cacheradhesion' => '',// rsnwebhide .= 'Adhésion'
		if($contactsData[0]['cacheradhesion'])
			$webHide .= ($webHide ? ' |##| ' : '') . 'Adhésion';
		//set
		if($webHide){
			$record->set($fieldName, $webHide);
		}
			
			
		// copie depuis tout en haut
		//
		//'reffiche' => '',//contact_no
		//'pasderelanceadhesion' => 'donotrelanceadh',
		//'pasdecourrierag' => 'donotcourrierag',
		//'affichagesurlewebspecifique' => 'webscriptspecifique',
		//'groupesignataire' => '',//toujours vide
		//'datevalidationsignaturecharte' => '',//toujours vide
		//'cachernometprenom' => '',// rsnwebhide .= 'Nom et prénom'
		//'cacheradressepostale' => '',// rsnwebhide .= 'Adresse postale'
		//'cachertel' => '',// rsnwebhide .= 'Téléphone'
		//'cacherfax' => '',// rsnwebhide .= 'Fax'
		//'cacherportable' => '',// rsnwebhide .= 'Portable'
		//'cachermail' => '',// rsnwebhide .= 'Email'
		//'webinutilise1' => '',//toujours vide
		//'webinutilise2' => '',//toujours vide
		//'contactweb' => '',//toujours vide
		//'nomlongdugroupe' => '',//grpnomlong //TODO risque d'écrasement de AssociationNomCourt qui est dans grpnomlong
		//'cacheradhesion' => '',// rsnwebhide .= 'Adhésion'
	}
	
	/**
	 * Crée un commentaire avec les données initiales de 4D
	 */
	function createInitialModComment($contact, $contactsData){
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		
		$record = Vtiger_Record_Model::getCleanInstance('ModComments');
		$record->set('mode', 'create');
		
		$text = 'Données 4D du Groupe : ';
		$fieldsMapping = $this->getContactsFieldsMapping();
		foreach($fieldsMapping as $srcFieldName => $fieldName)
			$text .= "\n- $srcFieldName = " . print_r($contactsData[0][$srcFieldName], true);
		
		$record->set('commentcontent', $text);
		$record->set('related_to', $contact->getId());
		
		$record->set('assigned_user_id', ASSIGNEDTO_ALL);
		$record->set('userid', $currentUserModel->getId());
		
		$record->save();
		
		return $record;
	}
	
	
	/**
	 * Method that pre import an invoice.
	 *  It adds one row in the temporary pre-import table by invoice line.
	 * @param $contactsData : the data of the invoice to import.
	 */
	function preImportContact($contactsData) {
		
		$contactsValues = $this->getContactsValues($contactsData);
		
		$contacts = new RSNImportSources_Preimport_Model($contactsValues, $this->user, 'Contacts');
		$contacts->save();
	}

	/**
	 * Method that retrieve a contact.
	 * @param string $firstname : the firstname of the contact.
	 * @param string $lastname : the lastname of the contact.
	 * @param string $email : the mail of the contact.
	 * @return the row data of the contact | null if the contact is not found.
	 */
	function getContact($ref4d) {
		if(is_array($ref4d)){
			//$ref4d is $rsnprelvirementsData
			$ref4d = $ref4d[0]['reffiche'];
		}
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
			echo "not opened ...";
		}
		return false;
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
		if(!$string || $string === '00/00/00')
			return null;
		$dateArray = preg_split('/[-\/]/', $string);
		return '20'.$dateArray[2] . '-' . $dateArray[1] . '-' . $dateArray[0];
	}

	/**
	 * Method that check if a line of the file is a contact information line.
	 *  It assume that the line is a client information line only and only if the first data is a date.
	 * @param array $line : the data of the file line.
	 * @return boolean - true if the line is a contact information line.
	 */
	function isRecordHeaderInformationLine($line) {
		if (sizeof($line) > 0 && is_numeric($line[0]) && is_numeric($line[1])) {
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
}