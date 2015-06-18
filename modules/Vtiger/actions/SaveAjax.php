<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_SaveAjax_Action extends Vtiger_Save_Action {

	/*
	 */
	public function process(Vtiger_Request $request) {
		//save
		$recordModel = $this->saveRecord($request);

		$fieldModelList = $recordModel->getModule()->getFields();
		$result = array();
		foreach ($fieldModelList as $fieldName => $fieldModel) {
			$recordFieldValue = $recordModel->get($fieldName);
			if(is_array($recordFieldValue)){
				if($fieldModel->getFieldDataType() == 'multipicklist') {
					$recordFieldValue = implode(' |##| ', $recordFieldValue);
					$fieldValue = $displayValue = Vtiger_Util_Helper::toSafeHTML($recordFieldValue);
				}
				else {
					$recordFieldValue = json_encode($recordFieldValue);
					$fieldValue = html_entity_decode($recordFieldValue);
					$displayValue = ($recordFieldValue);
				}
			
			}
			else
				$fieldValue = $displayValue = Vtiger_Util_Helper::toSafeHTML($recordFieldValue);
			switch ($fieldModel->getFieldDataType()){
			 case 'currency':
			 case 'datetime':
			 case 'date':
				break;
			 case 'uicolor':
				$displayValue = sprintf('<div class="" style="background-color:%s">&nsbp;</div>',
							$fieldModel->getDisplayValue($fieldValue, $recordModel->getId()));
				break;
			 case 'buttonSet':
				$values = $recordModel->getPicklistValuesDetails($fieldName);
				$displayValue = isset($values[$fieldValue])
					? sprintf('<div class="buttonset  ui-buttonset">'.
						  '<label class="ui-button ui-widget ui-state-default ui-corner-left ui-corner-right ui-state-active" aria-pressed="true" role="button" aria-disabled="false">'.
						  '<span class="ui-button-text"><span class="%s"></span>&nbsp;%s</span></label></div>',
						      $values[$fieldValue]['icon'],
						      $values[$fieldValue]['label']
					)
					: $fieldValue;
				break;
			 default:
				/* ED141005
				 * les types 56, 15, 16, 33 et 402 ont une chance de voir leur valeur d'affichage adaptée par le Record_Model
				 */
				if(in_array( $fieldModel->get('uitype'), array(56,15,16,33,402))){
					$displayValue = $recordModel->getDisplayValue($fieldName, $recordModel->getId());
				}
				else
					$displayValue = $fieldModel->getDisplayValue($fieldValue, $recordModel->getId());
				break;
			}
			$result[$fieldName] = array('value' => $fieldValue, 'display_value' => $displayValue);
		}

		//Handling salutation type
		if ($request->get('field') === 'firstname' && in_array($request->getModule(), array('Contacts', 'Leads'))) {
			$salutationType = $recordModel->getDisplayValue('salutationtype');
			$firstNameDetails = $result['firstname'];
			$firstNameDetails['display_value'] = $salutationType. " " .$firstNameDetails['display_value'];
			if ($salutationType != '--None--') $result['firstname'] = $firstNameDetails;
		}

		$result['_recordLabel'] = $recordModel->getName();
		$result['_recordId'] = $recordModel->getId();

		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}

	/* ED150125
	 * Classiquement, les appels AJax passent une valeur de field avec un champ $request->get('field') et un champ $request->get('value').
	 *  un champ $request->get('fields') peut remplacer cela pour fournir plusieurs champs.
	 *  Les classes héritières préexistantes ne prennent pas en compte 'fields', d'où cette function générale.
	 * Les valeurs de plusieurs champs sont passés par fields
	 * Attention, un autre mode (FORM ?) passe tous les champs directement dans la $request, $request->get($fieldName)
	*/
	public function getRequestFieldsValues(Vtiger_Request $request) {

		$fieldsValues = $request->get('fields');
		if($fieldsValues)
			return $fieldsValues;
		if($request->get('field'))
			return array($request->get('field') => $request->get('value'));
		return array(); //TODO liste des propriétés de $request mais exclure 'record', 'module', 'mode', ...
	}
	
	/**
	 * Function to get the record model based on the request parameters
	 * @param Vtiger_Request $request
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 *
	 * mode d'origine
	 * 	$request->get('field') et $request->get('value')
	 * mode multiple
	 * 	$request->get('fields') === array( <fieldName> => <fieldValue>, <fieldName> => <fieldValue>, ...)
	 */
	public function getRecordModelFromRequest(Vtiger_Request $request) {

		/* ED150125
		 * Les valeurs de plusieurs champs sont passés par fields
		 */
		$fieldsValues = $this->getRequestFieldsValues($request);
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		if(!empty($recordId)) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			$recordModel->set('id', $recordId);
			$recordModel->set('mode', 'edit');

			$fieldModelList = $recordModel->getModule()->getFields();
			foreach ($fieldModelList as $fieldName => $fieldModel) {
				
				/* ED150125 */
				if(isset($fieldsValues[$fieldName]))
					$fieldValue = $fieldsValues[$fieldName];
				else
					$fieldValue = $fieldModel->getUITypeModel()->getUserRequestValue($recordModel->get($fieldName));

				$fieldDataType = $fieldModel->getFieldDataType();
				if ($fieldDataType == 'time') {
					$fieldValue = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldValue);
				}
				if ($fieldValue !== null) {
					if (!is_array($fieldValue)) {
						$fieldValue = trim($fieldValue);
					}
					$recordModel->set($fieldName, $fieldValue);
				}
				//$recordModel->set($fieldName, $fieldValue);
			}
		} else {
			$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

			$recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
			$recordModel->set('mode', '');

			$fieldModelList = $moduleModel->getFields();
			foreach ($fieldModelList as $fieldName => $fieldModel) {
				if ($request->has($fieldName)) {
					$fieldValue = $request->get($fieldName, null);
				} else {
					$fieldValue = $fieldModel->getDefaultFieldValue();
				}
				$fieldDataType = $fieldModel->getFieldDataType();
				if ($fieldDataType == 'time') {
					$fieldValue = Vtiger_Time_UIType::getTimeValueWithSeconds($fieldValue);
				}
				if ($fieldValue !== null) {
					if (!is_array($fieldValue)) {
						$fieldValue = trim($fieldValue);
					}
					$recordModel->set($fieldName, $fieldValue);
				}
			} 
		}

		return $recordModel;
	}
}
