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
			default:
				//die($fieldname);
				return array();
		}
	}
}
