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
 * Vtiger Detail View Record Structure Model
 */
class RsnPrelevements_DetailRecordStructure_Model extends Vtiger_DetailRecordStructure_Model {

	/**
	 * Function to get the values in stuctured format
	 * @return <array> - values in structure array('block'=>array(fieldinfo));
	 * ED151204 verrouille les champs si un ordre de prŽlvement existe djˆ
	 */
	public function getStructure() {
		$structuredValues = parent::getStructure();
		RsnPrelevements_RecordStructure_Model::checkEditableStructure($this->record, $structuredValues);
		return $structuredValues;
	}
}