<?php

/*
 * Importation des donateurs Web depuis le fichier provenant du site en ligne.
 * Permet de créer les contacts et de référencer les références de transactions.
 */
class RSNImportSources_ImportRSNDonateursWebFromSite_View extends RSNImportSources_ImportFromFile_View {

	private $coupon = null;

	/**
	 * Method to get the source import label to display.
	 * @return string - The label.
	 */
	public function getSource() {
		return 'LBL_DONATEURSWEB';
	}

	/**
	 * Method to get the source type label to display.
	 * @return string - The label.
	 */
	public function getSourceType() {
		return 'LBL_CSV_FILE';
	}

	/**
	 * Method to get the modules that are concerned by the import.
	 * @return array - An array containing concerned module names.
	 */
	public function getImportModules() {
		return array('Contacts', 'RSNDonateursWeb');
	}
	
	/**
	 * Function that returns if the import controller has a validating step of pre-import data
	 */
	public function hasValidatingStep(){
		return in_array('Contacts', $this->getImportModules());//héritage From4D
	}
	
	/**
	 * After preImport validation, and before real import, the controller needs a validation step of pre-imported data
	 */
	public function needValidatingStep(){
		if(!$this->hasValidatingStep())
			return false;
		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($this->user, 'Contacts');
		$sql = 'SELECT * FROM ' . $tableName . '
			WHERE ( _contactid_status = '.RSNImportSources_Import_View::$RECORDID_STATUS_NONE.'
			OR _contactid_status >= '.RSNImportSources_Import_View::$RECORDID_STATUS_CHECK.')
			AND status = '. RSNImportSources_Data_Action::$IMPORT_RECORD_NONE .'
			LIMIT 1';

		$result = $adb->query($sql);
		if(!$result){
			echo "<pre>$sql</pre>";
			$adb->echoError('needValidatingStep');
			return true;
		}
		$numberOfRecords = $adb->num_rows($result);
		return $numberOfRecords;
	}

