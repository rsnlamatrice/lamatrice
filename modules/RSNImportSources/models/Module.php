<?php
/*+***********************************************************************************
 * 
 * ************************************************************************************/

class RSNImportSources_Module_Model extends Vtiger_Module_Model {
    /**
	 * Function to check whether the entity has an quick create menu
	 * @return <Boolean> true/false
     */
    public function isQuickCreateMenuVisible() {
        return false ;
    }
}
