<?php
/*+***********************************************************************************
 * 
 *************************************************************************************/

class PriceBooks_InProductsRelation_View extends Vtiger_RelatedList_View {
	function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$relatedModuleName = $request->get('relatedModule');
		$parentId = $request->get('record');
		
		$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
		$priceBookRecordModel = Vtiger_Record_Model::getCleanInstance($relatedModuleName);
		
		$priceUnits = PriceBooks_Relation_Model::getPriceUnits();
		$remiseTypes = $priceBookRecordModel->getPicklistValuesDetails('discounttype');
		
		$viewer = $this->getViewer($request);
		
		$viewer->assign('PRICE_UNITS', $priceUnits);
		$viewer->assign('DISCOUNT_TYPES', $remiseTypes);
		
		$viewer->assign('PRODUCT_PRICE', CurrencyField::convertToUserFormat($parentRecordModel->get('unit_price')));
		$viewer->assign('PRODUCT_PRICE_TAXED', CurrencyField::convertToUserFormat($parentRecordModel->getTaxedPrice()));
						
		return parent::process($request);
	}
}
