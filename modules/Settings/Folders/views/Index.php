<?php
/*+**********************************************************************************
 * ED141226
 ************************************************************************************/

class Settings_Folders_Index_View extends Settings_Vtiger_Index_View {

	public function process(Vtiger_Request $request) {
		
		$allModelsList = Documents_Folder_Model::getAllWithUseCount(true);
		$moduleName = $request->getModule();
		$qualifiedModuleName = $request->getModule(false);
		
		$viewer = $this->getViewer($request);
		$viewer->assign('ALL_FOLDERS', $allModelsList);
		$viewer->assign('MODULE_NAME', $moduleName);
		
		$viewer->view('Index.tpl', $qualifiedModuleName);
	}
}
