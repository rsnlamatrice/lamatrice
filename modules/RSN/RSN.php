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
			if($existingFields[$newFieldName])
				continue;
		
			$tableName = $newField['tablename'];
			
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
}