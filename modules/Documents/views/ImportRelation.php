<?php
/*+***********************************************************************************
 * ED150814
 *************************************************************************************/

Class Documents_ImportRelation_View extends Vtiger_ImportRelation_View {
	
	public function process (Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		
		parent::process($request);
	}

}
?>