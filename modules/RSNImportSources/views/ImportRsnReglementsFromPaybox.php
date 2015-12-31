<?php
/**
 * Importation des réglements effectués sur Paybox, c'est à dire la boutique Prestashop, mais aussi des paiements de dons réguliers ou ponctuels
 * Cet import s'effectue après l'importation des Donateurs web (qui fournit les coordonnées des contacts)
 * et après l'import Boutique qui référencent les factures de la boutique, mais pas forcément.
 * Les écritures DR et DP correspondent aux données RSNDonateursWeb et génèrent des factures de dons, les autres correspondent aux factures Boutique/Prestashop.
 * Les factures peuvent ne pas encore exister, l'association réglement/facture doit donc se faire ultérieurement.
 *
 * Il faut bien distinguer les paiements PayBox en DP ou DR et les autres, en règlement de factures Boutique.
 * Les DP et DR génèrent une facture.
 * Les factures Boutique peuvent être importées après les règlements.
 * Les Donateurs Web doivent pré-exister.
 *
 * parseAndSaveFile()
  	checkCurrencies()
	initCancelledReglements()
	preImportRsnReglement()
	preImportInvoice()
 * postPreImportData()
	postPreImportRsnReglementsData()
	postPreImportInvoiceData()
	checkPreImportedRecords()
 * importInvoice
  	each importOneInvoice
  		importInvoiceLine
 * importRsnReglements
   beforeImportRsnReglements
  	each importOneRsnReglements
		addInvoiceReglementRelation
 */

class RSNImportSources_ImportRsnReglementsFromPaybox_View extends RSNImportSources_ImportFromFile_View {

	private $reglementOrigine = 'PayBox';
	
	public $sourceid_prefix = 'PBOX';

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
	function getInvoiceFields() {
		return array(
			//header
			'transactionid',
			'refdonateursweb',
			'importsourceid',
			'num_ligne',//n° de ligne (0 pour l'en-tête). Sert à détecter les doublons de factures quand on importe plusieurs fichiers à la fois
			'contact_no',
			'firstname',
			'lastname',
			'street',
			'street2',
			'street3',
			'pobox',
			'zip',
			'city',
			'country',
			'subject',
			'invoicedate',
			'typedossier',
			'invoicetype',
			'receivedmoderegl',
			
			//lines
			'productcode',
			'productid',
			'productname',
			'isproduct',
			'quantity',
			'prix_unit_ht',
			'taxrate',
			
			'_invoiceid',
			'_contactid',
			'_accountid',
			'_rsndonateurswebid',
			'_rsnreglementsid',
		);
	}	
	
