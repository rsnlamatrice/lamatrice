<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Vtiger_CompanyDetails_Model extends Settings_Vtiger_Module_Model {

	STATIC $logoSupportedFormats = array('jpeg', 'jpg', 'png', 'gif', 'pjpeg', 'x-png');

	var $baseTable = 'vtiger_organizationdetails';
	var $baseIndex = 'organization_id';
	var $listFields = array('organizationname');
	var $nameFields = array('organizationname');
	var $logoPath = 'test/logo/';
	
	/*ED151104*/
	var $subTable = 'vtiger_organizationsubdetails';

	/*ED151104	Note : $fields are extended in getInstance()*/
	
	var $fields = array(
			'organizationname' => 'text',
			'logoname' => 'text',
			'logo' => 'file',
			'print_logoname' => 'text',
			'print_logo' => 'file',
			'address' => 'textarea',
			'city' => 'text',
			'state' => 'text',
			'code'  => 'text',
			'country' => 'text',
			'phone' => 'text',
			//'rsnprelevements_phone' => 'text',
			'fax' => 'text',
			'website' => 'text',
			/*'lettertoaccount_header_text' => 'text',
			'rsnprelevements_header_text' => 'text',
			'lettertoaccount_lastpage_footer_text' => 'text',
			'inventory_header_text' => 'text',
			'inventory_lastpage_footer_text' => 'text',*/
	);

	/**
	 * Function to get Edit view Url
	 * @return <String> Url
	 */
	public function getEditViewUrl() {
		return 'index.php?module=Vtiger&parent=Settings&view=CompanyDetailsEdit';
	}
	
	/**
	 * Function to get CompanyDetails Menu item
	 * @return menu item Model
	 */
	public function getMenuItem() {
		$menuItem = Settings_Vtiger_MenuItem_Model::getInstance('LBL_COMPANY_DETAILS');
		return $menuItem;
	}
	
	/**
	 * Function to get Index view Url
	 * @return <String> URL
	 */
	public function getIndexViewUrl() {
		$menuItem = $this->getMenuItem();
		return 'index.php?module=Vtiger&parent=Settings&view=CompanyDetails&block='.$menuItem->get('blockid').'&fieldid='.$menuItem->get('fieldid');
	}

	/**
	 * Function to get fields
	 * @return <Array>
	 */
	public function getFields() {
		return $this->fields;
	}

	/**
	 * Function to get Logo path to display
	 * @return <String> path
	 */
	public function getLogoPath($logoPrefix = '') {
		$logoPath = $this->logoPath;
		$handler = @opendir($logoPath);
		$logoName = $this->get($logoPrefix.'logoname');
		if ($logoName && $handler) {
			while ($file = readdir($handler)) {
				if($logoName === $file && in_array(str_replace('.', '', strtolower(substr($file, -4))), self::$logoSupportedFormats) && $file != "." && $file!= "..") {
					closedir($handler);
					return $logoPath.$logoName;
				}
			}
		}
		return '';
	}

	/**
	 * Function to save the logoinfo
	 */
	public function saveLogo($logoPrefix = '') {
		$uploadDir = vglobal('root_directory'). '/' .$this->logoPath;
		$logoName = $uploadDir.$_FILES[$logoPrefix."logo"]["name"];
		move_uploaded_file($_FILES[$logoPrefix."logo"]["tmp_name"], $logoName);
		copy($logoName, $uploadDir.'application.ico');
	}

	/**
	 * Function to save the Company details
	 */
	public function save() {
		$db = PearDatabase::getInstance();
		$id = $this->get('id');
		$fieldsList = $this->getFields();
		unset($fieldsList['logo']);
		unset($fieldsList['print_logo']);
		$tableName = $this->baseTable;
			
		if ($id) {
			$params = array();

			$query = "UPDATE $tableName SET ";
			foreach ($fieldsList as $fieldName => $fieldType){
				if(strpos($fieldName, '::') === false){
					$query .= " $fieldName = ?, ";
					array_push($params, $this->get($fieldName));
				}
			}
			$query .= " logo = NULL WHERE organization_id = ?";
			array_push($params, $id);
			
		} else {
			$allParams = $this->getData();
			$params = array();
			$query = "INSERT INTO $tableName (";
			foreach ($fieldsList as $fieldName => $fieldType){
				if(strpos($fieldName, '::') === false){
					$query .= " $fieldName,";
					array_push($params, $this->get($fieldName));
				}
			}
			$query .= " organization_id)";
			$id = $db->getUniqueID($this->baseTable);
			array_push($params, $id);
			$query .= " VALUES (". generateQuestionMarks($params). ", ?)";
		}
		$db->pquery($query, $params);
		
		/* ED151104*/
		$tableName = $this->subTable;
		$params = array();
		$query = "UPDATE $tableName SET value = CASE ";
		foreach ($fieldsList as $fieldName => $fieldType){
			if(strpos($fieldName, '::') !== false){
				$parts = explode('::', $fieldName);
				$query .= "\r\n WHEN context = ? AND parameter = ? THEN ?";
				array_push($params, $parts[0], $parts[1], $this->get($fieldName));
			}
		}
		$query .= " END WHERE organization_id = ?";
		array_push($params, $id);
		$result = $db->pquery($query, $params);
		if(!$result){
			echo "<pre>$query</pre>";
			var_dump($params);
			$db->echoError();
			die();
		}
	}

	/**
	 * Function to get the instance of Company details module model
	 * @return <Settings_Vtiger_CompanyDetais_Model> $moduleModel
	 */
	public static function getInstance() {
		$moduleModel = new self();
		$db = PearDatabase::getInstance();

		$result = $db->pquery("SELECT * FROM ".$moduleModel->baseTable, array());
		if ($db->num_rows($result) == 1) {
			$moduleModel->setData($db->query_result_rowdata($result));
			$moduleModel->set('id', $moduleModel->get($moduleModel->baseIndex));
		}
		
		/* ED151104 */
		$result = $db->pquery("SELECT * FROM ".$moduleModel->subTable."
							  WHERE ".$moduleModel->baseIndex." = ?
							  ORDER BY sequence, context"
							  , array($moduleModel->get('id')));
		if(!$result)
			$db->echoError();
		$nbRows = $db->num_rows($result);
		for($nRow = 0; $nRow < $nbRows; $nRow++) {
			$param = $db->query_result_rowdata($result, $nRow);
			$fieldName = $param['context'] . '::' . $param['parameter'];
			$moduleModel->set($fieldName, $param['value']);
			if($param['visible'])
				$moduleModel->fields[$fieldName] = self::getUITypeName($param['uitype']);
		}

		return $moduleModel;
	}
	private static function getUITypeName($uitype){
		switch($uitype){
		case 19:
			return 'textarea';
		default:
			return 'text';
		}
	}
}