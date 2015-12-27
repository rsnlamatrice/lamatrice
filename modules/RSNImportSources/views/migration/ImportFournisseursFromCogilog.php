<?php


/* Phase de migration
 * Importation des fournisseurs depuis le fichier provenant de Cogilog
 *
 * L'export s'effectue depuis le menu Fichier de Cogilog
 */
class RSNImportSources_ImportFournisseursFromCogilog_View extends RSNImportSources_ImportFromFile_View {
        
	/**
	 * Method to get the source import label to display.
	 * @return string - The label.
	 */
	public function getSource() {
		return 'LBL_FOURNISSEURS_COGILOG';
	}

	/**
	 * Method to get the modules that are concerned by the import.
	 * @return array - An array containing concerned module names.
	 */
	public function getImportModules() {
		return array('Vendors');
	}

	/**
	 * Method to default file enconding for this import.
	 * @return string - the default file encoding.
	 */
	public function getDefaultFileEncoding() {
		return 'macintosh';
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
	 * Method to get the imported fields for the vendors module.
	 * @return array - the imported fields for the vendors module.
	 */
	function getVendorsFieldsMapping() {
		//laisser exactement les colonnes du fichier, dans l'ordre 
		return array (
			"categorie" => "vendorscategory",//vide check picklist
			"code" => "vendorcode",//Code Fournisseur
			"prefixe_fournisseur" => "",//vide
			"nom" => "vendorname",
			"num_rue" => "",//à concaténer avec Rue
			"rue" => "street",
			"complement" => "street2",
			"complement2" => "street3",//si BP*, dans pobox			
			"cp" => "postalcode",
			"ville" => "city",
			"cedex" => "pobox",
			"pays" => "country",//vider si FR
			"sexe" => "",
			"contactcivilite" => "",//CONCAT contactname
			"contact" => "contactname",//TODO
			"contactprenom" => "",//CONCAT contactname
			"contactfunction" => "",//ignore
			"telephone1" => "phone",//
			"telephone2" => "phone2",//
			"fax" => "fax",//TODO
			"email" => "email",			
			"num_intracom" => "intracom",//TODO fusion avec compte ?
			"siret" => "",//à mettre dans glacct si glacct est vide
			"compte" => "glacct",
			"banque1" => "",//vide
			"banquecode1" => "",//vide
			"banqueguichet1" => "",//vide
			"banquecompte1" => "",//vide
			"banquecptecle1" => "",//vide
			"facturation" => "paymode",//TODO mode de facturation HT+TVA, TTC, Import HT
			"paiement" => "paycomment",//TODO remarque sur le paiement			
			"r_pc" => "",//vide taux de remise
			"delai" => "",//vide
			"nbjours" => "paydelay",//TODO délai de paiement
			"paiementcode" => "",// 	
			"jour" => "",//
			"commande" => "",//ignorer 
			"br" => "",//ignorer 
			"fact" => "",//ignorer 
			"ar" => "",//Étiquettes adresses 
			"notation" => "",//ignorer 
			"notes" => "description",
			"monnumclient" => "",//ignorer 
			"nbexemplaires" => "",//vide
			"texte1" => "",//vide
			"texte2" => "",//vide
			"texte3" => "",//vide
			"texte4" => "",//vide
			"texte5" => "",//vide
			"texte6" => "",//vide
			"texte7" => "",//vide
			"texte8" => "",//vide
			"texte9" => "",//vide
			"nombre1" => "",//vide
			"nombre2" => "",//vide
			"nombre3" => "",//vide
			"nombre4" => "",//vide
			"nombre5" => "",//vide
			"nombre6" => "",//vide
			"nombre7" => "",//vide
			"nombre8" => "",//vide
			"nombre9" => "",//vide
			"date1" => "",//vide
			"date2" => "",//vide
			"date3" => "",//vide
			"date4" => "",//vide
			"date5" => "",//vide
			"date6" => "",//vide
			"date7" => "",//vide
			"date8" => "",//vide
			"date9" => "",//vide			
			"iban1" => "",
			"bic1" => "",
			"iban2" => "",
			"dossierattache" => "",
			"banque2" => "",
			"iban2" => "",
			"bic2" => "",
			"banque3" => "",
			"iban3" => "",
			"bic3" => "",
			"banque4" => "",
			"iban4" => "",
			"bic4" => "",
			"nbexbc" => "",
			"nbexbr" => "",
			"nbexar" => "",
		);
	}
	
	function getVendorsDateFields(){
		return array();
	}
	
	/**
	 * Method to get the imported fields for the vendors module.
	 * @return array - the imported fields for the vendors module.
	 */
	function getVendorsFields() {
		//laisser exactement les colonnes du fichier
		return array_keys($this->getVendorsFieldsMapping());
	}

	/**
	 * Method to process to the import of the Vendors module.
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importVendors($importDataController) {
		global $VTIGER_BULK_SAVE_MODE;
		$VTIGER_BULK_SAVE_MODE = true;
		$config = new RSNImportSources_Config_Model();
		
		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($this->user, 'Vendors');
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
			$this->importOneVendors(array($row), $importDataController);
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
	 * Method to process to the import of a one prelevement.
	 * @param $vendorsData : the data of the prelevement to import
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importOneVendors($vendorsData, $importDataController) {
					
		global $log;
		
		$sourceId = $vendorsData[0]['nom'];
		
		//test sur nom == $sourceId
		$query = "SELECT crmid
			FROM vtiger_vendor
			JOIN vtiger_crmentity
				ON vtiger_vendor.vendorid = vtiger_crmentity.crmid
			WHERE deleted = FALSE
			AND vendorname = ?
			LIMIT 1
		";
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, array($sourceId));
		if($db->num_rows($result)){
			//already imported !!
			$row = $db->fetch_row($result, 0); 
			$entryId = $this->getEntryId("Vendors", $row['crmid']);
			foreach ($vendorsData as $vendorsLine) {
				$entityInfo = array(
					'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_SKIPPED,
					'id'		=> $entryId
				);
				
				//TODO update all with array
				$importDataController->updateImportStatus($vendorsLine[id], $entityInfo);
			}
		}
		else {
			$record = Vtiger_Record_Model::getCleanInstance('Vendors');
			$record->set('mode', 'create');
			
			$this->updateVendorsRecordModelFromData($record, $vendorsData);
			
			//$db->setDebug(true);
			$record->save();
			$vendorsId = $record->getId();
			
			if(!$vendorsId){
				//TODO: manage error
				echo "<pre><code>Impossible d'enregistrer le fournisseur</code></pre>";
				foreach ($vendorsData as $vendorsLine) {
					$entityInfo = array(
						'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
					);
					
					//TODO update all with array
					$importDataController->updateImportStatus($vendorsLine[id], $entityInfo);
				}

				return false;
			}
			
			$entryId = $this->getEntryId("Vendors", $vendorsId);
			foreach ($vendorsData as $vendorsLine) {
				$entityInfo = array(
					'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_CREATED,
					'id'		=> $entryId
				);
				$importDataController->updateImportStatus($vendorsLine[id], $entityInfo);
			}
			
			/*$record->set('mode','edit');
			$db = PearDatabase::getInstance();
			$query = "UPDATE vtiger_crmentity
				JOIN vtiger_vendor
					ON vtiger_crmentity.crmid = vtiger_vendor.vendorid
				SET smownerid = ?
				, createdtime = ?
				, modifiedtime = ?
				WHERE vtiger_crmentity.crmid = ?
			";
			$result = $db->pquery($query, array(ASSIGNEDTO_ALL
								, $vendorsData[0]['date_saisie']
								, $vendorsData[0]['date_modif'] ? $vendorsData[0]['date_modif'] : $vendorsData[0]['date_saisie']
								, $vendorsId));
			
			$log->debug("" . basename(__FILE__) . " update imported vendors (id=" . $record->getId() . ", Ref 4D=$sourceId , date=" . $vendorsData[0]['date_saisie']
					. ", result=" . ($result ? " true" : "false"). " )");
			if( ! $result)
				$db->echoError();
			else {
			}*/
			return $record;
		}

