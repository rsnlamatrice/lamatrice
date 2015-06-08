<?php

define('ASSIGNEDTO_ALL', '7');
define('COUPON_FOLDERID', '9');

//TODO : end the implementation of this import!
class RSNImport_ImportInvoicesFromPrestashop_View extends RSNImport_ImportFromFile_View {

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
	 * Method to get the imported fields for the contact module.
	 * @return array - the imported fields for the contact module.
	 */
	public function getContactsFields() {
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

	/**
	 * Method to get the imported fields for the invoice module.
	 * @return array - the imported fields for the invoice module.
	 */
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

	/**
	 * Method to process to the import of the invoice module.
	 * @param RSNImport_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importInvoice($importDataController) {
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
				$this->importOneInvoice($invoiceData, $importDataController);
				$invoiceData = array($row);
				$previousInvoiceSubjet = $invoiceSubject;
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
	function importInvoiceLine($invoice, $invoiceLine, $sequence){
	//TODO : end implementation of this method !

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

	/**
	 * Method to process to the import of a one invoice.
	 * @param $invoiceData : the data of the invoice to import
	 * @param RSNImport_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importOneInvoice($invoiceData, $importDataController) {
		//TODO check sizeof $invoiceata
		$contact = $this->getContact($invoiceData[0]['firstname'], $invoiceData[0]['lastname'], $invoiceData[0]['email']);
		if ($contact != null) {
			$account = $contact->getAccountRecordModel();

			if ($account != null) {
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
					$entryId = $this->getEntryId("Invoice", $row['crmid']);
					foreach ($invoiceData as $invoiceLine) {
						$entityInfo = array(
							'status'	=>	RSNImport_Data_Action::$IMPORT_RECORD_SKIPPED,
							'id'		=> $entryId
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
					$invoiceId = $record->getId();

					if(!$invoiceId){
						//TODO: manage error
	                    echo "<pre><code>Impossible d'enregistrer la nouvelle facture</code></pre>";
	                    foreach ($invoiceData as $invoiceLine) {
							$entityInfo = array(
								'status'	=>	RSNImport_Data_Action::$IMPORT_RECORD_FAILED,
							);
							
							$importDataController->updateImportStatus($invoiceLine[id], $entityInfo);
						}

	                    return false;
	                }
					$record->set('MODE', '');
					//This field is not manage by save()
					$query = "UPDATE vtiger_invoice
						JOIN vtiger_crmentity
							ON vtiger_crmentity.crmid = vtiger_invoice.invoiceid
						SET smownerid = ?
						WHERE invoiceid = ?
					";// TODO: invoice_no = ?, total = ?
					$result = $db->pquery($query, array(ASSIGNEDTO_ALL, $invoiceId));
					
					if( ! $result)
						$db->echoError();

					$entryId = $this->getEntryId("Invoice", $invoiceId);
					$sequence = 0;

					foreach ($invoiceData as $invoiceLine) {
						$this->importInvoiceLine($record, $invoiceLine, ++$sequence);
						$entityInfo = array(
							'status'	=>	RSNImport_Data_Action::$IMPORT_RECORD_CREATED,
							'id'	=> $entryId
						);
						
						$importDataController->updateImportStatus($invoiceLine[id], $entityInfo);
					}

					return $record;//tmp 
				}
			} else {
				//TODO: manage error
				echo "<pre><code>Unable to find Account</code></pre>";
			}
		} else {
			foreach ($invoiceData as $invoiceLine) {//TODO: remove duplicated code
				$entityInfo = array(
					'status'	=>	RSNImport_Data_Action::$IMPORT_RECORD_FAILED,
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
		$contact = new RSNImport_Preimport_Model($contactValues, $this->user, 'Contacts');
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
			$invoice = new RSNImport_Preimport_Model($invoiceLine, $this->user, 'Invoice');
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
		$result = $db->pquery($query, array($firstname, remove_accent($lastname), $email));

		if($db->num_rows($result)){
			$row = $db->fetch_row($result, 0);
			return Vtiger_Record_Model::getInstanceById($row['contactid'], 'Contacts');
		}

		return null;
	}

	/**
	 * Method that pre-import a contact if he does bnot exist in database.
	 * @param $invoice : the invoice data.
	 */
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
		$result = $db->pquery($query, array($contactData['firstname'], remove_accent($contactData['lastname']), $contactData['email']));
		if(!$db->num_rows($result)){
			$this->preImportContact($contactData);
		}
	}

