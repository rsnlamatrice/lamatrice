<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_Moduleslist_UIType extends Vtiger_Base_UIType {

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/Moduleslist.tpl';
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDisplayValue($value) {//tmp to check !!
		return vtranslate($value, $value);
	}

	public static function getListOfModules() {
		global $adb, $current_user, $old_related_modules;
		$restricted_modules = array('Events','Webmails');
		$moduleList = array();

		// Prefetch module info to check active or not and also get list of tabs
		$modulerows = vtlib_prefetchModuleActiveInfo(false);

		if($modulerows) {
			foreach($modulerows as $resultrow) {
				if($resultrow['presence'] == '1') continue;      // skip disabled modules
				if($resultrow['isentitytype'] != '1') continue;  // skip extension modules
				if(in_array($resultrow['name'], $restricted_modules)) { // skip restricted modules
					continue;
				}

				$moduleName = $resultrow['name'];

				if(isPermitted($moduleName,'index') == "yes") {
					$moduleList[$moduleName] = vtranslate($moduleName, $moduleName);
				}
			}

			asort($moduleList);
		}

		return $moduleList;
	}

}