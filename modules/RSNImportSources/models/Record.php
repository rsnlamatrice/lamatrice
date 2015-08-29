<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Vtiger Entity Record Model Class
 */
class RSNImportSources_Record_Model extends Vtiger_Record_Model {

	/** ED150827
	 * Static Function to get the instance of the Vtiger Record Model given the className and the module name
	 * @param <Number> $className
	 * @param <String> $moduleName
	 * @return Vtiger_Record_Model or Module Specific Record Model instance
	 */
	public static function getInstanceByClassName($className, $module=null) {
		$query = 'SELECT vtiger_crmentity.crmid
		FROM vtiger_crmentity
		JOIN vtiger_rsnimportsources
			ON vtiger_crmentity.crmid
		WHERE vtiger_crmentity.deleted = 0
		AND classname = ?';
		global $adb;
		$result = $adb->pquery($query, array($className));
		$id = $adb->query_result($result, 0);
		if($id)
			return self::getInstanceById($id, 'RSNImportSources');
	}
}
