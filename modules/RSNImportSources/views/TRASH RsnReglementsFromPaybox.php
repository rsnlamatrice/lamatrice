<?php


//TODO : end the implementation of this import!
class RSNImportSources_ImportRsnReglementsFromPaybox_View extends RSNImportSources_ImportFromFile_View {

	private $coupon = null;

	/**
	 * Method to get the source import label to display.
	 * @return string - The label.
	 */
	public function getSource() {
		return 'LBL_PAYBOX';
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
		return array('RsnReglements');
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
		return 'ISO-8859-1';
	}

	/**
	 * Method to get the imported fields for the invoice module.
	 * @return array - the imported fields for the invoice module.
	 */
	function getRsnReglementsFields() {
		return array(
			//header
			'	',
			'dateregl',
			'dateoperation',
			'email',
			'amount',
			'currency',
			'payment',
			'paymentstatus',
			'ip',
			'errorcode',
			'bank',
			'typeregl',
			'rsnmoderegl',
		);
	}

	/**
	 * Method to process to the import of the RSNReglements module.
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importRsnReglements($importDataController) {
		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($this->user, 'RsnReglements');
		$sql = 'SELECT * FROM ' . $tableName . ' WHERE status = '. RSNImportSources_Data_Action::$IMPORT_RECORD_NONE . ' ORDER BY numpiece';

		$result = $adb->query($sql);
		$numberOfRecords = $adb->num_rows($result);

		if ($numberOfRecords <= 0) {
			return;
		}

		$row = $adb->raw_query_result_rowdata($result, 0);
		$previousReglementSubjet = $row['numpiece'];//tmp subject, use invoice_no ???
		$reglementData = array($row);

		for ($i = 1; $i < $numberOfRecords; ++$i) {
			$row = $adb->raw_query_result_rowdata($result, $i);
			$reglementSubject = $row['numpiece'];

			if ($previousReglementSubjet == $reglementSubject) {
				array_push($reglementData, $row);
			} else {
				$this->importOneRsnReglements($reglementData, $importDataController);
				$reglementData = array($row);
				$previousReglementSubjet = $reglementSubject;
			}
		}

		$this->importOneRsnReglements($reglementData, $importDataController);
	}

	
	/**
	 * Method to process to the import of a one invoice.
	 * @param $reglementData : the data of the invoice to import
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importOneRsnReglements($reglementData, $importDataController) {
					
		global $log;
		
		//TODO check sizeof $reglementata
		$invoice = $this->getInvoice($reglementData[0]);
		if ($invoice != null) {
			$accountId = $invoice->get('account_id');

			if ($accountId != null) {
				$numpiece = $reglementData[0]['numpiece'];
		
				$query = "SELECT crmid, rsnreglementsid
					FROM vtiger_rsnreglements
					JOIN vtiger_crmentity
					    ON vtiger_rsnreglements.rsnreglementsid = vtiger_crmentity.crmid
					WHERE numpiece = ? AND deleted = FALSE
					LIMIT 1
				";
				$db = PearDatabase::getInstance();
				$result = $db->pquery($query, array($numpiece));
				if($db->num_rows($result)){
					//already imported !!
					$row = $db->fetch_row($result, 0); 
					$entryId = $this->getEntryId("RsnReglements", $row['crmid']);
					foreach ($reglementData as $reglementLine) {
						$entityInfo = array(
							'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_SKIPPED,
							'id'		=> $entryId
						);
						
						//TODO update all with array
						$importDataController->updateImportStatus($reglementLine[id], $entityInfo);
					}
				}
				else {
					$record = Vtiger_Record_Model::getCleanInstance('RsnReglements');
					$record->set('MODE', 'create');
					$record->set('numpiece', $reglementData[0]['numpiece']);
					$record->set('rsnmoderegl', $reglementData[0]['rsnmoderegl']);
					$record->set('dateregl', $reglementData[0]['dateregl']);
					$record->set('rsnbanque', $reglementData[0]['rsnbanque']);
					$record->set('amount', $reglementData[0]['amount']);
					$record->set('currency_id', $reglementData[0]['currency_id']);
					$record->set('dateoperation', $reglementData[0]['dateoperation']);
					$record->set('accountid', $accountId);
					$record->set('typeregl', $reglementData[0]['typeregl']);
					
					//$db->setDebug(true);
					$record->save();
					$reglementId = $record->getId();

					if(!$reglementId){
						//TODO: manage error
						echo "<pre><code>Impossible d'enregistrer la nouvelle facture</code></pre>";
						foreach ($reglementData as $reglementLine) {
							$entityInfo = array(
								'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
							);
							
							//TODO update all with array
							$importDataController->updateImportStatus($reglementLine[id], $entityInfo);
						}

						return false;
					}
					
					$this->importRsnReglementsOfInvoice($invoice, $record, $reglementData);
					
					$record->set('mode','edit');
					return $record;//tmp 
				}
			} else {
				//TODO: manage error
				echo "<pre><code>Unable to find Account</code></pre>";
			}
		} else {
			foreach ($reglementData as $reglementLine) {//TODO: remove duplicated code
				$entityInfo = array(
					'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
				);
				
				$importDataController->updateImportStatus($reglementLine[id], $entityInfo);
			}

			return false;
		}

		return true;
	}

	/**
	 * Associe le réglement à la facture
	 * @param $invoice : facture liée
	 * @param $reglement : RsnRegements
	 * @param $data : pre-import data
	 */
	function importRsnReglementsOfInvoice($invoice, $reglement, $data){
		$product = $this->getProduct($reglement, $data);
		
		$qty = 1;
		$listprice = $reglement->get('amount');
		
		$discount_amount = 0;
		$taxName = 'tax1';
		$taxValue = null;
		$incrementOnDel = 0;
		
		$db = PearDatabase::getInstance();
		$query ="INSERT INTO vtiger_inventoryproductrel (id, productid, sequence_no, quantity, listprice, discount_amount, incrementondel, $taxName) VALUES(?,?,?,?,?,?,?,?)";
		$qparams = array($invoice->getId(), $product->getId(), $sequence, $qty, $listprice, $discount_amount, $incrementOnDel, $taxValue);
		//$db->setDebug(true);
		$db->pquery($query, $qparams);
	}
	
	
	/**
	 * Récupère ou génère la facture correspondant au paiement
	 *
	 */
	function getInvoice($reglement){
		$invoiceData = $this->getInvoiceValues($reglement['rsnreglementsInformations']);
		$query = "SELECT invoiceid
			FROM vtiger_invoice
                        JOIN vtiger_crmentity
                            ON vtiger_invoice.invoiceid = vtiger_crmentity.crmid
			WHERE deleted = FALSE
		";
		switch($invoiceData['typeregl']){
		case 'Facture':
			$invoiceData['typeregl'] = 'BOU';
			$query = " AND invoice_no LIKE CONCAT('BOU%-', ?)";
			break;
		case 'DP':
		case 'DR':
			$query = " AND invoice_no = CONCAT(?, ?)";
			break;
		}
		$query = " LIMIT 1";
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, array($invoiceData['sourceid']));
		if($db->num_rows($result)){
			$row = $db->fetch_row($result, 0);
			return Vtiger_Record_Model::getInstanceById($row[0], 'Invoice') ;
		}
		
