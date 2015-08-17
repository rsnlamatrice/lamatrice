<?php
/***********************************************************************************
 * ED140000
 ************************************************************************************/

include_once 'modules/Vtiger/CRMEntity.php';

class RsnPrelVirement extends Vtiger_CRMEntity {
	var $table_name = 'vtiger_rsnprelvirement';
	var $table_index= 'rsnprelvirementid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_rsnprelvirementcf', 'rsnprelvirementid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_rsnprelvirement', 'vtiger_rsnprelvirementcf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_rsnprelvirement' => 'rsnprelvirementid',
		'vtiger_rsnprelvirementcf'=>'rsnprelvirementid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array (
		'LBL_DATEEXPORT' => array('rsnprelvirement', 'dateexport'),
		'LBL_MONTANT' => array('rsnprelvirement', 'montant'),
		'LBL_COMMENTAIRE' => array('rsnprelvirement', 'commentaire'),
		'LBL_STATUS' => array('rsnprelvirement', 'rsnprelvirstatus'),

	);
	var $list_fields_name = Array (
		'LBL_DATEEXPORT' => 'dateexport',
		'LBL_MONTANT' => 'montant',
		'LBL_COMMENTAIRE' => 'commentaire',
		'LBL_STATUS' => 'rsnprelvirstatus',

	);

	// Make the field link to detail view
	var $list_link_field = '';

	// For Popup listview and UI type support
	var $search_fields = Array(
		'LBL_STATUS' => array('rsnprelvirement', 'rsnprelvirstatus'),
		'LBL_COMMENTAIRE' => array('rsnprelvirement', 'commentaire'),
		'LBL_MONTANT' => array('rsnprelvirement', 'montant'),
		'LBL_DATEEXPORT' => array('rsnprelvirement', 'dateexport'),

	);
	var $search_fields_name = Array (
		'LBL_STATUS' => 'rsnprelvirstatus',
		'LBL_COMMENTAIRE' => 'commentaire',
		'LBL_MONTANT' => 'montant',
		'LBL_DATEEXPORT' => 'dateexport',

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