	function getImportPreviewTemplateName($moduleName){
		if($this->request->get('mode') === 'validatePreImportData'
		|| $this->request->get('mode') === 'getPreviewData'
		|| $this->needValidatingStep())//$moduleName === 'Contacts' && 
			return 'ImportPreviewContacts.tpl';
		return parent::getImportPreviewTemplateName($moduleName);
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
	 * Method to default file enconding for this import.
	 * @return string - the default file encoding.
	 */
	public function getDefaultFileEncoding() {
		return 'macintosh';
	}
	
	/**
	 * Method to get the imported fields for the contacts module.
	 * @return array - the imported fields for the contacts module.
	 */
	function getContactsFieldsMapping() {
		return array(
			'externalid' => 'externalid',
			
			"firstname" => "firstname",
			"lastname" => "lastname",
			"mailingstreet3" => "mailingstreet3",
			"mailingstreet" => "mailingstreet",
			"mailingzip" => "mailingzip",
			"mailingcity" => "mailingcity",
			"mailingcountry" => "mailingcountry",
			"email" => "email",
			'phone' => 'phone',
			'mobile' => 'mobile',
			'accounttype' => 'accounttype',
			'leadsource' => 'leadsource',
			'isgroup' => 'isgroup',
			
			"date" => "",//format MySQL
			
			//champ supplémentaire
			"mailingstreet2" => "mailingstreet2",
			"mailingpobox" => "mailingpobox",
			"rsnnpai" => "rsnnpai",
			
			'_contactid' => '',
			'_contactid_status' => '', //Type de reconnaissance automatique. Massively updated after preImport
			'_contactid_source' => '', //Source de la reconnaissance automatique. Massively updated after preImport
		);
	}
	function getContactsDateFields(){
		return array();
	}
	function getContactsFieldsMappingForPreview(){
		$fields = $this->getContactsFieldsMapping();
		unset($fields['externalid']);
		unset($fields['isgroup']);
		unset($fields['accounttype']);
		unset($fields['leadsource']);
		unset($fields['phone']);
		unset($fields['mobile']);
		unset($fields['date']);
		$fields = array_move_assoc('mailingstreet2', 'lastname', $fields);
		$fields = array_move_assoc('mailingstreet3', 'mailingstreet', $fields);
		$fields = array_move_assoc('mailingpobox', 'mailingstreet3', $fields);
		$fields = array_move_assoc('rsnnpai', 'lastname', $fields);
		$fields = array_move_assoc('isgroup', 'email', $fields);
		
		return $fields;
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
	 * Method to get the imported fields for the invoice module.
	 * @return array - the imported fields for the invoice module.
	 */
	function getRSNDonateursWebFields() {
		return array(
			'externalid',
			//'contactid',
			//'relatedmoduleid',
			'email',
			'datedon',
			'amount',
			'paiementerror',
			'modepaiement',
			'frequency',
			'paiementid',
			'abocode',
			'dateaboend',
			'recu',
			'revue',
			'clicksource',
			'listesn',
			//'assigned_user_id',
			//'createdtime',
			//'modifiedtime',
			'autorisation',
			'ipaddress',
			'firstname',
			'lastname',
			'address',
			'address3',
			'zip',
			'city',
			'country',
			'phone',
			'isvalid',
			
			'_contactid',
		);
	}

	/**
	 * Method to process to the import of the Contacts module.
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importContacts($importDataController) {

		$this->identifyContacts();
		if($this->needValidatingStep()){
			$this->skipNextScheduledImports = true;
			$this->keepScheduledImport = true;
			return;
		}
		
		$config = new RSNImportSources_Config_Model();
		
		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($this->user, 'Contacts');
		$sql = 'SELECT * FROM ' . $tableName . '
			WHERE status = '. RSNImportSources_Data_Action::$IMPORT_RECORD_NONE . '
			AND _contactid_status IN ('.RSNImportSources_Import_View::$RECORDID_STATUS_SELECT.'
									, '.RSNImportSources_Import_View::$RECORDID_STATUS_CREATE.'
									, '.RSNImportSources_Import_View::$RECORDID_STATUS_UPDATE.')
			ORDER BY id';

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
			if(Import_Utils_Helper::isMemoryUsageToHigh(true)){
				$keepScheduledImport = true;
				break;
			}
		}
		$perf->terminate();
		
		if(isset($keepScheduledImport))
			$this->keepScheduledImport = $keepScheduledImport;
		elseif($numberOfRecords == $config->get('importBatchLimit'))
			$this->keepScheduledImport = $this->getNumberOfRecords() > 0;
		
		if($this->keepScheduledImport)
			$this->skipNextScheduledImports = true;
	}

	/**
	 * Method to process to the import of a one contact.
	 * @param $contactsData : the data of the contact to import
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
			
			$log->debug("" . basename(__FILE__) . " update imported contacts (id=" . $record->getId() . ", date=" . $contactsData[0]['date']
					. ", result=" . ($result ? " true" : "false"). " )");
			if( ! $result){
				$db->echoError(__FILE__.'::importOneContacts');
			}
			return $record;
		}

		return true;
	}

	/**
	 * Method to process to the import of the RSNDonateursWeb module.
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importRSNDonateursWeb($importDataController) {

		if($this->needValidatingStep()){
			$this->skipNextScheduledImports = true;
			$this->keepScheduledImport = true;
			return;
		}

		$this->beforeImportRSNDonateursWeb();		

		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($this->user, 'RSNDonateursWeb');
		$sql = 'SELECT * FROM ' . $tableName . ' WHERE status = '. RSNImportSources_Data_Action::$IMPORT_RECORD_NONE . ' ORDER BY id';

		$result = $adb->query($sql);
		$numberOfRecords = $adb->num_rows($result);

		if ($numberOfRecords <= 0) {
			return;
		}

		$row = $adb->raw_query_result_rowdata($result, 0);
		$previousRSNDonateursWebSubjet = $row['externalid'];//tmp subject, use invoice_no ???
		$rsndonateurswebData = array($row);

		for ($i = 1; $i < $numberOfRecords; ++$i) {
			$row = $adb->raw_query_result_rowdata($result, $i);
			$rsndonateurswebSubject = $row['externalid'];

			if ($previousRSNDonateursWebSubjet == $rsndonateurswebSubject) {
				array_push($rsndonateurswebData, $row);
			} else {
				$this->importOneRSNDonateursWeb($rsndonateurswebData, $importDataController);
				$rsndonateurswebData = array($row);
				$previousRSNDonateursWebSubjet = $rsndonateurswebSubject;
			}
		}

		$this->importOneRSNDonateursWeb($rsndonateurswebData, $importDataController);
	}

	/**
	 * Method to process to the import of a one donateur.
	 * @param $rsndonateurswebData : the data of the donateur to import
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importOneRSNDonateursWeb($rsndonateurswebData, $importDataController) {
					
		global $log;
		
		//TODO check sizeof $rsndonateurswebata
		$contact = $this->getContact($rsndonateurswebData);
		if ($contact != null) {
			$account = $contact->getAccountRecordModel();

			if ($account != null) {
				$sourceId = $rsndonateurswebData[0]['externalid'];
		
				//test sur externalid == $sourceId
				$query = "SELECT crmid
					FROM vtiger_rsndonateursweb
					JOIN vtiger_crmentity
					    ON vtiger_rsndonateursweb.rsndonateurswebid = vtiger_crmentity.crmid
					WHERE externalid = ? AND deleted = FALSE
					LIMIT 1
				";
				$db = PearDatabase::getInstance();
				$result = $db->pquery($query, array($sourceId));
				if($db->num_rows($result)){
					//already imported !!
					$row = $db->fetch_row($result, 0); 
					$entryId = $this->getEntryId("RSNDonateursWeb", $row['crmid']);
					foreach ($rsndonateurswebData as $rsndonateurswebLine) {
						$entityInfo = array(
							'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_SKIPPED,
							'id'		=> $entryId
						);
						
						//TODO update all with array
						$importDataController->updateImportStatus($rsndonateurswebLine[id], $entityInfo);
					}
				}
				else {
					$record = Vtiger_Record_Model::getCleanInstance('RSNDonateursWeb');
					$record->set('mode', 'create');
					foreach($rsndonateurswebData[0] as $fieldName => $value)
						if(!is_numeric($fieldName) && $fieldName != 'id')
							$record->set($fieldName, $value);
					
					$fieldName = 'amount';
					$value = str_replace('.', ',', $rsndonateurswebData[0][$fieldName]);
					$record->set($fieldName, $value);
					
					$fieldName = 'contactid';
					$value = $contact->getId();
					$record->set($fieldName, $value);
					
					//$db->setDebug(true);
					$record->save();
					$rsndonateurswebId = $record->getId();

					if(!$rsndonateurswebId){
						//TODO: manage error
						echo "<pre><code>Impossible d'enregistrer la nouvelle facture</code></pre>";
						foreach ($rsndonateurswebData as $rsndonateurswebLine) {
							$entityInfo = array(
								'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
							);
							
							//TODO update all with array
							$importDataController->updateImportStatus($rsndonateurswebLine[id], $entityInfo);
						}

						return false;
					}
					
					
					$entryId = $this->getEntryId("RSNDonateursWeb", $rsndonateurswebId);
					foreach ($rsndonateurswebData as $rsndonateurswebLine) {
						$entityInfo = array(
							'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_CREATED,
							'id'		=> $entryId
						);
						$importDataController->updateImportStatus($rsndonateurswebLine[id], $entityInfo);
					}
					
					$record->set('mode','edit');
					$query = "UPDATE vtiger_crmentity
						JOIN vtiger_rsndonateursweb
							ON vtiger_crmentity.crmid = vtiger_rsndonateursweb.rsndonateurswebid
						SET smownerid = ?
						"/*, createdtime = ?*/."
						WHERE vtiger_crmentity.crmid = ?
					";
					$result = $db->pquery($query, array(ASSIGNEDTO_ALL
									    /*, $rsndonateurswebData[0]['datedon']*/
									    , $rsndonateurswebId));
					
					$log->debug("" . basename(__FILE__) . " update imported rsndonateursweb (id=" . $record->getId() . ", sourceId=$sourceId , date=" . $rsndonateurswebData[0]['datedon']
						    . ", result=" . ($result ? " true" : "false"). " )");
					if( ! $result)
						$db->echoError();
						
						
					
					return $record;
				}
			} else {
				//TODO: manage error
				echo "<pre><code>Unable to find Account</code></pre>";
			}
		} else {
			foreach ($rsndonateurswebData as $rsndonateurswebLine) {//TODO: remove duplicated code
				$entityInfo = array(
					'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
				);
				
				$importDataController->updateImportStatus($rsndonateurswebLine[id], $entityInfo);
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
		
		$fieldName = 'isgroup';
		$record->set('isgroup', 0);
		$record->set('leadsource', 'PETITION');//TODO
		
		
	}
	
	/**
	 * Method that pre import a contact.
	 * @param $contactValues : the values of the contact to import.
	 */
	function preImportContact($contactValues) {
		$contact = new RSNImportSources_Preimport_Model($contactValues, $this->user, 'Contacts');
		$contact->save();
	}

	/**
	 * Method that pre import an invoice.
	 *  It adds one row in the temporary pre-import table by invoice line.
	 * @param $rsndonateurswebData : the data of the invoice to import.
	 */
	function preImportRSNDonateursWeb($rsndonateurswebData) {
		$rsndonateurswebValues = $this->getRSNDonateursWebValues($rsndonateurswebData);
		
		$rsndonateursweb = new RSNImportSources_Preimport_Model($rsndonateurswebValues, $this->user, 'RSNDonateursWeb');
		$rsndonateursweb->save();
	}

	/**
	 * Method that retrieve a contact.
	 * @param string $firstname : the firstname of the contact.
	 * @param string $lastname : the lastname of the contact.
	 * @param string $email : the mail of the contact.
	 * @return the row data of the contact | null if the contact is not found.
	 */
	function getContact($rsndonateurswebData) {
		$id = $rsndonateurswebData[0]['_contactid'];
		if(!$id){
			$id = $this->getContactId($rsndonateurswebData[0]['firstname'], $rsndonateurswebData[0]['lastname'], $rsndonateurswebData[0]['email']);
		}
		if($id){
			return Vtiger_Record_Model::getInstanceById($id, 'Contacts');
		}

		return null;
	}
	
	/**
	 * Method that pre-import a contact if he does not exist in database.
	 * @param $rsndonateursweb : the invoice data.
	 */
	function checkContact($rsndonateursweb) {
		
		if(!in_array('Contacts', $this->getImportModules()))
			return;
		
		$contactData = $this->getContactValues($rsndonateursweb['donInformations']);
		if($this->checkPreImportInCache('Contacts', $contactData['firstname'], $contactData['lastname'], $contactData['email']))
			return true;
		
		/* recherche massive
		$id = $this->getContactId($contactData['firstname'], $contactData['lastname'], $contactData['email']);*/
		$id = false;
		$this->setPreImportInCache($id, 'Contacts', $contactData['firstname'], $contactData['lastname'], $contactData['email']);
		
		if(!$id){
			$this->preImportContact($contactData);
		}
	}
	
	/**
	 * Method to parse the uploaded file and save data to the temporary pre-import table.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - true if pre-import is ended successfully
	 */
	function parseAndSaveFile(RSNImportSources_FileReader_Reader $fileReader) {
		$this->clearPreImportTable();
		
		if($fileReader->open()) {
			if ($this->moveCursorToNextRSNDonateursWeb($fileReader)) {
				do {
					$rsndonateursweb = $this->getNextRSNDonateursWeb($fileReader);
					if ($rsndonateursweb != null) {
						$this->checkContact($rsndonateursweb);
						$this->preImportRSNDonateursWeb($rsndonateursweb);
					}
				} while ($rsndonateursweb != null);

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
	 * Method that check if a string is a formatted date (DD/MM/YYYY).
	 * @param string $string : the string to check.
	 * @return boolean - true if the string is a date.
	 */
	function isDate($string) {
		//TODO do not put this function here ?
		return preg_match("/^(20)?[0-9][0-9][-\/][0-3][0-9][-\/][0-3][0-9]/", $string);//only true for french format
	}
	/**
	 * Method that returns a formatted date for mysql (Y-m-d).
	 * @param string $string : the string to format.
	 * @return string - formated date.
	 */
	function getMySQLDate($string) {
		return substr($string, 0, 10);
		//$dateArray = preg_split('/[-\/]/', $string);
		//return $dateArray[2] . '-' . $dateArray[1] . '-' . $dateArray[0];
	}
	/**
	 * Method that returns a formatted date for mysql (Y-m-d) from ABO FIN column.
	 * @param string $string : the string to format.
	 * @return string - formated date.
	 */
	function getMySQLDateAboEnd($string) {
		if(!$string)
			return null;
		return '20' . substr($string, 0,2) . '-' . substr($string, 2, 2) . '-01';
	}

	/**
	 * Method that check if a line of the file is a client information line.
	 *  It assume that the line is a client information line only and only if the first data is a date.
	 * @param array $line : the data of the file line.
	 * @return boolean - true if the line is a client information line.
	 */
	function isRecordHeaderInformationLine($line) {
		if (sizeof($line) > 0 && is_numeric($line[0]) && $this->isDate($line[11])) {
			return true;
		}
		return false;
	}

	/**
	 * Method that move the cursor of the file reader to the beginning of the next found invoice.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - false if error or if no invoice found.
	 */
	function moveCursorToNextRSNDonateursWeb(RSNImportSources_FileReader_Reader $fileReader) {
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
	function getNextRSNDonateursWeb(RSNImportSources_FileReader_Reader $fileReader) {
		$nextLine = $fileReader->readNextDataLine($fileReader);
		if ($nextLine != false) {
			$rsndonateursweb = array(
				'donInformations' => $nextLine,
				'detail' => array());
			do {
				$cursorPosition = $fileReader->getCurentCursorPosition();
				$nextLine = $fileReader->readNextDataLine($fileReader);

				if (!$this->isRecordHeaderInformationLine($nextLine)) {
					if ($nextLine[1] != null && $nextLine[1] != '') {
						array_push($rsndonateursweb['detail'], $nextLine);
					}
				} else {
					break;
				}

			} while ($nextLine != false);

			if ($nextLine != false) {
				$fileReader->moveCursorTo($cursorPosition);
			}

			return $rsndonateursweb;
		}

		return null;
	}
	
	/**
	 * Method called before the data are really imported.
	 *  This method must be overload in the child class.
	 *
	 * Note : pas de postPreImportData() à cause de la validation du pre-import
	 */
	function beforeImportRSNDonateursWeb() {
		$db = PearDatabase::getInstance();
		$contactsTableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'Contacts');
		$rsndonateurswebTableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'RSNDonateursWeb');
							
		/* Affecte l'id du contact trouvé dans l'import Factures ou Contacts à l'autre table
		*/
		$query = "UPDATE $contactsTableName
		JOIN  $rsndonateurswebTableName
			ON ($rsndonateurswebTableName.externalid = `$contactsTableName`.externalid
				OR (
					$rsndonateurswebTableName.email = `$contactsTableName`.email AND
					NOT($rsndonateurswebTableName.email IS NULL OR $rsndonateurswebTableName.email = '')
				)
			)
		";
		$query .= " SET `$rsndonateurswebTableName`._contactid = `$contactsTableName`.recordid
		/* affecte le status FAILED si contactid est inconnu */
		, `$rsndonateurswebTableName`.status = IF(`$contactsTableName`.recordid IS NULL, ?, `$rsndonateurswebTableName`.status)";
		$query .= "
			WHERE `$rsndonateurswebTableName`._contactid IS NULL
			AND `$rsndonateurswebTableName`.status = ? 
		";
		$result = $db->pquery($query, array(RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED, RSNImportSources_Data_Action::$IMPORT_RECORD_NONE));
		if(!$result){
			echo '<br><br><br><br>';
			$db->echoError($query);
			echo("<pre>$query</pre>");
			die();
		}
		return true;
	}

	/**
	 * Method that return the formated information of a contact found in the file.
	 * @param $rsndonateurswebInformations : the invoice informations data found in the file.
	 * @return array : the formated data of the contact.
	 */
	function getContactValues($rsndonateurswebInformations) {
		if(preg_match('/^0?6/', $rsndonateurswebInformations[9]))
			$mobile = $rsndonateurswebInformations[9];
		else
			$phone = $rsndonateurswebInformations[9];
			
		$country = $rsndonateurswebInformations[7];
		
		$prenom = $rsndonateurswebInformations[1];
		if($prenom == strtoupper($prenom))
			$prenom = ucfirst( mb_strtolower($prenom) );
		else
			$prenom = ucfirst( $prenom );
		$date = $this->getMySQLDate($rsndonateurswebInformations[11]);
		
					
		$contactMapping = array(
			'externalid'		=> $rsndonateurswebInformations[0],
			'lastname'		=> mb_strtoupper($rsndonateurswebInformations[2]),
			'firstname'		=> $prenom,
			'email'			=> $rsndonateurswebInformations[8],
			'mailingstreet'		=> $rsndonateurswebInformations[3],
			'mailingstreet3'	=> $rsndonateurswebInformations[4],
			'mailingzip'		=> $rsndonateurswebInformations[5],
			'mailingcity'		=> mb_strtoupper($rsndonateurswebInformations[6]),
			'mailingcountry' 	=> $country == 'France' ? '' : $country,
			'phone'			=> isset($phone) ? $phone : '',
			'mobile'		=> isset($mobile) ? $mobile : '',
			'accounttype'		=> 'Donateur Web',
			'leadsource'		=> $this->getContactOrigine($rsndonateurswebInformations),
			'isgroup'			=> 0,
			'date'				=> $date,
		);
		return $contactMapping;
	}

	/**
	 * Retourne l'origine du contact suivant le mode de paiement
	 */
	function getContactOrigine($data){
		$col_modepaiement = 21;
		$col_frequency = 13;
		switch($data[$col_modepaiement]){				
			case 'cb':
				if($data[$col_frequency]=='1')
					return "DON_ETALE";
				return 'PayBox';
				
			case 'paypal':
				return 'PayPal';
				
			case 'cheque':
			case 'virement':
			case 'prelevement':
			default:
				return 'Donateur web';
		}
	}
	
	/**
	 * Method that return the formated information of an invoice found in the file.
	 * @param $rsndonateursweb : the invoice data found in the file.
	 * @return array : the formated data of the invoice.
	 */
	function getRSNDonateursWebValues($rsndonateursweb) {
	//TODO end implementation of this method
		$rsndonateurswebValues = array();
		$date = $this->getMySQLDate($rsndonateursweb['donInformations'][11]);
		$dateEndAbo = $this->getMySQLDateAboEnd($rsndonateursweb['donInformations'][16]);
		$country = $rsndonateursweb['donInformations'][7];
		$prenom = $rsndonateursweb['donInformations'][1];
		if($prenom == strtoupper($prenom))
			$prenom = ucfirst( mb_strtolower($prenom) );
		else
			$prenom = ucfirst( $prenom );
			
		$rsndonateurswebHeader = array(
			'externalid'		=> $rsndonateursweb['donInformations'][0],
			'lastname'		=> mb_strtoupper($rsndonateursweb['donInformations'][2]),
			'firstname'		=> $prenom,
			'email'			=> $rsndonateursweb['donInformations'][8],
			'address'		=> $rsndonateursweb['donInformations'][3],
			'address3'		=> $rsndonateursweb['donInformations'][4],
			'zip'			=> $rsndonateursweb['donInformations'][5],
			'city'			=> mb_strtoupper($rsndonateursweb['donInformations'][6]),
			'country' 		=> $country == 'France' ? '' : $country,
			'datedon'		=> $date,
			//'contactid',
			//'relatedmoduleid',
			'phone' 		=> $rsndonateursweb['donInformations'][9],
			'frequency' 		=> $rsndonateursweb['donInformations'][13],
			'paiementid' 		=> $rsndonateursweb['donInformations'][14],
			'abocode' 		=> $rsndonateursweb['donInformations'][15],
			'dateaboend' 		=> $dateEndAbo,
			'paiementerror' 	=> $rsndonateursweb['donInformations'][20],
			'modepaiement' 		=> $rsndonateursweb['donInformations'][21],
			'amount' 		=> $rsndonateursweb['donInformations'][12],
			'recu' 			=> $rsndonateursweb['donInformations'][17],
			'revue' 		=> $rsndonateursweb['donInformations'][18],
			'clicksource' 		=> $rsndonateursweb['donInformations'][22],
			'listesn' 		=> $rsndonateursweb['donInformations'][23],
			//'assigned_user_id',
			//'createdtime',
			//'modifiedtime',
			'autorisation' 		=> $rsndonateursweb['donInformations'][19],
			'ipaddress' 		=> $rsndonateursweb['donInformations'][10],
			'isvalid' 		=> 1,
		);
		if($rsndonateurswebHeader['frequency'] == '')
			$rsndonateurswebHeader['frequency'] = 0;
		if($rsndonateurswebHeader['paiementerror'] == '')
			$rsndonateurswebHeader['paiementerror'] = 0;
		if($rsndonateurswebHeader['modepaiement'] == 'cb'
		&& $rsndonateurswebHeader['paiementerror'] != '0')
			$rsndonateurswebHeader['isvalid'] = 0;
		return $rsndonateurswebHeader;
	}
	
	function getClickSource($clickSource){
		if ($clickSource == "ACAR 30")
			return "ACAR+30";
		return $clickSource;
	}
	
	/**
	 * Initialise les données de validation des Contacts
	 */
	function initDisplayPreviewData() {
		$this->initDisplayPreviewContactsData();
		return parent::initDisplayPreviewData();
	}
	
	/**
	 * Method to get the pre Imported data in order to preview them.
	 *  By default, it return the values in the pre-imported table.
	 *  This method can be overload in the child class.
	 * @return array - the pre-imported values group by module.
	 */
	public function getPreviewData($request, $offset = 0, $limit = 24, $importModules = false) {
		if(!$importModules
		&& $this->needValidatingStep())
			$importModules =array('Contacts');
		$data = parent::getPreviewData($request, $offset, $limit, $importModules);
		return RSNImportSources_Utils_Helper::getPreviewDataWithMultipleContacts($data);
	}
	

}
