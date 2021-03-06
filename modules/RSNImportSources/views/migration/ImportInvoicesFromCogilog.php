<?php

require_once ('modules/RSNImportSources/views/ImportFromDBCogilog.php');

// ED150831 : TODO importer quand même les factures sans contact connu car il y a les décédés qui peuvent avoir disparu de 4D

/*
SELECT facture.datepiece, facture.numero, facture.nom2, facture.compteclient , ligne_fact.*
			FROM gfactu00002 facture
			JOIN gclien00002 cl
				ON facture.id_gclien = cl.id
			JOIN glfact00002 ligne_fact
				ON ligne_fact.id_piece = facture.id
			LEFT JOIN gaffai00002 affaire
				ON affaire.id = facture.id_gaffai
			LEFT JOIN "gprodu00002" AS "produit"
				ON "ligne_fact"."id_gprodu" = "produit"."id"
			LEFT JOIN "gtprod00002" AS "famille"
				ON "famille"."id" = "produit"."id_gtprod"
			LEFT JOIN "gtvacg00002" AS "codetauxtva"
				ON "produit"."codetva" = "codetauxtva"."code"
		 WHERE "produit"."id" IS NULL
AND ligne_fact.nom  IS NOT NULL
AND ligne_fact.nom <> ''
AND facture.compteclient NOT IN ( '411DEP', '411dep', '411ATEL')
ORDER BY  facture.datepiece DESC
LIMIT 1000
*/


class RSNImportSources_ImportInvoicesFromCogilog_View extends RSNImportSources_ImportFromDBCogilog_View {

	/**
	 * Method to get the source import label to display.
	 * @return string - The label.
	 */
	public function getSource() {
		return 'LBL_COGILOG_INVOICES';
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
		return array(/*'Contacts', */'Invoice');
	}

