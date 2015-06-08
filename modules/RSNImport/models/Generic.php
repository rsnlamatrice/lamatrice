<?php

class RSNImport_Generic_Model extends Vtiger_Base_Model { // TMP NAME !!!

	//static $tableName = 'vtiger_import_maps';
	var $fieldMapping;//tmp $map !!
	var $user;
	var $module;

	public function  __construct($fieldMapping, $user, $module) {
		$this->fieldMapping = $fieldMapping;
		$this->user = $user;
		$this->module = $module;
	}

	/*public static function getInstanceFromDb($row, $user) {
		$map = array();
		foreach($row as $key=>$value) {
			if($key == 'content') {
				$content = array();
				$pairs = explode("&", $value);
				foreach($pairs as $pair) {
					list($mappedName, $sequence) = explode("=", $pair);
					$mappedName = str_replace('/eq/', '=', $mappedName);
					$mappedName = str_replace('/amp/', '&', $mappedName);
					$content["$mappedName"] = $sequence;
				}
				$map[$key] = $content;

			} else {
				$map[$key] = $value;
			}
		}
		return new Import_Map_Model($map, $user);
	}*/

	/*public static function markAsDeleted($mapId) {
		$db = PearDatabase::getInstance();
		$db->pquery('UPDATE vtiger_import_maps SET deleted=1 WHERE id=?', array($mapId));
	}*/

	/*public function getId() {
		$map = $this->map;
		return $map['id'];
	}*/

	public function getAllValues() {
		return $this->fieldMapping;
	}

	public function getValue($key) {
		$fieldMapping = $this->fieldMapping;
		return $fieldMapping[$key];
	}

	public function getStringifiedContent() {
		/*if(empty($this->map['content'])) return;
		$content = $this->map['content'];
		$keyValueStrings = array();
		foreach($content as $key => $value) {
			$key = str_replace('=', '/eq/', $key);
			$key = str_replace('&', '/amp/', $key);
			$keyValueStrings[] = $key.'='.$value;
		}
		$stringifiedContent = implode('&', $keyValueStrings);
		return $stringifiedContent;*/
		return '';
	}

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