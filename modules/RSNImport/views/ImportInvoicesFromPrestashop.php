<?php

define('ASSIGNEDTO_ALL', '7');
define('COUPON_FOLDERID', '9');

class RSNImport_ImportInvoicesFromPrestashop_View extends RSNImport_ImportFromFile_View {

	private $coupon = null;

	public function getSource() {
		return 'LBL_PRESTASHOP';
	}

	public function getSourceType() {
		return 'LBL_CSV_FILE';
	}

	public function getDefaultFileDelimiter() {
		return '	';
	}

	public function getSupportedFileExtentions() {
		return array('csv');
	}

	function isDate($string) {//TODO do not put this function here!
		return preg_match("/^[0-3][0-9]-[0-1][0-9]-[0-9]{4}$/", $string);//only true for french format
	}

	function isClientInformationLine($line) {
		if (sizeof($line) > 0 && $line[0] != "" && $this->isDate($line[0])) {
			return true;
		}

		return false;
	}

	function moveCursorToNextInvoice(RSNImport_FileReader_Reader $fileReader) {
		do {
			$cursorPosition = $fileReader->getCurentCursorPosition();
			$nextLine = $fileReader->readNextDataLine($fileReader);

			if ($nextLine == false) {
				return false;
			}

		} while(!$this->isClientInformationLine($nextLine));

		$fileReader->moveCursorTo($cursorPosition);

		return true;
	}

