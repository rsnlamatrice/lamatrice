<?php


/* Phase de migration
 * Importation des remises produits depuis le fichier provenant de Cogilog
 *
 * L'export s'effectue par la requête
 * SELECT "gprodu00002"."code", "gprodu00002"."nom", "gtremi00002"."nom", "grprod00002".* FROM "public"."gtremi00002" AS "gtremi00002", "public"."grprod00002" AS "grprod00002", "public"."gprodu00002" AS "gprodu00002" WHERE "gtremi00002"."id" = "grprod00002"."id_gtremi" AND "grprod00002"."id_gprodu" = "gprodu00002"."id"
 *
 * WINDOWS-1252
 */
class RSNImportSources_ImportRemisesProduitsFromCogilog_View extends RSNImportSources_ImportFromFile_View {
        
	/**
	 * Method to get the source import label to display.
	 * @return string - The label.
	 */
	public function getSource() {
		return 'LBL_REMISESPROD_COGILOG';
	}

	/**
	 * Method to get the modules that are concerned by the import.
	 * @return array - An array containing concerned module names.
	 */
	public function getImportModules() {
		return array('Products');
	}

	/**
	 * Method to default file enconding for this import.
	 * @return string - the default file encoding.
	 */
	public function getDefaultFileEncoding() {
		return 'WINDOWS-1252';
	}

