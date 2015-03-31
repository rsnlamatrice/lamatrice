<?php
/************************************************************************************
 * 
 *************************************************************************************/

class RSNMediaContacts_Record_Model extends Vtiger_Record_Model {

	
	/**
	 * ED141109
	 * getPicklistValuesDetails
	 */
	public function getPicklistValuesDetails($fieldname){
		switch($fieldname){
			case 'satisfaction':
				return array(
					'100' => array( 'label' => '', 'icon' => 'icon-rsn-small-smiley-100' ),
					'50' => array( 'label' => '', 'icon' => 'icon-rsn-small-smiley-50' ),
					'0' => array( 'label' => '', 'icon' => 'icon-rsn-small-smiley-0' ),
					'-50' => array( 'label' => '', 'icon' => 'icon-rsn-small-smiley--50' ),
					'-100' => array( 'label' => '', 'icon' => 'icon-rsn-small-smiley--100' ),
					
				);
			case 'emailoptout':
				return array(
					'0' => array( 'label' => 'si, on peut', 'icon' => 'ui-icon ui-icon-unlocked darkgreen' ),
					'1' => array( 'label' => 'Pas d\'email', 'icon' => 'ui-icon ui-icon-locked darkred' )
				);
			default:
				//die($fieldname);
				return array();
		}
	}
}
