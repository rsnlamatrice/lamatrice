<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_Multipicklist_UIType extends Vtiger_Base_UIType {

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/MultiPicklist.tpl';
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
		$fieldModel = $this->get('field');
		if(is_string($value) && preg_match('/\<.*uicolor/', $value))
			return $value;
		/* search of uicolor */
		$uiColumns = Vtiger_Util_Helper::getPicklistUIColumns($fieldModel->getName());
		if(is_array($uiColumns) && isset($uiColumns['uicolor'])){
			if(!is_array($value)){
				$value = explode(' |##| ', $value);
			}
			$picklistvaluesdata = array();
			$picklistValues = Vtiger_Util_Helper::getPickListValues($fieldModel->getName(), $picklistvaluesdata);
			if(is_array($picklistvaluesdata)){
				for($i = 0; $i < count($value); $i++){
					$val_decoded = trim(html_entity_decode($value[$i]));
					if(isset($picklistvaluesdata[$val_decoded])){
						$uicolor = $picklistvaluesdata[$val_decoded]['uicolor'];
						$value[$i] = ($uicolor ? '<div class="picklistvalue-uicolor" style="background-color:'. $uicolor . '">&nbsp;</div>' : '')
							. $value[$i];
					}
				}
			}
		}
		
		if(is_array($value)){
		    $value = implode(' |##| ', $value);
		}
		return str_ireplace(' |##| ', ', ', $value);
	}
    
	public function getDBInsertValue($value) {
		if(is_array($value)){
			$value = implode(' |##| ', $value);
		}
		return $value;
	}
}