	/**
	 * Method to get the imported fields for the RsnReglements module.
	 * @return array - the imported fields for the RsnReglements module.
	 */
	function getRsnReglementsFields() {
		return array(
			//header
			'transactionid',
			'numpiece',
			'refboutique',
			'importsourceid',
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
			
			'_invoiceid',
			'_contactid',
			'_rsndonateurswebid',
		);
	}	
	
	
	/**
	 * Method to process to the import of the invoice module.
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importInvoice($importDataController) {

		if($this->needValidatingStep()){
			$this->skipNextScheduledImports = true;
			$this->keepScheduledImport = true;
			return;
		}
		
		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($this->user, 'Invoice');
		$sql = 'SELECT * FROM ' . $tableName . ' WHERE status = '. RSNImportSources_Data_Action::$IMPORT_RECORD_NONE . ' ORDER BY id';

		$result = $adb->query($sql);
		$numberOfRecords = $adb->num_rows($result);

		if ($numberOfRecords <= 0) {
			return;
		}

		$row = $adb->raw_query_result_rowdata($result, 0);
		$invoiceData = array($row);

		for ($i = 1; $i < $numberOfRecords; ++$i) {
			$row = $adb->raw_query_result_rowdata($result, $i);
			if ($row['num_ligne'] > 0) {
				array_push($invoiceData, $row);
			} else {
				//previous
				$this->importOneInvoice($invoiceData, $importDataController);
				$invoiceData = array($row);
			}
		}
		//previous
		$this->importOneInvoice($invoiceData, $importDataController);
	}

	/**
	 * Method to process to the import a line of the invoice.
	 * @param $invoice : the concerned invoice.
	 * @param $invoiceLine : the line to import.
	 * @param int $sequence : the line number of this invoice.
	 */
	function importInvoiceLine($invoice, $invoiceLine, $sequence, &$totalAmountHT, &$totalTax){
        
		$qty = str_to_float($invoiceLine['quantity']);
		$listprice = str_to_float($invoiceLine['prix_unit_ht']);
		
		//N'importe pas les lignes de frais de port à 0
		if($listprice == 0
		&& $invoiceLine['productcode'] === 'ZFPORT')
			return;
		
		$discount_amount = 0;
		$tax = self::getTax($invoiceLine['taxrate']);
		if($tax){
			$taxName = $tax['taxname'];
			$taxValue = $tax['percentage'];
			$totalTax += $qty * $listprice * ($taxValue/100);
			//var_dump('$totalTax', $totalTax, "$qty * $listprice * ($taxValue/100)");
		}
		else {
			$taxName = 'tax1';
			$taxValue = null;
		}
		$totalAmountHT += $qty * $listprice;
		//var_dump('$totalAmountHT', $totalAmountHT, "$qty * $listprice", $invoiceLine['taxrate']);
		
		$incrementOnDel = $invoiceLine['isproduct'] ? 1 : 0;
		
		$db = PearDatabase::getInstance();
		$query ="INSERT INTO vtiger_inventoryproductrel (id, productid, sequence_no, quantity, listprice, discount_amount, incrementondel, $taxName) VALUES(?,?,?,?,?,?,?,?)";
		$qparams = array($invoice->getId(), $invoiceLine['productid'], $sequence, $qty, $listprice, $discount_amount, $incrementOnDel, $taxValue);
		//$db->setDebug(true);
		$result = $db->pquery($query, $qparams);
		if(!$result){
			echo "<pre>$query</pre>";
			var_dump($qparams);
			$db->echoError();
		}
	}

