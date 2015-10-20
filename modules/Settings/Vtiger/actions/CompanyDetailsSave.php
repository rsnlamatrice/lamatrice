<?php

/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ********************************************************************************** */

class Settings_Vtiger_CompanyDetailsSave_Action extends Settings_Vtiger_Basic_Action {

	public function process(Vtiger_Request $request) {
		$qualifiedModuleName = $request->getModule(false);
		$moduleModel = Settings_Vtiger_CompanyDetails_Model::getInstance();
		$status = false;

        if ($request->get('organizationname')) {
            $saveLogo = $status = true;
			$saveLogoError = false;
			$logosDetails = array('' => false, 'print_' => false);
			foreach(array_keys($logosDetails) as $logoPrefix){
				if(!empty($_FILES[$logoPrefix.'logo']['name'])) {
					$logoDetails = $_FILES[$logoPrefix.'logo'];
					$fileType = explode('/', $logoDetails['type']);
					$fileType = $fileType[1];
	
					if (!$logoDetails['size'] || !in_array($fileType, Settings_Vtiger_CompanyDetails_Model::$logoSupportedFormats)) {
						$saveLogo = false;
						$saveLogoError = true;
					}
					// Check for php code injection
					$imageContents = file_get_contents($_FILES[$logoPrefix."logo"]["tmp_name"]);
					if (preg_match('/(<\?php?(.*?))/i', $imageContents) == 1) {
						$saveLogo = false;
						$saveLogoError = true;
					}
					if ($saveLogo) {
						$moduleModel->saveLogo($logoPrefix);
					}
				}else{
					$saveLogo = true;
				}
				if($saveLogo)
					$logosDetails[$logoPrefix] = $logoDetails;
			}
			$fields = $moduleModel->getFields();
			foreach ($fields as $fieldName => $fieldType) {
				$fieldValue = $request->get($fieldName);
				if (preg_match('/logoname$/', $fieldName)) {
					$logoPrefix = substr($fieldName, 0, strlen($fieldName)-strlen('logoname'));
					if (!empty($logosDetails[$logoPrefix]['name'])) {
						$fieldValue = ltrim(basename(" " . $logosDetails[$logoPrefix]['name']));
					} else {
						$fieldValue = $moduleModel->get($fieldName);
					}
				}
				$moduleModel->set($fieldName, $fieldValue);
			}
			$moduleModel->save();
		}

		$reloadUrl = $moduleModel->getIndexViewUrl();
		if (!$saveLogoError && $status) {

		} else if ($saveLogoError) {
			$reloadUrl .= '&error=LBL_INVALID_IMAGE';
		} else {
			$reloadUrl = $moduleModel->getEditViewUrl() . '&error=LBL_FIELDS_INFO_IS_EMPTY';
		}
		header('Location: ' . $reloadUrl);
	}

}