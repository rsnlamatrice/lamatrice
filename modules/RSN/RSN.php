<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

//modifications ini_set('mbstring',)
// de sorte que $zipCode = mb_strtoupper('iété'); returns "IÉTÉ" et non "IéTé"
ini_set('mbstring.language', 'UTF-8');
ini_set('mbstring.internal_encoding', 'UTF-8');
ini_set('mbstring.http_input', 'UTF-8');
ini_set('mbstring.http_output', 'UTF-8');
ini_set('mbstring.detect_order', 'auto');



class RSN {
	/**
	* Invoked when special actions are performed on the module.
	* @param String Module name
	* @param String Event Type
	*/
	function vtlib_handler($moduleName, $eventType) {
		global $adb;
 		if($eventType == 'module.postinstall') {
			// TODO Handle actions after this module is installed.
			$this->_registerLinks($moduleName);
			$this->add_uiclass_field();
			$this->add_fielduirelation_table();
			$this->add_rsncity_table();
		} else if ($eventType == 'module.enabled') {
			$this->_registerLinks($moduleName);
			$this->setTablesDefaultOwner($moduleName);
			$this->add_uiclass_field();
			$this->add_fielduirelation_table();
			$this->add_rsncity_table();
			$this->add_invoice_handler();
			$this->add_customview_description_field();
			$this->add_customview_orderbyfields_field();
			$this->add_customview_lockstatus_field();
			$this->registerEvents();
			self::add_mysql_function_levenshtein();
			$this->add_users_default_module_field();
			$this->add_vendors_fields();
			$this->add_salesorder_fields();
			$this->add_purchaseorders_fields();
			$this->add_duplicateentities_table();
			$this->add_products_fields();
			$this->add_rsnstatisticsfields_fields();
			$this->add_isabonnable_rsnabotype();
			$this->add_rsnsqlqueries_fields();
			$this->add_rsnstatistics_relatedlist();
			$this->add_organizationparams_table();
			$this->add_crontasks_fields();
			$this->add_rsnreglements_fields();
			$this->add_RSNStatisticsResults_Extension();
			$this->add_documents_fields();
		} else if($eventType == 'module.disabled') {
			// TODO Handle actions before this module is being uninstalled.
			$this->_deregisterLinks($moduleName);
			$this->remove_invoice_handler();
			$this->unregisterEvents();
		} else if($eventType == 'module.preuninstall') {
			// TODO Handle actions when this module is about to be deleted.
		} else if($eventType == 'module.preupdate') {
			// TODO Handle actions before this module is updated.
		} else if($eventType == 'module.postupdate') {
			// TODO Handle actions after this module is updated.
		}
 	}

	/* ED141015
	 * Attribution de assignation par défaut au groupe '(tous)'
	 * Affecte la valeur du champ vtiger_field.defaultvalue des champs smownerid de certaines tables.
	 *
	 * Excécuté lors de l'activation du module RSN / La Matrice
	 */
	protected function setTablesDefaultOwner($moduleName) {
		$exclude_tables = "'RsnTODO',
			'Calendar',
			'Events',
			'Project',
			'ProjectMilestone',
			'ProjectTask'";
		$sql ="UPDATE vtiger_field
		JOIN vtiger_tab ON vtiger_field.tabid = vtiger_tab.tabid
		JOIN vtiger_groups ON vtiger_groups.groupname = '(tous)'
		SET defaultvalue = vtiger_groups.groupid
		WHERE columnname = 'smownerid'
		AND NOT vtiger_tab.name IN ($exclude_tables)
		AND vtiger_field.defaultvalue = ''";
		$db = PearDatabase::getInstance();
		//$db->setDebug(true);
		$db->pquery($sql);
	}

	protected function _registerLinks($moduleName) {
		$thisModuleInstance = Vtiger_Module::getInstance($moduleName);
		if ($thisModuleInstance) {
			$thisModuleInstance->addLink("HEADERCSS", "rsn.css", "layouts/vlayout/modules/RSN/resources/css/style.css");
		}
	}

	protected function _deregisterLinks($moduleName) {
		$thisModuleInstance = Vtiger_Module::getInstance($moduleName);
		if ($thisModuleInstance) {
			$thisModuleInstance->deleteLink("HEADERCSS", "rsn.css", "layouts/vlayout/modules/RSN/resources/css/style.css");
		}
	}
	
