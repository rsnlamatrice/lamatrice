<?php

/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Settings_Picklist_Index_View extends Settings_Vtiger_Index_View {
    
    public function process(Vtiger_Request $request) {
        
        $sourceModule = $request->get('source_module');
        $pickListSupportedModules = Settings_Picklist_Module_Model::getPicklistSupportedModules();
        if(empty($sourceModule)) {
            //take the first module as the source module
            $sourceModule = $pickListSupportedModules[0]->name;
        }
        $moduleModel = Settings_Picklist_Module_Model::getInstance($sourceModule);
        $viewer = $this->getViewer($request);
        $qualifiedName = $request->getModule(FALSE);
        
        $viewer->assign('PICKLIST_MODULES',$pickListSupportedModules);
        
        //TODO: see if you needs to optimize this , since its will gets all the fields and filter picklist fields
        $pickListFields = $moduleModel->getFieldsByType(array('picklist','multipicklist'));
        if(count($pickListFields) > 0) {
            $selectedPickListFieldModel = reset($pickListFields);

            /* ED141127
			* Ajout des données supplémentaires (uicolor, uiicon, ...)
			*/
			$selectedFieldAllPickListData = array();
			$selectedFieldAllPickListValues = Vtiger_Util_Helper::getPickListValues($selectedPickListFieldModel->getName(), $selectedFieldAllPickListData);
			$viewer->assign('PICKLIST_FIELDS',$pickListFields);
			$viewer->assign('SELECTED_PICKLIST_FIELDMODEL',$selectedPickListFieldModel);
			$viewer->assign('SELECTED_PICKLISTFIELD_ALL_VALUES',$selectedFieldAllPickListValues);
			$viewer->assign('SELECTED_PICKLISTFIELD_ALL_DATA',$selectedFieldAllPickListData);
			
			$properties = getPicklistProperties($selectedPickListFieldModel);
			if($properties){
			   $viewer->assign('PROPERTIES_UICOLOR', $properties["uicolor"]);
			   $viewer->assign('PROPERTIES_UIICON', $properties["uiicon"]);
			}
			
			$settingFieldModels = $selectedPickListFieldModel->getSettingFieldModels($sourceModule);
			if($settingFieldModels){
			   //var_dump($settingFieldModels);
			   $viewer->assign('SETTING_TABLE_FIELDS_MODELS', $settingFieldModels);
			}
	    
            $viewer->assign('ROLES_LIST', Settings_Roles_Record_Model::getAll());
        }else{
            $viewer->assign('NO_PICKLIST_FIELDS',true);
            $createPicklistUrl = '';
            $settingsLinks = $moduleModel->getSettingLinks();
            foreach($settingsLinks as $linkDetails) {
                if($linkDetails['linklabel'] == 'LBL_EDIT_FIELDS') {
                    $createPicklistUrl = $linkDetails['linkurl'];
                    break;
                }
            }
            $viewer->assign('CREATE_PICKLIST_URL',$createPicklistUrl);
                
        }
        $viewer->assign('SELECTED_MODULE_NAME', $sourceModule);
        $viewer->assign('QUALIFIED_NAME',$qualifiedName);
        
		$viewer->view('Index.tpl',$qualifiedName);
    }
	
	function getHeaderCSS(Vtiger_Request $request) {
		$headerCssInstances = parent::getHeaderCss($request);
		$moduleName = $request->getModule();

		$cssFileNames = array(
			"~/layouts/vlayout/modules/Settings/$moduleName/resources/$moduleName.css",
		);
		$cssInstances = $this->checkAndConvertCssStyles($cssFileNames);
		$headerCssInstances = array_merge($headerCssInstances, $cssInstances);
		
		return $headerCssInstances;
	}
	
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			"modules.$moduleName.resources.$moduleName",
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}