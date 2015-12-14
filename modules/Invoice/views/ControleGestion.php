<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
 
class Invoice_ControleGestion_View extends Invoice_GestionVSComptaCA_View {

	
	public function initFormData(Vtiger_Request $request) {
		parent::initFormData($request);
		$viewer = $this->getViewer($request);
		$viewer->assign('FORM_VIEW', 'ControleGestion');
		$viewer->assign('ROWS_URL', 'index.php?module='.$request->get('module').'&view=ControleGestionRows');
		$viewer->assign('TITLE', "Contrôle de gestion");
	}
	
	public function initComptesEntries(Vtiger_Request $request) {
		parent::initComptesEntries($request);
		
		$viewer = $this->getViewer($request);
		
		$viewer->assign('ALL_SOURCES', array('LAM' => 'Gestion'));
	}
	
	public function getCogilogComptesEntries($dateDebut, $dateFin){
		return array();
	}
	
}