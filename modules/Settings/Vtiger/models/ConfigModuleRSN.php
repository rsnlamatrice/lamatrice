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
			
			'RSN_PRELVIRNTS_EMETTEUR'		=> array('label' => 'SEPA : Emetteur', 'fieldType' => 'string'),
			'RSN_PRELVIRNTS_SEPAIBANPAYS'		=> array('label' => 'SEPA : IBAN Pays', 'fieldType' => 'string'),
			'RSN_PRELVIRNTS_SEPAIBANCLE'		=> array('label' => 'SEPA : IBAN Clé', 'fieldType' => 'string'),
			'RSN_PRELVIRNTS_ETABLISSEMENT'		=> array('label' => 'SEPA : Etablissement', 'fieldType' => 'string'),
			'RSN_PRELVIRNTS_GUICHET'		=> array('label' => 'SEPA : Guichet', 'fieldType' => 'string'),
			'RSN_PRELVIRNTS_NUMEROCC'		=> array('label' => 'SEPA : Numéro CC', 'fieldType' => 'string'),
			'RSN_PRELVIRNTS_RIBCLE'			=> array('label' => 'SEPA : RIB Clé', 'fieldType' => 'string'),
			'RSN_PRELVIRNTS_SEPABIC'		=> array('label' => 'SEPA : SEPA BIC', 'fieldType' => 'string'),
			'RSN_PRELVIRNTS_NUMEROICS'		=> array('label' => 'SEPA : Numéro ICS', 'fieldType' => 'string'),
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