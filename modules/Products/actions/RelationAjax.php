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
		$relatedModuleModel = Vtiger_Module_Model::getInstance($relatedModule);
		$related_data = $request->get('related_data');
		
		global $adb;
		
		//Clear DB data
		$query = "DELETE FROM vtiger_pricebookproductrel
			WHERE productid = ?";
		$adb->pquery($query, array($recordId));
		
		//
		foreach($related_data as $discountType => $quantities){
			foreach($quantities as $quantity => $priceData){
				$pricebookId = $relatedModuleModel->getPriceBookRecordId($discountType, $quantity);
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
	
}

?>