	/**
	 * Method to process to the import of a one invoice.
	 * @param $invoiceData : the data of the invoice to import
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importOneInvoice($invoiceData, $importDataController) {
		
		global $log;
		
		//TODO check sizeof $invoiceata
		$contact = $this->getContact($invoiceData);
		if ($contact != null) {
			$account = $contact->getAccountRecordModel();

			if ($account != null) {
				$importSourceId = $invoiceData[0]['importsourceid'];
		
				//test sur importsourceid == $importSourceId
				$query = "SELECT crmid, invoiceid
					FROM vtiger_invoicecf
					JOIN vtiger_crmentity
					    ON vtiger_invoicecf.invoiceid = vtiger_crmentity.crmid
					WHERE importsourceid = ? AND deleted = FALSE
					LIMIT 1
				";
				$db = PearDatabase::getInstance();
				$result = $db->pquery($query, array($importSourceId));//$invoiceData[0]['subject']
				if($db->num_rows($result)){
					//already imported !!
					$row = $db->fetch_row($result, 0); 
					$entryId = $this->getEntryId("Invoice", $row['crmid']);
					foreach ($invoiceData as $invoiceLine) {
						$entityInfo = array(
							'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_SKIPPED,
							'id'		=> $entryId
						);
						
						//TODO update all with array
						$importDataController->updateImportStatus($invoiceLine[id], $entityInfo);
					}
				}
				else {
					$subject = str_replace('  ', ' ', $invoiceData[0]['contact_no']
										   . ' ' . $invoiceData[0]['firstname']. ' ' . $invoiceData[0]['lastname']
										   . '/' . $invoiceData[0]['zip']
										   . ' ' . $invoiceData[0]['subject']);
					
					$record = Vtiger_Record_Model::getCleanInstance('Invoice');
					$record->set('mode', 'create');
					$record->set('bill_street', $invoiceData[0]['street']);
					$record->set('bill_street2', $invoiceData[0]['street2']);
					$record->set('bill_street3', $invoiceData[0]['street3']);
					$record->set('bill_city', $invoiceData[0]['city']);
					$record->set('bill_code', $invoiceData[0]['zip']);
					$record->set('bill_country', $invoiceData[0]['country']);
					$record->set('subject', $subject);
					//$record->set('receivedcomments', $srcRow['paiementpropose']);
					$record->set('receivedmoderegl', $invoiceData[0]['receivedmoderegl']);
					//$record->set('description', $srcRow['notes']);
					$record->set('invoicedate', $invoiceData[0]['invoicedate']);
					$record->set('duedate', $invoiceData[0]['invoicedate']);
					$record->set('contact_id', $contact->getId());
					$record->set('account_id', $account->getId());
					//$record->set('received', str_replace('.', ',', $srcRow['netht']+$srcRow['nettva']));
					//$record->set('hdnGrandTotal', $srcRow['netht']+$srcRow['nettva']);//TODO non enregistré : à cause de l'absence de ligne ?
					$record->set('typedossier', $invoiceData[0]['typedossier']);
					$record->set('invoicestatus', 'Validated');//TODO
					$record->set('currency_id', CURRENCY_ID);
					$record->set('conversion_rate', CONVERSION_RATE);
					$record->set('hdnTaxType', 'individual');
					$record->set('importsourceid', $invoiceData[0]['importsourceid']);
				    
					$coupon = $this->getCoupon($invoiceData[0]);
					if($coupon){
						$record->set('notesid', $coupon->getId());
						$campagne = $this->getCampaign($invoiceData[0]['affaire_code'], $coupon);
						if($campagne)
							$record->set('campaign_no', $campagne->getId());
						
					}
					
					//$db->setDebug(true);
					$record->saveInBulkMode();
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
						$entityInfo = array(
							'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_CREATED,
							'id'		=> $entryId
						);
						//TODO update all with array
						$importDataController->updateImportStatus($invoiceLine[id], $entityInfo);
					}
					$record->set('mode','edit');
					
					$invoiceNo = $record->getEntity()->setModuleSeqNumber("increment", $record->getModuleName());
					
					//This field is not manage by save()
					$record->set('invoice_no', $invoiceNo);
					//set invoice_no, date, amounts
					$query = "UPDATE vtiger_invoice
						JOIN vtiger_crmentity
							ON vtiger_crmentity.crmid = vtiger_invoice.invoiceid
						SET invoice_no = ?
						, total = ?
						, subtotal = ?
						, taxtype = ?
						, smownerid = ?
						, balance = total - received
						/*, invoicestatus = IF(balance = 0, 'Paid', invoicestatus)*/
						WHERE invoiceid = ?
					";
					$total = $totalAmount + $totalTax;
					$result = $db->pquery($query, array(
										  $invoiceNo
									    , $total
									    , $total
									    , 'individual'
									    , ASSIGNEDTO_ALL
									    //, $invoiceData[0]['invoicedate']
									    //, $invoiceData[0]['invoicedate']
									    , $invoiceId));
					
					$log->debug("" . basename(__FILE__) . " update imported invoice (id=" . $record->getId() . ", invoiceNo=$importSourceId , total=$total, date=" . $invoiceData[0]['invoicedate']
						    . ", result=" . ($result ? " true" : "false"). " )");
					if( ! $result)
						$db->echoError();
					
					//Affectation du règlement à la facture
					if($invoiceData[0]['_rsnreglementsid']){
						RSNImportSources_Utils_Helper::addInvoiceReglementRelation($record, $invoiceData[0]['_rsnreglementsid'], 'Import PayBox');
					}
					
					//raise trigger instead of ->save() whose need invoice rows
					
