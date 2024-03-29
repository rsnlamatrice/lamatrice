<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Invoice_Module_Model extends Inventory_Module_Model {
	
	/**
	 * Function to get the Quick Links for the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Vtiger_Link_Model instances
	 */
	public function getSideBarLinks($linkParams) {
		$links = parent::getSideBarLinks($linkParams);

		$quickLinks = array(
			array(
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'Contr&ocirc;le par mode de r&egrave;glnt.',
				'linkurl' => 'index.php?module='.$this->get('name').'&view=GestionVSComptaCA',
				'linkicon' => '',
			),
			//array(
			//	'linktype' => 'SIDEBARLINK',
			//	'linklabel' => 'Gestion vs Compta ENC',
			//	'linkurl' => 'index.php?module='.$this->get('name').'&view=GestionVSComptaENC',
			//	'linkicon' => '',
			//),
			array(
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'Contr&ocirc;le des ventes',
				'linkurl' => 'index.php?module='.$this->get('name').'&view=GestionVSCompta',
				'linkicon' => '',
			),
			array(
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'Contr&ocirc;le de TVA',
				'linkurl' => 'index.php?module='.$this->get('name').'&view=GestionVSComptaTVA',
				'linkicon' => '',
			),
			array(
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'Nombre de factures',
				'linkurl' => 'index.php?module='.$this->get('name').'&view=GestionVSComptaNbFact',
				'linkicon' => '',
			),
			array(
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'Contr&ocirc;le de gestion',
				'linkurl' => 'index.php?module='.$this->get('name').'&view=ControleGestion',
				'linkicon' => '',
			),
		);
		foreach($quickLinks as $quickLink) {
			$links['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
		}
		return $links;
	}
	
	/**
	 * Function to delete a given record model of the current module
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function deleteRecord(Vtiger_Record_Model $recordModel) {
		if($recordModel->get('invoicestatus') === 'Compta'
		|| $recordModel->get('invoicestatus') === 'Validated')
			return false;
		return parent::deleteRecord($recordModel);
	}

}