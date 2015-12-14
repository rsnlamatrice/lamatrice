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
 * Vtiger Entity Record Model Class
 */
class ContactEmails_Record_Model extends Vtiger_Record_Model {

	/**
	 * ED141109
	 * getPicklistValuesDetails
	 */
	public function getPicklistValuesDetails($fieldname){
		switch($fieldname){
			case 'emailoptout'://contacts, rsnmediacontacts
				return array(
					'0' => array( 'label' => 'Ok', 'icon' => 'ui-icon ui-icon-unlocked darkgreen' ),
					'1' => array( 'label' => 'Erreur', 'icon' => 'ui-icon ui-icon-locked darkred' ),
				);
			default:
				return parent::getPicklistValuesDetails($fieldname);
		}
	}
}
