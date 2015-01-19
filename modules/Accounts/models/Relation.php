<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/* ED140906
	 */
class Accounts_Relation_Model extends Vtiger_Relation_Model {

	/**
	 * Function to get Critere4D enabled modules list for detail view of record
	 * @return <array> List of modules
	 * ED140906
	 */
	public function getModulesInfoForDetailView() {
		return array(
			'Critere4D' => array('fieldName' => 'critere4did', 'tableName' => 'vtiger_critere4dcontrel')
		);
	}
}