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

class RSNDonateursWeb extends Vtiger_CRMEntity {
	var $table_name = 'vtiger_rsndonateursweb';
	var $table_index= 'rsndonateurswebid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_rsndonateurswebcf', 'rsndonateurswebid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_rsndonateursweb', 'vtiger_rsndonateurswebcf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_rsndonateursweb' => 'rsndonateurswebid',
		'vtiger_rsndonateurswebcf'=>'rsndonateurswebid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array (
		'LBL_REVUE' => array('rsndonateursweb', 'revue'),
		'LBL_PAIEMENTID' => array('rsndonateursweb', 'paiementid'),
		'LBL_RELATEDMODULEID' => array('rsndonateursweb', 'relatedmoduleid'),
		'LBL_LASTNAME' => array('rsndonateursweb', 'lastname'),
		'LBL_CLICKSOURCE' => array('rsndonateursweb', 'clicksource'),
		'LBL_CONTACTID' => array('rsndonateursweb', 'contactid'),
		'LBL_FIRSTNAME' => array('rsndonateursweb', 'firstname'),
		'LBL_DATEABOEND' => array('rsndonateursweb', 'dateaboend'),
		'LBL_LISTESN' => array('rsndonateursweb', 'listesn'),
		'LBL_MODEPAIEMENT' => array('rsndonateursweb', 'modepaiement'),
		'LBL_DATEDON' => array('rsndonateursweb', 'datedon'),
		'LBL_PAIEMENTERROR' => array('rsndonateursweb', 'paiementerror'),
		'LBL_AMOUNT' => array('rsndonateursweb', 'amount'),

	);
	var $list_fields_name = Array (
		'LBL_REVUE' => 'revue',
		'LBL_PAIEMENTID' => 'paiementid',
		'LBL_RELATEDMODULEID' => 'relatedmoduleid',
		'LBL_LASTNAME' => 'lastname',
		'LBL_CLICKSOURCE' => 'clicksource',
		'LBL_CONTACTID' => 'contactid',
		'LBL_FIRSTNAME' => 'firstname',
		'LBL_DATEABOEND' => 'dateaboend',
		'LBL_LISTESN' => 'listesn',
		'LBL_MODEPAIEMENT' => 'modepaiement',
		'LBL_DATEDON' => 'datedon',
		'LBL_PAIEMENTERROR' => 'paiementerror',
		'LBL_AMOUNT' => 'amount',

	);

	// Make the field link to detail view
	var $list_link_field = '';

	// For Popup listview and UI type support
	var $search_fields = Array(
		'LBL_MODEPAIEMENT' => array('rsndonateursweb', 'modepaiement'),
		'LBL_DATEDON' => array('rsndonateursweb', 'datedon'),
		'LBL_AMOUNT' => array('rsndonateursweb', 'amount'),
		'LBL_PAIEMENTERROR' => array('rsndonateursweb', 'paiementerror'),
		'LBL_LISTESN' => array('rsndonateursweb', 'listesn'),
		'LBL_PAIEMENTID' => array('rsndonateursweb', 'paiementid'),
		'LBL_RELATEDMODULEID' => array('rsndonateursweb', 'relatedmoduleid'),
		'LBL_REVUE' => array('rsndonateursweb', 'revue'),
		'LBL_ABOCODE' => array('rsndonateursweb', 'abocode'),
		'LBL_LASTNAME' => array('rsndonateursweb', 'lastname'),
		'LBL_DATEABOEND' => array('rsndonateursweb', 'dateaboend'),
		'LBL_FIRSTNAME' => array('rsndonateursweb', 'firstname'),
		'LBL_CLICKSOURCE' => array('rsndonateursweb', 'clicksource'),
		'LBL_CONTACTID' => array('rsndonateursweb', 'contactid'),

	);
	var $search_fields_name = Array (
		'LBL_MODEPAIEMENT' => 'modepaiement',
		'LBL_DATEDON' => 'datedon',
		'LBL_AMOUNT' => 'amount',
		'LBL_PAIEMENTERROR' => 'paiementerror',
		'LBL_LISTESN' => 'listesn',
		'LBL_PAIEMENTID' => 'paiementid',
		'LBL_RELATEDMODULEID' => 'relatedmoduleid',
		'LBL_REVUE' => 'revue',
		'LBL_ABOCODE' => 'abocode',
		'LBL_LASTNAME' => 'lastname',
		'LBL_DATEABOEND' => 'dateaboend',
		'LBL_FIRSTNAME' => 'firstname',
		'LBL_CLICKSOURCE' => 'clicksource',
		'LBL_CONTACTID' => 'contactid',

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

	function RSNDonateursWeb() {
		$this->log =LoggerManager::getLogger('RSNDonateursWeb');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('RSNDonateursWeb');
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