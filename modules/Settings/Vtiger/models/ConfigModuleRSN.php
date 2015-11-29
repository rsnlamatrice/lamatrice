<?php
/*+***********************************************************************************
 * ED150522
 *************************************************************************************/

class Settings_Vtiger_ConfigModuleRSN_Model extends Settings_Vtiger_ConfigModule_Model {

	var $fileName = 'config.RSN.inc.php';
	//ED150521
	var $config_domain = 'RSN';
	
	/**
	 * Function to get editable fields
	 * @return <Array> list of field names
	 */
	public function getEditableFields() {
		return array(
			'RSN_PRODUCTS_ALLOW_MULTIPLE_TAXES'	=> array('label' => 'RSN_PRODUCTS_ALLOW_MULTIPLE_TAXES', 'fieldType' => 'picklist'),
			'RSN_PRODUCT_ALLOW_DUPLICATE'		=> array('label' => 'RSN_PRODUCT_ALLOW_DUPLICATE', 'fieldType' => 'picklist'),
		);
	}

	/**
	 * Function to validate the field values
	 * @param <Array> $updatedFields
	 * @return <String> True/Error message
	 */
	public function validateFieldValues($updatedFields){
		return true;
	}

}