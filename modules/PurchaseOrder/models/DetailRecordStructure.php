<?php
/*+***********************************************************************************
 * ED150630
 *************************************************************************************/

/**
 * Vtiger Detail View Record Structure Model
 */
class PurchaseOrder_DetailRecordStructure_Model extends Vtiger_DetailRecordStructure_Model {

	/**
	 * Function to get the values in stuctured format
	 * @return <array> - values in structure array('block'=>array(fieldinfo));
	 * ED150630 : sent2compta visible only for invoices
	 */
	public function getStructure() {
		if(!empty($this->structuredValues)) {
			return $this->structuredValues;
		}

		$values = array();
		$recordModel = $this->getRecord();
		$recordExists = !empty($recordModel);
		$moduleModel = $this->getModule();
		$blockModelList = $moduleModel->getBlocks();
		foreach($blockModelList as $blockLabel=>$blockModel) {
			$fieldModelList = $blockModel->getFields();
			if (!empty ($fieldModelList)) {
				$values[$blockLabel] = array();
				foreach($fieldModelList as $fieldName=>$fieldModel) {
					//ED150630 : sent2compta visible only for invoices
					if($fieldName === 'sent2compta' && (!$recordExists || $recordModel->get('potype') != 'invoice'))
					   continue;
					if($fieldModel->isViewableInDetailView()) {
						if($recordExists) {
							$fieldModel->set('fieldvalue', $recordModel->get($fieldName));
						}
						$values[$blockLabel][$fieldName] = $fieldModel;
					}
				}
			}
		}
		$this->structuredValues = $values;
		return $values;
	}
}