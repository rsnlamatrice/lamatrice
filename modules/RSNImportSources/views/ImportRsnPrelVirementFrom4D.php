<?php


/* Phase de migration
 * Importation des prelevements Web depuis le fichier provenant de 4D
 */
class RSNImportSources_ImportRsnPrelVirementFrom4D_View extends RSNImportSources_ImportFromFile_View {
        
	/**
	 * Method to get the source import label to display.
	 * @return string - The label.
	 */
	public function getSource() {
		return 'LBL_PRELVIREMENT_4D';
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
			'annee',
			'mois',
			'reffiche',
			'montant',
			'periodicite',
			'separum',
			'dateexport',
			'iban',
			'bic',
			
			/* post pré import */
			'_contactid',
		);
	}

	/**
	 * Method to process to the import of the RsnPrelVirement module.
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importRsnPrelVirement($importDataController) {
		$config = new RSNImportSources_Config_Model();
		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($this->user, 'RsnPrelVirement');
		$sql = 'SELECT * FROM ' . $tableName . ' WHERE status = '
			. RSNImportSources_Data_Action::$IMPORT_RECORD_NONE
			. ' ORDER BY id'
			. ' LIMIT 0, ' . $config->get('importBatchLimit');

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
			$this->importOneRsnPrelVirement(array($row), $importDataController);
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
		
		//ED150826 : d'autres données sont disponibles, empêche la suppression de l'import programmé
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
		
		$prelevement = $this->getPrelevement($rsnprelvirementsData);
		if ($prelevement != null) {
			$sourceId = $rsnprelvirementsData[0]['separum'];
			$datePvt = $rsnprelvirementsData[0]['dateexport'];
	
			//test sur separum == $sourceId
			$query = "SELECT crmid
				FROM vtiger_rsnprelvirement
				JOIN vtiger_crmentity
					ON vtiger_rsnprelvirement.rsnprelvirementid = vtiger_crmentity.crmid
				WHERE vtiger_crmentity.deleted = FALSE
				AND dateexport = ?
				AND separum = ?
				AND rsnprelevementsid = ?
				LIMIT 1
			";
			$params = array($datePvt, $sourceId, $prelevement->getId());
			$db = PearDatabase::getInstance();
			$result = $db->pquery($query, $params);
			if($db->num_rows($result)){
				//already imported !!
				$row = $db->fetch_row($result, 0); 
				$entryId = $this->getEntryId("RsnPrelVirement", $row['crmid']);
				foreach ($rsnprelvirementsData as $rsnprelvirementsLine) {
					$entityInfo = array(
						'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_SKIPPED,
						'id'		=> $entryId
					);
					
					//TODO update all with array
					$importDataController->updateImportStatus($rsnprelvirementsLine[id], $entityInfo);
				}
			}
			else {
				$record = Vtiger_Record_Model::getCleanInstance('RsnPrelVirement');
				$record->set('mode', 'create');
				foreach($rsnprelvirementsData[0] as $fieldName => $value)
					if(!is_numeric($fieldName) && $fieldName != 'id')
						$record->set($fieldName, $value);
				
				$fieldName = 'montant';
				$value = str_replace('.', ',', $rsnprelvirementsData[0][$fieldName]);
				$record->set($fieldName, $value);
				
				$fieldName = 'rsnprelvirstatus';
				$value = 'Ok';
				$record->set($fieldName, $value);
				
				$fieldName = 'is_first';
				$value = 0;
				$record->set($fieldName, $value);
				
				$comments = '';
				
				$oldPeriodicite = $this->getPeriodiciteFrom4D($prelevement->get('periodicite'), $record->get('dateexport'));
				if($prelevement->get('periodicite') != $oldPeriodicite){
					$comments = 'Dans 4D, la périodicité était ' . $oldPeriodicite . ', maintenant c\'est '.$prelevement->get('periodicite').'.';
				}
				
				if(strlen($prelevement->get('separum')) > 3){
					$oldRUM = $record->get('separum');
					if($prelevement->get('separum') != $oldRUM){
						if($comments) $comments .= "\r\n";
						$comments .= 'Dans 4D, la RUM était ' . $oldRUM . ', maintenant c\'est '.$prelevement->get('separum').'.';
					}
				}
				
				if($comments){
					$fieldName = 'commentaire';
					$record->set($fieldName, $comments);
				}

				$fieldName = 'rsnprelevementsid';
				$value = $prelevement->getId();
				$record->set($fieldName, $value);
				
				//$db->setDebug(true);
				$record->save();
				$rsnprelvirementsId = $record->getId();

				if(!$rsnprelvirementsId){
					//TODO: manage error
					echo "<pre><code>Impossible d'enregistrer l'ordre de prélèvement</code></pre>";
					foreach ($rsnprelvirementsData as $rsnprelvirementsLine) {
						$entityInfo = array(
							'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
						);
						
						//TODO update all with array
						$importDataController->updateImportStatus($rsnprelvirementsLine[id], $entityInfo);
					}

					return false;
				}
				
				
				$entryId = $this->getEntryId("RsnPrelVirement", $rsnprelvirementsId);
				foreach ($rsnprelvirementsData as $rsnprelvirementsLine) {
					$entityInfo = array(
						'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_CREATED,
						'id'		=> $entryId
					);
					$importDataController->updateImportStatus($rsnprelvirementsLine[id], $entityInfo);
				}
				
				$record->set('mode','edit');
				$query = "UPDATE vtiger_crmentity
					JOIN vtiger_rsnprelvirement
						ON vtiger_crmentity.crmid = vtiger_rsnprelvirement.rsnprelvirementid
					SET smownerid = ?
					, createdtime = ?
					WHERE crmid = ?
				";
				$result = $db->pquery($query, array(ASSIGNEDTO_ALL
									, $rsnprelvirementsData[0]['dateexport']
									, $rsnprelvirementsId));
				
				$log->debug("" . basename(__FILE__) . " update imported rsnprelvirement (id=" . $record->getId() . ", sourceId=$sourceId , date=" . $rsnprelvirementsData[0]['dateexport']
						. ", result=" . ($result ? " true" : "false"). " )");
				if( ! $result)
					$db->echoError();
					
					
				
				return $record;
			}
		} else {
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

		////TODO : cache
		//$query = "SELECT 1
		//	FROM vtiger_rsnprelvirement
		//	JOIN vtiger_crmentity
		//		ON vtiger_crmentity.crmid = vtiger_rsnprelvirement.rsnprelvirementid
		//	WHERE vtiger_crmentity.deleted = 0
		//	AND dateexport = ?
		//	AND separum = ?
		//	LIMIT 1
		//";
		//$sourceId = $rsnprelvirementsData[0]['separum'];
		//$db = PearDatabase::getInstance();
		//$result = $db->pquery($query, array($rsnprelvirementsData[0]['dateexport'], $sourceId));
		//if($db->num_rows($result)){
		//	var_dump("Preimport RsnPrelVirement", $rsnprelvirementsValues);
		//	return true;
		//}
		
		$rsnprelvirements = new RSNImportSources_Preimport_Model($rsnprelvirementsValues, $this->user, 'RsnPrelVirement');
		$rsnprelvirements->save();
	}

	function getPrelevement($rsnprelvirementsData){
		$contactId = $rsnprelvirementsData[0]['_contactid'];
		if(!$contactId){
			$ref4d = $rsnprelvirementsData[0]['reffiche'];
			$contactId = $this->getContactIdFromRef4D($ref4d);
		}
		if($contactId)
			$contact = Vtiger_Record_Model::getInstanceById($contactId, 'Contacts');
			
		if(!$contact){
			var_dump("Impossible de trouver le contact du pr�l�vement.", $rsnprelvirementsData[0]);
			return false;
		}
		$account = $contact->getAccountRecordModel();
		if(!$account){
			var_dump("Impossible de trouver le compte du prélèvement. ContactId=", $contact->getId());
			return false;
		}
		
		$separum = $rsnprelvirementsData[0]['separum'];
		//TODO : cache
		$query = "SELECT crmid
			FROM vtiger_rsnprelevements
			JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_rsnprelevements.rsnprelevementsid
			WHERE vtiger_crmentity.deleted = 0
			AND accountid = ?
		";
		$params = array($account->getId());
		if(strlen($separum) > 12){
			$query .= "
				AND separum = ?
			";
			$params[] = $separum;
		}
		$query .= "
			AND montant = ?
			AND DATE(vtiger_crmentity.createdtime) <= ?
			ORDER BY etat ASC
			LIMIT 1
		";
		$params[] = $rsnprelvirementsData[0]['montant'];
		$params[] = $rsnprelvirementsData[0]['dateexport'];
		
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, $params);
		if(!$db->num_rows($result)){
			var_dump("Impossible de trouver le prélèvement. SEPARUM = $separum. ContactId=", $contact->getId(), array($account->getId(), $sourceId), $rsnprelvirementsData[0]);
			return false;
		}
		
		$id = $db->query_result($result, 0, 0);
		//var_dump("Prélèvement", $id);
		return Vtiger_Record_Model::getInstanceById($id, 'RsnPrelevements');
		
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
	//TODO end implementation of this method
		/*if(!self::str_to_float($rsnprelvirements['prlvInformations'][3]))
			var_dump('getRsnPrelVirementValues', self::str_to_float($rsnprelvirements['prlvInformations'][3]), $rsnprelvirements['prlvInformations'][3]);
		*/	
		$rsnprelvirementsHeader = array(
			'annee'	=> $rsnprelvirements['prlvInformations'][0],
			'mois'	=> $rsnprelvirements['prlvInformations'][1],
			'reffiche'	=> $rsnprelvirements['prlvInformations'][2],
			'montant'	=> self::str_to_float($rsnprelvirements['prlvInformations'][3]),
			'periodicite'	=> $this->getPeriodiciteFrom4D($rsnprelvirements['prlvInformations'][4], $this->getMySQLDate($rsnprelvirements['prlvInformations'][6])),
			'separum'	=> $rsnprelvirements['prlvInformations'][5],
			'dateexport'	=> $this->getMySQLDate($rsnprelvirements['prlvInformations'][6]),
			'iban'	=> $rsnprelvirements['prlvInformations'][7],
			'bic'	=> $rsnprelvirements['prlvInformations'][8],
		);
		return $rsnprelvirementsHeader;
	}
	
	function getPeriodiciteFrom4D($period4D, $dateCreation){
		switch($period4D){
			case '0':
				return 'Mensuel';
			case '1':
				return 'Trimestriel 1';
			case '2':
				return 'Trimestriel 2';
			case '3':
				return 'Trimestriel 3';
			case '4':
				return 'Annuel 1';//TODO en fonction de l'historique existant
			case '5':
				return 'Semestriel 1';//TODO en fonction de l'historique existant
			default:
				return $period4D;
		}
	}
}
