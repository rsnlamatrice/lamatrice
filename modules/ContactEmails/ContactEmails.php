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

class ContactEmails extends Vtiger_CRMEntity {
	var $table_name = 'vtiger_contactemails';
	var $table_index= 'contactemailsid';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_contactemailscf', 'contactemailsid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_contactemails', 'vtiger_contactemailscf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_contactemails' => 'contactemailsid',
		'vtiger_contactemailscf'=>'contactemailsid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array (
		'LBL_EMAIL' => array('contactemails', 'email'),
		'LBL_EMAILADDRESSORIGIN' => array('contactemails', 'emailaddressorigin'),
		'LBL_RSNMEDIADOCUMENTS' => array('contactemails', 'rsnmediadocuments'),
		'LBL_RSNMEDIADOCUMENTSDONOT' => array('contactemails', 'rsnmediadocumentsdonot'),
		'LBL_COMMENTS' => array('contactemails', 'comments'),
		'LBL_CONTACTID' => array('contactemails', 'contactid'),

	);
	var $list_fields_name = Array (
		'LBL_EMAIL' => 'email',
		'LBL_EMAILADDRESSORIGIN' => 'emailaddressorigin',
		'LBL_RSNMEDIADOCUMENTS' => 'rsnmediadocuments',
		'LBL_RSNMEDIADOCUMENTSDONOT' => 'rsnmediadocumentsdonot',
		'LBL_COMMENTS' => 'comments',
		'LBL_CONTACTID' => 'contactid',

	);

	// Make the field link to detail view
	var $list_link_field = '';

	// For Popup listview and UI type support
	var $search_fields = Array(
		'LBL_EMAIL' => array('contactemails', 'email'),
		'LBL_CONTACTID' => array('contactemails', 'contactid'),
		'LBL_RSNMEDIADOCUMENTSDONOT' => array('contactemails', 'rsnmediadocumentsdonot'),
		'LBL_COMMENTS' => array('contactemails', 'comments'),
		'LBL_RSNMEDIADOCUMENTS' => array('contactemails', 'rsnmediadocuments'),
		'LBL_EMAILADDRESSORIGIN' => array('contactemails', 'emailaddressorigin'),

	);
	var $search_fields_name = Array (
		'LBL_EMAIL' => 'email',
		'LBL_CONTACTID' => 'contactid',
		'LBL_RSNMEDIADOCUMENTSDONOT' => 'rsnmediadocumentsdonot',
		'LBL_COMMENTS' => 'comments',
		'LBL_RSNMEDIADOCUMENTS' => 'rsnmediadocuments',
		'LBL_EMAILADDRESSORIGIN' => 'emailaddressorigin',

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

	function ContactEmails() {
		$this->log =LoggerManager::getLogger('ContactEmails');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('ContactEmails');
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
	

	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id) {
		if($return_module !== 'RSNEmailListes')
			return parent::unlinkRelationship($id, $return_module, $return_id);
		
		if(empty($return_module) || empty($return_id)) return;

		$sql = 'DELETE FROM vtiger_rsnemaillistesrel WHERE rsnemaillistesid = ? AND contactemailsid = ?';
		$params = array($return_id, $id);
		$this->db->pquery($sql, $params);
	
	}
	
	
	
	
	/** Returns a list of the associated campaigns, ...
	 * saved in vtiger_senotesrel
	* ED141018
	*/
	function get_related_rsnemaillistes($id, $cur_tab_id, $rel_tab_id, $actions=false) {
	       global $log, $singlepane_view,$currentModule,$current_user;
	       $log->debug("Entering Documents get_related_rsnemaillistes(".$id.") method ...");
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
	       $query = "SELECT vtiger_rsnemaillistes.*,
		       vtiger_crmentity.crmid,
		       vtiger_crmentity.smownerid,
		       case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name
		       FROM vtiger_rsnemaillistes
			   INNER JOIN vtiger_rsnemaillistesrel
				ON vtiger_rsnemaillistesrel.rsnemaillistesid = vtiger_rsnemaillistes.rsnemaillistesid
			   /*INNER JOIN vtiger_rsnemaillistescf
				ON vtiger_rsnemaillistes.rsnemaillistesid = vtiger_rsnemaillistescf.rsnemaillistesid*/
		       INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_rsnemaillistes.rsnemaillistesid
		       LEFT JOIN vtiger_groups	ON vtiger_groups.groupid = vtiger_crmentity.smownerid
		       LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id
		       WHERE vtiger_crmentity.deleted = 0
		       AND vtiger_rsnemaillistesrel.contactemailsid = ".$id
		;
		
	       $return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);
	
	       if($return_value == null) $return_value = Array();
	       $return_value['CUSTOM_BUTTON'] = $button;
	
	       $log->debug("Exiting Documents get_related_rsnemaillistes method ...");
	       
	       /*print_r($query);
		$db = PearDatabase::getInstance();	$db->setDebug(true);
		echo_callstack();*/
	
	       return $return_value;
	}

	/* addRelation
	 */
	function save_related_module($module, $crmid, $with_module, $with_crmids) {
		if($with_module !== 'RSNEmailListes') 
			return parent::save_related_module($module, $crmid, $with_module, $with_crmids);
		
		$adb = PearDatabase::getInstance();
		
		if(!is_array($with_crmids)) $with_crmids = Array($with_crmids);
		foreach($with_crmids as $with_crmid) {
			$adb->pquery("INSERT INTO vtiger_rsnemaillistesrel (contactemailsid, rsnemaillistesid, datesubscribe)
					 VALUES(?,?, NOW())", array($crmid, $with_crmid));
		}
	}
}