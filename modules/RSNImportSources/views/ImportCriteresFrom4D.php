<?php


/* Phase de migration
 * Importation des prelevements Web depuis le fichier provenant de 4D
 */
class RSNImportSources_ImportCriteresFrom4D_View extends RSNImportSources_ImportFromFile_View {
        
	/**
	 * Method to get the source import label to display.
	 * @return string - The label.
	 */
	public function getSource() {
		return 'LBL_CRITERES4D_4D';
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
	 * Method to get the imported fields for the critere4ds module.
	 * @return array - the imported fields for the critere4ds module.
	 */
	function getCritere4DFieldsMapping() {
		//laisser exactement les colonnes du fichier, dans l'ordre 
		return array (
			'critere' => 'nom',
			'libelledetail' => 'commentaire',
			'periode' => 'usage_debut',
			'periodefin' => 'usage_fin',
			'ordredetri' => 'ordredetri',
			'critereorigine' => 'origine',
			'numeroencours' => '',//toujours à 0
			'utilisedansautomates' => '',//nu
			'typechampcompl' => '',//relationparameters .=
			'libellechampcompl' => '',//relationparameters .=
			'libelledatecompl' => '',//relationparameters .=
			'valeur_par_defaut' => '',//nu
			'categorieducritere' => 'categorie',
			'vtiger' => '',//abandon
		);
			//donotcourrierag, rsnwebhide dans import Groupe ?
	}
	
	function getCritere4DDateFields(){
		return array(
			'periode', 'periodefin',
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

		$perf = new RSNImportSources_Utils_Performance($numberOfRecords);
		for ($i = 0; $i < $numberOfRecords; ++$i) {
			$row = $adb->raw_query_result_rowdata($result, $i);
			$this->importOneCritere4D(array($row), $importDataController);
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
	 * @param $critere4dsData : the data of the prelevement to import
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importOneCritere4D($critere4dsData, $importDataController) {
					
		global $log;
		
		$sourceId = $critere4dsData[0]['critere'];
		
		//test sur nom == $sourceId
		$query = "SELECT crmid
			FROM vtiger_rsnprelevements
			JOIN vtiger_crmentity
				ON vtiger_rsnprelevements.rsnprelevementsid = vtiger_crmentity.crmid
			WHERE deleted = FALSE
			AND nom = ?
			LIMIT 1
		";
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, array($sourceId));
		if($db->num_rows($result)){
			//already imported !!
			$row = $db->fetch_row($result, 0); 
			$entryId = $this->getEntryId("Critere4D", $row['crmid']);
			foreach ($critere4dsData as $critere4dsLine) {
				$entityInfo = array(
					'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_SKIPPED,
					'id'		=> $entryId
				);
				
				//TODO update all with array
				$importDataController->updateImportStatus($critere4dsLine[id], $entityInfo);
			}
		}
		else {
			$record = Vtiger_Record_Model::getCleanInstance('Critere4D');
			$record->set('mode', 'create');
			
			$this->updateCritere4DRecordModelFromData($record, $critere4dsData);
			
			//$db->setDebug(true);
			$record->save();
			$critere4dId = $record->getId();
			
			if(!$critere4dId){
				//TODO: manage error
				echo "<pre><code>Impossible d'enregistrer le critere4d</code></pre>";
				foreach ($critere4dsData as $critere4dsLine) {
					$entityInfo = array(
						'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
					);
					
					//TODO update all with array
					$importDataController->updateImportStatus($critere4dsLine[id], $entityInfo);
				}

				return false;
			}
			
			$entryId = $this->getEntryId("Critere4D", $critere4dId);
			foreach ($critere4dsData as $critere4dsLine) {
				$entityInfo = array(
					'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_CREATED,
					'id'		=> $entryId
				);
				$importDataController->updateImportStatus($critere4dsLine[id], $entityInfo);
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
								, $critere4dsData[0]['periode']
								, $critere4dId));
			
			$log->debug("" . basename(__FILE__) . " update imported critere4ds (id=" . $record->getId() . ", Ref 4D=$sourceId , date=" . $critere4dsData[0]['datecreation']
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
	private function updateCritere4DRecordModelFromData($record, $critere4dsData){
		
		$fieldsMapping = $this->getCritere4DFieldsMapping();
		foreach($critere4dsData[0] as $fieldName => $value)
			if(!is_numeric($fieldName) && $fieldName != 'id'){
				$vField = $fieldsMapping[$fieldName];
				if($vField)
					$record->set($vField, $value);
			}
		
		//cast des DateTime
		foreach($this->getCritere4DDateFields() as $fieldName){
			$value = $record->get($fieldName);
			if( is_object($value) )
				$record->set($fieldsMapping[$fieldName], $value->format('Y-m-d'));
		}
		
		//TODO Check exist in picklist known values -> generic function to insert new value
		//'critereorigine' => 'origine',
		$fieldName = 'origine';
		if($record->get($fieldName))
			RSNImportSources_Utils_Helper::checkPickListValue('Critere4D', $fieldName, $fieldName, $record->get($fieldName));
		
		//'categorieducritere' => 'categorie',
		$fieldName = 'categorie';
		if(!$record->get($fieldName))//critère par défaut : Divers
			$record->set($fieldName, 'Divers');
		elseif(!$record->get($fieldName))
			RSNImportSources_Utils_Helper::checkPickListValue('Critere4D', $fieldName, $fieldName, $record->get($fieldName));
		
			
		$fieldName = 'relationparameters';
		if($critere4dsData[0]['typechampcompl']){
			$value = $record->get($fieldName);
			foreach(array('typechampcompl', 'libellechampcompl', 'libelledatecompl' ) as $srcField)
				if($critere4dsData[0][$srcField]){
					if($value) $value .= "\r";
					$value .= "4D.$srcField = ".$critere4dsData[0][$srcField];
				}
			$record->set($fieldName, $value);
		}
		
		//'critere' => 'nom',
		//'libelledetail' => 'commentaire',
		//'periode' => 'usage_debut',
		//'periodefin' => 'usage_fin',
		//'ordredetri' => 'ordredetri',
		//'critereorigine' => 'origine',
		//'numeroencours' => '',//toujours à 0
		//'utilisedansautomates' => '',//nu
		//'typechampcompl' => '',//relationparameters .=
		//'libellechampcompl' => '',//relationparameters .=
		//'libelledatecompl' => '',//relationparameters .=
		//'valeur_par_defaut' => '',//nu
		//'categorieducritere' => 'categorie',
		//'vtiger' => '',//abandon
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
			echo "not opened ...";
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
		if (sizeof($line) > 0 && is_numeric($line[5]) && $this->isDate($line[3])) {
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
		
		$fieldName = 'libelledetail';
		if($critere4DHeader[$fieldName])
			$critere4DHeader[$fieldName] = str_replace("\\r", "\r", $critere4DHeader[$fieldName]);
		
		return $critere4DHeader;
	}
	
}