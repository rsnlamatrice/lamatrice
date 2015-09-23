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
	 * After preImport validation, and before real import, the controller needs a validation step of pre-imported data
	 */
	public function needValidatingStep(){
		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($this->user, 'Contacts');
		$sql = 'SELECT * FROM ' . $tableName . '
			WHERE (_contactid_status = '.RSNImportSources_Import_View::$RECORDID_STATUS_NONE.'
			OR _contactid_status >= '.RSNImportSources_Import_View::$RECORDID_STATUS_CHECK.')
			AND status = '. RSNImportSources_Data_Action::$IMPORT_RECORD_NONE .'
			LIMIT 1';

		$result = $adb->query($sql);
		$numberOfRecords = $adb->num_rows($result);

		return $numberOfRecords;
	}

	function getImportPreviewTemplateName($moduleName){
		return 'ImportPreviewContacts.tpl';
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
			
			"firstname" => "firstname",
			"lastname" => "lastname",
			"mailingstreet3" => "mailingstreet3",
			"mailingstreet2" => "mailingstreet2",
			"mailingzip" => "mailingzip",
			"mailingcity" => "mailingcity",
			"mailingcountry" => "mailingcountry",
			"email" => "email",
			"listesn" => "listesn",
			"listesd" => "listesd",
			"date" => "dateapplication",//format MySQL
			"ip" => "address_ip",
			"x" => "_no_use_of_x",
			
			//champ supplémentaire
			'_contactid' => '', //Contact Id. May be many. Massively updated after preImport
			'_contactid_status' => '', //Type de reconnaissance automatique. Massively updated after preImport
			'_contactid_source' => '', //Source de la reconnaissance automatique. Massively updated after preImport
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

		$this->identifyContacts();
		if($this->needValidatingStep()){
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
		//"mailingcountry" => "mailingcountry",
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
		
		return true;
	}
	
	/**
	 * Exécute des requêtes longues d'identification des contacts
	 * Doit être déclenché par le cron
	 * Seulement au premier appel du cron on ne devrait exécuter cette fonction.
	 * On peut réactiver la recherche sur certaines lignes en mettant à NULL _contactid_status
	 */
	function identifyContacts() {
		
		//Teste si il y a des lignes à analyser
		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($this->user, 'Contacts');
		$sql = 'SELECT 1 FROM ' . $tableName . '
			WHERE status = '. RSNImportSources_Data_Action::$IMPORT_RECORD_NONE . '
			AND _contactid_status IS NULL
			LIMIT 1
		';
		$result = $adb->query($sql);
		$numberOfRecords = $adb->num_rows($result);
		if ($numberOfRecords === 0) {
			return;
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
			'IF(crmids LIKE "%,%,%", '.RSNImportSources_Import_View::$RECORDID_STATUS_MULTI.','.RSNImportSources_Import_View::$RECORDID_STATUS_SINGLE.')',
			'_contactid_source',
			'Tout'
		);

		/* pas très intéressant et aussi long que le précédent */
		//$partialFields = $fields;
		//unset($partialFields['mailingstreet']);
		//unset($partialFields['mailingstreet2']);
		//RSNImportSources_Utils_Helper::setPreImportDataContactIdByFields(
		//	$this->user,
		//	'Contacts',
		//	'_contactid',
		//	$partialFields,
		//	'_contactid_status',
		//	'Tout sauf adresse1 et adresse2'
		//);
		
		$partialFields = array('email' => $fields['email'], 'lastname' => $fields['lastname'], 'firstname' => $fields['firstname'], 'mailingzip' => $fields['mailingzip']);
		RSNImportSources_Utils_Helper::setPreImportDataContactIdByFields(
			$this->user,
			'Contacts',
			'_contactid',
			$partialFields,
			'_contactid_status',
			RSNImportSources_Import_View::$RECORDID_STATUS_CHECK,
			'_contactid_source',
			'Email, nom, prénom et code postal'
		);
		
		unset($partialFields['mailingzip']);
		RSNImportSources_Utils_Helper::setPreImportDataContactIdByFields(
			$this->user,
			'Contacts',
			'_contactid',
			$partialFields,
			'_contactid_status',
			RSNImportSources_Import_View::$RECORDID_STATUS_CHECK,
			'_contactid_source',
			'Email, nom et prénom'
		);
		
		$partialFields = array('lastname' => $fields['lastname'], 'firstname' => $fields['firstname'], 'mailingzip' => $fields['mailingzip']);
		RSNImportSources_Utils_Helper::setPreImportDataContactIdByFields(
			$this->user,
			'Contacts',
			'_contactid',
			$partialFields,
			'_contactid_status',
			RSNImportSources_Import_View::$RECORDID_STATUS_CHECK,
			'_contactid_source',
			'Nom, prénom et code postal'
		);
		
		$partialFields = array('email' => $fields['email']);
		RSNImportSources_Utils_Helper::setPreImportDataContactIdByFields(
			$this->user,
			'Contacts',
			'_contactid',
			$partialFields,
			'_contactid_status',
			RSNImportSources_Import_View::$RECORDID_STATUS_CHECK,
			'_contactid_source',
			'Email seul'
		);
		
		//Marque tous les autres enregistrements
		$sql = 'UPDATE ' . $tableName . '
			SET _contactid_status = '. RSNImportSources_Import_View::$RECORDID_STATUS_NONE . '
			WHERE status = '. RSNImportSources_Data_Action::$IMPORT_RECORD_NONE . '
			AND _contactid_status IS NULL
		';
		$result = $adb->query($sql);
		
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
		$contactsHeader['_contactid_status'] = null;
		
		//nom en majuscules
		// mb_strtoupper fonctionne pour les accents grace au paramétrage ini_set('mbstring',) dans modules\RSN\RSN.php
		$contactsHeader['lastname'] = mb_strtoupper($contactsHeader['lastname']);
		//ville en majuscules
		$contactsHeader['mailingcity'] = mb_strtoupper($contactsHeader['mailingcity']);
		//email en minuscules
		$contactsHeader['email'] = strtolower($contactsHeader['email']);
		
		//TODO clean up email address (noms de domaines connus)
		
		//TODO 'France' en constante de config
		if(strcasecmp($contactsHeader['mailingcountry'], 'France') === 0)
			$contactsHeader['mailingcountry'] = '';
		
		//Ajout du code de pays en préfixe du code postal
		$contactsHeader['mailingzip'] = RSNImportSources_Utils_Helper::checkZipCodePrefix($contactsHeader['mailingzip'], $contactsHeader['mailingcountry']);
		
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
	public function preImportData() {
		//Récupération de la référence de la pétition
		$fieldName = $this->request->get('related_record_fieldname');
		$this->relatedDocumentId = $this->request->get($fieldName);
		$this->relatedDocumentName = $this->request->get($fieldName . '_display');
		
		return parent::preImportData($this->request);
	}
	
	
	function displayDataPreview() {
		$viewer = $this->getViewer($this->request);
		$moduleName = $this->request->get('for_module');
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		
		$viewer->assign('CONTACTS_FIELDS_MAPPING', $this->getContactsFieldsMapping());
		$viewer->assign('CONTACTS_MODULE_MODEL', $moduleModel);
		
		return parent::displayDataPreview();
	}

	/**
	 * Method to get the pre Imported data in order to preview them.
	 *  By default, it return the values in the pre-imported table.
	 *  This method can be overload in the child class.
	 * @return array - the pre-imported values group by module.
	 */
	public function getPreviewData($offset = 0, $limit = 12) {
		$data = parent::getPreviewData($offset, $limit);
		
		$db = PearDatabase::getInstance();
		
		/* Ajoute une propriété '_contact_rows' dont la valeur est un tableau des contacts similaires */
		
		//Référencement des contacts
		$moduleName = 'Contacts';
		$rowsByContactId = array();
		foreach($data[$moduleName] as $rowId => $importRow){
			$contactId = preg_replace('/(^,+|,+$|,(,+))/', '$2', $importRow['_contactid']);
			if($contactId){
				foreach( explode(',', $contactId) as $contactId)
					if($contactId){
						if(!$rowsByContactId[$contactId])
							$rowsByContactId[$contactId] = array();
						$rowsByContactId[$contactId][] = $rowId;
					}
			}
		}
		if($rowsByContactId){
			$query = 'SELECT vtiger_crmentity.crmid
				, vtiger_contactdetails.contact_no, vtiger_contactdetails.isgroup
				, vtiger_contactdetails.firstname, vtiger_contactdetails.lastname, vtiger_contactdetails.email
				, vtiger_contactaddress.*
				FROM vtiger_contactdetails
				JOIN vtiger_contactaddress
					ON vtiger_contactdetails.contactid = vtiger_contactaddress.contactaddressid
				JOIN vtiger_crmentity
					ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
				WHERE vtiger_crmentity.deleted = 0
				AND vtiger_crmentity.crmid IN ('.generateQuestionMarks($rowsByContactId).')
			';
			$params = array_keys($rowsByContactId);
			$result = $db->pquery($query, $params);
			if(!$result){
				echo '<br><br><br><br>';
				$db->echoError();
				echo("<pre>$query</pre>");
				die();
			}
			while($contact = $db->fetch_row($result)){
				$contactId = $contact['crmid'];
				foreach($rowsByContactId[$contactId] as $rowId){
					if(!$data[$moduleName][$rowId]['_contact_rows'])
						$data[$moduleName][$rowId]['_contact_rows'] = array();
					$data[$moduleName][$rowId]['_contact_rows'][$contactId] = $contact;
				}
			}
		}
		return $data;
	}
}