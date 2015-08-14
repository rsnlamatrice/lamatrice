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
			
			'RsnReglements' => array('fieldName' => 'relcrmid', 'tableName' => 'vtiger_crmentityrel'
									 
					   , 'sourceFieldName' => 'vtiger_invoice.invoiceid' //WHERE %s IN
					   , 'sourceFieldNameInRelation' => 'vtiger_crmentityrel.relcrmid' // WHERE sourceFieldName IN ( SELECT %s FROM relationTableName JOIN %sub
					   , 'relationTableName' => 'vtiger_crmentityrel' // FROM %s JOIN %sub
					   , 'relatedFieldName' => 'rsnreglementsid' //  JOIN %sub ON relationTableName.%s = %sub.relatedSourceFieldName
					   , 'relatedSourceFieldName' => 'crmid'),
			
			'Products' => array('fieldName' => 'productid', 'tableName' => 'vtiger_inventoryproductrel'
									 
					   , 'sourceFieldName' => 'vtiger_invoice.invoiceid' //WHERE %s IN
					   , 'sourceFieldNameInRelation' => 'vtiger_inventoryproductrel.id' // WHERE sourceFieldName IN ( SELECT %s FROM relationTableName JOIN %sub
					   , 'relationTableName' => 'vtiger_inventoryproductrel' // FROM %s JOIN %sub
					   , 'relatedFieldName' => 'productid' //  JOIN %sub ON relationTableName.%s = %sub.relatedSourceFieldName
					   , 'relatedSourceFieldName' => 'productid'),
			
			'Services' => array('fieldName' => 'productid', 'tableName' => 'vtiger_inventoryproductrel'
									 
					   , 'sourceFieldName' => 'vtiger_invoice.invoiceid' //WHERE %s IN
					   , 'sourceFieldNameInRelation' => 'vtiger_inventoryproductrel.id' // WHERE sourceFieldName IN ( SELECT %s FROM relationTableName JOIN %sub
					   , 'relationTableName' => 'vtiger_inventoryproductrel' // FROM %s JOIN %sub
					   , 'relatedFieldName' => 'serviceid' //  JOIN %sub ON relationTableName.%s = %sub.relatedSourceFieldName
					   , 'relatedSourceFieldName' => 'productid'),
		);
	}
	
	public static function getAllRelations($parentModuleModel, $selected = true, $onlyActive = true) {
	}
}