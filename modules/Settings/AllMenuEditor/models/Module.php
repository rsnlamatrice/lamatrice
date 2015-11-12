<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_AllMenuEditor_Module_Model extends Settings_Vtiger_Module_Model {
	
	var $name = 'AllMenuEditor';

	/**
	 * Function to save the menu structure
	 * ED141226 : enregistrement dans vtiger_rsnroletabsequence par rôle
	 */
	public function saveMenuStructure() {
		$db = PearDatabase::getInstance();
		$menusBlocks = $this->get('itemsPositions');
		
		/* vtiger_parenttab */
		$updateQuery = "UPDATE `vtiger_parenttab` SET `visible` = 0";			
		$db->pquery($updateQuery, array());
		
		$insertQuery = "INSERT INTO `vtiger_parenttab`
			(`parenttab_label`, `sequence`, `visible`) 
			";
		$params = array();
		$blockSequence = 0;
		foreach ($menusBlocks as $parenttab_label => $parentInfos) {
			if($blockSequence++ === 0)
				$insertQuery .= ' VALUES';
			else
				$insertQuery .= ', ';
			$insertQuery .= "(?, ?, 1)";
			$params[] = $parenttab_label;
			$params[] = $blockSequence;
		}
		$insertQuery .= ' ON DUPLICATE KEY UPDATE ';
		
		//sequence
		$blockSequence = 0;
		foreach ($menusBlocks as $parenttab_label => $parentInfos) {
			if($blockSequence++ === 0)
				$insertQuery .= ' sequence = CASE parenttab_label';
			$insertQuery .= ' WHEN ? THEN ?';
			$params[] = $parenttab_label;
			$params[] = $blockSequence;
		}
		$insertQuery .= ' END';
		
		//visible
		$insertQuery .= ', visible = 1';
		
		$result = $db->pquery($insertQuery, $params);
		if(!$result){
			echo "<pre>$insertQuery</pre>";
			var_dump($params);
			$db->echoError();
			die();
		}
		
		//get parent ids
		$parentIds = array();
		$query = 'SELECT parenttabid, parenttab_label
			FROM vtiger_parenttab';
		$result = $db->query($query);
		$rowIndex = 0;
		while($row = $db->fetch_row($result, $rowIndex++))
			$parentIds[strtoupper($row['parenttab_label'])] = $row['parenttabid'];
			
		/* tabrel */
		$deleteQuery = "DELETE FROM `vtiger_parenttabrel`";
		$result = $db->query($deleteQuery);
		
		//tabrel
		$insertQuery = "INSERT INTO `vtiger_parenttabrel`
			(`parenttabid`, `tabid`, `sequence`) 
			";
		$params = array();
		$sequence = 0;
		foreach ($menusBlocks as $parenttab_label => $parentInfos) {
			foreach ($parentInfos as $tabId) {
				if($sequence++ === 0)
					$insertQuery .= ' VALUES';
				else
					$insertQuery .= ', ';
				$insertQuery .= "(?, ?, ?)";
				$params[] = $parentIds[strtoupper($parenttab_label)];
				$params[] = $tabId;
				$params[] = $sequence;
			}
		}
		
		$result = $db->pquery($insertQuery, $params);
		if(!$result){
			echo "<pre>$insertQuery</pre>";
			var_dump($params);
			$db->echoError();
			die();
		}
		
		/* tab sequence */
		$updateQuery = "UPDATE `vtiger_tab`";
		
		$params = array();
		//parent
		$sequence = 0;
		foreach ($menusBlocks as $parenttab_label => $parentInfos) {
			foreach ($parentInfos as $tabId) {
				if($sequence++ === 0)
					$updateQuery .= ' SET parent = CASE tabid';
				$updateQuery .= ' WHEN ? THEN ?';
				$params[] = $tabId;
				$params[] = $parenttab_label;
			}
		}
		$updateQuery .= ' ELSE parent END';
		//tab sequence
		$sequence = 0;
		foreach ($menusBlocks as $parentInfos) {
			foreach ($parentInfos as $tabId) {
				if($sequence++ === 0)
					$updateQuery .= ', tabsequence = CASE tabid';
				$updateQuery .= ' WHEN ? THEN ?';
				$params[] = $tabId;
				$params[] = $sequence;
			}
		}
		$updateQuery .= ' ELSE tabsequence END';
		
		$result = $db->pquery($updateQuery, $params);
		if(!$result){
			echo "<pre>$updateQuery</pre>";
			var_dump($params);
			$db->echoError();
			die();
		}
	}
}
