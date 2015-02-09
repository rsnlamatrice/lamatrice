<?php
/***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
/* ED150127 : ne sert pas ˆ grand chose. EntitŽ de spŽcialisation des services en facture. */
include_once 'modules/Vtiger/CRMEntity.php';

class RsnAbonnements extends Vtiger_CRMEntity {
	var $table_name = 'vtiger_rsnabonnements';
	var $table_index= 'rsnabonnementsid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_rsnabonnementscf', 'rsnabonnementsid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_rsnabonnements', 'vtiger_rsnabonnementscf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_rsnabonnements' => 'rsnabonnementsid',
		'vtiger_rsnabonnementscf'=>'rsnabonnementsid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array (
		'LBL_COMPTE' => array('rsnabonnements', 'compte'),
		'LBL_DATEDON' => array('rsnabonnements', 'invoicedate'),
		'LBL_MONTANT' => array('rsnabonnements', 'montant'),
		'LBL_ORIGINE' => array('rsnabonnements', 'origine'),

	);
	var $list_fields_name = Array (
		'LBL_COMPTE' => 'compte',
		'LBL_DATEDON' => 'invoicedate',
		'LBL_MONTANT' => 'montant',
		'LBL_ORIGINE' => 'origine',

	);

	// Make the field link to detail view
	var $list_link_field = '';

	// For Popup listview and UI type support
	var $search_fields = Array(
		'LBL_ORIGINE' => array('rsnabonnements', 'origine'),
		'LBL_ORIGINE_DETAIL' => array('rsnabonnements', 'origine_detail'),
		'LBL_MONTANT' => array('rsnabonnements', 'montant'),
		'LBL_DATEDON' => array('rsnabonnements', 'invoicedate'),
		'LBL_COMPTE' => array('rsnabonnements', 'compte'),

	);
	var $search_fields_name = Array (
		'LBL_ORIGINE' => 'origine',
		'LBL_ORIGINE_DETAIL' => 'origine_detail',
		'LBL_MONTANT' => 'montant',
		'LBL_DATEDON' => 'invoicedate',
		'LBL_COMPTE' => 'compte',

	);

	// For Popup window record selection
	var $popup_fields = Array (
		'LBL_ORIGINE' => 'origine',
		'LBL_ORIGINE_DETAIL' => 'origine_detail',
		'LBL_MONTANT' => 'montant',
		'LBL_DATEDON' => 'invoicedate',
		'LBL_COMPTE' => 'compte',);

	// For Alphabetical search
	var $def_basicsearch_col = 'origine';

	// Column value to use on detail view record text display
	var $def_detailview_recname = 'invoicedate';

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = Array('montant', 'invoicedate', 'assigned_user_id');

	var $default_order_by = 'invoicedate';
	var $default_sort_order='DESC';

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
	
	

    /** Function to retreive the user info of the specifed user id The user info will be available in $this->column_fields array
     * @param $record -- record id:: Type integer
     * @param $module -- module:: Type varchar
     */
    function retrieve_entity_info($record, $module) {
	global $adb, $log, $app_strings;
	// Lookup module field cache
	$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module);
	if ($cachedModuleFields === false) {
		// Pull fields and cache for further use
		$tabid = getTabid($module);

		$sql0 = "SELECT fieldname, fieldid, fieldlabel, columnname, tablename, uitype, typeofdata,presence FROM vtiger_field WHERE tabid=?";
		// NOTE: Need to skip in-active fields which we will be done later.
		$result0 = $adb->pquery($sql0, array($tabid));
		if ($adb->num_rows($result0)) {
			while ($resultrow = $adb->fetch_array($result0)) {
				// Update cache
				VTCacheUtils::updateFieldInfo(
					$tabid, $resultrow['fieldname'], $resultrow['fieldid'], $resultrow['fieldlabel'], $resultrow['columnname'], $resultrow['tablename'], $resultrow['uitype'], $resultrow['typeofdata'], $resultrow['presence']
				);
			}
			// Get only active field information
			$cachedModuleFields = VTCacheUtils::lookupFieldInfo_Module($module);
		}
	}
	$sql = 'SELECT f.invoicedate AS vtiger_rsnabonnementsinvoicedate, f.accountid as vtiger_rsnabonnementscompte, lg.`listprice` as vtiger_rsnabonnementsmontant
	, p.productcode as vtiger_rsnabonnementsorigine, "" as vtiger_rsnabonnementsorigine_detail
	, p.productid as vtiger_rsnabonnementsproduit
	, f.accountid as vtiger_rsnabonnementsid
	,e.smownerid AS vtiger_crmentityassigned_user_id
	,e.createdtime AS vtiger_crmentitycreatedtime
	,e.modifiedtime AS vtiger_crmentitymodifiedtime
	,e.deleted
	FROM `vtiger_inventoryproductrel` lg
	INNER JOIN `vtiger_products` p
		ON lg.productid = p.productid
		AND p.productcategory = ?
	INNER JOIN `vtiger_invoice` f
		ON lg.id = f.invoiceid
	INNER JOIN `vtiger_crmentity` e
		ON e.crmid = f.invoiceid
	WHERE e.crmid=?
	AND e.deleted = 0
	LIMIT 1';
	/*var_dump($params);
	var_dump($sql);
	echo($sql);*/
	$params[] = 'Abonnement';
	$params[] = $record;
	$result = $adb->pquery($sql, $params);

	if (!$result || $adb->num_rows($result) < 1) {
		throw new Exception($app_strings['LBL_RECORD_NOT_FOUND'], -1);
	} else {
		$resultrow = $adb->query_result_rowdata($result);
		if (!empty($resultrow['deleted'])) {
			throw new Exception($app_strings['LBL_RECORD_DELETE'], 1);
		}

		foreach ($cachedModuleFields as $fieldinfo) {
			$fieldvalue = '';
			$fieldkey = $this->createColumnAliasForField($fieldinfo);
			//Note : value is retrieved with a tablename+fieldname as we are using alias while building query
			if (isset($resultrow[$fieldkey])) {
				$fieldvalue = $resultrow[$fieldkey];
			}
			$this->column_fields[$fieldinfo['fieldname']] = $fieldvalue;
		}
	}

	$this->column_fields['record_id'] = $record;
	$this->column_fields['record_module'] = $module;
	return $this;
    }
}