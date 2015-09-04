<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_Picklist_UIType extends Vtiger_Base_UIType {

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/Picklist.tpl';
	}
	
	/** ED150413
	 * Function to get the header filter input template name for the current UI Type Object
	 * @return <String> - Template Name
	 */
	public function getHeaderFilterTemplateName() {
		return 'uitypes/MultiPicklistHeaderFilter.tpl';
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDisplayValue($value) {
		return Vtiger_Language_Handler::getTranslatedString($value, $this->get('field')->getModuleName());
	}

	/** ED150903
	 * Function to get the "alphabet" filter input template name for the current UI Type Object
	 * @return <String> - Template Name
	 */
	public function getAlphabetTemplateName() {
		return 'uitypes/PicklistAlphabet.tpl';
	}
}