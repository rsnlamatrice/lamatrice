<?php
/***********************************************************************************
 * Prises de contact avec un journaliste de média
 ************************************************************************************/

include_once 'modules/Vtiger/CRMEntity.php';

class RSNMediaRelations extends Vtiger_CRMEntity {
	var $table_name = 'vtiger_rsnmediarelations';
	var $table_index= 'rsnmediarelationsid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_rsnmediarelationscf', 'rsnmediarelationsid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_rsnmediarelations', 'vtiger_rsnmediarelationscf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_rsnmediarelations' => 'rsnmediarelationsid',
		'vtiger_rsnmediarelationscf'=>'rsnmediarelationsid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array (
		'LBL_DATERELATION' => array('rsnmediarelations', 'daterelation'),
		'LBL_COMMENT' => array('rsnmediarelations', 'comment'),
		'LBL_SATISFACTION' => array('rsnmediarelations', 'satisfaction'),
		'LBL_SUJET' => array('rsnmediarelations', 'sujet'),
		'LBL_RSNMEDIAID' => array('rsnmediarelations', 'rsnmediaid'),
		'LBL_MEDIACONTACTID' => array('rsnmediarelations', 'mediacontactid'),
		'LBL_RSNTHEMATIQUES' => array('rsnmediarelations', 'rsnthematiques'),
		'LBL_CAMPAGNE' => array('rsnmediarelations', 'campagne'),
		'LBL_BYUSERID' => array('rsnmediarelations', 'byuserid'),

	);
	var $list_fields_name = Array (
		'LBL_DATERELATION' => 'daterelation',
		'LBL_COMMENT' => 'comment',
		'LBL_SATISFACTION' => 'satisfaction',
		'LBL_SUJET' => 'sujet',
		'LBL_RSNMEDIAID' => 'rsnmediaid',
		'LBL_MEDIACONTACTID' => 'mediacontactid',
		'LBL_RSNTHEMATIQUES' => 'rsnthematiques',
		'LBL_CAMPAGNE' => 'campagne',
		'LBL_BYUSERID' => 'byuserid',

	);

	// Make the field link to detail view
	var $list_link_field = '';

	// For Popup listview and UI type support
	var $search_fields = Array(
		'LBL_BYUSERID' => array('rsnmediarelations', 'byuserid'),
		'LBL_RSNMEDIAID' => array('rsnmediarelations', 'rsnmediaid'),
		'LBL_MEDIACONTACTID' => array('rsnmediarelations', 'mediacontactid'),
		'LBL_RSNTHEMATIQUES' => array('rsnmediarelations', 'rsnthematiques'),
		'LBL_CAMPAGNE' => array('rsnmediarelations', 'campagne'),
		'LBL_SUJET' => array('rsnmediarelations', 'sujet'),
		'LBL_COMMENT' => array('rsnmediarelations', 'comment'),
		'LBL_DATERELATION' => array('rsnmediarelations', 'daterelation'),
		'LBL_SATISFACTION' => array('rsnmediarelations', 'satisfaction'),

	);
	var $search_fields_name = Array (
		'LBL_BYUSERID' => 'byuserid',
		'LBL_RSNMEDIAID' => 'rsnmediaid',
		'LBL_MEDIACONTACTID' => 'mediacontactid',
		'LBL_RSNTHEMATIQUES' => 'rsnthematiques',
		'LBL_CAMPAGNE' => 'campagne',
		'LBL_SUJET' => 'sujet',
		'LBL_COMMENT' => 'comment',
		'LBL_DATERELATION' => 'daterelation',
		'LBL_SATISFACTION' => 'satisfaction',

	);

	// For Popup window record selection
	var $popup_fields = Array ('');

	// For Alphabetical search
	var $def_basicsearch_col = 'mediacontactid';

	// Column value to use on detail view record text display
	var $def_detailview_recname = 'daterelation,mediacontactid';

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('','assigned_user_id');

	var $default_order_by = 'daterelation';
	var $default_sort_order='DESC';

	function RSNMediaRelations() {
		$this->log =LoggerManager::getLogger('RSNMediaRelations');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('RSNMediaRelations');
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