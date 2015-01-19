<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_Reference_UIType extends Vtiger_Base_UIType {

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName() {
		return 'uitypes/Reference.tpl';
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getReferenceModule($value) {
		$fieldModel = $this->get('field');
		$referenceModuleList = $fieldModel->getReferenceList();
		$referenceEntityType = getSalesEntityType($value);
		if(in_array($referenceEntityType, $referenceModuleList)) {
			return Vtiger_Module_Model::getInstance($referenceEntityType);
		} elseif (in_array('Users', $referenceModuleList)) {
			return Vtiger_Module_Model::getInstance('Users');
		}
		return null;
	}

	/**
	 * Function to get the display value in detail view
	 * @param <Integer> crmid of record
	 * @return <String>
	 */
	public function getDisplayValue($value) {
		$referenceModule = $this->getReferenceModule($value);
		if($referenceModule && !empty($value)) {
			$referenceModuleName = $referenceModule->get('name');
			if($referenceModuleName == 'Users') {
				$db = PearDatabase::getInstance();
				$nameResult = $db->pquery('SELECT first_name, last_name FROM vtiger_users WHERE id = ?', array($value));
				if($db->num_rows($nameResult)) {
					return $db->query_result($nameResult, 0, 'first_name').' '.$db->query_result($nameResult, 0, 'last_name');
				}
			} else {
				$entityNames = getEntityName($referenceModuleName, array($value));
				$label = $entityNames[$value];
				
				$focus = CRMEntity::getInstance($referenceModuleName);
				if(isset($focus->uicolor_field)){
					
					$uicolorField = Vtiger_Field_Model::getInstance( $focus->uicolor_field, $referenceModule);
					if($uicolorField) {
						$uicolorFieldName = $uicolorField->getName();
						$uicolorList = Vtiger_Cache::get('Field-UIColor-List-' . $uicolorFieldName, $uicolorField->id);
						if(!$uicolorList)
							$uicolorList = array();
						if(@$uicolorList[$value]){
							
						}
						else {
							
							switch($uicolorField->get('uitype')){
							case '33':
							case '15':
							case '16':
								/* picklist, les uicolor sont dans la table du picklist */
								/* toutes les valeurs du picklist */
								$pickListValuesData = array();
								$pickListValues = Vtiger_Util_Helper::getPickListValues($uicolorFieldName, $pickListValuesData);
							//var_dump($pickListValuesData);
								/* les valeurs du champ pour chaque id */
								$fieldValueList = getEntityFieldValue($referenceModule, $uicolorField, array($value));
							//var_dump($fieldValueList);
							
								/* concordance avec les id */
								foreach($fieldValueList as $id => $pickValue){
									if(strpos($pickValue, ' |##| ')){
										$pickValue = substr($pickValue, 0, strpos($pickValue,' |##| '));
									}
									$uicolorList[$id] = @$pickListValuesData[decode_html($pickValue)]['uicolor'];
								}
								break;
							default:
								/* valeur du champ comme couleur */
								$fieldValueList = getEntityFieldValue($referenceModule, $uicolorField, array($value));
								foreach($fieldValueList as $id => $fieldValue){
									$uicolorList[$id] = $fieldValue;
								}
								
								break;
							}
							//echo "ICICIICIC IC IC " . $uicolorField->getName(); var_dump($this->uicolorList[$uicolorFieldName]);
							Vtiger_Cache::set('Field-UIColor-List-' . $uicolorFieldName, $uicolorField->id, $uicolorList);
						     
						}
						if(@$uicolorList[$value])
							$label = '<div class="picklistvalue-uicolor" style="background-color:'. $uicolorList[$value] . '">&nbsp;</div>'
								. $label;
							
					}
				}

				$linkValue = "<a href='index.php?module=$referenceModuleName&view=".$referenceModule->getDetailViewName()."&record=$value'
							title='".vtranslate($referenceModuleName, $referenceModuleName)."'>$label</a>";
				return $linkValue;
			}
		}
		return '';
	}

	/**
	 * Function to get the display value in edit view
	 * @param reference record id
	 * @return link
	 */
	public function getEditViewDisplayValue($value) {
		$referenceModule = $this->getReferenceModule($value);
		if($referenceModule) {
			$referenceModuleName = $referenceModule->get('name');
			$entityNames = getEntityName($referenceModuleName, array($value));
			return $entityNames[$value];
		}
		return '';
	}

}