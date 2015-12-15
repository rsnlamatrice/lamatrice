<?php
/*+***********************************************************************************
 * 
 *************************************************************************************/
 
class Invoice_ControleGestionRows_View extends Invoice_GestionVSComptaCARows_View {

	
	public function initFormData(Vtiger_Request $request) {
		
		parent::initFormData($request);
		$viewer = $this->getViewer($request);
		$viewer->assign('FORM_VIEW', 'ControleGestionRows');
		$viewer->assign('TITLE', "Contrôle de gestion");
	}
	
	public function initRowsEntries(Vtiger_Request $request) {
		parent::initRowsEntries($request);
		
		$viewer = $this->getViewer($request);
		
		$viewer->assign('ALL_SOURCES', array('LAM' => 'Gestion'));
	}
	
	public function getCogilogRowsEntries($dateDebut, $dateFin, $compte){
		return array();
	}
	
}