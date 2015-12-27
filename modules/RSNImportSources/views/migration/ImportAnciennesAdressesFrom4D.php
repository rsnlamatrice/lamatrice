<?php

/* Phase de migration
 * Importation des prelevements Web depuis le fichier provenant de 4D
 */
class RSNImportSources_ImportAnciennesAdressesFrom4D_View extends RSNImportSources_ImportFromFile_View {
        
	/**
	 * Method to get the source import label to display.
	 * @return string - The label.
	 */
	public function getSource() {
		return 'LBL_CONTACTADRESSES_4D';
	}

	/**
	 * Method to get the modules that are concerned by the import.
	 * @return array - An array containing concerned module names.
	 */
	public function getImportModules() {
		return array('ContactAddresses');
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
	 * Method to get the imported fields for the contacts module.
	 * @return array - the imported fields for the contacts module.
	 */
	function getContactAddressesFieldsMapping() {
		//laisser exactement les colonnes du fichier, dans l'ordre 
		return array (
			'reffiche' => '',
			'association_r' => '',
			'prénom_r' => '',
			'nom_r' => '',
			'adresse1_r' => 'mailingstreet2',
			'adresse2_r' => 'mailingstreet',
			'adresse3_r' => 'mailingpobox',
			'cp_r' => 'mailingzip',
			'ville_r' => 'mailingcity',
			'majeure_r' => '',
			'evaladr' => '',
			'nbnpai' => 'rsnnpai',
			'indicmaitre' => '',
			'identmaitre' => '',
			'nomadresse2_new' => '',
			'adresse3_new' => '',
			'adresse4_new' => '',
			'adresse5_new' => '',
			'codepostal_new' => '',
			'bureaudistributeur_new' => '',
			'novoie_v' => '',
			'typevoie_v' => '',
			'libellevoie_v' => '',
			'pays_new' => '',
			'dateenregistrement' => 'mailingmodifiedtime',
			'prénom_new' => '',
			'pays_r' => 'mailingcountry',
			'asso_new' => '',
			'top_rnvp' => '',
			'adresse6_new' => '',
			'adresseligne2_r' => 'mailingstreet3',
			'adresse2_new' => '',
			
			//champ supplémentaire
			'_contactid' => 'contactid', //Massively updated after preImport
		);
			//donotcourrierag, rsnwebhide dans import Groupe ?
	}
	
	function getContactAddressesDateFields(){
		return array(
			'dateenregistrement', 
		);
	}
	
	/**
	 * Method to get the imported fields for the contacts module.
	 * @return array - the imported fields for the contacts module.
	 */
	function getContactAddressesFields() {
		//laisser exactement les colonnes du fichier
		return array_keys($this->getContactAddressesFieldsMapping());
	}

	/**
	 * Method to process to the import of the ContactAddresses module.
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importContactAddresses($importDataController) {
		global $VTIGER_BULK_SAVE_MODE;
		$VTIGER_BULK_SAVE_MODE = true;
		$config = new RSNImportSources_Config_Model();
		
		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($this->user, 'ContactAddresses');
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
			$this->importOneContactAddresses(array($row), $importDataController);
			$perf->tick();
			if(Import_Utils_Helper::isMemoryUsageToHigh()){
				$this->skipNextScheduledImports = true;
				$keepScheduledImport = true;
				$size = RSN_Performance_Helper::getMemoryUsage();
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
	function importOneContactAddresses($contactsData, $importDataController) {
					
		global $log;
		
		$entryId = $contactsData[0]['_contactid']; // initialisé dans le postPreImportData
		
		//note : pas de test sur doublon
		
		if($entryId){
			$record = Vtiger_Record_Model::getCleanInstance('ContactAddresses');
			$record->set('mode', 'create');
			
			$this->updateContactAddressRecordModelFromData($record, $contactsData);
			
			//$db->setDebug(true);
			$record->save();
			$contactAddressesId = $record->getId();
			
			if(!$contactAddressesId){
				//TODO: manage error
				echo "<pre><code>Impossible d'enregistrer l'adresse</code></pre>";
				foreach ($contactsData as $contactsLine) {
					$entityInfo = array(
						'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
					);
					
					//TODO update all with array
					$importDataController->updateImportStatus($contactsLine[id], $entityInfo);
				}

				return false;
			}
			
			$entryId = $this->getEntryId("ContactAddresses", $contactAddressesId);
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
				JOIN vtiger_contactaddresses
					ON vtiger_crmentity.crmid = vtiger_contactaddresses.contactaddressesid
				SET smownerid = ?
				, modifiedtime = ?
				, createdtime = ?
				WHERE vtiger_crmentity.crmid = ?
			";
			$result = $db->pquery($query, array(ASSIGNEDTO_ALL
								, $contactsData[0]['dateenregistrement']
								, $contactsData[0]['dateenregistrement']
								, $contactAddressesId));
			
			$log->debug("" . basename(__FILE__) . " update imported contact address (id=" . $record->getId() . ", Ref 4D=$sourceId , date=" . $contactsData[0]['dateenregistrement']
					. ", result=" . ($result ? " true" : "false"). " )");
			if( ! $result)
				$db->echoError();
			else {
			}
			return $record;
		}

		return true;
	}

	//Mise à jour des données du record model nouvellement créé à partir des données d'importation
	private function updateContactAddressRecordModelFromData($record, $contactsData){
		
		$fieldsMapping = $this->getContactAddressesFieldsMapping();
		foreach($contactsData[0] as $fieldName => $value)
			if(!is_numeric($fieldName) && $fieldName != 'id'){
				$vField = $fieldsMapping[$fieldName];
				if($vField)
					$record->set($vField, $value);
			}
			
	}
	
	/**
	 * Method that pre import an invoice.
	 *  It adds one row in the temporary pre-import table by invoice line.
	 * @param $contactsData : the data of the invoice to import.
	 */
	function preImportContactAddress($contactsData) {
		
		$contactsValues = $this->getContactAddressesValues($contactsData);
		
		$contacts = new RSNImportSources_Preimport_Model($contactsValues, $this->user, 'ContactAddresses');
		$contacts->save();
	}

	/**
	 * Method that retrieve a contact.
	 * @param string $ref4d : the ref4D ou le tableau de valeurs du contact
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
			if ($this->moveCursorToNextContactAddress($fileReader)) {
				$i = 0;
				do {
					$contact = $this->getNextContactAddress($fileReader);
					if ($contact != null) {
						$this->preImportContactAddress($contact);
					}
				} while ($contact != null);

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
			'ContactAddresses',
			'reffiche',
			'_contactid',
			false
		);
	
		RSNImportSources_Utils_Helper::skipPreImportDataForMissingContactsByRef4D(
			$this->user,
			'ContactAddresses',
			'_contactid'
		);
		
		$fieldsMapping = $this->getContactAddressesFieldsMapping();
		
		self::setPreImportDataSkipSameAddress(
			$this->user,
			'ContactAddresses',
			'_contactid',
			$fieldsMapping
		);
		
		self::setPreImportDataSkipSameAddressInAddresses(
			$this->user,
			'ContactAddresses',
			'_contactid',
			$fieldsMapping
		);
		
		return true;
	}
        
	/**
	 * Méthode qui annule l'import des adresses identiques
	 */
	public static function setPreImportDataSkipSameAddress($user, $moduleName, $contactIdField, $fieldsMapping) {
		$db = PearDatabase::getInstance();
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($user, $moduleName);
		
		$fieldsMapping = array_combine(array_values($fieldsMapping), array_keys($fieldsMapping));
		
		$changeStatus = RSNImportSources_Data_Action::$IMPORT_RECORD_SKIPPED;
			
		$query = "UPDATE $tableName
			JOIN vtiger_contactaddress
				ON vtiger_contactaddress.contactaddressid = `$tableName`.`$contactIdField`
			JOIN vtiger_crmentity
				ON vtiger_contactaddress.contactaddressid = vtiger_crmentity.crmid
		";
		
		$query .= " SET `$tableName`.status = ".$changeStatus;
			
		$query .= "
			WHERE vtiger_crmentity.deleted = 0
			AND `$tableName`.status = ".RSNImportSources_Data_Action::$IMPORT_RECORD_NONE."
			AND IFNULL(`$tableName`.".$fieldsMapping['mailingstreet2'].", '') = IFNULL(vtiger_contactaddress.mailingstreet2, '')
			AND IFNULL(`$tableName`.".$fieldsMapping['mailingstreet'].", '') = IFNULL(vtiger_contactaddress.mailingstreet, '')
			AND IFNULL(`$tableName`.".$fieldsMapping['mailingstreet3'].", '') = IFNULL(vtiger_contactaddress.mailingstreet3, '')
			AND IFNULL(`$tableName`.".$fieldsMapping['mailingpobox'].", '') = IFNULL(vtiger_contactaddress.mailingpobox, '')
			AND (IFNULL(`$tableName`.".$fieldsMapping['mailingzip'].", '') = IFNULL(vtiger_contactaddress.mailingzip, '')
				OR CONCAT('0', IFNULL(`$tableName`.".$fieldsMapping['mailingzip'].", '')) = IFNULL(vtiger_contactaddress.mailingzip, '')
				OR IFNULL(`$tableName`.".$fieldsMapping['mailingzip'].", '') = CONCAT('0', IFNULL(vtiger_contactaddress.mailingzip, ''))
			)
			AND IFNULL(`$tableName`.".$fieldsMapping['mailingcity'].", '') = IFNULL(vtiger_contactaddress.mailingcity, '')
		";
		
		$result = $db->query($query);
		if(!$result)
			$db->echoError($query);
			
		return !!$result;
	}
        
	/**
	 * Méthode qui annule l'import des adresses identiques (doublons d'import)
	 */
	public static function setPreImportDataSkipSameAddressInAddresses($user, $moduleName, $contactIdField, $fieldsMapping) {
		$db = PearDatabase::getInstance();
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($user, $moduleName);
		
		$fieldsMapping = array_combine(array_values($fieldsMapping), array_keys($fieldsMapping));
		
		$changeStatus = RSNImportSources_Data_Action::$IMPORT_RECORD_SKIPPED;
			
		$query = "UPDATE $tableName
			JOIN vtiger_contactaddresses
				ON vtiger_contactaddresses.contactid = `$tableName`.`$contactIdField`
			JOIN vtiger_crmentity
				ON vtiger_contactaddresses.contactaddressesid = vtiger_crmentity.crmid
		";
		
		$query .= " SET `$tableName`.status = ".$changeStatus;
			
		$query .= "
			WHERE vtiger_crmentity.deleted = 0
			AND `$tableName`.status = ".RSNImportSources_Data_Action::$IMPORT_RECORD_NONE."
			AND IFNULL(`$tableName`.".$fieldsMapping['mailingstreet2'].", '') = IFNULL(vtiger_contactaddresses.street2, '')
			AND IFNULL(`$tableName`.".$fieldsMapping['mailingstreet'].", '') = IFNULL(vtiger_contactaddresses.street, '')
			AND IFNULL(`$tableName`.".$fieldsMapping['mailingstreet3'].", '') = IFNULL(vtiger_contactaddresses.street3, '')
			AND IFNULL(`$tableName`.".$fieldsMapping['mailingpobox'].", '') = IFNULL(vtiger_contactaddresses.pobox, '')
			AND (IFNULL(`$tableName`.".$fieldsMapping['mailingzip'].", '') = IFNULL(vtiger_contactaddresses.zip, '')
				OR CONCAT('0', IFNULL(`$tableName`.".$fieldsMapping['mailingzip'].", '')) = IFNULL(vtiger_contactaddresses.zip, '')
				OR IFNULL(`$tableName`.".$fieldsMapping['mailingzip'].", '') = CONCAT('0', IFNULL(vtiger_contactaddresses.zip, ''))
			)
			AND IFNULL(`$tableName`.".$fieldsMapping['mailingcity'].", '') = IFNULL(vtiger_contactaddresses.city, '')
		";
		
		$result = $db->query($query);
		if(!$result)
			$db->echoError($query);
			
		return !!$result;
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
		if (sizeof($line) > 0 && is_numeric($line[0]) && $this->isDate($line[24]) && $line[7]/*zip*/) {
			return true;
		}

		return false;
	}

	/**
	 * Method that move the cursor of the file reader to the beginning of the next found invoice.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - false if error or if no invoice found.
	 */
	function moveCursorToNextContactAddress(RSNImportSources_FileReader_Reader $fileReader) {
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
	function getNextContactAddress(RSNImportSources_FileReader_Reader $fileReader) {
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
	function getContactAddressesValues($contacts) {

		$fields = $this->getContactAddressesFields();
		
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
		foreach($this->getContactAddressesDateFields() as $fieldName)
			$contactsHeader[$fieldName] = $this->getMySQLDate($contactsHeader[$fieldName]);
		
		return $contactsHeader;
	}
	
}
