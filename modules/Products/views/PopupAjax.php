<?php
/*+**********************************************************************************
 * ED151130
 ************************************************************************************/

class Products_PopupAjax_View extends Vtiger_PopupAjax_View {
	

	public function process (Vtiger_Request $request) {
		$mode = $request->get('mode');
		
		//ED150922 : si on cherche par code alors qu'on est positionné en recherche par nom, change le champ de recherche
		$search_key = $request->get('search_key');
		$search_value = $request->get('search_value');
		$operator = $request->get('operator');
		if((empty($mode) || $mode == 'getPageCount')){
			if(is_string($search_key)){
				$search_key = array($search_key);
				$search_value = array($search_value);
				$operator = array($operator);
			}
			if(is_array($search_key) && count($search_key) === 1 && ($search_key[0] == 'servicename' || $search_key[0] == 'productname')){
				$search_key = array($search_key[0], null, 'productcode');
				$search_value = array($search_value[0], null, $search_value[0]);
				$operator = array($operator[0], 'OR', $operator[0]);
			}
			$request->set('search_key', $search_key);
			$request->set('search_value', $search_value);
			$request->set('operator', $operator);
		}
		//var_dump($request);
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
		$viewer = $this->getViewer ($request);
		$moduleName = $request->getModule();
		$this->initializeListViewContents($request, $viewer);

		echo $viewer->view('PopupContents.tpl', $moduleName, true);
	}
}