	/* AV150415
	 * Add the 'uiclass' field in the field table.
	 */ 
	static function add_uiclass_field(){
		$db = PearDatabase::getInstance();
		
		$sql = "ALTER TABLE  `vtiger_field` ADD  `uiclass` VARCHAR( 255 ) NOT NULL";
		$db->pquery($sql);
		
		
		$sql = "
CREATE TABLE IF NOT EXISTS `vtiger_fielduirelation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field` int(11) NOT NULL,
  `related_field` int(11) DEFAULT NULL,
  `relation` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_FIELDID_FIELDUIRELATION` (`field`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 
";
		$db->pquery($sql);
		
		$sql = "ALTER TABLE `vtiger_fielduirelation`
  ADD CONSTRAINT `FK_FIELDID_FIELDUIRELATION` FOREIGN KEY (`field`) REFERENCES `vtiger_field` (`fieldid`) ON DELETE CASCADE ON UPDATE CASCADE";
		$db->pquery($sql);
	}

	/* AV150415
	 * remove the 'uiclass' field in the field table.
	 * ED150507 : commented because dangerous
	 */ 
	//static function remove_uiclass_field(){
	//	$sql = "ALTER TABLE  `vtiger_field` DROP  `uiclass`";
	//	$db = PearDatabase::getInstance();
	//	$db->pquery($sql);
	//}

	/* TODO Choose the best way between Method+Task vs Handler (called every time)
	
	/* ED150418
	 * Add the 'InvoiceHandler' workflow.
	 */ 
	static function add_invoice_handler(){
		$adb = PearDatabase::getInstance();
		
		//registerEntityMethods
		vimport("~~modules/com_vtiger_workflow/include.inc");
		vimport("~~modules/com_vtiger_workflow/tasks/VTEntityMethodTask.inc");
		vimport("~~modules/com_vtiger_workflow/VTEntityMethodManager.inc");
		$emm = new VTEntityMethodManager($adb);

		// Registering method for Updating Inventory Stock
		//Adding EntityMethod for Updating Products data after creating Invoice
		$emm->addEntityMethod("Invoice","RSNInvoiceSaved","modules/Invoice/InvoiceHandler.php","handleRSNInvoiceSaved");
	}

	/* ED150418
	 * Remove the 'InvoiceHandler' workflow.
	 */ 
	static function remove_invoice_handler(){
		$adb = PearDatabase::getInstance();
		
		//registerEntityMethods
		vimport("~~modules/com_vtiger_workflow/include.inc");
		vimport("~~modules/com_vtiger_workflow/tasks/VTEntityMethodTask.inc");
		vimport("~~modules/com_vtiger_workflow/VTEntityMethodManager.inc");
		$emm = new VTEntityMethodManager($adb);

		// 
		$emm->removeEntityMethod("Invoice","RSNInvoiceSaved");
	
	}
	
	
	/**
	 * Function registers all the event handlers
	 */
	function registerEvents() {
		$adb = PearDatabase::getInstance();
		vimport('~~include/events/include.inc');
		$em = new VTEventsManager($adb);

		// Registering event for Recurring Invoices
		$em->registerHandler('vtiger.entity.aftersave', 'modules/Invoice/InvoiceHandler.php', 'RSNInvoiceHandler');
		$em->setModuleForHandler('Invoice', 'RSNInvoiceHandler');
	}
	
	
	/**
	 * Function registers all the event handlers
	 */
	function unregisterEvents() {
		$adb = PearDatabase::getInstance();
		vimport('~~include/events/include.inc');
		$em = new VTEventsManager($adb);

		// Registering event for Recurring Invoices
		$em->unregisterHandler('RSNInvoiceHandler');
	}

	static function add_fielduirelation_table() {
		$sql = "CREATE TABLE IF NOT EXISTS `vtiger_fielduirelation` (
	  		`id` int(11) NOT NULL AUTO_INCREMENT,
	  		`field` int(11) NOT NULL,
	  		`related_field` int(11) DEFAULT NULL,
	  		`relation` varchar(200) NOT NULL,
	  		PRIMARY KEY (`id`))";
		$db = PearDatabase::getInstance();
		$db->pquery($sql);
	}

	static function remove_fielduirelation_table() {
		$sql = "DROP TABLE `vtiger_fielduirelation`";
		$db = PearDatabase::getInstance();
		$db->pquery($sql);
	}
	
	static function add_rsncity_table() {
		$sql = "CREATE TABLE IF NOT EXISTS `vtiger_rsncity` (
  `rsncityid` int(11) NOT NULL AUTO_INCREMENT,
  `rsncity` varchar(200) NOT NULL,
  `sortorderid` int(11) NOT NULL,
  `presence` int(11) NOT NULL DEFAULT '1',
  `uicolor` varchar(128) DEFAULT NULL,
  `rsnzipcode` varchar(30) NOT NULL,
  `countryalpha2` char(2) NOT NULL,
  PRIMARY KEY (`rsncityid`),
  KEY `countryalpha2` (`countryalpha2`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8";
		$db = PearDatabase::getInstance();
		$db->pquery($sql);
	}
	
	static function add_customview_description_field() {
		$sql = "ALTER TABLE `vtiger_customview` ADD `description` TEXT NULL";
		$db = PearDatabase::getInstance();
		$result = $db->pquery($sql);
	}
	
	static function add_customview_orderbyfields_field() {
		$sql = "ALTER TABLE `vtiger_customview` ADD `orderbyfields` VARCHAR(512) NULL";
		$db = PearDatabase::getInstance();
		$result = $db->pquery($sql);
	}
	
	static function add_customview_lockstatus_field() {
		$sql = "ALTER TABLE `vtiger_customview` ADD `lockstatus` VARCHAR(32) NULL";
		$db = PearDatabase::getInstance();
		$result = $db->pquery($sql);
	}
	
	static function add_users_default_module_field() {
		$sql = "ALTER TABLE `vtiger_users` ADD `default_module` VARCHAR( 64 ) NULL ";
		$db = PearDatabase::getInstance();
		$result = $db->pquery($sql);
		if(!$result)
			return;
		$module = Vtiger_Module_Model::getInstance('Users');
		foreach( $module->getBlocks() as $block1)
			break;
		
		$field3 = new Vtiger_Field();
		$field3->name = 'default_module';
		$field3->label = 'default_module';
		$field3->table = 'vtiger_users';
		$field3->column = 'default_module';
		$field3->columntype = 'VARCHAR(64)';
		$field3->uitype = 16;
		$field3->typeofdata = 'V~O';
		$block1->addField($field3);
	}
	
	static function add_vendors_fields() {
		$module = Vtiger_Module_Model::getInstance('Vendors');
		foreach( $module->getBlocks() as $block1)
			break;
		$existingFields = $module->getFields();
		$newFields = array(
			'contactname' 	=> array( 'columntype' => 'VARCHAR(255)',	),
			'phone2' 	=> array( 'columntype' => 'VARCHAR(32)', 'uitype' => '11',	),
			'fax' 		=> array( 'columntype' => 'VARCHAR(32)', 'uitype' => '11',	),
			'intracom' 	=> array( 'columntype' => 'VARCHAR(32)',	),
			'paymode' 	=> array( 'columntype' => 'VARCHAR(32)',	),
			'paycomment' 	=> array( 'columntype' => 'VARCHAR(255)', 'uitype' => '24',	),
			'paydelay' 	=> array( 'columntype' => 'INT(3)', 'typeofdata' => 'N~O',	),
			'vendorscategory' => array( 'columntype' => 'VARCHAR(32)', 'uitype' => '15',	),
			'creatoruser' 	=> array( 'columntype' => 'VARCHAR(255)',	),
		);
		foreach($newFields as $newFieldName => $newField){
			if($existingFields[$newFieldName])
				continue;
		
			//Pas sûr que ce soit nécessaire
			$sql = "ALTER TABLE `vtiger_vendorcf` ADD `$newFieldName` ".$newField['columntype']." NULL ";
			$db = PearDatabase::getInstance();
			$result = $db->pquery($sql);
			if(!$result){
				/*$db->echoError();
				die($sql);*/
				continue;
			}
			
			$field3 = new Vtiger_Field();
			$field3->name = $newFieldName;
			$field3->label = $newFieldName;
			$field3->table = 'vtiger_vendorcf';
			$field3->column = $newFieldName;
			$field3->columntype = $newField['columntype'];
			$field3->uitype = $newField['uitype'] ? $newField['uitype'] : 1;
			$field3->typeofdata = $newField['typeofdata'] ? $newField['typeofdata'] : 'V~O';
			$block1->addField($field3);
		}
	}
	
	static function add_salesorder_fields() {
		$module = Vtiger_Module_Model::getInstance('SalesOrder');
		foreach( $module->getBlocks() as $block1)
			break;
		$existingFields = $module->getFields();
		$newFields = array(
			'socomment' 	=> array( 'columntype' => 'TEXT', 'uitype' => '19',	),
			'bill_street2' 	=> array( 'columntype' => 'VARCHAR(255)', 'tablename' => 'vtiger_sobillads', 'label' => 'Billing Street 2' ),
			'bill_street3' 	=> array( 'columntype' => 'VARCHAR(255)', 'tablename' => 'vtiger_sobillads', 'label' => 'Billing Street 3' ),
			'bill_addressformat' 	=> array( 'columntype' => 'VARCHAR(8)', 'uitype' => '402', 'tablename' => 'vtiger_sobillads', 'label' => 'Address format' ),
			'ship_street2' 	=> array( 'columntype' => 'VARCHAR(255)', 'tablename' => 'vtiger_soshipads', 'label' => 'Shipping Street 2' ),
			'ship_street3' 	=> array( 'columntype' => 'VARCHAR(255)', 'tablename' => 'vtiger_soshipads', 'label' => 'Shipping Street 3' ),
			'ship_addressformat' 	=> array( 'columntype' => 'VARCHAR(8)', 'uitype' => '402', 'tablename' => 'vtiger_soshipads', 'label' => 'Address format' ),
		);
		$tableName = 'vtiger_salesordercf';
		foreach($newFields as $newFieldName => $newField){
			if($existingFields[$newFieldName]){
				continue;
			}
			
			$field3 = new Vtiger_Field();
			$field3->name = $newFieldName;
			$field3->label = $newField['label'] ? $newField['label'] : $newFieldName;
			$field3->table = $tableName;
			$field3->column = $newFieldName;
			$field3->columntype = $newField['columntype'];
			$field3->uitype = $newField['uitype'] ? $newField['uitype'] : 1;
			$field3->typeofdata = $newField['typeofdata'] ? $newField['typeofdata'] : 'V~O';
			$block1->addField($field3);
		}
	}
	
	
	static function add_purchaseorders_fields() {
		$module = Vtiger_Module_Model::getInstance('PurchaseOrder');
		foreach( $module->getBlocks() as $block1)
			if($block1->get('label') === 'LBL_ADDRESS_INFORMATION')
				break;
		$existingFields = $module->getFields();
		$newFields = array(
			'bill_street2' 	=> array( 'columntype' => 'VARCHAR(255)', 'tablename' => 'vtiger_pobillads', 'label' => 'Billing Street 2' ),
			'bill_street3' 	=> array( 'columntype' => 'VARCHAR(255)', 'tablename' => 'vtiger_pobillads', 'label' => 'Billing Street 3' ),
			'bill_addressformat' 	=> array( 'columntype' => 'VARCHAR(8)', 'uitype' => '402', 'tablename' => 'vtiger_pobillads', 'label' => 'Address format'),
			'ship_street2' 	=> array( 'columntype' => 'VARCHAR(255)', 'tablename' => 'vtiger_poshipads', 'label' => 'Shipping Street 2' ),
			'ship_street3' 	=> array( 'columntype' => 'VARCHAR(255)', 'tablename' => 'vtiger_poshipads', 'label' => 'Shipping Street 3' ),
			'ship_addressformat' 	=> array( 'columntype' => 'VARCHAR(8)', 'uitype' => '402', 'tablename' => 'vtiger_poshipads', 'label' => 'Address format' ),
		);
		foreach($newFields as $newFieldName => $newField){
			self::add_new_field($newFieldName, $newField, $block1, $existingFields);
		}
	}
	
	static function add_products_fields() {
		$module = Vtiger_Module_Model::getInstance('Products');
		foreach( $module->getBlocks() as $block1)
			if($block1->get('label') === 'LBL_PRICING_INFORMATION')
				break;
		$existingFields = $module->getFields();
		$newFields = array(
			'purchaseprice' 	=> array( 'columntype' => 'DECIMAL(25,8)', 'uitype' => '72', 'tablename' => 'vtiger_products', 'label' => 'Purchase price', 'typeofdata' => 'N~0' ),
		);
		foreach($newFields as $newFieldName => $newField){
			self::add_new_field($newFieldName, $newField, $block1, $existingFields);
		}
	}
	
	static function add_rsnsqlqueries_fields() {
		$module = Vtiger_Module_Model::getInstance('RSNSQLQueries');
		foreach( $module->getBlocks() as $block1)
				break;
		$existingFields = $module->getFields();
		$newFields = array(
			'relmodule' 	=> array( 'columntype' => 'VARCHAR(64)', 'uitype' => '86', 'tablename' => 'vtiger_rsnsqlqueries', 'label' => 'LBL_RELMODULE', 'typeofdata' => 'V~M', 'summaryfield' => '1' ),
		);
		foreach($newFields as $newFieldName => $newField){
			self::add_new_field($newFieldName, $newField, $block1, $existingFields);
		}
	}
	
	static function add_rsnreglements_fields() {
		$module = Vtiger_Module_Model::getInstance('RsnReglements');
		foreach( $module->getBlocks() as $block1)
				break;
		$existingFields = $module->getFields();
		$newFields = array(
			'contactname' 	=> array( 'columntype' => 'VARCHAR(255)', 'uitype' => '1', 'tablename' => 'vtiger_rsnreglements', 'label' => 'LBL_CONTACT_NAME', 'typeofdata' => 'V~O' ),
			'origine' 	=> array( 'columntype' => 'VARCHAR(32)', 'uitype' => '1', 'tablename' => 'vtiger_rsnreglements', 'label' => 'LBL_ORIGIN', 'typeofdata' => 'V~O', 'summaryfield' => 1 ),
			'error' 	=> array( 'columntype' => 'INT(1)', 'uitype' => '56', 'tablename' => 'vtiger_rsnreglements', 'label' => 'LBL_ERROR', 'typeofdata' => 'I~O', 'default' => '0' ),
			'errormsg' 	=> array( 'columntype' => 'VARCHAR(255)', 'uitype' => '1', 'tablename' => 'vtiger_rsnreglements', 'label' => 'LBL_ERROR_MESSAGE', 'typeofdata' => 'V~O'),
		);
		foreach($newFields as $newFieldName => $newField){
			self::add_new_field($newFieldName, $newField, $block1, $existingFields);
		}
	}
	
	static function add_rsnstatisticsfields_fields() {
		$module = Vtiger_Module_Model::getInstance('RSNStatisticsFields');
		foreach( $module->getBlocks() as $block1)
				break;
		$existingFields = $module->getFields();
		$newFields = array(
			'sequence' 	=> array( 'columntype' => 'INT(8)', 'uitype' => '1', 'tablename' => 'vtiger_rsnstatisticsfields', 'label' => 'LBL_SEQUENCE', 'typeofdata' => 'N~O', 'default' => '99' ),
			'aggregatefunction' => array( 'columntype' => 'VARCHAR(255)', 'uitype' => '16', 'tablename' => 'vtiger_rsnstatisticsfields', 'label' => 'LBL_AGGREGATE_FUNCTION', 'typeofdata' => 'V~M'
										 , 'default' => 'SUM'
										 , 'picklist_values' => array('SUM', 'COUNT', 'MIN', 'MAX', 'AVG', 'COUNT DISTINCT', 'STD', 'STDDEV'),
										)
		);
		foreach($newFields as $newFieldName => $newField){
			self::add_new_field($newFieldName, $newField, $block1, $existingFields);
		}
	}
	
	static function add_documents_fields() {
		$module = Vtiger_Module_Model::getInstance('Documents');
		foreach( $module->getBlocks() as $block1)
				break;
		$existingFields = $module->getFields();
		$newFields = array(
			'relatedcounter' => array( 'columntype' => 'INT(8)', 'uitype' => '1', 'tablename' => 'vtiger_notes', 'label' => 'LBL_RELATEDCOUNTER', 'typeofdata' => 'N~O' ),
		);
		foreach($newFields as $newFieldName => $newField){
			self::add_new_field($newFieldName, $newField, $block1, $existingFields);
		}
	}
	
	static function add_rsnstatistics_relatedlist() {
		return;//TODO test is exists already
		$module = Vtiger_Module_Model::getInstance('RSNStatistics');
		$module->setRelatedList($module, 'LBL_RESULTS', Array(), 'get_statistics_data');
		//TODO ne semble pas fonctionner
		Vtiger_Module::getInstance('Contacts')->setRelatedList($module, 'RSNStatistics', Array(), 'get_statistics_data');
		Vtiger_Module::getInstance('Documents')->setRelatedList($module, 'RSNStatistics', Array(), 'get_statistics_data');
	}
	
	// fonction générique
	static function add_new_field($newFieldName, $newField, $block1, $existingFields) {
			
		if($existingFields[$newFieldName]){
			if($newField['picklist_values'] && is_array($newField['picklist_values']))
				$existingFields[$newFieldName]->setPicklistValues($newField['picklist_values']);
			return;
		}
	
		$tableName = $newField['tablename'];
		
		$field3 = new Vtiger_Field();
		$field3->name = $newFieldName;
		$field3->label = $newField['label'] ? $newField['label'] : $newFieldName;
		$field3->table = $tableName;
		$field3->column = $newFieldName;
		$field3->columntype = $newField['columntype'];
		$field3->uitype = $newField['uitype'] ? $newField['uitype'] : 1;
		$field3->typeofdata = $newField['typeofdata'] ? $newField['typeofdata'] : 'V~O';
		$field3->summaryfield = $newField['summaryfield'] ? $newField['summaryfield'] : '0';
		$field3->defaultvalue = $newField['defaultvalue'] ? $newField['defaultvalue'] : '';
		$block1->addField($field3);
		if($newField['picklist_values'] && is_array($newField['picklist_values']))
			$field3->setPicklistValues($newField['picklist_values']);
	}
	
	static function add_mysql_function_levenshtein(){
		$db = PearDatabase::getInstance();
		$sql = 'DROP FUNCTION `levenshtein`';
		$db->pquery($sql);
		
		$sql = 'DELIMITER $$
CREATE FUNCTION levenshtein( s1 VARCHAR(255), s2 VARCHAR(255) )
RETURNS INT
DETERMINISTIC
BEGIN
	DECLARE s1_len, s2_len, i, j, c, c_temp, cost INT;
	DECLARE s1_char CHAR;
	-- max strlen=255
	DECLARE cv0, cv1 VARBINARY(256);
	SET s1_len = CHAR_LENGTH(s1)
	, s2_len = CHAR_LENGTH(s2)
	, cv1 = 0x00
	, j = 1
	, i = 1
	, c = 0;
	IF s1 = s2 THEN
		RETURN 0;
	ELSEIF s1_len = 0 THEN
		RETURN s2_len;
	ELSEIF s2_len = 0 THEN
		RETURN s1_len;
	ELSE
		WHILE j <= s2_len DO
			SET cv1 = CONCAT(cv1, UNHEX(HEX(j)))
			, j = j + 1;
		END WHILE;
		WHILE i <= s1_len DO
			SET s1_char = SUBSTRING(s1, i, 1)
			, c = i
			, cv0 = UNHEX(HEX(i))
			, j = 1;
			WHILE j <= s2_len DO
				SET c = c + 1;
				IF s1_char = SUBSTRING(s2, j, 1) THEN
					SET cost = 0;
				ELSE
					SET cost = 1;
				END IF;
				SET c_temp = CONV(HEX(SUBSTRING(cv1, j, 1)), 16, 10) + cost;
				IF c > c_temp THEN SET c = c_temp; END IF;
				SET c_temp = CONV(HEX(SUBSTRING(cv1, j+1, 1)), 16, 10) + 1;
				IF c > c_temp THEN
					SET c = c_temp;
				END IF;
				SET cv0 = CONCAT(cv0, UNHEX(HEX(c)))
				, j = j + 1;
			END WHILE;
			SET cv1 = cv0
			, i = i + 1;
		END WHILE;
	END IF;
	RETURN c;
END$$
DELIMITER ;';
		$result = $db->pquery($sql);
	}
	
	
	static function add_duplicateentities_table() {
		$sql = array();
		
		//$sql[] = "DROP TABLE IF EXISTS `vtiger_duplicateentities`";
		
		$sql[] = "CREATE TABLE IF NOT EXISTS `vtiger_duplicateentities` (
  `crmid1` int(19) NOT NULL,
  `crmid2` int(19) NOT NULL,
  `duplicatestatus` int(4) NOT NULL DEFAULT '0',
  `duplicatefields` varchar(255) DEFAULT NULL,
  `mergeaction` varchar(255) DEFAULT NULL,
  `checkdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`crmid1`,`crmid2`),
  KEY `duplicatestatus` (`duplicatestatus`),
  KEY `duplicatefields` (`duplicatefields`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
";
		
		$db = PearDatabase::getInstance();
		foreach($sql as $query){
			$db->pquery($query);
		}
	}
	
