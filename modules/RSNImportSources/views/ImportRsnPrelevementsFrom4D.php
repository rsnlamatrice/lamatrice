<?php


/* Phase de migration
 * Importation des prelevements Web depuis le fichier provenant de 4D
 */
class RSNImportSources_ImportRsnPrelevementsFrom4D_View extends RSNImportSources_ImportFromFile_View {
        
	/**
	 * Method to get the source import label to display.
	 * @return string - The label.
	 */
	public function getSource() {
		return 'LBL_PRELEVEMENTS_4D';
	}

	/**
	 * Method to get the modules that are concerned by the import.
	 * @return array - An array containing concerned module names.
	 */
	public function getImportModules() {
		return array('RsnPrelevements');
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
	function getRsnPrelevementsFields() {
		return array(
			'reffiche',
			'nom',
			'numcompte',
			'codebanque',
			'codeguichet',
			'clerib',
			'montant',
			'periodicite',
			'msg',
			'datedernmodif',
			'heuredernmodif',
			'datecreation',
			'dejapreleve',
			'etat',
			'datedernmodifetat',
			'datedernmodifmontant',
			'prelevementenligne',
			'origine',
			'originedernmodif',
			'sepaibanpays',
			'sepaibancle',
			'sepaibanbban',
			'sepabic',
			'sepadatesignature',
			'separum',
			'datedernierpvt',
			'heuretraitementpvt',
			
			/* post pré import */
			'_contactid',
		);
	}

	/**
	 * Method to process to the import of the RsnPrelevements module.
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importRsnPrelevements($importDataController) {
		global $VTIGER_BULK_SAVE_MODE;
		$VTIGER_BULK_SAVE_MODE = true;
		
		$config = new RSNImportSources_Config_Model();

		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($this->user, 'RsnPrelevements');
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
			$this->importOneRsnPrelevements(array($row), $importDataController);
		}
		
		if($numberOfRecords == $config->get('importBatchLimit')){
			$this->keepScheduledImport = $this->getNumberOfRecords() > 0;
		}
	}

	/**
	 * Method to process to the import of a one prelevement.
	 * @param $rsnprelevementsData : the data of the prelevement to import
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importOneRsnPrelevements($rsnprelevementsData, $importDataController) {
					
		global $log;
		
		//TODO check sizeof $rsnprelevementsata
		$sourceId = $rsnprelevementsData[0]['separum'];
		$contact = $this->getContact($rsnprelevementsData);
		if ($contact != null) {
			$account = $contact->getAccountRecordModel();

			if ($account != null) {
				$datesign = $rsnprelevementsData[0]['sepadatesignature'];
				$etat = $rsnprelevementsData[0]['etat'];
				$numcompte = $rsnprelevementsData[0]['numcompte'];
				$sepabic = $rsnprelevementsData[0]['sepabic'];
				$montant = $rsnprelevementsData[0]['montant'];
				//test sur separum == $sourceId
				$query = "SELECT crmid
					FROM vtiger_rsnprelevements
					JOIN vtiger_crmentity
					    ON vtiger_rsnprelevements.rsnprelevementsid = vtiger_crmentity.crmid
					WHERE separum = ?
					AND accountid = ?
					AND sepadatesignature = ?
					AND etat = ?
					AND numcompte = ?
					AND sepabic = ?
					AND montant = ?
					AND deleted = FALSE
					LIMIT 1
				";
				$db = PearDatabase::getInstance();
				$result = $db->pquery($query, array($sourceId, $account->getId(), $datesign, $etat, $numcompte, $sepabic, $montant));
				if($db->num_rows($result)){
					//already imported !!
					$row = $db->fetch_row($result, 0); 
					$entryId = $this->getEntryId("RsnPrelevements", $row['crmid']);
					foreach ($rsnprelevementsData as $rsnprelevementsLine) {
						$entityInfo = array(
							'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_SKIPPED,
							'id'		=> $entryId
						);
						
						//TODO update all with array
						$importDataController->updateImportStatus($rsnprelevementsLine[id], $entityInfo);
					}
				}
				else {
					$record = Vtiger_Record_Model::getCleanInstance('RsnPrelevements');
					$record->set('mode', 'create');
					foreach($rsnprelevementsData[0] as $fieldName => $value)
						if(!is_numeric($fieldName) && $fieldName != 'id')
							$record->set($fieldName, $value);
					
					$fieldName = 'montant';
					$value = str_replace('.', ',', $rsnprelevementsData[0][$fieldName]);
					$record->set($fieldName, $value);
					
					$fieldName = 'accountid';
					$value = $account->getId();
					$record->set($fieldName, $value);
					
					$fieldName = 'rsnprelvtype';
					$value = 'Prélèvement périodique';
					$record->set($fieldName, $value);
					
					//$db->setDebug(true);
					$record->save();
					$rsnprelevementsId = $record->getId();

					if(!$rsnprelevementsId){
						//TODO: manage error
						echo "<pre><code>Impossible d'enregistrer le prélèvement</code></pre>";
						foreach ($rsnprelevementsData as $rsnprelevementsLine) {
							$entityInfo = array(
								'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
							);
							
							//TODO update all with array
							$importDataController->updateImportStatus($rsnprelevementsLine[id], $entityInfo);
						}

						return false;
					}
					
					
					$entryId = $this->getEntryId("RsnPrelevements", $rsnprelevementsId);
					foreach ($rsnprelevementsData as $rsnprelevementsLine) {
						$entityInfo = array(
							'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_CREATED,
							'id'		=> $entryId
						);
						$importDataController->updateImportStatus($rsnprelevementsLine[id], $entityInfo);
					}
					
					$record->set('mode','edit');
					$query = "UPDATE vtiger_crmentity
						JOIN vtiger_rsnprelevements
							ON vtiger_crmentity.crmid = vtiger_rsnprelevements.rsnprelevementsid
						SET smownerid = ?
						, modifiedtime = ?
						, createdtime = ?
						WHERE vtiger_crmentity.crmid = ?
					";
					$result = $db->pquery($query, array(ASSIGNEDTO_ALL
									    , $rsnprelevementsData[0]['datedernmodif']
									    , $rsnprelevementsData[0]['datecreation']
									    , $rsnprelevementsId));
					
					$log->debug("" . basename(__FILE__) . " update imported rsnprelevements (id=" . $record->getId() . ", sourceId=$sourceId , date=" . $rsnprelevementsData[0]['datecreation']
						    . ", result=" . ($result ? " true" : "false"). " )");
					if( ! $result)
						$db->echoError();
						
						
					
					return $record;
				}
			} else {
				//TODO: manage error
				$log->debug("" . basename(__FILE__) . " error importing rsnprelevements (sourceId=$sourceId , date=" . $rsnprelevementsData[0]['datecreation']
						    . ", result=Compte inconnu pour ContactId=".$contact->getId());
					
				echo "<pre><code>Unable to find Account</code></pre>";
			}
		} else {
			$log->debug("" . basename(__FILE__) . " error importing rsnprelevements (sourceId=$sourceId , date=" . $rsnprelevementsData[0]['datecreation']
						. ", result=Contact inconnu");
			foreach ($rsnprelevementsData as $rsnprelevementsLine) {//TODO: remove duplicated code
				$entityInfo = array(
					'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
				);
				
				$importDataController->updateImportStatus($rsnprelevementsLine[id], $entityInfo);
			}

			return false;
		}

		return true;
	}

	/**
	 * Method that pre import an invoice.
	 *  It adds one row in the temporary pre-import table by invoice line.
	 * @param $rsnprelevementsData : the data of the invoice to import.
	 */
	function preImportRsnPrelevements($rsnprelevementsData) {
		
		$rsnprelevementsValues = $this->getRsnPrelevementsValues($rsnprelevementsData);
		////TODO : cache
		//$query = "SELECT 1
		//	FROM vtiger_rsnprelevements
		//	JOIN vtiger_crmentity
		//		ON vtiger_crmentity.crmid = vtiger_rsnprelevements.rsnprelevementsid
		//	WHERE vtiger_crmentity.deleted = 0
		//	AND separum = ?
		//	LIMIT 1
		//";
		//$sourceId = $rsnprelevementsData[0]['separum'];
		//$db = PearDatabase::getInstance();
		//$result = $db->pquery($query, array($sourceId));//$rsnprelevementsData[0]['subject']
		//if($db->num_rows($result))
		//	return true;
		//
		$rsnprelevements = new RSNImportSources_Preimport_Model($rsnprelevementsValues, $this->user, 'RsnPrelevements');
		$rsnprelevements->save();
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
		if(is_array($ref4d)){ //$ref4d is $rsnprelvirementsData
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
			if ($this->moveCursorToNextRsnPrelevements($fileReader)) {
				$i = 0;
				do {
					$rsnprelevements = $this->getNextRsnPrelevements($fileReader);
					if ($rsnprelevements != null) {
						$this->preImportRsnPrelevements($rsnprelevements);
					}
				} while ($rsnprelevements != null);

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
			'RsnPrelevements',
			'reffiche',
			'_contactid',
			/*$changeStatus*/ false
		);
	
		RSNImportSources_Utils_Helper::skipPreImportDataForMissingContactsByRef4D(
			$this->user,
			'RsnPrelevements',
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
	function moveCursorToNextRsnPrelevements(RSNImportSources_FileReader_Reader $fileReader) {
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
	function getNextRsnPrelevements(RSNImportSources_FileReader_Reader $fileReader) {
		$nextLine = $fileReader->readNextDataLine($fileReader);
		if ($nextLine != false) {
			$rsnprelevements = array(
				'prlvInformations' => $nextLine,
				'detail' => array());
			do {
				$cursorPosition = $fileReader->getCurentCursorPosition();
				$nextLine = $fileReader->readNextDataLine($fileReader);

				if (!$this->isRecordHeaderInformationLine($nextLine)) {
					if ($nextLine[1] != null && $nextLine[1] != '') {
						//impossible ici array_push($rsnprelevements['detail'], $nextLine);
					}
				} else {
					break;
				}

			} while ($nextLine != false);

			if ($nextLine != false) {
				$fileReader->moveCursorTo($cursorPosition);
			}

			return $rsnprelevements;
		}

		return null;
	}
	
	/**
	 * Method that return the formated information of an invoice found in the file.
	 * @param $rsnprelevements : the invoice data found in the file.
	 * @return array : the formated data of the invoice.
	 */
	function getRsnPrelevementsValues($rsnprelevements) {
	//TODO end implementation of this method
		//affectation de la date de création comme date de 1er prélèvement
		$dejapreleve = $rsnprelevements['prlvInformations'][12] == '1' && $rsnprelevements['prlvInformations'][11] != '00/00/00'
			?  $this->getMySQLDate($rsnprelevements['prlvInformations'][11]) : null;
		$rsnprelevementsHeader = array(
			'reffiche'	=> $rsnprelevements['prlvInformations'][0],
			'nom'	=> $rsnprelevements['prlvInformations'][1],
			'numcompte'	=> $rsnprelevements['prlvInformations'][2],
			'codebanque'	=> $rsnprelevements['prlvInformations'][3],
			'codeguichet'	=> $rsnprelevements['prlvInformations'][4],
			'clerib'	=> $rsnprelevements['prlvInformations'][5],
			'montant'	=> str_to_float($rsnprelevements['prlvInformations'][6]),
			'periodicite'	=> $this->getPeriodiciteFrom4D($rsnprelevements['prlvInformations'][7], $this->getMySQLDate($rsnprelevements['prlvInformations'][11])),
			'msg'	=> $rsnprelevements['prlvInformations'][8],
			'datedernmodif'	=> $this->getMySQLDate($rsnprelevements['prlvInformations'][9]),
			'heuredernmodif'	=> $rsnprelevements['prlvInformations'][10],
			'datecreation'	=> $this->getMySQLDate($rsnprelevements['prlvInformations'][11]),
			'dejapreleve'	=> $dejapreleve,
			'etat'	=> $rsnprelevements['prlvInformations'][13],
			'datedernmodifetat'	=> $this->getMySQLDate($rsnprelevements['prlvInformations'][14]),
			'datedernmodifmontant'	=> $this->getMySQLDate($rsnprelevements['prlvInformations'][15]),
			'prelevementenligne'	=> $rsnprelevements['prlvInformations'][16],
			'origine'	=> $rsnprelevements['prlvInformations'][17],
			'originedernmodif'	=> $rsnprelevements['prlvInformations'][18],
			'sepaibanpays'	=> $rsnprelevements['prlvInformations'][19],
			'sepaibancle'	=> $rsnprelevements['prlvInformations'][20],
			'sepaibanbban'	=> $rsnprelevements['prlvInformations'][21],
			'sepabic'	=> $rsnprelevements['prlvInformations'][22],
			'sepadatesignature'	=> $this->getMySQLDate($rsnprelevements['prlvInformations'][23]),
			'separum'	=> $rsnprelevements['prlvInformations'][24],
			'datedernierpvt'	=> $rsnprelevements['prlvInformations'][25],
			'heuretraitementpvt'	=> $rsnprelevements['prlvInformations'][26],
		);
		return $rsnprelevementsHeader;
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
				return 'Annuel 10';//TODO en fonction de l'historique existant
			case '5':
				return 'Semestriel 4';//TODO en fonction de l'historique existant
			default:
				return $period4D;
		}
	}
}
