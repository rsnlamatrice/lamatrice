<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

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
			$this->registerEvents();
			selff::add_mysql_function_levenshtein();
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
	
	
	static function add_mysql_function_levenshtein(){
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
		$db = PearDatabase::getInstance();
		$result = $db->pquery($sql);
	}
}