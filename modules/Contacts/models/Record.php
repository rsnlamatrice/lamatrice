<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Contacts_Record_Model extends Vtiger_Record_Model {

	
	/**
	 * Function returns the url for create event
	 * @return <String>
	 */
	function getCreateEventUrl() {
		$calendarModuleModel = Vtiger_Module_Model::getInstance('Calendar');
		return $calendarModuleModel->getCreateEventRecordUrl().'&contact_id='.$this->getId();
	}

	/**
	 * Function returns the url for create todo
	 * @return <String>
	 */
	function getCreateTaskUrl() {
		$calendarModuleModel = Vtiger_Module_Model::getInstance('Calendar');
		return $calendarModuleModel->getCreateTaskRecordUrl().'&contact_id='.$this->getId();
	}


	/**
	 * Function to get List of Fields which are related from Contacts to Inventory Record
	 * @return <array>
	 */
	public function getInventoryMappingFields() {
		return array(
				array('parentField'=>'account_id', 'inventoryField'=>'account_id', 'defaultValue'=>''),

				//Billing Address Fields
				array('parentField'=>'mailingcity', 'inventoryField'=>'bill_city', 'defaultValue'=>''),
				array('parentField'=>'mailingstreet', 'inventoryField'=>'bill_street', 'defaultValue'=>''),
				array('parentField'=>'mailingstate', 'inventoryField'=>'bill_state', 'defaultValue'=>''),
				array('parentField'=>'mailingzip', 'inventoryField'=>'bill_code', 'defaultValue'=>''),
				array('parentField'=>'mailingcountry', 'inventoryField'=>'bill_country', 'defaultValue'=>''),
				array('parentField'=>'mailingpobox', 'inventoryField'=>'bill_pobox', 'defaultValue'=>''),

				//Shipping Address Fields
				array('parentField'=>'otherstreet', 'inventoryField'=>'ship_street', 'defaultValue'=>''),
				array('parentField'=>'othercity', 'inventoryField'=>'ship_city', 'defaultValue'=>''),
				array('parentField'=>'otherstate', 'inventoryField'=>'ship_state', 'defaultValue'=>''),
				array('parentField'=>'otherzip', 'inventoryField'=>'ship_code', 'defaultValue'=>''),
				array('parentField'=>'othercountry', 'inventoryField'=>'ship_country', 'defaultValue'=>''),
				array('parentField'=>'otherpobox', 'inventoryField'=>'ship_pobox', 'defaultValue'=>'')
		);
	}
	
	/**
	 * Function to get Image Details
	 * @return <array> Image Details List
	 */
	public function getImageDetails() {
		$db = PearDatabase::getInstance();
		$imageDetails = array();
		$recordId = $this->getId();
		
		if ($recordId) {
			$sql = "SELECT vtiger_attachments.*, vtiger_crmentity.setype FROM vtiger_attachments
						INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_attachments.attachmentsid
						WHERE vtiger_crmentity.setype = 'Contacts Image' and vtiger_seattachmentsrel.crmid = ?";

			$result = $db->pquery($sql, array($recordId));

			/* ED40927 */
			if(true && $db->getRowCount($result) == 0){
				$imageId = null;
				$imagePath = null;
				if($this->get('isgroup')){
					$imageName = 'Collectif';
					$rsnClass = 'collectif';
				}
				else {
					$imageName = 'Individuel';
					$rsnClass = 'contact';
				}
			}			
			else {
				$imageId = $db->query_result($result, 0, 'attachmentsid');
				$imagePath = $db->query_result($result, 0, 'path');
				$imageName = $db->query_result($result, 0, 'name');
				$rsnClass = null;
			}
			//decode_html - added to handle UTF-8 characters in file names
			$imageOriginalName = decode_html($imageName);

			//urlencode - added to handle special characters like #, %, etc.,
			$imageName = urlencode($imageName);

			$imageDetails[] = array(
					'id' => $imageId,
					'orgname' => $imageOriginalName,
					'path' => $imagePath.$imageId,
					'name' => $imageName,
					'rsnClass' => $rsnClass
			);
		}
		return $imageDetails;
	}
	
	/**
	 * Function to retieve display value for a field
	 * @param <String> $fieldName - field name for which values need to get
	 * @param <Integer> $recordId - record
	 * @param <Boolean> if field is unknown, returns the value otherwise FALSE value ED140907
	 *	in .tpl : {$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME, false, true)}
	 * @return <String>
	 */
	public function getDisplayValue($fieldName, $recordId = false, $unknown_field_returns_value = false) {
		if(empty($recordId)) {
			$recordId = $this->getId();
		}
		
		if(is_a($fieldName, "Vtiger_Field_Model"))
			$fieldName = $fieldName->getName();
		switch($fieldName){
			case 'isgroup':
				if(empty($recordId)) {
					$recordId = $this->getId();
				}
				$values = $this::getPicklistValuesDetails($fieldName);
				return @$values[$this->get($fieldName)]['label'];
			default:
				return parent::getDisplayValue($fieldName, $recordId, $unknown_field_returns_value);
		}
	}
	
	/**
	 * ED141005
	 * getPicklistValuesDetails
	 */
	public function getPicklistValuesDetails($fieldname){
		switch($fieldname){
			case 'isgroup':
				return array(
					'0' => array( 'label' => 'Particulier', 'icon' => 'icon-rsn-small-contact' ),
					'1' => array( 'label' => 'Structure', 'icon' => 'icon-rsn-small-collectif' )
				);
			case 'reference':
				return array(
					'0' => array( 'label' => 'Non', 'icon' => 'ui-icon ui-icon-close' ),
					'1' => array( 'label' => 'Référent du compte', 'icon' => 'ui-icon ui-icon-check darkgreen' )
				);
			case 'donotprospect':
				return array(
					'0' => array( 'label' => 'si, on peut', 'icon' => 'ui-icon ui-icon-unlocked darkgreen' ),
					'1' => array( 'label' => 'Ne pas prospecter', 'icon' => 'ui-icon ui-icon-locked darkred' )
				);
			case 'emailoptout':
				return array(
					'0' => array( 'label' => 'si, on peut', 'icon' => 'ui-icon ui-icon-unlocked darkgreen' ),
					'1' => array( 'label' => 'Pas d\'email', 'icon' => 'ui-icon ui-icon-locked darkred' )
				);
			case 'donotcall':
				return array(
					'0' => array( 'label' => 'si, on peut', 'icon' => 'ui-icon ui-icon-unlocked darkgreen' ),
					'1' => array( 'label' => 'Ne pas appeler', 'icon' => 'ui-icon ui-icon-locked darkred' )
				);
			case 'donotrelanceabo':
			case 'donotrelanceadh':
				return array(
					'0' => array( 'label' => 'si, on peut', 'icon' => 'ui-icon ui-icon-unlocked darkgreen' ),
					'1' => array( 'label' => 'Ne pas relancer', 'icon' => 'ui-icon ui-icon-locked darkred' )
				);
			case 'donotappeldonweb':
			case 'donotappeldoncourrier':
				return array(
					'0' => array( 'label' => 'si, on peut', 'icon' => 'ui-icon ui-icon-unlocked darkgreen' ),
					'1' => array( 'label' => 'Pas d\'appel à don', 'icon' => 'ui-icon ui-icon-locked darkred' )
				);
			case 'contreltype':
				return array(
					'REF' => array( 'label' => 'Référence', 'icon' => 'ui-icon ui-icon-locked darkred' ),
					'MBR' => array( 'label' => 'Membre', 'icon' => '' ),
					'FOYER' => array( 'label' => 'Membre', 'icon' => '' )
				);
			case 'rsnnpai':
				return array(
					'0' => array( 'label' => 'Ok', 'icon' => 'ui-icon ui-icon-check green' ),
					'1' => array( 'label' => 'Supposée', 'icon' => 'ui-icon ui-icon-check darkgreen' ),
					'2' => array( 'label' => 'A confirmer', 'icon' => 'ui-icon ui-icon-close orange' ),
					'3' => array( 'label' => 'Définitive', 'icon' => 'ui-icon ui-icon-close darkred' ),
				);
			default:
				//die($fieldname);
				return array();
		}
	}
	
	/**
	 * ED141005
	 * getListViewPicklistValues
	 */
	public function getListViewPicklistValues($fieldname){
		switch($fieldname){
			case 'isgroup':
				return array(
					'0' => array( 'label' => '', 'icon' => 'icon-rsn-small-contact' ),
					'1' => array( 'label' => '', 'icon' => 'icon-rsn-small-collectif' )
				);
			case 'donotcall':
				return array(
					'0' => array( 'label' => 'si, on peut', 'icon' => ' ' ),
					'1' => array( 'label' => 'Ne pas appeler', 'icon' => 'ui-icon ui-icon-locked darkred' )
				);
			default:
				return $this->getPicklistValuesDetails($fieldname);
		}
	}
	
	/**
	 *
	 */
	public function getAccountRecordModel($createIfNone = true){
		//echo '<pre>'; var_dump($this);echo '</pre>'; 
		$account_id = $this->get('account_id');
		// creation d'un compte par défaut                                      
		if(($account_id == null || $account_id == '0') ){
			if(!$createIfNone) return false;
			//var_dump("getCleanInstance");
			$account = Vtiger_Record_Model::getCleanInstance('Accounts');    
			//var_dump("set('accountname'," .  trim($this->get('lastname') . ' ' . $this->get('firstname')));
			$account->set('accountname', decode_html(trim($this->get('lastname') . ' ' . $this->get('firstname'))));
			$account->set('mode', 'create');
			$account->save();
			$account_id = $account->getId();
			//var_dump($account_id);
			$this->set('account_id', $account_id);
			$this->set('reference', 1);// contact referent du compte
			//var_dump($this->get('account_id'));
			$this->set('mode', 'edit');
			$this->save();
			return $account;
		}	
		return Vtiger_Record_Model::getInstanceById($account_id, 'Accounts');
	}
}
