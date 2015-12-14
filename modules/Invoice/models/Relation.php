<?php
/*+***********************************************************************************
 * ED150417
 *************************************************************************************/

class Invoice_Relation_Model extends Inventory_Relation_Model {

	public function addRelation($sourcerecordId, $destinationRecordId) {
		$destinationModuleName = $this->getRelationModuleModel()->get('name');
		if($destinationModuleName === 'RsnReglements'){
			$sourceModule = $this->getParentModuleModel();
			$sourceModuleName = $sourceModule->get('name');
			$invoiceRecordModel = Vtiger_Record_Model::getInstanceById($sourcerecordId, $sourceModuleName);
			$reglementRecordModel = Vtiger_Record_Model::getInstanceById($destinationRecordId, $destinationModuleName);
			$reglementRecordModel->set('mode', 'edit');
			$reglementRecordModel->set('account_id', $invoiceRecordModel->get('account_id'));
			$reglementRecordModel->save();
		}
		parent::addRelation($sourcerecordId, $destinationRecordId);
	}

}