					$log->debug("BEFORE " . basename(__FILE__) . " raise event handler(" . $record->getId() . ", " . $record->get('mode') . " )");
					//raise event handler
					$record->triggerEvent('vtiger.entity.aftersave');
					$log->debug("AFTER " . basename(__FILE__) . " raise event handler");
					return $record;//tmp 
				}
			} else {
				//TODO: manage error
				echo "<pre><code>Unable to find Account</code></pre>";
			}
		} else {
			echo "<pre><code>Contact introuvable</code></pre>";
			foreach ($invoiceData as $invoiceLine) {//TODO: remove duplicated code
				$entityInfo = array(
					'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
				);
				
				$importDataController->updateImportStatus($invoiceLine[id], $entityInfo);
			}

			return false;
		}

		return true;
	}


	/**
	 * Method to process to the import of the RSNReglements module.
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importRsnReglements($importDataController) {
		
		$this->beforeImportRsnReglements();
		
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
			$record->set('reglementstatus', $record->get('error')
						 ? 'Cancelled'
						 : ($invoice
							? 'Validated'
							: 'Created')
						);
			
			//$db->setDebug(true);
			$record->save();
			$reglementId = $record->getId();

			if(!$reglementId){
				//TODO: manage error
				echo "<pre><code>Impossible d'enregistrer le nouveau règlement</code></pre>";
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
				RSNImportSources_Utils_Helper::addInvoiceReglementRelation($invoice, $record, 'Import PayBox');
			
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
	 * Method that returns the invoice associated with data.
	 * @param $reglement : the rsnreglements data.
	 * @return invoice record
	 */
	function getInvoice($reglement) {
		if($reglement['_invoiceid'])
			return Vtiger_Record_Model::getInstanceById($reglement['_invoiceid'], 'Invoice');
		
	}
	
	/**
	 * Method that pre-import an invoice if it's needed (cad DP et DR, pas de BOUtique)
	 * @param $reglement : the rsnreglements data.
	 * @return invoiceid if exists or true if pre-import
	 */
	function preImportInvoice($reglement) {
		if($reglement['autorisation'] !== 'Autorisation')
			return true;
		$invoiceValues = $this->getInvoiceValues($reglement);
		if(!$invoiceValues)
			return true;
		switch($invoiceValues['invoicetype']){
		case 'BOU':
			return true;
		case 'DP':
		case 'DR':
			
			break;
		default:
			var_dump('$reglement : ', $reglement);
			var_dump('$invoiceData : ', $invoiceValues);
			echo '<pre>Erreur : Type de facture inconnu "'.$invoiceValues['invoicetype'].'"</pre>';
			return false;
			break;
		}
		
		$invoice = new RSNImportSources_Preimport_Model($invoiceValues, $this->user, 'Invoice');
		$invoice->save();
		
		return true;
	}
	
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
								$this->preImportInvoice($reglement);
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
	protected function getCoupon($invoiceData){
		$invoicetype = $invoiceData['invoicetype'];
		switch($invoicetype){
		 case 'DR':
			$codeAffaire='PAYBOX';
			break;
		 case 'DP':
			$codeAffaire='PAYBOXP';
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
		$this->postPreImportRsnReglementsData();
		$this->postPreImportInvoiceData();
		$this->checkPreImportedRecords();
		return true;
	}
	/**
	 * Method called after the file is processed.
	 *  This method must be overload in the child class.
	 */
	function postPreImportRsnReglementsData() {
		$db = PearDatabase::getInstance();
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'RsnReglements');

		RSNImportSources_Utils_Helper::clearDuplicatesInTable($tableName, array('numpiece', 'autorisation'));
		
		/* Affecte l'id du règlement déjà connu */
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
					
		/* Affecte l'id de la facture
		*/
		$query = "UPDATE  vtiger_invoicecf
		JOIN  vtiger_invoice
			ON vtiger_invoicecf.invoiceid = vtiger_invoice.invoiceid
		JOIN $tableName
			ON  (vtiger_invoicecf.importsourceid = `$tableName`.importsourceid
			OR (vtiger_invoicecf.importsourceid = `$tableName`.numpiece
				AND `$tableName`.dateoperation = vtiger_invoice.invoicedate) /* [migration] via Cogilog */
			OR vtiger_invoicecf.importsourceid LIKE CONCAT('BOU%-', `$tableName`.refboutique)
		)
		JOIN vtiger_crmentity
			ON vtiger_invoicecf.invoiceid = vtiger_crmentity.crmid
		";
		$query .= " SET `_invoiceid` = vtiger_crmentity.crmid";
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
					
		/* Affecte l'id du donateur web
		vtiger_rsnreglements.numpiece LIKE DP ou DR
		*/
		$query = "UPDATE $tableName
		JOIN  vtiger_rsndonateursweb
			ON  vtiger_rsndonateursweb.paiementid = `$tableName`.transactionid
		JOIN vtiger_crmentity
			ON vtiger_rsndonateursweb.rsndonateurswebid = vtiger_crmentity.crmid
		";
		$query .= " SET `_rsndonateurswebid` = vtiger_crmentity.crmid
		, `_contactid` = vtiger_rsndonateursweb.contactid
		";
		$query .= "
			WHERE vtiger_crmentity.deleted = 0
			AND `$tableName`.`_invoiceid` IS NULL
			AND `$tableName`.status = ".RSNImportSources_Data_Action::$IMPORT_RECORD_NONE."
		";
		$result = $db->pquery($query, array());
		if(!$result){
			echo '<br><br><br><br>';
			$db->echoError($query);
			echo("<pre>$query</pre>");
			die();
		}
		
		return true;
	}
	/**
	 * Method called after the file is processed.
	 *  This method must be overload in the child class.
	 *  Facture PBOX
	 */
	function postPreImportInvoiceData() {
		$db = PearDatabase::getInstance();
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'Invoice');
		$reglementsTableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'RsnReglements');
		
		RSNImportSources_Utils_Helper::clearDuplicatesInTable($tableName, array('importsourceid', 'num_ligne'));
		
		/* Affecte l'id de la facture déjà connue
		*/
		$query = "UPDATE  vtiger_invoicecf
		JOIN  vtiger_invoice
			ON vtiger_invoicecf.invoiceid = vtiger_invoice.invoiceid
		JOIN $tableName
			ON  vtiger_invoicecf.importsourceid = `$tableName`.importsourceid
			OR (vtiger_invoicecf.importsourceid = `$tableName`.refdonateursweb
				AND `$tableName`.invoicedate = vtiger_invoice.invoicedate) /* [migration] via Cogilog */
		JOIN vtiger_crmentity
			ON vtiger_invoice.invoiceid = vtiger_crmentity.crmid
		JOIN $reglementsTableName
			ON `$reglementsTableName`.transactionId = `$tableName`.transactionId
		";
		$query .= " SET `$tableName`.`recordid` = vtiger_crmentity.crmid
		, `$tableName`.status = ?
		, `$reglementsTableName`._invoiceid = vtiger_crmentity.crmid";
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
					
		/* Affecte l'id du contact du donateur web
		*/
		$query = "UPDATE $tableName
		JOIN  vtiger_rsndonateursweb
			ON  vtiger_rsndonateursweb.paiementid = `$tableName`.refdonateursweb
		JOIN vtiger_crmentity
			ON vtiger_rsndonateursweb.rsndonateurswebid = vtiger_crmentity.crmid
		JOIN vtiger_contactdetails
			ON vtiger_rsndonateursweb.contactid = vtiger_contactdetails.contactid
		JOIN vtiger_contactaddress
			ON vtiger_contactaddress.contactaddressid = vtiger_contactdetails.contactid
		";
		$query .= " SET `_contactid` = vtiger_rsndonateursweb.contactid
		, _accountid = vtiger_contactdetails.accountid /*may be empty*/
		, `$tableName`.contact_no = vtiger_contactdetails.contact_no
		, `$tableName`.firstname = vtiger_contactdetails.firstname
		, `$tableName`.lastname = vtiger_contactdetails.lastname
		, `$tableName`.street = vtiger_contactaddress.mailingstreet
		, `$tableName`.street2 = vtiger_contactaddress.mailingstreet2
		, `$tableName`.street3 = vtiger_contactaddress.mailingstreet3
		, `$tableName`.pobox = vtiger_contactaddress.mailingpobox
		, `$tableName`.zip = vtiger_contactaddress.mailingzip
		, `$tableName`.city = vtiger_contactaddress.mailingcity
		, `$tableName`.country = vtiger_contactaddress.mailingcountry
		";
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
		
		/* Affecte l'id du réglement déjà connu
		*/
		$query = "UPDATE $tableName
		JOIN $reglementsTableName
			ON `$reglementsTableName`.transactionId = `$tableName`.transactionId
		";
		$query .= " SET `$tableName`.`_rsnreglementsid` = `$reglementsTableName`.recordid";
		$query .= "
			WHERE `$reglementsTableName`.recordid IS NOT NULL
		";
		$result = $db->pquery($query, array());
		if(!$result){
			echo '<br><br><br><br>';
			$db->echoError($query);
			echo("<pre>$query</pre>");
			die();
		}
		
		return true;
	}
	
	//Vérifie que les données sont traitables
	function checkPreImportedRecords(){
		$db = PearDatabase::getInstance();
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'Invoice');
		
		$query = "SELECT COUNT(*)
			FROM $tableName
			WHERE `_contactid` IS NULL
			AND `$tableName`.status = ".RSNImportSources_Data_Action::$IMPORT_RECORD_NONE."
		";
		$result = $db->pquery($query, array());
		if(!$result){
			echo '<br><br><br><br>';
			$db->echoError($query);
			echo("<pre>$query</pre>");
			die();
		}
		$count = $db->query_result($result, 0, 0);
		if($count){
			echo '<br><br><br><br>';
			echo '<code>Attention, '.$count.' donateur'.($count > 1 ? 's' : '').' web introuvable'.($count > 1 ? 's' : '').'.</code>';
			$query = "SELECT *
				FROM $tableName
				WHERE `_contactid` IS NULL
				AND `$tableName`.status = ".RSNImportSources_Data_Action::$IMPORT_RECORD_NONE."
				LIMIT 12
			";
			echo "<table>";
			$result = $db->pquery($query, array());
			while ($row = $db->getNextRow($result)){
				echo "<tr>";
				foreach($row as $column=>$value)
					if(!is_numeric($column))
						echo "<td>$value</td>";
				echo "</tr>";
			}
			echo "</table>";
			exit;
		}
        
	}
	
	/**
	 * Method called before the data are really imported.
	 *  This method must be overload in the child class.
	 *
	 * Note : les factures qui viennent d'être créées doivent identifier les reglements à importer
	 */
	function beforeImportRsnReglements() {
		$db = PearDatabase::getInstance();
		$reglementsTableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'RsnReglements');
		$invoicesTableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'Invoice');
							
		/* Affecte l'id du contact trouvé dans l'import Factures ou Contacts à l'autre table
		*/
		$query = "UPDATE $reglementsTableName
		JOIN  $invoicesTableName
			ON $invoicesTableName.transactionid = `$reglementsTableName`.transactionid
		";
		$query .= " SET `$reglementsTableName`._invoiceid = `$invoicesTableName`.recordid";
		$query .= "
			WHERE `$reglementsTableName`._invoiceid IS NULL
			AND `$reglementsTableName`.status = ? 
		";
		$result = $db->pquery($query, array(RSNImportSources_Data_Action::$IMPORT_RECORD_NONE));
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
		$emailTests = array('sabine@pustule.org');
		if (sizeof($line) > 0 && is_numeric($line[2])
		    && $this->isDate($line[9]) //date
		    && $line[3] == 1 //Rank
		    && !in_array($line[13], $emailTests)
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
	 * @return array : the formated data of the invoice.
	 */
	function getInvoiceValues($reglement) {
		$reference = $reglement['numpiece'];//DR05707_20150225130243 ou 4297;CP;25-04-15-17:38:33
		if(!$reference)
			return false;
		if(strpos($reference, ';') !== FALSE)
			$referenceParts = explode( ';', $reference);
		else
			$referenceParts = explode( '_', $reference);
		$sourceId = $reglement['transactionid'];
		$date = $reglement['dateregl'];
		$invoiceType = $this->getInvoiceType($reference);
		$typeDossier = $this->getInvoiceTypeDossier($reference);
		$receivedmoderegl = $this->getModeRegl($reference);
		
		$importSourceId = $this->getImportationSourceId($reglement);
		
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
		if(isset($product) && $reglement){
			$subject = $invoiceType . ' ' . $reglement['amount'] . '€';
			$invoiceValues = array(
				'transactionid' => $reglement['transactionid'],
				'refdonateursweb' => $reglement['numpiece'],//vtiger_rsndonateursweb.paiementid
				'importsourceid'		=> $importSourceId,
				'num_ligne' => 0,
				'invoicetype'		=> $invoiceType,
				'typedossier'		=> $typeDossier,
				'subject'		=> $subject,
				'invoicedate'		=> $date,
				'receivedmoderegl' => $receivedmoderegl,
			);
			$isProduct = false;
			$invoiceValues = array_merge($invoiceValues, array(
				'productcode'	=> $product['code'],
				'productid'	=> $this->getProductId($product['code'], $isProduct),
				'quantity'	=> 1,
				'productname'	=> $product['name'],
				'prix_unit_ht'	=> $reglement['amount'],
				'isproduct'	=> false,
				'taxrate'	=> 0.0,
			));
		}
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
		$modeRegl = $this->getModeRegl($reference);
		$numpiece = $reference;
		$rank = (int)$reglement[3];
		$transactionId = $reglement[7];
		$importSourceId = $this->getImportationSourceId($reglement);
		
		if($numpiece){
			if(strpos($numpiece, ';') !== FALSE)
				$referenceParts = explode( ';', $numpiece);
			else
				$referenceParts = explode( '_', $numpiece);
			$numcart = $referenceParts[0];
		}
		
		
		$reglementValues = array(
			'transactionid' => $transactionId,
			'numpiece'		=> $numpiece,//vtiger_rsndonateursweb.paiementid
			'refboutique'		=> $numcart,
			'importsourceid'		=> $importSourceId,
			'rank'			=> $rank,
			'dateregl'		=> $date,
			'dateoperation'		=> $dateoperation,
			'email'			=> $reglement[13],
			'autorisation'		=> $reglement[14],
			'amount'		=> str_to_float($reglement[17]) / 100,
			'currency_id'		=> $currencyId,
			'payment'		=> $reglement[23],
			'paymentstatus'		=> $reglement[28],
			'ip'			=> $reglement[30],
			'errorcode'		=> $reglement[31],
			'bank'			=> $reglement[1],
			'typeregl'		=> $typeregl,
			'rsnmoderegl'		=> $modeRegl,
		);

		return $reglementValues;
	}

	function getImportationSourceId($reglement){
		$sourceId = $reglement['transactionid'];
		if(!$sourceId)
			$sourceId = $reglement[7];
		return $this->sourceid_prefix . $sourceId;
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
	 * Retourne le mode de règlement associé : PayBox BOU, PayBox DR, PayBox DP
	 */
	function getModeRegl($reference){
		return 'PayBox ' . $this->getInvoiceType($reference);
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
	 * Retourne le type de facture associée : BOU, DR, DP
	 * Différencie les paiements DR et DP des autres factures boutiques (qui peuvent commencer par 'dr' (minuscules))
	 */
	function getInvoiceTypeDossier($reference){
		if($reference[0] == 'D') 
			return 'Don DP/DR';
		return 'Boutique';
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


	/**
	 * Method that retrieve a contact.
	 * @return the row data of the contact | null if the contact is not found.
	 */
	function getContact($invoiceData) {
		$id = $invoiceData[0]['_contactid'];
		if($id){
			return Vtiger_Record_Model::getInstanceById($id, 'Contacts');
		}

		return null;
	}
	
}

?>