<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Invoice_ListView_Model extends Inventory_ListView_Model {
	

	/**
	 * Function to get the list of Mass actions for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associative array of Link type to List of  Vtiger_Link_Model instances for Mass Actions
	 */
	public function getListViewMassActions($linkParams) {
		$moduleModel = $this->getModule();

		$links = parent::getListViewMassActions($linkParams);
		
		//Suppression du menu Modifier en masse
		foreach($links['LISTVIEWMASSACTION'] as $index => $massActionLink){
			if($massActionLink->get('linklabel') === 'LBL_EDIT'){
				unset($links['LISTVIEWMASSACTION'][$index]);
				break;
			}
		}
		//Ajout du menu Valider les en cours
		$massActionLink = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'Valider les "En cours"',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerMassUpdate("index.php?module='.$moduleModel->get('name').'&action=MassSave&mode=enCoursStatusToValidated");',
				'linkicon' => ''
			);
		array_unshift($links['LISTVIEWMASSACTION'], Vtiger_Link_Model::getInstanceFromValues($massActionLink));

		return $links;
	}
}