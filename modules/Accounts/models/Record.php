<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Accounts_Record_Model extends Vtiger_Record_Model {

	/**
	 * Function returns the details of Accounts Hierarchy
	 * @return <Array>
	 */
	function getAccountHierarchy() {
		$focus = CRMEntity::getInstance($this->getModuleName());
		$hierarchy = $focus->getAccountHierarchy($this->getId());
		$i=0;
		foreach($hierarchy['entries'] as $accountId => $accountInfo) {
			preg_match('/<a href="+/', $accountInfo[0], $matches);
			if($matches != null) {
				preg_match('/[.\s]+/', $accountInfo[0], $dashes);
				preg_match("/<a(.*)>(.*)<\/a>/i",$accountInfo[0], $name);

				$recordModel = Vtiger_Record_Model::getCleanInstance('Accounts');
				$recordModel->setId($accountId);
				$hierarchy['entries'][$accountId][0] = $dashes[0]."<a href=".$recordModel->getDetailViewUrl().">".$name[2]."</a>";
			}
		}
		return $hierarchy;
	}
	/** ED150515
	 * Function returns the account main contact
	 * @return <Array>
	 */
	function getRelatedMainContacts() {
		
		$moduleName = $this->getModuleName();
		$relatedModuleName = 'Contacts';
		$parentId = $this->getId();
		
		$query = "SELECT vtiger_crmentity.crmid, vtiger_crmentity.label, vtiger_contactdetails.*
			FROM vtiger_contactdetails
			JOIN vtiger_crmentity
				ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
			WHERE vtiger_crmentity.deleted = 0
			AND vtiger_contactdetails.accountid = ?
			AND vtiger_contactdetails.reference = 1
		";
		global $adb;
		$entries = array();
		$result = $adb->pquery($query, array($this->getId()));
		while(!$result->EOF){
			$row = $adb->fetchByAssoc($result);
			$entries[$row['crmid']] = $row;
		}
		return $entries;
	}

	/**
	 * Function returns the url for create event
	 * @return <String>
	 */
	function getCreateEventUrl() {
		$calendarModuleModel = Vtiger_Module_Model::getInstance('Calendar');
		return $calendarModuleModel->getCreateEventRecordUrl().'&parent_id='.$this->getId();
	}

	/**
	 * Function returns the url for create todo
	 * @retun <String>
	 */
	function getCreateTaskUrl() {
		$calendarModuleModel = Vtiger_Module_Model::getInstance('Calendar');
		return $calendarModuleModel->getCreateTaskRecordUrl().'&parent_id='.$this->getId();
	}

	/**
	 * Function to check duplicate exists or not
	 * @return <boolean>
	 */
	public function checkDuplicate() {
		$db = PearDatabase::getInstance();

		$query = "SELECT 1 FROM vtiger_crmentity WHERE setype = ? AND label = ? AND deleted = 0";
		$params = array($this->getModule()->getName(), decode_html($this->getName()));

		$record = $this->getId();
		if ($record) {
			$query .= " AND crmid != ?";
			array_push($params, $record);
		}

		$result = $db->pquery($query, $params);
		if ($db->num_rows($result)) {
			return true;
		}
		return false;
	}

	/**
	 * Function to get List of Fields which are related from Accounts to Inventory Record.
	 * @return <array>
	 */
	public function getInventoryMappingFields() {
		return array(	
				//Billing Address Fields
				array('parentField'=>'bill_city', 'inventoryField'=>'bill_city', 'defaultValue'=>''),
				array('parentField'=>'bill_street', 'inventoryField'=>'bill_street', 'defaultValue'=>''),
				array('parentField'=>'bill_street2', 'inventoryField'=>'bill_street2', 'defaultValue'=>''),
				array('parentField'=>'bill_street3', 'inventoryField'=>'bill_street3', 'defaultValue'=>''),
				array('parentField'=>'bill_state', 'inventoryField'=>'bill_state', 'defaultValue'=>''),
				array('parentField'=>'bill_code', 'inventoryField'=>'bill_code', 'defaultValue'=>''),
				array('parentField'=>'bill_country', 'inventoryField'=>'bill_country', 'defaultValue'=>''),
				array('parentField'=>'bill_pobox', 'inventoryField'=>'bill_pobox', 'defaultValue'=>''),
				array('parentField'=>'bill_addressformat', 'inventoryField'=>'bill_addressformat', 'defaultValue'=>''),

				//Shipping Address Fields
				array('parentField'=>'ship_city', 'inventoryField'=>'ship_city', 'defaultValue'=>''),
				array('parentField'=>'ship_street', 'inventoryField'=>'ship_street', 'defaultValue'=>''),
				array('parentField'=>'ship_street2', 'inventoryField'=>'ship_street2', 'defaultValue'=>''),
				array('parentField'=>'ship_street3', 'inventoryField'=>'ship_street3', 'defaultValue'=>''),
				array('parentField'=>'ship_state', 'inventoryField'=>'ship_state', 'defaultValue'=>''),
				array('parentField'=>'ship_code', 'inventoryField'=>'ship_code', 'defaultValue'=>''),
				array('parentField'=>'ship_country', 'inventoryField'=>'ship_country', 'defaultValue'=>''),
				array('parentField'=>'ship_pobox', 'inventoryField'=>'ship_pobox', 'defaultValue'=>''),
				array('parentField'=>'ship_addressformat', 'inventoryField'=>'ship_addressformat', 'defaultValue'=>''),
		);
	}
	
	
	
	/** ED150507
	 * Function to get RSNAboRevues array for this account
	 */
	public function getRSNAboRevues($isabonneOnly = false){
		
		$moduleName = $this->getModuleName();
		$relatedModuleName = 'RSNAboRevues';
		$parentId = $this->getId();
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page',1);
		$pagingModel->set('limit', $isabonneOnly ? 8 : 99); //TODO 99?!
			
		$parentRecordModel = $this;
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName, null);

		$orderBy = 'debutabo';
		$sortOrder = 'DESC';
			
		$relationListView->set('orderby', $orderBy);
		$relationListView->set('sortorder',$sortOrder);

//$db = PearDatabase::getInstance();
//$db->setDebug(true);
		$allEntries = $relationListView->getEntries($pagingModel);
		if(!$isabonneOnly)
			return $allEntries;
		$entries = array();
		foreach($allEntries as $id=>$entry)
			if($entry->get('isabonne'))
				$entries[$id] = $entry;
			else
				break;
		return $entries;
	}
}
