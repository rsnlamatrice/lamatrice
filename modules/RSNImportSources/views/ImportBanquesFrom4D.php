<?php


/* Phase de migration
 * Importation des banques depuis le fichier provenant de 4D
 */
class RSNImportSources_ImportBanquesFrom4D_View extends RSNImportSources_ImportFromFile_View {
        
	/**
	 * Method to get the source import label to display.
	 * @return string - The label.
	 */
	public function getSource() {
		return 'LBL_BANQUES_4D';
	}

	/**
	 * Method to get the modules that are concerned by the import.
	 * @return array - An array containing concerned module names.
	 */
	public function getImportModules() {
		return array('RSNBanques');
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
	 * Method to get the imported fields for the invoice module.
	 * @return array - the imported fields for the invoice module.
	 */
	function getRSNBanquesFields() {
		return array(
			'codebanque',
			'name',
			'bic',
			
			/* post pré import */
			'_rsnbanquesid',
		);
	}

	/**
	 * Method to process to the import of the RSNBanques module.
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importRSNBanques($importDataController) {
		global $VTIGER_BULK_SAVE_MODE;
		$VTIGER_BULK_SAVE_MODE = false;
		
		$config = new RSNImportSources_Config_Model();

		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($this->user, 'RSNBanques');
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
			$this->importOneRSNBanques(array($row), $importDataController);
		}
		
		if($numberOfRecords == $config->get('importBatchLimit')){
			$this->keepScheduledImport = $this->getNumberOfRecords() > 0;
		}
	}

	/**
	 * Method to process to the import of a one prelevement.
	 * @param $rsnbanquesData : the data of the prelevement to import
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importOneRSNBanques($rsnbanquesData, $importDataController) {
					
		global $log;
		
		//TODO check sizeof $rsnbanquesata
		$sourceId = $rsnbanquesData[0]['_rsnbanquesid'];
		if ($sourceId) {
			$entryId = $this->getEntryId("RSNBanques", $sourceId);
			foreach ($rsnbanquesData as $rsnbanquesLine) {
				$entityInfo = array(
					'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_SKIPPED,
					'id'		=> $entryId
				);
				
				//TODO update all with array
				$importDataController->updateImportStatus($rsnbanquesLine[id], $entityInfo);
			}
		}
		else {
			$record = Vtiger_Record_Model::getCleanInstance('RSNBanques');
			$record->set('mode', 'create');
			foreach($rsnbanquesData[0] as $fieldName => $value)
				if(!is_numeric($fieldName) && $fieldName != 'id')
					$record->set($fieldName, $value);
			
			//$db->setDebug(true);
			$record->save();
			$rsnbanquesId = $record->getId();

			if(!$rsnbanquesId){
				//TODO: manage error
				echo "<pre><code>Impossible d'enregistrer la banque</code></pre>";
				foreach ($rsnbanquesData as $rsnbanquesLine) {
					$entityInfo = array(
						'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
					);
					
					//TODO update all with array
					$importDataController->updateImportStatus($rsnbanquesLine[id], $entityInfo);
				}

				return false;
			}
			
			
			$entryId = $this->getEntryId("RSNBanques", $rsnbanquesId);
			foreach ($rsnbanquesData as $rsnbanquesLine) {
				$entityInfo = array(
					'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_CREATED,
					'id'		=> $entryId
				);
				$importDataController->updateImportStatus($rsnbanquesLine[id], $entityInfo);
			}
			
			$log->debug("" . basename(__FILE__) . " update imported rsnbanques (id=" . $record->getId() . ", sourceId=$sourceId , date=" . $rsnbanquesData[0]['datecreation']
					. " )");
			
			return $record;
		}

		return true;
	}

	/**
	 * Method that pre import an invoice.
	 *  It adds one row in the temporary pre-import table by invoice line.
	 * @param $rsnbanquesData : the data of the invoice to import.
	 */
	function preImportRSNBanques($rsnbanquesData) {
		
		$rsnbanquesValues = $this->getRSNBanquesValues($rsnbanquesData);
		$rsnbanques = new RSNImportSources_Preimport_Model($rsnbanquesValues, $this->user, 'RSNBanques');
		$rsnbanques->save();
	}

	/**
	 * Method to parse the uploaded file and save data to the temporary pre-import table.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - true if pre-import is ended successfully
	 */
	function parseAndSaveFile(RSNImportSources_FileReader_Reader $fileReader) {
		$this->clearPreImportTable();
		
		if($fileReader->open()) {
			if ($this->moveCursorToNextRSNBanques($fileReader)) {
				$i = 0;
				do {
					$rsnbanques = $this->getNextRSNBanques($fileReader);
					if ($rsnbanques != null) {
						$this->preImportRSNBanques($rsnbanques);
					}
				} while ($rsnbanques != null);

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
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'RSNBanques');
		// Pré-identifie les banques
		
		/* Affecte l'id */
		$query = "UPDATE $tableName
		JOIN vtiger_rsnbanques
			ON vtiger_rsnbanques.codebanque = `$tableName`.codebanque
		JOIN vtiger_crmentity
			ON vtiger_rsnbanques.rsnbanquesid = vtiger_crmentity.crmid
		";
		$query .= " SET `_rsnbanquesid` = vtiger_rsnbanques.rsnbanquesid
		, `$tableName`.status = ?
		, vtiger_rsnbanques.name = `$tableName`.name";
		$query .= "
			WHERE vtiger_crmentity.deleted = 0
			AND `$tableName`.status = ".RSNImportSources_Data_Action::$IMPORT_RECORD_NONE."
		";
		$result = $db->pquery($query, array(RSNImportSources_Data_Action::$IMPORT_RECORD_SKIPPED));
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
		if (sizeof($line) > 0 && is_numeric($line[0]) && $line[1]) {
			return true;
		}

		return false;
	}

	/**
	 * Method that move the cursor of the file reader to the beginning of the next found invoice.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - false if error or if no invoice found.
	 */
	function moveCursorToNextRSNBanques(RSNImportSources_FileReader_Reader $fileReader) {
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
	function getNextRSNBanques(RSNImportSources_FileReader_Reader $fileReader) {
		$nextLine = $fileReader->readNextDataLine($fileReader);
		if ($nextLine != false) {
			$rsnbanques = array(
				'header' => $nextLine,
				'detail' => array());
			do {
				$cursorPosition = $fileReader->getCurentCursorPosition();
				$nextLine = $fileReader->readNextDataLine($fileReader);

				if (!$this->isRecordHeaderInformationLine($nextLine)) {
					if ($nextLine[1] != null && $nextLine[1] != '') {
						//impossible ici array_push($rsnbanques['detail'], $nextLine);
					}
				} else {
					break;
				}

			} while ($nextLine != false);

			if ($nextLine != false) {
				$fileReader->moveCursorTo($cursorPosition);
			}

			return $rsnbanques;
		}

		return null;
	}
	
	/**
	 * Method that return the formated information of an invoice found in the file.
	 * @param $rsnbanques : the invoice data found in the file.
	 * @return array : the formated data of the invoice.
	 */
	function getRSNBanquesValues($rsnbanques) {
	//TODO end implementation of this method
			
		$rsnbanquesHeader = array(
			'codebanque'	=> $rsnbanques['header'][0],
			'name'	=> $rsnbanques['header'][1],
			'bic'	=> $rsnbanques['header'][2],
		);
		return $rsnbanquesHeader;
	}
}
