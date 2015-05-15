<?php
/*+***********************************************************************************
 * ED150417
 *************************************************************************************/


class Inventory_Relation_Model extends Vtiger_Relation_Model {

	/**
	 * Function to get enabled modules list for detail view of record
	 * @return <array> List of modules
	 * ED150417
	 */
	public function getModulesInfoForDetailView() {
		return array(
			'Contacts' => array('fieldName' => 'accountid', 'tableName' => 'vtiger_invoice'
					   , 'sourceFieldName' => 'vtiger_contactdetails.accountid'),
			
			'Accounts' => array('fieldName' => 'accountid', 'tableName' => 'vtiger_invoice'
					   , 'sourceFieldName' => 'vtiger_account.accountid'),
		);
	}

}