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
class RsnReglements_ListView_Model extends Vtiger_ListView_Model {

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
			$queryGenerator->addField('amount');
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
			$query .= ', sum(amount) AS `total`';
			
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