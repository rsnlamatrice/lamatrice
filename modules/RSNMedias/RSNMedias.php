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

class RSNMedias extends Vtiger_CRMEntity {
	var $table_name = 'vtiger_rsnmedias';
	var $table_index= 'rsnmediasid';
	
	var $uicolor_field = 'rsntypesmedia';

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_rsnmediascf', 'rsnmediasid');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_rsnmedias', 'vtiger_rsnmediascf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_rsnmedias' => 'rsnmediasid',
		'vtiger_rsnmediascf'=>'rsnmediasid');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = array (
		'LBL_NOM' => array('rsnmedias', 'nom'),
		'LBL_RSNTYPESMEDIA' => array('rsnmedias', 'rsntypesmedia'),
		'LBL_RSNCOUNTRY' => array('rsnmedias', 'rsncountry'),
		'LBL_MEDIACOMMENT' => array('rsnmedias', 'mediacomment'),
		'LBL_RSNREGION' => array('rsnmedias', 'rsnregion'),
		'LBL_RUBRIQUEPREFEREE' => array('rsnmedias', 'rubriquepreferee'),

);
	var $list_fields_name = array (
		'LBL_NOM' => 'nom',
		'LBL_RSNTYPESMEDIA' => 'rsntypesmedia',
		'LBL_RSNCOUNTRY' => 'rsncountry',
		'LBL_MEDIACOMMENT' => 'mediacomment',
		'LBL_RSNREGION' => 'rsnregion',
		'LBL_RUBRIQUEPREFEREE' => 'rubriquepreferee',

);

	// Make the field link to detail view
	var $list_link_field = '';

	// For Popup listview and UI type support
	var $search_fields = array (
		'LBL_NOM' => array('rsnmedias', 'nom'),
		'LBL_RSNTYPESMEDIA' => array('rsnmedias', 'rsntypesmedia'),
		'LBL_RUBRIQUEPREFEREE' => array('rsnmedias', 'rubriquepreferee'),
		'LBL_RSNCOUNTRY' => array('rsnmedias', 'rsncountry'),
		'LBL_RSNREGION' => array('rsnmedias', 'rsnregion'),
		'LBL_MEDIACOMMENT' => array('rsnmedias', 'mediacomment'),

);
	var $search_fields_name = array (
		'LBL_NOM' => 'nom',
		'LBL_RSNTYPESMEDIA' => 'rsntypesmedia',
		'LBL_RUBRIQUEPREFEREE' => 'rubriquepreferee',
		'LBL_RSNCOUNTRY' => 'rsncountry',
		'LBL_RSNREGION' => 'rsnregion',
		'LBL_MEDIACOMMENT' => 'mediacomment',

);

	// For Popup window record selection
	var $popup_fields = array('');

	// For Alphabetical search
	var $def_basicsearch_col = 'nom';

	// Column value to use on detail view record text display
	var $def_detailview_recname = '';

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = array('createdtime', 'modifiedtime', '');

	var $default_order_by = '';
	var $default_sort_order='ASC';

	function RSNMedias() {
		$this->log =LoggerManager::getLogger('RSNMedias');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('RSNMedias');
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
	
	
	
	
	/**
	* Function to get Contact related rsnabonnements/adhŽsions
	* @param  integer   $id      - contactid
	* returns related Invoices record in array format
	* ED140905
	*/
	function get_rsnmediarelations($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_rsnmediarelations(".$id.") method ...");
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

		$button .= '<input type="hidden" name="email_directing_module"><input type="hidden" name="record">';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input title='". getTranslatedString('LBL_ADD_NEW')." ". getTranslatedString($singular_modname)."' accessyKey='F' class='crmbutton small create' onclick='fnvshobj(this,\"sendmail_cont\");sendmail(\"$this_module\",$id);' type='button' name='button' value='". getTranslatedString('LBL_ADD_NEW')." ". getTranslatedString($singular_modname)."'></td>";
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name,
					vtiger_rsnmediarelations.*,
					vtiger_rsnmediarelationscf.*,
					vtiger_rsnmediacontacts.nom AS vtiger_rsnmediacontacts_nom,
					vtiger_crmentity.crmid, vtiger_crmentity.smownerid, vtiger_crmentity.modifiedtime
					FROM vtiger_rsnmediarelations
					inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_rsnmediarelations.rsnmediarelationsid
					inner join vtiger_rsnmediarelationscf ON vtiger_rsnmediarelationscf.rsnmediarelationsid = vtiger_rsnmediarelations.rsnmediarelationsid
					left join vtiger_rsnmediacontacts on vtiger_rsnmediacontacts.rsnmediacontactsid = vtiger_rsnmediarelations.mediacontactid
					left join vtiger_groups on vtiger_groups.groupid=vtiger_crmentity.smownerid
					left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid
					where vtiger_rsnmediarelations.rsnmediaid=".$id." and vtiger_crmentity.deleted=0";//
//echo("<textarea>$query</textarea>");
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);
//echo("<textarea>".json_encode($return_value)."</textarea>");
/*echo_callstack();
$db = PearDatabase::getInstance();
$db->setDebug(true);*/
		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;
		$return_value['UNKNOWN_FIELD_RETURNS_VALUE'] = true;

		$log->debug("Exiting get_rsnmediarelations method ...");
		return $return_value;
	}
	
	
}