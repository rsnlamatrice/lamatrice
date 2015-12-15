<?php

/*
 * Les données de Prestashop sont à relier aux réglements PayBox.
 * Aucun lien avec DonateursWeb, qui ne concerne que PayPal.
 *
 * Prestashop fournit un champ BOUnnn-<NumCart>
 * PayBox fournit un champ <NumCart>;CP;nnn;nnn...
 *
 * Lors de l'import Prestashop, tous les règlements PayBox ne sont pas forcément présents.
 * 
 */ 


//TODO : end the implementation of this import!
class RSNImportSources_ImportInvoicesFromPrestashop_View extends RSNImportSources_ImportFromFile_View {

	private $coupon = null;

	/**
	 * Method to get the source import label to display.
	 * @return string - The label.
	 */
	public function getSource() {
		return 'LBL_PRESTASHOP';
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
		return array('Contacts', 'Invoice');
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
	 * Function that returns if the import controller has a validating step of pre-import data
	 */
	public function hasValidatingStep(){
		return true;
	}
	
	/**
	 * After preImport validation, and before real import, the controller needs a validation step of pre-imported data
	 */
	public function needValidatingStep(){
		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($this->user, 'Contacts');
		$sql = 'SELECT * FROM ' . $tableName . '
			WHERE (_contactid_status = '.RSNImportSources_Import_View::$RECORDID_STATUS_NONE.'
			OR _contactid_status >= '.RSNImportSources_Import_View::$RECORDID_STATUS_CHECK.')
			AND status = '. RSNImportSources_Data_Action::$IMPORT_RECORD_NONE .'
			LIMIT 1';

		$result = $adb->query($sql);
		if(!$result){
			//normal si tous les contacts sont connus, la table a déjà disparu
			$adb->echoError('needValidatingStep');
			return false;
		}
		$numberOfRecords = $adb->num_rows($result);

		return $numberOfRecords;
	}

	function getImportPreviewTemplateName($moduleName){
		if($this->request->get('mode') === 'validatePreImportData'
		|| $this->request->get('mode') === 'getPreviewData'
		|| $this->needValidatingStep())//$moduleName === 'Contacts' && 
			return 'ImportPreviewContacts.tpl';
		return parent::getImportPreviewTemplateName($moduleName);
	}

	function getContactsFieldsMapping() {
		return array(
			'sourceid' => 'sourceid',
			'firstname' => 'firstname',
			'lastname' => 'lastname',
			"rsnnpai" => "rsnnpai",
			'mailingstreet2' => 'mailingstreet2',
			'mailingstreet' => 'mailingstreet',
			'mailingstreet3' => 'mailingstreet3',
			"mailingpobox" => "mailingpobox",
			'mailingzip' => 'mailingzip',
			'mailingcity' => 'mailingcity',
			'mailingcountry' => 'mailingcountry',
			'email' => 'email',
			'phone' => 'phone',
			'mobile' => 'mobile',
			'accounttype' => 'accounttype',
			'leadsource' => 'leadsource',
			'date' => '',//createdtime
			
			//champ supplémentaire
			"mailingpobox" => "mailingpobox",
			"rsnnpai" => "rsnnpai",
			'_contactid' => '', //Contact Id. May be many. Massively updated after preImport
			'_contactid_status' => '', //Type de reconnaissance automatique. Massively updated after preImport
			'_contactid_source' => '', //Source de la reconnaissance automatique. Massively updated after preImport
		);	
	}	
	
	function getContactsDateFields(){
		return array();
	}
	
	/**
	 * Method to get the imported fields for the contacts module.
	 * @return array - the imported fields for the contacts module.
	 */
	function getContactsFields() {
		//laisser exactement les colonnes du fichier
		return array_keys($this->getContactsFieldsMapping());
	}
	
	function getContactsFieldsMappingForPreview(){
		$fields = $this->getContactsFieldsMapping();
		unset($fields['sourceid']);
		unset($fields['isgroup']);
		unset($fields['accounttype']);
		unset($fields['leadsource']);
		unset($fields['phone']);
		unset($fields['mobile']);
		unset($fields['date']);
		
		//$fields = array_move_assoc('mailingstreet2', 'lastname', $fields);
		//$fields = array_move_assoc('mailingstreet3', 'mailingstreet', $fields);
		//$fields = array_move_assoc('mailingpobox', 'mailingstreet3', $fields);
		//$fields = array_move_assoc('rsnnpai', 'lastname', $fields);
		
		return $fields;
	}

	/**
	 * Method to get the imported fields for the invoice module.
	 * @return array - the imported fields for the invoice module.
	 */
	function getInvoiceFields() {
		return array(
			//header
			'sourceid',
			'numcart',
			'num_ligne',//n° de ligne (0 pour l'en-tête). Sert à détecter les doublons de factures quand on importe plusieurs fichiers à la fois
			'lastname',
			'firstname',
			'email',
			'street',
			'street2',
			'street3',
			'zip',
			'city',
			'country',
			'subject',
			'invoicedate',
			//lines
			'productcode',
			'productid',
			'productname',
			'isproduct',
			'quantity',
			'prix_unit_ht',
			'taxrate',
			
			'_rsnreglementsid',
			'_contactid',
		);
	}

	/**
	 * Method to process to the import of the Contacts module.
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importContacts($importDataController) {

		$this->identifyContacts();
		if($this->needValidatingStep()){
			$this->skipNextScheduledImports = true;
			$this->keepScheduledImport = true;
			return;
		}
		
		$config = new RSNImportSources_Config_Model();
		
		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($this->user, 'Contacts');
		$sql = 'SELECT * FROM ' . $tableName . '
			WHERE status = '. RSNImportSources_Data_Action::$IMPORT_RECORD_NONE . '
			AND _contactid_status IN ('.RSNImportSources_Import_View::$RECORDID_STATUS_SELECT.'
									, '.RSNImportSources_Import_View::$RECORDID_STATUS_CREATE.'
									, '.RSNImportSources_Import_View::$RECORDID_STATUS_UPDATE.')
			ORDER BY id';

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
			$this->importOneContacts(array($row), $importDataController);
			$perf->tick();
			if(Import_Utils_Helper::isMemoryUsageToHigh(true)){
				$keepScheduledImport = true;
				break;
			}
		}
		$perf->terminate();
		
		if(isset($keepScheduledImport))
			$this->keepScheduledImport = $keepScheduledImport;
		elseif($numberOfRecords == $config->get('importBatchLimit'))
			$this->keepScheduledImport = $this->getNumberOfRecords() > 0;
		
		if($this->keepScheduledImport)
			$this->skipNextScheduledImports = true;
	}

	/**
	 * Method to process to the import of a one contact.
	 * @param $contactsData : the data of the contact to import
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importOneContacts($contactsData, $importDataController) {
					
		global $log;
		
		$entryId = $contactsData[0]['_contactid']; // initialisé dans le postPreImportData
		if($entryId){
			//clean up concatened ids
			$entryId = preg_replace('/(^,+|,+$|,(,+))/', '$2', $entryId);
			if(strpos($entryId, ',')){
				//Contacts multiples : non validable
				return false;
			}
		}
		if(is_numeric($entryId)){
			$record = Vtiger_Record_Model::getInstanceById($entryId, 'Contacts');
			
			//already imported !!
			foreach ($contactsData as $contactsLine) {
				$entityInfo = array(
					'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_UPDATED,
					'id'		=> $entryId
				);
				
				//TODO update all with array
				$importDataController->updateImportStatus($contactsLine[id], $entityInfo);
			}
		}
		else {
			$record = Vtiger_Record_Model::getCleanInstance('Contacts');
			$record->set('mode', 'create');
			
			$this->updateContactRecordModelFromData($record, $contactsData);
			
			//$db->setDebug(true);
			$record->save();
			$contactId = $record->getId();
			
			if(!$contactId){
				//TODO: manage error
				echo "<pre><code>Impossible d'enregistrer le contact</code></pre>";
				foreach ($contactsData as $contactsLine) {
					$entityInfo = array(
						'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
					);
					
					//TODO update all with array
					$importDataController->updateImportStatus($contactsLine[id], $entityInfo);
				}

				return false;
			}
			
			$entryId = $this->getEntryId("Contacts", $contactId);
			foreach ($contactsData as $contactsLine) {
				$entityInfo = array(
					'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_CREATED,
					'id'		=> $entryId
				);
				$importDataController->updateImportStatus($contactsLine[id], $entityInfo);
			}
			
			$record->set('mode','edit');
			$db = PearDatabase::getInstance();
			$query = "UPDATE vtiger_crmentity
				SET smownerid = ?
				, createdtime = ?
				WHERE vtiger_crmentity.crmid = ?
			";
			$result = $db->pquery($query, array(ASSIGNEDTO_ALL
								, $contactsData[0]['date']
								, $contactId));
			
			$log->debug("" . basename(__FILE__) . " update imported contacts (id=" . $record->getId() . ", date=" . $contactsData[0]['date']
					. ", result=" . ($result ? " true" : "false"). " )");
			if( ! $result){
				$db->echoError(__FILE__.'::importOneContacts');
			}
			return $record;
		}

		return true;
	}
	
	//Mise à jour des données du record model nouvellement créé à partir des données d'importation
	private function updateContactRecordModelFromData($record, $contactsData){
		
		$fieldsMapping = $this->getContactsFieldsMapping();
		foreach($contactsData[0] as $fieldName => $value)
			if(!is_numeric($fieldName) && $fieldName != 'id'){
				$vField = $fieldsMapping[$fieldName];
				if($vField)
					$record->set($vField, $value);
			}
					
		//cast des DateTime
		foreach($this->getContactsDateFields() as $fieldName){
			$value = $record->get($fieldName);
			if( is_object($value) )
				$record->set($fieldsMapping[$fieldName], $value->format('Y-m-d'));
		}
		
		$fieldName = 'isgroup';
		$record->set('isgroup', 0);
		
		if(!$record->get('mailingzip') || !$record->get('mailingcity')
		|| (!$record->get('mailingstreet') && !$record->get('mailingstreet3') && !$record->get('mailingstreet2') && !$record->get('mailingpobox')))
			$record->set('rsnnpai', 4);//incomplète
		
		// copie depuis tout en haut
		//
		//
		//'sourceid' => 'sourceid',
		//'lastname' => 'lastname',
		//'firstname' => 'firstname',
		//'email' => 'email',
		//'mailingstreet' => 'mailingstreet',
		//'mailingstreet2' => 'mailingstreet2',
		//'mailingstreet3' => 'mailingstreet3',
		//'mailingzip' => 'mailingzip',
		//'mailingcity' => 'mailingcity',
		//'mailingcountry' => 'mailingcountry',
		//'phone' => 'phone',
		//'mobile' => 'mobile',
		//'accounttype' => 'accounttype',
		//'leadsource' => 'leadsource',
		//
		////champ supplémentaire
		//"mailingpobox" => "mailingpobox",
		//"rsnnpai" => "rsnnpai",
		//'_contactid' => '', //Contact Id. May be many. Massively updated after preImport
		//'_contactid_status' => '', //Type de reconnaissance automatique. Massively updated after preImport
		//'_contactid_source' => '', //Source de la reconnaissance automatique. Massively updated after preImport
		
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
		
		$this->beforeImportInvoices();
		
		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($this->user, 'Invoice');
		$sql = 'SELECT * FROM ' . $tableName . ' WHERE status = '. RSNImportSources_Data_Action::$IMPORT_RECORD_NONE . ' ORDER BY id';

		$result = $adb->query($sql);
		$numberOfRecords = $adb->num_rows($result);

		if ($numberOfRecords <= 0) {
			return;
		}

		$row = $adb->raw_query_result_rowdata($result, 0);
		$previousSourceId = $row['sourceid'];
		$invoiceData = array($row);

		for ($i = 1; $i < $numberOfRecords; ++$i) {
			$row = $adb->raw_query_result_rowdata($result, $i);
			$sourceId = $row['sourceid'];

			if ($previousSourceId == $sourceId) {
				array_push($invoiceData, $row);
			} else {
				$this->importOneInvoice($invoiceData, $importDataController);
				$invoiceData = array($row);
				$previousSourceId = $sourceId;
			}
		}

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
		$db->pquery($query, $qparams);
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
				$sourceId = $invoiceData[0]['sourceid'];
		
				//test sur importsourceid == $sourceId
				$query = "SELECT crmid, invoiceid
					FROM vtiger_invoicecf
					JOIN vtiger_crmentity
					    ON vtiger_invoicecf.invoiceid = vtiger_crmentity.crmid
					WHERE importsourceid = ? AND deleted = FALSE
					LIMIT 1
				";
				$db = PearDatabase::getInstance();
				$result = $db->pquery($query, array($sourceId));//$invoiceData[0]['subject']
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
					$record = Vtiger_Record_Model::getCleanInstance('Invoice');
					$record->set('mode', 'create');
					$record->set('bill_street', $invoiceData[0]['street']);
					$record->set('bill_street2', $invoiceData[0]['street2']);
					$record->set('bill_street3', $invoiceData[0]['street3']);
					$record->set('bill_city', $invoiceData[0]['city']);
					$record->set('bill_code', $invoiceData[0]['zip']);
					$record->set('bill_country', $invoiceData[0]['country']);
					$record->set('subject', $invoiceData[0]['subject']);
					//$record->set('receivedcomments', $srcRow['paiementpropose']);
					$record->set('receivedmoderegl', 'PayBox BOU');
					//$record->set('description', $srcRow['notes']);
					$record->set('invoicedate', $invoiceData[0]['invoicedate']);
					$record->set('duedate', $invoiceData[0]['invoicedate']);
					$record->set('contact_id', $contact->getId());
					$record->set('account_id', $account->getId());
					//$record->set('received', str_replace('.', ',', $srcRow['netht']+$srcRow['nettva']));
					//$record->set('hdnGrandTotal', $srcRow['netht']+$srcRow['nettva']);//TODO non enregistré : à cause de l'absence de ligne ?
					$record->set('typedossier', 'Facture'); //TODO
					$record->set('invoicestatus', 'Validated');//TODO
					$record->set('currency_id', CURRENCY_ID);
					$record->set('conversion_rate', CONVERSION_RATE);
					$record->set('hdnTaxType', 'individual');
		                    
					$record->set('importsourceid', $sourceId);
				    
					$coupon = $this->getCoupon($invoiceData[0]);
					if($coupon){
						$record->set('notesid', $coupon->getId());
						$campagne = $this->getCampaign($invoiceData[0]['affaire_code'], $coupon);
						if($campagne)
							$record->set('campaign_no', $campagne->getId());
						
					}
					/*$campagne = self::findCampagne($srcRow, $coupon);
					if($campagne)
						$record->set('campaign_no', $campagne->getId());*/
					
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
					
					//set dates, invoice_no
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
									    , $invoiceData[0]['invoicedate']
									    , $invoiceData[0]['invoicedate']
									    , $invoiceId));
					
					$log->debug("" . basename(__FILE__) . " update imported invoice (id=" . $record->getId() . ", sourceId=$sourceId , total=$total, date=" . $invoiceData[0]['invoicedate']
						    . ", result=" . ($result ? " true" : "false"). " )");
					if( ! $result)
						$db->echoError();
						
					RSNImportSources_Utils_Helper::addInvoiceReglementRelation($record, $invoiceData[0]['_rsnreglementsid'], 'Import Boutique');
					
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
	 * Method that check if a product exist.
	 * @param $product : the product to check.
	 * @return boolean : true if the product exist.
	 */
	function productExist($product) {
		$db = PearDatabase::getInstance();
		if($product['productcode']){
			$searchKey = 'productcode';
			$searchValue = $product['productcode'];
		} else {
			$searchKey = 'productname';
			$searchValue = $product['productname'];
			if(!$searchValue){
				echo "\nProduit sans code ni nom !";
				return false;
			}
		}
		$query = 'SELECT productid
			FROM vtiger_products p
			JOIN vtiger_crmentity e on p.productid = e.crmid
			WHERE p.'.$searchKey.' = ?
			AND e.deleted = FALSE
			ORDER BY discontinued
			LIMIT 1';
		$result = $db->pquery($query, array($searchValue));
		
		if ($db->num_rows($result) == 1) {
			return true;
		}
		
		if($product['productcode']){
			$searchKey = 'productcode';
			$searchValue = $product['productcode'];
		} else {
			$searchKey = 'servicename';
			$searchValue = $product['productname'];
		}
		$query = 'SELECT *
			FROM vtiger_service s
			JOIN vtiger_crmentity e on s.serviceid = e.crmid
			WHERE s.'.$searchKey.' = ?
			AND e.deleted = FALSE
			ORDER BY discontinued
			LIMIT 1';
		$result = $db->pquery($query, array($searchValue));
		
		return ($db->num_rows($result) === 1);
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
	 * @param $invoiceData : the data of the invoice to import.
	 */
	function preImportInvoice($invoiceData) {
		$invoiceValues = $this->getInvoiceValues($invoiceData);
		foreach ($invoiceValues as $invoiceLine) {
			$invoice = new RSNImportSources_Preimport_Model($invoiceLine, $this->user, 'Invoice');
			$invoice->save();
		}
	}

	/**
	 * Method that retrieve a contact.
	 * @param string $firstname : the firstname of the contact.
	 * @param string $lastname : the lastname of the contact.
	 * @param string $email : the mail of the contact.
	 * @return the row data of the contact | null if the contact is not found.
	 */
	function getContact($invoiceData) {
		$id = $invoiceData[0]['_contactid'];
		if(!$id){
			$id = $this->getContactId($invoiceData[0]['firstname'], $invoiceData[0]['lastname'], $invoiceData[0]['email']);
		}
		if($id){
			return Vtiger_Record_Model::getInstanceById($id, 'Contacts');
		}

		return null;
	}

	/**
	 * Method that pre-import a contact if he does not exist in database.
	 * @param $invoice : the invoice data.
	 */
	function checkContact($invoice) {
		$contactData = $this->getContactValues($invoice['header']);
		
		//if($this->checkPreImportInCache('Contacts', $contactData['firstname'], $contactData['lastname'], $contactData['email']))
		//	return true;
		
		/* recherche massive
		$id = $this->getContactId($contactData['firstname'], $contactData['lastname'], $contactData['email']);*/
		$id = false;
		
		//$this->setPreImportInCache($id, 'Contacts', $contactData['firstname'], $contactData['lastname'], $contactData['email']);
		
		if(!$id){
			$this->preImportContact($contactData);
		}
	}

	/**
	 * Method that check if a product is already in the specified array.
	 * @param $product : the product to check.
	 * @param $productArray : the array of product.
	 * @return boolean : true if the product is in the array.
	 */
	function productIsInArray($product, $productArray) {
		for ($i = 0; $i < sizeof($productArray); ++$i) {
			if (($product['productcode'] && $product['productcode'] == $productArray[$i]['productcode']) 
			|| (!$product['productcode'] && $product['productname'] == $productArray[$i]['productname'])) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Method that check if there is new products in the file to import.
	 *  If there is some new products found, it ended the process and display the "new product found" template
	 * @param RSNImportSources_FileReader_Reader $fileReader : the reader of the uploaded file.
	 * @return boolean : true if there is no new product.
	 */
	function checkNewProducts(RSNImportSources_FileReader_Reader $fileReader) {
		$newProducts = array();

		if($fileReader->open()) {
			if ($this->moveCursorToNextInvoice($fileReader)) {
				$i = 0;
				do {
					$invoice = $this->getNextInvoice($fileReader);
					if ($invoice != null) {
						for ($i = 0; $i < sizeof($invoice['detail']); ++$i) {
							$productValues = $this->getProductValues($invoice['detail'][$i]);

							if (!$this->productExist($productValues) && !$this->productIsInArray($productValues, $newProducts)) {
								array_push($newProducts, $productValues);
							} 
						}
					}
				} while ($invoice != null);
			}

			$fileReader->close();

			if (sizeof($newProducts) > 0) {
				
				global $HELPDESK_SUPPORT_NAME;
				$viewer = new Vtiger_Viewer();
				$viewer->assign('FOR_MODULE', 'Invoice');
				$viewer->assign('MODULE', 'RSNImportSources');
				$viewer->assign('NEW_PRODUCTS', $newProducts);
				$viewer->assign('HELPDESK_SUPPORT_NAME', $HELPDESK_SUPPORT_NAME);
				$viewer->view('NewProductsFound.tpl', 'RSNImportSources');

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
		if ($this->checkNewProducts($fileReader)) {
			$this->clearPreImportTable();

			if($fileReader->open()) {
				if ($this->moveCursorToNextInvoice($fileReader)) {
					$i = 0;
					do {
						$invoice = $this->getNextInvoice($fileReader);
						if ($invoice != null) {
							$this->checkContact($invoice);
							$this->preImportInvoice($invoice);
						}
					} while ($invoice != null);

				}

				$fileReader->close(); 
				return true;
			} else {
				//TODO: manage error
				echo "<code>le fichier n'a pas pu être ouvert...</code>";
			}
		}
		return false;
	}

	/**
	 * Method that return the coupon for prestashop source.
	 *  It cache the value in the $this->coupon attribute.
	 * @return the coupon.
	 */
	protected function getCoupon(){
		if ($this->coupon == null) {
			$codeAffaire='BOUTIQUE';
			$query = "SELECT vtiger_crmentity.crmid
				FROM vtiger_notes
				JOIN vtiger_notescf
                    ON vtiger_notescf.notesid = vtiger_notes.notesid
                JOIN vtiger_crmentity
                    ON vtiger_notes.notesid = vtiger_crmentity.crmid
				WHERE codeaffaire = ?
				AND folderid = ?
				AND vtiger_crmentity.deleted = 0
				LIMIT 1
			";
			$db = PearDatabase::getInstance();
			$result = $db->pquery($query, array($codeAffaire, COUPON_FOLDERID));
			if(!$result)
				$db->echoError();
			if($db->num_rows($result)){
				$row = $db->fetch_row($result, 0);
				$this->coupon = Vtiger_Record_Model::getInstanceById($row['crmid'], 'Documents');
			}
		}

		return $this->coupon;
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
	 * @return string - formated date.
	 */
	function getMySQLDate($string) {
		$dateArray = preg_split('/[-\/]/', $string);
		return $dateArray[2] . '-' . $dateArray[1] . '-' . $dateArray[0];
	}

	/**
	 * Method that check if a line of the file is a client information line.
	 *  It assume that the line is a client information line only and only if the first data is a date.
	 * @param array $line : the data of the file line.
	 * @return boolean - true if the line is a client information line.
	 */
	function isRecordHeaderInformationLine($line) {
		if (sizeof($line) > 0 && $line[0] != "" && $this->isDate($line[0])) {
			return true;
		}

		return false;
	}

	/**
	 * Method that move the cursor of the file reader to the beginning of the next found invoice.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - false if error or if no invoice found.
	 */
	function moveCursorToNextInvoice(RSNImportSources_FileReader_Reader $fileReader) {
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
	function getNextInvoice(RSNImportSources_FileReader_Reader $fileReader) {
		$nextLine = $fileReader->readNextDataLine($fileReader);
		if ($nextLine != false) {
			$invoice = array(
				'header' => $nextLine,
				'detail' => array());
			do {
				$cursorPosition = $fileReader->getCurentCursorPosition();
				$nextLine = $fileReader->readNextDataLine($fileReader);

				if (!$this->isRecordHeaderInformationLine($nextLine)) {
					if (!$nextLine[0] && $nextLine[2]) {
						array_push($invoice['detail'], $nextLine);
					}
				} else {
					break;
				}

			} while ($nextLine != false);

			if ($nextLine != false) {
				$fileReader->moveCursorTo($cursorPosition);
			}

			return $invoice;
		}

		return null;
	}

	/**
	 * Method that return the formated information of a contact found in the file.
	 * @param $invoiceInformations : the invoice informations data found in the file.
	 * @return array : the formated data of the contact.
	 */
	function getContactValues($invoiceInformations) {
		$sourceId = $invoiceInformations[41];
		$date = $this->getMySQLDate($invoiceInformations[0]);
		$contactsHeader = array(
			'sourceid'		=> $sourceId,
			'lastname'		=> ucfirst($invoiceInformations[46]),
			'firstname'		=> ucfirst($invoiceInformations[45]),
			'email'			=> mb_strtolower($invoiceInformations[22]),
			'mailingstreet'		=> $invoiceInformations[13],
			'mailingstreet2'	=> $invoiceInformations[14],
			'mailingstreet3'	=> $invoiceInformations[15],
			'mailingzip'		=> $invoiceInformations[16],
			'mailingcity'		=> mb_strtoupper($invoiceInformations[17]),
			'mailingcountry' 	=> $invoiceInformations[19],
			'phone'			=> $invoiceInformations[20],
			'mobile'		=> $invoiceInformations[21],
			'accounttype'		=> 'Boutique',
			'leadsource'		=> 'BOUTIQUE',
			'date'		=> $date,
		);
		//numérique
		$contactsHeader['_contactid_status'] = null;
		
		//TODO 'France' en constante de config
		if(strcasecmp($contactsHeader['mailingcountry'], 'France') === 0)
			$contactsHeader['mailingcountry'] = '';
			
		//Ajout du code de pays en préfixe du code postal
		$contactsHeader['mailingzip'] = RSNImportSources_Utils_Helper::checkZipCodePrefix($contactsHeader['mailingzip'], $contactsHeader['mailingcountry']);

		return $contactsHeader;
	}

	/**
	 * Method that returns the formated information of a product found in the file.
	 * @param $product : the product data found in the file.
	 * @return array : the formated data of the product.
	 */
	function getProductValues($product) {
		$product = array(
			'productcode'	=> $product[1],
			'productname'	=> $product[2],
			'unit_price'	=> str_to_float($product[3]),//TTC, TODO HT
			'qty_per_unit'	=> 1,
			'taxrate'	=> str_to_float($product[8]),
		);
		return $product;
	}


	/**
	 * Method that return the formated information of an invoice found in the file.
	 * @param $invoice : the invoice data found in the file.
	 * @return array : the formated data of the invoice.
	 */
	function getInvoiceValues($invoice) {
		$invoiceValues = array();
		$date = $this->getMySQLDate($invoice['header'][0]);
		$sourceId = $invoice['header'][41];
		$numcart = explode('-',$sourceId);
		if(count($numcart) == 2)
			$numcart = $numcart[1];
		else	$numcart = null;
		$numLigne = 0;
		$invoiceHeader = array(
			'sourceid'		=> $sourceId,
			'numcart'		=> $numcart,
			'num_ligne'		=> $numLigne++,
			'lastname'		=> $invoice['header'][46],
			'firstname'		=> $invoice['header'][45],
			'email'			=> $invoice['header'][22],
			'street'		=> $invoice['header'][13],
			'street2'		=> $invoice['header'][14],
			'street3'		=> $invoice['header'][15],
			'zip'			=> $invoice['header'][16],
			'city'			=> $invoice['header'][17],
			'country' 		=> $invoice['header'][19],
			'subject'		=> $invoice['header'][11],
			'invoicedate'		=> $date,
		);
		foreach ($invoice['detail'] as $product) {
			$isProduct = null;
			$productCode = $product[1];
			$productName = $product[2];
			$productId = $this->getProductId($productCode, $isProduct, $productName);
			$taxrate = str_to_float($product[8])/100;
			array_push($invoiceValues, array_merge($invoiceHeader, array(
				'num_ligne'	=> $numLigne++,
				'productid'	=> $productId,
				'productcode'	=> $productCode,
				'productname'	=> $productName,
				'isproduct'	=> $isProduct,
				'quantity'	=> str_to_float($product[5]),
				'prix_unit_ht'	=> str_to_float($product[3]) / (1 + $taxrate),
				'taxrate'	=> str_to_float($product[8]),
            
			)));
		}
		return $invoiceValues;
	}

	/**
	 * Method called after the file is processed.
	 *  This method must be overload in the child class.
	 */
	function postPreImportData() {
		
		$this->postPreImportContactsData();
		$this->postPreImportInvoiceData();
		$this->postPreImportContactsInvoicesData();
	}

	/**
	 * Method called after the file is processed.
	 *  This method must be overload in the child class.
	 */
	function postPreImportInvoiceData() {
		$db = PearDatabase::getInstance();
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'Invoice');
		
		RSNImportSources_Utils_Helper::clearDuplicatesInTable($tableName, array('sourceid', 'num_ligne'));
		
		/* création d'un index */
		$query = "ALTER TABLE `$tableName` ADD INDEX(`sourceid`)";
		$db->pquery($query);
		
		/* Annule les factures déjà importées
		*/
		$query = "UPDATE $tableName
		JOIN  vtiger_invoicecf
			ON  vtiger_invoicecf.importsourceid = `$tableName`.sourceid
		JOIN  vtiger_invoice
			ON  vtiger_invoice.invoiceid = vtiger_invoicecf.invoiceid
		JOIN vtiger_crmentity
			ON vtiger_invoice.invoiceid = vtiger_crmentity.crmid
		";
		$query .= " SET `$tableName`.status = ?
		, `$tableName`.recordid = vtiger_invoice.invoiceid
		, _contactid = vtiger_invoice.contactid";
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
					
		/* Affecte l'id du règlement
		vtiger_rsnreglements.numpiece LIKE <numcart>;CP;15-09-15-23:33:44
		*/
		$query = "UPDATE $tableName
		JOIN  vtiger_rsnreglements
			ON  vtiger_rsnreglements.numpiece LIKE CONCAT(`$tableName`.numcart, ';%')
		JOIN vtiger_crmentity
			ON vtiger_rsnreglements.rsnreglementsid = vtiger_crmentity.crmid
		";
		$query .= " SET `_rsnreglementsid` = vtiger_crmentity.crmid";
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
		
		return true;
	}

	/**
	 * Method called after the file is processed.
	 *  This method must be overload in the child class.
	 */
	function postPreImportContactsData() {
		$db = PearDatabase::getInstance();
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'Contacts');
				
		/* création d'un index */
		$query = "ALTER TABLE `$tableName` ADD INDEX(`lastname`)";
		$db->pquery($query);
		/* création d'un index */
		$query = "ALTER TABLE `$tableName` ADD INDEX(`firstname`)";
		$db->pquery($query);
		/* création d'un index */
		$query = "ALTER TABLE `$tableName` ADD INDEX(`email`)";
		$db->pquery($query);
		
		//see PreImport validation step
		
		//RSNImportSources_Utils_Helper::setPreImportDataContactIdByNameAndEmail(
		//	$this->user,
		//	'Contacts',
		//	'_contactid',
		//	array(
		//		'lastname' => 'lastname',
		//		'firstname' => 'firstname',
		//		'email' => 'email',
		//	), 
		//	RSNImportSources_Data_Action::$IMPORT_RECORD_SKIPPED
		//);
		return true;
	}

	/**
	 * Method called after the file is processed.
	 *  This method must be overload in the child class.
	 */
	function postPreImportContactsInvoicesData() {
		$db = PearDatabase::getInstance();
		$contactsTableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'Contacts');
		$invoiceTableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'Invoice');
					
		/* Affecte l'id du contact trouvé dans l'import Factures ou Contacts à l'autre table
		*/
		$query = "UPDATE $contactsTableName
		JOIN  $invoiceTableName
			ON ($invoiceTableName.sourceid = `$contactsTableName`.sourceid
				OR ( /* comme on a supprimé les doublons de contacts correspondant à +ieurs factures, on doit chercher par nom et email*/
					$invoiceTableName.lastname = `$contactsTableName`.lastname AND
					$invoiceTableName.firstname = `$contactsTableName`.firstname AND
					$invoiceTableName.email = `$contactsTableName`.email AND
					NOT($invoiceTableName.email IS NULL OR $invoiceTableName.email = '')
				)
			)
		";
		$query .= " SET `$contactsTableName`._contactid = IFNULL(`$contactsTableName`.`_contactid`,`$invoiceTableName`._contactid)
		, `$invoiceTableName`._contactid = IFNULL(`$invoiceTableName`.`_contactid`,`$contactsTableName`._contactid)
		/* affecte le status SKIP si contactid est connu et status = 0 */
		, `$contactsTableName`.status = IF(`$contactsTableName`.status = ? AND `$contactsTableName`._contactid IS NULL, `$contactsTableName`.status, ?)
		, `$contactsTableName`._contactid_status = IF(`$contactsTableName`.status = ? OR `$contactsTableName`._contactid IS NULL, `$contactsTableName`._contactid_status, ?)";
		$query .= "
			WHERE NOT (`$invoiceTableName`._contactid IS NULL AND `$contactsTableName`._contactid IS NULL)
		";
		$result = $db->pquery($query, array(RSNImportSources_Data_Action::$IMPORT_RECORD_NONE, RSNImportSources_Data_Action::$IMPORT_RECORD_SKIPPED
											, RSNImportSources_Data_Action::$IMPORT_RECORD_SKIPPED, RSNImportSources_Data_Action::$IMPORT_RECORD_SKIPPED));
		if(!$result){
			echo '<br><br><br><br>';
			$db->echoError($query);
			echo("<pre>$query</pre>");
			die();
		}
		return true;
	}


	
	/**
	 * Method called before the data are really imported.
	 *  This method must be overload in the child class.
	 *
	 * Note : pas de postPreImportData() à cause de la validation du pre-import
	 */
	function beforeImportInvoices() {
		$db = PearDatabase::getInstance();
		$contactsTableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'Contacts');
		$invoicesTableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'Invoice');
		
		/* en scheduled import, si tous les contacts sont connus, la table de contacts a déjà disparu. Ce qui provoque un plantage de la requête plus bas */
		$query = "SELECT 1
			FROM $invoicesTableName
			WHERE $invoicesTableName._contactid IS NULL
			LIMIT 1
		";
		$result = $db->pquery($query);
		if($db->getRowCount($result)){
			
			/* Affecte l'id du contact trouvé dans l'import Factures ou Contacts à l'autre table
			*/
			$query = "UPDATE $contactsTableName
			JOIN  $invoicesTableName
				ON ($invoicesTableName.sourceid = `$contactsTableName`.sourceid
					OR (
						$invoicesTableName.email = `$contactsTableName`.email AND
						NOT($invoicesTableName.email IS NULL OR $invoicesTableName.email = '')
					)
				)
			";
			$query .= " SET `$invoicesTableName`._contactid = `$contactsTableName`.recordid
			/* affecte le status FAILED si contactid est inconnu */
			, `$invoicesTableName`.status = IF(`$contactsTableName`.recordid IS NULL, ?, `$invoicesTableName`.status)";
			$query .= "
				WHERE `$invoicesTableName`._contactid IS NULL
				AND `$invoicesTableName`.status = ? 
			";
			$result = $db->pquery($query, array(RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED, RSNImportSources_Data_Action::$IMPORT_RECORD_NONE));
			if(!$result){
				echo '<br><br><br><br>';
				$db->echoError($query);
				echo("<pre>$query</pre>");
				die();
			}
		}
		return true;
	}
	
	/**
	 * Initialise les données de validation des Contacts
	 */
	function initDisplayPreviewData() {
		$this->initDisplayPreviewContactsData();
		return parent::initDisplayPreviewData();
	}
	
	/**
	 * Method to get the pre Imported data in order to preview them.
	 *  By default, it return the values in the pre-imported table.
	 *  This method can be overload in the child class.
	 * @return array - the pre-imported values group by module.
	 */
	public function getPreviewData($request, $offset = 0, $limit = 24, $importModules = false) {
		if(!$importModules
		&& $this->needValidatingStep())
			$importModules =array('Contacts');
		$data = parent::getPreviewData($request, $offset, $limit, $importModules);
		return RSNImportSources_Utils_Helper::getPreviewDataWithMultipleContacts($data);
	}
}