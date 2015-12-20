<?php


/* Phase de migration
 * Importation des prelevements Web depuis le fichier provenant de 4D
 */
class RSNImportSources_ImportCriteresContactsRelationsFrom4D_View extends RSNImportSources_ImportFromFile_View {
        
	/**
	 * Method to get the source import label to display.
	 * @return string - The label.
	 */
	public function getSource() {
		return 'LBL_RELATIONSCRITERESCONTACTS_4D';
	}

	/**
	 * Method to get the modules that are concerned by the import.
	 * @return array - An array containing concerned module names.
	 */
	public function getImportModules() {
		return array('Critere4D');
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
	 * Method to get the imported fields for the critere4ds module.
	 * @return array - the imported fields for the critere4ds module.
	 */
	function getCritere4DFieldsMapping() {
		//laisser exactement les colonnes du fichier, dans l'ordre 
		return array (
			'reffiche' => 'reffiche',
			'critere' => 'nom',
			'champcomplementaire' => '', // reldata .=
			'datecomplementaire' => '', // reldata .=
			'dateapplication' => 'dateapplication',
			'inutilise' => '',
			
			//post preimport
			'_critere4did' => '',
			'_notesid' => '',
			'_contactid' => '',
		);
			//donotcourrierag, rsnwebhide dans import Groupe ?
	}
	
	function getCritere4DDateFields(){
		return array(
			'datecomplementaire', 'dateapplication',
		);
	}
	
	/**
	 * Method to get the imported fields for the critere4ds module.
	 * @return array - the imported fields for the critere4ds module.
	 */
	function getCritere4DFields() {
		//laisser exactement les colonnes du fichier
		return array_keys($this->getCritere4DFieldsMapping());
	}

	/**
	 * Method to process to the import of the Critere4D module.
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importCritere4D($importDataController) {
		
		$this->importMissingCritere4D($importDataController);
		
		global $VTIGER_BULK_SAVE_MODE;
		$VTIGER_BULK_SAVE_MODE = true;
		$config = new RSNImportSources_Config_Model();
		
		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($this->user, 'Critere4D');
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
			$this->importOneCritere4DRelation(array($row), $importDataController);
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
	 * Method to process to the import of the Critere4D module.
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importMissingCritere4D($importDataController) {
		global $VTIGER_BULK_SAVE_MODE;
		$VTIGER_BULK_SAVE_MODE = true;
		$config = new RSNImportSources_Config_Model();
		
		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($this->user, 'Critere4D');
		$sql = 'SELECT critere, MIN(dateapplication) AS dateapplication
			FROM ' . $tableName . '
			WHERE status = '. RSNImportSources_Data_Action::$IMPORT_RECORD_NONE . '
			AND (_notesid IS NULL AND _critere4did IS NULL)
			GROUP BY critere
			ORDER BY dateapplication';

		$result = $adb->query($sql);
		$numberOfRecords = $adb->num_rows($result);

		if ($numberOfRecords <= 0) {
			return;
		}

		$perf = new RSN_Performance_Helper($numberOfRecords);
		for ($i = 0; $i < $numberOfRecords; ++$i) {
			$row = $adb->raw_query_result_rowdata($result, $i);
			
			echo('
				 <br>Création du critère manquant
				 '.print_r($row, true).'
				 ');
			
			
			$this->importOneCritere4D(array($row), $importDataController);
			$perf->tick();
			if(Import_Utils_Helper::isMemoryUsageToHigh()){
				$this->skipNextScheduledImports = true;
				$keepScheduledImport = true;
				break;
			}
		}
		$perf->terminate();
		
		self::setPreImportDataCritere4DIdByNom(
			$this->user,
			'Critere4D',
			'critere',
			'_critere4did',
			/*$changeStatus*/ false
		);
	}

	/**
	 * Method to process to the import of a one critere 4D.
	 * @param $critere4dsData : the data of the critere 4D to import
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importOneCritere4D($critere4dsData, $importDataController) {
		global $log;
		$record = Vtiger_Record_Model::getCleanInstance('Critere4D');
		$record->set('mode', 'create');
		
		$name = $critere4dsData[0]['critere'];
		$record->set('nom', $name);
		$dateApplication = $critere4dsData[0]['dateapplication'];
		$record->set('usage_debut', $dateApplication);
		
		//$db->setDebug(true);
		$record->save();
		$critere4dId = $record->getId();
		
		if(!$critere4dId){
			return false;
		}
				
		$record->set('mode','edit');
		$db = PearDatabase::getInstance();
		$query = "UPDATE vtiger_crmentity
			JOIN vtiger_critere4d
				ON vtiger_crmentity.crmid = vtiger_critere4d.critere4did
			SET smownerid = ?
			, createdtime = ?
			WHERE vtiger_crmentity.crmid = ?
		";
		$result = $db->pquery($query, array(ASSIGNEDTO_ALL
							, $dateApplication
							, $critere4dId));
		
		$log->debug("" . basename(__FILE__) . " update imported critere4ds (id=" . $record->getId() . ", Ref 4D=$sourceId , date=" . $critere4dsData[0]['datecreation']
				. ", result=" . ($result ? " true" : "false"). " )");
		if( ! $result)
			$db->echoError();
		else {
		}
		return $record;
	}
					
	/**
	 * Method to process to the import of a one relation.
	 * @param $critere4dsData : the data of the relation to import
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importOneCritere4DRelation($critere4dsData, $importDataController) {
					
		global $log;
		
		$critere4dId = $critere4dsData[0]['_critere4did'];
		$notesId = $critere4dsData[0]['_notesid'];
		$contactId = $critere4dsData[0]['_contactid'];
		
		if(!$contactId || (!$critere4dId && !$notesId)){
			//var_dump("One is null", $contactId, $critere4dId, $critere4dsData);
			foreach ($critere4dsData as $critere4dsLine) {
				$entityInfo = array(
					'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
				);
				
				$importDataController->updateImportStatus($critere4dsLine[id], $entityInfo);
			}

			return false;
		}
		else {
			$dateApplication = $critere4dsData[0]['dateapplication'];
			if(!$dateApplication)
				$dateApplication = '2000-01-01';
			
			if($critere4dsData[0]['datecomplementaire'])
				$relData = $critere4dsData[0]['datecomplementaire'];
			else
				$relData = '';
			if($critere4dsData[0]['champcomplementaire']){
				if($relData) $relData .= ' : ';
				$relData .= $critere4dsData[0]['champcomplementaire'];
			}
			
			$db = PearDatabase::getInstance();
			
			$skipInsertRelation = false;
			$this->specialCases($critere4dsData, $skipInsertRelation);
			
			if(!$skipInsertRelation){
				if($notesId){
					$query = "INSERT INTO vtiger_senotesrel (notesid, crmid, dateapplication, data)
							VALUES(?, ?, ?, ?)
							ON DUPLICATE KEY UPDATE data = ?
					";
					$params = array($notesId, $contactId, $dateApplication, $relData, $relData);
				}
				else {
					$query = "INSERT INTO vtiger_critere4dcontrel (critere4did, contactid, dateapplication, data)
							VALUES(?, ?, ?, ?)
							ON DUPLICATE KEY UPDATE data = ?
					";
					$params = array($critere4dId, $contactId, $dateApplication, $relData, $relData);
				}
				$result = $db->pquery($query, $params);
			}
			if(!$skipInsertRelation && !$result){
				//TODO: manage error
				$db->echoError();
				echo "<pre><code>Impossible d'enregistrer la relation du critere4d</code></pre>";
				foreach ($critere4dsData as $critere4dsLine) {
					$entityInfo = array(
						'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
					);
					
					//TODO update all with array
					$importDataController->updateImportStatus($critere4dsLine[id], $entityInfo);
				}

				return false;
			}
			
			$entryId = $contactId;
			foreach ($critere4dsData as $critere4dsLine) {
				$entityInfo = array(
					'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_CREATED,
					'id'		=> $entryId
				);
				$importDataController->updateImportStatus($critere4dsLine['id'], $entityInfo);
			}
			
			$log->debug("" . basename(__FILE__) . " update imported critere4ds (contactId=" . $contactId . ", Ref 4D=$sourceId , date=" . $critere4dsData[0]['dateapplication']
					. ", result=" . ($result ? " true" : "false"). " )");
			if( ! $result)
				$db->echoError();
			else {
			}
			return $record;
		}

		return true;
	}
	
	/**
	 * Method that pre import an invoice.
	 *  It adds one row in the temporary pre-import table by invoice line.
	 * @param $critere4dsData : the data of the invoice to import.
	 */
	function preImportCritere4D($critere4dsData) {
		
		$critere4dsValues = $this->getCritere4DValues($critere4dsData);
		
		$critere4ds = new RSNImportSources_Preimport_Model($critere4dsValues, $this->user, 'Critere4D');
		$critere4ds->save();
	}
	
	/**
	 * Method to parse the uploaded file and save data to the temporary pre-import table.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - true if pre-import is ended successfully
	 */
	function parseAndSaveFile(RSNImportSources_FileReader_Reader $fileReader) {
		$this->clearPreImportTable();
		
		if($fileReader->open()) {
			if ($this->moveCursorToNextCritere4D($fileReader)) {
				$i = 0;
				do {
					$critere4d = $this->getNextCritere4D($fileReader);
					if ($critere4d != null) {
						$this->preImportCritere4D($critere4d);
					}
				} while ($critere4d != null);

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
			'Critere4D',
			'reffiche',
			'_contactid',
			/*$changeStatus*/ false
		);
		
		// Pré-identifie les criteres
		
		self::setPreImportDataCritere4DIdByNom(
			$this->user,
			'Critere4D',
			'critere',
			'_critere4did',
			/*$changeStatus*/ false
		);
		
		self::setPreImportDataDocumentIdByNom(
			$this->user,
			'Critere4D',
			'critere',
			'_notesid',
			/*$changeStatus*/ false
		);
	
		self::failPreImportDataForNonExistingContact(
			$this->user,
			'Critere4D'
		);
		
		
		
	}
	
	/**
	 * Méthode qui affecte le critere4did pour tous ceux qu'on trouve d'après leur Ref4D
	 */
	public static function setPreImportDataCritere4DIdByNom($user, $moduleName, $nomFieldName, $critere4DIdField, $changeStatus = true) {
		$db = PearDatabase::getInstance();
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($user, $moduleName);
		
		if($changeStatus === true)
			$changeStatus = RSNImportSources_Data_Action::$IMPORT_RECORD_SKIPPED;
			
		// Pré-identifie les contacts
		
		/* Affecte la réf du critere d'après la ref 4D */
		$query = "UPDATE $tableName
			JOIN vtiger_critere4d
				ON vtiger_critere4d.nom = `$tableName`.`$nomFieldName`
			JOIN vtiger_crmentity
				ON vtiger_critere4d.critere4did = vtiger_crmentity.crmid
		";
		
		$query .= " SET `$tableName`.`$critere4DIdField` = vtiger_crmentity.crmid";
		
		if($changeStatus !== false)
			$query .= ", `$tableName`.status = ".$changeStatus;
			
		$query .= "
			WHERE vtiger_crmentity.deleted = 0
			AND `$tableName`.status = ".RSNImportSources_Data_Action::$IMPORT_RECORD_NONE."
			AND `$tableName`._contactid IS NOT NULL
		";
		$result = $db->query($query);
		if(!$result)
			$db->echoError($query);
			
		return !!$result;
	}
	
	/**
	 * Méthode qui affecte le notesid pour tous ceux qu'on trouve d'après leur Ref4D
	 */
	public static function setPreImportDataDocumentIdByNom($user, $moduleName, $nomFieldName, $notesIdField, $changeStatus = true) {
		$db = PearDatabase::getInstance();
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($user, $moduleName);
		
		if($changeStatus === true)
			$changeStatus = RSNImportSources_Data_Action::$IMPORT_RECORD_SKIPPED;
			
		// Pré-identifie les contacts
		
		/* Affecte la réf du document d'après la ref 4D */
		$query = "UPDATE $tableName
			JOIN vtiger_notescf
				ON vtiger_notescf.critere4d = `$tableName`.`$nomFieldName`
			JOIN vtiger_crmentity
				ON vtiger_notescf.notesid = vtiger_crmentity.crmid
		";
		
		$query .= " SET `$tableName`.`$notesIdField` = vtiger_crmentity.crmid";
		
		if($changeStatus !== false)
			$query .= ", `$tableName`.status = ".$changeStatus;
			
		$query .= "
			WHERE vtiger_crmentity.deleted = 0
			AND `$tableName`.status = ".RSNImportSources_Data_Action::$IMPORT_RECORD_NONE."
			AND `$tableName`._contactid IS NOT NULL
		";
		$result = $db->query($query);
		if(!$result)
			$db->echoError($query);
			
		return !!$result;
	}

	/**
	 * Méthode qui court-circuite tous enregistrements pour lesquels on ne connait pas le critère ou le contact
	 */
	public static function failPreImportDataForNonExistingContact($user, $moduleName) {
		$db = PearDatabase::getInstance();
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($user, $moduleName);
		
		/* Met en échec les enregistrements pour lesquels on ne connait pas le critère ou le contact */
		$query = "UPDATE $tableName
		";
		$query .= " SET ";
		$query .= "`$tableName`.status = ".RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED;
		$query .= "
			WHERE `$tableName`.status = ".RSNImportSources_Data_Action::$IMPORT_RECORD_NONE."
			AND (`$tableName`._contactid IS NULL OR `$tableName`._contactid = ''
			)
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
	 * Method that check if a line of the file is a critere4d information line.
	 *  It assume that the line is a client information line only and only if the first data is a date.
	 * @param array $line : the data of the file line.
	 * @return boolean - true if the line is a critere4d information line.
	 */
	function isRecordHeaderInformationLine($line) {
		if (sizeof($line) > 0 && is_numeric($line[0]) && $this->isDate($line[4])) {
			return true;
		}

		return false;
	}

	/**
	 * Method that move the cursor of the file reader to the beginning of the next found invoice.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - false if error or if no invoice found.
	 */
	function moveCursorToNextCritere4D(RSNImportSources_FileReader_Reader $fileReader) {
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
	function getNextCritere4D(RSNImportSources_FileReader_Reader $fileReader) {
		$nextLine = $fileReader->readNextDataLine($fileReader);
		if ($nextLine != false) {
			$critere4d = array(
				'header' => $nextLine,
				'detail' => array());
			do {
				$cursorPosition = $fileReader->getCurentCursorPosition();
				$nextLine = $fileReader->readNextDataLine($fileReader);

				if (!$this->isRecordHeaderInformationLine($nextLine)) {
					if ($nextLine[1] != null && $nextLine[1] != '') {
						//impossible ici array_push($critere4d['detail'], $nextLine);
					}
				} else {
					break;
				}

			} while ($nextLine != false);

			if ($nextLine != false) {
				$fileReader->moveCursorTo($cursorPosition);
			}
			return $critere4d;
		}

		return null;
	}
	
	/**
	 * Method that return the formated information of a record found in the file.
	 * @param $critere4D : the invoice data found in the file.
	 * @return array : the formated data of the invoice.
	 */
	function getCritere4DValues($critere4D) {
		$fields = $this->getCritere4DFields();
		
		// contrôle l'égalité des tailles de tableaux
		if(count($fields) != count($critere4D['header'])){
			if(count($fields) > count($critere4D['header']))
				$critere4D['header'] = array_merge($critere4D['header'], array_fill (0, count($fields) - count($critere4D['header']), null));
			else
				$critere4D['header'] = array_slice($critere4D['header'], 0, count($fields));
		}
		//tableau associatif dans l'ordre fourni
		$critere4DHeader = array_combine($fields, $critere4D['header']);
		
		//Parse dates
		foreach($this->getCritere4DDateFields() as $fieldName)
			$critere4DHeader[$fieldName] = $this->getMySQLDate($critere4DHeader[$fieldName]);
				
		return $critere4DHeader;
	}
	
	/** Traitements spéciaux de migration
	 *
	 */
	function specialCases($critere4dsData, &$skipInsertRelation){
		$critereName = decode_html($critere4dsData[0]['critere']);
		switch($critereName){
		case 'courrier_électroniqu':
			//Coche
			//	Ne pas envoyer d'email (emailoptout) : si, on peut
			//	Pas d appel à don Courrier (donotappeldoncourrier) : Ne pas
			$contact = Vtiger_Record_Model::getInstanceById($critere4dsData[0]['_contactid'], 'Contacts');
			$contact->set('mode', 'edit');
			$contact->set('emailoptout', false);
			$contact->set('donotappeldoncourrier', true);
			//save
			$contact->save();
			break;
		case 'Reçu fiscal spécial':
			//réflechir pour 2 adresses différente (postale et fiscale)
			//set use_address2_for_recu_fiscal
			$contact = Vtiger_Record_Model::getInstanceById($critere4dsData[0]['_contactid'], 'Contacts');
			$contact->set('mode', 'edit');
			$contact->set('use_address2_for_recu_fiscal', true);
			//check adresse 2 ou alerte admin
			//save
			$contact->save();
			break;
		case 'Parainage_de_la_part':
			//le critère est ambigü, le parrainage ne correspond pas qu'au abonnement
			//Affectation du parrain présent dans Data dans le champ parraincontactid de l'abonnement
			//Modification du type d'abonnement : de Abonné(e) à Abonné(e) parrainé(e)
			//Si pas d'abo à la date, on crée juste une relation entre les contacts
			$contact = Vtiger_Record_Model::getInstanceById($critere4dsData[0]['_contactid'], 'Contacts');
			$dateApplication = $critere4dsData[0]['dateapplication'];
			$ref4DParrain = $critere4dsData[0]['champcomplementaire'];
			if(is_numeric($ref4DParrain)){
				$parrainContactId = $this->getContactIdFromRef4D($ref4DParrain);
				if($parrainContactId){
					$dateApplication = new DateTime($dateApplication);
					$abosRevues = $contact->getRSNAboRevues(false, $dateApplication);
					if($abosRevues){
						foreach($abosRevues as $aboRevue){
							if($aboRevue->isTypeAbo(RSNABOREVUES_TYPE_ABONNE)
							&& $aboRevue->getDebutAbo() == $dateApplication){
								$aboRevue->set('mode', 'edit');
								$aboRevue->setTypeAbo(RSNABOREVUES_TYPE_ABO_PARRAINE);
								$aboRevue->set('parraincontactid', $parrainContactId);
								$aboRevue->save();
								break;//first only
							}
						}
					}
					$this->insertContactsRelation($contact->getId(), $parrainContactId, $dateApplication, $contact->get('firstname') . ' est parrainé(e)');
					$skipInsertRelation = true;
				}
			}
			break;
		default:
			break;
		}
	}
	
	function insertContactsRelation($contactId1, $contactId2, $dateApplication, $contRelType){
		global $adb;
		$query = 'SELECT 1
			FROM vtiger_contactscontrel
			WHERE (contactid = ? AND relcontid = ?
				OR contactid = ? AND relcontid = ?)
			AND dateapplication = ?';
		$params = array($contactId1, $contactId2, $contactId2, $contactId1);
		if(is_object($dateApplication))
			array_push($params, $dateApplication->format('Y-m-d'));
		else
			array_push($params, $dateApplication);
		$result = $adb->pquery($query, $params);
		if(!$result){
			$adb->echoError();
			die();
		}
		if($adb->getRowCount($result))
			return;
		
		$query = 'INSERT INTO vtiger_contactscontrel(`contactid`, `relcontid`, `contreltype`, `dateapplication`, `data`)
			VALUES (?, ?, ?, ?, NULL)';
		$params = array($contactId1, $contactId2, $contRelType);
		if(is_object($dateApplication))
			array_push($params, $dateApplication->format('Y-m-d'));
		else
			array_push($params, $dateApplication);
		$result = $adb->pquery($query, $params);
		if(!$result){
			$adb->echoError();
			die();
		}
	}
	
}