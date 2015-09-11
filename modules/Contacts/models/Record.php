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

	public static $preventInfiniteSave;
	
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
				array('parentField'=>'mailingstreet2', 'inventoryField'=>'bill_street2', 'defaultValue'=>''),
				array('parentField'=>'mailingstreet3', 'inventoryField'=>'bill_street3', 'defaultValue'=>''),
				array('parentField'=>'mailingstate', 'inventoryField'=>'bill_state', 'defaultValue'=>''),
				array('parentField'=>'mailingzip', 'inventoryField'=>'bill_code', 'defaultValue'=>''),
				array('parentField'=>'mailingcountry', 'inventoryField'=>'bill_country', 'defaultValue'=>''),
				array('parentField'=>'mailingpobox', 'inventoryField'=>'bill_pobox', 'defaultValue'=>''),

				//Shipping Address Fields
				array('parentField'=>'otherstreet', 'inventoryField'=>'ship_street', 'defaultValue'=>''),
				array('parentField'=>'otherstreet2', 'inventoryField'=>'ship_street2', 'defaultValue'=>''),
				array('parentField'=>'otherstreet3', 'inventoryField'=>'ship_street3', 'defaultValue'=>''),
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
			if($db->getRowCount($result) == 0){
				$imageId = null;
				$imagePath = null;
				if($this->get('isgroup')){
					$imageName = 'Collectif';
					$rsnClass = 'isgroup'.$this->get('isgroup');
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
					'1' => array( 'label' => 'Association', 'icon' => 'icon-rsn-small-isgroup1' ),
					'2' => array( 'label' => 'Entreprise', 'icon' => 'icon-rsn-small-isgroup2' ),
					'3' => array( 'label' => 'Orga. public', 'icon' => 'icon-rsn-small-isgroup3' ),
					'4' => array( 'label' => 'Parti politique', 'icon' => 'icon-rsn-small-isgroup4' ),
					'5' => array( 'label' => 'Autre structure', 'icon' => 'icon-rsn-small-isgroup5' ),
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
			case 'donotcourrierag':
				return array(
					'0' => array( 'label' => 'si, on peut', 'icon' => 'ui-icon ui-icon-unlocked darkgreen' ),
					'1' => array( 'label' => 'Pas de courrier AG', 'icon' => 'ui-icon ui-icon-locked darkred' )
				);
			case 'contreltype':
				return array(
					'REF' => array( 'label' => 'Référence', 'icon' => 'ui-icon ui-icon-locked darkred' ),
					'MBR' => array( 'label' => 'Membre', 'icon' => '' ),
					'FOYER' => array( 'label' => 'Membre', 'icon' => '' )
				);
			case 'signcharte':
				return array(
					'0' => array( 'label' => 'non', 'icon' => 'ui-icon ui-icon-close' ),
					'1' => array( 'label' => 'signataire', 'icon' => 'ui-icon ui-icon-check darkgreen' )
				);
			
			case 'donototherdocuments':
				// champ issu de 4D : typeabonnee == 'Reçu fiscal seul (1)'
				// types de document que nous pouvons envoyé à ce contact
				//
				return array(
					'' => array( 'label' => 'aucun', 'icon' => 'ui-icon ui-icon-unlocked darkgreen' ),
					'Reçu fiscal seul' => array( 'label' => 'Reçu fiscal seul', 'icon' => 'ui-icon ui-icon-locked darkred' )
				);
			
			case '(all the same)':
				return array(
					'0' => array( 'label' => 'si, on peut tout faire', 'icon' => 'ui-icon ui-icon-unlocked darkgreen' ),
					'1' => array( 'label' => 'ne rien faire', 'icon' => 'ui-icon ui-icon-locked darkred' )
				);
			default:
				return parent::getPicklistValuesDetails($fieldname);
		}
	}
	
	/** ED150828 for abstract
	* getPicklistValues called on HeaderFilter context
	*/
	public function getPicklistValuesDetailsForHeaderFilter($fieldname){
		if($fieldname == 'isgroup'){
			$valuesData = $this->getPicklistValuesDetails($fieldname);
			$valuesData['<>0'] = array( 'label' => '(toutes struct.)', 'icon' => 'icon-rsn-small-collectif' );
			return $valuesData;
		}
		else
			return $this->getPicklistValuesDetails($fieldname);
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
					'1' => array( 'label' => '', 'icon' => 'icon-rsn-small-isgroup1' ),
					'2' => array( 'label' => '', 'icon' => 'icon-rsn-small-isgroup2' ),
					'3' => array( 'label' => '', 'icon' => 'icon-rsn-small-isgroup3' ),
					'4' => array( 'label' => '', 'icon' => 'icon-rsn-small-isgroup4' ),
					'5' => array( 'label' => '', 'icon' => 'icon-rsn-small-isgroup5' ),
				);
			case 'donotcall':
				return array(
					'0' => array( 'label' => 'si, on peut', 'icon' => ' ' ),
					'1' => array( 'label' => 'Ne pas appeler', 'icon' => 'ui-icon ui-icon-locked darkred' )
				);
			default:
				return parent::getListViewPicklistValues($fieldname);
		}
	}
	
	/**
	 * Retourne le compte du contact.
	 * Si absent, le génère.
	 * ED150000
	 */
	public function getAccountRecordModel($createIfNone = true){
		//echo '<pre>'; var_dump($this);echo '</pre>'; 
		$account_id = $this->get('account_id');
		// creation d'un compte par défaut                                      
		if(($account_id == null || $account_id == '0') ){
			if(!$createIfNone) return false;
			
			$account = Vtiger_Record_Model::getCleanInstance('Accounts');    
			$account->set('mode', 'create');
			
			// Nom en majsucules
			$account->set('accountname', decode_html(trim($this->get('lastname') . ' ' . $this->get('firstname'))));
			// Synchro de l'adresse
			$this->updateAccountAddress($account, false);
			// Save account
			$account->save();
			
			// Update contact
			$this->set('mode', 'edit');
			
			$account_id = $account->getId();
			$this->set('account_id', $account_id);
			$this->set('reference', 1);// contact referent du nouveau compte
			
			$this->save();
			return $account;
		}	
		return Vtiger_Record_Model::getInstanceById($account_id, 'Accounts');
	}
	
	/**
	 * Retourne les contacts référents du même compte que ce contact.
	 * ED150225
	 */
	public function getCompteCommunContacts(){
		//echo '<pre>'; var_dump($this);echo '</pre>'; 
		$account_id = $this->get('account_id');
		$this_id = $this->getId();
		
		global $adb;
		if($this_id ==null)
			$this_id = $this->id;
		$contacts = array();
		$query = 'SELECT contactid FROM vtiger_contactdetails
				INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid
				WHERE vtiger_contactdetails.accountid = ?
				AND vtiger_contactdetails.contactid <> ?
				AND vtiger_contactdetails.reference = 1
				AND vtiger_crmentity.deleted = 0';
		$accountContacts = $adb->pquery($query, array($account_id, $this_id));
		$numOfContacts = $adb->num_rows($accountContacts);
		if($accountContacts && $numOfContacts > 0) {
			for($i=0; $i < $numOfContacts; ++$i) {
				//TODO : better then multi-query (in fact, may not have more then 1 contact)
				$contact = Vtiger_Record_Model::getInstanceById($adb->query_result($accountContacts, $i, 'contactid'), 'Contacts');
				array_push($contacts, $contact);
			}
		}
		return $contacts;
	}
	
	/**
	 * Recopie l'adresse du contact vers l'adresse du compte et des autres contacts référents du même compte.
	 * ED150205
	 */
	public function synchronizeAddressToOthers(){
		if(!is_array(self::$preventInfiniteSave))
			self::$preventInfiniteSave = array();
		elseif(in_array($this->getId(), self::$preventInfiniteSave))
			return;
		self::$preventInfiniteSave[] = $this->getId();
		$this->updateAccountAddress();
		// update contacts en compte commun
		$this->updateCompteCommunContactsAddress();
	}
	
	/**
	 * Recopie l'adresse du contact dans l'adresse du compte.
	 * ED150205
	 */
	public function updateAccountAddress($account = false, $save = true){
		//echo '<pre>'; var_dump($this);echo '</pre>'; 
		$account_id = $this->get('account_id');
		if(!$account
		&& !($account_id == null || $account_id == '0') ){
			$account = $this->getAccountRecordModel(false);
		}
		if($account){
			if($save)
				$account->set('mode', 'edit');
			
			$thisFields = $this->getModule()->getFields();
			$has_changed = false; //prevents recursive update
			//Parcourt les champs
			foreach($thisFields as $fieldName => $field){
				// Champ commençant par "mailing"
				if(strpos($fieldName, 'mailing') === 0
				|| strpos($fieldName, 'rsnnpai') === 0){
					if($fieldName == 'mailingzip')
						$account_field = 'bill_code';
					else
						$account_field = 'bill_' . substr($fieldName, 7);
					if($account->get($account_field) != $this->get($fieldName)){
						$account->set($account_field, $this->get($fieldName));
						$has_changed = true;
					}
				}
				//else var_dump($fieldName);
			
			}
			if($has_changed && $save)
				$account->save();
		}
		
		return $has_changed;
	}
	
	/**
	 * Recopie l'adresse des contacts référents du même compte.
	 * ED150205
	 */
	public function updateCompteCommunContactsAddress(){
		
		$contacts = $this->getCompteCommunContacts();
		$thisFields = $this->getModule()->getFields();
			
		foreach($contacts as $contact){
			if(!is_array(self::$preventInfiniteSave))
				self::$preventInfiniteSave = array();
			elseif(in_array(self::$preventInfiniteSave, array($this->getId())))
				continue;
			self::$preventInfiniteSave[] = $contact->getId();
			
			$contact->set('mode', 'edit');
			$has_changed = false; //prevents recursive update
			//Parcourt les champs
			foreach($thisFields as $fieldName => $field){
				// Champ commençant par "mailing"
				if(strpos($fieldName, 'mailing') === 0
				|| strpos($fieldName, 'rsnnpai') === 0){
					if(html_entity_decode($contact->get($fieldName)) != html_entity_decode($this->get($fieldName))){
						$contact->set($fieldName, $this->get($fieldName));
						$has_changed = true;
					}
				}
			}
			if($has_changed)
				$contact->save();
		}		
		return $has_changed;
	}
	
	/*ED150312
	 * Copy current address to a new ContactAdresses record
	 *
	 * used by Contacts_Save_Action::process
	 */
	public function createContactAddressesRecord($fieldPrefix = 'mailing', $save = true){
		
		$addressModule = Vtiger_Module_Model::getInstance('ContactAddresses');
		$addressRecord = Vtiger_Record_Model::getCleanInstance('ContactAddresses');
		$addressRecord->set('mode', 'create');
		$addressRecord->set('addresstype', 'Ancienne adresse');
		$mapping = array(
			'id' => 'contactid',
			'modifiedtime' => 'createdtime',
			'rsnnpai' => 'rsnnpai',
			'rsnnpaicomment' => 'rsnnpaicomment',
			$fieldPrefix.'street' => 'mailingstreet',
			$fieldPrefix.'street2' =>'mailingstreet2',
			$fieldPrefix.'street3' => 'mailingstreet3',
			$fieldPrefix.'pobox' => 'mailingpobox',
			$fieldPrefix.'city' => 'mailingcity',
			$fieldPrefix.'state' => 'mailingstate',
			$fieldPrefix.'zip' => 'mailingzip',
			$fieldPrefix.'country' => 'mailingcountry',
			$fieldPrefix.'addressformat' => 'mailingaddressformat',
		);
		//echo '<pre>';
		foreach($mapping as $sourceField => $destField){
			$addressRecord->set($destField, $this->get($sourceField));
			//var_dump($sourceField, $destField, $this->get($sourceField), $addressRecord->get($destField));
		}
		//echo '</pre>';
		//die();
		if($save){
			$addressRecord->save();
			$addressRecord->set('mode', '');
		}
		return $addressRecord;
	}
	
	/*ED150318
	 * Copy current email address to a new ContactEmails record
	 *
	 * used by Contacts_Save_Action::process
	 */
	public function createContactEmailsRecord($save = true){
		
		$addressModule = Vtiger_Module_Model::getInstance('ContactEmails');
		$addressRecord = Vtiger_Record_Model::getCleanInstance('ContactEmails');
		$addressRecord->set('mode', 'create');
		$mapping = array(
			'id' => 'contactid',
			'email' => 'email',
			'emailoptout' => 'emailoptout',
			'modifiedtime' => 'createdtime',
		);
		//echo '<pre>';
		foreach($mapping as $sourceField => $destField){
			$addressRecord->set($destField, $this->get($sourceField));
			//var_dump($sourceField, $destField, $addressRecord->get($destField));
		}
		//echo '</pre>';
		//die();
		if($save){
			$addressRecord->save();
			$addressRecord->set('mode', '');
		}
		return $addressRecord;
	}
	
	
	/* ED15015 : un seul contact peut être référent du compte
	 */
	public function ensureAccountHasOnlyOneMainContact(){
		if($this->get('account_id') && $this->get('reference')){
			$query = "UPDATE vtiger_contactdetails
			SET reference = 0
			WHERE contactid <> ?
			AND accountid = ?
			AND reference = 1";
		
			global $adb;
			$adb->pquery($query, array( $this->getId(), $this->get('account_id') ));
		}
		return $this;
	}
	
	/** ED150814
	* Affectation de critère au contact
	*/	
	function assignRelatedCritere4D($critereId, $dateApplication, $relData) {
		
		$params = array();
		$query = 'INSERT INTO `vtiger_critere4dcontrel` (`critere4did`, `contactid`, `dateapplication`, `data`)
			VALUES(?, ?, ?, ?)';
		$params[] = $critereId;
		$params[] = $this->getId();
		$params[] = DateTimeField::convertToDBFormat($dateApplication);
		$params[] = $relData;
		
		$query .= '
		ON DUPLICATE KEY UPDATE data = ?';
		$params[] = $relData;
		
		global $adb;
		return $adb->pquery($query, $params);
	}
	
	

	/** ED150910
	 * Function to transfer related records of parent records to this record
	 * @param <Array> $recordIds
	 * @return <Boolean> true/false
	 *
	 * Called from Vtiger_ProcessDuplicates_Action
	 * New values are already set.
	 */
	public function transferRelationInfoOfRecords($recordIds = array()) {
		if ($recordIds) {
			
			$mainAccountModel = $this->getAccountRecordModel(false);
			foreach($recordIds as $recordId){
				$deleteRecord = Vtiger_Record_Model::getInstanceById($recordId, $this->getModuleName());
				$deleteAccount = $deleteRecord->getAccountRecordModel(false);
				if($deleteAccount){
					if( ! $mainAccountModel){
						// Transfers account owner
						$this->set('mode', 'edit');
						$this->set('reference', $deleteRecord->get('reference'));
						$this->set('account_id', $deleteAccount->getId());
						$this->save();
						$mainAccountModel = $deleteAccount;
					}
					elseif($mainAccountModel->getId() != $deleteAccount->getId()){
						// Transfers account related data
						// only if this contact to delete is the "reference"
						$deleteAccountContacts = $deleteAccount->getContactsRecordModels();
						$doDeleteAccount = true;
						if(count($deleteAccountContacts) > 1){
							//Not alone
							
							if($deleteRecord->get('reference')){
								// every one follow me ! (done above in $mainAccountModel->transferRelationInfoOfRecords())
							}
							else //some one else is the "reference"
								$doDeleteAccount = false;
						}
						if($deleteRecord->get('reference')){
							$mainAccountModel->transferRelationInfoOfRecords(array($deleteAccount->getId()));
						}
						if($doDeleteAccount)
							$deleteAccount->delete();
						
					}
				}
			}
			
			parent::transferRelationInfoOfRecords($recordIds);
			
		}
		return true;
	}
}
