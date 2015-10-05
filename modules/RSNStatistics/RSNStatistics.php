<?php
/***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

include_once 'modules/Vtiger/CRMEntity.php';

class RSNStatistics extends Vtiger_CRMEntity {
	var $table_name = 'vtiger_rsnstatistics';
	var $table_index= 'rsnstatisticsid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_rsnstatisticscf', 'rsnstatisticsid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_rsnstatistics', 'vtiger_rsnstatisticscf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_rsnstatistics' => 'rsnstatisticsid',
		'vtiger_rsnstatisticscf'=>'rsnstatisticsid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array (
		'LBL_STAT_NAME' => array('rsnstatistics', 'stat_name'),
		'LBL_RELMODULE' => array('rsnstatistics', 'relmodule'),

	);
	var $list_fields_name = Array (
		'LBL_STAT_NAME' => 'stat_name',
		'LBL_RELMODULE' => 'relmodule',

	);

	// Make the field link to detail view
	var $list_link_field = '';

	// For Popup listview and UI type support
	var $search_fields = Array(

	);
	var $search_fields_name = Array (

	);

	// For Popup window record selection
	var $popup_fields = Array ('');

	// For Alphabetical search
	var $def_basicsearch_col = '';

	// Column value to use on detail view record text display
	var $def_detailview_recname = '';

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('','assigned_user_id');

	var $default_order_by = '';
	var $default_sort_order='ASC';

	function RSNStatistics() {
		$this->log =LoggerManager::getLogger('RSNStatistics');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('RSNStatistics');
	}

	/**
	* Invoked when special actions are performed on the module.
	* @param String Module name
	* @param String Event Type
	*/
	function vtlib_handler($moduleName, $eventType) {
 		if($eventType == 'module.postinstall') {
			//Delete duplicates from all picklist
			static::deleteDuplicatesFromAllPickLists($moduleName);
		} else if($eventType == 'module.disabled') {
			// TODO Handle actions before this module is being uninstalled.
		} else if($eventType == 'module.preuninstall') {
			// TODO Handle actions when this module is about to be deleted.
		} else if($eventType == 'module.preupdate') {
			// TODO Handle actions before this module is updated.
		} else if($eventType == 'module.postupdate') {
			//Delete duplicates from all picklist
			static::deleteDuplicatesFromAllPickLists($moduleName);
		}
 	}

 	/*
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
		$emm->addEntityMethod("RSNStatistics","RSNStatisticsSaved","modules/RSNStatistics/RSNStatisticsHandler.php","handleRSNInvoiceSaved");
	}

	/*
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
		$emm->removeEntityMethod("RSNStatistics","RSNStatisticsSaved");
	
	}

	//tmp: call an handler to update the field of the tmp table ! (new field stat!!)
	function save_related_module($module, $crmid, $with_module, $with_crmid) {
		$tableName = RSNStatistics_Utils_Helper::getStatsTableNameFromId($crmid);
		//echo "Add a column in the stat field table!! " . $tableName . "<br/>";
		$sql = "ALTER TABLE `" . $tableName . "` ADD`" . RSNStatistics_Utils_Helper::getFieldUniqueCodeFromId($with_crmid) . "` DOUBLE";//tmp double -> use a specified data type !!
		$db = PearDatabase::getInstance();
		$db->pquery($sql);

		parent::save_related_module($module, $crmid, $with_module, $with_crmid);
	}
	
	/**
	 * Delete doubloons from all pick list from module
	 */
	public static function deleteDuplicatesFromAllPickLists($moduleName)
	{
		global $adb,$log;

		$log->debug("Invoking deleteDuplicatesFromAllPickList(".$moduleName.") method ...START");

		//Deleting doubloons
		$query = "SELECT columnname FROM `vtiger_field` WHERE uitype in (15,16,33) "
				. "and tabid in (select tabid from vtiger_tab where name = '$moduleName')";
		$result = $adb->pquery($query, array());

		$a_picklists = array();
		while($row = $adb->fetchByAssoc($result))
		{
			$a_picklists[] = $row["columnname"];
		}
		
		foreach ($a_picklists as $picklist)
		{
			static::deleteDuplicatesFromPickList($picklist);
		}
		
		$log->debug("Invoking deleteDuplicatesFromAllPickList(".$moduleName.") method ...DONE");
	}
	
	public static function deleteDuplicatesFromPickList($pickListName)
	{
		global $adb,$log;
		
		$log->debug("Invoking deleteDuplicatesFromPickList(".$pickListName.") method ...START");
	
		//Deleting doubloons
		$query = "SELECT {$pickListName}id FROM vtiger_{$pickListName} GROUP BY {$pickListName}";
		$result = $adb->pquery($query, array());
	
		$a_uniqueIds = array();
		while($row = $adb->fetchByAssoc($result))
		{
			$a_uniqueIds[] = $row[$pickListName.'id'];
		}
	
		if(!empty($a_uniqueIds))
		{
			$query = "DELETE FROM vtiger_{$pickListName} WHERE {$pickListName}id NOT IN (".implode(",", $a_uniqueIds).")";
			$adb->pquery($query, array());
		}
		
		$log->debug("Invoking deleteDuplicatesFromPickList(".$pickListName.") method ...DONE");
	}
}