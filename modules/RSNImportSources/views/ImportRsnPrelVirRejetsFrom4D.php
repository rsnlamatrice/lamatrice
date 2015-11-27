<?php

/* Phase de migration
 * Importation des prelevements Web depuis le fichier provenant de 4D
 */
class RSNImportSources_ImportRsnPrelVirRejetsFrom4D_View extends RSNImportSources_ImportFromFile_View {
        
	/**
	 * Method to get the source import label to display.
	 * @return string - The label.
	 */
	public function getSource() {
		return 'LBL_REJETSPRELVIREMENT_4D';
	}

	/**
	 * Method to get the modules that are concerned by the import.
	 * @return array - An array containing concerned module names.
	 */
	public function getImportModules() {
		return array('RsnPrelVirement');
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
	function getRsnPrelVirementFields() {
		return array(
			'reffiche',
			'annee',
			'mois',
			'daterejet',
			'motifrejet',
			'montant',
			'dateimpr',
			
			/* post pré import */
			'dateexport',
			'_contactid',
			'_rsnprelvirementid',
		);
	}

	/**
	 * Method to process to the import of the RsnPrelVirement module.
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importRsnPrelVirement($importDataController) {
		global $VTIGER_BULK_SAVE_MODE;
		$VTIGER_BULK_SAVE_MODE = true;
		
		$config = new RSNImportSources_Config_Model();
		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($this->user, 'RsnPrelVirement');
		$sql = 'SELECT * FROM ' . $tableName . ' WHERE status = '
			. RSNImportSources_Data_Action::$IMPORT_RECORD_NONE
			. ' ORDER BY id'
			//. ' LIMIT 0, ' . $config->get('importBatchLimit')
		;

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
			$this->importOneRsnPrelVirement(array($row), $importDataController);
			$perf->tick();
			if(Import_Utils_Helper::isMemoryUsageToHigh()){
				$this->skipNextScheduledImports = true;
				$keepScheduledImport = true;
				break;
			}
		}
		$perf->terminate();
		
		//ED150826 : d'autres données sont disponibles, empèche la suppression de l'import programmé
		/*var_dump("\n\n\n\n\n\n\n\n\nnumberOfRecords\n\n\n\n\n\n\n\n\n\n\n", $numberOfRecords, $config->get('importBatchLimit')
				, '$this->getNumberOfRecords()', $this->getNumberOfRecords());*/
		if($numberOfRecords == $config->get('importBatchLimit')){
			$this->keepScheduledImport = $this->getNumberOfRecords() > 0;
		}
	}

	/**
	 * Method to process to the import of a one prelevement.
	 * @param $rsnprelvirementsData : the data of the prelevement to import
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importOneRsnPrelVirement($rsnprelvirementsData, $importDataController) {
					
		global $log;
		
		$sourceId = $rsnprelvirementsData[0]['_rsnprelvirementid'];
		if (!$sourceId) {

			foreach ($rsnprelvirementsData as $rsnprelvirementsLine) {//TODO: remove duplicated code
				$entityInfo = array(
					'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
				);
				
				$importDataController->updateImportStatus($rsnprelvirementsLine[id], $entityInfo);
			}

			return false;
		}

		return true;
	}

	/**
	 * Method that pre import an invoice.
	 *  It adds one row in the temporary pre-import table by invoice line.
	 * @param $rsnprelvirementsData : the data of the invoice to import.
	 */
	function preImportRsnPrelVirement($rsnprelvirementsData) {
		
		$rsnprelvirementsValues = $this->getRsnPrelVirementValues($rsnprelvirementsData);
		
		$rsnprelvirements = new RSNImportSources_Preimport_Model($rsnprelvirementsValues, $this->user, 'RsnPrelVirement');
		$rsnprelvirements->save();
	}

	/**
	 * Method to parse the uploaded file and save data to the temporary pre-import table.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - true if pre-import is ended successfully
	 */
	function parseAndSaveFile(RSNImportSources_FileReader_Reader $fileReader) {
		$this->clearPreImportTable();
		
		if($fileReader->open()) {
			if ($this->moveCursorToNextRsnPrelVirement($fileReader)) {
				$i = 0;
				do {
					$rsnprelvirements = $this->getNextRsnPrelVirement($fileReader);
					if ($rsnprelvirements != null) {
						$this->preImportRsnPrelVirement($rsnprelvirements);
					}
				} while ($rsnprelvirements != null);

			}

			$fileReader->close(); 
			return true;
		} else {
			//TODO: manage error
			echo "<code>le fichier n'a pas pu Ãªtre ouvert...</code>";
		}
		return false;
	}
	
	/**
	 * Method called after the file is processed.
	 *  This method must be overload in the child class.
	 */
	function postPreImportData() {
		$db = PearDatabase::getInstance();
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'RsnPrelVirement');
		// Pré-identifie les contacts
		
		RSNImportSources_Utils_Helper::setPreImportDataContactIdByRef4D(
			$this->user,
			'RsnPrelVirement',
			'reffiche',
			'_contactid',
			/*$changeStatus*/ false
		);
	