	/**
	 * Method to upload the file in the temporary location.
	 */
	function getDBQuery() {
		/* factures déjà importés */
		$query = "SELECT MAX(CAST(SUBSTR(invoice_no,4) AS UNSIGNED)) AS codefacture_max
			FROM vtiger_invoice
			JOIN vtiger_crmentity
				ON vtiger_invoice.invoiceid = vtiger_crmentity.crmid
			WHERE vtiger_crmentity.deleted = 0
			AND invoice_no LIKE 'COG%'
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
		
		$query = 'SELECT cl.code AS CodeClient, cl.nom2 AS NomClient,
			facture.*,
			affaire.code AS affaire_code, affaire.nom AS affaire_nom
			'/*, "ligne_fact"."prix" AS "prix_unit_ht"*/.'
			, CASE WHEN ( "produit"."codetva" = 0 ) THEN "ligne_fact"."prixttc" ELSE "ligne_fact"."prixttc" / ( 1 + "codetauxtva"."taux"/100) END  AS "prix_unit_ht"
			, "ligne_fact"."ht2" AS "total_ligne_ht"
			, "ligne_fact"."remise" AS "remise_ligne"
			'/*, ROUND( CAST( ( "ligne_fact"."quantite" * "ligne_fact"."prixttc" * ( 100 - "ligne_fact"."remise" ) / 100 ) AS NUMERIC ), 2 ) AS "montant_ligne"*/.'
			, ROUND( CAST( ( "ligne_fact"."quantite" * ( CASE WHEN ( "produit"."codetva" = 0 ) THEN "produit"."achat" ELSE "produit"."achat" * ( 1 + ( "codetauxtva"."taux" / 100 ) ) END ) ) AS NUMERIC ), 2 ) AS "montant_achat"
			, ( "ligne_fact"."quantite" ) AS "quantite", CASE WHEN ( "produit"."codetva" = 0 ) THEN "produit"."achat" ELSE "produit"."achat" * ( 1 + ( "codetauxtva"."taux" / 100 ) ) END AS "prixachatttc"
			, "famille"."nom" AS "produit_famille"
			, "ligne_fact"."position" AS "position_ligne"
			, "produit"."id" AS "id_produit"
			, "produit"."nom" AS "nom_produit"
			, "produit"."code" AS "code_produit"
			, "produit"."gererstock" AS "gererstock"
			, "produit"."stockdivers" AS "stockdivers"
			, "produit"."prix" AS "prixvente_produit"
			, "produit"."stockqte" AS "stock_produit"
			, "produit"."compte" AS "compte_produit"
			, "produit"."section" AS "section_produit"
			, "produit"."codetva" AS "tva_produit"
			, "produit"."indisponible" AS "indisponible"
			, "codetauxtva"."taux" AS "taux_tva"
			, "facture"."compteclient" AS "compteclient"
			, "facture"."paiementpropose" AS "paiementpropose"
			, "facture"."solde" AS "solde"
			FROM gfactu00002 facture
			JOIN gclien00002 cl
				ON facture.id_gclien = cl.id
			JOIN glfact00002 ligne_fact
				ON ligne_fact.id_piece = facture.id
			JOIN gaffai00002 affaire
				ON affaire.id = facture.id_gaffai
			JOIN "gprodu00002" AS "produit"
				ON "ligne_fact"."id_gprodu" = "produit"."id"
			JOIN "gtprod00002" AS "famille"
				ON "famille"."id" = "produit"."id_gtprod"
			LEFT JOIN "gtvacg00002" AS "codetauxtva"
				ON "produit"."codetva" = "codetauxtva"."code"
		';
		if(true){
			$query .= ' WHERE (
				facture.numero IN ( 10106, 10108, 10107, 10109, 10110, 10111, 7031, 6774, 5958, 3126, 2960, 2387, 2334, 2359, 2072, 2072, 2073, 2004, 1419, 1373, 1274, 1251, 1221, 874, 514, 524, 512 )
					AND annee = 2015
				OR
				facture.numero IN ( 7948, 7946, 7298, 6453, 6452, 1926, 1505, 1274, 1241 )
					AND annee = 2014
				)
			';
		} else {
			/* Attention à ne pas importer une facture en cours de saisie */
			$query .= ' WHERE facture.datepiece < CURRENT_DATE 
			';
			if($factMax)
				$query .= ' AND ((facture.numero > '.$factMax.' AND facture.annee = '.$anneeMax.')
				OR facture.annee > '.$anneeMax.')';
		}
		$query .= ' ORDER BY facture.annee, facture.numero, position_ligne ASC
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
			echo "\rNouvelles factures de Cogilog à importer : " . count($new_rows);
		return $new_rows;
	}
	
	///**
	// * Method to get the imported fields for the contact module.
	// * @return array - the imported fields for the contact module.
	// */
	//public function getContactsFields() {
	//	return array(
	//		'reffiche',
	//		'lastname',
	//		'firstname',
	//		'email',
	//		'mailingstreet',
	//		'mailingstreet2',
	//		'mailingstreet3',
	//		'mailingpobox',
	//		'mailingzip',
	//		'mailingcity',
	//		'mailingcountry',
	//		'phone',
	//		'mobile',
	//		'contacttype',
	//		'leadsource',
	//	);
	//}

	/**
	 * Method to get the imported fields for the invoice module.
	 * @return array - the imported fields for the invoice module.
	 */
	function getInvoiceFields() {
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
			'invoicedate',
			//lines
			'productcode',
			'productid',
			'article',
			'isproduct',
			'quantity',
			'prix_unit_ht',
			'taxrate',
			'modereglement',
			'solde',
			'remise_ligne',
			
			/* post pré-import */
			'_receivedcomments',
			'_contactid',
		);
	}

	/**
	 * Method to process to the import of the invoice module.
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importInvoice($importDataController) {
		global $VTIGER_BULK_SAVE_MODE;
		$VTIGER_BULK_SAVE_MODE = true;
		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($this->user, 'Invoice');
		$sql = 'SELECT * FROM ' . $tableName . ' WHERE status = '. RSNImportSources_Data_Action::$IMPORT_RECORD_NONE . ' ORDER BY id';

		$result = $adb->query($sql);
		$numberOfRecords = $adb->num_rows($result);

		if ($numberOfRecords <= 0) {
			return;
		}

		$row = $adb->raw_query_result_rowdata($result, 0);
		$previousInvoiceNo = $row['sourceid'];//tmp subject, use invoice_no ???
		$invoiceData = array($row);

		$perf = new RSN_Performance_Helper($numberOfRecords);
		for ($i = 1; $i < $numberOfRecords; ++$i) {
			$row = $adb->raw_query_result_rowdata($result, $i);
			$invoiceNo = $row['sourceid'];

			if ($previousInvoiceNo == $invoiceNo) {
				array_push($invoiceData, $row);
			} else {
				$this->importOneInvoice($invoiceData, $importDataController);
				$invoiceData = array($row);
				$previousInvoiceNo = $invoiceNo;
			}
			
			//perf
			$perf->tick();
			if(Import_Utils_Helper::isMemoryUsageToHigh()){
				$this->skipNextScheduledImports = true;
				$keepScheduledImport = true;
				break;
			}
		}

		//dernière facture
		$this->importOneInvoice($invoiceData, $importDataController);

		$perf->terminate();

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
		
				//test sur invoice_no == $sourceId
				$query = "SELECT crmid, invoiceid
					FROM vtiger_invoice
					JOIN vtiger_crmentity
					    ON vtiger_invoice.invoiceid = vtiger_crmentity.crmid
					WHERE invoice_no = ? AND deleted = FALSE
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
					//$record->set('description', $srcRow['notes']);
					$record->set('invoicedate', $invoiceData[0]['invoicedate']);
					$record->set('duedate', $invoiceData[0]['invoicedate']);
					$record->set('contact_id', $contact->getId());
					$record->set('account_id', $account->getId());
					//$record->set('received', str_replace('.', ',', $srcRow['netht']+$srcRow['nettva']));
					//$record->set('hdnGrandTotal', $srcRow['netht']+$srcRow['nettva']);//TODO non enregistré : à cause de l'absence de ligne ?
					$record->set('typedossier', 'Facture'); //TODO
					/*if($invoiceData[0]['solde'] == 0)
						$record->set('invoicestatus', 'Paid');
					else
						$record->set('invoicestatus', 'Approved');//TODO*/
					$record->set('invoicestatus', 'Compta');
					
					$record->set('receivedmoderegl', $invoiceData[0]['modereglement']);
					$record->set('receivedcomments', $srcRow['_receivedcomments']);
					$record->set('currency_id', CURRENCY_ID);
					$record->set('conversion_rate', CONVERSION_RATE);
					$record->set('hdnTaxType', 'individual');
		                    
					$record->set('sent2compta', $invoiceData[0]['invoicedate']);//TODO field displaytype === 2
					
					//Coupon et campagne
					$coupon = $this->getCoupon($invoiceData[0]['affaire_code']);
					if($coupon){
						$record->set('notesid', $coupon->getId());
						$campagne = $this->getCampaign($invoiceData[0]['affaire_code'], $coupon);
						if($campagne)
							$record->set('campaign_no', $campagne->getId());
						
					}
					//Coupon introuvable dans la Matrice
					//TODO log 
					elseif($invoiceData[0]['affaire_code'])
						$record->set('description', 'Code affaire : ' + $invoiceData[0]['affaire_code']);
						
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
					//This field is not manage by save()
					// et ça fout la merde
					//$record->set('invoice_no',$sourceId);
					//$record->set('received', $totalAmount - $invoiceData[0]['solde']);
					//$record->save();
					
					//set invoice_no
					$query = "UPDATE vtiger_invoice
						JOIN vtiger_invoicecf
							ON vtiger_invoice.invoiceid = vtiger_invoicecf.invoiceid
						JOIN vtiger_crmentity
							ON vtiger_crmentity.crmid = vtiger_invoice.invoiceid
						SET invoice_no = ?
						, total = ?
						, subtotal = ?
						, taxtype = ?
						, received = ?
						, sent2compta = ?
						, smownerid = ?
						, createdtime = ?
						, modifiedtime = ?
						WHERE vtiger_crmentity.crmid = ?
					";
					$total = round($totalAmount + $totalTax,2);
					$result = $db->pquery($query, array($sourceId
									    , $total
									    , $total
									    , 'individual'
									    , $total - $invoiceData[0]['solde']
									    , $invoiceData[0]['invoicedate']
									    , ASSIGNEDTO_ALL
									    , $invoiceData[0]['invoicedate']
									    , $invoiceData[0]['invoicedate']
									    , $invoiceId));
					
					$log->debug("" . basename(__FILE__) . " update imported invoice (id=" . $record->getId() . ", sourceId=$sourceId , total=$total, date=" . $invoiceData[0]['invoicedate']
						    . ", result=" . ($result ? " true" : "false"). " )");
					if( ! $result)
						$db->echoError();
						
					//raise trigger instead of ->save() whose need invoice rows
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
		&& $invoiceLine['productcode'] == 'ZFPORT')
			return;
		
		$discount_amount = 0;
		$discount_percent = str_to_float($invoiceLine['remise_ligne']);
		$amountHT = $qty * $listprice;
		$tax = self::getTax($invoiceLine['taxrate']);
		if($tax){
			$taxName = $tax['taxname'];
			$taxValue = $tax['percentage'];
			$amountTTC = $amountHT * (1 + $taxValue/100);
			$taxAmount = $amountTTC - $amountHT;
			//var_dump('$totalTax', $totalTax, "$qty * $listprice * ($taxValue/100)");
		}
		else {
			$taxName = 'tax1';
			$taxValue = null;
			$taxAmount = 0.0;
			$amountTTC = $amountHT;
		}
		$totalTax += $taxAmount * (1 - $discount_percent/100);
		$totalAmountHT += $amountHT * (1 - $discount_percent/100);
		$listprice = ($amountTTC - $taxAmount) / $qty;
		//var_dump('$totalAmountHT', $totalAmountHT, "$qty * $listprice", $invoiceLine['taxrate']);
		
		$incrementOnDel = $invoiceLine['isproduct'] ? 1 : 0;
		
		$db = PearDatabase::getInstance();
		$query ="INSERT INTO vtiger_inventoryproductrel (id, productid, sequence_no, quantity, listprice, discount_percent, discount_amount, incrementondel, $taxName)
			VALUES(?,?,?,?,?,?,?,?,?)";
		$qparams = array($invoice->getId(), $invoiceLine['productid'], $sequence, $qty, $listprice, $discount_percent, $discount_amount, $incrementOnDel, $taxValue);
		//$db->setDebug(true);
		$db->pquery($query, $qparams);
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

