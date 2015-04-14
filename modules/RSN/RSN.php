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
		} else if ($eventType == 'module.enabled') {
			$this->_registerLinks($moduleName);
			$this->setTablesDefaultOwner();
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
	
	/* ajoute le champ accountboss pour identifier le référent du compte
	 * ANNULE : utilisation du champ existant "reference"
	 */ 
	static function contact_addField_accountboss(){
		
		$sql = "ALTER TABLE `vtiger_contactdetails` ADD `accountboss` BOOLEAN NULL AFTER `accountid`";
		
		// ça n'a pas l'air de fonctionner...
		$fieldInstance = new Vtiger_Field();
		$fieldInstance->name = 'Référent du compte';
		$fieldInstance->table = 'vtiger_contactdetails';
		$fieldInstance->column = 'accountboss';
		$fieldInstance->columntype = 'TINYINT(1)';
		$fieldInstance->uitype = 56;
		$fieldInstance->typeofdata = 'V~M';
		
		if(isset($blockInstance))
			$blockInstance->addField($fieldInstance);
	}
}