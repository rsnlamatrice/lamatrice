<?php
/*+**********************************************************************************
 * ED151025
 ************************************************************************************/

class RSNStatisticsResults extends CRMEntity {
	
	var $tab_name = array();
	
	var $table_index= 'id';
		/**
	 * Mandatory for Listing (Related listview)
	 */
	var $list_fields = Array (
		'LBL_RSNSTATISTICSID' => array('rsnstatisticsresults', 'rsnstatisticsid'),
		'LBL_CRMID' => array('rsnstatisticsresults', 'crmid'),
		'LBL_CODE' => array('rsnstatisticsresults', 'code'),
		'LBL_NAME' => array('rsnstatisticsresults', 'name'),
	);
	var $list_fields_name = Array (
		'LBL_RSNSTATISTICSID' => 'rsnstatisticsid',
		'LBL_CRMID' => 'crmid',
		'LBL_CODE' => 'code',
		'LBL_NAME' => 'name',
	);

	/**
	* Invoked when special actions are performed on the module.
	* @param String Module name
	* @param String Event Type
	*/
	function vtlib_handler($moduleName, $eventType) {
		global $adb;
 		if($eventType == 'module.postinstall') {
			// TODO Handle actions after this module is installed.
		} else if ($eventType == 'module.enabled') {
		} else if($eventType == 'module.disabled') {
			// TODO Handle actions before this module is being uninstalled.
		} else if($eventType == 'module.preuninstall') {
			// TODO Handle actions when this module is about to be deleted.
		} else if($eventType == 'module.preupdate') {
			// TODO Handle actions before this module is updated.
		} else if($eventType == 'module.postupdate') {
			// TODO Handle actions after this module is updated.
		}
 	}
}