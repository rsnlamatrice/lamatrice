<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
/* ED140907
 */
class _____Critere4D_Popup_View extends Vtiger_Popup_View {}
class Critere4D_Popup_View extends Vtiger_Popup_View {
	
	//protected $listViewHeaders
	/* Retourne les en-têtes des colonnes des tables liées
	 * Ajoute les champs de la relation
	 * */
	public function getHeaders($request) {
		$moduleName = $this->getModule($request);
		
		$relatedModuleModel = Vtiger_Module_Model::getInstance($moduleName);
			
		$headerFields = array();
		
		$headerFieldNames = $relatedModuleModel->getRelatedListFields();

		foreach($headerFieldNames as $fieldName) {
		    $headerFields[$fieldName] = $relatedModuleModel->getField($fieldName);
		}
		
		/* Champs issus de critere4dcontrel */
		return self::get_related_fields($headerFields, $request);
		
	}
	
	/* Retourne les en-têtes des colonnes des contacts
	 * Ajoute les champs de la relation
	 * ED140907
	 * */
	public static function get_related_fields($headerFields = false, $request = false) {
	    if(!$headerFields)
			$headerFields = array();

	    //Added to support data
	    $field = new Vtiger_Field_Model();
	    $field->set('tablename', 'vtiger_critere4dcontrel');
	    $field->set('name', '_counter_vtiger_critere4dcontrel');
	    $field->set('column', '_counter_vtiger_critere4dcontrel');
	    $field->set('label', 'Affectations');
	    /*ED140906 tests*/
	    $field->set('typeofdata', 'VARCHAR(255)');
	    $field->set('uitype', 2);
		    
	    array_push($headerFields, $field);
		
	    //Added to support dateapplication
	    $field = new Vtiger_Field_Model();
	    $field->set('tablename', 'vtiger_critere4dcontrel');
	    $field->set('name', 'dateapplication');
	    $field->set('column', strtolower( 'dateapplication' ));
	    $field->set('label', 'Derni&egrave;re application');
	    /*ED140906 tests*/
	    $field->set('typeofdata', 'DATETIME');
	    $field->set('uitype', 6);
	    
	    array_push($headerFields, $field);
	    
	    return $headerFields;
	}
	
	/*
	 * Function to initialize the required data in smarty to display the List View Contents
	 */
	public function initializeListViewContents(Vtiger_Request $request, Vtiger_Viewer $viewer) {
		
		if(!$this->listViewHeaders)
			$this->listViewHeaders = $this->getHeaders($request);
			
		$return = parent::initializeListViewContents($request, $viewer);
		
		$listViewEntries = $this->listViewEntries;
		
		// one more, for each row, set related values
		foreach($listViewEntries as $recordId => $record) {
			$record->set('dateapplication', $record->rawData['dateapplication']);
			$record->set('_counter_vtiger_critere4dcontrel', $record->rawData['_counter_vtiger_critere4dcontrel']);
		}
		
		return $return;
	}
}