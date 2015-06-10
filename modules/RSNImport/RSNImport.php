<?php
/*********************************************************************************
 * To create a new import:
 *    - Create a new class in RSNImport module that inherite from Import class.
 *		 -> the showConfiguration method is called when the user choose this import class. It must return a template containing the configuration parameter. If there is no configuration, just don't overload the method.
 *		 -> the preImportData method must save new imported data in the temporary table. (if the cass inherite from ImportFromFile, use the parseAndSaveFile method)
 *		 -> the getImportModules method must return an array containing all the module concerned by the import. (By default, it return only the curent module)
 *       -> the get<<Module>>Field methods must return a list of the imported field in the concerned module.
 *       -> the import<<Module>> methods process to the import from temporary table. If the method does not exit for a module, the default import method is called.
 *    - Add a row in the vtiger_rsnimportsources table.
 *       -> `tabid` is the linked module
 *       -> `class` is the name of the previously created class.
 *
 * 
 ********************************************************************************/

class RSNImport extends Vtiger_CRMEntity {
    
        var $table_name = 'vtiger_rsnimportsources_av';
	var $table_index= 'importsourcesid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array();//'vtiger_rsnimportsourcescf', 'importsourcesid'

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_rsnimportsources_av');
        
        /**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_rsndons' => 'importsourcesid',
                );

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array (
		'LBL_CLASS' => array('rsnimportsources', 'class'),

	);
	var $list_fields_name = Array (
		'LBL_CLASS' => 'compte',

	);

	// Make the field link to detail view
	var $list_link_field = '';

	// For Popup listview and UI type support
	var $search_fields = Array(
		'LBL_CLASS' => array('rsndons', 'origine'),

	);
	var $search_fields_name = Array (
		'LBL_ORIGINE' => 'origine',
		'LBL_ORIGINE_DETAIL' => 'origine_detail',
		'LBL_MONTANT' => 'montant',
		'LBL_DATEDON' => 'invoicedate',
		'LBL_COMPTE' => 'compte',

	);

	public function vtlib_handler($moduleName, $eventType) {
		if ($eventType == 'module.postinstall') {
			self::addRsnImportSourceTable();
			self::initImportSources();
			self::addScheduleTask();
		} else if ($eventType == 'module.enabled') {
			self::enableScheduleTask();
		} else if ($eventType == 'module.disabled') {
			self::disableScheduleTask();
		}
	}

	/**
	 * Method to create the rsnimportsource table.
	 */
	static function addRsnImportSourceTable() {
		$db = PearDatabase::getInstance();
		$sql = "CREATE TABLE IF NOT EXISTS `vtiger_rsnimportsources` (
			`importsourcesid` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
			  `tabid` int(19) NOT NULL,
			  `class` varchar(64) NOT NULL,
			  `disabled` tinyint(1) NOT NULL DEFAULT '0',
			  `sortorderid` int(11) NOT NULL DEFAULT '0',
			  `creationdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4;";
		$db->pquery($sql);
	}

	/**
	 * Method to init the import sources.
	 *  It clear the importsource table and insert the default values.
	 */
	static function initImportSources() {
		$db = PearDatabase::getInstance();
		$sql = "TRUNCATE `vtiger_rsnimportsources`;";
		$db->pquery($sql);
		$sql = "INSERT INTO `vtiger_rsnimportsources` (`tabid`, `class`, `disabled`, `sortorderid`, `creationdate`) VALUES
			(23, 'ImportInvoicesFromPrestashop', 0, 0, '2015-05-06 16:35:19'),
			(23, 'ImportInvoicesFromCogilog', 0, 0, '2015-05-06 16:41:12');";
		$db->pquery($sql);
	}

	/** 
	 * Method to add the schedule task in the cron_task table.
	 */
	static function addScheduleTask() {
		$db = PearDatabase::getInstance();
		$sql = "DELETE FROM `vtiger_cron_task` WHERE name = 'Schedule RSNImport';";
		$db->pquery($sql);
		$sql = "INSERT INTO `vtiger_cron_task` (`name`, `handler_file`, `frequency`, `laststart`, `lastend`, `status`, `module`, `sequence`, `description`) VALUES
			('Schedule RSNImport', 'cron/modules/RSNImport/ScheduledImport.service', 900, 0, 0, 1, 'RSNImport', 7, 'Recommended frequency for RSNImport is 15 mins');";
		$db->pquery($sql);
	}

	/**
	 * Method to enable import cron task
	 */
	static function enableScheduleTask() {
		$db = PearDatabase::getInstance();
		$sql = "UPDATE `vtiger_cron_task`
			SET status = 1
			WHERE name = 'Schedule RSNImport'";
		$db->pquery($sql);
	}

	/**
	 * Method to disable import cron task
	 */
	static function disableScheduleTask() {
		$db = PearDatabase::getInstance();
		$sql = "UPDATE `vtiger_cron_task`
			SET status = 0
			WHERE name = 'Schedule RSNImport'";
		$db->pquery($sql);
	}
}
