<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Inventory_RelationAjax_Action extends Vtiger_RelationAjax_Action {
	

	/*
	 * Function to add relation for specified source record id and related record id list
	 * @param <array> $request
	 *		keys					Content
	 *		src_module				source module name
	 *		src_record				source record id
	 *		related_module			related module name
	 *		related_record_list		json encoded of list of related record ids
	 */
	function addRelation($request) {
		$relatedModule = $request->get('related_module');
		
		parent::addRelation($request);
		
		if($relatedModule === 'RsnReglements'){
			$sourceModule = $request->getModule();
			$sourceRecordId = $request->get('src_record');
			//Mise ˆ jour de l'encaissement de la facture
			$sourceRecordModel = Vtiger_Record_Model::getInstanceById($sourceRecordId, $sourceModule);
			$result = $sourceRecordModel->updateReceivedFromRelated();
			if($result){
				
				$response = new Vtiger_Response();
				$response->setResult($result);
				$response->emit();
			}
		}
	}
}
