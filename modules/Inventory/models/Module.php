<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/**
 * Inventory Module Model Class
 */
class Inventory_Module_Model extends Vtiger_Module_Model {

	/**
	 * Function to check whether the module is an entity type module or not
	 * @return <Boolean> true/false
	 */
	public function isQuickCreateSupported(){
		//SalesOrder module is not enabled for quick create
		return false;
	}

	/**
	 * Function to check whether the module is summary view supported
	 * @return <Boolean> - true/false
	 */
	public function isSummaryViewSupported() {
		return true;
	}

	static function getAllCurrencies() {
		return getAllCurrencies();
	}

	static function getAllProductTaxes() {
		return getAllTaxes('available');
	}

	static function getAllShippingTaxes() {
		return getAllTaxes('available', 'sh');
	}

	/**
	 * Function to get relation query for particular module with function name
	 * @param <record> $recordId
	 * @param <String> $functionName
	 * @param Vtiger_Module_Model $relatedModule
	 * @return <String>
	 */
	public function getRelationQuery($recordId, $functionName, $relatedModule) {
		if ($functionName === 'get_activities') {
			$userNameSql = getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');

			$query = "SELECT CASE WHEN (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END AS user_name,
						vtiger_crmentity.*, vtiger_activity.activitytype, vtiger_activity.subject, vtiger_activity.date_start, vtiger_activity.time_start,
						vtiger_activity.recurringtype, vtiger_activity.due_date, vtiger_activity.time_end, vtiger_seactivityrel.crmid AS parent_id,
						CASE WHEN (vtiger_activity.activitytype = 'Task') THEN (vtiger_activity.status) ELSE (vtiger_activity.eventstatus) END AS status
						FROM vtiger_activity
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_activity.activityid
						LEFT JOIN vtiger_seactivityrel ON vtiger_seactivityrel.activityid = vtiger_activity.activityid
						LEFT JOIN vtiger_cntactivityrel ON vtiger_cntactivityrel.activityid = vtiger_activity.activityid
						LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
						LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
							WHERE vtiger_crmentity.deleted = 0 AND vtiger_activity.activitytype = 'Task'
								AND vtiger_seactivityrel.crmid = ".$recordId;

			$relatedModuleName = $relatedModule->getName();
			$query .= $this->getSpecificRelationQuery($relatedModuleName);
			$nonAdminQuery = $this->getNonAdminAccessControlQueryForRelation($relatedModuleName);
			if ($nonAdminQuery) {
				$query = appendFromClauseToQuery($query, $nonAdminQuery);
			}
		} else {
			$query = parent::getRelationQuery($recordId, $functionName, $relatedModule);
		}

		return $query;
	}

	/** ED150619
	 * Function to get relation query for particular module with function name
	 * Similar to getRelationQuery but overridable.
	 * @param <record> $recordId
	 * @param <String> $functionName
	 * @param Vtiger_Module_Model $relatedModule
	 * @return <String>
	 */
	public function getRelationCounterQuery($recordId, $functionName, $relatedModule) {

		switch($relatedModule->getName()){
		// case 'RsnReglements' :
		 case 'Contacts' :
				//don't show if not > 1
				$query = parent::getRelationCounterQuery($recordId, $functionName, $relatedModule);
				$query = preg_replace('/^SELECT\sCOUNT\(\*\)/', 'SELECT IF(COUNT(*)>1, COUNT(*), 0)', $query);
				return $query;
		 default:
			return parent::getRelationCounterQuery($recordId, $functionName, $relatedModule);
		}
	}

	/**
	 * Function returns export query
	 * @param <String> $where
	 * @return <String> export query
	 */
	public function getExportQuery($focus, $query) {
		$baseTableName = $focus->table_name;
		$splitQuery = preg_split('/ FROM /i', $query);
		$columnFields = explode(',', $splitQuery[0]);
		foreach ($columnFields as $key => &$value) {
			if($value == ' vtiger_inventoryproductrel.discount_amount'){
				$value = ' vtiger_inventoryproductrel.discount_amount AS item_discount_amount';
			} else if($value == ' vtiger_inventoryproductrel.discount_percent'){
				$value = ' vtiger_inventoryproductrel.discount_percent AS item_discount_percent';
			} else if($value == " $baseTableName.currency_id"){
				$value = ' vtiger_currency_info.currency_name AS currency_id';
			}
		}
		$joinSplit = preg_split('/ WHERE /i',$splitQuery[1]);
		$joinSplit[0] .= " LEFT JOIN vtiger_currency_info ON vtiger_currency_info.id = $baseTableName.currency_id";
		$splitQuery[1] = $joinSplit[0] . ' WHERE ' .$joinSplit[1];

		$query = implode(',', $columnFields).' FROM ' . $splitQuery[1];

		return $query;
	}



	/**
	 * Function to save a given record model of the current module
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function saveRecord(Vtiger_Record_Model $recordModel) {
		//ED150707 : missing account_id
		if($this->getName() !== 'PurchaseOrder'
		&& (!$recordModel->get('account_id') || $recordModel->get('account_id') =='0')){
			$contactRecordModel = Vtiger_Record_Model::getInstanceById($recordModel->get('contact_id'), 'Contacts');
			$accountRecordModel = $contactRecordModel->getAccountRecordModel();
			$recordModel->set('account_id', $accountRecordModel->getId());
		}

		switch($this->getName()){
		case 'Invoice':
			$statusFieldName = 'invoicestatus';
			$previousStatus = Vtiger_Functions::getInvoiceStatus($recordModel->getId());
			break;
		case 'PurchaseOrder':
			$statusFieldName = 'postatus';
			$previousStatus = Vtiger_Functions::getPurchaseOrderStatus($recordModel->getId());
			break;
		case 'SalesOrder':
			$statusFieldName = 'sostatus';
			$previousStatus = Vtiger_Functions::getSalesOrderStatus($recordModel->getId());
			break;
		}

		$_REQUEST['previous_status'] = $previousStatus;
		$_REQUEST['new_status'] = $_REQUEST[$statusFieldName];

		//Annulation de la facture
		if($previousStatus === 'Cancelled'
		&& $_REQUEST[$statusFieldName] !== 'Cancelled'){
			$_REQUEST['status_previously_cancelled'] = true;
		}

		if($this->getName() === 'Invoice'){
			//ED151201 abandon
			///**
			// * ED151027
			//* Approved invoice with balance === 0 becomes Paid
			//*/
			//$approvedStatus = array('Approved', 'Created', 'AutoCreated');
			//$balance = (float)str_replace(',', '.', $recordModel->get('balance'));
			//if(in_array($_REQUEST[$statusFieldName], $approvedStatus)
			//&& $recordModel->get($statusFieldName) == $_REQUEST[$statusFieldName]
			//&& abs($balance) < 0.01){
			//	$recordModel->set($statusFieldName, 'Paid');
			//}
			//elseif($_REQUEST[$statusFieldName] === 'Paid'
			//&& $recordModel->get($statusFieldName) == $_REQUEST[$statusFieldName]
			//&& abs($balance) >= 0.01){
			//	//TODO alerter l'utilisateur
			//	//$recordModel->set('invoicestatus', 'Approved');
			//}
		}
		//Annulation de la facture
		if($_REQUEST[$statusFieldName] === 'Cancelled'
		&& $previousStatus !== 'Cancelled'){
			$_REQUEST['status_becomes_cancelled'] = true;
		}
		return parent::saveRecord($recordModel);
	}

	/**
	 * Function to get Alphabet Search Field
	 */
	public function getAlphabetSearchField(){
		return 'account_id'; //TODO invoicestatus ne fonctionne pas
	}
}
