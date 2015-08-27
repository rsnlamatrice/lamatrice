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

	/** ED150825, à cause de l'héritage de Import, List devient ListView
	 * Function to get the ListView Component Name
	 * @return string
	 */
	public function getListViewName() {
		return 'ListView';
	}
	/** ED150825, à cause de l'héritage de Import, List devient ListView
	 * Function to get the url for default view of the module
	 * @return <string> - url
	 */
	public function getDefaultUrl() {
		return $this->getListViewUrl();
	}
}
