<?php
/**
 * Importation des réglements effectués sur Paypal, c'est à dire des dons effectués depuis le site web
 * Cet import s'effectue après l'importation des Donateurs web (qui fournit les coordonnées des contacts)
 * Les écritures avec un n° d'objet correspondent aux données RSNDonateursWeb et génèrent des factures de dons.
 * Les écritures sans n° d'objet génèrent, éventuellement un contact, et des factures de dons.
 * TODO
 * Les réglements des commissions de Paypal sont à générer en Facture Fournisseur, à transférer en compta.
 */

class RSNImportSources_ImportRsnReglementsFromPaypal_View extends RSNImportSources_ImportFromFile_View {

	private $reglementOrigine = 'PayPal';
	
	public $sourceid_prefix = 'PPAL';

	/**
	 * Method to get the source import label to display.
	 * @return string - The label.
	 */
	public function getSource() {
		return 'LBL_PAYPAL';
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
		return array('Contacts', 'Invoice', 'RsnReglements'/*, 'PurchaseOrder'*/);
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
	 * Function that returns if the import controller has a validating step of pre-import data
	 */
	public function hasValidatingStep(){
		return in_array('Contacts', $this->getImportModules());//héritage From4D
	}
	
	/**
	 * After preImport validation, and before real import, the controller needs a validation step of pre-imported data
	 */
	public function needValidatingStep(){
		if(!$this->hasValidatingStep())
			return false;
		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($this->user, 'Contacts');
		$sql = 'SELECT * FROM ' . $tableName . '
			WHERE ( _contactid_status = '.RSNImportSources_Import_View::$RECORDID_STATUS_NONE.'
			OR _contactid_status >= '.RSNImportSources_Import_View::$RECORDID_STATUS_CHECK.')
			AND status = '. RSNImportSources_Data_Action::$IMPORT_RECORD_NONE .'
			LIMIT 1';

		$result = $adb->query($sql);
		if(!$result){
			echo "<pre>$sql</pre>";
			$adb->echoError('needValidatingStep');
			return true;
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

	/**
	 * Method to get the imported fields for the contact module.
	 * @return array - the imported fields for the contact module.
	 *
	 * Les contacts devraient tous pré-exister du fait des imports DonateursWeb ou Boutique
	 * sauf que ce n'est pas vrai pour les DR
	 */
	public function getContactsFieldsMapping() {
		return array(
			'importsourceid' => '',
			
			"firstname" => "firstname",
			"lastname" => "lastname",
			"mailingstreet3" => "mailingstreet3",
			"mailingstreet" => "mailingstreet",
			"mailingzip" => "mailingzip",
			"mailingcity" => "mailingcity",
			"mailingcountry" => "mailingcountry",
			"email" => "email",
			'phone' => 'phone',
			'mobile' => 'mobile',
			'accounttype' => 'accounttype',
			'leadsource' => 'leadsource',
			'isgroup' => 'isgroup',
			
			"date" => "",//format MySQL
			
			//champ supplémentaire
			"mailingstreet2" => "mailingstreet2",
			"mailingpobox" => "mailingpobox",
			"rsnnpai" => "rsnnpai",
			
			'_contactid' => '',
			'_contactid_status' => '', //Type de reconnaissance automatique. Massively updated after preImport
			'_contactid_source' => '', //Source de la reconnaissance automatique. Massively updated after preImport
		);
	}
	function getContactsDateFields(){
		return array();
	}
	function getContactsFieldsMappingForPreview(){
		$fields = $this->getContactsFieldsMapping();
		unset($fields['importsourceid']);
		unset($fields['isgroup']);
		unset($fields['accounttype']);
		unset($fields['leadsource']);
		unset($fields['phone']);
		unset($fields['mobile']);
		unset($fields['date']);
		$fields = array_move_assoc('mailingstreet2', 'lastname', $fields);
		$fields = array_move_assoc('mailingstreet3', 'mailingstreet', $fields);
		$fields = array_move_assoc('mailingpobox', 'mailingstreet3', $fields);
		$fields = array_move_assoc('rsnnpai', 'lastname', $fields);
		$fields = array_move_assoc('isgroup', 'email', $fields);
		
		return $fields;
	}
	
	/**
	 * Method to get the imported fields for the contacts module.
	 * @return array - the imported fields for the contacts module.
	 */
	function getContactsFields() {
		//laisser exactement les colonnes du fichier
		return array_keys($this->getContactsFieldsMapping());
	}
	

	/**
	 * Method to get the imported fields for the invoice module.
	 * @return array - the imported fields for the invoice module.
	 *
	 * Factures des dons DR et DP
	 */
	function getInvoiceFields() {
		return array(
			//header
			'importsourceid',
			'lastname',
			'firstname',
			'email',
			'street',
			'street2',
			'street3',
			'zip',
			'city',
			'state',
			'country',
			'subject',
			'invoicedate',
			'invoicetype',
			'typedossier',
			//lines
			'productcode',
			'productid',
			'article',
			'isproduct',
			'quantity',
			'prix_unit_ht',
			'taxrate',
			'rsndonateurweb_externalid',
			'affaire_code',
			
			'receivedmoderegl',
			
			//Massively updated
			'_rsndonateurswebid',
			'_contactid',
		);
	}

	

	/**
	 * Method to get the imported fields for the purchase order module.
	 * @return array - the imported fields for the purchase order module.
	 *
	 * Factures de PayPal
	 */
	function getPurchaseOrderFields() {
		return array(
			//header
			'importsourceid',
			'lastname',
			'firstname',
			'email',
			'street',
			'street2',
			'street3',
			'zip',
			'city',
			'state',
			'country',
			'subject',
			'invoicedate',
			'invoicetype',
			//lines
			'productcode',
			'productid',
			'article',
			'isproduct',
			'quantity',
			'prix_unit_ht',
			'taxrate',
		);
	}

	
	/**
	 * Method to get the imported fields for the invoice module.
	 * @return array - the imported fields for the invoice module.
	 */
	function getRsnReglementsFields() {
		return array(
			//header
			'numpiece',
			'refdonateurweb',
			'importsourceid',
			'clicksource',
			'dateregl',
			'email',
			'amount',
			'commission',
			'currency_id',
			'typeregl',
			'rsnmoderegl',
			'errorcode',
			'contactname',
			'phone',
			'firstname',
			'lastname',
			'street',
			'street3',
			'zip',
			'city',
			'state',
			'country',
			
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
	
	
	/**
	 * Method called before the data are really imported.
	 *  This method must be overload in the child class.
	 *
	 * Note : pas de postPreImportData() à cause de la validation du pre-import
	 */
	function beforeImportInvoice() {
		if(!$this->hasValidatingStep())
			return;
		$db = PearDatabase::getInstance();
		$contactsTableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'Contacts');
		$invoiceTableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'Invoice');
							
		/* Affecte l'id du contact trouvé dans l'import Factures ou Contacts à l'autre table
		*/
		$query = "UPDATE $contactsTableName
		JOIN  $invoiceTableName
			ON ($invoiceTableName.importsourceid = `$contactsTableName`.importsourceid
				OR ( /* tous les importsourceid ne sont pas présents, si doublon */
					$invoiceTableName.email = `$contactsTableName`.email AND
					NOT($invoiceTableName.email IS NULL OR $invoiceTableName.email = '')
				)
			)
		";
		$query .= " SET `$invoiceTableName`._contactid = `$contactsTableName`.recordid
		/* affecte le status FAILED si contactid est inconnu */
		, `$invoiceTableName`.status = IF(`$contactsTableName`.recordid IS NULL, ?, `$invoiceTableName`.status)";
		$query .= "
			WHERE `$invoiceTableName`._contactid IS NULL
			AND `$invoiceTableName`.status = ? 
		";
		$result = $db->pquery($query, array(RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED, RSNImportSources_Data_Action::$IMPORT_RECORD_NONE));
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
	function beforeImportRsnReglements() {
		
		if(!$this->hasValidatingStep())
			return;
		$db = PearDatabase::getInstance();
		$contactsTableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'Contacts');
		$invoiceTableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'Invoice');
		$rsnreglementsTableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'RsnReglements');
		
		/* Affecte l'id du contact trouvé dans l'import Contacts à l'autre table
		*/
		$query = "UPDATE $contactsTableName
		JOIN  $rsnreglementsTableName
			ON ($rsnreglementsTableName.importsourceid = `$contactsTableName`.importsourceid
				OR ( /* tous les importsourceid ne sont pas présents, si doublon */
					$rsnreglementsTableName.email = `$contactsTableName`.email AND
					NOT($rsnreglementsTableName.email IS NULL OR $rsnreglementsTableName.email = '')
				)
			)
		";
		$query .= " SET `$rsnreglementsTableName`._contactid = `$contactsTableName`.recordid
		/* affecte le status FAILED si contactid est inconnu */
		, `$rsnreglementsTableName`.status = IF(`$contactsTableName`.recordid IS NULL, ?, `$rsnreglementsTableName`.status)";
		$query .= "
			WHERE `$rsnreglementsTableName`._contactid IS NULL
			AND `$rsnreglementsTableName`.status = ? 
		";
		$result = $db->pquery($query, array(RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED, RSNImportSources_Data_Action::$IMPORT_RECORD_NONE));
		if(!$result){
			echo '<br><br><br><br>';
			$db->echoError($query);
			echo("<pre>$query</pre>");
			die();
		}
		
		/* Affecte l'id du contact trouvé dans l'import Factures à l'autre table
		*/
		$query = "UPDATE $invoiceTableName
		JOIN  $rsnreglementsTableName
			ON ($rsnreglementsTableName.importsourceid = `$invoiceTableName`.importsourceid
				OR ( /* tous les importsourceid ne sont pas présents, si doublon */
					$rsnreglementsTableName.email = `$invoiceTableName`.email AND
					NOT($rsnreglementsTableName.email IS NULL OR $rsnreglementsTableName.email = '')
				)
			)
		";
		$query .= " SET `$rsnreglementsTableName`._contactid = `$invoiceTableName`._contactid
		/* affecte le status FAILED si contactid est inconnu */
		, `$rsnreglementsTableName`.status = IF(`$invoiceTableName`.recordid IS NULL, ?, `$rsnreglementsTableName`.status)";
		$query .= "
			WHERE `$rsnreglementsTableName`._contactid IS NULL
			AND `$rsnreglementsTableName`.status = ? 
		";
		$result = $db->pquery($query, array(RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED, RSNImportSources_Data_Action::$IMPORT_RECORD_NONE));
		if(!$result){
			echo '<br><br><br><br>';
			$db->echoError($query);
			echo("<pre>$query</pre>");
			die();
		}
		
		return true;
	}
	

	/**
	 * Method to process to the import of the RSNReglements module.
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importRsnReglements($importDataController) {

		if($this->needValidatingStep()){
			$this->skipNextScheduledImports = true;
			$this->keepScheduledImport = true;
			return;
		}

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
		$previousReglementKey = $row['numpiece'];
		$reglementData = array($row);

		for ($i = 1; $i < $numberOfRecords; ++$i) {
			$row = $adb->raw_query_result_rowdata($result, $i);
			$reglementKey = $row['numpiece'];

			if ($previousReglementKey == $reglementKey) {
				array_push($reglementData, $row);
			} else {
				$this->importOneRsnReglements($reglementData, $importDataController);
				$reglementData = array($row);
				$previousReglementKey = $reglementKey;
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
		$reglement = $reglementData[0];
		//TODO check sizeof $reglementata
		$invoice = $this->getInvoice($reglement);
		
		//var_dump('importOneRsnReglements', $reglement, $invoice);
		
		if ($invoice != null) {
			$accountId = $invoice->get('account_id');

			if ($accountId != null) {
				$numpiece = $reglement['numpiece'];
		
				$query = "SELECT crmid, rsnreglementsid
					FROM vtiger_rsnreglements
					JOIN vtiger_crmentity
					    ON vtiger_rsnreglements.rsnreglementsid = vtiger_crmentity.crmid
					WHERE numpiece = ?
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
					$record->set('contactname', $reglement['contactname']);
					$record->set('origine', $this->reglementOrigine);
					if($reglement['errorcode']){
						$record->set('errorcode', $reglement['errorcode']);
						$record->set('error', 1);
					}
					else
						$record->set('error', 0);
					
					//$db->setDebug(true);
					$record->save();
					$reglementId = $record->getId();

					if(!$reglementId){
						//TODO: manage error
						echo "<pre><code>Impossible d'enregistrer le nouvel règlement</code></pre>";
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
					RSNImportSources_Utils_Helper::addInvoiceReglementRelation($invoice, $record, 'Import PayPal');
					
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
			} else {
				//TODO: manage error
				echo "<pre><code>Unable to find Account</code></pre>
				";
			}
		} else {
			echo "<pre><code>Unable to find Invoice</code></pre>
			";
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
	 * Method to process to the import of the invoice module.
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importInvoice($importDataController) {

		if($this->needValidatingStep()){
			$this->skipNextScheduledImports = true;
			$this->keepScheduledImport = true;
			return;
		}

		$this->beforeImportInvoice();
		
		global $adb;
		$tableName = Import_Utils_Helper::getDbTableName($this->user, 'Invoice');
		$sql = 'SELECT * FROM ' . $tableName . ' WHERE status = '. RSNImportSources_Data_Action::$IMPORT_RECORD_NONE . ' ORDER BY id';

		$result = $adb->pquery($sql);
		$numberOfRecords = $adb->num_rows($result);

		if ($numberOfRecords <= 0) {
			return;
		}

		$row = $adb->raw_query_result_rowdata($result, 0);
		$previousInvoiceKey = $row['importsourceid'];//tmp subject, use importsourceid ???
		$invoiceData = array($row);

		for ($i = 1; $i < $numberOfRecords; ++$i) {
			$row = $adb->raw_query_result_rowdata($result, $i);
			$invoiceKey = $row['importsourceid'];

			if ($previousInvoiceKey == $invoiceKey) {
				array_push($invoiceData, $row);
			} else {
				$this->importOneInvoice($invoiceData, $importDataController);
				$invoiceData = array($row);
				$previousInvoiceKey = $invoiceKey;
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
        
		$qty = $invoiceLine['quantity'];
		$listprice = $invoiceLine['prix_unit_ht'];
		
		//N'importe pas les lignes de frais de port à 0
		if($listprice == 0
		&& $invoiceLine['productcode'] == 'ZFPORT')
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
		$contactId = $invoiceData[0]['_contactid'];
		if(!$contactId)
			$contactId = $this->getContactId($invoiceData[0]['firstname'], $invoiceData[0]['lastname'], $invoiceData[0]['email']);
		if($contactId){
			$contact = Vtiger_Record_Model::getInstanceById($contactId, 'Contacts');
		}
		if ($contact != null) {
			$account = $contact->getAccountRecordModel();

			if ($account != null) {
				$sourceId = $invoiceData[0]['importsourceid'];
		
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
					$record->set('subject', substr($invoiceData[0]['subject']
									. ' ' . $contact->get('contact_no')
									. ' ' . $contact->getName()
									. '/' . $invoiceData[0]['zip']
									. ' / ' . $invoiceData[0]['invoicedate']
								 , 0, 100));
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
		                    
				    
					/*$coupon = $this->getCoupon($invoiceData[0]);
					if($coupon != null)
						$record->set('notesid', $coupon->getId());*/
					
					//Coupon et campagne
					$coupon = $this->getCoupon($invoiceData[0]['affaire_code']);
					if($coupon){
						$record->set('notesid', $coupon->getId());
						$campagne = $this->getCampaign($invoiceData[0]['affaire_code'], $coupon);
						if($campagne)
							$record->set('campaign_no', $campagne->getId());
						
					}
					
					//$db->setDebug(true);
					$log->debug('ImportRsnReglementsFromPaypal.php importOneInvoice $record->save();');
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
					//This field is not manage by save()
					
					$invoiceNo = $record->getEntity()->setModuleSeqNumber("increment", $record->getModuleName());
					$record->set('invoice_no',$invoiceNo);
					
					//set importsourceid
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
					$result = $db->pquery($query, array(
										  $invoiceNo
									    , $total
									    , $total
									    , 'individual'
									    , ASSIGNEDTO_ALL
									    , $invoiceData[0]['invoicedate']
									    , $invoiceData[0]['invoicedate']
									    , $invoiceId));
					
					$log->debug("" . basename(__FILE__) . " update imported invoice (id=" . $record->getId() . ", importsourceid=$sourceId , total=$total, date=" . $invoiceData[0]['invoicedate']
						    . ", result=" . ($result ? " true" : "false"). " )");
					if( ! $result)
						$db->echoError();
						
						
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
	 * Method that pre import an invoice.
	 *  It adone row in the temporary pre-import table by invoice line.
	 * @param $invoiceData : the data of the invoice to import.
	 */
	function preImportInvoice($invoiceData, $donateurWeb) {
		$invoiceValues = $this->getInvoiceValues(null, $invoiceData, $donateurWeb);
		$invoice = new RSNImportSources_Preimport_Model($invoiceValues, $this->user, 'Invoice');
		$invoice->save();
		//var_dump('preImportInvoice', $invoiceData, $invoiceValues);
	}

	/**
	 * Method that pre import a purchase order.
	 *  It adone row in the temporary pre-import table by purchase order line.
	 * @param $invoiceData : the data of the purchase order to import.
	 */
	function preImportPurchaseOrder($purchaseOrderData) {
		$purchaseOrderValues = $this->getPurchaseOrderValues(null, $purchaseOrderData);
		$purchaseOrder = new RSNImportSources_Preimport_Model($purchaseOrderValues, $this->user, 'PurchaseOrder');
		$purchaseOrder->save();
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
	 * @param $invoice : the invoice data.
	 */
	function checkContact($invoice, $reglement) {
		$contactData = $this->getContactValues($invoice, $reglement);
		
		if($this->checkPreImportInCache('Contacts', $contactData['firstname'], $contactData['lastname'], $contactData['email']))
			return true;
		
		/* recherche massive
		$id = $this->getContactId($contactData['firstname'], $contactData['lastname'], $contactData['email']);*/
		$id = false;
		
		$this->setPreImportInCache($id, 'Contacts', $contactData['firstname'], $contactData['lastname'], $contactData['email']);
		
		if(!$id){
			$this->preImportContact($contactData);
		}
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
		$record->set($fieldName, 0);
		$record->set('leadsource', 'PETITION');//TODO
		
		if(!$record->get('mailingzip') || !$record->get('mailingcity')
		|| (!$record->get('mailingstreet') && !$record->get('mailingstreet3') && !$record->get('mailingstreet2') && !$record->get('mailingpobox')))
			$record->set('rsnnpai', 4);//incomplète
		
		
	}
	/**
	 * Method that return the formated information of a contact found in the file.
	 * @param $invoiceInformations : the invoice informations data found in the file.
	 * @return array : the formated data of the contact.
	 */
	function getContactValues($invoice, $reglement) {
		if(!isset($invoice['importsourceid']))
			$invoice = $this->getInvoiceValues($invoice);
		if(preg_match('/^0?6/', $reglement['phone']))
			$mobile = $reglement['phone'];
		else
			$phone = $reglement['phone'];
		$contactsHeader = array(
			'importsourceid'		=> $invoice['importsourceid'],
			'lastname'		=> $invoice['lastname'],
			'firstname'		=> $invoice['firstname'],
			'email'			=> $invoice['email'],
			'mailingstreet'		=> $invoice['street'],
			'mailingstreet2'	=> $invoice['street2'],
			'mailingstreet3'	=> $invoice['street3'],
			'mailingzip'		=> $invoice['zip'],
			'mailingcity'		=> $invoice['city'],
			'mailingcountry' 	=> $invoice['country'],
			'phone'			=> $phone,
			'mobile'		=> $mobile,
			'accounttype'		=> 'Donateur Web',
			'leadsource'		=> 'PAYPAL',
			'date'		=> $invoice['invoicedate'],
		);
		
		//TODO 'France' en constante de config
		if(strcasecmp($contactsHeader['mailingcountry'], 'France') === 0)
			$contactsHeader['mailingcountry'] = '';
			
		//Ajout du code de pays en préfixe du code postal
		$contactsHeader['mailingzip'] = RSNImportSources_Utils_Helper::checkZipCodePrefix($contactsHeader['mailingzip'], $contactsHeader['mailingcountry']);


		return $contactsHeader;
	}

	/**
	 * Method that returns the invoice associated with data.
	 * @param $reglement : the rsnreglements data.
	 * @return invoice record
	 */
	function getInvoice($reglement) {
		$query = "SELECT crmid
			FROM vtiger_invoicecf
			JOIN vtiger_crmentity
				ON vtiger_invoicecf.invoiceid = vtiger_crmentity.crmid
			WHERE deleted = FALSE
			AND importsourceid = ?
			LIMIT 1";
		$queryParams = array($reglement['importsourceid']);
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, $queryParams);
		if($db->num_rows($result)){
			$row = $db->fetch_row($result, 0);
			return Vtiger_Record_Model::getInstanceById($row['crmid'], 'Invoice');
		}
	}
	
	/**
	 * Method that pre-import an invoice if it does not exist in database.
	 * @param $reglement : the rsnreglements data.
	 * @return invoiceid if exists or true if pre-import
	 */
	function checkInvoice($reglement) {
		$invoiceData = $this->getInvoiceValues($reglement);
		$query = "SELECT crmid
			FROM vtiger_invoicecf
			JOIN vtiger_crmentity
				ON vtiger_invoicecf.invoiceid = vtiger_crmentity.crmid
			WHERE deleted = FALSE
		";
		switch($invoiceData['invoicetype']){
		case 'PI':
			//TODO Purchase invoice
			return false;
			break;
		case 'DP':
		case 'DR':
			$query .= " AND importsourceid = ?";
			$queryParams = array($invoiceData['importsourceid']);
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
		if(!$db->num_rows($result)){
			$donateurWeb = $this->getDonateurWeb($reglement);
			
			if(!$donateurWeb){
				$this->checkContact($invoiceData, $reglement);
			}
			$this->preImportInvoice($invoiceData, $donateurWeb);
			return true;
		}
		$row = $db->fetch_row($result, 0);
			
		return $row['crmid'];
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

			if($fileReader->open()) {
				if ($this->moveCursorToNextRsnReglement($fileReader)) {
					$i = 0;
					do {
						$reglement = $this->getNextRsnReglement($fileReader);
						if ($reglement != null) {
							$reglement = $this->getRsnReglementsValues($reglement);
							if(!$this->checkInvoice($reglement)){
								$error = 'LBL_INVOICE_MISSING';
								echo 'Facture ou Donateur web manquant.<br>Les importations "Donateurs Web" et "Boutique" ont-elles bien été effectuées ?';
								break;
							}
							$this->preImportRsnReglement($reglement);
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
	 * Method that pre import an RsnReglements.
	 *  It adone row in the temporary pre-import table by line.
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
	 * Method that return the coupon for source.
	 *  It cache the value in the $this->coupon attribute.
	 * @return the coupon.
	 */
	protected function getCoupon($codeAffaire){
		
		$coupon = parent::getCoupon($codeAffaire);
		if($coupon)
			return $coupon;
		
		$codeAffaire='PAYPAL';
		return parent::getCoupon($codeAffaire);
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
		    && $this->isDate($line[0]) //date
		    && self::str_to_float($line[7]) !== FALSE
		    && self::str_to_float($line[7]) > 0 //ignore les lignes avec un montant négatif
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
	 * Method called after the file is processed.
	 *  This method must be overload in the child class.
	 */
	function postPreImportData() {
		$this->postPreImportRsnReglementsData();
		$this->postPreImportInvoiceData();
	}

	/**
	 * Method called after the file is processed.
	 *  This method must be overload in the child class.
	 */
	function postPreImportRsnReglementsData() {
		$db = PearDatabase::getInstance();
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'RsnReglements');
					
		/* Affecte l'id du règlement */
		$query = "UPDATE $tableName
		JOIN  vtiger_rsnreglements
			ON  vtiger_rsnreglements.numpiece = `$tableName`.numpiece
		JOIN vtiger_crmentity
			ON vtiger_rsnreglements.rsnreglementsid = vtiger_crmentity.crmid
		";
		$query .= " SET `$tableName`.`recordid` = vtiger_crmentity.crmid
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
	 * Method called after the file is processed.
	 *  This method must be overload in the child class.
	 */
	function postPreImportInvoiceData() {
		$db = PearDatabase::getInstance();
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'Invoice');
					
		/* Affecte l'id du règlement */
		$query = "UPDATE $tableName
		JOIN  vtiger_rsndonateursweb
			ON vtiger_rsndonateursweb.externalid = `$tableName`.rsndonateurweb_externalid
		JOIN vtiger_crmentity
			ON vtiger_rsndonateursweb.rsndonateurswebid = vtiger_crmentity.crmid
		";
		$query .= " SET `_rsndonateurswebid` = vtiger_crmentity.crmid
		,  `_contactid` = vtiger_rsndonateursweb.contactid
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
		
		//Découvre les contacts d'après les factures
		$contactsTableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'Contacts');
		
		$query = "UPDATE $contactsTableName
		JOIN  $tableName
			ON $contactsTableName.importsourceid = `$tableName`.importsourceid
		";
		$query .= " SET `$contactsTableName`.`_contactid` = `$tableName`._contactid
			, `$contactsTableName`.status = ?
			WHERE NOT `$tableName`._contactid IS NULL
			AND `$contactsTableName`.status = ".RSNImportSources_Data_Action::$IMPORT_RECORD_NONE."
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
	 * Method that return the formated information of an invoice found in the file.
	 * @param $invoice : the invoice data found in the file.
	 * @param $donateurWeb : donateur web avec la même référence
	 * @return array : the formated data of the invoice.
	 */
	function getInvoiceValues($reglement, $invoiceValues = FALSE, $donateurWeb = FALSE) {
		if(!$invoiceValues){
			$date = $reglement['dateregl'];
			$invoiceType = $this->getInvoiceType($reglement['typeregl']);
			
			$externalid = $reglement['refdonateurweb'] ? $reglement['refdonateurweb'] : $reglement['numpiece'];//transactionid
			$clickSource = $reglement['clicksource'];
			$importSourceId = $this->getImportationSourceId($reglement);
			if(!is_numeric($externalid))
				$externalid = null;
			switch($invoiceType){
			case 'PI': //Purchase invoice
				throw new Exception('Facture fournisseur non encore gérée');
				break;
			case 'DP':
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
			$modeRegl = $reglement['rsnmoderegl'];
			
			$typeDossier = $this->getInvoiceTypeDossier($invoiceType);
			$invoiceValues = array(
				'importsourceid'		=> $importSourceId,
				'rsndonateurweb_externalid' => $externalid,
				'importsourceid'	=> $importSourceId,
				'invoicetype'		=> $invoiceType,
				'subject'		=> $product['name'],
				'invoicedate'		=> $date,
				'affaire_code' => $clickSource,
				'typedossier' => $typeDossier,
				'receivedmoderegl' => $modeRegl,
			);
		}
		
		if($donateurWeb){
			foreach(array('lastname', 'firstname', 'email', 'street', 'street2', 'street3', 'zip', 'city', 'state', 'country') as $fieldName)
				$invoiceValues[$fieldName] = decode_html($donateurWeb->get($fieldName));
		}
		elseif($reglement){
			foreach(array('lastname', 'firstname', 'email', 'street', 'street3', 'zip', 'city', 'state', 'country') as $fieldName)
				$invoiceValues[$fieldName] = $reglement[$fieldName];
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

	function getImportationSourceId($reglement){
		$sourceId = $reglement['refdonateurweb'] ? $reglement['refdonateurweb'] : $reglement['numpiece'];//transactionid
		return $this->sourceid_prefix . $sourceId;
	}

	/**
	 * Method that return the formated information of a purchase order found in the file.
	 * @param $invoice : the purchase order data found in the file.
	 * @return array : the formated data of the purchase order.
	 *
	 * TODO
	 */
	function getPurchaseOrderValues($reglement, $purchaseOrderValues = FALSE) {
		if(!$invoiceValues){
			$date = $reglement['dateregl'];
			$poType = 'invoice';
			
			$sourceId = $reglement['numpiece'];//transactionid
			$importSourceId = $this->getImportationSourceId($reglement);
				
			$product = array(
				'code' => 'DIVFOURN',
				'name' => 'Service fournisseur',
			);
			$isProduct = false;
	
			$purchaseOrderValues = array(
				'sourceid'		=> $sourceId,
				'importsourceid'	=> $importSourceId,
				'invoicetype'		=> $poType,
				'subject'		=> $product['name'],
				'invoicedate'		=> $date,
			);
		}
		if($reglement){
			foreach(array('lastname', 'email', 'street', 'street3', 'zip', 'city', 'state', 'country') as $fieldName)
				$purchaseOrderValuesv[$fieldName] = $reglement[$fieldName];
		}
		if(isset($product) && $reglement)
			$purchaseOrderValues = array_merge($invoiceValues, array(
				'productcode'	=> $product['code'],
				'productid'	=> $this->getProductId($product['code'], $isProduct),
				'quantity'	=> 1,
				'article'	=> $product['name'],
				'prix_unit_ht'	=> $reglement['amount'],
				'isproduct'	=> false,
				'taxrate'	=> 0.0,
			));

		return $purchaseOrderValues;
	}


	/**
	 * Method that return the formated information of an invoice found in the file.
	 * @param $reglement : the invoice data found in the file.
	 * @return array : the formated data of the invoice.
	 */
	function getRsnReglementsValues($reglement) {
	//TODO end implementation of this method
		$date = $this->getMySQLDate($reglement[0], $reglement[1]);
		$dateoperation = $this->getMySQLDate($reglement[0]);
		$currency = $this->getCurrency($reglement);
		$currencyId = $this->getCurrencyId($currency);
		$refdonateurweb = preg_replace('/^(.*\s)?\\\'?(\d+)\\\'?$/', '$2', $reglement[16]);
		$clickSource = preg_replace('/^(.*)\s\\\'?\d+\\\'?$/', '$1', $reglement[16]);
		$numpiece = $reglement[12];
		$typeregl = $this->getTypeRegl($reglement[4]);
		$reglementValues = array(
			'numpiece'		=> $numpiece,
			'refdonateurweb'	=> $refdonateurweb,
			'dateregl'		=> $date,
			'email'			=> mb_strtolower($reglement[10]),
			'phone'			=> $reglement[39],
			'amount'		=> self::str_to_float($reglement[7]),
			'commission'		=> self::str_to_float($reglement[8]),
			'currency_id'		=> $currencyId,
			'typeregl'		=> $typeregl,
			'rsnmoderegl'		=> 'PayPal',
			'lastname'		=> trim($reglement[3]),
			'street'		=> $reglement[33],
			'street3'		=> $reglement[34],
			'zip'			=> $reglement[37],
			'city'			=> $reglement[35],
			'state'			=> $reglement[36],
			'country'		=> $reglement[38],
			'errorcode'		=> $reglement[5] === "Terminé" ? null : $reglement[5],
			'contactname'		=> $reglement[3] . ' - ' . $reglement[10],
			'clicksource'		=> $clickSource,
		);
		
		$reglementValues['importsourceid'] = $this->getImportationSourceId($reglementValues);
		
		//Le nom est en un seul champ
		if(strpos($reglementValues['lastname'], ' ') !== false){
			$reglementValues['firstname'] = explode(' ', $reglementValues['lastname'])[0];
			$reglementValues['lastname'] = trim(substr($reglementValues['lastname'], strlen($reglementValues['firstname'])));
		}
		return $reglementValues;
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
	 * Retourne le type de facture associée : Boutique, Don Régulier, Don Ponctuel
	 * Différencie les paiements DR et DP des autres factures boutiques (qui peuvent commencer par 'dr' (minuscules))
	 */
	function getTypeRegl($type){
		switch($type){
		 case 'Don reçu':
			return 'Don ponctuel';
		 case 'Paiement d\'abonnement envoyé':
			return 'Facture fournisseur'; //DotSpirit
		 case 'Paiement récurrent reçu':
			return 'Don régulier';
		}
		return FALSE;
	}
	
	
	/**
	 * Retourne le type de facture associée : BOU, DR, DP
	 * Différencie les paiements DR et DP des autres factures boutiques (qui peuvent commencer par 'dr' (minuscules))
	 */
	function getInvoiceType($typeregl){
		switch($typeregl){
		 case 'Don ponctuel':
			return 'DP';
		 case 'Don régulier':
			return 'DR';
		 case 'Facture fournisseur':
			return 'PI'; //DotSpirit
		 default:
			echo_callstack();
			throw new Exception('Type de réglement inconnu : "' . $typeregl . '"');
		}
		return FALSE;
	}
	
	/**
	 * Retourne le type de facture associée : BOU, DR, DP
	 * Différencie les paiements DR et DP des autres factures boutiques (qui peuvent commencer par 'dr' (minuscules))
	 */
	function getInvoiceTypeDossier($invoiceType){
		switch($invoiceType){
		case 'PI': //Purchase invoice
			return 'Facture fournisseur';
		case 'DP':
		case 'DR':
			return 'Don DP/DR';
		default:
			return 'Boutique';
		}
	}
	
	/**
	 *
	 *
	 */
	function getCurrency($reglement){
		$currencyCode = $reglement[6];
		switch ($currencyCode){
		case 'EUR':
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
	 * Returns DonateursWeb record if 'numpiece' is not empty
	 *
	 */
	function getDonateurWeb($reglement){
		$refdonateurweb = $reglement['refdonateurweb'];//4297
		if(!$refdonateurweb)
			return false;
		
		$query = "SELECT crmid
			FROM vtiger_rsndonateursweb
			JOIN vtiger_crmentity
				ON vtiger_rsndonateursweb.rsndonateurswebid = vtiger_crmentity.crmid
			WHERE deleted = FALSE
			AND externalid = ?
			AND isvalid = 1 /* TODO existing 0 should raise error */
			LIMIT 1
		";
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, array($refdonateurweb));
		if(!$db->num_rows($result)){
			//var_dump('getDonateurWeb',$query, array($reference));
			return false;
		}
		$row = $db->fetch_row($result, 0);
		return Vtiger_Record_Model::getInstanceById( $row['crmid'], 'RSNDonateursWeb');
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
		if(!$this->hasValidatingStep())
			return parent::getPreviewData($request, $offset, $limit, $importModules);
		if(!$importModules
		&& $this->needValidatingStep())
			$importModules =array('Contacts');
		$data = parent::getPreviewData($request, $offset, $limit, $importModules);
		return RSNImportSources_Utils_Helper::getPreviewDataWithMultipleContacts($data);
	}
	
}

?>