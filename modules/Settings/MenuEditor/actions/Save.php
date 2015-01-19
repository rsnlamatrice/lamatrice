<?php
/*+***********************************************************************************
 * ED141226
 *************************************************************************************/

class Settings_MenuEditor_Save_Action extends Settings_Vtiger_Index_Action {

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule(false);
		$menuEditorModuleModel = Settings_Vtiger_Module_Model::getInstance($moduleName);
		$selectedModulesList = $request->get('selectedModulesList');

		if ($selectedModulesList) {
			if($request->get('roleid'))
				$menuEditorModuleModel->set('roleid', $request->get('roleid'));
			$menuEditorModuleModel->set('selectedModulesList', $selectedModulesList);
			$menuEditorModuleModel->saveMenuStruncture();
		}
		$loadUrl = $menuEditorModuleModel->getIndexViewUrl();
		if($request->get('roleid'))
			$loadUrl .= '&roleid=' . $request->get('roleid');
		header("Location: $loadUrl");
	}

}
