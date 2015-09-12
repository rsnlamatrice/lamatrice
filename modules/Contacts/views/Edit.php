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
				if(!array_key_exists('emailoptout',$params))
					$request->set('emailoptout', 1);
				if(!array_key_exists('donotcall',$params))
					$request->set('donotcall', 1);
				if(!array_key_exists('donotappeldoncourrier',$params))
					$request->set('donotappeldoncourrier', 1);
				if(!array_key_exists('donotappeldonweb',$params))
					$request->set('donotappeldonweb', 1);
				if(!array_key_exists('donotappeldonweb',$params))
					$request->set('donotappeldonweb', 1);
				if(!array_key_exists('donotrelanceabo',$params))
					$request->set('donotrelanceabo', 1);
				if(!array_key_exists('donotrelanceadh',$params))
					$request->set('donotrelanceadh', 1);
				if(!array_key_exists('donototherdocuments',$params))
					$request->set('donototherdocuments', 'Reçu fiscal seul');
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