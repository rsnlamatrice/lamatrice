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
class RSNContactsPanels_Record_Model extends Vtiger_Record_Model {

	/**
	 * retourne la requête d'après le champ query
	 *
	 */
	public function getPanelQuery(){
		return str_replace(array('&lt;', '&gt;','&#039;')
				   , array('<', '>', "'")
				   , $this->get('query'));
	}
}
