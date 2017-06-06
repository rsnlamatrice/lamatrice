<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Critere4D_Module_Model extends Vtiger_Module_Model {

	/**
	 * Funtion to get TransormAsNewDocument Url
	 * @return <String>
	 */
	public function getTransformAsNewDocumentUrl() {
		return 'index.php?module='.$this->getName().'&action=ToDocumentAjax&mode=tranformAsNewDocument';

	}

	/**
	 * Function to get list view query for popup window
	 * @param <String> $sourceModule Parent module
	 * @param <String> $field parent fieldname
	 * @param <Integer> $record parent id
	 * @param <String> $listQuery
	 * @return <String> Listview Query
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, $listQuery) {
		if (in_array($sourceModule, array('Contacts'))) {
			switch($sourceModule) {
			case 'Contacts':
				$tableName = 'vtiger_critere4dcontrel';
				$relatedFieldName = 'contactid';
				$select_field = 'dateapplication';
				$condition = false;
				break;
			default:
				return $listQuery;
			}

			if(isset($select_field)){
				$pos = stripos($listQuery, ' FROM ');
	//var_dump($listQuery);
				if ($pos) {//TODO substr($pos)
					$split = preg_split('/ FROM /i', $listQuery);
					$overRideQuery = $split[0] //TODO jointure
						. ', (SELECT COUNT(*)'
							.' FROM ' . $tableName
							.' WHERE ' . $tableName .'.critere4did = vtiger_crmentity.crmid ) AS _counter_' . $tableName
						. ', (SELECT MAX(dateapplication)'
							.' FROM ' . $tableName
							.' WHERE ' . $tableName .'.critere4did = vtiger_crmentity.crmid ) AS dateapplication'
						. ' FROM ' . $split[1];
				} else { //BUGG
					$overRideQuery = $listQuery;
				}
	//var_dump($overRideQuery);
			}
			if(!$condition)
				return $overRideQuery;

			$position = stripos($overRideQuery, ' WHERE ');
			if($position) {
				$split = preg_split('/ WHERE /i', $overRideQuery);
				$overRideQuery = $split[0] . ' WHERE ' . $split[1] . ' AND ' . $condition;
			} else {
				$overRideQuery = $overRideQuery. ' WHERE ' . $condition;
			}
			return $overRideQuery;
		}
	}

	/**
	 * Function to get Alphabet Search Field
	 */
	public function getAlphabetSearchField(){
		return 'categorie,nom';
	}

	/**
	 * Function to get Alphabet Search Field for popup
	 */
	public function getAlphabetSearchFieldForPopup(){
		return 'categorie';
	}
}