//	/**
//	 * Method that retrieve a contact id.
//	 * @param string $contact_no : the contact number
//	 * @return the id of the contact | null if the contact is not found.
//	 */
//	function getContactId($contact_no) {
//		$query = "SELECT crmid
//			FROM vtiger_contactdetails
//                        JOIN vtiger_crmentity
//                            ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
//			WHERE deleted = FALSE
//			AND contact_no = CONCAT('C', ?)
//			LIMIT 1
//		";
//		$db = PearDatabase::getInstance();
//		
//		$result = $db->pquery($query, array($contact_no));
//
//		if($db->num_rows($result)){
//			return $db->query_result($result, 0, 0);
//		}
//
//		return null;
//	}
//	/**
//	 * Method that pre-import a contact if he does not exist in database.
//	 * @param $invoice : the invoice data.
//	 */
//	function checkContact($invoice) {
//		$contactData = $this->getContactValues($invoice['invoiceInformations']);
//		
//		$id = $this->getContactId($contactData['reffiche']);
//		
//		if(!$id){
//			$this->preImportContact($contactData);
//		}
//	}

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
							//$this->checkContact($invoice);
							$this->preImportInvoice($invoice);
						}
						$i++;
					} while ($invoice != null);
				}
				$fileReader->close();
				
				echo('preImportInvoice count : ' . print_r($i, true));

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
				'invoiceInformations' => $nextLine,
				'detail' => array($nextLine));
			do {
				$cursorPosition = $fileReader->getCurentCursorPosition();
				$nextLine = $fileReader->readNextDataLine($fileReader);

				if (!$this->isRecordHeaderInformationLine($nextLine)) {
					if ($this->isDate($nextLine[$this->columnName_indexes['datepiece']])) {
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
		$this->erreurSaisieCodeClient($invoiceInformations);
		$contactMapping = array(
			'reffiche' 		=> $invoiceInformations[$this->columnName_indexes['codeclient']],
			'lastname'		=> $invoiceInformations[$this->columnName_indexes['nomclient']],
			'firstname'		=> '',
			'email'			=> $invoiceInformations[$this->columnName_indexes['email']],
			'mailingstreet'		=> $invoiceInformations[$this->columnName_indexes['num']]. ' ' .$invoiceInformations[$this->columnName_indexes['voie']],//$srcRow['num']. ' ' .$srcRow['voie']
			'mailingstreet2'	=> $invoiceInformations[$this->columnName_indexes['nom1']],
			'mailingstreet3'	=> $invoiceInformations[$this->columnName_indexes['compad1']],
			'mailingpobox'		=> $invoiceInformations[$this->columnName_indexes['compad2']],
			'mailingzip'		=> $invoiceInformations[$this->columnName_indexes['cp']],
			'mailingcity'		=> $invoiceInformations[$this->columnName_indexes['ville']],
			'mailingcountry' 	=> $invoiceInformations[$this->columnName_indexes['pays']],
			'phone'			=> '',
			'mobile'		=> '',
			'contacttype'		=> 'Boutique',
			'leadsource'		=> 'BOUTIQUE',
			);
		$codeClient = preg_replace('/^0+/', '', $contactMapping['reffiche']);
		$regexp = '/^0*'.$codeClient.'\s(.+)\/(\w+-)?\d+\*.*$/';
		if(preg_match($regexp, $contactMapping['lastname'])){
			$nomClient = preg_replace($regexp,'$1', $contactMapping['lastname']);
			$contactMapping['lastname'] = $nomClient;
		}
		$contactMapping['reffiche'] = $codeClient;
		return $contactMapping;
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
			'unit_price'	=> str_to_float($product[$this->columnName_indexes['prixvente_produit']]),//TTC, TODO HT
			'qty_per_unit'	=> 1,
			'taxrate'	=> str_to_float($product[$this->columnName_indexes['tva_produit']]),
			'rsnsectionanal' => $product[$this->columnName_indexes['section_produit']],
			'qtyinstock' => $product[$this->columnName_indexes['stock_produit']],
			'glacct' => $product[$this->columnName_indexes['glacct']],
			'discontinued' => $product[$this->columnName_indexes['indisponible']], //TODO n'est ce pas l'inverse ?
			'remise_ligne'	=> str_to_float($product[$this->columnName_indexes['remise_ligne']]),
		);
		return $product;
	}


	/**
	 * Method that return the formated information of an invoice found in the file.
	 * @param $invoice : the invoice data found in the file.
	 * @return array : the formated data of the invoice.
	 */
	function getInvoiceValues($invoice) {
		$invoiceInformations = $invoice['invoiceInformations'];
		$this->erreurSaisieCodeClient($invoiceInformations);
		$invoiceValues = array();
		$date = $this->getMySQLDate($invoiceInformations[$this->columnName_indexes['datepiece']]);
		$receivedcomments = null;
		$invoiceHeader = array(
			'sourceid'		=> 'COG' . substr($date, 2, 2) . str_pad ($invoiceInformations[$this->columnName_indexes['numero']], 5, '0', STR_PAD_LEFT),
			'reffiche' 		=> $invoiceInformations[$this->columnName_indexes['codeclient']],
			'lastname'		=> $invoiceInformations[$this->columnName_indexes['nomclient']],
			'firstname'		=> '',
			'email'			=> $invoiceInformations[$this->columnName_indexes['email']],
			'street'		=> $invoiceInformations[$this->columnName_indexes['num']]. ' ' .$invoiceInformations[$this->columnName_indexes['voie']],//$srcRow['num']. ' ' .$srcRow['voie']
			'street2'	=> $invoiceInformations[$this->columnName_indexes['nom1']],
			'street3'	=> $invoiceInformations[$this->columnName_indexes['compad1']],
			'pobox'		=> $invoiceInformations[$this->columnName_indexes['compad2']],
			'zip'		=> $invoiceInformations[$this->columnName_indexes['cp']],
			'city'		=> $invoiceInformations[$this->columnName_indexes['ville']],
			'country' 	=> $invoiceInformations[$this->columnName_indexes['pays']],
			'subject'		=> $invoiceInformations[$this->columnName_indexes['nomclient']],
			'invoicedate'		=> $date,
			'affaire_code' 	=> $invoiceInformations[$this->columnName_indexes['affaire_code']],
			'modereglement' => $this->getModeReglement($invoiceInformations[$this->columnName_indexes['compteclient']], $invoiceInformations[$this->columnName_indexes['paiementpropose']], $receivedcomments),
			'solde'	=> str_to_float($invoiceInformations[$this->columnName_indexes['solde']]),
			'_receivedcomments' => $receivedcomments,
			
		);
		$codeClient = preg_replace('/^0+/', '', $invoiceHeader['reffiche']);
		$regexp = '/^0*'.$codeClient.'\s(.+)\/(\w+-)?\d+\*.*$/';
		if(preg_match($regexp, $invoiceHeader['lastname'])){
			$nomClient = preg_replace($regexp,'$1', $invoiceHeader['lastname']);
			$invoiceHeader['lastname'] = $nomClient;
		}
		if(is_numeric($invoiceHeader['subject']))
			$invoiceHeader['subject'] .= ' ' . $invoiceInformations[$this->columnName_indexes['nom2']];
		
		$invoiceHeader['reffiche'] = $codeClient;
		
		//var_dump($this->columnName_indexes);
		
		foreach ($invoice['detail'] as $product) {
			//var_dump($product);
			$isProduct = null;
			$product_name = '';
			$taxrate = str_to_float($product[$this->columnName_indexes['taux_tva']])/100;
			$qty = str_to_float($product[$this->columnName_indexes['quantite']]);
			array_push($invoiceValues, array_merge($invoiceHeader, array(
				'productcode'	=> $product[$this->columnName_indexes['code_produit']],
				'productid'	=> $this->getProductId($product[$this->columnName_indexes['code_produit']], $isProduct, $product_name),
				'quantity'	=> $qty,
				'article'	=> $product_name,
				'prix_unit_ht'	=> str_to_float($product[$this->columnName_indexes['prix_unit_ht']]),
				'isproduct'	=> $isProduct,
				'taxrate'	=> str_to_float($product[$this->columnName_indexes['taux_tva']]),
				'remise_ligne'	=> str_to_float($product[$this->columnName_indexes['remise_ligne']]),
            
			)));
		}
		//var_dump($invoiceValues);
		return $invoiceValues;
	}
	
	//erreurs trouvés dans les logs
	function erreurSaisieCodeClient(&$data){
		switch($data[$this->columnName_indexes['codeclient']]){
		case '2395372':
		case '1552052':
		case '2416112':
		case '2146702':
		case '1928872':
		case '1905072':
		case '2396042':
		case '2396032':
		case '2396052':
		case '1497532':
		case '1921532':
		case '2178562':
		case '2047752':
		case '407952':
		case '1910802':
			$data[$this->columnName_indexes['codeclient']] = substr($data[$this->columnName_indexes['codeclient']], 0, 6);
			break;
		case '214685':
			$data[$this->columnName_indexes['codeclient']] = '241685';
			break;
		case '193368':
			$data[$this->columnName_indexes['codeclient']] = '198368';
			break;
		case '420792':
			$data[$this->columnName_indexes['codeclient']] = '42079';
			break;
		case '240134':
			$data[$this->columnName_indexes['codeclient']] = '215871';
			break;
		case '240166':
			$data[$this->columnName_indexes['codeclient']] = '11567';
			break;
		case '214103':
			$data[$this->columnName_indexes['codeclient']] = '241103';
			break;
		case '211289':
			$data[$this->columnName_indexes['codeclient']] = '48803';
			break;
		case '211091':
			$data[$this->columnName_indexes['codeclient']] = '110296';
			break;
		case '80078':
			$data[$this->columnName_indexes['codeclient']] = '29106';
			break;
		case '208764':
			$data[$this->columnName_indexes['codeclient']] = '85092';
			break;
		case '202278':
			$data[$this->columnName_indexes['codeclient']] = '19270';
			break;
		case '161271':
			$data[$this->columnName_indexes['codeclient']] = '139861';
			break;
		case '210536':
			$data[$this->columnName_indexes['codeclient']] = '26968';
			break;
		case '201652':
			$data[$this->columnName_indexes['codeclient']] = '166435';
			break;
		case '212237':
			$data[$this->columnName_indexes['codeclient']] = '12308';
			break;
		case '211232':
			$data[$this->columnName_indexes['codeclient']] = '198866';
			break;
		case '201515':
			$data[$this->columnName_indexes['codeclient']] = '108234';
			break;
		case '205873':
			$data[$this->columnName_indexes['codeclient']] = '66480';
			break;
		case '198131':
			$data[$this->columnName_indexes['codeclient']] = '154846';
			break;
		case '208875':
			$data[$this->columnName_indexes['codeclient']] = '40677';
			break;
		case '200142':
			$data[$this->columnName_indexes['codeclient']] = '48017';
			break;
		case '4658L':
		case '240081':
		case '240165':
		case '213920':
		case '164808':
		case '197497':
		case '186551':
		case '172848':
		case '150215':
		case '127982':
		case '210314':
		case '199297':
		case '208250':
		case '196423':
		case '168768':
		case '888888':
		case '144438':
		case '16276':
		case '200508':
		case '30318':
		case '44318':
		case '40730':
		case '198542':
		case '184092':
		case '16501':
		case '197707':
		case '17237':
		case '139619':
		case '197001':
		case '2150192':
		case '208036':
		case '195850':
		case '186669':
		case '197024':
		case '215671':
		case '18293':
		case '202718':
		case '198085':
		case '195790':
		case '208142':
		case '195999':
		case '25620':
		case '156550':
		case '21707':
		case '73377':
		case '217436':
		case '218879':
		case '1867362':
			$data[$this->columnName_indexes['codeclient']] = '999999';
			break;
		}
	}

	function getModeReglement($compteClient, $paiementpropose, &$receivedcomments){
		$modesRegl = getModeReglementInfo();
		foreach($modesRegl as $modeRegl => $info)
			if($info['comptevente'] === $compteClient)
				return $modeRegl;
		$receivedcomments = $paiementpropose;
		return '(autre)';
	
		//if(preg_match('/ch(.{1,2}|&e.+)que/i', $paiementpropose))
		//	return 'Chèque';
		//elseif(preg_match('/esp(.{1,2}|&e.+)ce/i', $paiementpropose))
		//	return 'Espèces';
		//elseif(preg_match('/PAYBOX/i', $paiementpropose))
		//	return 'PayBox';
		//elseif(preg_match('/PAYPAL/i', $paiementpropose))
		//	return 'PayPal';
		//elseif(preg_match('/Vir(emen)?t?|Virt/i', $paiementpropose))
		//	return 'Virement';
		//elseif(preg_match('/mandat/i', $paiementpropose))
		//	return 'Mandat';
		//else{
		//	$receivedcomments = $paiementpropose;
		//	return '(autre)';
		//}
		//$result = RSNImportSources_Utils_Helper::checkPickListValue('Invoice', 'receivedmoderegl', 'receivedmoderegl', $paiementpropose);
		//return $paiementpropose;
	}
	
	/**
	 * Method called after the file is processed.
	 *  This method must be overload in the child class.
	 */
	function postPreImportData() {
		// Pré-identifie les contacts//
		
		
		$db = PearDatabase::getInstance();
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'Invoice');
		
		/* création d'un index */
		$query = "ALTER TABLE `$tableName` ADD INDEX(`sourceid`)";
		$db->pquery($query);
		
		if(false){
			/* ATTENTION ! Supprime les factures déjà importées
			*/
			$query = "UPDATE $tableName
			JOIN  vtiger_invoice
				ON  vtiger_invoice.invoice_no = `$tableName`.sourceid
			JOIN  vtiger_invoicecf
				ON  vtiger_invoice.invoiceid = `vtiger_invoicecf`.invoiceid
			JOIN vtiger_crmentity
				ON vtiger_invoice.invoiceid = vtiger_crmentity.crmid
			";
			$query .= " SET vtiger_crmentity.deleted = 1";
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
		}
		
		RSNImportSources_Utils_Helper::setPreImportDataContactIdByRef4D(
			$this->user,
			'Invoice',
			'reffiche',
			'_contactid',
			/*$changeStatus*/ false
		);
	
		RSNImportSources_Utils_Helper::skipPreImportDataForMissingContactsByRef4D(
			$this->user,
			'Invoice',
			'_contactid'
		);
	}
}
