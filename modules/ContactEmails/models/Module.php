<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class ContactEmails_Module_Model extends Vtiger_Module_Model{

	/**
	 * Function to check whether the entity has an quick create menu
	 * @return <Boolean> true/false
	 * ED141024
	 */
	public function isQuickCreateMenuVisible() {
		return false ;
	}
	
	/*
	 * Function that returns the record model for a contact and an email address
	 */
	public function getRecordModelFromContactAndEmail($contactId, $email){
		
		global $adb;
		
		$params = array();
		$query = 'SELECT contactemailsid
			FROM `vtiger_contactemails`
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = `vtiger_contactemails`.`contactemailsid`
			WHERE vtiger_crmentity.deleted = 0
			AND `vtiger_contactemails`.contactid = ?
			AND `vtiger_contactemails`.email = ?
			ORDER BY vtiger_crmentity.createdtime DESC
			LIMIT 1';
		$result = $adb->pquery($query, array($contactId, $email));
		if(!$result)
			$adb->echoError();
		if($adb->num_rows($result)){
			$contactEmailsId = $adb->query_result($result, 0, 0);
			return Vtiger_Record_Model::getInstanceById($contactEmailsId, 'ContactEmails');
		}
		return false;
	}
	
	/**
	 * Function to save a given record model of the current module
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function saveRecord(Vtiger_Record_Model $recordModel) {
		$return = parent::saveRecord($recordModel);
		if($recordModel->get('emailaddressorigin') === 'Principale'){
			$this->ensureOnlyOnePrincipale($recordModel);
		}
		return $return;
	}
	
	//S'assure qu'une seule adresse est Principale
	public function ensureOnlyOnePrincipale(Vtiger_Record_Model $recordModel){
		
		global $adb;
		$query = 'UPDATE `vtiger_contactemails`
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = `vtiger_contactemails`.`contactemailsid`
			SET emailaddressorigin = "Ancienne adresse"
			WHERE vtiger_crmentity.deleted = 0
			AND `vtiger_contactemails`.contactemailsid != ?
			AND `vtiger_contactemails`.contactid = ?
			AND `vtiger_contactemails`.emailaddressorigin = "Principale"'
		;
		$params = array($recordModel->getId(), $recordModel->get('contactid'));
		$result = $adb->pquery($query, $params);
		if(!$result)
			die($adb->echoError(__FILE__.'::ensureOnlyOnePrincipale()', true));
	}
}
?>