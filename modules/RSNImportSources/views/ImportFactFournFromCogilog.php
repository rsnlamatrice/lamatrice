<?php

/*
 * Factures fournisseurs exportées depuis Cogilog (menu Analyse / Détails fournisseurs)
 *
 *
 *
Numéro	Date	Code fournisseur	Fournisseur	Code produit	Produit	Prix unitaire ht	Qté	Unité	Remise	Montant ht
1	03/07/2012	TERREV	Terre Vivante		BON DE RÉCEPTION 120001 du 02/07/2012					
1	03/07/2012	TERREV	Terre Vivante		BON DE COMMANDE 120001 du 29/06/2012					
1	03/07/2012	TERREV	Terre Vivante	LMSIM	Ma maison solaire, ici et maintenant	9,0200	20,0	1		180,40
1	03/07/2012	TERREV	Terre Vivante	ZMAF	Arrondis sur facture	-0,0300	1,0	1		-0,03

 */ 


//TODO : end the implementation of this import!
class RSNImportSources_ImportFactFournFromCogilog_View extends RSNImportSources_ImportFromFile_View {

	private $coupon = null;

	/**
	 * Method to get the source import label to display.
	 * @return string - The label.
	 */
	public function getSource() {
		return 'LBL_COGILOG_FACTFOURN';
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
		return array('Vendors', 'PurchaseOrder');
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
	 * Method to get the imported fields for the purchaseorder module.
	 * @return array - the imported fields for the purchaseorder module.
	 */
	function getPurchaseOrderFields() {
		return array(
			//header
			'sourceid',//à composer FF<année><num>
			'num_ligne',//n° de ligne (0 pour l'en-tête). Sert à détecter les doublons de factures quand on importe plusieurs fichiers à la fois
			'duedate',
			'vendorcode',
			'vendorname',
			'productcode',
			'productname',
			'unit_price',
			'quantity',
			'unit',
			'discount',
			'amount',
			
			'_vendorid',
			'_productid',
		);
	}
	
	/**
	 * Method to process to the import of the purchaseorder module.
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importPurchaseOrder($importDataController) {

		if($this->needValidatingStep()){
			$this->skipNextScheduledImports = true;
			$this->keepScheduledImport = true;
			return;
		}
		
		$this->beforeImportPurchaseOrders();
		
		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($this->user, 'PurchaseOrder');
		$sql = 'SELECT * FROM ' . $tableName . ' WHERE status = '. RSNImportSources_Data_Action::$IMPORT_RECORD_NONE . ' ORDER BY id';

		$result = $adb->query($sql);
		$numberOfRecords = $adb->num_rows($result);

		if ($numberOfRecords <= 0) {
			return;
		}

		$row = $adb->raw_query_result_rowdata($result, 0);
		$previousSourceId = $row['sourceid'];
		$purchaseorderData = array($row);

		for ($i = 1; $i < $numberOfRecords; ++$i) {
			$row = $adb->raw_query_result_rowdata($result, $i);
			$sourceId = $row['sourceid'];

			if ($previousSourceId == $sourceId) {
				array_push($purchaseorderData, $row);
			} else {
				$this->importOnePurchaseOrder($purchaseorderData, $importDataController);
				$purchaseorderData = array($row);
				$previousSourceId = $sourceId;
			}
		}

		$this->importOnePurchaseOrder($purchaseorderData, $importDataController);
	}

	/**
	 * Method to process to the import a line of the purchaseorder.
	 * @param $purchaseorder : the concerned purchaseorder.
	 * @param $purchaseorderLine : the line to import.
	 * @param int $sequence : the line number of this purchaseorder.
	 */
	function importPurchaseOrderLine($purchaseorder, $purchaseorderLine, $sequence, &$totalAmountHT, &$totalTax){
        
		$qty = str_to_float($purchaseorderLine['quantity']);
		$listprice = str_to_float($purchaseorderLine['prix_unit_ht']);
		
		//N'importe pas les lignes de frais de port à 0
		if($listprice == 0
		&& $purchaseorderLine['productcode'] === 'ZFPORT')
			return;
		
		$discount_amount = 0;
		$totalAmountHT += $qty * $listprice;
		
		$incrementOnDel = $purchaseorderLine['isproduct'] ? 1 : 0;
		
		$db = PearDatabase::getInstance();
		$query ="INSERT INTO vtiger_inventoryproductrel (id, productid, sequence_no, quantity, listprice, discount_amount, incrementondel) VALUES(?,?,?,?,?,?,?,?)";
		$qparams = array($purchaseorder->getId(), $purchaseorderLine['productid'], $sequence, $qty, $listprice, $discount_amount, $incrementOnDel);
		//$db->setDebug(true);
		$db->pquery($query, $qparams);
	}

	/**
	 * Method to process to the import of a one purchaseorder.
	 * @param $purchaseorderData : the data of the purchaseorder to import
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importOnePurchaseOrder($purchaseorderData, $importDataController) {
					
		global $log;
		
		//TODO check sizeof $purchaseorderata
		$vendor = $this->getVendor($purchaseorderData);
		if ($vendor != null) {
			$account = $vendor->getAccountRecordModel();

			if ($account != null) {
				$sourceId = $purchaseorderData[0]['sourceid'];
		
				//test sur importsourceid == $sourceId
				$query = "SELECT crmid, purchaseorderid
					FROM vtiger_purchaseordercf
					JOIN vtiger_crmentity
					    ON vtiger_purchaseordercf.purchaseorderid = vtiger_crmentity.crmid
					WHERE importsourceid = ? AND deleted = FALSE
					LIMIT 1
				";
				$db = PearDatabase::getInstance();
				$result = $db->pquery($query, array($sourceId));//$purchaseorderData[0]['subject']
				if($db->num_rows($result)){
					//already imported !!
					$row = $db->fetch_row($result, 0); 
					$entryId = $this->getEntryId("PurchaseOrder", $row['crmid']);
					foreach ($purchaseorderData as $purchaseorderLine) {
						$entityInfo = array(
							'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_SKIPPED,
							'id'		=> $entryId
						);
						
						//TODO update all with array
						$importDataController->updateImportStatus($purchaseorderLine[id], $entityInfo);
					}
				}
				else {
					$record = Vtiger_Record_Model::getCleanInstance('PurchaseOrder');
					$record->set('mode', 'create');
					$record->set('bill_street', $purchaseorderData[0]['street']);
					$record->set('bill_street2', $purchaseorderData[0]['street2']);
					$record->set('bill_street3', $purchaseorderData[0]['street3']);
					$record->set('bill_city', $purchaseorderData[0]['city']);
					$record->set('bill_code', $purchaseorderData[0]['zip']);
					$record->set('bill_country', $purchaseorderData[0]['country']);
					$record->set('subject', $purchaseorderData[0]['subject']);
					$record->set('duedate', $purchaseorderData[0]['duedate']);
					$record->set('vendor_id', $vendor->getId());
					$record->set('account_id', $account->getId());
					//$record->set('received', str_replace('.', ',', $srcRow['netht']+$srcRow['nettva']));
					//$record->set('hdnGrandTotal', $srcRow['netht']+$srcRow['nettva']);//TODO non enregistré : à cause de l'absence de ligne ?
					$record->set('typedossier', 'Facture'); //TODO
					$record->set('purchaseorderstatus', 'Validated');//TODO
					$record->set('currency_id', CURRENCY_ID);
					$record->set('conversion_rate', CONVERSION_RATE);
					$record->set('hdnTaxType', 'individual');
		                    
					$record->set('importsourceid', $sourceId);
				    
					$coupon = $this->getCoupon($purchaseorderData[0]);
					if($coupon){
						$record->set('notesid', $coupon->getId());
						$campagne = $this->getCampaign($purchaseorderData[0]['affaire_code'], $coupon);
						if($campagne)
							$record->set('campaign_no', $campagne->getId());
						
					}
					/*$campagne = self::findCampagne($srcRow, $coupon);
					if($campagne)
						$record->set('campaign_no', $campagne->getId());*/
					
					//$db->setDebug(true);
					$record->saveInBulkMode();
					$purchaseorderId = $record->getId();

					if(!$purchaseorderId){
						//TODO: manage error
						echo "<pre><code>Impossible d'enregistrer la nouvelle facture</code></pre>";
						foreach ($purchaseorderData as $purchaseorderLine) {
							$entityInfo = array(
								'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
							);
							
							//TODO update all with array
							$importDataController->updateImportStatus($purchaseorderLine[id], $entityInfo);
						}

						return false;
					}
					
					$entryId = $this->getEntryId("PurchaseOrder", $purchaseorderId);
					$sequence = 0;
					$totalAmount = 0.0;
					$totalTax = 0.0;
					foreach ($purchaseorderData as $purchaseorderLine) {
						$this->importPurchaseOrderLine($record, $purchaseorderLine, ++$sequence, $totalAmount, $totalTax);
						$entityInfo = array(
							'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_CREATED,
							'id'		=> $entryId
						);
						//TODO update all with array
						$importDataController->updateImportStatus($purchaseorderLine[id], $entityInfo);
					}
					
					$record->set('mode','edit');
					
					$purchaseorderNo = $record->getEntity()->setModuleSeqNumber("increment", $record->getModuleName());
					
					//set dates, purchaseorder_no
					$query = "UPDATE vtiger_purchaseorder
						JOIN vtiger_crmentity
							ON vtiger_crmentity.crmid = vtiger_purchaseorder.purchaseorderid
						SET purchaseorder_no = ?
						, total = ?
						, subtotal = ?
						, taxtype = ?
						, smownerid = ?
						, createdtime = ?
						, modifiedtime = ?
						, balance = total - received
						/*, purchaseorderstatus = IF(balance = 0, 'Paid', purchaseorderstatus)*/
						WHERE purchaseorderid = ?
					";
					$total = $totalAmount + $totalTax;
					$result = $db->pquery($query, array(
										  $purchaseorderNo
									    , $total
									    , $total
									    , 'individual'
									    , ASSIGNEDTO_ALL
									    , $purchaseorderData[0]['purchaseorderdate']
									    , $purchaseorderData[0]['purchaseorderdate']
									    , $purchaseorderId));
					
					$log->debug("" . basename(__FILE__) . " update imported purchaseorder (id=" . $record->getId() . ", sourceId=$sourceId , total=$total, date=" . $purchaseorderData[0]['purchaseorderdate']
						    . ", result=" . ($result ? " true" : "false"). " )");
					if( ! $result)
						$db->echoError();
						
					RSNImportSources_Utils_Helper::addPurchaseOrderReglementRelation($record, $purchaseorderData[0]['_rsnreglementsid'], 'Import Boutique');
					
					//raise trigger instead of ->save() whose need purchaseorder rows
					
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
			foreach ($purchaseorderData as $purchaseorderLine) {//TODO: remove duplicated code
				$entityInfo = array(
					'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
				);
				
				$importDataController->updateImportStatus($purchaseorderLine[id], $entityInfo);
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
			JOIN vtiger_crmentity e ON p.productid = e.crmid
			WHERE p.'.$searchKey.' = ?
			AND e.deleted = FALSE
			AND (IFNULL(p.unit_price, 0) = 0
				OR (
					IFNULL(p.taxclass, "") != ""
					AND p.taxclass != "0"
					AND IFNULL(p.glacct, "") != ""
				)
			)
			ORDER BY discontinued
			LIMIT 1';
		$result = $db->pquery($query, array($searchValue));
		
		if (!$result) {
			$db->echoError($query);
			return false;
		}
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
			JOIN vtiger_crmentity e ON s.serviceid = e.crmid
			JOIN vtiger_servicecf scf ON scf.serviceid = e.crmid
			WHERE s.'.$searchKey.' = ?
			AND e.deleted = FALSE
			AND (IFNULL(s.unit_price, 0) = 0
				OR (
					IFNULL(s.taxclass, "") != ""
					AND s.taxclass != "0"
					AND IFNULL(scf.glacct, "") != ""
				)
			)
			ORDER BY discontinued
			LIMIT 1';
		$result = $db->pquery($query, array($searchValue));
		if (!$result) {
			$db->echoError($query);
			return false;
		}
		if ($db->num_rows($result) == 1) {
			return true;
		}
		
		return ($db->num_rows($result) === 1);
	}

	/**
	 * Method that pre import a vendor.
	 * @param $vendorValues : the values of the vendor to import.
	 */
	function preImportVendor($vendorValues) {
		$vendor = new RSNImportSources_Preimport_Model($vendorValues, $this->user, 'Vendors');
		$vendor->save();
	}

	/**
	 * Method that pre import an purchaseorder.
	 *  It adone row in the temporary pre-import table by purchaseorder line.
	 * @param $purchaseorderData : the data of the purchaseorder to import.
	 */
	function preImportPurchaseOrder($purchaseorderData) {
		$purchaseorderValues = $this->getPurchaseOrderValues($purchaseorderData);
		foreach ($purchaseorderValues as $purchaseorderLine) {
			$purchaseorder = new RSNImportSources_Preimport_Model($purchaseorderLine, $this->user, 'PurchaseOrder');
			$purchaseorder->save();
		}
	}

	/**
	 * Method that retrieve a vendor.
	 * @param string $firstname : the firstname of the vendor.
	 * @param string $lastname : the lastname of the vendor.
	 * @param string $email : the mail of the vendor.
	 * @return the row data of the vendor | null if the vendor is not found.
	 */
	function getVendor($purchaseorderData) {
		$id = $purchaseorderData[0]['_vendorid'];
		if(!$id){
			$id = $this->getVendorId($purchaseorderData[0]['vendorcode']);
		}
		if($id){
			return Vtiger_Record_Model::getInstanceById($id, 'Vendors');
		}

		return null;
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
			if ($this->moveCursorToNextPurchaseOrder($fileReader)) {
				$i = 0;
				do {
					$purchaseorder = $this->getNextPurchaseOrder($fileReader);
					if ($purchaseorder != null) {
						for ($i = 0; $i < sizeof($purchaseorder['detail']); ++$i) {
							$productValues = $this->getProductValues($purchaseorder['detail'][$i]);

							if (!$this->productExist($productValues) && !$this->productIsInArray($productValues, $newProducts)) {
								array_push($newProducts, $productValues);
							} 
						}
					}
				} while ($purchaseorder != null);
			}

			$fileReader->close();

			if (sizeof($newProducts) > 0) {
				
				global $HELPDESK_SUPPORT_NAME;
				$viewer = new Vtiger_Viewer();
				$viewer->assign('FOR_MODULE', 'PurchaseOrder');
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
				if ($this->moveCursorToNextPurchaseOrder($fileReader)) {
					$i = 0;
					do {
						$purchaseorder = $this->getNextPurchaseOrder($fileReader);
						if ($purchaseorder != null) {
							$this->preImportPurchaseOrder($purchaseorder);
						}
					} while ($purchaseorder != null);

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
	 * Method that check if a string is a formatted date (DD/MM/YYYY).
	 * @param string $string : the string to check.
	 * @return boolean - true if the string is a date.
	 */
	function isDate($string) {
	//TODO do not put this function here ?
		return preg_match("/^[0-3][0-9][-\/][0-1][0-9][-\/][0-9]{4}$/", $string);//only true for french format
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
		if (sizeof($line) > 0 && is_numeric($line[0]) && $this->isDate($line[1])) {
			return true;
		}

		return false;
	}

	/**
	 * Method that move the cursor of the file reader to the beginning of the next found purchaseorder.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - false if error or if no purchaseorder found.
	 */
	function moveCursorToNextPurchaseOrder(RSNImportSources_FileReader_Reader $fileReader) {
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
	 * Method that return the information of the next first purchaseorder found in the file.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return the purchaseorder information | null if no purchaseorder found.
	 */
	function getNextPurchaseOrder(RSNImportSources_FileReader_Reader $fileReader) {
		$nextLine = $fileReader->readNextDataLine($fileReader);
		if ($nextLine != false) {
			$purchaseorder = array(
				'header' => $nextLine,
				'detail' => array());
			do {
				$cursorPosition = $fileReader->getCurentCursorPosition();
				$nextLine = $fileReader->readNextDataLine($fileReader);

				if (!$this->isRecordHeaderInformationLine($nextLine)) {
					if (!$nextLine[0] && $nextLine[2]) {
						array_push($purchaseorder['detail'], $nextLine);
					}
				} else {
					break;
				}

			} while ($nextLine != false);

			if ($nextLine != false) {
				$fileReader->moveCursorTo($cursorPosition);
			}

			return $purchaseorder;
		}

		return null;
	}
	
	/**
	 * Method that returns the formated information of a product found in the file.
	 * @param $product : the product data found in the file.
	 * @return array : the formated data of the product.
	 */
	function getProductValues($product) {
		$product = array(
			'productcode'	=> $product[4],
			'productname'	=> $product[5],
			'unit_price'	=> str_to_float($product[6]),//TTC, TODO HT
			'qty_per_unit'	=> 1,
		);
		return $product;
	}


	/**
	 * Method that return the formated information of an purchaseorder found in the file.
	 * @param $purchaseorder : the purchaseorder data found in the file.
	 * @return array : the formated data of the purchaseorder.
	 */
	function getPurchaseOrderValues($purchaseorder) {
		$purchaseorderValues = array();
		$date = $this->getMySQLDate($purchaseorder['header'][0]);
		$sourceId = $purchaseorder['header'][41];
		$numcart = explode('-',$sourceId);
		if(count($numcart) == 2)
			$numcart = $numcart[1];
		else	$numcart = null;
		$numLigne = 0;
		$purchaseorderHeader = array(
			'sourceid'		=> $sourceId,
			'numcart'		=> $numcart,
			'num_ligne'		=> $numLigne++,
			'lastname'		=> $purchaseorder['header'][46],
			'firstname'		=> $purchaseorder['header'][45],
			'email'			=> $purchaseorder['header'][22],
			'street'		=> $purchaseorder['header'][13],
			'street2'		=> $purchaseorder['header'][14],
			'street3'		=> $purchaseorder['header'][15],
			'zip'			=> $purchaseorder['header'][16],
			'city'			=> $purchaseorder['header'][17],
			'country' 		=> $purchaseorder['header'][19],
			'subject'		=> $purchaseorder['header'][11],
			'purchaseorderdate'		=> $date,
		);
		foreach ($purchaseorder['detail'] as $product) {
			$isProduct = null;
			$productCode = $product[1];
			$productName = $product[2];
			$productId = $this->getProductId($productCode, $isProduct, $productName);
			$taxrate = str_to_float($product[8])/100;
			array_push($purchaseorderValues, array_merge($purchaseorderHeader, array(
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
		return $purchaseorderValues;
	}

	/**
	 * Method called after the file is processed.
	 *  This method must be overload in the child class.
	 */
	function postPreImportData() {
		
		$this->postPreImportPurchaseOrderData();
		$this->postPreImportVendorsPurchaseOrdersData();
	}

	/**
	 * Method called after the file is processed.
	 *  This method must be overload in the child class.
	 */
	function postPreImportPurchaseOrderData() {
		$db = PearDatabase::getInstance();
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'PurchaseOrder');
		
		/* création d'un index */
		$query = "ALTER TABLE `$tableName` ADD INDEX(`sourceid`)";
		$db->pquery($query);
		
		/* Annule les factures déjà importées
		*/
		$query = "UPDATE $tableName
		JOIN  vtiger_purchaseordercf
			ON  vtiger_purchaseordercf.importsourceid = `$tableName`.sourceid
		JOIN  vtiger_purchaseorder
			ON  vtiger_purchaseorder.purchaseorderid = vtiger_purchaseordercf.purchaseorderid
		JOIN vtiger_crmentity
			ON vtiger_purchaseorder.purchaseorderid = vtiger_crmentity.crmid
		";
		$query .= " SET `$tableName`.status = ?
		, `$tableName`.recordid = vtiger_purchaseorder.purchaseorderid
		, _vendorid = vtiger_purchaseorder.vendorid";
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
	function postPreImportVendorsPurchaseOrdersData() {
		$db = PearDatabase::getInstance();
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'PurchaseOrder');
		
		/* création d'un index */
		$query = "ALTER TABLE `$tableName` ADD INDEX(`vendorcode`)";
		$db->pquery($query);
		
		/* Affecte le vendeur
		*/
		$query = "UPDATE $tableName
		JOIN  vtiger_vendorcf
			ON  vtiger_vendorcf.vendorcode = $tableName.vendorcode
		JOIN vtiger_crmentity
			ON vtiger_vendorcf.vendorid = vtiger_crmentity.crmid
		";
		$query .= " SET _vendorid = vtiger_vendorcf.vendorid";
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
		
		/* Failed for missing vendor
		*/
		$query = "UPDATE $tableName
		";
		$query .= " SET status = ?";
		$query .= "
			WHERE _vendorid IS NULL
			AND `$tableName`.status = ".RSNImportSources_Data_Action::$IMPORT_RECORD_NONE."
		";
		$result = $db->pquery($query, array(RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED));
		if(!$result){
			echo '<br><br><br><br>';
			$db->echoError($query);
			echo("<pre>$query</pre>");
			die();
		}
		
		return true;
	}
}