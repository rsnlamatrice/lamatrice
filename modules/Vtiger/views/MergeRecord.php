<?php
/**************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 **************************************************************************************/

class Vtiger_MergeRecord_View extends Vtiger_Popup_View {
	
	

	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			'modules.Vtiger.resources.MergeRecord', 
			"modules.$moduleName.resources.MergeRecord", /* chargé grâce ˆ #popUpClassName (cf .tpl) */
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
	
	
	function process(Vtiger_Request $request) {
		$records = $request->get('records');
		$records = explode(',', $records);
		$module = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($module);
		$blockModels =  $this->getFieldsByBlocks($moduleModel);//$moduleModel->getMergeableFields();//ED150910 getMergeableFields instead of getFields
		$focus = CRMEntity::getInstance($module);
		if($module === 'Contacts')
			$accountFocus = CRMEntity::getInstance('Accounts');
				
		$relatedModules = array();
		
		foreach($records as $record) {
			$recordModel = Vtiger_Record_Model::getInstanceById($record);
			$recordModels[] = $recordModel;
			if (method_exists($focus, 'countTransferRelatedRecords')) {
				$relatedModulesCounter = $focus->countTransferRelatedRecords($module, array($record), array(0));
				foreach($relatedModulesCounter as $relatedModule => $counter){
					$relatedModules[$relatedModule] = $relatedModule;
					$recordModel->set('_related_module_'.$relatedModule, $counter);
				}
				if($module === 'Contacts' && $recordModel->get('account_id')){
					$relatedModulesCounter = $accountFocus->countTransferRelatedRecords($module, array($recordModel->get('account_id')), array(0));
					foreach($relatedModulesCounter as $relatedModule => $counter){
						$relatedModules[$relatedModule] = $relatedModule;
						$recordModel->set('_related_module_'.$relatedModule, $counter);
					}
				}
			}
			$recordModel->set('url', $moduleModel->getDetailViewUrl($recordModel->getId()));
		}
		$viewer = $this->getViewer($request);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('RECORDS', $records);
		$viewer->assign('RECORDMODELS', $recordModels);
		$viewer->assign('RELATED_MODULES', $relatedModules);
		//$viewer->assign('FIELDS', $fieldModels);
		$viewer->assign('BLOCKS', $blockModels);
		$viewer->assign('MODULE', $module);
		$viewer->assign('MODULE_NAME', $module);
		$viewer->view('MergeRecords.tpl', $module);
	}
	
	protected function getFieldsByBlocks($moduleModel){
		$blockList = $moduleModel->getBlocks();
		$fieldModels =  $moduleModel->getMergeableFields();//ED150910 getMergeableFields instead of getFields
		$blockModels = array();
		//reference par id
		foreach($blockList as $blockModel){
			$blockModel->set('fields', array());
			$blockModels[$blockModel->get('id')] = $blockModel;
		}
		//add editable fields to blocks
		foreach($fieldModels as $fieldModel)
			if($fieldModel->isEditable()){
				$blockModel = $blockModels[$fieldModel->block->id];
				$blockModel->fields[$fieldModel->getId()] = $fieldModel;
			}
		//remove empty blocks
		foreach($blockModels as $id => $blockModel){
			if(!$blockModel->fields){
				unset($blockModels[$id]);
			}
		}
		return $blockModels;
	}
}
