<?php
/*+***********************************************************************************
 * 
 *************************************************************************************/

/**
 * Vtiger Field Model Class
 */
class PurchaseOrder_Field_Model extends Vtiger_Field_Model {

	/**
	 * potype est toujours interdit de modification
	 * @return <Boolean> - true/false
	 */
	public function isReadOnly() {
		if($this->getName() === 'potype')
			return true;
		return parent::isReadOnly();
	}
}
?>