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
					   , 'sourceFieldNameInRelation' => 'vtiger_crmentityrel.relcrmid' // WHERE sourceFieldName IN ( SELECT %s FROM relationTableName JOIN %sub
					   , 'relationTableName' => 'vtiger_crmentityrel' // FROM %s JOIN %sub
					   , 'relatedFieldName' => 'invoiceid' //  JOIN %sub ON relationTableName.%s = %sub.relatedSourceFieldName
					   , 'relatedSourceFieldName' => 'crmid'),
		);
	}
	public function addRelation($sourcerecordId, $destinationRecordId) {
		$destinationModuleName = $this->getRelationModuleModel()->get('name');
		if($destinationModuleName === 'Invoice'){
			$sourceModule = $this->getParentModuleModel();
			$sourceModuleName = $sourceModule->get('name');
			$invoiceRecordModel = Vtiger_Record_Model::getInstanceById($destinationRecordId, $destinationModuleName);
			$reglementRecordModel = Vtiger_Record_Model::getInstanceById($sourcerecordId, $sourceModuleName);
			$reglementRecordModel->set('mode', 'edit');
			$reglementRecordModel->set('account_id', $invoiceRecordModel->get('account_id'));
			$reglementRecordModel->save();
		}
		parent::addRelation($sourcerecordId, $destinationRecordId);
	}
}