	function getNextInvoice(RSNImport_FileReader_Reader $fileReader) {
		$nextLine = $fileReader->readNextDataLine($fileReader);
		if ($nextLine != false) {
			$invoice = array(
				'invoiceInformations' => $nextLine,
				'detail' => array());
			do {
				$cursorPosition = $fileReader->getCurentCursorPosition();
				$nextLine = $fileReader->readNextDataLine($fileReader);

				if (!$this->isClientInformationLine($nextLine)) {
					if ($nextLine[1] != null && $nextLine[1] != '') {
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

	function getContactValues($invoiceInformations) {//tmp needed field ???
		$contactMapping = array(
			'lastname'			=> $invoiceInformations[46],
			'firstname'			=> $invoiceInformations[45],
			'email'				=> $invoiceInformations[22],
			'mailingstreet'		=> $invoiceInformations[13],
			'mailingstreet2'	=> $invoiceInformations[14],
			'mailingstreet3'	=> $invoiceInformations[15],
			'mailingzip'		=> $invoiceInformations[16],
			'mailingcity'		=> $invoiceInformations[17],
			'mailingcountry' 	=> $invoiceInformations[19],
			'phone'				=> $invoiceInformations[20],
			'mobile'			=> $invoiceInformations[21]
			);

		return $contactMapping;
	}

	function getContactFields() {
		return array(
			'lastname',
			'firstname',
			'email',
			'mailingstreet',
			'mailingstreet2',
			'mailingstreet3',
			'mailingzip',
			'mailingcity',
			'mailingcountry',
			'phone',
			'mobile'
			);
	}

	function getProductValues($product) {
		$product = array(
			'productname'	=> $product[2],
			'productcode'	=> $product[1],
			'unit_price'	=> $product[3],
			'qty_per_unit'	=> $product[4],//to check
			'taxclass'		=> $product[7]//to check
			//'class'	=> $product[8] //$csv_output .= number_format(round($orderdetail['rate'],2), 2, ',', '') . "\t" ;//col9 classe fiscale
		);

		return $product;
	}

	function getProductId($productcode) {
		$db = PearDatabase::getInstance();
		$query = 'SELECT productid FROM vtiger_products p JOIN vtiger_crmentity e on p.productid = e.crmid WHERE p.productcode = ? AND e.deleted = FALSE LIMIT 1';
		$result = $db->pquery($query, array($productcode));

		if ($db->num_rows($result) == 1) {
			$row = $db->fetch_row($result, 0);
			return $row['productid'];
		}

		$query = 'SELECT serviceid FROM vtiger_service s JOIN vtiger_crmentity e on s.serviceid = e.crmid WHERE s.productcode = ? AND e.deleted = FALSE LIMIT 1';
		$result = $db->pquery($query, array($productcode));

        if ($db->num_rows($result) == 1) {
			$row = $db->fetch_row($result, 0);
			return $row['serviceid'];
		}

		return null;
	}

	function getInvoiceValues($invoice) {//tmp 
		$invoiceValues = array();

		foreach ($invoice['detail'] as $product) {
			$dateArray = explode('-', $invoice['invoiceInformations'][0]);
			array_push($invoiceValues, array(
				'lastname'		=> $invoice['invoiceInformations'][46],
				'firstname'		=> $invoice['invoiceInformations'][45],
				'email'			=> $invoice['invoiceInformations'][22],
				'street'		=> $invoice['invoiceInformations'][13],
				'street2'		=> $invoice['invoiceInformations'][14],
				'street3'		=> $invoice['invoiceInformations'][15],
				'zip'			=> $invoice['invoiceInformations'][16],
				'city'			=> $invoice['invoiceInformations'][17],
				'country' 		=> $invoice['invoiceInformations'][19],
				'subject'		=> $invoice['invoiceInformations'][11],
				'invoicedate'	=> $dateArray[2] . '-' . $dateArray[1] . '-' . $dateArray[0],
				//'id'			=> $invoice['invoiceInformations'][41], //tmp parse data + rename id

				'productid'	=> $this->getProductId($product[1]),
				'quantity'	=> $product[5],
				'listprice'	=> $product[3],//tmp !!
			));
		}

		return $invoiceValues;
	}

	function getInvoiceFields() {
		return array(
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
			//'id',
			'productid',
			'quantity',
			'listprice',//tmp !!
			);
	}

	function getFieldsFor($module) {
		switch ($module) {
			case 'Contacts':
				return $this->getContactFields();
				break;
			case 'Invoice':
				return $this->getInvoiceFields();
				break;
		}
		return array();
	}

	function getDefaultValuesFor($module) {
		switch ($module) {
			case 'Contacts':
				return $this->getContactDefaultValues();
				break;
		}
		return array();
	}

	function getContactMaping() {
		
	}

	function getContactDefaultValues() {
		return array();
	}

	function getImportModules() {
		return array('Contacts', 'Invoice');
	}

	public function doImport($importDataController, $module) {
		switch($module) {
		case 'Contacts':
			parent::doImport($importDataController, $module);
			break;
		case 'Invoice':
			$this->importInvoices($importDataController);
			break;
		}
	}

	function importInvoice($invoiceData, $importDataController) {
		//tmp check sizeof $invoiceata
		$contact = $this->getContact($invoiceData[0]['firstname'], $invoiceData[0]['lastname'], $invoiceData[0]['email']);
		if ($contact != null) {
			$account = $contact->getAccountRecordModel();

			if ($account != null) {
				//tmp use invoice_no instead of subject !!
				$query = "SELECT crmid, invoiceid, vtiger_crmentity.deleted
					FROM vtiger_invoice
		                        JOIN vtiger_crmentity
		                            ON vtiger_invoice.invoiceid = vtiger_crmentity.crmid
					WHERE subject = ? AND deleted = FALSE
					LIMIT 1
				";
				$db = PearDatabase::getInstance();
				$result = $db->pquery($query, array($invoiceData[0]['subject']));
				if($db->num_rows($result)){
					//allready imported !!
					$row = $db->fetch_row($result, 0); // tmp restore from recyclebin??

					foreach ($invoiceData as $invoiceLine) {
						$entityInfo = array(
							'status'	=>	RSNImport_Data_Action::$IMPORT_RECORD_SKIPPED,
							'id'		=> $row['crmid']
						);
						
						$importDataController->updateImportStatus($invoiceLine[id], $entityInfo);
					}
				}
				else {
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
					//$record->set('duedate', $srcRow['echeance']);
					$record->set('contact_id', $contact->getId());
					$record->set('account_id', $account->getId());
					//$record->set('received', str_replace('.', ',', $srcRow['netht']+$srcRow['nettva']));
					//$record->set('hdnGrandTotal', $srcRow['netht']+$srcRow['nettva']);//TODO non enregistré : à cause de l'absence de ligne ?
					//$record->set('typedossier', 'Facture'); //TODO
					//$record->set('invoicestatus', 'Paid');//TODO
		                    
				    
					$coupon = $this->getCoupon($srcRow);
					if($coupon != null)
						$record->set('notesid', $coupon->getId());
					/*$campagne = self::findCampagne($srcRow, $coupon);
					if($campagne)
						$record->set('campaign_no', $campagne->getId());*/
					
					//$db->setDebug(true);
					$record->save();
					if(!$record->getId()){
						//tmp error
	                    echo "<pre><code>Impossible d'enregistrer la nouvelle facture</code></pre>";
	                    foreach ($invoiceData as $invoiceLine) {
							$entityInfo = array(
								'status'	=>	RSNImport_Data_Action::$IMPORT_RECORD_FAILED,
							);
							
							$importDataController->updateImportStatus($invoiceLine[id], $entityInfo);
						}

	                    return false;
	                }
					/*$record->set('MODE', '');
					//This field is not manage by save()
					$cogId = str_pad ($srcRow['id'], 6, '0', STR_PAD_LEFT);
					$record->set('invoice_no','COG'.$cogId);
					//set invoice_no
					$query = "UPDATE vtiger_invoice
						JOIN vtiger_crmentity
							ON vtiger_crmentity.crmid = vtiger_invoice.invoiceid
						SET invoice_no = CONCAT('COG', ?)
						, total = ?
						, smownerid = ?
						WHERE invoiceid = ?
					";
					$result = $db->pquery($query, array($cogId, $srcRow['netht']+$srcRow['nettva'], ASSIGNEDTO_ALL, $record->getId()));
					
					if( ! $result)
						$db->echoError();;*/
					$sequence = 0;
					foreach ($invoiceData as $invoiceLine) {
						$this->importInvoiceLine($record, $invoiceLine, ++$sequence);
						$entityInfo = array(
							'status'	=>	RSNImport_Data_Action::$IMPORT_RECORD_CREATED,
							'id'		=> $record->getId()
						);
						
						$importDataController->updateImportStatus($invoiceLine[id], $entityInfo);
					}

					return $record;//tmp 
				}
			} else {
				echo "<pre><code>Unable to find Account</code></pre>";
				
			}
		} else {
			foreach ($invoiceData as $invoiceLine) {//tmp duplicated code !!
				$entityInfo = array(
					'status'	=>	RSNImport_Data_Action::$IMPORT_RECORD_FAILED,
				);
				
				$importDataController->updateImportStatus($invoiceLine[id], $entityInfo);
			}

			return false;
		}

		return true;
	}

	function importInvoiceLine($invoice, $invoiceLine, $sequence/*, $contact*/){//tmp to check
		/*$product = $this->getProductOrService($srcRow);
		if(!$product)
			return false;
		//adds invoice/product relation
		$qty = $srcRow['quantite'];
		$listprice = $srcRow['prix_unit_ht'];
		$discount_amount = $srcRow['total_ligne_ht'] - $qty * $listprice;*/
		
		$db = PearDatabase::getInstance();
		$query ="INSERT INTO vtiger_inventoryproductrel (id, productid, sequence_no, quantity, listprice) VALUES(?,?,?,?,?)";//discount_amount ?
		$qparams = array($invoice->getId(), $invoiceLine['productid'], $sequence, $invoiceLine['quantity'], $invoiceLine['listprice']);//tmp price and taxes !!!
		$db->pquery($query, $qparams);
	}

	function importInvoices($importDataController) {
		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($this->user, 'Invoice');
		$sql = 'SELECT * FROM ' . $tableName . ' WHERE status = '. RSNImport_Data_Action::$IMPORT_RECORD_NONE . ' ORDER BY subject';

		$result = $adb->query($sql);
		$numberOfRecords = $adb->num_rows($result);

		if ($numberOfRecords <= 0) {
			return;
		}

		$row = $adb->raw_query_result_rowdata($result, 0);
		$previousInvoiceSubjet = $row['subject'];//tmp subject, use invoice_no ???
		$invoiceData = array($row);

		for ($i = 1; $i < $numberOfRecords; ++$i) {
			$row = $adb->raw_query_result_rowdata($result, $i);
			$invoiceSubject = $row['subject'];

			if ($previousInvoiceSubjet == $invoiceSubject) {
				array_push($invoiceData, $row);
			} else {
				$this->importInvoice($invoiceData, $importDataController);
				$invoiceData = array($row);
				$previousInvoiceSubjet = $invoiceSubject;
			}
		}

		$this->importInvoice($invoiceData, $importDataController);

	}

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

	function preImportContact($contactValues) {
		$contact = new RSNImport_Generic_Model($contactValues, $this->user, 'Contacts');
		$contact->save();
	}

	function preImportInvoice($invoiceData) {
		$invoiceValues = $this->getInvoiceValues($invoiceData);
		foreach ($invoiceValues as $invoiceLine) {
			$invoice = new RSNImport_Generic_Model($invoiceLine, $this->user, 'Invoice');
			$invoice->save();
		}
	}

	function getContact($firstname, $lastname, $email) {
		$query = "SELECT contactid, deleted
			FROM vtiger_contactdetails
                        JOIN vtiger_crmentity
                            ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
			WHERE deleted = FALSE
			AND UPPER(firstname) = UPPER(?)
			AND UPPER(lastname) = UPPER(?)
			AND UPPER(email) = UPPER(?)
			LIMIT 1
		";

		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, array($firstname, $lastname, $email));

		if($db->num_rows($result)){
			$row = $db->fetch_row($result, 0);
			return Vtiger_Record_Model::getInstanceById($row['contactid'], 'Contacts');
		}

		return null;
	}

	function checkContact($invoice) {
		$contactData = $this->getContactValues($invoice['invoiceInformations']);
		$query = "SELECT contactid, deleted
			FROM vtiger_contactdetails
                        JOIN vtiger_crmentity
                            ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
			WHERE deleted = FALSE
			AND UPPER(firstname) = UPPER(?)
			AND UPPER(lastname) = UPPER(?)
			AND UPPER(email) = UPPER(?)
			LIMIT 1
		";
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, array($contactData['firstname'], $contactData['lastname'], $contactData['email']));
		if(!$db->num_rows($result)){
			$this->preImportContact($contactData);
		}
	}

	function productIsInArray($product, $productArray) {
		for ($i = 0; $i < sizeof($productArray); ++$i) {
			if ($product['productcode'] == $productArray[$i]['productcode']) {
				return true;
			}
		}

		return false;
	}

	function checkNewProducts(RSNImport_FileReader_Reader $fileReader) {
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
				$viewer = new Vtiger_Viewer();
				$viewer->assign('FOR_MODULE', 'Invoice');
				$viewer->assign('MODULE', 'RSNImport');
				$viewer->assign('NEW_PRODUCTS', $newProducts);
				$viewer->view('NewProductsFound.tpl', 'RSNImport');

				exit;//tmp js not loaded !!!
			}
		} else {
			echo "not openned ...";
			//tmp throw new exception ??
		}

		return true;
	}

	function parseAndSaveFile(RSNImport_FileReader_Reader $fileReader) {
		if ($this->checkNewProducts($fileReader)) {
			RSNImport_Utils_Helper::clearUserImportInfo($this->user, 'Contacts');//tmp do not do that here ??
			RSNImport_Utils_Helper::clearUserImportInfo($this->user, 'Invoice');//tmp do not do that here ??
			RSNImport_Utils_Helper::createTable($this->getContactFields(), $this->user, 'Contacts');
			RSNImport_Utils_Helper::createTable($this->getInvoiceFields(), $this->user, 'Invoice');

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

					return true;
				}

				$fileReader->close();
			} else {
				echo "not openned ...";
			}
		}

		return false;
	}

	private function getCoupon(){//tmp to check !!
		if ($this->coupon == null) {
			$codeAffaire='BOUTIQUE';
			$query = "SELECT vtiger_crmentity.crmid
				FROM vtiger_notes
				JOIN vtiger_notescf
	                            ON vtiger_notescf.notesid = vtiger_notes.notesid
	                        JOIN vtiger_crmentity
	                            ON vtiger_notes.notesid = vtiger_crmentity.crmid
				WHERE codeaffaire = ?
				AND folderid  =?
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
}