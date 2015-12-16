<?php


/* Phase de migration
 * Importation des agences bancaires depuis le fichier provenant de 4D
 */
class RSNImportSources_ImportBanqAgencesFrom4D_View extends RSNImportSources_ImportFromFile_View {
        
	/**
	 * Method to get the source import label to display.
	 * @return string - The label.
	 */
	public function getSource() {
		return 'LBL_BANQAGENCES_4D';
	}

	/**
	 * Method to get the modules that are concerned by the import.
	 * @return array - An array containing concerned module names.
	 */
	public function getImportModules() {
		return array('RSNBanqAgences');
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
	 * Method to get the imported fields for the invoice module.
	 * @return array - the imported fields for the invoice module.
	 */
	function getRSNBanqAgencesFieldsMapping() {
		return array(
			'codebanque' => 'codebanque',
			'codeagence' => 'codeagence',
			'street' => 'street',
			'street2' => 'street2',
			'zipcode' => 'zipcode',
			'city' => 'city',
			'bic' => 'bic',
			'name' => 'name',
			
			/* post pré import */
			'_rsnbanquesid' => 'rsnbanquesid',
			'_rsnbanqagencesid' => '',
		);
	}
	
	/**
	 * Method to get the imported fields for the contacts module.
	 * @return array - the imported fields for the contacts module.
	 */
	function getRSNBanqAgencesFields() {
		//laisser exactement les colonnes du fichier
		return array_keys($this->getRSNBanqAgencesFieldsMapping());
	}

	/**
	 * Method to process to the import of the RSNBanqAgences module.
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importRSNBanqAgences($importDataController) {
		global $VTIGER_BULK_SAVE_MODE;
		$VTIGER_BULK_SAVE_MODE = false;
		
		$config = new RSNImportSources_Config_Model();

		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($this->user, 'RSNBanqAgences');
		$sql = 'SELECT * FROM ' . $tableName . ' WHERE status = '. RSNImportSources_Data_Action::$IMPORT_RECORD_NONE . ' ORDER BY id';

		$result = $adb->query($sql);
		$numberOfRecords = $adb->num_rows($result);

		if ($numberOfRecords <= 0) {
			return;
		}
		if($numberOfRecords == $config->get('importBatchLimit')){
			$this->keepScheduledImport = true;
		}

		for ($i = 0; $i < $numberOfRecords; ++$i) {
			$row = $adb->raw_query_result_rowdata($result, $i);
			$this->importOneRSNBanqAgences(array($row), $importDataController);
		}
		
		if($numberOfRecords == $config->get('importBatchLimit')){
			$this->keepScheduledImport = $this->getNumberOfRecords() > 0;
		}
	}

	/**
	 * Method to process to the import of a one prelevement.
	 * @param $rsnbanqagencesData : the data of the prelevement to import
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importOneRSNBanqAgences($rsnbanqagencesData, $importDataController) {
					
		global $log;
		
		//TODO check sizeof $rsnbanqagencesata
		$sourceId = $rsnbanqagencesData[0]['_rsnbanqagencesid'];
		if ($sourceId) {
			$entryId = $this->getEntryId("RSNBanqAgences", $sourceId);
			foreach ($rsnbanqagencesData as $rsnbanqagencesLine) {
				$entityInfo = array(
					'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_SKIPPED,
					'id'		=> $entryId
				);
				
				//TODO update all with array
				$importDataController->updateImportStatus($rsnbanqagencesLine[id], $entityInfo);
			}
		}
		else {
			$record = Vtiger_Record_Model::getCleanInstance('RSNBanqAgences');
			$record->set('mode', 'create');
			
			$this->updateRSNBanqAgencesRecordModelFromData($record, $rsnbanqagencesData);
			
			//$db->setDebug(true);
			$record->save();
			$rsnbanqagencesId = $record->getId();

			if(!$rsnbanqagencesId){
				//TODO: manage error
				echo "<pre><code>Impossible d'enregistrer l'agence</code></pre>";
				foreach ($rsnbanqagencesData as $rsnbanqagencesLine) {
					$entityInfo = array(
						'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
					);
					
					//TODO update all with array
					$importDataController->updateImportStatus($rsnbanqagencesLine[id], $entityInfo);
				}

				return false;
			}
			
			
			$entryId = $this->getEntryId("RSNBanqAgences", $rsnbanqagencesId);
			foreach ($rsnbanqagencesData as $rsnbanqagencesLine) {
				$entityInfo = array(
					'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_CREATED,
					'id'		=> $entryId
				);
				$importDataController->updateImportStatus($rsnbanqagencesLine[id], $entityInfo);
			}
			
			$log->debug("" . basename(__FILE__) . " update imported rsnbanqagences (id=" . $record->getId() . ", sourceId=$sourceId , date=" . $rsnbanqagencesData[0]['datecreation']
					. " )");
			
			return $record;
		}

		return true;
	}


	//Mise à jour des données du record model nouvellement créé à partir des données d'importation
	private function updateRSNBanqAgencesRecordModelFromData($record, $data){
		
		$fieldsMapping = $this->getRSNBanqAgencesFieldsMapping();
		foreach($data[0] as $fieldName => $value)
			if(!is_numeric($fieldName) && $fieldName !== 'id'){
				$vField = $fieldsMapping[$fieldName];
				if($vField){
					$record->set($vField, $value);
				}
			}
	}
	/**
	 * Method that pre import an invoice.
	 *  It adds one row in the temporary pre-import table by invoice line.
	 * @param $rsnbanqagencesData : the data of the invoice to import.
	 */
	function preImportRSNBanqAgences($rsnbanqagencesData) {
		
		$rsnbanqagencesValues = $this->getRSNBanqAgencesValues($rsnbanqagencesData);
		$rsnbanqagences = new RSNImportSources_Preimport_Model($rsnbanqagencesValues, $this->user, 'RSNBanqAgences');
		$rsnbanqagences->save();
	}

	/**
	 * Method to parse the uploaded file and save data to the temporary pre-import table.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - true if pre-import is ended successfully
	 */
	function parseAndSaveFile(RSNImportSources_FileReader_Reader $fileReader) {
		$this->clearPreImportTable();
		
		if($fileReader->open()) {
			if ($this->moveCursorToNextRSNBanqAgences($fileReader)) {
				$i = 0;
				do {
					$rsnbanqagences = $this->getNextRSNBanqAgences($fileReader);
					if ($rsnbanqagences != null) {
						$this->preImportRSNBanqAgences($rsnbanqagences);
					}
				} while ($rsnbanqagences != null);

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
		$db = PearDatabase::getInstance();
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'RSNBanqAgences');
		// Pré-identifie les agences 
		
		/* Affecte l'id de la banque */
		$query = "UPDATE $tableName
		JOIN vtiger_rsnbanques
			ON vtiger_rsnbanques.codebanque = `$tableName`.codebanque
		JOIN vtiger_crmentity
			ON vtiger_rsnbanques.rsnbanquesid = vtiger_crmentity.crmid
		";
		$query .= " SET `_rsnbanquesid` = vtiger_rsnbanques.rsnbanquesid";
		$query .= "
			WHERE vtiger_crmentity.deleted = 0
			AND `$tableName`.status = ".RSNImportSources_Data_Action::$IMPORT_RECORD_NONE."
		";
		$result = $db->pquery($query, array());
		if(!$result){
			echo '<br><br><br><br>';
			$db->echoError($query);
			echo("<pre>$query</pre>");
			die();
		}
		
		/* Affecte l'id de l'agence */
		$query = "UPDATE $tableName
		JOIN vtiger_rsnbanqagences
			ON vtiger_rsnbanqagences.rsnbanquesid = `$tableName`._rsnbanquesid
			AND vtiger_rsnbanqagences.codeagence = `$tableName`.codeagence
		JOIN vtiger_crmentity as vtiger_crmentity_banques
			ON vtiger_rsnbanqagences.rsnbanqagencesid = vtiger_crmentity_banques.crmid
		JOIN vtiger_crmentity as vtiger_crmentity_agences
			ON vtiger_rsnbanqagences.rsnbanqagencesid = vtiger_crmentity_agences.crmid
		";
		$query .= " SET `_rsnbanqagencesid` = vtiger_rsnbanqagences.rsnbanqagencesid
		, `$tableName`.status = ?
		, vtiger_rsnbanqagences.name = `$tableName`.name
		, vtiger_rsnbanqagences.street = `$tableName`.street
		, vtiger_rsnbanqagences.street2 = `$tableName`.street2
		, vtiger_rsnbanqagences.zipcode = `$tableName`.zipcode
		, vtiger_rsnbanqagences.city = `$tableName`.city
		, vtiger_rsnbanqagences.bic = `$tableName`.bic
		, vtiger_rsnbanqagences.codebanque = `$tableName`.codebanque";
		$query .= "
			WHERE vtiger_crmentity_banques.deleted = 0
			AND vtiger_crmentity_agences.deleted = 0
			AND `$tableName`.status = ".RSNImportSources_Data_Action::$IMPORT_RECORD_NONE."
		";
		$result = $db->pquery($query, array(RSNImportSources_Data_Action::$IMPORT_RECORD_SKIPPED));
		if(!$result){
			echo '<br><br><br><br>';
			$db->echoError($query);
			echo("<pre>$query</pre>");
			die();
		}
		
		/* Affecte l'id de l'agence */
		$query = "UPDATE $tableName
		";
		$query .= " SET `$tableName`.status = ?
			WHERE `$tableName`.status = ".RSNImportSources_Data_Action::$IMPORT_RECORD_NONE."
			AND `$tableName`._rsnbanquesid IS NULL
		";
		$result = $db->pquery($query, array(RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED));
		if(!$result){
			echo '<br><br><br><br>';
			$db->echoError($query);
			echo("<pre>$query</pre>");
			die();
		}
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
		if($string == '00/00/00')
			return '1999-12-31';
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
	function moveCursorToNextRSNBanqAgences(RSNImportSources_FileReader_Reader $fileReader) {
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
	function getNextRSNBanqAgences(RSNImportSources_FileReader_Reader $fileReader) {
		$nextLine = $fileReader->readNextDataLine($fileReader);
		if ($nextLine != false) {
			$rsnbanqagences = array(
				'header' => $nextLine,
				'detail' => array());
			do {
				$cursorPosition = $fileReader->getCurentCursorPosition();
				$nextLine = $fileReader->readNextDataLine($fileReader);

				if (!$this->isRecordHeaderInformationLine($nextLine)) {
					if ($nextLine[1] != null && $nextLine[1] != '') {
						//impossible ici array_push($rsnbanqagences['detail'], $nextLine);
					}
				} else {
					break;
				}

			} while ($nextLine != false);

			if ($nextLine != false) {
				$fileReader->moveCursorTo($cursorPosition);
			}

			return $rsnbanqagences;
		}

		return null;
	}
	
	/**
	 * Method that return the formated information of an invoice found in the file.
	 * @param $rsnbanqagences : the invoice data found in the file.
	 * @return array : the formated data of the invoice.
	 */
	function getRSNBanqAgencesValues($rsnbanqagences) {
	//TODO end implementation of this method
		$nColumn = 0;
		$rsnbanqagencesHeader = array(
			'codebanque' => $rsnbanqagences['header'][$nColumn++],
			'codeagence' => $rsnbanqagences['header'][$nColumn++],
			'street' => $rsnbanqagences['header'][$nColumn++],
			'street2' => $rsnbanqagences['header'][$nColumn++],
			'zipcode' => $rsnbanqagences['header'][$nColumn++],
			'city' => $rsnbanqagences['header'][$nColumn++],
			'bic' => $rsnbanqagences['header'][$nColumn++],
			'name' => $rsnbanqagences['header'][$nColumn++],
		);
		return $rsnbanqagencesHeader;
	}
}
