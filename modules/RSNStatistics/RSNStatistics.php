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
		'LBL_STATS_PERIODICITE' => array('rsnstatistics', 'stats_periodicite'),
		'LBL_DISABLED' => array('rsnstatistics', 'disabled'),

	);
	var $list_fields_name = Array (
		'LBL_STAT_NAME' => 'stat_name',
		'LBL_RELMODULE' => 'relmodule',
		'LBL_STATS_PERIODICITE' => 'stats_periodicite',
		'LBL_DISABLED' => 'disabled',

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
		
		$this->add_statistic_table_field($with_crmid);

		parent::save_related_module($module, $crmid, $with_module, $with_crmid);
	}
	
	//new field stat
	function add_statistic_table_field($fieldId) {
		$tableName = RSNStatistics_Utils_Helper::getStatsTableNameFromId($this->id);
		//echo "Add a column in the stat field table!! " . $tableName . "<br/>";
		$sql = "ALTER TABLE `" . $tableName . "` ADD `" . RSNStatistics_Utils_Helper::getFieldUniqueCodeFromId($fieldId) . "` DOUBLE";//tmp double -> use a specified data type !!
		$db = PearDatabase::getInstance();
		$db->pquery($sql);
	}
	
	//new field stat
	function check_statistic_table_fields($moduleName) {
		$statFields = RSNStatistics_Utils_Helper::getRelatedStatsFieldsRecordModels($this->id);
		//var_dump($statFields);
		foreach($statFields as $statField){
			$this->add_statistic_table_field($statField->getId());
		}
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
	
	//TOTALS
	function get_statistics_data($id, $cur_tab_id, $rel_tab_id, $actions=false) {//tmp (do not put this method here -> need a generic method not depanding to the module!!!!!!!!!!!!
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_statistics(".$id.") method ...");
		$this_module = $currentModule;

		$related_module = vtlib_getModuleNameById($rel_tab_id);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';
		
		$recordModel = Vtiger_Record_Model::getInstanceById($id, $this_module);
		
		$relatedStatsTablesNames = RSNStatistics_Utils_Helper::getRelatedStatsTablesNames($recordModel->get('relmodule'));

		$tableName = $relatedStatsTablesNames[0];
		$query = "SELECT DISTINCT `$tableName`.name, `$tableName`.code";
		
		$query .= "
			FROM `$tableName`
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = `$tableName`.crmid
			WHERE vtiger_crmentity.deleted = FALSE";
		$query .= "
			GROUP BY `$tableName`.name, `$tableName`.code";
			
		$query = "SELECT * FROM ($query) _subquery";
		//echo $query;

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_invoices method ...");
		return $return_value;
	}
}