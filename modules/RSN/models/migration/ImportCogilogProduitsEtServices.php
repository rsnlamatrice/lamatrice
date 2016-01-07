<?php
/*
 * Les Produits et Services importés depuis Cogilog 
 *
 *
 */

 
class RSN_CogilogProduitsEtServices_Import {

	/* get postgresql data rows */
	private static function getPGDataRows($table_name){
			
		$dbconn = self::get_db_connect();
		
		if(stripos($table_name, 'SELECT ') === 0){
			$query = $table_name;
			if(!preg_match('/\sLIMIT\s/i', $query))
				$query .= ' LIMIT 999';
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
        
	// Importe les affaires qui n'ont pas encore été importées
	public static function importNexts(){
            
		$srcRows = self::getEntries();
		$doneRows = array();
		
		foreach($srcRows as $srcRow){
			
			if(self::importRow($srcRow)){
				$srcRow['LA MATRICE'] = 'Importée';
				
				//break;
			}
			else
				$srcRow['LA MATRICE'] = 'Non';
				
			$doneRows[] = $srcRow;
		}
                return $doneRows;
	}
        
	/*
	 * Les entrées contiennent autant de lignes que de lignes de factures
	 */
	private static function getEntries(){
            
		$query = 'SELECT DISTINCT "produit"."id" AS "id_produit"
			, "produit"."nom" AS "nom_produit"
			, "produit"."code" AS "code_produit"
			, "produit"."gererstock" AS "gererstock"
			, "produit"."stockdivers" AS "stockdivers"
			, "produit"."prix" AS "prixvente_produit"
			, "produit"."achat" AS "prixachat_produit"
			, "produit"."stockqte" AS "stock_produit"
			, "produit"."compte" AS "compte_produit"
			, "produit"."compteachat" AS "compteachat_produit"
			, "produit"."section" AS "section_produit"
			, "produit"."codetva" AS "tva_produit"
			, "codetauxtva"."taux" AS "tva_taux"
			, "produit"."indisponible" AS "indisponible"
			, "famille"."nom" AS "produit_famille"
			, "produit"."tssaisie" AS "createdtime"
			, "produit"."tsmod" AS "modifiedtime"
	
			FROM glfact00002 ligne_fact
			INNER JOIN "gprodu00002" AS "produit"
				ON "ligne_fact"."id_gprodu" = "produit"."id"
			INNER JOIN "gtprod00002" AS "famille"
				ON "famille"."id" = "produit"."id_gtprod"
			LEFT JOIN "gtvacg00002" AS "codetauxtva"
				ON "produit"."codetva" = "codetauxtva"."code"
				
		';
			
		
		/*$query .= ' 
			WHERE "produit"."code" = \'LCRAC\'
			LIMIT 1
			ORDER BY id_produit'; */
		return self::getPGDataRows($query);
    }
        
	/* Import de l'enregistrement
	 * @returns note record
	 */
	private static function importRow($row){
		
		//Imports Document
		$record = self::getProductOrService($row, true);
		if(!$record) return false;
		
		return $record;
	}
	
	public static function getProductOrService($srcRow, $doUpdate = false){
		$product = self::findProduct($srcRow);
		if(!$product)
			$product = self::findService($srcRow);
		if(!$product){
			if($srcRow['gererstock'] == 't' || $srcRow['stockdivers'] != 0)
				$product = self::importProduct($srcRow);
			else
				$product = self::importService($srcRow);
		}
		elseif($doUpdate)
			self::updateProduct($product, $srcRow);
		return $product;
	}
	
	private static function findProduct($srcRow){
		$codeProduct=$srcRow['code_produit'];
		$query = "SELECT productid
			FROM vtiger_products
                        JOIN vtiger_crmentity
                            ON vtiger_products.productid = vtiger_crmentity.crmid
			WHERE productcode = ?
			AND vtiger_crmentity.deleted = 0
			LIMIT 1
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
			AND vtiger_crmentity.deleted = 0
			LIMIT 1
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
		$record->set('mode','create');
		$record->set('compteachat', $srcRow['compteachat_produit']);
		$record->set('productname', $srcRow['nom_produit']);
		$record->set('productcode', $srcRow['code_produit']);
		$record->set('glacct', $srcRow['compte_produit']);
		$record->set('rsnsectionanal', $srcRow['section_produit']);
		$record->set('qtyinstock', $srcRow['stock_produit']);
		$record->set('unit_price', str_replace('.', ',', $srcRow['prixvente_produit'])); //Attention, il faut la virgule...
		$record->set('taxclass', $srcRow['tva_produit']);
		$record->set('discontinued', $srcRow['indisponible'] == 't' ? 0 : 1);
		$record->set('purchaseprice', str_replace('.', ',', $srcRow['prixachat_produit'])); //Attention, il faut la virgule...
		self::setProductCategory($record, $srcRow);
	    
		$db = PearDatabase::getInstance();
		//$db->setDebug(true);
		$record->save();
		//$db->setDebug(false);
		if(!$record->getId()){
		    echo "<pre><code>Impossible d'enregistrer le nouveau produit</code></pre>";
		    return false;
		}
		$record->set('mode','');
		self::updateProductDates($record, $srcRow);
		return $record;
	}
	private static function importService($srcRow){
		echo ("<pre>Création du service ".$srcRow['nom_produit']." (".$srcRow['code_produit'].") : ".$srcRow['prixvente_produit']." &euro;</pre>");
		
		$record = Vtiger_Record_Model::getCleanInstance('Services');
		$record->set('mode','create');
		$record->set('servicename', $srcRow['nom_produit']);
		$record->set('productcode', $srcRow['code_produit']);
		$record->set('glacct', $srcRow['compte_produit']);
		$record->set('rsnsectionanal', $srcRow['section_produit']);
		$record->set('unit_price', str_replace('.', ',', $srcRow['prixvente_produit'])); //Attention, il faut la virgule...
		$record->set('taxclass', $srcRow['tva_produit']);
		$record->set('discontinued', $srcRow['indisponible'] == 't' ? 0 : 1);
		self::setProductCategory($record, $srcRow);
	    
		//$db->setDebug(true);
		$record->save();
		//$db->setDebug(false);
		if(!$record->getId()){
		    echo "<pre><code>Impossible d'enregistrer le nouveau service</code></pre>";
		    return false;
		}
		$record->set('mode','');
		self::updateProductDates($record, $srcRow);
		return $record;
	}
	
	private static function updateProduct($record, $srcRow){
		echo ("<pre>Mise à jour ".$srcRow['nom_produit']." (".$srcRow['code_produit'].") : ".$srcRow['prixvente_produit']." &euro;</pre>");
		$record->set('mode','edit');
		if($record->getModuleName() == "Services"){
			$record->set('servicename', $srcRow['nom_produit']);
		}
		else{
			$record->set('compteachat', $srcRow['compteachat_produit']);
			$record->set('productname', $srcRow['nom_produit']);
			$record->set('qtyinstock', $srcRow['stock_produit']);
		}
		$record->set('glacct', $srcRow['compte_produit']);
		$record->set('rsnsectionanal', $srcRow['section_produit']);
		$record->set('unit_price', str_replace('.', ',', $srcRow['prixvente_produit'])); //Attention, il faut la virgule...
		$record->set('taxclass', $srcRow['tva_produit']);
		$record->set('discontinued', $srcRow['indisponible'] == 't' ? 0 : 1);
		$record->set('purchaseprice', str_replace('.', ',', $srcRow['prixachat_produit'])); //Attention, il faut la virgule...
		self::setProductCategory($record, $srcRow);
		
		$db = PearDatabase::getInstance();
		//$db->setDebug(true);
		$record->save();
		//$db->setDebug(false);
		if(!$record->getId()){
		    echo "<pre><code>Impossible de mettre à jour le produit</code></pre>";
		    return false;
		}
		$record->set('mode','');
		self::updateProductDates($record, $srcRow);
		self::updateProductTaxRel($record, $srcRow);
		return $record;
	}
	
	private static function setProductCategory($record, $srcRow){
		if($record->getModuleName() == "Services")
			$fieldName = 'servicecategory';
		else
			$fieldName = 'productcategory';
		$record->set($fieldName, decode_html($srcRow['produit_famille']));
	    
		RSNImportSources_Utils_Helper::checkPickListValue($record->getModuleName() , $fieldName, $fieldName, $record->get($fieldName));
			
		
	}
	
	private static function updateProductDates($record, $srcRow){
		$query = "UPDATE vtiger_crmentity
			SET createdtime = ?, modifiedtime = ?
			WHERE vtiger_crmentity.crmid = ?
			LIMIT 1
		";
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, array(substr( $srcRow['createdtime'], 0, 19), substr($srcRow['modifiedtime'], 0, 19), $record->getId()));
		return $record;
	}
	
	static $allTaxes;
	
	private static function findTaxId($srcRow){
		if(!$srcRow['tva_taux'])
			return false;
		if(!self::$allTaxes)
			self::$allTaxes = getAllTaxes();
		foreach(self::$allTaxes as $tax)
			if(!$tax['deleted'] && $tax['percentage'] == $srcRow['tva_taux'])
				return $tax['taxid'];
		echo 'Taux de TVA introuvable : ' . $srcRow['tva_taux'];
		return false;
	}
	
	private static function updateProductTaxRel($record, $srcRow){
		$taxId = self::findTaxId($srcRow);
		
		global $adb, $log;
		$sql = "delete from vtiger_producttaxrel where productid=?";
		$adb->pquery($sql, array($record->getId()));
		
		if($taxId){
			$query = "insert into vtiger_producttaxrel (`productid`, `taxid`, `taxpercentage`) values(?,?,?)";
			$adb->pquery($query, array($record->getId(), $taxId, $srcRow['tva_taux']));
		}
	}
}