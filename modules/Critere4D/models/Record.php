<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Critere4D_Record_Model extends Vtiger_Record_Model {
	

	/**
	 * Funtion to get TransormAsNewDocument Url
	 * @return <String>
	 */
	public function getTransformAsNewDocumentUrl() {
		return 'index.php?module='.$this->getModuleName().'&action=ToDocumentAjax&record='.$this->getId().'&mode=tranformAsNewDocument';

	}
	/**
	 * Funtion to get TransormAsNewDocument Url
	 * @return <String>
	 */
	public function getTransferToDocumentUrl() {
		return 'index.php?module='.$this->getModuleName().'&action=ToDocumentAjax&record='.$this->getId().'&mode=tranferToDocument';

	}
	
	/**
	 * Function to get selected ids list of related module for send email
	 * @param <String> $relatedModuleName
	 * @param <array> $excludedIds
	 * @return <array> List of selected ids
	 */
	public function getSelectedIdsList($relatedModuleName, $excludedIds = false) {
		$db = PearDatabase::getInstance();

		switch($relatedModuleName) {
		case 'Contacts'		: $tableName = "vtiger_critere4dcontrel";		$fieldName = "contactid";	break;
		}

		$query = "SELECT $fieldName FROM $tableName
					INNER JOIN vtiger_crmentity ON $tableName.$fieldName = vtiger_crmentity.crmid AND vtiger_crmentity.deleted = ?
					WHERE critere4did = ?";
		if ($excludedIds) {
			$query .= " AND $fieldName NOT IN (". implode(',', $excludedIds) .")";
		}

		$result = $db->pquery($query, array(0, $this->getId()));
		$numOfRows = $db->num_rows($result);

		$selectedIdsList = array();
		for ($i=0; $i<$numOfRows; $i++) {
			$selectedIdsList[] = $db->query_result($result, $i, $fieldName);
		}
		return $selectedIdsList;
	}
}

