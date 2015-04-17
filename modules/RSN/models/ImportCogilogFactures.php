<?php
/*
 * Les factures imporées depuis Cogilog ont un code de facture (invoice_no) commençant par COG et suivi de l'id Cogilog
 *
 *
 */

 define('ASSIGNEDTO_ALL', '7');
 
class RSN_CogilogFacturesRSN_Import {

	/* get postgresql data rows */
	private static function getPGDataRows($table_name){
			
		$dbconn = self::get_db_connect();
		
		if(stripos($table_name, 'SELECT ') === 0){
			$query = $table_name;
			if(!preg_match('/\sLIMIT\s/i', $query))
				$query .= ' LIMIT 99';
		}
		else
			$query = 'SELECT * FROM ' . $table_name . ' LIMIT 50';
		
		// Exécution de la requête SQL
		$result = pg_query($query);
		if(!$result){
			return '<code>Échec de la requête : ' . pg_last_error() .'</code>'
					. '<br>'.$query;
		}
		
		$rows = array();
		
		while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
		    $rows[] = $line;
		}
		
		// Libère le résultat
		pg_free_result($result);
		
		// Ferme la connexion
		pg_close($dbconn);
		
		return $rows;
	}
	
	private static function get_db_connect(){
		// Connexion, sélection de la base de données
		include_once('config.cogilog.php');
		global $cogilog_config;
		$cxString = 'host='.$cogilog_config['db_server'].' port='.$cogilog_config['db_port'].' dbname='.$cogilog_config['db_name'].' user='.$cogilog_config['db_username'].' password='.$cogilog_config['db_password'];
		//echo str_repeat('<br>',5);
		return pg_connect($cxString)
		    or die('Connexion impossible : ' . pg_last_error());
			
	}
        
	// Importe les factures qui n'ont pas encore été importées
	public static function importNexts(){
            
		$srcRows = self::getEntries();
		$doneRows = array();
		$factures = array();
		$current_facture = false;
		// répartit les lignes en deux niveaux : les factures et leurs lignes de produits
		foreach($srcRows as $srcRow){
			
			if($current_facture != $srcRow['id']){
				$factures[] = array(
						    'id' => $srcRow['id'],
						    'row' => $srcRow,
						    'lignes' => array(),
				);
				$current_facture = $srcRow['id'];
			}
			$factures[count($factures)-1]['lignes'][] = $srcRow;
			//break;//debug
		}
		//Supprime la dernière car il n'y a peut être pas toutes les lignes d'articles
		array_pop($factures);
		
		foreach($factures as $facture){
			if(self::importRow($facture))
				$srcRow['LA MATRICE'] = 'Importée';
			else
				$srcRow['LA MATRICE'] = 'Non';
				
			$doneRows = array_merge($doneRows, $facture['lignes']);
			
			//break;//debug
		}
                return $doneRows;
	}
        
	/*
	 * Les entrées contiennent autant de lignes que de lignes de factures
	 */
	private static function getEntries(){
            
		/* factures déjà importés */
		$query = "SELECT MIN(CAST(SUBSTR(invoice_no,4) AS UNSIGNED)) AS codefacture_min, MAX(CAST(SUBSTR(invoice_no,4) AS UNSIGNED)) AS codefacture_max
			FROM vtiger_invoice
			WHERE invoice_no LIKE 'COG%'
		";
		$db = PearDatabase::getInstance();
		//$db->setDebug(true);
		$result = $db->pquery($query, array());
		if($db->num_rows($result)){
			$row = $db->fetch_row($result, 0);
			$factMin = $row['codefacture_min'];
			$factMax = $row['codefacture_max'];
			//var_dump($factMin,$factMax);
		}
		else
			$factMin  = false;
		
		$query = 'SELECT cl.code AS CodeClient, cl.nom2 AS NomClient,
			facture.*,
			affaire.code AS affaire_code, affaire.nom AS affaire_nom
			, "ligne_fact"."prix" AS "prix_unit_ht"
			, "ligne_fact"."ht2" AS "total_ligne_ht"
			, ROUND( CAST( ( "ligne_fact"."quantite" * "ligne_fact"."prixttc" * ( 100 - "ligne_fact"."remise" ) / 100 ) AS NUMERIC ), 2 ) AS "montant_ligne"
			, ROUND( CAST( ( "ligne_fact"."quantite" * "ligne_fact"."prixttc" * ( 100 - "ligne_fact"."remise" ) / 100 ) AS NUMERIC ), 2 ) AS "montant_ligne"
			, ROUND( CAST( ( "ligne_fact"."quantite" * ( CASE WHEN ( "produit"."codetva" = 0 ) THEN "produit"."achat" ELSE "produit"."achat" * ( 1 + ( "codetauxtva"."taux" / 100 ) ) END ) ) AS NUMERIC ), 2 ) AS "montant_achat"
			, ( "ligne_fact"."quantite" ) AS "quantite", CASE WHEN ( "produit"."codetva" = 0 ) THEN "produit"."achat" ELSE "produit"."achat" * ( 1 + ( "codetauxtva"."taux" / 100 ) ) END AS "prixachatttc"
			, "famille"."nom" AS "produit_famille"
			, "ligne_fact".position AS "position_ligne"
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
			FROM gfactu00002 facture
			JOIN gclien00002 cl
				ON facture.id_gclien = cl.id
			JOIN glfact00002 ligne_fact
				ON ligne_fact.id_piece = facture.id
			JOIN gaffai00002 affaire
				ON affaire.id = facture.id_gaffai
			INNER JOIN "gprodu00002" AS "produit"
				ON "ligne_fact"."id_gprodu" = "produit"."id"
			INNER JOIN "gtprod00002" AS "famille"
				ON "famille"."id" = "produit"."id_gtprod"
			LEFT JOIN "gtvacg00002" AS "codetauxtva"
				ON "produit"."codetva" = "codetauxtva"."code"
		';
		/*
		SELECT ROUND( CAST( ( "ligne_fact"."quantite" * "ligne_fact"."prixttc" * ( 100 - "ligne_fact"."remise" ) / 100 ) AS NUMERIC ), 2 ) AS "montant_facture"
, ROUND( CAST( ( "ligne_fact"."quantite" * ( CASE WHEN ( "produit"."codetva" = 0 ) THEN "produit"."achat" ELSE "produit"."achat" * ( 1 + ( "codetauxtva"."taux" / 100 ) ) END ) ) AS NUMERIC ), 2 ) AS "montant_achat"
, ( "ligne_fact"."quantite" ) AS "quantite", CASE WHEN ( "produit"."codetva" = 0 ) THEN "produit"."achat" ELSE "produit"."achat" * ( 1 + ( "codetauxtva"."taux" / 100 ) ) END AS "prixachatttc"
, "affaire"."nom" AS "NomAffaire"
, "produit"."nom" AS "nomProduit"
, "famille"."nom" AS "NomFamille"
, CAST( date_trunc( 'month', "datepiece" ) AS DATE ) "dateMois"
, CASE WHEN ( EXTRACT( MONTH FROM "datepiece" ) < 9 ) THEN CAST( EXTRACT( YEAR FROM "datepiece" ) - 1 AS "text" ) || '-' || CAST( EXTRACT( YEAR FROM "datepiece" ) AS "text" )
    ELSE CAST( EXTRACT( YEAR FROM "datepiece" ) AS "text" ) || '-' || CAST( EXTRACT( YEAR FROM "datepiece" ) + 1 AS "text" ) END AS "Exercice"
, "compteclient"
, ROUND( CAST( ( "ligne_fact"."quantite" * ( CASE WHEN ( "produit"."codetva" = 0 ) THEN "produit"."prix"
    ELSE "produit"."prix" * ( 1 + ( "codetauxtva"."taux" / 100 ) ) END ) ) AS NUMERIC ), 2 ) "prixProduit"
FROM "gfactu00002" AS "facture"
INNER JOIN "glfact00002" AS "ligne_fact" ON "ligne_fact"."id_piece" = "facture"."id"
INNER JOIN "gprodu00002" AS "produit" ON "ligne_fact"."id_gprodu" = "produit"."id"
INNER JOIN "gaffai00002" AS "affaire" ON "affaire"."id" = "facture"."id_gaffai"
INNER JOIN "gtprod00002" AS "famille" ON "famille"."id" = "produit"."id_gtprod"
LEFT JOIN "gtvacg00002" AS "codetauxtva" ON "produit"."codetva" = "codetauxtva"."code"*/

		if($factMin)
			$query .= ' WHERE NOT facture.id BETWEEN '.$factMin.' AND '.$factMax.'
		';
			
		//var_dump($query);
		$rows = self::getPGDataRows("SELECT COUNT(*) AS nbrerestantes FROM ($query) a");
		echo("<pre>Nbre de lignes de factures à importer : ".$rows[0]['nbrerestantes']."</pre>");
		
		$query .= ' ORDER BY facture.id, position_ligne ASC
                    LIMIT 100 ';
		//var_dump($query);
		return self::getPGDataRows($query);
    }
        
	/* Import de la facture
	 * @returns invoice record
	 */
	private static function importRow($facture){
		//Checks Contact
		$contact = self::importContact($facture['row']);
		if(!$contact) return false;
		
		//Imports invoice
		$invoice = self::importFacture($facture['row'], $contact);
		if(!$invoice) return false;
		
		//Import invoice product/service rows
		
		$sequence = 1;
		foreach($facture['lignes'] as $ligne_facture){
			$product = self::importLigneFacture($ligne_facture, $invoice, $contact, $sequence++);
		}
		//Update totals
		
		return $invoice;
	}
	
	/* Import du client
	 * @returns contact record
	 */
	private static function importContact($srcRow){
		$codeClient = preg_replace('/^0+/', '', $srcRow['codeclient']);
		if(!preg_match('/^0*'.$codeClient.'\s(.+)\/\d+\*.*$/',$srcRow['nomclient'])){
			return false;
		}
		$nomClient = preg_replace('/^0*'.$codeClient.'\s(.+)\/\d+\*.*$/','$1', $srcRow['nomclient']);
			
		$query = "SELECT contactid, deleted
			FROM vtiger_contactdetails
                        JOIN vtiger_crmentity
                            ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
			WHERE contact_no = CONCAT('C', ?)
			LIMIT 1
		";
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, array($codeClient));
		if($db->num_rows($result)){
			$row = $db->fetch_row($result, 0);
			$contact = Vtiger_Record_Model::getInstanceById($row['contactid'], 'Contacts');
			//var_dump("$codeClient existe id=".$row['contactid']);
		}
		else {
			$contact = Vtiger_Record_Model::getCleanInstance('Contacts');
			$contact->set('MODE', 'create');
			$contact->set('lastname',$nomClient);
			$contact->set('mailingstreet', $srcRow['num']. ' ' .$srcRow['voie']);
			$contact->set('mailingstreet2', $srcRow['nom1']);
			$contact->set('mailingstreet3', $srcRow['compad1']);
			$contact->set('mailingpobox', $srcRow['compad2']);
			$contact->set('mailingcity', $srcRow['ville']);
			$contact->set('mailingzip', $srcRow['cp']);
			$contact->set('mailingcountry', $srcRow['pays']);
			$contact->set('email', $srcRow['email']);
			$contact->set('rsnnpai', 1);
			$contact->save();
			
			$contact->set('MODE', '');
			//This field is not manage by save()
			$contact->set('contact_no','C'.$codeClient);
			//set contact_no
			$query = "UPDATE vtiger_contactdetails
				JOIN vtiger_crmentity
					ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid
				SET contact_no = CONCAT('C', ?)
				, smownerid = ?
				WHERE contactid = ?
			";
			$result = $db->pquery($query, array($codeClient, ASSIGNEDTO_ALL, $contact->getId()));
		}
		
		return $contact;
	}
	
	
	
        /* Import du client
	 * @returns contact record
	 */
	private static function importFacture($srcRow, $contact){
			
		$account = $contact->getAccountRecordModel();
                
		$query = "SELECT invoiceid, vtiger_crmentity.deleted
			FROM vtiger_invoice
                        JOIN vtiger_crmentity
                            ON vtiger_invoice.invoiceid = vtiger_crmentity.crmid
			WHERE invoice_no = CONCAT('COG', ?)
			LIMIT 1
		";
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, array($srcRow['id']));
		if($db->num_rows($result)){
			$row = $db->fetch_row($result, 0);
                        if($row['deleted'] == 1){
                            //restore from bin
                            
                            $recordIds = array($srcRow['id']);
                            $recycleBinModule = new RecycleBin_Module_Model();
                            $recycleBinModule->deleteRecords($recordIds);
                        }
			$record = Vtiger_Record_Model::getInstanceById($row['invoiceid'], 'Invoice');
		}
		else {
			$record = Vtiger_Record_Model::getCleanInstance('Invoice');
			$record->set('MODE', 'create');
			$record->set('bill_street', $srcRow['num']. ' ' .$srcRow['voie']);
			$record->set('bill_street2', $srcRow['nom1']);
			$record->set('bill_street3', $srcRow['compad1']);
			$record->set('bill_pobox', $srcRow['compad2']);
			$record->set('bill_city', $srcRow['ville']);
			$record->set('bill_code', $srcRow['cp']);
			$record->set('bill_country', $srcRow['pays']);
			$record->set('subject', $srcRow['nom2']);
			$record->set('receivedcomments', $srcRow['paiementpropose']);
			$record->set('description', $srcRow['notes']);
			$record->set('invoicedate', $srcRow['datepiece']);
			$record->set('duedate', $srcRow['echeance']);
			$record->set('contact_id', $contact->getId());
			$record->set('account_id', $account->getId());
			$record->set('received', str_replace('.', ',', $srcRow['netht']+$srcRow['nettva']));
			//$record->set('hdnGrandTotal', $srcRow['netht']+$srcRow['nettva']);//TODO non enregistré : à cause de l'absence de ligne ?
			$record->set('typedossier', 'Facture'); //TODO
			$record->set('invoicestatus', 'Paid');//TODO
                    
			//$db->setDebug(true);
			$record->save();
			if(!$record->getId()){
                            echo "<pre><code>Impossible d'enregistrer la nouvelle facture</code></pre>";
                            return false;
                        }
			$record->set('MODE', '');
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
				$db->echoError();;
		}
		
		return $record;		
	}


	
	/* Import des lignes de factures
	 * Les lignes sont dans vtiger_inventoryproductrel
	 *
	 * cf include/utils/InventoryUtils.php, saveInventoryProductDetails()
	 */
	private static function importLigneFacture($srcRow, $invoice, $contact, $sequence){
		$product = self::getProductOrService($srcRow);
		if(!$product)
			return false;
		//adds invoice/product relation
		$qty = $srcRow['quantite'];
		$listprice = $srcRow['prix_unit_ht'];
		$discount_amount = $srcRow['total_ligne_ht'] - $qty * $listprice;
		
		$db = PearDatabase::getInstance();
		$query ="INSERT INTO vtiger_inventoryproductrel (id, productid, sequence_no, quantity, listprice, discount_amount) VALUES(?,?,?,?,?,?)";
		$qparams = array($invoice->getId(), $product->getId(), $sequence, $qty, $listprice, $discount_amount);
		//$db->setDebug(true);
		$db->pquery($query, $qparams);
		//$db->setDebug(false);
	}
	
	private static function getProductOrService($srcRow){
		$product = self::findProduct($srcRow);
		if(!$product)
			$product = self::findService($srcRow);
		if(!$product)
			if($srcRow['gererstock'] == 't' || $srcRow['stockdivers'] != 0)
				$product = self::importProduct($srcRow);
			else
				$product = self::importService($srcRow);
		return $product;
	}
	
	private static function findProduct($srcRow){
		$codeProduct=$srcRow['code_produit'];
		$query = "SELECT productid
			FROM vtiger_products
                        JOIN vtiger_crmentity
                            ON vtiger_products.productid = vtiger_crmentity.crmid
			WHERE productcode = ?
			ORDER BY vtiger_crmentity.deleted ASC
		";
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, array($codeProduct));
		if($db->num_rows($result)){
			$row = $db->fetch_row($result, 0);
			return Vtiger_Record_Model::getInstanceById($row['productid'], 'Products');
		}
	}
	
	private static function findService($srcRow){
		
		$codeProduct=$srcRow['code_produit'];
		$query = "SELECT serviceid
			FROM vtiger_service
                        JOIN vtiger_crmentity
                            ON vtiger_service.serviceid = vtiger_crmentity.crmid
			WHERE productcode = ?
			ORDER BY vtiger_crmentity.deleted ASC
		";
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, array($codeProduct));
		if($db->num_rows($result)){
			$row = $db->fetch_row($result, 0);
			return Vtiger_Record_Model::getInstanceById($row['serviceid'], 'Services');
		}
	}
	
	private static function importProduct($srcRow){
		echo ("<pre>Création du produit ".$srcRow['nom_produit']." (".$srcRow['code_produit'].") : ".$srcRow['prixvente_produit']." &euro;</pre>");
		$record = Vtiger_Record_Model::getCleanInstance('Products');
		$record->set('MODE', 'create');
		$record->set('productname', $srcRow['nom_produit']);
		$record->set('productcode', $srcRow['code_produit']);
		$record->set('glacct', $srcRow['compte_produit']);
		$record->set('rsnsectionanal', $srcRow['section_produit']);
		$record->set('qtyinstock', $srcRow['stock_produit']);
		$record->set('unit_price', str_replace('.', ',', $srcRow['prixvente_produit'])); //Attention, il faut la virgule...
		$record->set('taxclass', $srcRow['tva_produit']);
		$record->set('discontinued', $srcRow['indisponible'] == 't' ? 0 : 1);
	    
		$db = PearDatabase::getInstance();
		//$db->setDebug(true);
		$record->save();
		//$db->setDebug(false);
		if(!$record->getId()){
		    echo "<pre><code>Impossible d'enregistrer le nouveau produit</code></pre>";
		    return false;
		}
		$record->set('MODE', '');
		return $record;
	}
	private static function importService($srcRow){
		echo ("<pre>Création du service ".$srcRow['nom_produit']." (".$srcRow['code_produit'].") : ".$srcRow['prixvente_produit']." &euro;</pre>");
		
		$record = Vtiger_Record_Model::getCleanInstance('Services');
		$record->set('MODE', 'create');
		$record->set('servicename', $srcRow['nom_produit']);
		$record->set('productcode', $srcRow['code_produit']);
		$record->set('glacct', $srcRow['compte_produit']);
		$record->set('rsnsectionanal', $srcRow['section_produit']);
		$record->set('qtyinstock', $srcRow['stock_produit']);
		$record->set('unit_price', str_replace('.', ',', $srcRow['prixvente_produit'])); //Attention, il faut la virgule...
		$record->set('taxclass', $srcRow['tva_produit']);
		$record->set('discontinued', $srcRow['indisponible'] == 't' ? 0 : 1);
	    
		//$db->setDebug(true);
		$record->save();
		//$db->setDebug(false);
		if(!$record->getId()){
		    echo "<pre><code>Impossible d'enregistrer le nouveau service</code></pre>";
		    return false;
		}
		$record->set('MODE', '');
		return $record;
	}
}