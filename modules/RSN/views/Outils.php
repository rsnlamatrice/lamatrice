<?php
/*+***********************************************************************************
 * Affiche la page d'un outil
 * La liste est affichée dans le bandeau vertical de gauche,
 * 	initialisée dans Module.php::getSideBarLinks()
 *************************************************************************************/

class RSN_Outils_View extends Vtiger_Index_View {
	
	public function preProcess(Vtiger_Request $request, $display = true) {
		$viewer = $this->getViewer($request);
		$viewer->assign('CURRENT_SUB', $request->get('sub'));
		return parent::preProcess($request, $request);
	}
	
	/* les url doivent contenir le paramètre sub désignant l'outil à afficher */
	public function process(Vtiger_Request $request) {
		$viewer = $this->getViewer($request);
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		
		$sub = $request->get('sub', 'List');
		$viewer->assign('CURRENT_SUB', $sub);
		
		$viewer->assign('CURRENT_USER', $currentUserModel);
		$viewer->view('Outils/' . $sub . '.tpl', $request->getModule());
	}
	
	public function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$jsFileNames = array(
			//"modules.Calendar.resources.SharedCalendarView",
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
}