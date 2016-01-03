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

class Critere4D extends Vtiger_CRMEntity {
	var $table_name = 'vtiger_critere4d';
	var $table_index= 'critere4did';

	/**
	 * ED141011 TESTS
	 */
	var $jexiste = true;

	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_critere4dcf', 'critere4did');

	/**
	 * Mandatory for Saving, Include tables related to this module.
	 */
	var $tab_name = Array('vtiger_crmentity', 'vtiger_critere4d', 'vtiger_critere4dcf');

	/**
	 * Mandatory for Saving, Include tablename and tablekey columnname here.
	 */
	var $tab_name_index = Array(
		'vtiger_crmentity' => 'crmid',
		'vtiger_critere4d' => 'critere4did',
		'vtiger_critere4dcf'=>'critere4did');

	/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = array (
		'Nom'=>Array('critere4d'=>'nom'),
		'Categorie'=>Array('critere4d'=>'categorie'),
//		'dateapplication'=>Array('critere4dcontrel'=>'dateapplication'),
);
	var $list_fields_name = array (
		'Nom'=>'nom',
		'Categorie'=>'categorie',
//		'dateapplication'=>'dateapplication',
);

	// Make the field link to detail view
	var $list_link_field = 'nom';

	// For Popup listview and UI type support
	var $search_fields = array (
		'LBL_NOM' => array('critere4d' => 'nom'),
		'LBL_CATEGORIE' => array('critere4d' => 'categorie'),
		'LBL_ORIGINE' => array('critere4d' => 'origine'),
		'LBL_COMMENTAIRE' => array('critere4d' => 'commentaire'),
		'LBL_USAGE_DEBUT' => array('critere4d' => 'usage_debut'),
		'LBL_USAGE_FIN' => array('critere4d' => 'usage_fin'),
		'LBL_ORDREDETRI' => array('critere4d' => 'ordredetri'),
		'LBL__COUNTER_' => array('critere4dcontrel' => '_counter_critere4dcontrel'),
		'LBL_DATEAPPLICATION' => array('critere4dcontrel' => 'dateapplication'),

);
	var $search_fields_name = array (
		'LBL_NOM' => 'nom',
		'LBL_CATEGORIE' => 'categorie',
		'LBL_ORIGINE' => 'origine',
		'LBL_COMMENTAIRE' => 'commentaire',
		'LBL_USAGE_DEBUT' => 'usage_debut',
		'LBL_USAGE_FIN' => 'usage_fin',
		'LBL_ORDREDETRI' => 'ordredetri',
		'LBL__COUNTER_' => '_counter_critere4dcontrel',
		'LBL_DATEAPPLICATION' => 'dateapplication',

);

	// For Popup window record selection
	var $popup_fields = array (
		'LBL_ORDREDETRI' => array('critere4d', 'ordredetri'),
		'LBL_NOM' => array('critere4d', 'nom'),
		'LBL_CATEGORIE' => array('critere4d', 'categorie'),
		'LBL_ORIGINE' => array('critere4d', 'origine'),
		'LBL_COMMENTAIRE' => array('critere4d', 'commentaire'),
		'LBL__COUNTER_' => array('critere4dcontrel', '_counter_critere4dcontrel'),
		'LBL_DATEAPPLICATION' => array('critere4dcontrel', 'dateapplication'),
);

	// For Alphabetical search
	var $def_basicsearch_col = 'nom';

	// Column value to use on detail view record text display
	var $def_detailview_recname = 'nom';

	// Used when enabling/disabling the mandatory fields for the module.
	// Refers to vtiger_field.fieldname values.
	var $mandatory_fields = array('createdtime', 'modifiedtime', 'nom');

	var $default_order_by = 'ordredetri';
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
	
	

