<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Contacts_Save_Action extends Vtiger_Save_Action {

	public function process(Vtiger_Request $request) {
		$result = Vtiger_Util_Helper::transformUploadedFiles($_FILES, true);
		$_FILES = $result['imagename'];

		//To stop saving the value of salutation as '--None--'
		$salutationType = $request->get('salutationtype');
		if ($salutationType === '--None--') {
			$request->set('salutationtype', '');
		}
		
		if( $request->get('record') && !$request->get('isDuplicate') ){
			$contactModule = Vtiger_Module_Model::getInstance('Contacts');
			//"old" means "before saved"
			$contactOldRecord = Vtiger_Record_Model::getInstanceById($request->get('record'), $contactModule);
			
			if($contactOldRecord->get('rsnnpai') != $request->get('rsnnpai')
			&& !(!$contactOldRecord->get('rsnnpai') && !$request->get('rsnnpai'))){
				$request->set('rsnnpaidate', date('Y-m-d'));
			}
			
			//ED150312
			//duplicate address data to a new ContactAddresses record
			if($request->get('_archive_address') && $request->get('record')){
				$contactOldRecord->createContactAddressesRecord('mailing', true, $request);
			}
		}
		
		parent::process($request);
	}
}
