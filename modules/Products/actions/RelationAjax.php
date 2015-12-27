<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Products_RelationAjax_Action extends Vtiger_RelationAjax_Action {
	
	function __construct() {
		parent::__construct();
		$this->exposeMethod('saveRelatedPriceBooks');
	}
	
	function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode) && method_exists($this, "$mode")) {
			$this->$mode($request);
			return;
		}
		return parent::process($request);
	}
	
	/*
	 * Function to add relation for specified source record id and related record id list
	 * @param <array> $request
	 */
	function addRelation($request) {
		$sourceModule = $request->getModule();
		$sourceRecordId = $request->get('src_record');

		$relatedModule = $request->get('related_module');
		$relatedRecordIdList = $request->get('related_record_list');

		$sourceModuleModel = Vtiger_Module_Model::getInstance($sourceModule);
		$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
		$relationModel = Vtiger_Relation_Model::getInstance($sourceModuleModel, $relatedModuleModel);
		foreach($relatedRecordIdList as $relatedRecordId) {
			$relationModel->addRelation($sourceRecordId,$relatedRecordId,$listPrice);
			if($relatedModule == 'PriceBooks'){
				$recordModel = Vtiger_Record_Model::getInstanceById($relatedRecordId);
				if ($sourceRecordId && ($sourceModule === 'Products' || $sourceModule === 'Services')) {
					$parentRecordModel = Vtiger_Record_Model::getInstanceById($sourceRecordId, $sourceModule);
					$recordModel->updateListPrice($sourceRecordId, $parentRecordModel->get('unit_price'));
				}
			}
		}
	}
	
	/**
	 * Enregistrement de la grille de tarifs
	 * $request comporte un champ related_data : array (
	 * 		<discounttype> => array(
	 * 			<quantity> => array(
					'price' => <Float>,
					'unit' => <String HT|TTC|%>,
				)
	 * 		) ))))
	 */
	function saveRelatedPriceBooks(Vtiger_Request $request){
		
		$module = $request->getModule();
		$recordId = $request->get('record');
		$relatedModule = $request->get('related_module');//PriceBooks
		$related_data = $request->get('related_data');
		
		global $adb;
		
		//Clear DB data
		$query = "DELETE FROM vtiger_pricebookproductrel
			WHERE productid = ?";
		$adb->pquery($query, array($recordId));
		
		//
		foreach($related_data as $discountType => $quantities){
			foreach($quantities as $quantity => $priceData){
				$pricebookId = $this->getPriceBookRecordId($discountType, $quantity);
				if(!$pricebookId)
					throw new Exception('Impossible de trouver le pricebook');
				
				$query = "INSERT INTO vtiger_pricebookproductrel (`pricebookid`, `productid`, `listprice`, `listpriceunit`, `usedcurrency`)
					VALUES( ?, ?, ?, ?, 1)";
				$params = array($pricebookId, $recordId, $priceData['price'], $priceData['unit']);
				$result = $adb->pquery($query, $params);
				if(!$result){
					$adb->echoError();
					return;
				}
			}
		}
		
		$response = new Vtiger_Response();
		$response->setResult(true);
		$response->emit();
	}
	
	function getPriceBookRecordId($discountType, $quantity){
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
		
		//Création d'un nouveau record
		$recordModel = Vtiger_Record_Model::getCleanInstance('PriceBooks');
		$discountTypes = $recordModel->getPicklistValuesDetails('discounttype');
		$name = "";
		if($discountType){
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

?>