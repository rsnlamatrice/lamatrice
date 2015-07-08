<?php
/*+***********************************************************************************
 * 
 *************************************************************************************/

/**
 * Vtiger Field Model Class
 */
class Inventory_Field_Model extends Vtiger_Field_Model {

	/**
	 * sent2compta est toujours interdit de modification
	 * @return <Boolean> - true/false
	 */
	public function isReadOnly() {
		if($this->getName() === 'sent2compta')
			return true;
		return parent::isReadOnly();
	}
}