		if($invoiceData['typeregl'] == 'BOU'){
			//La facture devrait exister depuis l'import Boutique/Prestashop
			return false;
		}
		
		$donateurWeb = $this->getRsnDonateursWeb($reglement);
		
		
		return $this->createNewInvoice($invoiceData, $donateurWeb);
	}
	
	/**
	 * Récupère le donateur web du paiement
	 *
	 */
	function getRsnDonateursWeb($reglement){
		$paiementid = $reglement['rsnreglementsInformations']['numpiece'];
		$query = "SELECT crmid
			FROM vtiger_rsndonateursweb
                        JOIN vtiger_crmentity
                            ON vtiger_rsndonateursweb.rsndonateurswebid = vtiger_crmentity.crmid
			WHERE deleted = FALSE
			AND paiementid = ?
			LIMIT 1";
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, array($paiementid));
		if($db->num_rows($result)){
			$row = $db->fetch_row($result, 0);
			return Vtiger_Record_Model::getInstanceById($row[0], 'RsnDonateursWeb') ;
		}
		
		//L'enregistrement Donateur Web devrait exister depuis l'import Donateurs Web
		return false;
	}
	
	
	
	/**
	 * Génère une nouvelle facture spécifiquement pour l'encaissement d'un don régulier ou périodique
	 *
	 */
	function createNewInvoice($invoiceData, $donateurWeb){
		
		global $log;
		
		//TODO check sizeof $invoiceata
		$accountId = $donateurWeb->get('accountid');

		if ($account != null) {
			$sourceId = $invoiceData[0]['sourceid'];
			
			$record = Vtiger_Record_Model::getCleanInstance('Invoice');
			$record->set('MODE', 'create');
			$record->set('bill_street', $invoiceData[0]['street']);
			$record->set('bill_street2', $invoiceData[0]['street2']);
			$record->set('bill_street3', $invoiceData[0]['street3']);
			$record->set('bill_city', $invoiceData[0]['city']);
			$record->set('bill_code', $invoiceData[0]['zip']);
			$record->set('bill_country', $invoiceData[0]['country']);
			$record->set('subject', $invoiceData[0]['subject']);
			//$record->set('receivedcomments', $srcRow['paiementpropose']);
			//$record->set('description', $srcRow['notes']);
			$record->set('invoicedate', $invoiceData[0]['invoicedate']);
			$record->set('duedate', $invoiceData[0]['invoicedate']);
			$record->set('account_id', $account->getId());
			//$record->set('received', str_replace('.', ',', $srcRow['netht']+$srcRow['nettva']));
			//$record->set('hdnGrandTotal', $srcRow['netht']+$srcRow['nettva']);//TODO non enregistré : à cause de l'absence de ligne ?
			$record->set('typedossier', 'Facture'); //TODO
			$record->set('invoicestatus', 'Approuvée');//TODO
			$record->set('currency_id', CURRENCY_ID);
			$record->set('conversion_rate', CONVERSION_RATE);
			$record->set('hdnTaxType', 'individual');
		    
		    
			$coupon = $this->getCoupon($invoiceData[0]);
			if($coupon != null)
				$record->set('notesid', $coupon->getId());
			/*$campagne = self::findCampagne($srcRow, $coupon);
			if($campagne)
				$record->set('campaign_no', $campagne->getId());*/
			
			//$db->setDebug(true);
			$record->save();
			$invoiceId = $record->getId();

			if(!$invoiceId){
				//TODO: manage error
				echo "<pre><code>Impossible d'enregistrer la nouvelle facture</code></pre>";
				foreach ($invoiceData as $invoiceLine) {
					$entityInfo = array(
						'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
					);
					
					//TODO update all with array
					$importDataController->updateImportStatus($invoiceLine[id], $entityInfo);
				}

				return false;
			}
			
			
			$entryId = $this->getEntryId("Invoice", $invoiceId);
			$sequence = 0;
			$totalAmount = 0.0;
			$totalTax = 0.0;
			foreach ($invoiceData as $invoiceLine) {
				$this->importInvoiceLine($record, $invoiceLine, ++$sequence, $totalAmount, $totalTax);
			}
			
			$record->set('mode','edit');
			//This field is not manage by save()
			$record->set('invoice_no',$sourceId);
			//set invoice_no
			$query = "UPDATE vtiger_invoice
				JOIN vtiger_crmentity
					ON vtiger_crmentity.crmid = vtiger_invoice.invoiceid
				SET invoice_no = ?
				, total = ?
				, subtotal = ?
				, taxtype = ?
				, smownerid = ?
				, createdtime = ?
				, modifiedtime = ?
				WHERE invoiceid = ?
			";
			$total = $totalAmount + $totalTax;
			$result = $db->pquery($query, array($sourceId
							    , $total
							    , $total
							    , 'individual'
							    , ASSIGNEDTO_ALL
							    , $invoiceData[0]['invoicedate']
							    , $invoiceData[0]['invoicedate']
							    , $invoiceId));
			
			$log->debug("" . basename(__FILE__) . " update imported invoice (id=" . $record->getId() . ", sourceId=$sourceId , total=$total, date=" . $invoiceData[0]['invoicedate']
				    . ", result=" . ($result ? " true" : "false"). " )");
			if( ! $result)
				$db->echoError();
				
				
			//raise trigger instead of ->save() whose need invoice rows
			
			$log->debug("BEFORE " . basename(__FILE__) . " raise event handler(" . $record->getId() . ", " . $record->get('mode') . " )");
			//raise event handler
			$record->triggerEvent('vtiger.entity.aftersave');
			$log->debug("AFTER " . basename(__FILE__) . " raise event handler");
			
			return $record;//tmp 
		} else {
			//TODO: manage error
			echo "<pre><code>Unable to find Account</code></pre>";
		}

		return true;
	}
	
	/**
	 * Method that check if a product exist.
	 * @param $product : the product to check.
	 * @return boolean : true if the product exist.
	 */
	function productExist($product) {
		$db = PearDatabase::getInstance();
		$query = 'SELECT productid FROM vtiger_products p JOIN vtiger_crmentity e on p.productid = e.crmid WHERE p.productcode = ? AND e.deleted = FALSE LIMIT 1';
		$result = $db->pquery($query, array($product['productcode']));

		if ($db->num_rows($result) == 1) {
			return true;
		}

		$query = 'SELECT * FROM vtiger_service s JOIN vtiger_crmentity e on s.serviceid = e.crmid WHERE s.productcode = ? AND e.deleted = FALSE LIMIT 1';
		$result = $db->pquery($query, array($product['productcode']));

        return ($db->num_rows($result) == 1);
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
	 *  It adone row in the temporary pre-import table by invoice line.
	 * @param $reglementData : the data of the invoice to import.
	 */
	function preImportRsnReglements($reglementData) {
		$reglementValues = $this->getRsnReglementsValues($reglementData);
		$reglement = new RSNImportSources_Preimport_Model($reglementValues, $this->user, 'RsnReglements');
		$reglement->save();
	}

	/**
	 * Method that retrieve a contact.
	 * @param string $firstname : the firstname of the contact.
	 * @param string $lastname : the lastname of the contact.
	 * @param string $email : the mail of the contact.
	 * @return the row data of the contact | null if the contact is not found.
	 */
	function getContact($firstname, $lastname, $email) {
		$query = "SELECT contactid, deleted
			FROM vtiger_contactdetails
                        JOIN vtiger_crmentity
                            ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
			WHERE deleted = FALSE
			AND ((UPPER(firstname) = UPPER(?)
				AND UPPER(lastname) = UPPER(?))
			    OR UPPER(lastname) = UPPER(CONCAT(?, ' ', ?))
			)
			AND UPPER(email) = UPPER(?)
			LIMIT 1
		";

		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, array($firstname, remove_accent($lastname), remove_accent($lastname), $firstname, $email));

		if($db->num_rows($result)){
			$row = $db->fetch_row($result, 0);
			return Vtiger_Record_Model::getInstanceById($row['contactid'], 'Contacts');
		}

		return null;
	}

	/**
	 * Method that pre-import a contact if he does not exist in database.
	 * @param $reglement : the rsnreglements data.
	 */
	function checkInvoice($reglement) {
		$invoiceData = $this->getInvoiceValues($reglement['rsnreglementsInformations']);
		$query = "SELECT invoiceid, deleted
			FROM vtiger_invoice
                        JOIN vtiger_crmentity
                            ON vtiger_invoice.invoiceid = vtiger_crmentity.crmid
			WHERE deleted = FALSE
		";
		switch($invoiceData['typeregl']){
		case 'Facture':
			$invoiceData['typeregl'] = 'BOU';
			$query = " AND invoice_no LIKE CONCAT('BOU%-', ?)";
			break;
		case 'DP':
		case 'DR':
			$query = " AND invoice_no = CONCAT(?, ?)";
			break;
		}
		$query = " LIMIT 1";
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, array($invoiceData['sourceid']));
		if(!$db->num_rows($result)){
			$this->preImportInvoice($invoiceData);
		}
	}

	/**
	 * Method that return the formated information of an invoice found in the file.
	 * @param $reglement : the reglement data found in the file.
	 * @return array : the formated data of the invoice.
	 */
	function getInvoiceValues($reglement) {
	//TODO end implementation of this method
		$reference = $reglement['rsnreglementsInformations'][12];
		if(!$reference)
			return false;
		$references = explode(array(';','_'), $reference);
		$sourceId = $references[0];
		$date = $this->getMySQLDate($reglement['rsnreglementsInformations'][9]);
		$invoiceType = $this->getInvoiceType($sourceId);
		switch($invoiceType){
		case 'BOU':
			$typeRegl = 'Facture';
			break;
		case 'DP':
			$typeRegl = 'Don régulier';
			$sourceId = substr($sourceId, strlen($invoiceType));
			break;
		case 'DR':
			$typeRegl = 'Don ponctuel';
			$sourceId = substr($sourceId, strlen($invoiceType));
			break;
		}
		$typeRegl = $this->getTypeRegl($invoiceType);
		/* $dWDateDon:=[JournalPaybox]DateOfIssue_4D
		$aWDateDon:=Chaine($dWDateDon)
		$dWDateEcheance:=[JournalPaybox]Date_4D
		$aWDateEcheance:=Chaine($dWDateEcheance)
		C_TEXTE($tPaquetCogilog)
		C_ENTIER LONG($eCompteur)
		$bRefInconnu:=Faux
		$aWMontantBrut:=Chaine(([JournalPaybox]Amount/100))
		C_ALPHA(30;$aCodeProduit)
		$aCodeProduit:="ADETAL"
		$CompteDeVente:="511104"
		$codeAffaire:="PAYBOX"
		$tCommentaire:=$tCommentaire+$aWDateDon+◊aTab+Chaine([Adresse]RefFiche)+◊aTab+$aWMontantBrut+◊aTab
		ENVOYER PAQUET($hDocLog;$tCommentaire)
		$tCommentaire:=""*/
		$invoiceValues = array(
			'sourceid'		=> $sourceId,
			'invoicetype'		=> $invoiceType,//préfixe du n° de facture
			'reference'		=> $reference,
			'invoicedate'		=> $date,
			'typeregl'		=> $typeRegl,
		);

		return $invoiceValues;
	}
	
	/**
	 * Method to parse the uploaded file and save data to the temporary pre-import table.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - true if pre-import is ended successfully
	 */
	function parseAndSaveFile(RSNImportSources_FileReader_Reader $fileReader) {
		$this->clearPreImportTable();

		if($fileReader->open()) {
			if ($this->moveCursorToNextRsnReglements($fileReader)) {
				$i = 0;
				do {
					$reglement = $this->getNextRsnReglements($fileReader);
					if ($reglement != null) {
						$this->preImportRsnReglements($reglement);
					}
				} while ($reglement != null);

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
		return preg_match("/^[0-3][0-9][-\/][0-1][0-9][-\/][0-9]{2,4}$/", $string);//only true for french format
	}
	/**
	 * Method that returns a formatted date for mysql (Y-m-d).
	 * @param string $string : the string to format.
	 * @param string $hour : hours and minutes.
	 * @return string - formated date.
	 */
	function getMySQLDate($string, $hour = FALSE) {
		
		if($hour){
			switch(strlen($hour)){
			case 1:
			case 2:
				$hour = '00:' . $hour;
				break;
			case 3:
				$hour = '0' . $hour[0] . ':' . substr($hour,1);
				break;
			case 4:
				$hour = substr($hour,0,2) . ':' . substr($hour,2);
				break;
			default:
				$hour = '';
				break;
			}
		}
		$dateArray = preg_split('/[-\/]/', $string);
		return $dateArray[2] . '-' . $dateArray[1] . '-' . $dateArray[0]
			. ($hour ? ' ' . $hour : '');
	}

	/**
	 * Method that check if a line of the file is a valide information line.
	 *  It assume that the line is a validated receipt.
	 * @param array $line : the data of the file line.
	 * @return boolean - true if the line is a validated receipt.
	 */
	function isValidInformationLine($line) {
		if (sizeof($line) > 0 && $line[0] != ""
		    && $this->isDate($line[6]) //date
		    && $line[31] == "" //ErrorCode
		    && $line[28] == "Télécollecté" //Status
		    && $line[3] == '1' //Rank
		) {
			return true;
		}

		return false;
	}

	/**
	 * Method that move the cursor of the file reader to the beginning of the next found invoice.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - false if error or if no invoice found.
	 */
	function moveCursorToNextRsnReglements(RSNImportSources_FileReader_Reader $fileReader) {
		do {
			$cursorPosition = $fileReader->getCurentCursorPosition();
			$nextLine = $fileReader->readNextDataLine($fileReader);

			if ($nextLine == false) {
				return false;
			}

		} while(!$this->isValidInformationLine($nextLine));

		$fileReader->moveCursorTo($cursorPosition);

		return true;
	}

	/**
	 * Method that return the information of the next first invoice found in the file.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return the invoice information | null if no invoice found.
	 */
	function getNextRsnReglements(RSNImportSources_FileReader_Reader $fileReader) {
		$nextLine = $fileReader->readNextDataLine($fileReader);
		if ($nextLine != false) {
			$reglement = array('rsnreglementsInformations' => $nextLine);
			do {
				$cursorPosition = $fileReader->getCurentCursorPosition();
				$nextLine = $fileReader->readNextDataLine($fileReader);

				if ($this->isValidInformationLine($nextLine)) 
					break;

			} while ($nextLine != false);

			if ($nextLine != false) {
				$fileReader->moveCursorTo($cursorPosition);
			}

			return $reglement;
		}

		return null;
	}

	/**
	 * Method that return the formated information of an invoice found in the file.
	 * @param $reglement : the invoice data found in the file.
	 * @return array : the formated data of the invoice.
	 */
	function getRsnReglementsValues($reglement) {
	//TODO end implementation of this method
		$date = $this->getMySQLDate($reglement['rsnreglementsInformations'][9], $reglement['rsnreglementsInformations'][10]);
		$dateoperation = $this->getMySQLDate($reglement['rsnreglementsInformations'][6]);
		$currency = $this->getCurrency($reglement['rsnreglementsInformations'][18]);
		$reference = $reglement['rsnreglementsInformations'][12];
		$typeregl = $this->getTypeRegl($reference);
		$numpiece = $reference;
		$reglementValues = array(
			'numpiece'		=> $numpiece,
			'dateregl'		=> $date,
			'dateoperation'		=> $dateoperation,
			'email'			=> $reglement['rsnreglementsInformations'][13],
			'amount'		=> $reglement['rsnreglementsInformations'][17] / 100,
			'currency'		=> $currency,
			'payment'		=> $reglement['rsnreglementsInformations'][23],
			'paymentstatus'		=> $reglement['rsnreglementsInformations'][28],
			'ip'			=> $reglement['rsnreglementsInformations'][30],
			'errorcode'		=> $reglement['rsnreglementsInformations'][31],
			'bank'			=> $reglement['rsnreglementsInformations'][1],
			'typeregl'		=> $typeregl,
			'rsnmoderegl'		=> 'PAYBOX',
		);

		return $reglementValues;
	}
	
	/**
	 * Retourne le type de facture associée : Boutique, Don Régulier, Don Ponctuel
	 * Différencie les paiements DR et DP des autres factures boutiques (qui peuvent commencer par 'dr' (minuscules))
	 */
	function getTypeRegl($reference){
		if($reference[0] == 'D') 
			if($reference[1] == 'R')
				return 'Don régulier';
			elseif($reference[1] == 'P')
				return 'Don ponctuel';
		return 'Facture';
	}
	
	/**
	 *
	 *
	 */
	function getCurrency($currencyCode){
		switch ($currencyCode){
		case '978':	
		case '€':	
			return '€';
		}
		return '€';
	}
}