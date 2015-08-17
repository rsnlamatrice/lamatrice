<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Contacts_SaveAjax_Action extends Vtiger_SaveAjax_Action {

	public function process(Vtiger_Request $request) {
		if($request->get('mode')){
			$func = $request->get('mode');
			$this->$func($request);
			return;
		}
		parent::process($request);
	}
	
	public function saveNPAICriteres(Vtiger_Request $request) {
	
		$result = array();

		foreach($request->get('contacts') as $contactId=>$contactData){
			foreach($contactData as $critereId => $critereData){
				$contact = Vtiger_Record_Model::getInstanceById($contactId, 'Contacts');
				if($critereId == 'NPAI'){
					$contact->set('mode', 'edit');
					$contact->set('rsnnpai', $critereData['value']);
					if(array_key_exists('comment', $critereData))
						$contact->set('rsnnpaicomment', $critereData['comment']);
					$contact->save();
				}
				elseif(is_numeric($critereId)){
					$contact->assignRelatedCritere4D($critereId, $critereData['date'], $critereData['data']);
				}
			}
				
			$result[$contactId] = true;
		}
		$result['contacts'] = str_replace("\n", "<br>", print_r($contactData, true));
		
		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}
}
