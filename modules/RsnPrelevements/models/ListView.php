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
 * Vtiger ListView Model Class
 */
class RsnPrelevements_ListView_Model extends Vtiger_ListView_Model {

	/*
	 * Function to give advance links of a module
	 *	@RETURN array of advanced links
	 */
	public function getAdvancedLinks(){
		$moduleModel = $this->getModule();
		$createPermission = Users_Privileges_Model::isPermitted($moduleModel->getName(), 'EditView');
		$advancedLinks = parent::getAdvancedLinks();
		if($createPermission) {
			$advancedLinks[] = array(
							'linktype' => 'LISTVIEW',
							'linklabel' => '---',
							'linkurl' => '' ,
							'linkicon' => ''
			);
			$advancedLinks[] = array(
							'linktype' => 'LISTVIEW',
							'linklabel' => 'Générer les prélèvements du mois',
							'linkurl' => $moduleModel->getGenererPrelVirementsUrl() ,
							'linkicon' => ''
			);
		}

		return $advancedLinks;
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
			$query .= ', sum(montant) AS `total`';
			
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
