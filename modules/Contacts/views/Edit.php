<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Contacts_Edit_View extends Vtiger_Edit_View {

	public function process(Vtiger_Request $request) {
		
		$moduleName = $request->getModule();
		$recordId = $request->get('record');
		$recordModel = $this->record;
		if(!$recordModel){
		   if (!empty($recordId)) {
		       $recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			if($request->get('isDuplicate') == true)
				$recordModel->set('reference', '0');
		   } else {
		       $recordModel = Vtiger_Record_Model::getCleanInstance($moduleName);
		   }
		    $this->record = $recordModel;
		}

		/* ED150912
		 * Ne pas par défaut
		*/
		if(!$recordId){
			$params = $request->getAll();
			if($params['donotprospect']){
				$defaultValues = array(
					'emailoptout' => 1,
					'donotcall' => 1,
					'donotappeldoncourrier' => 1,
					'donotappeldonweb' => 1,
					'donotrelanceabo' => 1,
					'donotrelanceadh' => 1,
					'donototherdocuments' => 'Reçu fiscal seul',
				);
				foreach($defaultValues as $fieldName => $fieldValue)
					if(!array_key_exists($fieldName, $params))
						$request->set($fieldName, $fieldValue);
			}
		}
		
		
		$viewer = $this->getViewer($request);
		$viewer->assign('IMAGE_DETAILS', $recordModel->getImageDetails());

		$salutationFieldModel = Vtiger_Field_Model::getInstance('salutationtype', $recordModel->getModule());
		$salutationFieldModel->set('fieldvalue', $recordModel->get('salutationtype'));
		$viewer->assign('SALUTATION_FIELD_MODEL', $salutationFieldModel);

		parent::process($request);
	}

}