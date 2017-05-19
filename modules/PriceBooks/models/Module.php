<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class PriceBooks_Module_Model extends Vtiger_Module_Model {

	/**
	 * Function to check whether the entity has an quick create menu
	 * @return <Boolean> true/false
	 * ED141024
	 */
	public function isQuickCreateMenuVisible() {
		return false ;
	}

	/**
	 * Function to check whether the module is summary view supported
	 * @return <Boolean> - true/false
	 */
	public function isSummaryViewSupported() {
		return false;
	}

	/**
	 * Function returns query for PriceBook-Product relation
	 * @param <Vtiger_Record_Model> $recordModel
	 * @param <Vtiger_Record_Model> $relatedModuleModel
	 * @return <String>
	 */
	function get_pricebook_products($recordModel, $relatedModuleModel) {
		$query = 'SELECT vtiger_products.productid, vtiger_products.productname, vtiger_products.productcode, vtiger_products.commissionrate,
						vtiger_products.qty_per_unit, vtiger_products.unit_price, vtiger_crmentity.crmid, vtiger_crmentity.smownerid,
						vtiger_pricebookproductrel.listprice, vtiger_pricebookproductrel.listpriceunit
				FROM vtiger_products
				INNER JOIN vtiger_pricebookproductrel ON vtiger_products.productid = vtiger_pricebookproductrel.productid
				INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_products.productid
				INNER JOIN vtiger_pricebook on vtiger_pricebook.pricebookid = vtiger_pricebookproductrel.pricebookid
				LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid '
				. Users_Privileges_Model::getNonAdminAccessControlQuery($relatedModuleModel->getName()) .'
				WHERE vtiger_pricebook.pricebookid = '.$recordModel->getId().' and vtiger_crmentity.deleted = 0';
		return $query;
	}


	/**
	 * Function returns query for PriceBooks-Services Relationship
	 * @param <Vtiger_Record_Model> $recordModel
	 * @param <Vtiger_Record_Model> $relatedModuleModel
	 * @return <String>
	 */
	function get_pricebook_services($recordModel, $relatedModuleModel) {
		$query = 'SELECT vtiger_service.serviceid, vtiger_service.servicename, vtiger_service.commissionrate,
					vtiger_service.qty_per_unit, vtiger_service.unit_price, vtiger_crmentity.crmid, vtiger_crmentity.smownerid,
					vtiger_pricebookproductrel.listprice, vtiger_pricebookproductrel.listpriceunit
			FROM vtiger_service
			INNER JOIN vtiger_pricebookproductrel on vtiger_service.serviceid = vtiger_pricebookproductrel.productid
			INNER JOIN vtiger_crmentity on vtiger_crmentity.crmid = vtiger_service.serviceid
			INNER JOIN vtiger_pricebook on vtiger_pricebook.pricebookid = vtiger_pricebookproductrel.pricebookid
			LEFT JOIN vtiger_users ON vtiger_users.id=vtiger_crmentity.smownerid
			LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid '
			. Users_Privileges_Model::getNonAdminAccessControlQuery($relatedModuleModel->getName()) .'
			WHERE vtiger_pricebook.pricebookid = '.$recordModel->getId().' and vtiger_crmentity.deleted = 0';
		return $query;
	}

	/**
	 * Function to get list view query for popup window
	 * @param <String> $sourceModule Parent module
	 * @param <String> $field parent fieldname
	 * @param <Integer> $record parent id
	 * @param <String> $listQuery
	 * @return <String> Listview Query
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, $listQuery, $currencyId = false) {
		$relatedModulesList = array('Products', 'Services');
		if (in_array($sourceModule, $relatedModulesList)) {
			$pos = stripos($listQuery, ' where ');
			if ($currencyId && in_array($field, array('productid', 'serviceid'))) {
				$condition = " vtiger_pricebook.pricebookid IN (SELECT pricebookid FROM vtiger_pricebookproductrel WHERE productid = $record)
								AND vtiger_pricebook.currency_id = $currencyId AND vtiger_pricebook.active = 1";
				if ($pos) {
					$split = preg_split('/ where /i', $listQuery);
					$overRideQuery = $split[0] . ' WHERE ' . $split[1] . ' AND ' . $condition;
				} else {
					$overRideQuery = $listQuery . ' WHERE ' . $condition;
				}
			}
			return $overRideQuery;
		}
	}

	//Il est important que tous les champs soient transmis à l'éditeur de tarifs (Product->relatedPriceBooks)
	public function getConfigureRelatedListFields(){
		$fieldNames = array_keys($this->getFields());
		return array_merge(
			array_combine($fieldNames, $fieldNames),
			parent::getConfigureRelatedListFields(),
			array(
				'listprice'=>'listprice',
				'listpriceunit'=>'listpriceunit',
			));
	}
	//Il est important que tous les champs soient transmis à l'éditeur de tarifs (Product->relatedPriceBooks)
	public function getRelatedListFields(){
		return $this->getConfigureRelatedListFields();
	}

	/* ED151227
	 * Cherche un pricebook pour la remise et la quantité (exacte) données.
	 */
	function getPriceBookRecordId($discountType, $quantity, $createIfNone = true){
		global $adb;

		//Clear DB data
		$query = "SELECT vtiger_crmentity.crmid
			FROM vtiger_pricebook
			JOIN vtiger_crmentity
				ON vtiger_pricebook.pricebookid = vtiger_crmentity.crmid
			WHERE vtiger_crmentity.deleted = 0
			AND vtiger_pricebook.active = true
			AND IFNULL(discounttype, '') = ?
			AND IFNULL(minimalqty, 0) = ?
			AND IFNULL(modeapplication, '') = ?
			LIMIT 1";

		if($discountType || $discountType === 0 || $discountType === "0"){
			if($quantity)
				$modeapplication = 'qty,discounttype';
			else
				$modeapplication = 'discounttype';
		}
		elseif($quantity)
			$modeapplication = 'qty';
		else
			$modeapplication = '';
		$params = array($discountType, $quantity, $modeapplication);
		//var_dump($params);
		$result = $adb->pquery($query, $params);
		if(!$result){
			$adb->echoError();
			die();
		}
		if($adb->getRowCount($result)){
			return $adb->query_result($result, 0, 'crmid');
		}

		if(!$createIfNone)
			return false;

		//Création d'un nouveau record
		$recordModel = Vtiger_Record_Model::getCleanInstance('PriceBooks');
		$discountTypes = $recordModel->getPicklistValuesDetails('discounttype');
		$name = "";
		if($discountType || $discountType === 0 || $discountType === "0"){
			if($discountTypes[$discountType]){
				$name = vtranslate($discountTypes[$discountType]['label'], 'PriceBooks');
			} else {
				$name = vtranslate($discountType, 'PriceBooks');
			}
		}
		if($quantity){
			if($name)
				$name .= ', à';
			else
				$name = 'A';
			$name .= ' partir de ' . $quantity;
		}
		$recordModel->set('bookname', $name);
		$recordModel->set('currency_id', 1);
		$recordModel->set('active', 1);
		$recordModel->set('modeapplication', $modeapplication);
		$recordModel->set('minimalqty', $quantity);
		$recordModel->set('discounttype', $discountType);
		$recordModel->save();

		return $recordModel->getId();
	}
}