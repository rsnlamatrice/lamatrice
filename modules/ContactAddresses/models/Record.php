<?php
/*+***********************************************************************************
 *************************************************************************************/

class ContactAddresses_Record_Model extends Vtiger_Record_Model {


	/**
	 * ED141005
	 * getPicklistValuesDetails
	 */
	public function getPicklistValuesDetails($fieldname){
		switch($fieldname){
			case 'rsnnpai':
				return array(
					'0' => array( 'label' => 'Ok', 'icon' => 'ui-icon ui-icon-check green' ),
					'1' => array( 'label' => 'Supposée', 'icon' => 'ui-icon ui-icon-check darkgreen' ),
					'2' => array( 'label' => 'A confirmer', 'icon' => 'ui-icon ui-icon-close orange' ),
					'3' => array( 'label' => 'Définitive', 'icon' => 'ui-icon ui-icon-close darkred' ),
					'4' => array( 'label' => 'Incomplète', 'icon' => 'ui-icon ui-icon-close darkred' ),
				);
			default:
				return parent::getPicklistValuesDetails($fieldname);
		}
	}
}
