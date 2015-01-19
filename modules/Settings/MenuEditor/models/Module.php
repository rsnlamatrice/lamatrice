<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_MenuEditor_Module_Model extends Settings_Vtiger_Module_Model {
	
	var $name = 'MenuEditor';

	/**
	 * Function to save the menu structure
	 * ED141226 : enregistrement dans vtiger_rsnroletabsequence par rôle
	 */
	public function saveMenuStruncture() {
		$db = PearDatabase::getInstance();
		$selectedModulesList = $this->get('selectedModulesList');
		$roleid = $this->get('roleid');
		if(!$roleid || $roleid == 'H1'){
			$updateQuery = "UPDATE vtiger_tab SET tabsequence = CASE tabid ";
	
			foreach ($selectedModulesList as $sequence => $tabId) {
				$updateQuery .= " WHEN $tabId THEN $sequence ";
			}
			$updateQuery .= "ELSE -1 END";
			
			$db->pquery($updateQuery, array());
		}
		if($roleid){
			//purge
			$deleteQuery = "DELETE FROM vtiger_rsnroletabsequence WHERE roleid = ?";
			$db->pquery($deleteQuery, array($roleid));
			//insertion
			$insertQuery = "INSERT INTO vtiger_rsnroletabsequence (tabid, roleid, tabsequence)
			SELECT tabid, ?, CASE tabid";
			$i = 0;
			$params = array($roleid);
			foreach ($selectedModulesList as $sequence => $tabId) {
				$insertQuery .= " WHEN $tabId THEN $sequence ";
			}
			$insertQuery .= ' ELSE -1 END
				FROM vtiger_tab';
			$result = $db->pquery($insertQuery, $params);
			//var_dump($result);
		}
	}
}
