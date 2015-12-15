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

class RSNEmailListes extends Vtiger_CRMEntity {
	var $table_name = 'vtiger_rsnemaillistes';
	var $table_index= 'rsnemaillistesid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_rsnemaillistescf', 'rsnemaillistesid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_rsnemaillistes', 'vtiger_rsnemaillistescf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_rsnemaillistes' => 'rsnemaillistesid',
		'vtiger_rsnemaillistescf'=>'rsnemaillistesid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array (
		'LBL_NAME' => array('rsnemaillistes' => 'name'),
		'LBL_ENABLE' => array('rsnemaillistes' => 'enable'),
		'LBL_COMMENT' => array('rsnemaillistes' => 'comment'),
		'LBL_LASTTIME' => array('rsnemaillistes' => 'lasttime'),

	);
	var $list_fields_name = Array (
		'LBL_NAME' => 'name',
		'LBL_ENABLE' => 'enable',
		'LBL_COMMENT' => 'comment',
		'LBL_LASTTIME' => 'lasttime',

	);

	// Make the field link to detail view
	var $list_link_field = '';

	// For Popup listview and UI type support
	var $search_fields = Array(
		'LBL_NAME' => array('rsnemaillistes', 'name'),
		'LBL_LASTTIME' => array('rsnemaillistes', 'lasttime'),
		'LBL_COMMENT' => array('rsnemaillistes', 'comment'),
		'LBL_ENABLE' => array('rsnemaillistes', 'enable'),

	);
	var $search_fields_name = Array (
		'LBL_NAME' => 'name',
		'LBL_LASTTIME' => 'lasttime',
		'LBL_COMMENT' => 'comment',
		'LBL_ENABLE' => 'enable',

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

	function RSNEmailListes() {
		$this->log =LoggerManager::getLogger('RSNEmailListes');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('RSNEmailListes');
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
	
	/** Returns a list of the associated campaigns, ...
	 * saved in vtiger_senotesrel
	* ED141018
	*/
	function get_related_contactemails($id, $cur_tab_id, $rel_tab_id, $actions=false) {
	       global $log, $singlepane_view,$currentModule,$current_user;
	       $log->debug("Entering Documents get_related_contactemails(".$id.") method ...");
	       $this_module = $currentModule;
	
	       $related_module = vtlib_getModuleNameById($rel_tab_id);
	       require_once("modules/$related_module/$related_module.php");
	       $other = new $related_module();
	       vtlib_setup_modulevars($related_module, $other);
	       $singular_modname = vtlib_toSingular($related_module);
	
	       $parenttab = getParentTab();
	
	       if($singlepane_view == 'true')
		       $returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
	       else
		       $returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;
	
	       $button = '';
	       $userNameSql = getSqlForNameInDisplayFormat(array('first_name'=> 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
	       $query = "SELECT vtiger_contactemails.*, vtiger_contactemailscf.*,
		       vtiger_crmentity.crmid,
		       vtiger_crmentity.smownerid,
		       case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name
		       FROM vtiger_contactemails
			   INNER JOIN vtiger_rsnemaillistesrel
				ON vtiger_rsnemaillistesrel.contactemailsid = vtiger_contactemails.contactemailsid
			   INNER JOIN vtiger_contactemailscf
				ON vtiger_contactemails.contactemailsid = vtiger_contactemailscf.contactemailsid
		       INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_contactemails.contactemailsid
		       INNER JOIN vtiger_crmentity vtiger_contacts_crmentity
				ON vtiger_contacts_crmentity.crmid = vtiger_contactemails.contactid
		       LEFT JOIN vtiger_groups	ON vtiger_groups.groupid = vtiger_crmentity.smownerid
		       LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id
		       WHERE vtiger_crmentity.deleted = 0
			   AND vtiger_contacts_crmentity.deleted = 0
		       AND vtiger_rsnemaillistesrel.rsnemaillistesid = ".$id
		;
		
	       $return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);
	
	       if($return_value == null) $return_value = Array();
	       $return_value['CUSTOM_BUTTON'] = $button;
	
	       $log->debug("Exiting Documents get_related_contactemails method ...");
	       
	       /*print_r($query);
		$db = PearDatabase::getInstance();	$db->setDebug(true);
		echo_callstack();*/
	
	       return $return_value;
	}

	/* addRelation
	 */
	function save_related_module($module, $crmid, $with_module, $with_crmids) {
		if($with_module !== 'ContactEmails') 
			return parent::save_related_module($module, $crmid, $with_module, $with_crmids);
		
		$adb = PearDatabase::getInstance();
		
		if(!is_array($with_crmids)) $with_crmids = Array($with_crmids);
		foreach($with_crmids as $with_crmid) {
			$adb->pquery("INSERT INTO vtiger_rsnemaillistesrel (rsnemaillistesid, contactemailsid, datesubscribe)
					 VALUES(?,?, NOW())", array($crmid, $with_crmid));
		}
	}

	/**
	 * Delete the related module record information. Triggered from updateRelations.php
	 * @param String This module name
	 * @param Integer This module record number
	 * @param String Related module name
	 * @param mixed Integer or Array of related module record number
	 */
	function delete_related_module($module, $crmid, $with_module, $with_crmid) {
		if($return_module !== 'ContactEmails')
			return parent::delete_related_module($module, $crmid, $with_module, $with_crmid);
		global $adb;
		$params = Array($crmid);
		
		$query = "DELETE FROM vtiger_rsnemaillistesrel
			WHERE rsnemaillistesid = ?
			AND contactemailsid IN ("
				.(is_array($with_crmid) ? generateQuestionMarks($with_crmid) : '?')
			. ')'
		;
		if (!is_array($with_crmid))
			array_push($params, $with_crmid);
		else
			$params = array_merge($params, $with_crmid);
		
		$result = $adb->pquery($query, $params);
	}

	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id) {
		if($return_module !== 'ContactEmails')
			return parent::unlinkRelationship($id, $return_module, $return_id);
		
		if(empty($return_module) || empty($return_id)) return;

		$sql = 'DELETE FROM vtiger_rsnemaillistesrel WHERE rsnemaillistesid = ? AND contactemailsid = ?';
		$params = array($id, $return_id);
		$this->db->pquery($sql, $params);
	
	}
}