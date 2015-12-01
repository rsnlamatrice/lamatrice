//<?php
///*
// * Les factures imporées depuis Cogilog ont un code de facture (invoice_no) commençant par COG et suivi du n° de facture Cogilog
// *
// *
// *	ABANDONNE AU PROFIT DU RSNImportSources
// *
// */
//
//define('ASSIGNEDTO_ALL', '7');
//define('MAX_QUERY_ROWS', 1000); //DEBUG
//
//define('CURRENCY_ID', 1);
//define('CONVERSION_RATE', 1);
//
//
//require_once('modules/RSN/models/ImportCogilogProduitsEtServices.php');
//
//class RSN_CogilogFacturesRSN_Import {
//
//	/* get postgresql data rows */
//	private static function getPGDataRows($table_name){
//			
//		$dbconn = self::get_db_connect();
//		
//		if(stripos($table_name, 'SELECT ') === 0){
//			$query = $table_name;
//			if(!preg_match('/\sLIMIT\s/i', $query))
//				$query .= ' LIMIT 99';
//		}
//		else
//			$query = 'SELECT * FROM ' . $table_name . ' LIMIT 50';
//		
//		// Exécution de la requête SQL
//		$result = pg_query($query);
//		if(!$result){
//			return '<code>Échec de la requête : ' . pg_last_error() .'</code>'
//					. '<br>'.$query;
//		}
//		
//		$rows = array();
//		
//		while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
//		    $rows[] = $line;
//		}
//		
//		// Libère le résultat
//		pg_free_result($result);
//		
//		// Ferme la connexion
//		pg_close($dbconn);
//		
//		return $rows;
//	}
//	
//	private static function get_db_connect(){
//		// Connexion, sélection de la base de données
//		include_once('config.cogilog.php');
//		global $cogilog_config;
//		$cxString = 'host='.$cogilog_config['db_server'].' port='.$cogilog_config['db_port'].' dbname='.$cogilog_config['db_name'].' user='.$cogilog_config['db_username'].' password='.$cogilog_config['db_password'];
//		//echo str_repeat('<br>',5);
//		return pg_connect($cxString)
//		    or die('Connexion impossible : ' . pg_last_error());
//			
//	}
//        
//	// Importe les factures qui n'ont pas encore été importées
//	public static function importNexts(){
//		
//		$focus = CRMEntity::getInstance('Invoice');
//		
//		$nbRows = MAX_QUERY_ROWS;
//		$srcRows = self::getEntries($nbRows);
//		$doneRows = array();
//		$factures = array();
//		$current_facture = false;
//		// répartit les lignes en deux niveaux : les factures et leurs lignes de produits
//		foreach($srcRows as $srcRow){
//			
//			if($current_facture != $srcRow['id']){
//				$factures[] = array(
//						    'id' => $srcRow['id'],
//						    'row' => $srcRow,
//						    'lignes' => array(),
//				);
//				$current_facture = $srcRow['id'];
//			}
//			$factures[count($factures)-1]['lignes'][] = $srcRow;
//			//break;//debug
//		}
//		
//		if(count($factures) == $nbRows ){
//			//Supprime la dernière car il n'y a peut être pas toutes les lignes d'articles
//			array_pop($factures);
//		}
//		
//		foreach($factures as $facture){
//			if(self::importRow($facture))
//				$srcRow['LA MATRICE'] = 'Importée';
//			else
//				$srcRow['LA MATRICE'] = 'Non';
//				
//			$doneRows = array_merge($doneRows, $facture['lignes']);
//			
//			//break;//debug
//		}
//		
//                return $doneRows;
//	}
//        
//	/*
//	 * Les entrées contiennent autant de lignes que de lignes de factures
//	 */
//	private static function getEntries($nbRows = MAX_QUERY_ROWS){
//            
//		/* factures déjà importés */
//		$query = "SELECT MAX(CAST(SUBSTR(invoice_no,4) AS UNSIGNED)) AS codefacture_max
//			FROM vtiger_invoice
//                        JOIN vtiger_crmentity
//                            ON vtiger_invoice.invoiceid = vtiger_crmentity.crmid
//			WHERE invoice_no LIKE 'COG%'
//			AND vtiger_crmentity.deleted = 0
//		";
//		$db = PearDatabase::getInstance();
//		//$db->setDebug(true);
//		$result = $db->pquery($query, array());
//		if($db->num_rows($result)){
//			$row = $db->fetch_row($result, 0);
//			$factMax = intval(substr($row['codefacture_max'], 2));
//			$anneeMax = intval(substr($row['codefacture_max'], 0, strlen($row['codefacture_max']) - 5)) + 2000;
//			echo('Dernière facture existante : ' . $anneeMax . ', n° '. $factMax);
//		}
//		else
//			$factMax = false;
//		
//		$query = 'SELECT cl.code AS CodeClient, cl.nom2 AS NomClient,
//			facture.*,
//			affaire.code AS affaire_code, affaire.nom AS affaire_nom
//			, "ligne_fact"."prix" AS "prix_unit_ht"
//			, "ligne_fact"."ht2" AS "total_ligne_ht"
//			, ROUND( CAST( ( "ligne_fact"."quantite" * "ligne_fact"."prixttc" * ( 100 - "ligne_fact"."remise" ) / 100 ) AS NUMERIC ), 2 ) AS "montant_ligne"
//			, ROUND( CAST( ( "ligne_fact"."quantite" * "ligne_fact"."prixttc" * ( 100 - "ligne_fact"."remise" ) / 100 ) AS NUMERIC ), 2 ) AS "montant_ligne"
//			, ROUND( CAST( ( "ligne_fact"."quantite" * ( CASE WHEN ( "produit"."codetva" = 0 ) THEN "produit"."achat" ELSE "produit"."achat" * ( 1 + ( "codetauxtva"."taux" / 100 ) ) END ) ) AS NUMERIC ), 2 ) AS "montant_achat"
//			, ( "ligne_fact"."quantite" ) AS "quantite", CASE WHEN ( "produit"."codetva" = 0 ) THEN "produit"."achat" ELSE "produit"."achat" * ( 1 + ( "codetauxtva"."taux" / 100 ) ) END AS "prixachatttc"
//			, "famille"."nom" AS "produit_famille"
//			, "ligne_fact".position AS "position_ligne"
//			, "produit"."id" AS "id_produit"
//			, "produit"."nom" AS "nom_produit"
//			, "produit"."code" AS "code_produit"
//			, "produit"."gererstock" AS "gererstock"
//			, "produit"."stockdivers" AS "stockdivers"
//			, "produit"."prix" AS "prixvente_produit"
//			, "produit"."stockqte" AS "stock_produit"
//			, "produit"."compte" AS "compte_produit"
//			, "produit"."section" AS "section_produit"
//			, "produit"."codetva" AS "tva_produit"
//			, "produit"."indisponible" AS "indisponible"
//			, "codetauxtva"."taux" AS "taux_tva"
//			FROM gfactu00002 facture
//			JOIN gclien00002 cl
//				ON facture.id_gclien = cl.id
//			JOIN glfact00002 ligne_fact
//				ON ligne_fact.id_piece = facture.id
//			JOIN gaffai00002 affaire
//				ON affaire.id = facture.id_gaffai
//			INNER JOIN "gprodu00002" AS "produit"
//				ON "ligne_fact"."id_gprodu" = "produit"."id"
//			INNER JOIN "gtprod00002" AS "famille"
//				ON "famille"."id" = "produit"."id_gtprod"
//			LEFT JOIN "gtvacg00002" AS "codetauxtva"
//				ON "produit"."codetva" = "codetauxtva"."code"
//		';
//		/* Attention à ne pas importer une facture en cours de saisie */
//		$query .= ' WHERE facture.datepiece < CURRENT_DATE 
//		';
//		if(true)
//			$query .= ' AND (facture.numero = 3315 AND facture.annee = 2015)';
//		else
//		if($factMax)
//			$query .= ' AND ((facture.numero > '.$factMax.' AND facture.annee = '.$anneeMax.')
//			OR facture.annee > '.$anneeMax.')';
//		//$query .= ' AND cl.code = \'999999\'';
//		//print_r("<pre>$query</pre>");
//		$rows = self::getPGDataRows("SELECT COUNT(*) AS nbrerestantes FROM ($query) a");
//		echo("<pre>Nbre de lignes de factures à importer : ".$rows[0]['nbrerestantes']."</pre>");
//		
//		$query .= ' ORDER BY facture.annee, facture.numero, position_ligne ASC
//                    LIMIT ' . $nbRows;
//		//var_dump($query);
//		return self::getPGDataRows($query);
//	}
//        
//	/* Import de la facture
//	 * @returns invoice record
//	 */
//	private static function importRow($facture){
//		//Checks Contact
//		$contact = self::importContact($facture['row']);
//		if(!$contact) return false;
//		//Imports invoice
//		$invoice = self::importFacture($facture['row'], $contact);
//		if(!$invoice) return false;
//		
//		//Import invoice product/service rows
//		
//		$sequence = 1;
//		foreach($facture['lignes'] as $ligne_facture){
//			$product = self::importLigneFacture($ligne_facture, $invoice, $contact, $sequence++);
//		}
//		//Update totals
//		
//		global $log;
//		
//		$log->debug("BEFORE " . basename(__FILE__) . " raise event handler(" . $invoice->getId() . ", " . $invoice->get('mode') . " )");
//		//raise event handler
//		$invoice->triggerEvent('vtiger.entity.aftersave');
//		$log->debug("AFTER " . basename(__FILE__) . " raise event handler");
//		
//		return $invoice;
//	}
//	
//	/* Import du client
//	 * @returns contact record
//	 */
//	private static function importContact($srcRow){
//		$codeClient = preg_replace('/^0+/', '', $srcRow['codeclient']);
//		$regexp = '/^0*'.$codeClient.'\s(.+)\/(\w+-)?\d+\*.*$/';
//		if(preg_match($regexp,$srcRow['nomclient'])){
//			$nomClient = preg_replace($regexp,'$1', $srcRow['nomclient']);
//		}
//		else
//			$nomClient = $srcRow['nomclient'];
//			
//		$query = "SELECT contactid
//			FROM vtiger_contactdetails
//                        JOIN vtiger_crmentity
//                            ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
//			WHERE contact_no = CONCAT('C', ?)
//			AND vtiger_crmentity.deleted = 0
//			LIMIT 1
//		";
//		$db = PearDatabase::getInstance();
//		$result = $db->pquery($query, array($codeClient));
//		if($db->num_rows($result)){
//			$row = $db->fetch_row($result, 0);
//			$contact = Vtiger_Record_Model::getInstanceById($row['contactid'], 'Contacts');
//			//var_dump("$codeClient existe id=".$contact->getId());
//		}
//		else {
//			$contact = Vtiger_Record_Model::getCleanInstance('Contacts');
//			$contact->set('mode','create');
//			$contact->set('lastname',$nomClient);
//			$contact->set('isgroup',0);
//			$contact->set('mailingstreet', $srcRow['num']. ' ' .$srcRow['voie']);
//			$contact->set('mailingstreet2', $srcRow['nom1']);
//			$contact->set('mailingstreet3', $srcRow['compad1']);
//			$contact->set('mailingpobox', $srcRow['compad2']);
//			$contact->set('mailingcity', $srcRow['ville']);
//			$contact->set('mailingzip', $srcRow['cp']);
//			$contact->set('mailingcountry', $srcRow['pays']);
//			$contact->set('email', $srcRow['email']);
//			$contact->set('rsnnpai', 1);
//			//$db->setDebug(true);
//			$contact->save();
//			//$db->setDebug(false);
//			
//			$contact->set('mode','');
//			//This field is not manage by save()
//			$contact->set('contact_no','C'.$codeClient);
//			//set contact_no
//			$query = "UPDATE vtiger_contactdetails
//				JOIN vtiger_crmentity
//					ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid
//				SET contact_no = CONCAT('C', ?)
//				, smownerid = ?
//				WHERE contactid = ?
//			";
//			$result = $db->pquery($query, array($codeClient, ASSIGNEDTO_ALL, $contact->getId()));
//			
//			var_dump("Contact C$codeClient créé, id=".$contact->getId() . ", nom=".$contact->getName());
//		}
//		
//		return $contact;
//	}
//	
//	
//	
//        /* Import du client
//	 * @returns contact record
//	 */
//	private static function importFacture($srcRow, $contact){
//			
//		$cogId = substr($srcRow['annee'], 2, 2) . str_pad ($srcRow['numero'], 5, '0', STR_PAD_LEFT);
//		/*var_dump($cogId);
//		return;*/
//	
//		$account = $contact->getAccountRecordModel();
//                if(!$account->getId()){
//			var_dump('Le compte n\'est pas défini', $account);
//			return;
//                }
//		$query = "SELECT invoiceid /*, vtiger_crmentity.deleted*/
//			FROM vtiger_invoice
//                        JOIN vtiger_crmentity
//                            ON vtiger_invoice.invoiceid = vtiger_crmentity.crmid
//			WHERE invoice_no = CONCAT('COG', ?)
//			AND vtiger_crmentity.deleted = 0
//			LIMIT 1
//		";
//		$db = PearDatabase::getInstance();
//		$result = $db->pquery($query, array($cogId));
//		if($db->num_rows($result)){
//			$row = $db->fetch_row($result, 0);
//                        /*if($row['deleted'] == 1){
//                            //restore from bin
//                            
//                            $recordIds = array($srcRow['id']);
//                            $recycleBinModule = new RecycleBin_Module_Model();
//                            $recycleBinModule->deleteRecords($recordIds);
//                        }*/
//			$record = Vtiger_Record_Model::getInstanceById($row['invoiceid'], 'Invoice');
//		}
//		else {
//			$record = Vtiger_Record_Model::getCleanInstance('Invoice');
//			$record->set('mode','create');
//			$record->set('bill_street', trim($srcRow['num']. ' ' .$srcRow['voie']));
//			$record->set('bill_street2', $srcRow['nom1']);
//			$record->set('bill_street3', $srcRow['compad1']);
//			$record->set('bill_pobox', $srcRow['compad2']);
//			$record->set('bill_city', $srcRow['ville']);
//			$record->set('bill_code', $srcRow['cp']);
//			$record->set('bill_country', $srcRow['pays']);
//			$record->set('subject', $srcRow['nom2']);
//			$record->set('receivedcomments', $srcRow['paiementpropose']);
//			$record->set('description', $srcRow['notes']);
//			$record->set('invoicedate', $srcRow['datepiece']);
//			$record->set('duedate', $srcRow['echeance']);
//			$record->set('contact_id', $contact->getId());
//			$record->set('account_id', $account->getId());
//			$record->set('received', str_replace('.', ',', $srcRow['netht']+$srcRow['nettva']));
//			//$record->set('hdnGrandTotal', $srcRow['netht']+$srcRow['nettva']);//TODO non enregistré : à cause de l'absence de ligne ?
//			$record->set('typedossier', 'Facture'); //TODO
//			$record->set('invoicestatus', 'Paid');//TODO
//			$record->set('currency_id', CURRENCY_ID);
//			$record->set('conversion_rate', CONVERSION_RATE);
//			$record->set('hdnTaxType', 'individual');
//                    
//		    
//			$coupon = self::findCoupon($srcRow);
//			if($coupon)
//				$record->set('notesid', $coupon->getId());
//			$campagne = self::findCampagne($srcRow, $coupon);
//			if($campagne)
//				$record->set('campaign_no', $campagne->getId());
//			
//			//$db->setDebug(true);
//			$record->save();
//			if(!$record->getId()){
//                            echo "<pre><code>Impossible d'enregistrer la nouvelle facture</code></pre>";
//                            return false;
//                        }
//			echo "<pre><code>Nouvelle facture ".$record->getId()."</code></pre>";
//                           
//			$record->set('mode','edit');
//			//This field is not manage by save()
//			$record->set('invoice_no','COG'.$cogId);
//			//set invoice_no
//			$query = "UPDATE vtiger_invoice
//				JOIN vtiger_crmentity
//					ON vtiger_crmentity.crmid = vtiger_invoice.invoiceid
//				SET invoice_no = CONCAT('COG', ?)
//				, total = ?
//				, subtotal = ?
//				, taxtype = ?
//				, smownerid = ?
//				, createdtime = ?
//				, modifiedtime = ?
//				WHERE invoiceid = ?
//			";
//			$total = $srcRow['netht']+$srcRow['nettva'];
//			$result = $db->pquery($query, array($cogId
//							    , $total
//							    , $total
//							    , 'individual'
//							    , ASSIGNEDTO_ALL
//							    , substr($srcRow['tssaisie'], 0, 19)
//							    , $srcRow['tsmod'] ? substr($srcRow['tsmod'], 0, 19) : substr($srcRow['tssaisie'], 0, 19)
//							    , $record->getId()));
//			
//			if( ! $result)
//				$db->echoError();
//		}
//		
//		return $record;		
//	}
//
//
//	static $allTaxes;
//	
//	private static function getTax($rate){
//		if(!$rate)
//			return false;
//		if(!self::$allTaxes)
//			self::$allTaxes = getAllTaxes();
//		foreach(self::$allTaxes as $tax)
//			if($tax['percentage'] == $rate)
//				return $tax;
//		return false;
//	}
//	
//	/* Import des lignes de factures
//	 * Les lignes sont dans vtiger_inventoryproductrel
//	 *
//	 * cf include/utils/InventoryUtils.php, saveInventoryProductDetails()
//	 */
//	private static function importLigneFacture($srcRow, $invoice, $contact, $sequence){
//		$product = RSN_CogilogProduitsEtServices_Import::getProductOrService($srcRow);
//		if(!$product)
//			return false;
//		//adds invoice/product relation
//		$qty = $srcRow['quantite'];
//		$listprice = $srcRow['prix_unit_ht'];
//		$discount_amount = $srcRow['total_ligne_ht'] - $qty * $listprice;
//		
//		$tax = self::getTax($srcRow['taux_tva']);
//		if($tax){
//			$taxName = $tax['taxname'];
//			$taxValue = $tax['percentage'];
//		}
//		else {
//			$taxName = 'tax1';
//			$taxValue = null;
//		}
//		
//		$incrementOnDel = $product instanceof Services_Record_Model ? 0 : 1;
//		
//		$db = PearDatabase::getInstance();
//		$query ="INSERT INTO vtiger_inventoryproductrel (id, productid, sequence_no, quantity, listprice, discount_amount, incrementondel, $taxName) VALUES(?,?,?,?,?,?,?,?)";
//		$qparams = array($invoice->getId(), $product->getId(), $sequence, $qty, $listprice, $discount_amount, $incrementOnDel, $taxValue);
//		//$db->setDebug(true);
//		$db->pquery($query, $qparams);
//		//$db->setDebug(false);
//	}
//	
//	
//	
//	
//	/* coupon d'après code affaire d'un document de type Coupon
//	 */
//	private static function findCoupon($srcRow){
//		
//		$codeAffaire=$srcRow['affaire_code'];
//		$query = "SELECT vtiger_crmentity.crmid
//			FROM vtiger_notes
//			JOIN vtiger_notescf
//                            ON vtiger_notescf.notesid = vtiger_notes.notesid
//                        JOIN vtiger_crmentity
//                            ON vtiger_notes.notesid = vtiger_crmentity.crmid
//			WHERE codeaffaire = ?
//			AND folderid  =?
//			AND vtiger_crmentity.deleted = 0
//			LIMIT 1
//		";
//		$db = PearDatabase::getInstance();
//		$result = $db->pquery($query, array($codeAffaire, COUPON_FOLDERID));
//		if(!$result)
//			$db->echoError();
//		if($db->num_rows($result)){
//			$row = $db->fetch_row($result, 0);
//			return Vtiger_Record_Model::getInstanceById($row['crmid'], 'Documents');
//		}
//	}
//	
//	
//	/* campagne d'après code affaire / Coupon
//	 */
//	private static function findCampagne($srcRow, $coupon){
//		
//		if(!is_object($coupon)){
//			
//			$codeAffaire=$srcRow['affaire_code'];
//			$query = "SELECT vtiger_crmentity.crmid
//				FROM vtiger_campaignscf
//				JOIN vtiger_crmentity
//				    ON vtiger_campaignscf.campaignid = vtiger_crmentity.crmid
//				WHERE codeaffaire = ?
//				AND vtiger_crmentity.deleted = 0
//				LIMIT 1
//			";
//			$db = PearDatabase::getInstance();
//			$result = $db->pquery($query, array($codeAffaire));
//			if(!$result)
//				$db->echoError();
//			if($db->num_rows($result)){
//				$row = $db->fetch_row($result, 0);
//				return Vtiger_Record_Model::getInstanceById($row['crmid'], 'Campaigns');
//			}
//			return;
//		}
//		$campaigns = $coupon->getRelatedCampaigns();
//		if(count($campaigns) == 1)
//			foreach($campaigns as $campaign)
//				return $campaign;
//	}
//}