		RSNImportSources_Utils_Helper::skipPreImportDataForMissingContactsByRef4D(
			$this->user,
			'RsnPrelVirement',
			'_contactid'
		);
		
		/* Affecte l'id de l'ordre de prelèvement */
		$query = "UPDATE $tableName
		JOIN vtiger_contactdetails
			ON vtiger_contactdetails.contactid = `$tableName`._contactid
		JOIN vtiger_rsnprelevements
			ON vtiger_rsnprelevements.accountid = vtiger_contactdetails.accountid
		JOIN vtiger_rsnprelvirement
			ON vtiger_rsnprelvirement.rsnprelevementsid = vtiger_rsnprelevements.rsnprelevementsid
			AND ABS(vtiger_rsnprelvirement.dateexport - `$tableName`.dateexport) < 10*1000000
		JOIN vtiger_crmentity as vtiger_crmentity_contacts
			ON vtiger_contactdetails.contactid = vtiger_crmentity_contacts.crmid
		JOIN vtiger_crmentity as vtiger_crmentity_prelvirements
			ON vtiger_rsnprelvirement.rsnprelvirementid = vtiger_crmentity_prelvirements.crmid
		";
		$query .= " SET `_rsnprelvirementid` = vtiger_rsnprelvirement.rsnprelvirementid
		, `$tableName`.status = ?
		, vtiger_rsnprelvirement.rsnprelvirstatus = `$tableName`.motifrejet
		, vtiger_rsnprelvirement.commentaire = CONCAT(IFNULL(vtiger_rsnprelvirement.commentaire, ''), '-', `$tableName`.daterejet)
		, vtiger_crmentity_prelvirements.modifiedtime = NOW()
		";
		$query .= "
			WHERE vtiger_crmentity_contacts.deleted = 0
			AND vtiger_crmentity_prelvirements.deleted = 0
			AND `$tableName`.status = ".RSNImportSources_Data_Action::$IMPORT_RECORD_NONE."
		";
		$result = $db->pquery($query, array(RSNImportSources_Data_Action::$IMPORT_RECORD_UPDATED));
		if(!$result){
			echo '<br><br><br><br>';
			$db->echoError($query);
			echo("<pre>$query</pre>");
			die();
		}
		
		$this->addAllPicklistValues('RsnPrelVirement', 'motifrejet', 'rsnprelvirstatus');
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
		if (sizeof($line) > 0 && is_numeric($line[0]) && $this->isDate($line[6])) {
			return true;
		}

		return false;
	}

	/**
	 * Method that move the cursor of the file reader to the beginning of the next found invoice.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - false if error or if no invoice found.
	 */
	function moveCursorToNextRsnPrelVirement(RSNImportSources_FileReader_Reader $fileReader) {
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
	function getNextRsnPrelVirement(RSNImportSources_FileReader_Reader $fileReader) {
		$nextLine = $fileReader->readNextDataLine($fileReader);
		if ($nextLine != false) {
			$rsnprelvirements = array(
				'prlvInformations' => $nextLine,
				'detail' => array());
			do {
				$cursorPosition = $fileReader->getCurentCursorPosition();
				$nextLine = $fileReader->readNextDataLine($fileReader);

				if (!$this->isRecordHeaderInformationLine($nextLine)) {
					if ($nextLine[1] != null && $nextLine[1] != '') {
						//impossible ici array_push($rsnprelvirements['detail'], $nextLine);
					}
				} else {
					break;
				}

			} while ($nextLine != false);

			if ($nextLine != false) {
				$fileReader->moveCursorTo($cursorPosition);
			}

			return $rsnprelvirements;
		}

		return null;
	}
	
	/**
	 * Method that return the formated information of an invoice found in the file.
	 * @param $rsnprelvirements : the invoice data found in the file.
	 * @return array : the formated data of the invoice.
	 */
	function getRsnPrelVirementValues($rsnprelvirements) {
		
			//'reffiche',
			//'annee',
			//'mois',
			//'daterejet',
			//'motifrejet',
			//'montant',
			//'dateimpr',
		$rsnprelvirementsHeader = array(
			'reffiche'	=> $rsnprelvirements['prlvInformations'][0],
			'annee'	=> $rsnprelvirements['prlvInformations'][1],
			'mois'	=> $rsnprelvirements['prlvInformations'][2],
			'daterejet'	=> $this->getMySQLDate($rsnprelvirements['prlvInformations'][3]),
			'motifrejet'	=> ucfirst($rsnprelvirements['prlvInformations'][4]),
			'montant'	=> self::str_to_float($rsnprelvirements['prlvInformations'][5]),
			'daterejet'	=> $this->getMySQLDate($rsnprelvirements['prlvInformations'][6]),
		);
		$rsnprelvirementsHeader['dateexport'] = $rsnprelvirementsHeader['annee'] . '-' . $rsnprelvirementsHeader['mois'] . '-06';
		return $rsnprelvirementsHeader;
	}
	
}