	/**
	 * Method that check if a product is allready in the specified array.
	 * @param $product : the product to check.
	 * @param $productArray : the array of product.
	 * @return boolean : true if the product is in the array.
	 */
	function productIsInArray($product, $productArray) {
		for ($i = 0; $i < sizeof($productArray); ++$i) {
			if ($product['productcode'] == $productArray[$i]['productcode']) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Method that check if there is new products in the file to import.
	 *  If there is some new products found, it ended the process and display the "new product found" template
	 * @param RSNImport_FileReader_Reader $fileReader : the reader of the uploaded file.
	 * @return boolean : true if there is no new product.
	 */
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
				global $HELPDESK_SUPPORT_NAME;
				$viewer = new Vtiger_Viewer();
				$viewer->assign('FOR_MODULE', 'Invoice');
				$viewer->assign('MODULE', 'RSNImport');
				$viewer->assign('NEW_PRODUCTS', $newProducts);
				$viewer->assign('HELPDESK_SUPPORT_NAME', $HELPDESK_SUPPORT_NAME);
				$viewer->view('NewProductsFound.tpl', 'RSNImport');

				exit;//TODO: Be carefull: in this case, JS is not loaded !!!
			}
		} else {
			//TODO: manage error
			echo "not openned ...";
		}

		return true;
	}

	/**
	 * Method to parse the uploaded file and save data to the temporary pre-import table.
	 * @param RSNImport_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - true if pre-import is ended successfully
	 */
	function parseAndSaveFile(RSNImport_FileReader_Reader $fileReader) {
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

					return true;
				}

				$fileReader->close();
			} else {
				//TODO: manage error
				echo "not openned ...";
			}
		}

		return false;
	}

	/**
	 * Method that return the coupon for prestashop source.
	 *  It cache the value in the $this->coupon attribute.
	 * @return the coupon.
	 */
	private function getCoupon(){
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

	/**
	 * Method that check if a string is a formatted date (DD/MM/YYYY).
	 * @param string $string : the string to check.
	 * @return boolean - true if the string is a date.
	 */
	function isDate($string) {
	//TODO do not put this function here ?
		return preg_match("/^[0-3][0-9]-[0-1][0-9]-[0-9]{4}$/", $string);//only true for french format
	}

	/**
	 * Method that check if a line of the file is a client information line.
	 *  It assume that the line is a client information line only and only if the first data is a date.
	 * @param array $line : the data of the file line.
	 * @return boolean - true if the line is a client information line.
	 */
	function isClientInformationLine($line) {
		if (sizeof($line) > 0 && $line[0] != "" && $this->isDate($line[0])) {
			return true;
		}

		return false;
	}

	/**
	 * Method that move the cursor of the file reader to the beginning of the next found invoice.
	 * @param RSNImport_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - false if error or if no invoice found.
	 */
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

	/**
	 * Method that return the information of the next first invoice found in the file.
	 * @param RSNImport_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return the invoice information | null if no invoice found.
	 */
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

	/**
	 * Method that return the formated information of a contact found in the file.
	 * @param $invoiceInformations : the invoice informations data found in the file.
	 * @return array : the formated data of the contact.
	 */
	function getContactValues($invoiceInformations) {
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

	/**
	 * Method that return the formated information of a product found in the file.
	 * @param $product : the product data found in the file.
	 * @return array : the formated data of the product.
	 */
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

	/**
	 * Method that return the product id using his code.
	 * @param $productcode : the code of the product.
	 * @return int - the product id | null.
	 */
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

	/**
	 * Method that return the formated information of an invoice found in the file.
	 * @param $invoice : the invoice data found in the file.
	 * @return array : the formated data of the invoice.
	 */
	function getInvoiceValues($invoice) {
	//TODO end implementation of this method
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
}