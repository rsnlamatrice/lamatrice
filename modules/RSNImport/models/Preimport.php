<?php

class RSNImport_Preimport_Model extends Vtiger_Base_Model {
	var $fieldMapping;
	var $user;
	var $module;

	public function  __construct($fieldMapping, $user, $module) {
		$this->fieldMapping = $fieldMapping;
		$this->user = $user;
		$this->module = $module;
	}

	/**
	 * Method get all values of the current row.
	 * @param array : the values.
	 */
	public function getAllValues() {
		return $this->fieldMapping;
	}

	/**
	 * Method get value of an attribute of the current row.
	 * @param string : the value.
	 */
	public function getValue($key) {
		$fieldMapping = $this->fieldMapping;
		return $fieldMapping[$key];
	}

	/**
	 * Method to save current row in the temporary pre-import table.
	 */
	public function save() {
		$db = PearDatabase::getInstance();
		$tableName = RSNImport_Utils_Helper::getDbTableName($this->user, $this->module);
		$data = $this->getAllValues();

		$columnNames = array_keys($data);
		$columnValues = array_values($data);
		if(count($data) > 0) {
			$db->pquery('INSERT INTO '.$tableName.' ('. implode(',',$columnNames).') VALUES ('. generateQuestionMarks($columnValues).')', array($columnValues));
		}
	}
}