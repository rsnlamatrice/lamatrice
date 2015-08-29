<?php

/*
 * Importation des donateurs Web depuis le fichier provenant du site en ligne.
 * Permet de créer les contacts et de référencer les références de transactions.
 */
class RSNImportSources_ImportRSNDonateursWebFromSite_View extends RSNImportSources_ImportFromFile_View {

	private $coupon = null;

	/**
	 * Method to get the source import label to display.
	 * @return string - The label.
	 */
	public function getSource() {
		return 'LBL_DONATEURSWEB';
	}

	/**
	 * Method to get the source type label to display.
	 * @return string - The label.
	 */
	public function getSourceType() {
		return 'LBL_CSV_FILE';
	}

	/**
	 * Method to get the modules that are concerned by the import.
	 * @return array - An array containing concerned module names.
	 */
	public function getImportModules() {
		return array('Contacts', 'RSNDonateursWeb');
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
	 * Method to default file enconding for this import.
	 * @return string - the default file encoding.
	 */
	public function getDefaultFileEncoding() {
		return 'macintosh';
	}

	/**
	 * Method to get the imported fields for the contact module.
	 * @return array - the imported fields for the contact module.
	 */
	public function getContactsFields() {
		return array(
			'lastname',
			'firstname',
			'email',
			'mailingstreet',
			'mailingstreet3',
			'mailingzip',
			'mailingcity',
			'mailingcountry',
			'phone',
			'mobile',
			'accounttype',
			'leadsource',
		);
	}

	/**
	 * Method to get the imported fields for the invoice module.
	 * @return array - the imported fields for the invoice module.
	 */
	function getRSNDonateursWebFields() {
		return array(
			'externalid',
			//'contactid',
			//'relatedmoduleid',
			'email',
			'datedon',
			'amount',
			'paiementerror',
			'modepaiement',
			'frequency',
			'paiementid',
			'abocode',
			'dateaboend',
			'recu',
			'revue',
			'clicksource',
			'listesn',
			//'assigned_user_id',
			//'createdtime',
			//'modifiedtime',
			'autorisation',
			'ipaddress',
			'firstname',
			'lastname',
			'address',
			'address3',
			'zip',
			'city',
			'country',
			'phone',
			'isvalid',
		);
	}

	/**
	 * Method to process to the import of the RSNDonateursWeb module.
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importRSNDonateursWeb($importDataController) {
		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($this->user, 'RSNDonateursWeb');
		$sql = 'SELECT * FROM ' . $tableName . ' WHERE status = '. RSNImportSources_Data_Action::$IMPORT_RECORD_NONE . ' ORDER BY id';

		$result = $adb->query($sql);
		$numberOfRecords = $adb->num_rows($result);

		if ($numberOfRecords <= 0) {
			return;
		}

		$row = $adb->raw_query_result_rowdata($result, 0);
		$previousRSNDonateursWebSubjet = $row['externalid'];//tmp subject, use invoice_no ???
		$rsndonateurswebData = array($row);

		for ($i = 1; $i < $numberOfRecords; ++$i) {
			$row = $adb->raw_query_result_rowdata($result, $i);
			$rsndonateurswebSubject = $row['externalid'];

			if ($previousRSNDonateursWebSubjet == $rsndonateurswebSubject) {
				array_push($rsndonateurswebData, $row);
			} else {
				$this->importOneRSNDonateursWeb($rsndonateurswebData, $importDataController);
				$rsndonateurswebData = array($row);
				$previousRSNDonateursWebSubjet = $rsndonateurswebSubject;
			}
		}

		$this->importOneRSNDonateursWeb($rsndonateurswebData, $importDataController);
	}

	/**
	 * Method to process to the import of a one donateur.
	 * @param $rsndonateurswebData : the data of the donateur to import
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importOneRSNDonateursWeb($rsndonateurswebData, $importDataController) {
					
		global $log;
		
		//TODO check sizeof $rsndonateurswebata
		$contact = $this->getContact($rsndonateurswebData[0]['firstname'], $rsndonateurswebData[0]['lastname'], $rsndonateurswebData[0]['email']);
		if ($contact != null) {
			$account = $contact->getAccountRecordModel();

			if ($account != null) {
				$sourceId = $rsndonateurswebData[0]['externalid'];
		
				//test sur externalid == $sourceId
				$query = "SELECT crmid
					FROM vtiger_rsndonateursweb
					JOIN vtiger_crmentity
					    ON vtiger_rsndonateursweb.rsndonateurswebid = vtiger_crmentity.crmid
					WHERE externalid = ? AND deleted = FALSE
					LIMIT 1
				";
				$db = PearDatabase::getInstance();
				$result = $db->pquery($query, array($sourceId));
				if($db->num_rows($result)){
					//already imported !!
					$row = $db->fetch_row($result, 0); 
					$entryId = $this->getEntryId("RSNDonateursWeb", $row['crmid']);
					foreach ($rsndonateurswebData as $rsndonateurswebLine) {
						$entityInfo = array(
							'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_SKIPPED,
							'id'		=> $entryId
						);
						
						//TODO update all with array
						$importDataController->updateImportStatus($rsndonateurswebLine[id], $entityInfo);
					}
				}
				else {
					$record = Vtiger_Record_Model::getCleanInstance('RSNDonateursWeb');
					$record->set('mode', 'create');
					foreach($rsndonateurswebData[0] as $fieldName => $value)
						if(!is_numeric($fieldName) && $fieldName != 'id')
							$record->set($fieldName, $value);
					
					$fieldName = 'amount';
					$value = str_replace('.', ',', $rsndonateurswebData[0][$fieldName]);
					$record->set($fieldName, $value);
					
					$fieldName = 'contactid';
					$value = $contact->getId();
					$record->set($fieldName, $value);
					
					//$db->setDebug(true);
					$record->save();
					$rsndonateurswebId = $record->getId();

					if(!$rsndonateurswebId){
						//TODO: manage error
						echo "<pre><code>Impossible d'enregistrer la nouvelle facture</code></pre>";
						foreach ($rsndonateurswebData as $rsndonateurswebLine) {
							$entityInfo = array(
								'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
							);
							
							//TODO update all with array
							$importDataController->updateImportStatus($rsndonateurswebLine[id], $entityInfo);
						}

						return false;
					}
					
					
					$entryId = $this->getEntryId("RSNDonateursWeb", $rsndonateurswebId);
					foreach ($rsndonateurswebData as $rsndonateurswebLine) {
						$entityInfo = array(
							'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_CREATED,
							'id'		=> $entryId
						);
						$importDataController->updateImportStatus($rsndonateurswebLine[id], $entityInfo);
					}
					
					$record->set('mode','edit');
					$query = "UPDATE vtiger_crmentity
						JOIN vtiger_rsndonateursweb
							ON vtiger_crmentity.crmid = vtiger_rsndonateursweb.rsndonateurswebid
						SET smownerid = ?
						"/*, createdtime = ?*/."
						WHERE vtiger_crmentity.crmid = ?
					";
					$result = $db->pquery($query, array(ASSIGNEDTO_ALL
									    /*, $rsndonateurswebData[0]['datedon']*/
									    , $rsndonateurswebId));
					
					$log->debug("" . basename(__FILE__) . " update imported rsndonateursweb (id=" . $record->getId() . ", sourceId=$sourceId , date=" . $rsndonateurswebData[0]['datedon']
						    . ", result=" . ($result ? " true" : "false"). " )");
					if( ! $result)
						$db->echoError();
						
						
					
					return $record;
				}
			} else {
				//TODO: manage error
				echo "<pre><code>Unable to find Account</code></pre>";
			}
		} else {
			foreach ($rsndonateurswebData as $rsndonateurswebLine) {//TODO: remove duplicated code
				$entityInfo = array(
					'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
				);
				
				$importDataController->updateImportStatus($rsndonateurswebLine[id], $entityInfo);
			}

			return false;
		}

		return true;
	}
	
	/**
	 * Method that pre import a contact.
	 * @param $contactValues : the values of the contact to import.
	 */
	function preImportContact($contactValues) {
		$contact = new RSNImportSources_Preimport_Model($contactValues, $this->user, 'Contacts');
		$contact->save();
	}

	/**
	 * Method that pre import an invoice.
	 *  It adds one row in the temporary pre-import table by invoice line.
	 * @param $rsndonateurswebData : the data of the invoice to import.
	 */
	function preImportRSNDonateursWeb($rsndonateurswebData) {
		$rsndonateurswebValues = $this->getRSNDonateursWebValues($rsndonateurswebData);
		//TODO : cache
		$query = "SELECT 1
			FROM vtiger_rsndonateursweb
			JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_rsndonateursweb.rsndonateurswebid
			WHERE vtiger_crmentity.deleted = 0
			AND externalid = ?
			LIMIT 1
		";
		$sourceId = $rsndonateurswebData[0]['externalid'];
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, array($sourceId));//$rsndonateurswebData[0]['subject']
		if($db->num_rows($result))
			return true;
		
		$rsndonateursweb = new RSNImportSources_Preimport_Model($rsndonateurswebValues, $this->user, 'RSNDonateursWeb');
		$rsndonateursweb->save();
	}

	/**
	 * Method that retrieve a contact.
	 * @param string $firstname : the firstname of the contact.
	 * @param string $lastname : the lastname of the contact.
	 * @param string $email : the mail of the contact.
	 * @return the row data of the contact | null if the contact is not found.
	 */
	function getContact($firstname, $lastname, $email) {
		$id = $this->getContactId($firstname, $lastname, $email);
		if($id){
			return Vtiger_Record_Model::getInstanceById($id, 'Contacts');
		}

		return null;
	}
	
	/**
	 * Method that pre-import a contact if he does not exist in database.
	 * @param $rsndonateursweb : the invoice data.
	 */
	function checkContact($rsndonateursweb) {
		$contactData = $this->getContactValues($rsndonateursweb['donInformations']);
		if($this->checkPreImportInCache('Contacts', $contactData['firstname'], $contactData['lastname'], $contactData['email']))
			return true;
		
		$id = $this->getContactId($contactData['firstname'], $contactData['lastname'], $contactData['email']);
		$this->setPreImportInCache($id, 'Contacts', $contactData['firstname'], $contactData['lastname'], $contactData['email']);
		
		if(!$id){
			$this->preImportContact($contactData);
		}
	}
	
	/**
	 * Method to parse the uploaded file and save data to the temporary pre-import table.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - true if pre-import is ended successfully
	 */
	function parseAndSaveFile(RSNImportSources_FileReader_Reader $fileReader) {
		$this->clearPreImportTable();
		
		if($fileReader->open()) {
			if ($this->moveCursorToNextRSNDonateursWeb($fileReader)) {
				do {
					$rsndonateursweb = $this->getNextRSNDonateursWeb($fileReader);
					if ($rsndonateursweb != null) {
						$this->checkContact($rsndonateursweb);
						$this->preImportRSNDonateursWeb($rsndonateursweb);
					}
				} while ($rsndonateursweb != null);

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
		return preg_match("/^(20)?[0-9][0-9][-\/][0-3][0-9][-\/][0-3][0-9]/", $string);//only true for french format
	}
	/**
	 * Method that returns a formatted date for mysql (Y-m-d).
	 * @param string $string : the string to format.
	 * @return string - formated date.
	 */
	function getMySQLDate($string) {
		return substr($string, 0, 10);
		//$dateArray = preg_split('/[-\/]/', $string);
		//return $dateArray[2] . '-' . $dateArray[1] . '-' . $dateArray[0];
	}
	/**
	 * Method that returns a formatted date for mysql (Y-m-d) from ABO FIN column.
	 * @param string $string : the string to format.
	 * @return string - formated date.
	 */
	function getMySQLDateAboEnd($string) {
		if(!$string)
			return '';
		return '20' . substr($string, 0,2) . '-' . substr($string, 2, 2) . '-01';
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
	function moveCursorToNextRSNDonateursWeb(RSNImportSources_FileReader_Reader $fileReader) {
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
	function getNextRSNDonateursWeb(RSNImportSources_FileReader_Reader $fileReader) {
		$nextLine = $fileReader->readNextDataLine($fileReader);
		if ($nextLine != false) {
			$rsndonateursweb = array(
				'donInformations' => $nextLine,
				'detail' => array());
			do {
				$cursorPosition = $fileReader->getCurentCursorPosition();
				$nextLine = $fileReader->readNextDataLine($fileReader);

				if (!$this->isRecordHeaderInformationLine($nextLine)) {
					if ($nextLine[1] != null && $nextLine[1] != '') {
						array_push($rsndonateursweb['detail'], $nextLine);
					}
				} else {
					break;
				}

			} while ($nextLine != false);

			if ($nextLine != false) {
				$fileReader->moveCursorTo($cursorPosition);
			}

			return $rsndonateursweb;
		}

		return null;
	}

	/**
	 * Method that return the formated information of a contact found in the file.
	 * @param $rsndonateurswebInformations : the invoice informations data found in the file.
	 * @return array : the formated data of the contact.
	 */
	function getContactValues($rsndonateurswebInformations) {
		if(preg_match('/^0?6/', $rsndonateurswebInformations[9]))
			$mobile = $rsndonateurswebInformations[9];
		else
			$phone = $rsndonateurswebInformations[9];
			
		$country = $rsndonateurswebInformations[7];
		
		$prenom = $rsndonateurswebInformations[1];
		if($prenom == strtoupper($prenom))
			$prenom = ucfirst( mb_strtolower($prenom) );
		else
			$prenom = ucfirst( $prenom );
		
					
		$contactMapping = array(
			'lastname'		=> mb_strtoupper($rsndonateurswebInformations[2]),
			'firstname'		=> $prenom,
			'email'			=> $rsndonateurswebInformations[8],
			'mailingstreet'		=> $rsndonateurswebInformations[3],
			'mailingstreet3'	=> $rsndonateurswebInformations[4],
			'mailingzip'		=> $rsndonateurswebInformations[5],
			'mailingcity'		=> mb_strtoupper($rsndonateurswebInformations[6]),
			'mailingcountry' 	=> $country == 'France' ? '' : $country,
			'phone'			=> isset($phone) ? $phone : '',
			'mobile'		=> isset($mobile) ? $mobile : '',
			'accounttype'		=> 'Donateur Web',
			'leadsource'		=> $this->getContactOrigine($rsndonateurswebInformations),
		);
		return $contactMapping;
	}

	/**
	 * Retourne l'origine du contact suivant le mode de paiement
	 */
	function getContactOrigine($data){
		$col_modepaiement = 21;
		$col_frequency = 13;
		switch($data[$col_modepaiement]){				
			case 'cb':
				if($data[$col_frequency]=='1')
					return "DON_ETALE";
				return 'PayBox';
				
			case 'paypal':
				return 'PayPal';
				
			case 'cheque':
			case 'virement':
			case 'prelevement':
			default:
				return 'Donateur web';
		}
	}
	
	/**
	 * Method that return the formated information of an invoice found in the file.
	 * @param $rsndonateursweb : the invoice data found in the file.
	 * @return array : the formated data of the invoice.
	 */
	function getRSNDonateursWebValues($rsndonateursweb) {
	//TODO end implementation of this method
		$rsndonateurswebValues = array();
		$date = $this->getMySQLDate($rsndonateursweb['donInformations'][11]);
		$dateEndAbo = $this->getMySQLDateAboEnd($rsndonateursweb['donInformations'][16]);
		$country = $rsndonateursweb['donInformations'][7];
		$prenom = $rsndonateursweb['donInformations'][1];
		if($prenom == strtoupper($prenom))
			$prenom = ucfirst( mb_strtolower($prenom) );
		else
			$prenom = ucfirst( $prenom );
			
		$rsndonateurswebHeader = array(
			'externalid'		=> $rsndonateursweb['donInformations'][0],
			'lastname'		=> mb_strtoupper($rsndonateursweb['donInformations'][2]),
			'firstname'		=> $prenom,
			'email'			=> $rsndonateursweb['donInformations'][8],
			'address'		=> $rsndonateursweb['donInformations'][3],
			'address3'		=> $rsndonateursweb['donInformations'][4],
			'zip'			=> $rsndonateursweb['donInformations'][5],
			'city'			=> mb_strtoupper($rsndonateursweb['donInformations'][6]),
			'country' 		=> $country == 'France' ? '' : $country,
			'datedon'		=> $date,
			//'contactid',
			//'relatedmoduleid',
			'phone' 		=> $rsndonateursweb['donInformations'][9],
			'frequency' 		=> $rsndonateursweb['donInformations'][13],
			'paiementid' 		=> $rsndonateursweb['donInformations'][14],
			'abocode' 		=> $rsndonateursweb['donInformations'][15],
			'dateaboend' 		=> $dateEndAbo,
			'paiementerror' 	=> $rsndonateursweb['donInformations'][20],
			'modepaiement' 		=> $rsndonateursweb['donInformations'][21],
			'amount' 		=> $rsndonateursweb['donInformations'][12],
			'recu' 			=> $rsndonateursweb['donInformations'][17],
			'revue' 		=> $rsndonateursweb['donInformations'][18],
			'clicksource' 		=> $rsndonateursweb['donInformations'][22],
			'listesn' 		=> $rsndonateursweb['donInformations'][23],
			//'assigned_user_id',
			//'createdtime',
			//'modifiedtime',
			'autorisation' 		=> $rsndonateursweb['donInformations'][19],
			'ipaddress' 		=> $rsndonateursweb['donInformations'][10],
			'isvalid' 		=> 1,
		);
		if($rsndonateurswebHeader['modepaiement'] == 'cb'
		&& $rsndonateurswebHeader['paiementerror'] != '0')
			$rsndonateurswebHeader['isvalid'] = 0;
		return $rsndonateurswebHeader;
	}
	
	function getClickSource($clickSource){
		if ($clickSource == "ACAR 30")
			return "ACAR+30";
		return $clickSource;
	}
}