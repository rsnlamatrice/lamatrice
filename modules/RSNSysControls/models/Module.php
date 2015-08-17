<?php
/*+***********************************************************************************
 * 
 * ************************************************************************************/

class RSNSysControls_Module_Model extends Vtiger_Module_Model {
	/**
	 * Function to check whether the entity has an quick create menu
	 * @return <Boolean> true/false
	 * ED141024
	 */
	public function isQuickCreateMenuVisible() {
		return false ;
	}

}
