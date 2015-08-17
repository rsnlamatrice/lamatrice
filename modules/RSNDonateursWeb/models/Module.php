<?php
/*+***********************************************************************************
 * ED14150102
 *************************************************************************************/
//vimport('~~/vtlib/Vtiger/Module.php');
/**
 * Vtiger Module Model Class
 */
class RSNDonateursWeb_Module_Model extends Vtiger_Module_Model {
	/**
	 * Function to check whether the entity has an quick create menu
	 * @return <Boolean> true/false
	 * ED141024
	 */
	public function isQuickCreateMenuVisible() {
		return false ;
	}
}