	/* ED140905
	 *
	 *
	 */
	/**
	* Function to get Contact related criteres4D
	* @param  integer   $id      - contactid
	* returns related Invoices record in array format
	* ED140905
	*/
	function get_contacts($id, $cur_tab_id, $rel_tab_id, $actions=false) {


		global $log, $singlepane_view,$currentModule;
		$log->debug("Entering get_contacts(".$id.") method ...");
		$this_module = $currentModule;

		$related_module = vtlib_getModuleNameById($rel_tab_id);
		require_once("modules/$related_module/$related_module.php");
		$other = new $related_module();
		$is_CampaignStatusAllowed = false;
			
		vtlib_setup_modulevars($related_module, $other);


		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('SELECT', $actions) && isPermitted($related_module,4, '') == 'yes') {
				$button .= "<input title='".getTranslatedString('LBL_SELECT')." ". getTranslatedString($related_module). "' class='crmbutton small edit' type='button' onclick=\"return window.open('index.php?module=$related_module&return_module=$currentModule&action=Popup&popuptype=detailview&select=enable&form=EditView&form_submit=false&recordid=$id&parenttab=$parenttab','test','width=640,height=602,resizable=0,scrollbars=0');\" value='". getTranslatedString('LBL_SELECT'). " " . getTranslatedString($related_module) ."'>&nbsp;";
			}
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				$button .= "<input type='hidden' name='createmode' id='createmode' value='link' />".
					"<input title='".getTranslatedString('LBL_ADD_NEW'). " ". getTranslatedString($singular_modname) ."' class='crmbutton small create'" .
					" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\"' type='submit' name='button'" .
					" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString($singular_modname) ."'>&nbsp;";
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT vtiger_contactdetails.accountid, vtiger_account.accountname,
				CASE when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name ,
				vtiger_contactdetails.contactid, vtiger_contactdetails.lastname, vtiger_contactdetails.firstname, vtiger_contactdetails.title,
				vtiger_contactdetails.department, vtiger_contactdetails.email, vtiger_contactdetails.phone, vtiger_crmentity.crmid,
				vtiger_crmentity.smownerid, vtiger_crmentity.modifiedtime,
				vtiger_critere4dcontrel.dateapplication, vtiger_critere4dcontrel.data as rel_data
				FROM vtiger_contactdetails
				INNER JOIN vtiger_critere4dcontrel ON vtiger_critere4dcontrel.contactid = vtiger_contactdetails.contactid
				INNER JOIN vtiger_contactaddress ON vtiger_contactdetails.contactid = vtiger_contactaddress.contactaddressid
				INNER JOIN vtiger_contactsubdetails ON vtiger_contactdetails.contactid = vtiger_contactsubdetails.contactsubscriptionid
				INNER JOIN vtiger_customerdetails ON vtiger_contactdetails.contactid = vtiger_customerdetails.customerid
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid
				LEFT JOIN vtiger_contactscf ON vtiger_contactdetails.contactid = vtiger_contactscf.contactid
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid=vtiger_crmentity.smownerid
				LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid=vtiger_users.id
				LEFT JOIN vtiger_account ON vtiger_account.accountid = vtiger_contactdetails.accountid
				WHERE vtiger_critere4dcontrel.critere4did = ".$id." AND vtiger_crmentity.deleted=0";

//echo("<textarea>$query</textarea>");
		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);
