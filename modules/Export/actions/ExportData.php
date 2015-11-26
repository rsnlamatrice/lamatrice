<?php
/*+***********************************************************************************
	AV1511
 *************************************************************************************/

class Export_ExportData_Action extends Vtiger_ExportData_Action {
	/**
	 * Function is called by the controller
	 * @param Vtiger_Request $request
	 */
	function process(Vtiger_Request $request) {
		$exportClassName = $request->get('ExportClassName');
		$sourceModule = $request->get('source_module');
		$exporter = Export_Utils_Helper::getExportClassFromName($exportClassName, $sourceModule);

		if ($exporter) {
			$exporter->ExportData($request);
		} else {
			parent::ExportData($request);
		}
	}

	/**
	 * Function exports the data based on the mode
	 * @param Vtiger_Request $request
	 */
	function ExportData(Vtiger_Request $request) { //TMP chexk array enad sql query and...
		//tmp sql query / field / ...
		$exportStructure = $this->getExportStructure();
		$isPreview = $request->get('preview');
		//tmp previous code
		$db = PearDatabase::getInstance();
		$moduleName = $request->get('source_module');

		$this->moduleInstance = Vtiger_Module_Model::getInstance($moduleName);
		$this->moduleFieldInstances = $this->moduleInstance->getFields();
		$this->focus = CRMEntity::getInstance($moduleName);

		$query = $this->getExportQuery($request);
		$result = $db->pquery($query, array());

		if ($this->displayHeaderLine()) {
			$headers = array();
			foreach ($exportStructure as $label => $value) {
				$headers[] = $label;
			}

			foreach($headers as $header) $translatedHeaders[] = vtranslate(html_entity_decode($header, ENT_QUOTES), $moduleName);
		} else {
			$translatedHeaders = null;
		}

		$entries = array();
		for($j=0; $j<$db->num_rows($result); $j++) {
			$entries[] = $this->sanitizeValues($this->getValuesFromRow($db->fetchByAssoc($result, $j), $exportStructure), $exportStructure);
		}

		if ($isPreview) {
			$this->displayPrewiew($request, $translatedHeaders, $entries);
		} else {
			$this->output($request, $translatedHeaders, $entries);
		}
	}

	function sanitizeValues($arr, $exportStructure){
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$roleid = $currentUser->get('roleid');
		if(empty ($this->fieldArray)){
			$this->fieldArray = $this->moduleFieldInstances;
			foreach($this->fieldArray as $fieldName => $fieldObj){
				//In database we have same column name in two tables. - inventory modules only
				if($fieldObj->get('table') == 'vtiger_inventoryproductrel' && ($fieldName == 'discount_amount' || $fieldName == 'discount_percent')){
					$fieldName = 'item_'.$fieldName;
					$this->fieldArray[$fieldName] = $fieldObj;
				} else {
					$columnName = $fieldObj->get('column');
					$this->fieldArray[$columnName] = $fieldObj;
				}
			}
		}
		$moduleName = $this->moduleInstance->getName();
		foreach($arr as $label=>&$value){
			$value = decode_html($value);
			$fieldName = is_string($exportStructure[$label]) ? $exportStructure[$label] : "";

			if(isset($this->fieldArray[$fieldName])){
				$fieldInfo = $this->fieldArray[$fieldName];
			}else {
				continue;
			}

			$uitype = $fieldInfo->get('uitype');
			$fieldname = $fieldInfo->get('name');

			if(!$this->fieldDataTypeCache[$fieldName]) {
				$this->fieldDataTypeCache[$fieldName] = $fieldInfo->getFieldDataType();
			}
			$type = $this->fieldDataTypeCache[$fieldName];
			
			if($fieldname != 'hdnTaxType' && ($uitype == 15 || $uitype == 16 || $uitype == 33)){
				if(empty($this->picklistValues[$fieldname])){
					$this->picklistValues[$fieldname] = $this->fieldArray[$fieldname]->getPicklistValues();
				}
				// If the value being exported is accessible to current user
				// or the picklist is multiselect type.
				if($uitype == 33 || $uitype == 16 || in_array($value,$this->picklistValues[$fieldname])){
					// NOTE: multipicklist (uitype=33) values will be concatenated with |# delim
					$value = trim($value);
				} else {
					$value = '';
				}
			} elseif($uitype == 52 || $type == 'owner') {
				$value = Vtiger_Util_Helper::getOwnerName($value);
			}elseif($type == 'reference'){
				$value = trim($value);
				if(!empty($value)) {
					$parent_module = getSalesEntityType($value);
					$displayValueArray = getEntityName($parent_module, $value);
					if(!empty($displayValueArray)){
						foreach($displayValueArray as $k=>$v){
							$displayValue = $v;
						}
					}
					if(!empty($parent_module) && !empty($displayValue)){
						$value = $parent_module."::::".$displayValue;
					}else{
						$value = "";
					}
				} else {
					$value = '';
				}
			} elseif($uitype == 72 || $uitype == 71) {
                $value = CurrencyField::convertToUserFormat($value, null, true, true);
			} elseif($uitype == 7 && $fieldInfo->get('typeofdata') == 'N~O' || $uitype == 9){
				$value = decimalFormat($value);
			}
			if($moduleName == 'Documents' && $fieldname == 'description'){
				$value = strip_tags($value);
				$value = str_replace('&nbsp;','',$value);
				array_push($new_arr,$value);
			}
		}

		return $arr;
	}

	function getValuesFromRow($row, $exportStructure) {
		$entry = array();
		
		foreach ($exportStructure as $label => $value) {
			if (is_string($value)) {
				$entry[$label] = $row[$value];
			} else if (is_callable($value)) {
				$entry[$label] = $value($row);
			} else {
				$entry[$label] = "";
			}
		}

		return $entry;
	}

	function displayHeaderLine() {
		return true;
	}

	function getExportStructure() {
		return array(
		);
	}
}