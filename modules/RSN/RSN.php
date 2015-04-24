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
			$this->setTablesDefaultOwner();
			$this->add_uiclass_field();
			$this->add_fielduirelation_table();
			$this->add_rsncity_table();
			
		} else if($eventType == 'module.disabled') {
			// TODO Handle actions before this module is being uninstalled.
			$this->_deregisterLinks($moduleName);
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
		$sql = "ALTER TABLE  `vtiger_field` ADD  `uiclass` VARCHAR( 255 ) NOT NULL";
		$db = PearDatabase::getInstance();
		$db->pquery($sql);
	}

	/* AV150415
	 * remove the 'uiclass' field in the field table.
	 */ 
	static function remove_uiclass_field(){
		$sql = "ALTER TABLE  `vtiger_field` DROP  `uiclass`";
		$db = PearDatabase::getInstance();
		$db->pquery($sql);
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
}