//echo('<textarea rows="8" style="width:60em">' . json_encode($return_value,  JSON_PRETTY_PRINT) . '</textarea>');

		if($return_value == null)
			$return_value = Array();

		$return_value['CUSTOM_BUTTON'] = $button;

		$return_value['ROWS_COUNT'] = 2;

		$log->debug("Exiting get_contacts method ...");
		return $return_value;
	}
	/* get_contacts */
	
	
	/*
	 * Function to get the relation tables for related modules
	 * @param - $secmodule secondary module name
	 * returns the array with table names and fieldnames storing relations between module and this module
	 * ED140906
	 */
	function setRelationTables($secmodule){
		$rel_tables = array (
			"Contacts" => array("vtiger_critere4dcontrel"=>array("critere4did","contactid"),"vtiger_critere4d"=>"critere4did"),
		);
		return $rel_tables[$secmodule];
	}
	
	
	function save_related_module($module, $crmid, $with_module, $with_crmids) {
		$adb = PearDatabase::getInstance();

		if(!is_array($with_crmids)) $with_crmids = Array($with_crmids);
		foreach($with_crmids as $with_crmid) {
			/*ED140905*/
			if($with_module == 'Contacts') {
				$adb->pquery("INSERT INTO vtiger_critere4dcontrel (critere4did, contactid, dateapplication)
					     VALUES(?,?,NOW())", array($crmid, $with_crmid));

			} else {
				parent::save_related_module($module, $crmid, $with_module, $with_crmid);
			}
		}
	}
	

	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id) {
		global $log;
		if(empty($return_module) || empty($return_id)) return;

		if($return_module == 'Contacts') {
			$sql = 'DELETE FROM vtiger_critere4dcontrel WHERE contactid=? AND critere4did=?';
			$this->db->pquery($sql, array($return_id, $id));
		} else {
			$sql = 'DELETE FROM vtiger_crmentityrel WHERE (crmid=? AND relmodule=? AND relcrmid=?) OR (relcrmid=? AND module=? AND crmid=?)';
			$params = array($id, $return_module, $return_id, $id, $return_module, $return_id);
			$this->db->pquery($sql, $params);
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
	 * Move the related records of the specified list of id's to the given record.
	 * @param String This module name
	 * @param Array List of Entity Id's from which related records need to be transfered
	 * @param Integer Id of the the Record to which the related records are to be moved
	 * //ED151013
	 * @param Array If set with an array, count related but does not execute transfer. Returns array of module
	 *
	 *ED141012 TODO transfert Contacts avec champs inversés
	 * 
	 */
	function transferRelatedRecords($module, $transferEntityIds, $entityId, &$countingOnly = false) {
		global $adb,$log;
		$log->debug("Entering function transferRelatedRecords ($module, $transferEntityIds, $entityId)");

		$rel_table_arr = Array(
			"Critere4D"=>"vtiger_critere4dcontrel",
		);

		$tbl_field_arr = Array(
			"vtiger_critere4dcontrel"=>"contactid",
		);

		$entity_tbl_field_arr = Array(
			"vtiger_critere4dcontrel"=>"critere4did",
		);

		//ED150910
		$do_fields_reverse = Array();
		
		foreach($transferEntityIds as $transferId) {
			foreach($rel_table_arr as $rel_module=>$rel_table) {
				//ED150910 vtiger_contactscontrel dans les deux sens
				$do_reverse = array_key_exists($rel_table, $do_fields_reverse) ? 1 : 0;
				for($reverse = 0; $reverse <= 1; $reverse++){
					if($reverse === 0){
						//original
						$id_field = $tbl_field_arr[$rel_table];
						$entity_id_field = $entity_tbl_field_arr[$rel_table];
					}
					else {
						//reverse
						$entity_id_field = $tbl_field_arr[$rel_table];
						$id_field = $entity_tbl_field_arr[$rel_table];
					}
					// IN clause to avoid duplicate entries
					$sel_result =  $adb->pquery("select $id_field from $rel_table where $entity_id_field=? " .
							" and $id_field not in (select $id_field from $rel_table where $entity_id_field=?)",
							array($transferId,$entityId));
					$res_cnt = $adb->num_rows($sel_result);
					if($res_cnt > 0) {
						if(is_array($countingOnly)){
							if(!array_key_exists($rel_module, $countingOnly))
								$countingOnly[$rel_module] = $res_cnt;
							else
								$countingOnly[$rel_module] += $res_cnt;
						}
						else {
							for($i=0;$i<$res_cnt;$i++) {
								$id_field_value = $adb->query_result($sel_result,$i,$id_field);
								$adb->pquery("update $rel_table set $entity_id_field=? where $entity_id_field=? and $id_field=?",
									array($entityId,$transferId,$id_field_value));
							}
						}
					}
				}
			}
		}
		parent::transferRelatedRecords($module, $transferEntityIds, $entityId, $countingOnly);
		$log->debug("Exiting transferRelatedRecords...");
	}
}