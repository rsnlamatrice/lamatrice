<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Inventory ListView Model Class
 */
class Inventory_ListView_Model extends Vtiger_ListView_Model {
	
	/**
	 * Function to get the list view header
	 * @return <Array> - List of Vtiger_Field_Model instances
	 *
	 * ED140926
	 */
	public function getListViewHeaders() {
		$headerFieldModels = parent::getListViewHeaders();
		if(isset($headerFieldModels['productid']))
			$headerFieldModels['productid']->label = 'LBL_PRODUCT_NAME';
		//var_dump($headerFieldModels);
		return $headerFieldModels;
	}
	
	/**
	 * Function to get the list of listview links for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associate array of Link Type to List of Vtiger_Link_Model instances
	 */
	public function getListViewLinks($linkParams) {
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$moduleModel = $this->getModule();

		$linkTypes = array('LISTVIEWBASIC', 'LISTVIEW', 'LISTVIEWSETTING');
		$links = Vtiger_Link_Model::getAllByType($moduleModel->getId(), $linkTypes, $linkParams);

		$basicLinks = array();

		$createPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'EditView');
		if($createPermission) {
			$basicLinks[] = array(
					'linktype' => 'LISTVIEWBASIC',
					'linklabel' => 'LBL_ADD_RECORD',
					'linkurl' => 'javascript:Vtiger_List_Js.triggerAddRecord(event, "'.$moduleModel->getCreateRecordUrl().'")',
					'linkicon' => ''
			);
		}

		$exportPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'Export');
		if($exportPermission) {
			$advancedLinks[] = array(
					'linktype' => 'LISTVIEW',
					'linklabel' => 'LBL_EXPORT',
					'linkurl' => 'javascript:Vtiger_List_Js.triggerExportAction("'.$this->getModule()->getExportUrl().'")',
					'linkicon' => ''
				);
		}

		foreach($basicLinks as $basicLink) {
			$links['LISTVIEWBASIC'][] = Vtiger_Link_Model::getInstanceFromValues($basicLink);
		}

		$advancedLinks = $this->getAdvancedLinks();
		foreach($advancedLinks as $advancedLink) {
			$links['LISTVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($advancedLink);
		}

		if($currentUserModel->isAdminUser()) {
			$settingsLinks = $this->getSettingLinks();
			foreach($settingsLinks as $settingsLink) {
				$links['LISTVIEWSETTING'][] = Vtiger_Link_Model::getInstanceFromValues($settingsLink);
			}
		}
		return $links;
	}
	
	/*
	 * Function to give advance links of a module
	 *	@RETURN array of advanced links
	 */
	public function getAdvancedLinks(){
		return parent::getAdvancedLinks();
	}

	/** ED150928
	 * Function to get the list of Mass actions for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associative array of Link type to List of  Vtiger_Link_Model instances for Mass Actions
	 */
	public function getListViewMassActions($linkParams) {
	
		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$moduleModel = $this->getModule();

		$links = parent::getListViewMassActions($linkParams);

		$massActionLinks = array();
		if($currentUserModel->hasModuleActionPermission($moduleModel->getId(), 'EditView')) {
			$massActionLinks[] = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_SEND2COMPTA',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerMassEdit("index.php?module='.$moduleModel->get('name').'&view=Send2Compta&mode=showSend2ComptaForm");',
				'linkicon' => ''
			);
		}

		foreach($massActionLinks as $massActionLink) {
			$links['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}

		return $links;
	}


	/* ED150417
	 * add sql filter to get only first product of invoice
	 * TODO : attention, se base sur le présupposé qu'il y a toujours un product de la facture avec sequence_no = 1
	 */
	function getQuery() {
		$listQuery = parent::getQuery();
		if(stripos($listQuery, 'JOIN vtiger_inventoryproductrel') !== FALSE){
			//TODO Fait ramer les requêtes
			//$table = 'SELECT * FROM vtiger_inventoryproductrel WHERE (vtiger_inventoryproductrel.sequence_no IS NULL OR vtiger_inventoryproductrel.sequence_no = 1)';
			//$listQuery = preg_replace('/(RIGHT|FULL|INNER|(?<!LEFT))\sJOIN vtiger_inventoryproductrel/i', ' LEFT JOIN ('.$table.') vtiger_inventoryproductrel ', $listQuery);
			//Jointure externe
			$listQuery = preg_replace('/(RIGHT|FULL|INNER|(?<!LEFT))\sJOIN vtiger_inventoryproductrel/i', ' LEFT JOIN vtiger_inventoryproductrel ', $listQuery);
			//Limitation à la seule 1ère ligne de chaque facture
			$listQuery .= ' AND (vtiger_inventoryproductrel.sequence_no IS NULL OR vtiger_inventoryproductrel.sequence_no = 1)';
		}
		//var_dump($listQuery);
		//print_r("<pre>$listQuery</pre>");
		// add currency_id if total is queried
		if(strpos($listQuery, 'vtiger_invoice.total') !== FALSE
		&& strpos($listQuery, 'vtiger_invoice.currency_id') === FALSE){
			$listQuery = preg_replace('/^\s*SELECT\s/i', 'SELECT vtiger_invoice.currency_id,', $listQuery);
			
		}
		return $listQuery;
	}


	/**
	 * Function to get the list view entries
	 * @param Vtiger_Paging_Model $pagingModel
	 * @return <Array> - Associative array of record id mapped to Vtiger_Record_Model instance.
	 */
	public function getListViewCount(&$calculatedTotals = false) {
		$db = PearDatabase::getInstance();

		$queryGenerator = $this->get('query_generator');

		$searchKey = $this->get('search_key');
		$searchValue = $this->get('search_value');
		$operator = $this->get('operator');
		if(!empty($searchKey)) {
			$queryGenerator->addUserSearchConditions(array('search_field' => $searchKey, 'search_text' => $searchValue, 'operator' => $operator));
		}

		//ED150928
		if($calculatedTotals !== false){
			$queryGenerator->addField('total');
		}
		
		$listQuery = $this->getQuery();

		$sourceModule = $this->get('src_module');
		if(!empty($sourceModule)) {
			$moduleModel = $this->getModule();
			if(method_exists($moduleModel, 'getQueryByModuleField')) {
				$overrideQuery = $moduleModel->getQueryByModuleField($sourceModule, $this->get('src_field'), $this->get('src_record'), $listQuery);
				if(!empty($overrideQuery)) {
					$listQuery = $overrideQuery;
				}
			}
		}
//echo "<pre>$listQuery</pre>";

		//ED150507 : cou
		$query = 'SELECT count(*) AS count';
		
		//ED150906
		if($calculatedTotals !== false)
			$query .= ', sum(total) AS `total`';
			
		$query .= ' FROM (' . $listQuery . ') q';
		
		$listResult = $db->pquery($query, array());
		if(!$listResult){
			$db->echoError('Impossible de compter le nombre de lignes.');
			echo '<pre>'; print_r($query); echo '</pre>'; 
			return 0;
		}
		
		if($calculatedTotals !== false)
			$calculatedTotals = array(
				'total' => $db->query_result($listResult, 0, 'total')
			);
		
		return $db->query_result($listResult, 0, 'count');
	}
}