<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class RSNContactsPanels_RelationAjax_Action extends Vtiger_RelationAjax_Action {
	
	/**
	 * Function to delete the relation for specified source record id and related record id list
	 * @param <array> $request
	 *		keys					Content
	 *		src_module				source module name
	 *		src_record				source record id
	 *		related_module			related module name
	 *		related_record_list		json encoded of list of related record ids
	 */
	function deleteRelation($request) {
		$sourceModule = $request->getModule();
		$sourceRecordId = $request->get('src_record');
		$relatedModule = $request->get('related_module');
		$relatedRecordIdList = $request->get('related_record_list');
		
		if($relatedRecordIdList && $relatedRecordIdList[0])
			parent::deleteRelation($request);
		
		/* Réinitialise les variables d'après la requête */
		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$sourceRecordModel = Vtiger_Record_Model::getInstanceById($sourceRecordId);
		$sourceModuleModel->checkVariables($sourceRecordModel);
		if($request->get('reload_page'))
			if(preg_match('/^http/', $request->get('reload_page')))
				header('Location: '.$request->get('reload_page'));
			else
				header('Location: '.$_SERVER['HTTP_REFERER']);
	}
}
