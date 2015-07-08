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
 * Vtiger Field Model Class
 */
class Invoice_Field_Model extends Inventory_Field_Model {
	
	/** ED150625
	 * Returns parameters to filter popup selection list of a reference field.
	 * Data added to FieldInfo
	 */
	public function getPopupSearchInfo(){
		switch($this->getName()){
		 case 'notesid':
			return array('search_key' => 'folderid', 'search_value' => COUPON_FOLDERNAME);
		}
		return parent::getPopupSearchInfo();
	}
}