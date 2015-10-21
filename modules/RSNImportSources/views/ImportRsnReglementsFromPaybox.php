<?php
/**
 * Importation des réglements effectués sur Paybox, c'est à dire la boutique Prestashop
 * Cet import s'effectue après l'importation des Donateurs web (qui fournit les coordonnées des contacts)
 * et après l'import Boutique qui référencent les factures de la boutique.
 * Les écritures DR et DP correspondent aux données RSNDonateursWeb et génèrent des factures de dons, les autres correspondent aux factures boutique.
 * Les factures peuvent ne pas encore exister, l'association réglement/facture doit donc se faire ultérieurement TODO.
 */

class RSNImportSources_ImportRsnReglementsFromPaybox_View extends RSNImportSources_ImportFromFile_View {

	private $reglementOrigine = 'PayBox';

	//private $cancelledReglements = array();

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
		return array('Invoice', 'RsnReglements');
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
			'numpiece',
			'rank',
			'dateregl',
			'dateoperation',
			'email',
			'autorisation',
			'amount',
			'currency_id',
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
		$sql = 'SELECT * FROM ' . $tableName . ' WHERE status = '. RSNImportSources_Data_Action::$IMPORT_RECORD_NONE . ' ORDER BY id';

		$result = $adb->pquery($sql);
		$numberOfRecords = $adb->num_rows($result);

		if ($numberOfRecords <= 0) {
			return;
		}

		$row = $adb->raw_query_result_rowdata($result, 0);
		$previousReglementSubjet = $row['numpiece'];
		$reglementData = array($row);

