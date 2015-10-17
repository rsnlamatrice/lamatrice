<?php
/*+***********************************************************************************
 * 
 *************************************************************************************/

/**
 * RSNAboRevues Module Model Class
 */
class RSNAboRevues_Module_Model extends Vtiger_Module_Model {
	/**
	 * Function to get Alphabet Search Field 
	 */
	public function getAlphabetSearchField(){
		return 'rsnabotype,isabonne';
	}
	
}