		return true;
	}

	//Mise à jour des données du record model nouvellement créé à partir des données d'importation
	private function updateVendorsRecordModelFromData($record, $vendorsData){
		
		$fieldsMapping = $this->getVendorsFieldsMapping();
		foreach($vendorsData[0] as $fieldName => $value)
			if(!is_numeric($fieldName) && $fieldName != 'id'){
				$vField = $fieldsMapping[$fieldName];
				if($vField)
					$record->set($vField, $value);
			}
		
		//cast des DateTime
		foreach($this->getVendorsDateFields() as $fieldName){
			$value = $record->get($fieldName);
			if( is_object($value) )
				$record->set($fieldsMapping[$fieldName], $value->format('Y-m-d'));
		}
		
		
		$fieldName = 'enable';
		$record->set($fieldName, 1);
		
		$fieldName = 'pobox';
		if(!$record->get($fieldName)){
			$value = $record->get('street3');
			if(strpos($value,'BP') === 0){
				$record->set('street3', '');
				$record->set($fieldName, $value);
			}
			else
			{				
				$value = $record->get('street2');
				if(strpos($value,'BP') === 0){
					$record->set('street2', '');
					$record->set($fieldName, $value);
				}
			}
		}
		
		$fieldName = 'contactname';
		$contactName = $record->get($fieldName);
		$value = $vendorsData[0]['contactprenom'];
		if($value){
			$contactName = $value . ' ' . $contactName;
		}
		$value = $vendorsData[0]['contactcivilite'];
		if($value){
			$contactName = $value . ' ' . $contactName;
		}
		$record->set($fieldName, trim($contactName));
		
		
		$fieldName = 'glacct';
		if(!$record->get($fieldName)){
			$value = $vendorsData[0]['siret'];
			if($value)
				$record->set($fieldName, $value);
		}
		//"categorie" => "vendorscategory",//vide check picklist
		//"prefixe_fournisseur" => "",//vide
		//"code" => "vendorcode",//Code Fournisseur
		//"nom" => "vendorname",
		//"num_rue" => "",//à concaténer avec Rue
		//"rue" => "street",
		//"complement" => "street2",
		//"complement2" => "street3",//si BP*, dans pobox			
		//"cp" => "postalcode",
		//"ville" => "city",
		//"cedex" => "pobox",
		//"pays" => "country",//vider si FR
		//"sexe" => "",
		//"contactcivilite" => "",//CONCAT contactname
		//"contact" => "contactname",//TODO
		//"contactprenom" => "",//CONCAT contactname
		//"contactfunction" => "",//ignore
		//"telephone1" => "phone",//
		//"telephone2" => "phone2",//
		//"fax" => "fax",
		//"email" => "email",			
		//"num_intracom" => "intracom",//TODO fusion avec compte ?
		//"siret" => "",//à mettre dans glacct si glacct est vide
		//"compte" => "glacct",
		//"banque1" => "",//vide
		//"banquecode1" => "",//vide
		//"banqueguichet1" => "",//vide
		//"banquecompte1" => "",//vide
		//"banquecptecle1" => "",//vide
		//"facturation" => "paymode",//TODO mode de facturation HT+TVA, TTC, Import HT
		//"paiement" => "paycomment",//TODO remarque sur le paiement			
		//"r_pc" => "",//vide taux de remise
		//"delai" => "",//vide
		//"nbjours" => "paydelay",//TODO délai de paiement
		//"paiementcode" => "",// 	
		//"jour" => "",//
		//"commande" => "",//ignorer 
		//"br" => "",//ignorer 
		//"fact" => "",//ignorer 
		//"ar" => "",//Étiquettes adresses 
		//"notation" => "",//ignorer 
		//"notes" => "description",
		//"monnumclient" => "",//ignorer 
		//"nbexemplaires" => "",//vide
		//"texte1" => "",//vide
		//"texte2" => "",//vide
		//"texte3" => "",//vide
		//"texte4" => "",//vide
		//"texte5" => "",//vide
		//"texte6" => "",//vide
		//"texte7" => "",//vide
		//"texte8" => "",//vide
		//"texte9" => "",//vide
		//"nombre1" => "",//vide
		//"nombre2" => "",//vide
		//"nombre3" => "",//vide
		//"nombre4" => "",//vide
		//"nombre5" => "",//vide
		//"nombre6" => "",//vide
		//"nombre7" => "",//vide
		//"nombre8" => "",//vide
		//"nombre9" => "",//vide
		//"date1" => "",//vide
		//"date2" => "",//vide
		//"date3" => "",//vide
		//"date4" => "",//vide
		//"date5" => "",//vide
		//"date6" => "",//vide
		//"date7" => "",//vide
		//"date8" => "",//vide
		//"date9" => "",//vide			
		//"iban1" => "",
		//"bic1" => "",
		//"iban2" => "",
		//"dossierattache" => "",
		//"banque2" => "",
		//"iban2" => "",
		//"bic2" => "",
		//"banque3" => "",
		//"iban3" => "",
		//"bic3" => "",
		//"banque4" => "",
		//"iban4" => "",
		//"bic4" => "",
		//"nbexbc" => "",
		//"nbexbr" => "",
		//"nbexar" => "",
	}
	
	/**
	 * Method that pre import an invoice.
	 *  It adds one row in the temporary pre-import table by invoice line.
	 * @param $vendorsData : the data of the invoice to import.
	 */
	function preImportVendors($vendorsData) {
		
		$vendorsValues = $this->getVendorsValues($vendorsData);
		
		$vendors = new RSNImportSources_Preimport_Model($vendorsValues, $this->user, 'Vendors');
		$vendors->save();
	}
	
	/**
	 * Method to parse the uploaded file and save data to the temporary pre-import table.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - true if pre-import is ended successfully
	 */
	function parseAndSaveFile(RSNImportSources_FileReader_Reader $fileReader) {
		$this->clearPreImportTable();
		
		if($fileReader->open()) {
			if ($this->moveCursorToNextVendors($fileReader)) {
				$i = 0;
				do {
					$vendors = $this->getNextVendors($fileReader);
					if ($vendors != null) {
						$this->preImportVendors($vendors);
					}
				} while ($vendors != null);

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
		$dateArray = preg_split('/[-\/\s]/', trim($string));
		if(strlen($dateArray[2])<4)
			$dateArray[2] += 2000;
		return $dateArray[2] . '-' . $dateArray[1] . '-' . $dateArray[0];
	}

	/**
	 * Method that check if a line of the file is a vendors information line.
	 *  It assume that the line is a client information line only and only if the first data is a date.
	 * @param array $line : the data of the file line.
	 * @return boolean - true if the line is a vendors information line.
	 */
	function isRecordHeaderInformationLine($line) {
		if (sizeof($line) > 0 && !$line[2] && $line[3]) {
			return true;
		}

		return false;
	}

	/**
	 * Method that move the cursor of the file reader to the beginning of the next found invoice.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - false if error or if no invoice found.
	 */
	function moveCursorToNextVendors(RSNImportSources_FileReader_Reader $fileReader) {
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
	function getNextVendors(RSNImportSources_FileReader_Reader $fileReader) {
		$nextLine = $fileReader->readNextDataLine($fileReader);
		if ($nextLine != false) {
			$vendors = array(
				'header' => $nextLine,
				'detail' => array());
			do {
				$cursorPosition = $fileReader->getCurentCursorPosition();
				$nextLine = $fileReader->readNextDataLine($fileReader);

				if (!$this->isRecordHeaderInformationLine($nextLine)) {
					if ($nextLine[1] != null && $nextLine[1] != '') {
						//impossible ici array_push($vendors['detail'], $nextLine);
					}
				} else {
					break;
				}

			} while ($nextLine != false);

			if ($nextLine != false) {
				$fileReader->moveCursorTo($cursorPosition);
			}
			return $vendors;
		}

		return null;
	}
	
	/**
	 * Method that return the formated information of a record found in the file.
	 * @param $vendor : the invoice data found in the file.
	 * @return array : the formated data of the invoice.
	 */
	function getVendorsValues($vendor) {
		$fields = $this->getVendorsFields();
		
		// contrôle l'égalité des tailles de tableaux
		if(count($fields) != count($vendor['header'])){
			if(count($fields) > count($vendor['header']))
				$vendor['header'] = array_merge($vendor['header'], array_fill (0, count($fields) - count($vendor['header']), null));
			else
				$vendor['header'] = array_slice($vendor['header'], 0, count($fields));
		}
		//tableau associatif dans l'ordre fourni
		$vendorHeader = array_combine($fields, $vendor['header']);
		
		//Parse dates
		foreach($this->getVendorsDateFields() as $fieldName)
			$vendorHeader[$fieldName] = $this->getMySQLDate($vendorHeader[$fieldName]);
		
		$fieldName = "ville";
		$vendorHeader[$fieldName] = strtoupper($vendorHeader[$fieldName]);
			
		$fieldName = "rue";
		if($vendorHeader["num_rue"] && stripos($vendorHeader[$fieldName], $vendorHeader["num_rue"]) === false)
			$vendorHeader[$fieldName] = trim($vendorHeader["num_rue"] . ' ' . $vendorHeader[$fieldName]);
		
		$fieldName = "cedex";
		//"cedex" => "pobox",
		if($vendorHeader[$fieldName] && strcasecmp($vendorHeader[$fieldName], 'cedex') !== false)
			$vendorHeader[$fieldName] = trim('Cedex ' . $vendorHeader[$fieldName]);
		
		$fieldName = "complement2";
		//"complement2" => "street3",//si BP*, dans pobox
		if(stripos($vendorHeader[$fieldName], 'TSA') === 0
		|| stripos($vendorHeader[$fieldName], 'BP') === 0
		|| stripos($vendorHeader[$fieldName], 'boite postale') === 0){
			$vendorHeader['cedex'] = trim($vendorHeader[$fieldName] . ' ' . $vendorHeader['cedex']);
			$vendorHeader[$fieldName] = '';
		}
		
		return $vendorHeader;
	}
	
}