	/* ED151017
	 * Champ isabonnable des types d'abonnement
	 */ 
	static function add_isabonnable_rsnabotype(){
		
		$db = PearDatabase::getInstance();
		
		$sql = "ALTER TABLE `vtiger_rsnabotype` ADD `isabonnable` BOOLEAN NULL DEFAULT TRUE , ADD INDEX (`isabonnable`) ;";
		$db->pquery($sql);
		
		$sql = "UPDATE `vtiger_rsnabotype`
			SET `isabonnable` = 0
			WHERE rsnabotype IN ('Dépôt', 'Ne pas abonner', 'Non abonné')";
		$db->pquery($sql);
	}
	
	/* ED151020
	 * Champs supplémentaires de paramétrage de l'entreprise
	 */ 
	static function add_organizationparams_table(){
		
		$db = PearDatabase::getInstance();
		
		$sql = "

CREATE TABLE IF NOT EXISTS `vtiger_organizationsubdetails` (
  `organization_id` int(11) NOT NULL DEFAULT '1',
  `parameter` varchar(64) NOT NULL,
  `context` varchar(32) NOT NULL,
  `label` varchar(128) DEFAULT NULL,
  `value` text,
  `uitype` int(11) NOT NULL DEFAULT '1',
  `description` text NOT NULL,
  `uiclass` varchar(255) DEFAULT NULL,
  `visible` tinyint(4) NOT NULL DEFAULT '1',
  `sequence` int(11) NOT NULL DEFAULT '0',
  `createdtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifiedtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifieduser` int(11) DEFAULT NULL,
  PRIMARY KEY (`organization_id`,`parameter`,`context`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Contenu de la table `vtiger_organizationsubdetails`
--

INSERT INTO `vtiger_organizationsubdetails` (`organization_id`, `parameter`, `context`, `label`, `value`, `uitype`, `description`, `uiclass`, `visible`, `sequence`, `createdtime`, `modifiedtime`, `modifieduser`) VALUES
(1, 'bic', 'sepa', 'BIC', 'CCOPFRPPXXX', 1, '', NULL, 1, 103, '2015-11-04 17:23:08', '2015-11-04 17:23:08', 1),
(1, 'compte', 'sepa', 'N° de compte', '21029208904', 1, '', NULL, 1, 107, '2015-11-04 17:23:08', '2015-11-04 17:23:08', 1),
(1, 'emetteur', 'sepa', 'Emetteur', 'RES. SORTIR DU NUCLEAIRE', 1, '', NULL, 1, 100, '2015-11-04 17:23:08', '2015-11-04 17:23:08', 1),
(1, 'etablissement', 'sepa', 'Etablissement', '76', 1, '', NULL, 1, 105, '2015-11-04 17:23:08', '2015-11-04 17:23:08', 1),
(1, 'guichet', 'sepa', 'Guichet', '11', 1, '', NULL, 1, 106, '2015-11-04 17:23:08', '2015-11-04 17:23:08', 1),
(1, 'header_text', 'inventory', 'En-tête d''impression de factures, devis, ..., sous l''adresse', 'Fédération de plus de 930 associations,\r\nagréée pour la protection de l''environnement.\r\n\r\nContact : nadia.boukacem@sortirdunucleaire.fr', 19, '', NULL, 1, 200, '2015-11-04 17:13:43', '2015-11-04 17:13:43', 1),
(1, 'header_text', 'lettertoaccount', 'En-tête d''impression, sous l''adresse', 'Fédération de plus de 930 associations,\r\nagréée pour la protection de l''environnement.\r\n\r\nContact : nadia.boukacem@sortirdunucleaire.fr', 19, '', NULL, 1, 210, '2015-11-04 17:13:43', '2015-11-04 17:13:43', 1),
(1, 'header_text', 'rsnprelevements', 'En-tête d''impression des lettres de remerciements pour nouveaux prélèvements, sous l''adresse', 'Fédération de plus de 930 associations,\r\nagréée pour la protection de l''environnement.\r\n\r\nContact : annie.orenga@sortirdunucleaire.fr', 19, '', NULL, 1, 220, '2015-11-04 17:13:43', '2015-11-04 17:13:43', 1),
(1, 'ibancle', 'sepa', 'IBAN - Cle', '76', 1, '', NULL, 1, 102, '2015-11-04 17:23:08', '2015-11-04 17:23:08', 1),
(1, 'ibanpays', 'sepa', 'IBAN - Pays', 'FR', 1, '', NULL, 1, 101, '2015-11-04 17:23:08', '2015-11-04 17:23:08', 1),
(1, 'ics', 'sepa', 'N° ICS', 'FR72ZZZ434147', 1, '', NULL, 1, 104, '2015-11-04 17:23:08', '2015-11-04 17:23:08', 1),
(1, 'lastpage_footer_text', 'inventory', 'Bas de page d''impression de factures, devis, ...', 'Association \"loi de 1901\", organisme à but non lucratif partiellement assujetti à la TVA.\r\nSiret : 418 092 094 00014 - TVA intracommunautaire : FR43 418092094', 19, '', NULL, 1, 201, '2015-11-04 17:13:43', '2015-11-04 17:13:43', 1),
(1, 'lastpage_footer_text', 'lettertoaccount', 'Bas de page d''impression de courriers aux contacts', 'Association \"loi de 1901\", organisme à but non lucratif. Siret : 418 092 094 00014 ', 19, '', NULL, 1, 210, '2015-11-04 17:13:43', '2015-11-04 17:13:43', 1),
(1, 'phone', 'rsnprelevements', 'Téléphone de la compta', '04 82 53 38 58', 1, '', NULL, 1, 221, '2015-11-04 17:13:43', '2015-11-04 17:13:43', 1),
(1, 'ribcle', 'sepa', 'Clé', '7', 1, '', NULL, 1, 108, '2015-11-04 17:23:08', '2015-11-04 17:23:08', 1);
";
		$db->query($sql);
	}
	
	/* ED151021
	 * Champs supplémentaires de paramétrage des cron
	 */ 
	static function add_crontasks_fields(){
		
		$db = PearDatabase::getInstance();
		
		$sql = "ALTER TABLE `vtiger_cron_task`
			ADD `start_hour` FLOAT NULL COMMENT 'Start hour for daily task ' AFTER `frequency`;";
		$db->query($sql);
	}
	
	/* ED151025
	 * Crée le module d'extension RSNStatisticsResults
	 */ 
	static function add_RSNStatisticsResults_Extension(){
		$MODULENAME = 'RSNStatisticsResults';
		
		$moduleInstance = Vtiger_Module::getInstance($MODULENAME);
		if ($moduleInstance){// || file_exists(dirname(__FILE__).'/../'.$MODULENAME)) {
		   //echo "Module already present - choose a different name.";
		} else {
		   $moduleInstance = new Vtiger_Module();
		   $moduleInstance->name = $MODULENAME;
		   $moduleInstance->parent= 'Analytics';
		   $moduleInstance->isentitytype = true;
		   $moduleInstance->version = '1.0.0';
		   $moduleInstance->save();
		
		   mkdir(dirname(__FILE__).'/../'.$MODULENAME);

			$db = PearDatabase::getInstance();
			$sql = "INSERT INTO `vtiger_ws_entity` (`id`, `name`, `handler_path`, `handler_class`, `ismodule`, `uicolorfield`)
				VALUES (NULL, 'RSNStatisticsResults', 'include/Webservices/VtigerModuleOperation.php', 'VtigerModuleOperation', '1', NULL);";
			$db->query($sql);
		}
	}
	
	
}
