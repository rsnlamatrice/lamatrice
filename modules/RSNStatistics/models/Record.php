<?php

class RSNStatistics_Record_Model extends Vtiger_Record_Model {

	public function getResultsModuleNames(){
		return explode(' |##| ', $this->get('relmodule'));
	}

	public function getUpdateValuesUrl($crmid = '*', $begin_date = false){
		return $this->getModule()->getUpdateValuesUrl($crmid, $this->get('relmodule'), $this->getId(), $begin_date);
	}
	
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
	
	/**
	 * Contrôles que les champs sont correctement paramétrés par rapport à leur requête
	 *
	 */
	public function getErrors(){
		$errors = array();
		$statisticFields = RSNStatistics_Utils_Helper::getRelatedStatsFieldsRecordModels($this->getId());// tmp get fileds of oll related stats !!
		$existingTableFieldsDefinition = $this->getExistingTableFieldsDefinition();
		foreach($statisticFields as $statisticField){
			$statisticField->set('parent_RSNStatistics', $this);
			$error = $statisticField->getErrors();
			if($error)
				$errors[] = $error;
			if( ! array_key_exists($statisticField->get('uniquecode'), $existingTableFieldsDefinition) ){
				$errors[] = 'Le champ `'.$statisticField->get('uniquecode').'` n\'existe pas dans la table de cette statistique "'.$this->getName().'".'
					.($existingTableFieldsDefinition ? ' Valeurs possibles : '. implode(', ', array_keys($existingTableFieldsDefinition)) : '').'.'
					. ' Eventuellement, éditez et ré-enregistrez la statistique pour recréer les champs.';
			}
		}
		return $errors;
	}
		
	/**
	 * Returns the fields in this statistic values table
	 *
	 */
	public function getExistingTableFieldsDefinition(){
		
		$statTableName = RSNStatistics_Utils_Helper::getStatsTableName($this->getId(), $this);
		
		$fields = array();
		$excludeFields = array('id', 'crmid', 'name', 'code', 'begin_date', 'end_date', 'last_update');
		$params = array();
		$sql = "SELECT * FROM $statTableName LIMIT 0";
		$db = PearDatabase::getInstance();
		$result = $db->query($sql);
		if(!$result){
			echo "<pre>$sql</pre>";
			$db->echoError();
		}
		else{
			foreach($db->getFieldsDefinition($result) as $field){
				if(!in_array($field->name, $excludeFields)){
					$fields[$field->name] = $field;
				}
			}
		}
		return $fields;
	}
}
