<?php

require_once ('modules/RSNImportSources/views/ImportFromDBCogilog.php');

// ED150831 : TODO importer quand même les factures sans contact connu car il y a les décédés qui peuvent avoir disparu de 4D

class RSNImportSources_ImportDepotsVenteFromCogilog_View extends RSNImportSources_ImportFromDBCogilog_View {

	/**
	 * Method to get the source import label to display.
	 * @return string - The label.
	 */
	public function getSource() {
		return 'LBL_COGILOG_SALESORDER';
	}


	/**
	 * Method to default max query rows for this import.
	 *  This method should be overload in the child class.
	 * @return string - the default db port.
	 */
	public function getDefaultMaxQueryRows() {
		return 30000;
	}

	/**
	 * Method to get the modules that are concerned by the import.
	 * @return array - An array containing concerned module names.
	 */
	public function getImportModules() {
		return array(/*'Contacts', */'SalesOrder');
	}

	/**
	 * Method to upload the file in the temporary location.
	 */
	function getDBQuery() {
		/* factures déjà importés */
		$query = "SELECT MAX(CAST(SUBSTR(salesorder_no,4) AS UNSIGNED)) AS codefacture_max
			FROM vtiger_salesorder
			JOIN vtiger_crmentity
				ON vtiger_salesorder.salesorderid = vtiger_crmentity.crmid
			WHERE vtiger_crmentity.deleted = 0
			AND salesorder_no LIKE 'COG%'
			AND vtiger_crmentity.deleted = 0
		";
		$db = PearDatabase::getInstance();
		//$db->setDebug(true);
		$result = $db->pquery($query, array());
		if($db->num_rows($result)){
			$row = $db->fetch_row($result, 0);
			$factMax = intval(substr($row['codefacture_max'], 2));
			$anneeMax = intval(substr($row['codefacture_max'], 0, strlen($row['codefacture_max']) - 5)) + 2000;
			echo('<pre>Dernière facture existante : ' . $anneeMax . ', n° '. $factMax .'</pre>');
		}
		else
			$factMax = false;
		
		$query = 'SELECT "bdc".id AS bdcid,  "bdc".annee, "bdc"."numero", "bdc".datepiece, "bdc".facturation, id_gtremi, id_gcomme, compteclient, paiementpropose, mention
			, netht, nettva, solde, "bdc".archive, verrouille, imprime, transformtype, transformid
			, "bdc".tssaisie, "bdc".tsmod
			, contact, "bdc".notes, ptexte1, ptexte2, ptexte3, pdate1, pdate2, pdate3, commentaires
			, affaire.code AS "affaire_code"
			, "ligne_bdc".*
			, client.CodeClient, client.NomClient
			, "bdc".num || \' \' || "bdc".voie AS "voie", "bdc".compad1, "bdc".compad2, "bdc".cp, "bdc".ville, "bdc".cedex, "bdc".pays
			, 0 as "_contactid"
			, 0 as "_productid"
			 FROM "gbdcom00002" "bdc"
			 INNER JOIN ( 
				SELECT "glbdco00002"."id_piece", "glbdco00002"."id_gprodu", "gprodu00002"."nom" AS "nom_produit", "gprodu00002"."code" AS "code_produit"
				, "codetauxtva"."taux" AS "tva_produit"
				, SUM( "quantite" ) AS "quantite"
				, SUM( "ht2" ) AS "total_ligne_ht"
				, AVG( "glbdco00002"."prix" ) AS "prix_unit_ht"
				FROM "glbdco00002"
				INNER JOIN "gprodu00002" ON "glbdco00002"."id_gprodu" = "gprodu00002"."id"
			 LEFT JOIN "gtvacg00002" AS "codetauxtva"
				ON "gprodu00002"."codetva" = "codetauxtva"."code"
				GROUP BY "glbdco00002"."id_piece", "glbdco00002"."id_gprodu", "gprodu00002"."nom", "gprodu00002"."code", "codetauxtva"."taux"
			) AS "ligne_bdc" 
				ON "ligne_bdc"."id_piece" = "bdc"."id"
			 INNER JOIN (
				SELECT id as id_gclien, code AS CodeClient, nom2 AS NomClient
				FROM "gclien00002"
			 ) "client"
				ON "client"."id_gclien" = "bdc"."id_gclien"
			 INNER JOIN gaffai00002 affaire
				ON affaire.id=bdc.id_gaffai
		';
		if(FALSE)
			$query .= ' WHERE bdc.numero = 12923
				AND bdc.annee = 2012
			';
		else {
			/* Attention à ne pas importer une facture en cours de saisie */
			$query .= ' WHERE bdc.datepiece < CURRENT_DATE 
			';
			if($factMax)
				$query .= ' AND ((bdc.numero > '.$factMax.' AND bdc.annee = '.$anneeMax.')
				OR bdc.annee > '.$anneeMax.')';
		}
		$query .= ' ORDER BY bdc.annee, bdc.numero
                    OFFSET ' . $this->getQueryLimitStart().'
					LIMIT  ' . $this->getMaxQueryRows() ;
		//echo("<pre>$query</pre>");
		return $query;
	}
	
	/**
	 * Method to get db data.
	 */
	function getDBRows() {
		$rows = parent::getDBRows();
		if(!$rows)
			return $rows;
				
		//Identifie les lignes qui sont les en-têtes de factures ou les lignes suivantes de produits
		$fieldName = '_header_';
		$this->columnName_indexes[$fieldName] = count($this->columnName_indexes);
		
		$fieldName = 'numero';
		$id_column = $this->columnName_indexes[$fieldName];
		$previous_id = false;
		$previous_row = -1;
		$new_rows = array();
		$line = 0;
		foreach($rows as $row){
			if($row[$id_column] == $previous_id)
				$row[] = false;//!_header_
			else {
				$row[] = true;//_header_
				$previous_id = $row[$id_column];
				$previous_row = $line;
			}
			$new_rows[] = $row;
			$line++;
		}
		//Supprime la dernière facture car potentiellement toutes les lignes ne sont pas fournies à cause du LIMIT
		if($previous_row > 0
		&& count($new_rows) >= max($this->getMaxQueryRows() - 200, $this->getMaxQueryRows() * 0.8)){ //en espérant qu'il n'y a pas de dernière facture de 201 lignes...
			//$skipped_rows = array_slice($new_rows, $previous_row);
			$new_rows = array_slice($new_rows, 0, $previous_row);
		}
		if(count($new_rows))
			echo "\rNouveaux dépôts-vente de Cogilog à importer : " . count($new_rows);
		return $new_rows;
	}
	
	/**
	 * Method to get the imported fields for the salesorder module.
	 * @return array - the imported fields for the salesorder module.
	 */
	function getSalesOrderFields() {
		return array(
			//header
			'sourceid',
			'reffiche',
			'lastname',
			'firstname',
			'email',
			'street',
			'street2',
			'street3',
			'pobox',
			'zip',
			'city',
			'country',
			'subject',
			'affaire_code',
			'salesorderdate',
			'sostatus',
			//lines
			'productcode',
			'productid',
			'article',
			'isproduct',
			'quantity',
			'prix_unit_ht',
			'taxrate',
			
			
			/* post pré-import */
			'_contactid',
		);
	}

	/**
	 * Method to process to the import of the salesorder module.
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importSalesOrder($importDataController) {
		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($this->user, 'SalesOrder');
		$sql = 'SELECT * FROM ' . $tableName . ' WHERE status = '. RSNImportSources_Data_Action::$IMPORT_RECORD_NONE . ' ORDER BY id';

		$result = $adb->query($sql);
		$numberOfRecords = $adb->num_rows($result);

		if ($numberOfRecords <= 0) {
			return;
		}

		$row = $adb->raw_query_result_rowdata($result, 0);
		$previousSalesOrderSubjet = $row['subject'];//tmp subject, use salesorder_no ???
		$salesorderData = array($row);

		$perf = new RSN_Performance_Helper($numberOfRecords);
		for ($i = 1; $i < $numberOfRecords; ++$i) {
			$row = $adb->raw_query_result_rowdata($result, $i);
			$salesorderSubject = $row['subject'];

			if ($previousSalesOrderSubjet == $salesorderSubject) {
				array_push($salesorderData, $row);
			} else {
				$this->importOneSalesOrder($salesorderData, $importDataController);
				$salesorderData = array($row);
				$previousSalesOrderSubjet = $salesorderSubject;
			}
			
			//perf
			$perf->tick();
			if(Import_Utils_Helper::isMemoryUsageToHigh()){
				$this->skipNextScheduledImports = true;
				$keepScheduledImport = true;
				$size = RSN_Performance_Helper::getMemoryUsage();
				echo '
<pre>
	<b> '.vtranslate('LBL_MEMORY_IS_OVER', 'Import').' : '.$size.' </b>
</pre>
';
				break;
			}
		}

		//dernière facture
		$this->importOneSalesOrder($salesorderData, $importDataController);

		$perf->terminate();

	}

	/**
	 * Method to process to the import a line of the salesorder.
	 * @param $salesorder : the concerned salesorder.
	 * @param $salesorderLine : the line to import.
	 * @param int $sequence : the line number of this salesorder.
	 */
	function importSalesOrderLine($salesorder, $salesorderLine, $sequence, &$totalAmountHT, &$totalTax){
        
		$qty = self::str_to_float($salesorderLine['quantity']);
		$listprice = self::str_to_float($salesorderLine['prix_unit_ht']);
		
		//N'importe pas les lignes de frais de port à 0
		if($listprice == 0
		&& $salesorderLine['productcode'] == 'ZFPORT')
			return;
		
		$discount_amount = 0;
		$tax = self::getTax($salesorderLine['taxrate']);
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
		//var_dump('$totalAmountHT', $totalAmountHT, "$qty * $listprice", $salesorderLine['taxrate']);
		
		$incrementOnDel = $salesorderLine['isproduct'] ? 1 : 0;
		
		$db = PearDatabase::getInstance();
		$query ="INSERT INTO vtiger_inventoryproductrel (id, productid, sequence_no, quantity, listprice, discount_amount, incrementondel, $taxName) VALUES(?,?,?,?,?,?,?,?)";
		$qparams = array($salesorder->getId(), $salesorderLine['productid'], $sequence, $qty, $listprice, $discount_amount, $incrementOnDel, $taxValue);
		//$db->setDebug(true);
		$result = $db->pquery($query, $qparams);
		if(!$result){
			$db->echoError();
			var_dump($query, $qparams);
			die();
		}
	}

	/**
	 * Method to process to the import of a one salesorder.
	 * @param $salesorderData : the data of the salesorder to import
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importOneSalesOrder($salesorderData, $importDataController) {
					
		global $log;
		
		//TODO check sizeof $salesorderata
		$contact = $this->getContact($salesorderData);
		if ($contact != null) {
			$account = $contact->getAccountRecordModel();

			if ($account != null) {
				$sourceId = $salesorderData[0]['sourceid'];
		
				//test sur salesorder_no == $sourceId
				$query = "SELECT crmid, salesorderid
					FROM vtiger_salesorder
					JOIN vtiger_crmentity
					    ON vtiger_salesorder.salesorderid = vtiger_crmentity.crmid
					WHERE salesorder_no = ? AND deleted = FALSE
					LIMIT 1
				";
				$db = PearDatabase::getInstance();
				$result = $db->pquery($query, array($sourceId));//$salesorderData[0]['subject']
				if($db->num_rows($result)){
					//already imported !!
					$row = $db->fetch_row($result, 0); 
					$entryId = $this->getEntryId("SalesOrder", $row['crmid']);
					foreach ($salesorderData as $salesorderLine) {
						$entityInfo = array(
							'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_SKIPPED,
							'id'		=> $entryId
						);
						
						//TODO update all with array
						$importDataController->updateImportStatus($salesorderLine[id], $entityInfo);
					}
				}
				else {
					$record = Vtiger_Record_Model::getCleanInstance('SalesOrder');
					$record->set('mode', 'create');
					$record->set('bill_street', $salesorderData[0]['street']);
					$record->set('bill_street2', $salesorderData[0]['street2']);
					$record->set('bill_street3', $salesorderData[0]['street3']);
					$record->set('bill_city', $salesorderData[0]['city']);
					$record->set('bill_code', $salesorderData[0]['zip']);
					$record->set('bill_country', $salesorderData[0]['country']);
					$record->set('subject', $salesorderData[0]['subject']);
					//$record->set('receivedcomments', $srcRow['paiementpropose']);
					//$record->set('description', $srcRow['notes']);
					$record->set('salesorderdate', $salesorderData[0]['salesorderdate']);
					$record->set('duedate', $salesorderData[0]['salesorderdate']);
					$record->set('contact_id', $contact->getId());
					$record->set('account_id', $account->getId());
					//$record->set('received', str_replace('.', ',', $srcRow['netht']+$srcRow['nettva']));
					//$record->set('hdnGrandTotal', $srcRow['netht']+$srcRow['nettva']);//TODO non enregistré : à cause de l'absence de ligne ?
					$record->set('sostatus', $salesorderData[0]['sostatus']);//TODO
					$record->set('currency_id', CURRENCY_ID);
					$record->set('conversion_rate', CONVERSION_RATE);
					$record->set('hdnTaxType', 'individual');
						
					//$db->setDebug(true);
					$record->saveInBulkMode();
					$salesorderId = $record->getId();

					if(!$salesorderId){
						//TODO: manage error
						echo "<pre><code>Impossible d'enregistrer le nouveau dépôt-vente </code></pre>";
						foreach ($salesorderData as $salesorderLine) {
							$entityInfo = array(
								'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
							);
							
							//TODO update all with array
							$importDataController->updateImportStatus($salesorderLine[id], $entityInfo);
						}

						return false;
					}
					
					
					$entryId = $this->getEntryId("SalesOrder", $salesorderId);
					$sequence = 0;
					$totalAmount = 0.0;
					$totalTax = 0.0;
					foreach ($salesorderData as $salesorderLine) {
						$this->importSalesOrderLine($record, $salesorderLine, ++$sequence, $totalAmount, $totalTax);
						$entityInfo = array(
							'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_CREATED,
							'id'		=> $entryId
						);
						//TODO update all with array
						$importDataController->updateImportStatus($salesorderLine[id], $entityInfo);
					}
					
					$record->set('mode','edit');
					//This field is not manage by save()
					$record->set('salesorder_no',$sourceId);
					//set salesorder_no
					$query = "UPDATE vtiger_salesorder
						JOIN vtiger_crmentity
							ON vtiger_crmentity.crmid = vtiger_salesorder.salesorderid
						SET salesorder_no = ?
						, total = ?
						, subtotal = ?
						, taxtype = ?
						, smownerid = ?
						, createdtime = ?
						, modifiedtime = ?
						WHERE salesorderid = ?
					";
					$total = $totalAmount + $totalTax;
					$result = $db->pquery($query, array($sourceId
									    , $total
									    , $total
									    , 'individual'
									    , ASSIGNEDTO_ALL
									    , $salesorderData[0]['salesorderdate']
									    , $salesorderData[0]['salesorderdate']
									    , $salesorderId));
					
					$log->debug("" . basename(__FILE__) . " update imported salesorder (id=" . $record->getId() . ", sourceId=$sourceId , total=$total, date=" . $salesorderData[0]['salesorderdate']
						    . ", result=" . ($result ? " true" : "false"). " )");
					if( ! $result)
						$db->echoError();
						
						
					//raise trigger instead of ->save() whose need salesorder rows
					/* ED150831 Migration : is bulk mode
					$log->debug("BEFORE " . basename(__FILE__) . " raise event handler(" . $record->getId() . ", " . $record->get('mode') . " )");
					//raise event handler
					$record->triggerEvent('vtiger.entity.aftersave');
					$log->debug("AFTER " . basename(__FILE__) . " raise event handler");
					*/
					
					return $record; 
				}
			} else {
				//TODO: manage error
				echo "<pre><code>Unable to find Account</code></pre>";
			}
		} else {
			foreach ($salesorderData as $salesorderLine) {//TODO: remove duplicated code
				$entityInfo = array(
					'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
				);
				
				$importDataController->updateImportStatus($salesorderLine[id], $entityInfo);
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
		$query = 'SELECT productid
			FROM vtiger_products p
			JOIN vtiger_crmentity e on p.productid = e.crmid
			WHERE p.productcode = ? AND e.deleted = FALSE LIMIT 1';
		$result = $db->pquery($query, array($product['productcode']));

		if ($db->num_rows($result) == 1) {
			return true;
		}

		$query = 'SELECT 1
			FROM vtiger_service s
			JOIN vtiger_crmentity e on s.serviceid = e.crmid
			WHERE s.productcode = ?
			AND e.deleted = FALSE
			LIMIT 1';
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
	 * Method that pre import an salesorder.
	 *  It adone row in the temporary pre-import table by salesorder line.
	 * @param $salesorderData : the data of the salesorder to import.
	 */
	function preImportSalesOrder($salesorderData) {
		$salesorderValues = $this->getSalesOrderValues($salesorderData);
		foreach ($salesorderValues as $salesorderLine) {
			$salesorder = new RSNImportSources_Preimport_Model($salesorderLine, $this->user, 'SalesOrder');
			$salesorder->save();
		}
	}

	/**
	 * Method that retrieve a contact.
	 * @param string $ref4d : ref4d ou data array
	 * @return the row data of the contact | null if the contact is not found.
	 */
	function getContactId($ref4d) {
		$id = false;
		if(is_array($ref4d)){ //$ref4d is $rsnprelvirementsData
			if($ref4d[0]['_contactid'])
				$id = $ref4d[0]['_contactid'];
			else{
				$ref4d = $ref4d[0]['reffiche'];
			}
		}
		if(!$id)
			$id = $this->getContactIdFromRef4D($ref4d);

		return $id;
	}
	
	/**
	 * Method that retrieve a contact.
	 * @param string $ref4d : ref4d ou data array
	 * @return the row data of the contact | null if the contact is not found.
	 */
	function getContact($contact_no) {
		$id = $this->getContactId($contact_no);
		if($id){
			return Vtiger_Record_Model::getInstanceById($id, 'Contacts');
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
			if ($product['productcode'] == $productArray[$i]['productcode']) {
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
			if ($this->moveCursorToNextSalesOrder($fileReader)) {
				$i = 0;
				do {
					$salesorder = $this->getNextSalesOrder($fileReader);
					if ($salesorder != null) {
						for ($i = 0; $i < sizeof($salesorder['detail']); ++$i) {
							$productValues = $this->getProductValues($salesorder['detail'][$i]);

							if (!$this->productExist($productValues) && !$this->productIsInArray($productValues, $newProducts)) {
								array_push($newProducts, $productValues);
							} 
						}
					}
				} while ($salesorder != null);
			}

			$fileReader->close();

			if (sizeof($newProducts) > 0) {
				global $HELPDESK_SUPPORT_NAME;
				$viewer = new Vtiger_Viewer();
				$viewer->assign('FOR_MODULE', 'SalesOrder');
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
				if ($this->moveCursorToNextSalesOrder($fileReader)) {
					$i = 0;
					do {
						$salesorder = $this->getNextSalesOrder($fileReader);
						if ($salesorder != null) {
							//$this->checkContact($salesorder);
							$this->preImportSalesOrder($salesorder);
						}
						$i++;
					} while ($salesorder != null);
				}
				$fileReader->close();
				
				echo('preImportSalesOrder count : ' . print_r($i, true));

				return true;
			} else {
				//TODO: manage error
				echo "<code>le fichier n'a pas pu être ouvert...</code>";
			}
		}
		else
				echo "Des produits manquants empêchent l'importation des factures.";
		
		return false;
	}

	/**
	 * Method that check if a string is a formatted date (DD/MM/YYYY).
	 * @param string $string : the string to check.
	 * @return boolean - true if the string is a date.
	 */
	function isDate($string) {
	//TODO do not put this function here ?
		return preg_match("/^20[0-9]{2}[-\/][0-1][0-9][-\/][0-3][0-9]$/", $string);//only true for french format
	}
	/**
	 * Method that returns a formatted date for mysql (Y-m-d).
	 * @param string $string : the string to format.
	 * @return string - formated date.
	 */
	function getMySQLDate($string) {
		return $string;
		//$dateArray = preg_split('/[-\/]/', $string);
		//return $dateArray[2] . '-' . $dateArray[1] . '-' . $dateArray[0];
	}

	/**
	 * Method that check if a line of the file is a client information line.
	 *  It assume that the line is a client information line only and only if the first data is a date.
	 * @param array $line : the data of the file line.
	 * @return boolean - true if the line is a client information line.
	 */
	function isRecordHeaderInformationLine($line) {
		if (sizeof($line) > 0 && $line[$this->columnName_indexes['_header_']] && $this->isDate($line[$this->columnName_indexes['datepiece']])) {
			return true;
		}
		return false;
	}

	/**
	 * Method that move the cursor of the file reader to the beginning of the next found salesorder.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - false if error or if no salesorder found.
	 */
	function moveCursorToNextSalesOrder(RSNImportSources_FileReader_Reader $fileReader) {
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
	 * Method that return the information of the next first salesorder found in the file.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return the salesorder information | null if no salesorder found.
	 */
	function getNextSalesOrder(RSNImportSources_FileReader_Reader $fileReader) {
		$nextLine = $fileReader->readNextDataLine($fileReader);
		if ($nextLine != false) {
			$salesorder = array(
				'salesorderInformations' => $nextLine,
				'detail' => array($nextLine));
			do {
				$cursorPosition = $fileReader->getCurentCursorPosition();
				$nextLine = $fileReader->readNextDataLine($fileReader);

				if (!$this->isRecordHeaderInformationLine($nextLine)) {
					if ($this->isDate($nextLine[$this->columnName_indexes['datepiece']])) {
						array_push($salesorder['detail'], $nextLine);
					}
				} else {
					break;
				}

			} while ($nextLine != false);

			if ($nextLine != false) {
				$fileReader->moveCursorTo($cursorPosition);
			}

			return $salesorder;
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
			'productcode'	=> $product[$this->columnName_indexes['code_produit']],
			'productname'	=> $product[$this->columnName_indexes['nom_produit']],
			'unit_price'	=> self::str_to_float($product[$this->columnName_indexes['prixvente_produit']]),//TTC, TODO HT
			'qty_per_unit'	=> 1,
			'taxrate'	=> self::str_to_float($product[$this->columnName_indexes['tva_produit']]),
			'rsnsectionanal' => $product[$this->columnName_indexes['section_produit']],
			'qtyinstock' => $product[$this->columnName_indexes['stock_produit']],
			'glacct' => $product[$this->columnName_indexes['glacct']],
			'discontinued' => $product[$this->columnName_indexes['indisponible']], //TODO n'est ce pas l'inverse ?
		);
		return $product;
	}


	/**
	 * Method that return the formated information of an salesorder found in the file.
	 * @param $salesorder : the salesorder data found in the file.
	 * @return array : the formated data of the salesorder.
	 */
	function getSalesOrderValues($salesorder) {
	//TODO end implementation of this method
		$salesorderValues = array();
		$salesorderInformations = $salesorder['salesorderInformations'];
		$date = $this->getMySQLDate($salesorderInformations[$this->columnName_indexes['datepiece']]);
		$salesorderHeader = array(
			'sourceid'		=> 'COG' . substr($date, 2, 2) . str_pad ($salesorderInformations[$this->columnName_indexes['numero']], 5, '0', STR_PAD_LEFT),
			'reffiche' 		=> $salesorderInformations[$this->columnName_indexes['codeclient']],
			'lastname'		=> $salesorderInformations[$this->columnName_indexes['nomclient']],
			'firstname'		=> '',
			'email'			=> $salesorderInformations[$this->columnName_indexes['email']],
			'street'		=> $salesorderInformations[$this->columnName_indexes['num']]. ' ' .$salesorderInformations[$this->columnName_indexes['voie']],//$srcRow['num']. ' ' .$srcRow['voie']
			'street2'	=> $salesorderInformations[$this->columnName_indexes['nom1']],
			'street3'	=> $salesorderInformations[$this->columnName_indexes['compad1']],
			'pobox'		=> $salesorderInformations[$this->columnName_indexes['compad2']],
			'zip'		=> $salesorderInformations[$this->columnName_indexes['cp']],
			'city'		=> $salesorderInformations[$this->columnName_indexes['ville']],
			'country' 	=> $salesorderInformations[$this->columnName_indexes['pays']],
			'subject'		=> $salesorderInformations[$this->columnName_indexes['nomclient']].' / '. $salesorderInformations[$this->columnName_indexes['datepiece']],
			'salesorderdate'		=> $date,
			'affaire_code' 	=> $salesorderInformations[$this->columnName_indexes['affaire_code']],
			'sostatus' 	=> $salesorderInformations[$this->columnName_indexes['archive']] == 't' ? 'Archived' : 'Approved',
			
		);
		$codeClient = preg_replace('/^0+/', '', $salesorderHeader['reffiche']);
		$regexp = '/^0*'.$codeClient.'\s(.+)\/(\w+-)?\d+\*.*$/';
		if(preg_match($regexp, $salesorderHeader['lastname'])){
			$nomClient = preg_replace($regexp,'$1', $salesorderHeader['lastname']);
			$salesorderHeader['lastname'] = $nomClient;
		}
		$salesorderHeader['reffiche'] = $codeClient;
		
		//var_dump($this->columnName_indexes);
		
		foreach ($salesorder['detail'] as $product) {
			//var_dump($product);
			$isProduct = null;
			$product_name = '';
			$taxrate = self::str_to_float($product[$this->columnName_indexes['tva_produit']])/100;
			$qty = self::str_to_float($product[$this->columnName_indexes['quantite']]);
			array_push($salesorderValues, array_merge($salesorderHeader, array(
				'productcode'	=> $product[$this->columnName_indexes['code_produit']],
				'productid'	=> $this->getProductId($product[$this->columnName_indexes['code_produit']], $isProduct, $product_name),
				'quantity'	=> $qty,
				'article'	=> $product_name,
				'prix_unit_ht'	=> self::str_to_float($product[$this->columnName_indexes['total_ligne_ht']]) / $qty /* / (1 + $taxrate) */,
				'isproduct'	=> $isProduct,
				'taxrate'	=> self::str_to_float($product[$this->columnName_indexes['tva_produit']]),
            
			)));
		}
		//var_dump($salesorderValues);
		return $salesorderValues;
	}

	/**
	 * Method called after the file is processed.
	 *  This method must be overload in the child class.
	 */
	function postPreImportData() {
		// Pré-identifie les contacts//
			
		RSNImportSources_Utils_Helper::setPreImportDataContactIdByRef4D(
			$this->user,
			'SalesOrder',
			'reffiche',
			'_contactid',
			/*$changeStatus*/ false
		);
	
		RSNImportSources_Utils_Helper::skipPreImportDataForMissingContactsByRef4D(
			$this->user,
			'SalesOrder',
			'_contactid'
		);
	}
}