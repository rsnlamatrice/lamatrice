<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Products_Module_Model extends Vtiger_Module_Model {

	/**
	 * Function to get list view query for popup window
	 * @param <String> $sourceModule Parent module
	 * @param <String> $field parent fieldname
	 * @param <Integer> $record parent id
	 * @param <String> $listQuery
	 * @return <String> Listview Query
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, $listQuery) {
		$supportedModulesList = array($this->getName(), 'Vendors', 'Leads', 'Accounts', 'Contacts', 'Potentials');
		if (($sourceModule == 'PriceBooks' && $field == 'priceBookRelatedList')
				|| in_array($sourceModule, $supportedModulesList)
				|| in_array($sourceModule, getInventoryModules())) {

			$condition = " vtiger_products.discontinued = 1 ";
			if ($sourceModule === $this->getName()) {
				$condition .= " AND vtiger_products.productid NOT IN (SELECT productid FROM vtiger_seproductsrel UNION SELECT crmid FROM vtiger_seproductsrel WHERE productid = '$record')  AND vtiger_products.productid <> '$record' ";
			} elseif ($sourceModule === 'PriceBooks') {
				$condition .= " AND vtiger_products.productid NOT IN (SELECT productid FROM vtiger_pricebookproductrel WHERE pricebookid = '$record') ";
			} elseif ($sourceModule === 'Vendors') {
				$condition .= " AND vtiger_products.vendor_id != '$record' ";
			} elseif (in_array($sourceModule, $supportedModulesList)) {
				$condition .= " AND vtiger_products.productid NOT IN (SELECT productid FROM vtiger_seproductsrel WHERE crmid = '$record')";
			}

			$pos = stripos($listQuery, 'where');
			if ($pos) {
				$split = spliti('where', $listQuery);
				$overRideQuery = $split[0] . ' WHERE ' . $split[1] . ' AND ' . $condition;
			} else {
				$overRideQuery = $listQuery. ' WHERE ' . $condition;
			}
			return $overRideQuery;
		}
	}

	/**
	 * Function to get Specific Relation Query for this Module
	 * @param <type> $relatedModule
	 * @return <type>
	 */
	public function getSpecificRelationQuery($relatedModule) {
		if ($relatedModule === 'Leads') {
			$specificQuery = 'AND vtiger_leaddetails.converted = 0';
			return $specificQuery;
		}
		return parent::getSpecificRelationQuery($relatedModule);
 	}

	/**
	 * Function to get prices for specified products with specific currency
	 * @param <Integer> $currenctId
	 * @param <Array> $productIdsList
	 * @return <Array>
	 */
	public function getPricesForProducts($currencyId, $productIdsList) {
		return getPricesForProducts($currencyId, $productIdsList, $this->getName());
	}
	
	/**
	 * Function to check whether the module is summary view supported
	 * @return <Boolean> - true/false
	 */
	public function isSummaryViewSupported() {
		return false;
	}
	
	/**
	 * Function searches the records in the module, if parentId & parentModule
	 * is given then searches only those records related to them.
	 * @param <String> $searchValue - Search value
	 * @param <Integer> $parentId - parent recordId
	 * @param <String> $parentModule - parent module name
	 * @return <Array of Vtiger_Record_Model>
	 */
	public function searchRecord($searchValue, $parentId=false, $parentModule=false, $relatedModule=false) {
		if(!empty($searchValue) && empty($parentId) && empty($parentModule) && (in_array($relatedModule, getInventoryModules()))) {
			$matchingRecords = Products_Record_Model::getSearchResult($searchValue, $this->getName());
		}else {
			parent::searchRecord($searchValue);
		}

		return $matchingRecords;
	}
	
	/** ED150507
	 * Function searches the record of the next Revue SdN that will be sent
	 * vtiger_products.sales_start_date defines the next product
	 */
	public function getProchaineRevue(){
		
		$db = PearDatabase::getInstance();

		$query = 'SELECT crmid, vtiger_products.sales_start_date
		FROM vtiger_products
		INNER JOIN vtiger_crmentity
			ON vtiger_crmentity.crmid = vtiger_products.productid
		WHERE vtiger_crmentity.deleted = 0
		AND vtiger_products.productcategory = \'Revue\'
		AND vtiger_products.sales_start_date > NOW()
		ORDER BY vtiger_products.sales_start_date
		LIMIT 1
		';
		
		$dbResult = $db->pquery($query);
		$crmId = $db->query_result($dbResult, 0, 'crmid');
		
		if(!$crmId){
			//La prochaine revue n'a pas été crée, on prend la dernière
			
			$query = 'SELECT crmid, vtiger_products.sales_start_date
			FROM vtiger_products
			INNER JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_products.productid
			WHERE vtiger_crmentity.deleted = 0
			AND vtiger_products.productcategory = \'Revue\'
			ORDER BY vtiger_products.sales_start_date DESC
			LIMIT 1
			';
			
			$dbResult = $db->pquery($query);
			$crmId = $db->query_result($dbResult, 0, 'crmid');
		}
		return Vtiger_Record_Model::getInstanceById($crmId, 'Products');
	}
	
	

	/**
	 * Function to get the module is permitted to specific action
	 * @param <String> $actionName
	 * @return <boolean>
	 */
	public function isPermitted($actionName) {
		if($actionName == 'Duplicate'){
			global $RSN_PRODUCT_ALLOW_DUPLICATE;
			return (!isset($RSN_PRODUCT_ALLOW_DUPLICATE) || $RSN_PRODUCT_ALLOW_DUPLICATE == 'true')
				&& parent::isPermitted($actionName);
		}
		return parent::isPermitted($actionName);
	}
	
	/**
	 * Function to get Alphabet Search Field 
	 */
	public function getAlphabetSearchField(){
		return 'productcategory,productname'; //TODO invoicestatus ne fonctionne pas
	}
}