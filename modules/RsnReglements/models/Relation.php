<?php
/*+***********************************************************************************
 * ED151021
 *************************************************************************************/


class RsnReglements_Relation_Model extends Vtiger_Relation_Model {

	/**
	 * Function to get enabled modules list for detail view of record
	 * @return <array> List of modules
	 * ED150417
	 */
	public function getModulesInfoForDetailView() {
		return array(
			
			'Accounts' => array('fieldName' => 'accountid', 'tableName' => 'vtiger_rsnreglements'
					   , 'sourceFieldName' => 'vtiger_account.accountid'),
			
			'Invoice' => array('fieldName' => 'crmid', 'tableName' => 'vtiger_crmentityrel'
									 
					   , 'sourceFieldName' => 'vtiger_rsnreglements.rsnreglementsid' //WHERE %s IN
					   , 'sourceFieldNameInRelation' => 'vtiger_crmentityrel.crmid' // WHERE sourceFieldName IN ( SELECT %s FROM relationTableName JOIN %sub
					   , 'relationTableName' => 'vtiger_crmentityrel' // FROM %s JOIN %sub
					   , 'relatedFieldName' => 'invoiceid' //  JOIN %sub ON relationTableName.%s = %sub.relatedSourceFieldName
					   , 'relatedSourceFieldName' => 'relcrmid'),
		);
	}
}