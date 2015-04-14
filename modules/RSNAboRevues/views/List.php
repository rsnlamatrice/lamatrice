<?php
/*+**********************************************************************************
 * ED150414
 ************************************************************************************/

class RSNAboRevues_List_View extends Vtiger_List_View {
	
	/*
	 * Function to initialize the required data in smarty to display the List View Contents
	 */
	public function initializeListViewContents(Vtiger_Request $request, Vtiger_Viewer $viewer) {
		if(!$request->get('orderby')){
			//default order
			$request->set('orderby', 'debutabo');
			$request->set('sortorder', 'DESC');
		}
		return parent::initializeListViewContents($request, $viewer);
	}
}