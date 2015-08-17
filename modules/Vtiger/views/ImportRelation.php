<?php
/*+***********************************************************************************
 * ED150814
 *************************************************************************************/

Class Vtiger_ImportRelation_View extends Vtiger_Index_View {
	
	public function process (Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		$relatedModuleName = $request->get('relatedModule');
		$recordId = $request->get('record');
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		
		$viewer->assign('MODULE',$moduleName);
		$viewer->assign('RELATED_MODULE',$relatedModuleName);
		$viewer->assign('RECORD_MODEL',$recordModel);
		$viewer->view($this->getTemplate($moduleName, $relatedModuleName, $request), $moduleName);
	}
	
	public function getTemplate ($moduleName, $relatedModuleName, $request) {
		$root = 'layouts/vlayout/modules';
		
		$tpl = 'ImportRelation'.$relatedModuleName.'.tpl';
		
		$file = "$root/$moduleName/$tpl";
		if(file_exists($file))return $tpl;
		
		$file = "$root/Vtiger/$tpl";
		if(file_exists($file))return $tpl;
		
		$tpl = 'ImportRelation.tpl';
		
		return $tpl;
	}
	
	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);

		$moduleName = $request->getModule();

		$jsFileNames = array(
				'modules.Vtiger.resources.ImportRelation',
				'modules.'.$moduleName.'.resources.ImportRelation',
		);
		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

}
?>
