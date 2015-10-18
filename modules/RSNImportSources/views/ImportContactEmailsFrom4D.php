<?php


/* Phase de migration
 * Importation des prelevements Web depuis le fichier provenant de 4D
 */
class RSNImportSources_ImportContactEmailsFrom4D_View extends RSNImportSources_ImportFromFile_View {
        
	/**
	 * Method to get the source import label to display.
	 * @return string - The label.
	 */
	public function getSource() {
		return 'LBL_CONTACTEMAILS_4D';
	}

	/**
	 * Method to get the modules that are concerned by the import.
	 * @return array - An array containing concerned module names.
	 */
	public function getImportModules() {
		return array('ContactEmails');
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
	function getContactEmailsFieldsMapping() {
		//laisser exactement les colonnes du fichier, dans l'ordre 
		return array(
			'ref4d' => 'ref4d',
			'liste_nationale' => '',// rsnmediadocuments .=
			'liste_regionale' => '',// rsnmediadocuments .=
			'liste_boutique' => '',// rsnmediadocuments .=
			'revue' => '',//toujours à 0
			'liste_groupe' => '',// rsnmediadocuments .=
			'administratif' => '',// 0 ou 1. TODO ?
			'erreur' => 'emailoptout',// 0 ou 1. TODO
			'origine' => 'emailaddressorigin',
			'email' => 'email',
			'date_creation' => '',
			'date_modification' => '',
			
			/* post pré-import */
			'_contactid' => '',
		);
	}
	
	function getContactEmailsDateFields(){
		return array(
			'date_creation', 'date_modification'
		);
	}
	/**
	 * Method to get the imported fields for the contact emails module.
	 * @return array - the imported fields for the contact emails module.
	 */
	function getContactEmailsFields() {
		//laisser exactement les colonnes du fichier
		return array_keys($this->getContactEmailsFieldsMapping());
	}

	/**
	 * Method to process to the import of the ContactEmails module.
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importContactEmails($importDataController) {
		$config = new RSNImportSources_Config_Model();
		
		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($this->user, 'ContactEmails');
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
			$this->importOneContactEmails(array($row), $importDataController);
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
	 * @param $contactemailsData : the data of the prelevement to import
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importOneContactEmails($contactemailsData, $importDataController) {
					
		global $log;
		
		//TODO check sizeof $contactemailsata
		$sourceId = $contactemailsData[0]['ref4d'];
		$contactId = $contactemailsData[0]['_contactid']; // initialisé dans le postPreImportData
		if(!$contactId){
			//Contrôle des doublons dans la source
			if(false) // parce que [Migration]
				$contactId = $this->getContactIdFromRef4D($sourceId);
		}
		if ($contactId) {
			$sourceId = $contactemailsData[0]['email'];
	
			//test sur email == $sourceId
			$query = "SELECT crmid
				FROM vtiger_contactemails
				JOIN vtiger_crmentity
					ON vtiger_contactemails.contactemailsid = vtiger_crmentity.crmid
				WHERE email = ?
				AND contactid = ?
				AND deleted = FALSE
				LIMIT 1
			";
			$db = PearDatabase::getInstance();
			$result = $db->pquery($query, array($sourceId, $contactId));
			if($db->num_rows($result)){
				//already imported !!
				$row = $db->fetch_row($result, 0); 
				$entryId = $this->getEntryId("ContactEmails", $row['crmid']);
				foreach ($contactemailsData as $contactemailsLine) {
					$entityInfo = array(
						'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_SKIPPED,
						'id'		=> $entryId
					);
					
					//TODO update all with array
					$importDataController->updateImportStatus($contactemailsLine[id], $entityInfo);
				}
			}
			else {
				$record = Vtiger_Record_Model::getCleanInstance('ContactEmails');
				$record->set('mode', 'create');
		
				$this->updateContactEmailsRecordModelFromData($record, $contactemailsData);
				
				$record->set('contactid', $contactId);
				
				//$db->setDebug(true);
				$record->save();
				$contactemailsId = $record->getId();

				if(!$contactemailsId){
					//TODO: manage error
					echo "<pre><code>Impossible d'enregistrer le prélèvement</code></pre>";
					foreach ($contactemailsData as $contactemailsLine) {
						$entityInfo = array(
							'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
						);
						
						//TODO update all with array
						$importDataController->updateImportStatus($contactemailsLine[id], $entityInfo);
					}

					return false;
				}
				
				$entryId = $this->getEntryId("ContactEmails", $contactemailsId);
				foreach ($contactemailsData as $contactemailsLine) {
					$entityInfo = array(
						'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_CREATED,
						'id'		=> $entryId
					);
					$importDataController->updateImportStatus($contactemailsLine[id], $entityInfo);
				}
				
				$record->set('mode','edit');
				$query = "UPDATE vtiger_crmentity
					JOIN vtiger_contactemails
						ON vtiger_crmentity.crmid = vtiger_contactemails.contactemailsid
					SET smownerid = ?
					, modifiedtime = ?
					, createdtime = ?
					WHERE vtiger_crmentity.crmid = ?
				";
				$result = $db->pquery($query, array(ASSIGNEDTO_ALL
									, $contactemailsData[0]['date_modification']
									, $contactemailsData[0]['date_creation']
									, $contactemailsId));
				
				//Affectation de l'adresse e-mail principale au contact
				$fieldName = 'emailaddressorigin';
				$emailaddressorigin = $record->get($fieldName);
				if($emailaddressorigin === 'Principale'){
					//Met à jour l'email du contact
					$query = "UPDATE vtiger_contactdetails
						SET emailoptout = ?
						, email = ?
						WHERE contactid = ?
					";
					$result = $db->pquery($query, array(
									$record->get('emailoptout'),
									$record->get('email'),
									$contactId));
				
					$log->debug("" . basename(__FILE__) . " update imported contact (id=" . $contactId . ", email=" . $record->get('email')
							. ", result=" . ($result ? " true" : "false"). " )");
					if( ! $result)
						$db->echoError();
				}
					
					
				
				return $record;
			}
		} else {
			foreach ($contactemailsData as $contactemailsLine) {//TODO: remove duplicated code
				$entityInfo = array(
					'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
				);
				
				$importDataController->updateImportStatus($contactemailsLine[id], $entityInfo);
			}

			return false;
		}

		return true;
	}

	//Mise à jour des données du record model nouvellement créé à partir des données d'importation
	private function updateContactEmailsRecordModelFromData($record, $contactemailsData){
		
		$fieldsMapping = $this->getContactEmailsFieldsMapping();
		foreach($contactemailsData[0] as $fieldName => $value)
			if(!is_numeric($fieldName) && $fieldName != 'id'){
				$vField = $fieldsMapping[$fieldName];
				if($vField)
					$record->set($vField, $value);
			}
		
		
		$fieldName = 'comments';
		if($contactemailsData[0]['erreur']){
			$value = '4D indique Erreur';
		}
		else
			$value = '';
		if($contactemailsData[0]['date_modification'] != $contactemailsData[0]['date_creation'])
		$value = '4D - modifié le ' . $contactemailsData[0]['date_modification'] . "\r"
			. $value;
		$record->set($fieldName, $value);
		
		
		$fieldName = 'emailaddressorigin';
		$emailaddressorigin = $this->getEmailOrigine($record->get($fieldName));
		RSNImportSources_Utils_Helper::checkPickListValue('ContactEmails', $fieldName, $fieldName, $value);
		$record->set($fieldName, $emailaddressorigin);
		
		
		$fieldName = 'rsnmediadocuments';
		RSNImportSources_Utils_Helper::checkPickListValue('ContactEmails', $fieldName, $fieldName, 'Liste régionale');
		RSNImportSources_Utils_Helper::checkPickListValue('ContactEmails', $fieldName, $fieldName, 'Lettre boutique');
		RSNImportSources_Utils_Helper::checkPickListValue('ContactEmails', $fieldName, $fieldName, 'Liste Groupes');
		$documents = '';
		//'liste_nationale' => '',// rsnmediadocuments .=
		if($contactemailsData[0]['liste_nationale'])
			$documents .= ($documents ? ' |##| ' : '') . 'rezo-info';
		//'liste_regionale' => '',// rsnmediadocuments .=
		if($contactemailsData[0]['liste_regionale'])
			$documents .= ($documents ? ' |##| ' : '') . 'Liste régionale';
		//'liste_boutique' => '',// rsnmediadocuments .=
		if($contactemailsData[0]['liste_boutique'])
			$documents .= ($documents ? ' |##| ' : '') . 'Lettre boutique';
		//'liste_groupe' => '',// rsnmediadocuments .=
		if($contactemailsData[0]['liste_groupe'])
			$documents .= ($documents ? ' |##| ' : '') . 'Liste Groupes';
		//set
		if($documents){
			$record->set($fieldName, $documents);
		}
	
		////Adresse principale
		//$fieldName = 'email';
		//var_dump($emailaddressorigin);
		//if($emailaddressorigin === 'Principale'){
		//	$contact = $this->getContact($contactemailsData);
		//	var_dump($contact->get($fieldName), $contactemailsData[0][$fieldName], strcasecmp($contact->get($fieldName), $contactemailsData[0][$fieldName]) !== 0);
		//	if(strcasecmp($contact->get($fieldName), $contactemailsData[0][$fieldName]) !== 0){
		//		$contact->set('mode', 'edit');
		//		$contact->set($fieldName, $contactemailsData[0][$fieldName]);
		//		var_dump("UPDATE", $contact);
		//	
		//		$contact->save();
		//	}
		//}
		// copie depuis tout en haut
		//		
		//'ref4d' => 'ref4d',
		//'liste_nationale' => '',// rsnmediadocuments .=
		//'liste_regionale' => '',// rsnmediadocuments .=
		//'liste_boutique' => '',// rsnmediadocuments .=
		//'revue' => '',//toujours à 0
		//'liste_groupe' => '',// rsnmediadocuments .=
		//'administratif' => '',// 0 ou 1. TODO ?
		//'erreur' => 'emailoptout',// 0 ou 1. TODO
		//'origine' => 'emailaddressorigin',
		//'email' => 'email',
		//'date_creation' => '',
		//'date_modification' => ''
	}
	

	/**
	 * Method that pre import an invoice.
	 *  It adds one row in the temporary pre-import table by invoice line.
	 * @param $contactemailsData : the data of the invoice to import.
	 */
	function preImportContactEmails($contactemailsData) {
		
		$contactemailsValues = $this->getContactEmailsValues($contactemailsData);
		
		$contactemails = new RSNImportSources_Preimport_Model($contactemailsValues, $this->user, 'ContactEmails');
		$contactemails->save();
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
			if ($this->moveCursorToNextContactEmails($fileReader)) {
				$i = 0;
				do {
					$contactemails = $this->getNextContactEmails($fileReader);
					if ($contactemails != null) {
						$this->preImportContactEmails($contactemails);
					}
				} while ($contactemails != null);

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
			'ContactEmails',
			'ref4d',
			'_contactid',
			/*$changeStatus*/ false
		);
	
		RSNImportSources_Utils_Helper::skipPreImportDataForMissingContactsByRef4D(
			$this->user,
			'ContactEmails',
			'_contactid'
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
		if (sizeof($line) > 0 && is_numeric($line[0]) && $line[9] && $this->isDate($line[10])) {
			return true;
		}

		return false;
	}

	/**
	 * Method that move the cursor of the file reader to the beginning of the next found invoice.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - false if error or if no invoice found.
	 */
	function moveCursorToNextContactEmails(RSNImportSources_FileReader_Reader $fileReader) {
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
	function getNextContactEmails(RSNImportSources_FileReader_Reader $fileReader) {
		$nextLine = $fileReader->readNextDataLine($fileReader);
		if ($nextLine != false) {
			$contactemails = array(
				'header' => $nextLine,
				'detail' => array());
			do {
				$cursorPosition = $fileReader->getCurentCursorPosition();
				$nextLine = $fileReader->readNextDataLine($fileReader);

				if (!$this->isRecordHeaderInformationLine($nextLine)) {
					if ($nextLine[1] != null && $nextLine[1] != '') {
						//impossible ici array_push($contactemails['detail'], $nextLine);
					}
				} else {
					break;
				}

			} while ($nextLine != false);

			if ($nextLine != false) {
				$fileReader->moveCursorTo($cursorPosition);
			}

			return $contactemails;
		}

		return null;
	}
	
	/**
	 * Method that return the formated information of an invoice found in the file.
	 * @param $contactemails : the invoice data found in the file.
	 * @return array : the formated data of the invoice.
	 */
	function getContactEmailsValues($contactemails) {
		
		$fields = $this->getContactEmailsFields();
		
		// contrôle l'égalité des tailles de tableaux
		if(count($fields) != count($contactemails['header'])){
			if(count($fields) > count($contactemails['header']))
				$contactemails['header'] = array_merge($contactemails['header'], array_fill (0, count($fields) - count($contactemails['header']), null));
			else
				$contactemails['header'] = array_slice($contactemails['header'], 0, count($fields));
		}
		//tableau associatif dans l'ordre fourni
		$contactemailsHeader = array_combine($fields, $contactemails['header']);
		
		//Parse dates
		foreach($this->getContactEmailsDateFields() as $fieldName)
			$contactemailsHeader[$fieldName] = $this->getMySQLDate($contactemailsHeader[$fieldName]);
		
		$fieldName = 'email';
		$contactemailsHeader[$fieldName] = trim($contactemailsHeader[$fieldName]);
		
		return $contactemailsHeader;
	}
	
	//translate origine
	function getEmailOrigine($origine){
		switch($origine){
			case 'PRINCIPAL' :
				return 'Principale';
			case 'PETITION' :
				return 'Pétition';
			case 'BOUTIQUE' :
				return 'Boutique';
			case 'PAYPAL' :
				return 'Paypal';
			case 'PAYBOX' :
				return 'Paybox';
			case 'Donateur w' :
				return 'Donateur web';
			case 'ERREUR IMP' :
				return '(Erreur imp 4D)';
			default :
				return $origine;
		}
	}
}