	/**
	 * Method to get the source type label to display.
	 * @return string - The label.
	 */
	public function getSourceType() {
		return 'LBL_CSV_FILE';
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
	 * Method to get the imported fields for the products module.
	 * @return array - the imported fields for the products module.
	 */
	function getProductsFieldsMapping() {
		//laisser exactement les colonnes du fichier, dans l'ordre 
		return array (
			"productcode"=>"productcode",
			"productname" => "",
			"moderemise"=>"listpriceunit",//0 : %, 1 : HT, 2 : TTC
			"quantiteddef"=>"",//1 : avec qty
			"nomremise"=>"",//Aucune remise : 0, depôt : dv, groupe signataire : grp
			"taux"=>"listprice",
			"quantite" => "minimalqty",
			
			"_productid" => "",
			"_module" => "",
		);
	}
	
	function getProductsDateFields(){
		return array();
	}
	
	/**
	 * Method to get the imported fields for the products module.
	 * @return array - the imported fields for the products module.
	 */
	function getProductsFields() {
		//laisser exactement les colonnes du fichier
		return array_keys($this->getProductsFieldsMapping());
	}

	/**
	 * Method to process to the import of the Products module.
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importProducts($importDataController) {
		global $VTIGER_BULK_SAVE_MODE;
		$VTIGER_BULK_SAVE_MODE = true;
		$config = new RSNImportSources_Config_Model();
		
		$this->beforeImportData();
		
		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($this->user, 'Products');
		$sql = 'SELECT * FROM ' . $tableName . ' WHERE status = '. RSNImportSources_Data_Action::$IMPORT_RECORD_NONE . ' ORDER BY id';

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
			$this->importOneProducts(array($row), $importDataController);
			$perf->tick();
			if(Import_Utils_Helper::isMemoryUsageToHigh()){
				$this->skipNextScheduledImports = true;
				$keepScheduledImport = true;
				break;
			}
		}
		$perf->terminate();
		
		if(isset($keepScheduledImport))
			$this->keepScheduledImport = $keepScheduledImport;
		elseif($numberOfRecords == $config->get('importBatchLimit')){
			$this->keepScheduledImport = $this->getNumberOfRecords() > 0;
		}
	}

	/**
	 * Method to process to the import of a one prelevement.
	 * @param $productsData : the data of the prelevement to import
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importOneProducts($productsData, $importDataController) {
					
		global $log;
		
		$productId = $productsData[0]['_productid'];
		
		$db = PearDatabase::getInstance();
		if(!$productId){
			//already imported !!
			foreach ($productsData as $productsLine) {
				$entityInfo = array(
					'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
				);
				
				//TODO update all with array
				$importDataController->updateImportStatus($productsLine[id], $entityInfo);
			}
		}
		else {
			
			$relatedModuleModel = Vtiger_Module_Model::getInstance('PriceBooks');
			
			//
			//"moderemise"=>"listpriceunit",//0 : %, 1 : HT, 2 : TTC
			//"quantiteddef"=>"",//1 : avec qty
			//"nomremise"=>"",//Aucune remise : 0, depôt : dv, groupe signataire : grp
			//"taux"=>"listprice",
			//"quantite" => "minimalqty",
			//
			$discountType = $productsData[0]['nomremise'];
			$quantity = $productsData[0]['quantite'];
			$price = str_to_float($productsData[0]['taux']);
			$priceUnit = $productsData[0]['moderemise'];
			
			$pricebookId = $relatedModuleModel->getPriceBookRecordId($discountType, $quantity);
			if(!$pricebookId)
				throw new Exception('Impossible de trouver le pricebook');
			
			$query = "INSERT INTO vtiger_pricebookproductrel (`pricebookid`, `productid`, `listprice`, `listpriceunit`, `usedcurrency`)
				VALUES( ?, ?, ?, ?, 1)";
			$params = array($pricebookId, $productId, $price, $priceUnit);
			$result = $db->pquery($query, $params);
			if(!$result){
				echo "<pre>$query</pre>";
				var_dump($params);
				$db->echoError();
				die();
			}
			
			$entryId = $productId;
			foreach ($productsData as $productsLine) {
				$entityInfo = array(
					'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_CREATED,
					'id'		=> $entryId
				);
				$importDataController->updateImportStatus($productsLine[id], $entityInfo);
			}
			
			return true;
		}

		return true;
	}

	/**
	 * Method that pre import an invoice.
	 *  It adds one row in the temporary pre-import table by invoice line.
	 * @param $productsData : the data of the invoice to import.
	 */
	function preImportProducts($productsData) {
		
		$productsValues = $this->getProductsValues($productsData);
		
		$products = new RSNImportSources_Preimport_Model($productsValues, $this->user, 'Products');
		$products->save();
	}
	
	/**
	 * Method to parse the uploaded file and save data to the temporary pre-import table.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - true if pre-import is ended successfully
	 */
	function parseAndSaveFile(RSNImportSources_FileReader_Reader $fileReader) {
		$this->clearPreImportTable();
		
		if($fileReader->open()) {
			if ($this->moveCursorToNextProducts($fileReader)) {
				$i = 0;
				do {
					$products = $this->getNextProducts($fileReader);
					if ($products != null) {
						$this->preImportProducts($products);
					}
				} while ($products != null);

			}

			$fileReader->close(); 
			return true;
		} else {
			//TODO: manage error
			echo "<code>le fichier n'a pas pu être ouvert...</code>";
		}
		return false;
	}
    

	/**
	 * Method called after the file is processed.
	 *  This method must be overload in the child class.
	 */
	function postPreImportData() {
		// Pré-identifie les produits
		$db = PearDatabase::getInstance();
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'Products');
		
		/* Affecte l'id du produit
		*/
		$query = "UPDATE $tableName
		JOIN  vtiger_products
			ON  vtiger_products.productcode = `$tableName`.productcode
		JOIN vtiger_crmentity
			ON vtiger_products.productid = vtiger_crmentity.crmid
		";
		$query .= " SET `_productid` = vtiger_crmentity.crmid
		, _module = vtiger_crmentity.setype";
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
		/* Affecte l'id du service
		*/
		$query = "UPDATE $tableName
		JOIN  vtiger_service
			ON  vtiger_service.productcode = `$tableName`.productcode
		JOIN vtiger_crmentity
			ON vtiger_service.serviceid = vtiger_crmentity.crmid
		";
		$query .= " SET `_productid` = vtiger_crmentity.crmid
		, _module = vtiger_crmentity.setype";
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
		
		/* productid manquant
		*/
		$query = "UPDATE $tableName
		";
		$query .= " SET status = ?";
		$query .= "
			WHERE _productid IS NULL
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

	/**
	 * Method called after the file is processed.
	 *  This method must be overload in the child class.
	 */
	function beforeImportData() {
		// Pré-identifie les produits
		$db = PearDatabase::getInstance();
		$tableName = RSNImportSources_Utils_Helper::getDbTableName($this->user, 'Products');
		
		//Clear DB data
		$query = "DELETE vtiger_pricebookproductrel
			FROM vtiger_pricebookproductrel
			JOIN $tableName
				ON $tableName._productid = vtiger_pricebookproductrel.productid
			WHERE `$tableName`.status = ".RSNImportSources_Data_Action::$IMPORT_RECORD_NONE."
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
	 * Method that check if a line of the file is a products information line.
	 *  It assume that the line is a client information line only and only if the first data is a date.
	 * @param array $line : the data of the file line.
	 * @return boolean - true if the line is a products information line.
	 */
	function isRecordHeaderInformationLine($line) {
		if (sizeof($line) > 0 && $line[0] && is_numeric($line[2])) {
			return true;
		}

		return false;
	}

	/**
	 * Method that move the cursor of the file reader to the beginning of the next found invoice.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - false if error or if no invoice found.
	 */
	function moveCursorToNextProducts(RSNImportSources_FileReader_Reader $fileReader) {
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
	function getNextProducts(RSNImportSources_FileReader_Reader $fileReader) {
		$nextLine = $fileReader->readNextDataLine($fileReader);
		if ($nextLine != false) {
			$products = array(
				'header' => $nextLine,
				'detail' => array());
			do {
				$cursorPosition = $fileReader->getCurentCursorPosition();
				$nextLine = $fileReader->readNextDataLine($fileReader);

				if (!$this->isRecordHeaderInformationLine($nextLine)) {
					if ($nextLine[1] != null && $nextLine[1] != '') {
						//impossible ici array_push($products['detail'], $nextLine);
					}
				} else {
					break;
				}

			} while ($nextLine != false);

			if ($nextLine != false) {
				$fileReader->moveCursorTo($cursorPosition);
			}
			return $products;
		}

		return null;
	}
	
	/**
	 * Method that return the formated information of a record found in the file.
	 * @param $product : the invoice data found in the file.
	 * @return array : the formated data of the invoice.
	 */
	function getProductsValues($product) {
		$fields = $this->getProductsFields();
		
		// contrôle l'égalité des tailles de tableaux
		if(count($fields) != count($product['header'])){
			if(count($fields) > count($product['header']))
				$product['header'] = array_merge($product['header'], array_fill (0, count($fields) - count($product['header']), null));
			else
				$product['header'] = array_slice($product['header'], 0, count($fields));
		}
		//tableau associatif dans l'ordre fourni
		$productHeader = array_combine($fields, $product['header']);
		
		//Parse dates
		foreach($this->getProductsDateFields() as $fieldName)
			$productHeader[$fieldName] = $this->getMySQLDate($productHeader[$fieldName]);
		
		$fieldName = "productname";
		$productHeader[$fieldName] = substr($productHeader[$fieldName], 0, 64);
		
		$fieldName = "moderemise";//=>"listpriceunit",//0 : %, 1 : HT, 2 : TTC
		switch($productHeader[$fieldName]){
			case '0' :
				$productHeader[$fieldName] = '%';
				break;
			case '1' :
				$productHeader[$fieldName] = 'HT';
				break;
			case '2' :
				$productHeader[$fieldName] = 'TTC';
				break;
			default :
				echo "Mode de remise inconnu : " . $productHeader[$fieldName];
				break;
		}
		
		$fieldName = "quantiteddef";//1 : avec qty
		switch($productHeader[$fieldName]){
			case '1' :
				$productHeader[$fieldName] = 'HT';
				break;
			default :
				echo "Mode de saisie inconnu : " . $productHeader[$fieldName];
				break;
		}
		
		//"nomremise"=>"",//Aucune remise : 0, depôt : dv, groupe signataire : grp
		$fieldName = "nomremise";
		switch($productHeader[$fieldName]){
			case 'Aucune remise' :
				$productHeader[$fieldName] = '0';
				break;
			case 'depôt' :
				$productHeader[$fieldName] = 'dv';
				break;
			case 'groupe signataire' :
				$productHeader[$fieldName] = 'grp';
				break;
			default :
				echo "Type de remise inconnu : " . $productHeader[$fieldName];
				break;
		}
		//"taux"=>"listprice",
		//"quantite" => "minimalqty",
		
		return $productHeader;
	}
	
}