		for ($i = 1; $i < $numberOfRecords; ++$i) {
			$row = $adb->raw_query_result_rowdata($result, $i);
			$reglementSubject = $row['numpiece'];

			if ($previousReglementSubjet == $reglementSubject && $row['rank'] > 1) {
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
	 * Method to process to the import of a one reglement.
	 * @param $reglementData : the data of the reglement to import
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importOneRsnReglements($reglementData, $importDataController) {
					
		global $log;
		$reglement = $reglementData[0];
		
		$invoice = $this->getInvoice($reglement);
		//La facture n'est pas obligatoire, elle peut être affecté ultérieurement
		if ($invoice != null) {
			$accountId = $invoice->get('account_id');
		} else {
			$accountId = 0;
		}
		$numpiece = $reglement['numpiece'];

		//Déjà importée (au pré-import on a mis à jour massivement ceux connus, il s'agit donc ici d'un test en trop, sauf à considérer un import entre temps)
		$query = "SELECT crmid, rsnreglementsid
			FROM vtiger_rsnreglements
			JOIN vtiger_crmentity
				ON vtiger_rsnreglements.rsnreglementsid = vtiger_crmentity.crmid
			WHERE numpiece = ?
			AND (vtiger_rsnreglements.error = 0 AND `$tableName`.autorisation = 'Autorisation'
				OR vtiger_rsnreglements.error > 0 AND `$tableName`.autorisation != 'Autorisation'
			)
			AND deleted = FALSE
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
			$record->set('mode', 'create');
			$record->set('numpiece', $reglement['numpiece']);
			$record->set('rsnmoderegl', $reglement['rsnmoderegl']);
			$record->set('dateregl', $reglement['dateregl']);
			$record->set('rsnbanque', $reglement['rsnbanque']);
			$record->set('amount', str_replace('.', ',', $reglement['amount']));
			$record->set('currency_id', $reglement['currency_id']);
			$record->set('dateoperation', $reglement['dateoperation']);
			$record->set('account_id', $accountId);
			$record->set('typeregl', $reglement['typeregl']);
			$record->set('contactname', $reglement['email']);
			$record->set('origine', $this->reglementOrigine);
			if($reglement['autorisation'] === 'Autorisation'){
				$record->set('error', 0);
			}
			else{
				$record->set('error', 1);
				$record->set('errormsg', $reglement['paymentstatus'] . ' (' . $reglement['errorcode'] . ')');
			}
			
			//$db->setDebug(true);
			$record->save();
			$reglementId = $record->getId();

			if(!$reglementId){
				//TODO: manage error
				echo "<pre><code>Impossible d'enregistrer le nouveau réglement</code></pre>";
				foreach ($reglementData as $reglementLine) {
					$entityInfo = array(
						'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
					);
					
					//TODO update all with array
					$importDataController->updateImportStatus($reglementLine[id], $entityInfo);
				}

				return false;
			}
			
			$record->set('mode','edit');
			
			//Relation Facture/Réglement
			if($invoice)
				$this->addInvoiceReglementRelation($record, $invoice);
			
			$entryId = $this->getEntryId("RsnReglements", $reglementId);
			foreach ($reglementData as $reglementLine) {
				$entityInfo = array(
					'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_CREATED,
					'id'		=> $entryId
				);
				
				//TODO update all with array
				$importDataController->updateImportStatus($reglementLine[id], $entityInfo);
			}
			
			return $record;//tmp 
		}
		return true;
	}
	
	/**
	 * Associe le réglement à la facture.
	 * Met à jour la facture
	 */
	function addInvoiceReglementRelation($reglement, $invoice){
		global $adb;
		$query = 'INSERT INTO vtiger_crmentityrel (crmid, module, relcrmid, relmodule)
			VALUES ( ?, \'RsnReglements\', ?, \'Invoice\')';
		$params = array($reglement->getId(), $invoice->getId());
		$result = $adb->pquery($query, $params);
		if(!$result) {//duplicate
			$adb->echoError();
			return false;
		}
		
		//mise à jour de la facture
		$query = 'UPDATE vtiger_invoice
			SET received = IFNULL(received,0) + ?
			, balance = received - total
			, invoicestatus = IF(ABS(balance) < 0.01, \'Paid\', invoicestatus)
			WHERE invoiceid = ?';
			
		$amount = self::str_to_float($reglement->get('amount'));
		
		$params = array(
			  $amount
			, $invoice->getId()
		);
		
		$result = $adb->pquery($query, $params);
		
		if(!$result) {
			var_dump(/*$query,*/ $params);
			$adb->echoError();
			return false;
		}
		
		//mise à jour de la facture
		$query = 'UPDATE vtiger_invoicecf
			SET receivedreference = IF(receivedreference IS NULL OR receivedreference = \'\', ?, CONCAT(receivedreference, \', \', ?))
			, receivedcomments = IF(receivedcomments IS NULL OR receivedcomments = \'\', ?, CONCAT(receivedcomments, \'\\n\', ?))
			, receivedmoderegl = IF(receivedmoderegl IS NULL OR receivedmoderegl = \'\', ?, CONCAT(receivedmoderegl, \', \', ?))
			WHERE invoiceid = ?';
		
		$params = array(
			$reglement->get('numpiece'), $reglement->get('numpiece')
			, 'Import PayBox', 'Import PayBox'
			, $reglement->get('rsnmoderegl'), $reglement->get('rsnmoderegl')
			, $invoice->getId()
		);
		
		$result = $adb->pquery($query, $params);
		
		if(!$result) {
			var_dump(/*$query,*/ $params);
			$adb->echoError();
			return false;
		}
		return true;
	}
	
	
	/**
	 * Method that returns the invoice associated with data.
	 * @param $reglement : the rsnreglements data.
	 * @return invoice record
	 */
	function getInvoice($reglement) {
		$invoiceData = $this->getInvoiceValues($reglement);
		$query = "SELECT crmid
			FROM vtiger_invoice
                        JOIN vtiger_crmentity
                            ON vtiger_invoice.invoiceid = vtiger_crmentity.crmid
			WHERE deleted = FALSE
		";
		switch($invoiceData['invoicetype']){
		case 'BOU':
			$query .= " AND invoice_no LIKE CONCAT('BOU%-', ?)";
			$queryParams = array($invoiceData['sourceid']);
			break;
		case 'DP':
		case 'DR':
			$query .= " AND invoice_no = ?";
			$queryParams = array($invoiceData['sourceid']);
			break;
		default:
			var_dump('$reglement : ', $reglement);
			var_dump('$invoiceData : ', $invoiceData);
			echo '<pre>Erreur : Type de facture inconnu "'.$invoiceData['invoicetype'].'"</pre>';
			return false;
			break;
		}
		$query .= " LIMIT 1";
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, $queryParams);
		if($db->num_rows($result)){
			$row = $db->fetch_row($result, 0);
			return Vtiger_Record_Model::getInstanceById($row['crmid'], 'Invoice');
		}
	}
	
//	/**
//	 * Method that pre-import an invoice if it does not exist in database.
//	 * @param $reglement : the rsnreglements data.
//	 * @return invoiceid if exists or true if pre-import
//	 */
//	function checkInvoice($reglement) {
//		$invoiceData = $this->getInvoiceValues($reglement);
//		$query = "SELECT crmid
//			FROM vtiger_invoice
//                        JOIN vtiger_crmentity
//                            ON vtiger_invoice.invoiceid = vtiger_crmentity.crmid
//			WHERE deleted = FALSE
//		";
//		switch($invoiceData['invoicetype']){
//		case 'BOU':
//			$query .= " AND invoice_no LIKE CONCAT('BOU%-', ?)";
//			$queryParams = array($invoiceData['sourceid']);
//			break;
//		case 'DP':
//		case 'DR':
//			$query .= " AND invoice_no = ?";
//			$queryParams = array($invoiceData['sourceid']);
//			break;
//		default:
//			var_dump('$reglement : ', $reglement);
//			var_dump('$invoiceData : ', $invoiceData);
//			echo '<pre>Erreur : Type de facture inconnu "'.$invoiceData['invoicetype'].'"</pre>';
//			return false;
//			break;
//		}
//		$query .= " LIMIT 1";
//		$db = PearDatabase::getInstance();
//		$result = $db->pquery($query, $queryParams);
//		if(!$db->num_rows($result)){
//			if($invoiceData['invoicetype'] == 'BOU'){
//				var_dump('$query : ', $query, $queryParams);
//				var_dump('$reglement : ', $reglement);
//				var_dump('$invoiceData : ', $invoiceData);
//				echo '<pre>Erreur : La facture Boutique est introuvable pour la référence "'.$invoiceData['sourceid'].'" ('.$reglement['email'].', '.$reglement['dateoperation'].')</pre>';
//				return false;
//			}
//			$donateurWeb = $this->getDonateurWeb($reglement);
//			
//			if(!$donateurWeb){
//				//var_dump('$query : ', $query, $queryParams);
//				var_dump('$reglement : ', $reglement);
//				var_dump('$invoiceData : ', $invoiceData);
//				//echo_callstack();
//				echo '<pre>Erreur : L\'enregistrement Donateur Web est introuvable pour la référence "'.$invoiceData['sourceid'].'" ('.$reglement['email'].', '.$reglement['dateoperation'].')</pre>';
//				return false;
//			}
//			$this->preImportInvoice($invoiceData, $donateurWeb);
//			return true;
//		}
//		$row = $db->fetch_row($result, 0);
//			
//		return $row['crmid'];
//	}
	
	/**
	 * Method that check if there is currencies found in file exist.
	 *  If there is some new currencies found, it ended the process and display the "new currency found" template
	 * @param RSNImportSources_FileReader_Reader $fileReader : the reader of the uploaded file.
	 * @return boolean : true if there is no new product.
	 */
	function checkCurrencies(RSNImportSources_FileReader_Reader $fileReader) {
		$newCurrencies = array();

		if($fileReader->open()) {
			if ($this->moveCursorToNextRsnReglement($fileReader)) {
				$i = 0;
				do {
					$reglement = $this->getNextRsnReglement($fileReader);
					if ($reglement != null) {
						$currency = $this->getCurrency($reglement);
						if (!$this->getCurrencyId($currency)) {
							array_push($newCurrencies, $currency);
						} 
					}
				} while ($reglement != null);
			}

			$fileReader->close();

			if (sizeof($newProducts) > 0) {
				global $HELPDESK_SUPPORT_NAME;
				$viewer = new Vtiger_Viewer();
				$viewer->assign('FOR_MODULE', 'Invoice');
				$viewer->assign('MODULE', 'RSNImportSources');
				$viewer->assign('NEW_CURRENCIES', $newCurrencies);
				$viewer->assign('HELPDESK_SUPPORT_NAME', $HELPDESK_SUPPORT_NAME);
				$viewer->view('NewCurrenciesFound.tpl', 'RSNImportSources');

				exit;//TODO: Be carefull: in this case, JS is not loaded !!!
			}
		} else {
			//TODO: manage error
			echo "<code>le fichier n'a pas pu être ouvert...</code>";
		}

		return true;
	}

	/**
	 * Method to parse the uploaded file and save data to the temporary pre-import table.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - true if pre-import is ended successfully
	 */
	function parseAndSaveFile(RSNImportSources_FileReader_Reader $fileReader) {
		if ($this->checkCurrencies($fileReader)) {
			$this->clearPreImportTable();
			
			$this->initCancelledReglements($fileReader);

			if($fileReader->open()) {
				if ($this->moveCursorToNextRsnReglement($fileReader)) {
					$i = 0;
					do {
						$reglement = $this->getNextRsnReglement($fileReader);
						if ($reglement != null){
							$reglement = $this->getRsnReglementsValues($reglement);
							//if( $reglement['autorisation'] == 'Autorisation'){ on enregistre tout, avec les champs error et errormsg
								//if(!$this->checkInvoice($reglement)){ Les factures ne sont plus obligatoires
									//if(array_key_exists($reglement['numpiece'], $cancelledReglements))
									//	continue;
								/*	$error = 'LBL_INVOICE_MISSING';
									echo 'Facture ou Donateur web manquant.<br>Les importations "Donateurs Web" et "Boutique" ont-elles bien été effectuées ?';
									break;
								}*/
								$this->preImportRsnReglement($reglement);
							//}
						}
					} while ($reglement != null);

				}

				$fileReader->close(); 
				return !isset($error);
			} else {
				//TODO: manage error
				echo "<code>le fichier n'a pas pu être ouvert...</code>";
			}
		}
		return false;
	}
	
	/**
	 * Référencement des écritures d'annulation
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - true if pre-import is ended successfully
	 */
	function initCancelledReglements(RSNImportSources_FileReader_Reader $fileReader) {
		if($fileReader->open()) {
			if ($this->moveCursorToNextRsnReglement($fileReader)) {
				$i = 0;
				do {
					$reglement = $this->getNextRsnReglement($fileReader);
					if ($reglement != null) {
						$reglement = $this->getRsnReglementsValues($reglement);
						if($reglement['autorisation'] != 'Autorisation'){
							$cancelledReglements[$reglement['numpiece']] = $reglement;
						}
					}
				} while ($reglement != null);

			}

			$fileReader->close(); 
			return !isset($error);
		} else {
			//TODO: manage error
			echo "<code>le fichier n'a pas pu être ouvert...</code>";
		}
	}
	
	/**
	 * Method that pre import an RsnReglements.
	 *  It adone row in the temporary pre-import table by RsnReglements line.
	 * @param $reglementData : the data of the invoice to import.
	 */
	function preImportRsnReglement($reglementData) {
		if(isset($reglementData['numpiece']))
			$reglementValues = $reglementData;
		else
			$reglementValues = $this->getRsnReglementsValues($reglementData);
		$reglement = new RSNImportSources_Preimport_Model($reglementValues, $this->user, 'RsnReglements');
		$reglement->save();
	}
	/**
	 * Method that return the coupon for prestashop source.
	 *  It cache the value in the $this->coupon attribute.
	 * @return the coupon.
	 */
	protected function getCoupon($reglement){
		$typeregl = $this->getInvoiceType($reglement['reference']);
		switch($typeregl){
		 case 'DR':
			$codeAffaire='PAYBOX';
			break;
		 case 'DP':
			$codeAffaire='PAYBOXR';
			break;
		 default:
			$codeAffaire='BOUTIQUE';
			break;
		}
		return parent::getCoupon($codeAffaire);
	}
	/**
	 * Method called after the file is processed.
	 *  This method must be overload in the child class.
	 */
	function postPreImportData() {
		$db = PearDatabase::getInstance();
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'RsnReglements');

		RSNImportSources_Utils_Helper::clearDuplicatesInTable($tableName, array('numpiece', 'autorisation'));
		
		/* Affecte l'id du règlement */
		$query = "UPDATE $tableName
		JOIN  vtiger_rsnreglements
			ON  vtiger_rsnreglements.numpiece = `$tableName`.numpiece
			AND (vtiger_rsnreglements.error = 0 AND `$tableName`.autorisation = 'Autorisation'
				OR vtiger_rsnreglements.error > 0 AND `$tableName`.autorisation != 'Autorisation'
			)
		JOIN vtiger_crmentity
			ON vtiger_rsnreglements.rsnreglementsid = vtiger_crmentity.crmid
		";
		$query .= " SET `recordid` = vtiger_crmentity.crmid
		, `$tableName`.status = ?";
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
		
		return true;
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
		if (sizeof($line) > 0 && is_numeric($line[2])
		    && $this->isDate($line[9]) //date
		    && $line[3] == 1 //Rank
		) {
			return true;
		}
		return false;
	}

	/**
	 * Method that move the cursor of the file reader to the beginning of the next found RsnReglements.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - false if error or if no invoice found.
	 */
	function moveCursorToNextRsnReglement(RSNImportSources_FileReader_Reader $fileReader) {
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
	 * Method that return the information of the next first reglement found in the file.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return the reglement information | null if no reglement found.
	 */
	function getNextRsnReglement(RSNImportSources_FileReader_Reader $fileReader) {
		$nextLine = $fileReader->readNextDataLine($fileReader);
		if ($nextLine != false) {
			$reglement = $nextLine;
			do {
				$cursorPosition = $fileReader->getCurentCursorPosition();
				$nextLine = $fileReader->readNextDataLine($fileReader);

				if ($this->isValidInformationLine($nextLine)) {
					break;
				}

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
	 * @param $invoice : the invoice data found in the file.
	 * @param $donateurWeb : donateur web avec la même référence
	 * @return array : the formated data of the invoice.
	 */
	function getInvoiceValues($reglement, $invoiceValues = FALSE, $donateurWeb = FALSE) {
		if(!$invoiceValues){
			$reference = $reglement['numpiece'];//DR05707_20150225130243 ou 4297;CP;25-04-15-17:38:33
			if(!$reference)
				return false;
			if(strpos($reference, ';') !== FALSE)
				$referenceParts = explode( ';', $reference);
			else
				$referenceParts = explode( '_', $reference);
			$sourceId = $referenceParts[0];
			$date = $reglement['dateregl'];
			$invoiceType = $this->getInvoiceType($sourceId);
			switch($invoiceType){
			case 'BOU':
				//4297;CP;25-04-15-17:38:33
				break;
			case 'DP':
				//DP05707_20150225130243
				$product = array(
					'code' => 'ADLB',
					'name' => 'Don ponctuel',
				);
				break;
			case 'DR':
				//DR05707
				$product = array(
					'code' => 'ADETAL',
					'name' => 'Don régulier',
				);
				break;
			}
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
				'invoicetype'		=> $invoiceType,
				'subject'		=> $reference,
				'invoicedate'		=> $date,
			);
		}
		
		if($donateurWeb){
			foreach(array('lastname', 'firstname', 'email', 'street', 'street2', 'street3', 'zip', 'city', 'country') as $fieldName)
				$invoiceValues[$fieldName] = decode_html($donateurWeb->get($fieldName));
		}
		if(isset($product) && $reglement)
			$invoiceValues = array_merge($invoiceValues, array(
				'productcode'	=> $product['code'],
				'productid'	=> $this->getProductId($product['code'], $isProduct),
				'quantity'	=> 1,
				'article'	=> $product['name'],
				'prix_unit_ht'	=> $reglement['amount'],
				'isproduct'	=> false,
				'taxrate'	=> 0.0,
			));

		return $invoiceValues;
	}


	/**
	 * Method that return the formated information of an RsnReglements found in the file.
	 * @param $reglement : the invoice data found in the file.
	 * @return array : the formated data of the invoice.
	 */
	function getRsnReglementsValues($reglement) {
	//TODO end implementation of this method
		$date = $this->getMySQLDate($reglement[9], $reglement[10]);
		if($reglement[6])
			$dateoperation = $this->getMySQLDate($reglement[6]);
		else
			$dateoperation = $date;
		$currency = $this->getCurrency($reglement);
		$currencyId = $this->getCurrencyId($currency);
		$reference = $reglement[12];
		$typeregl = $this->getTypeRegl($reference);
		$numpiece = $reference;
		$rank = (int)$reglement[3];
		$reglementValues = array(
			'numpiece'		=> $numpiece,
			'rank'			=> $rank,
			'dateregl'		=> $date,
			'dateoperation'		=> $dateoperation,
			'email'			=> $reglement[13],
			'autorisation'		=> $reglement[14],
			'amount'		=> self::str_to_float($reglement[17]) / 100,
			'currency_id'		=> $currencyId,
			'payment'		=> $reglement[23],
			'paymentstatus'		=> $reglement[28],
			'ip'			=> $reglement[30],
			'errorcode'		=> $reglement[31],
			'bank'			=> $reglement[1],
			'typeregl'		=> $typeregl,
			'rsnmoderegl'		=> 'PayBox',
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
		return 'Boutique';
	}
	
	
	/**
	 * Retourne le type de facture associée : BOU, DR, DP
	 * Différencie les paiements DR et DP des autres factures boutiques (qui peuvent commencer par 'dr' (minuscules))
	 */
	function getInvoiceType($reference){
		if($reference[0] == 'D') 
			if($reference[1] == 'R')
				return 'DR';
			elseif($reference[1] == 'P')
				return 'DP';
		return 'BOU';
	}
	
	/**
	 *
	 *
	 */
	function getCurrency($reglement){
		$currencyCode = $reglement[18];
		switch ($currencyCode){
		case '978':
			return 'Euro';
		}
		return $currencyCode;
	}
	
	/**
	 *
	 *
	 */
	function getCurrencyId($currency){
		if(!$currency)
			return false;
		if($currency == 'Euro')
			return CURRENCY_ID;
		//TODO
		return 0;
	}
	
//	/**
//	 *
//	 *
//	 */
//	function getDonateurWeb($reglement){
//		$reference = $reglement['numpiece'];//DR05707_20150225130243 ou 4297;CP;25-04-15-17:38:33
//		if(!$reference)
//			return false;
//		
//		$query = "SELECT crmid
//			FROM vtiger_rsndonateursweb
//                        JOIN vtiger_crmentity
//                            ON vtiger_rsndonateursweb.rsndonateurswebid = vtiger_crmentity.crmid
//			WHERE deleted = FALSE
//			AND paiementid = ?
//			AND isvalid = 1 /* TODO existing 0 should raise error */
//			LIMIT 1
//		";
//		$db = PearDatabase::getInstance();
//		$result = $db->pquery($query, array($reference));
//		if(!$db->num_rows($result)){
//			//var_dump('getDonateurWeb',$query, array($reference));
//			return false;
//		}
//		$row = $db->fetch_row($result, 0);
//		return Vtiger_Record_Model::getInstanceById( $row['crmid'], 'RSNDonateursWeb');
//	}
	
}

?>