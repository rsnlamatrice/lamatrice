<?php

class RSNStatistics_Module_Model extends Vtiger_Module_Model {

	//AUR_TMP check row in db !!!!!
	public function getRelatedListFields($parentModuleName) {
		$relatedListFields['name'] = 'name';
		$relatedListFields['begin_date'] = 'begin_date';
		$relatedListFields['end_date'] = 'end_date';
		
		$relatedStatsFieldsCode = RSNStatistics_Utils_Helper::getModuleRelatedStatsFieldsCodes($parentModuleName);
		foreach($relatedStatsFieldsCode as $code) {
			$relatedListFields[$code] = $code;
		}

		return $relatedListFields;
	}

	public function getConfigureRelatedListFields(){
		return $this->getRelatedListFields();
	}

	public function getRelationHeaders($parentModuleName){
		$headerFields = array();
		$headerFields['name'] = self::getFieldModelFromName('name');
		$headerFields['begin_date'] = self::getFieldModelFromName('begin_date');
		$headerFields['end_date'] = self::getFieldModelFromName('end_date');
		$headerFields = array_merge($headerFields, $this->getRelatedStatsFieldsModels($parentModuleName));

		return $headerFields;
	}

	public static function getFieldModel($name, $label, $typeOfData, $uiType) {
		$field = new Vtiger_Field_Model();
		$field->set('name', $name);
		$field->set('column', strtolower($name));
		$field->set('label', $label);
		$field->set('typeofdata', $typeOfData);
		$field->set('uitype', $uiType);

		return $field;
	}

	public static function getFieldModelFromName($name) {
		switch ($name) {
		case 'name':
			return self::getFieldModel($name, 'Nom', 'V~M', 1);
		case 'begin_date':
			return self::getFieldModel($name, 'Date debut', 'D~M', 5);
		case 'end_date':
			return self::getFieldModel($name, 'Date fin', 'D~M', 5);
		default:
			$cachedInfo = VTCacheUtils::lookupStatsFieldInfo($name);
			if ($cachedInfo === false) {
				$statField = RSNStatistics_Utils_Helper::getStatFieldFromUniquecode($name);
				if ($statField) {
					return self::getStatFieldModel($statField);
				}
			} else {
				return self::getFieldModel($name, $cachedInfo['fieldname'], $cachedInfo['typeofdata'], $cachedInfo['uitype']);
			}
		}

		return null;
	}

	public static function getStatFieldModel($statField) {
		switch ($statField['fieldtype']) { // AUR_TMP : add all needed type !!
		case 'DATE':
			$typeOfData = 'D~M';
			$uiType = 5;
			break;
		case 'CURRENCY':
			$typeOfData = 'N~M';
			$uiType = 72;
			break;
		default:
			$typeOfData = 'N~M';
			$uiType = 6;
		}

		VTCacheUtils::updateStatsFieldInfo($statField['uniquecode'], $statField['fieldname'], $uiType, $typeOfData);
		return self::getFieldModel($statField['uniquecode'], $statField['fieldname'], $typeOfData, $uiType);
	}

	public function getRelatedStatsFieldsModels($parentModuleName) {
		$fieldModels = array();
		$relatedStatistics = RSNStatistics_Utils_Helper::getRelatedStatistics($parentModuleName);

		foreach ($relatedStatistics as $relatedStatistic) {
			$statId = $relatedStatistic['rsnstatisticsid'];
			$relatedStatFields = RSNStatistics_Utils_Helper::getRelatedStatsFields($statId);//tmp

			foreach ($relatedStatFields as $statField) {
				$fieldModels[$statField['uniquecode']] = self::getStatFieldModel($statField);
			}
		}

		return $fieldModels;
	}
}
?>