<?php

class RSNStatistics_Record_Model extends Vtiger_Record_Model {

	//AUR_TMP (get the right data) //AUR_TMP warning with the real moudle !!
	public function getDisplayValue($fieldName, $recordId = false, $unknown_field_returns_value = false) {//$value, $record=false, $recordInstance = false) {
		if(empty($recordId)) {
			$recordId = $this->getId();
		}

		$fieldModel = RSNStatistics_Module_Model::getFieldModelFromName($fieldName);
		if ($fieldModel != null) {
			$uiTypeInstance = Vtiger_Base_UIType::getInstanceFromField(RSNStatistics_Module_Model::getFieldModelFromName($fieldName));
			return $uiTypeInstance->getDisplayValue($this->get($fieldName), $recordId, $this);
		}

		return parent::getDisplayValue($fieldName, $recordId, $unknown_field_returns_value);
	}

	public static function getStatFieldDetailViewUrl($uniquecode) {
		$fieldId = RSNStatistics_Utils_Helper::getIdFromUniquecode($uniquecode); // tmp: to many sql request...
		return 'index.php?module=RSNStatisticsFields&view=Detail&record=' . $fieldId;
	}
}
