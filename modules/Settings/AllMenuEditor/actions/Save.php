<?php
/*+***********************************************************************************
 * ED141226
 *************************************************************************************/

class Settings_AllMenuEditor_Save_Action extends Settings_Vtiger_Index_Action {

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule(false);
		$menuEditorModuleModel = Settings_Vtiger_Module_Model::getInstance($moduleName);
		$itemsPositions = $request->get('itemsPositions');

		if ($itemsPositions) {
			$menuEditorModuleModel->set('itemsPositions', $itemsPositions);
			$menuEditorModuleModel->saveMenuStructure();
		}
		$loadUrl = $menuEditorModuleModel->getIndexViewUrl();
		header("Location: $loadUrl");
	}

}
