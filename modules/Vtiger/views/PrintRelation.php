<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

 /* ED 140921 relation Critere D
  * Un widget apparait en résumé de contact car l'enregistrement correspondant existe dans la table vtiger_link
  *  renvoyant à l'url <code>module=Contacts&relatedModule=Critere4D&view=Detail&mode=showRelatedList&widget_inside=1&page=1&limit=5</code>
  *
  */
class Vtiger_PrintRelation_View extends Vtiger_Index_View {

	function preProcess(Vtiger_Request $request) {
		return true;
	}

	function postProcess(Vtiger_Request $request) {
		return true;
	}
	
	function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$relatedModuleName = $request->get('relatedModule');
		$parentId = $request->get('record');
		$label = $request->get('tab_label');
		$requestedPage = $request->get('page');
		if(empty($requestedPage)) {
			$requestedPage = 1;
		}
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page',$requestedPage);
		$pagingModel->set('limit', 99999);
			
		$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName, $label);

		$viewer = $this->getViewer($request);
		
		$orderBy = $request->get('orderby');
		$sortOrder = $request->get('sortorder');
		if($sortOrder == 'ASC') {
			$nextSortOrder = 'DESC';
			$sortImage = 'icon-chevron-down';
		} else {
			$nextSortOrder = 'ASC';
			$sortImage = 'icon-chevron-up';
		}
		if(!empty($orderBy)) {
			$relationListView->set('orderby', $orderBy);
			$relationListView->set('sortorder',$sortOrder);
		}
		
		//ED150701
		$searchKey = $request->get('search_key');
		$searchValue = $request->get('search_value');
		$operator = $request->get('operator');
			
		if(!empty($operator)) {
			$relationListView->set('operator', $operator);
			$viewer->assign('OPERATOR',is_array($operator) ? htmlspecialchars(json_encode($operator)) : $operator);
			$viewer->assign('ALPHABET_VALUE',is_array($searchValue) ? htmlspecialchars(json_encode($searchValue)) : $searchValue);
		}
		//ED150414 $searchValue == 0 is acceptable
		if(!empty($searchKey) && (!empty($searchValue) || ($searchValue == '0'))) {
			$relationListView->set('search_key', $searchKey);
			$relationListView->set('search_value', $searchValue);
		}
		
		$models = $relationListView->getEntries($pagingModel);
		
		$links = $relationListView->getLinks();
		$header = $relationListView->getHeaders();
		$noOfEntries = count($models);
		
		/*ED140907*/
		$unknown_field_returns_value = true; //$relatedModuleName == "Critere4D" || $relatedModuleName == "Contacts";

		$relationModel = $relationListView->getRelationModel();
		$relatedModuleModel = $relationModel->getRelationModuleModel();
		$relationField = $relationModel->getRelationField();
		
		switch($moduleName){
		case "Contacts" :
			switch($relatedModuleName){
/*			  case "Critere4D":
			  case "Documents":
				$tpl = "RelatedListMultiDates.tpl";
				break;
			  case "Contacts" :
				$tpl = "RelatedListContacts.tpl";
				break;
			  case "ContactAddresses" :
				$tpl = "RelatedListContactAddresses.tpl";
				break;
			  case "ContactEmails" :
				$tpl = "RelatedListContactEmails.tpl";
				break;
			  case "Services" :
				$tpl = "RelatedListServices.tpl";
				break*/;
			  case "Invoice" :
			  case "PurchaseOrder" :
			  case "SalesOrder" :
				$models = $this->addRelatedProducts($models, $header);
				$this->addRelatedProductsDetailsHeaders($header);
				$tpl = 'PrintRelation.tpl';
				break;
			  default:
				$tpl = 'PrintRelation.tpl';
				break;
			}
			break;
		
		case "Accounts" :
			switch($relatedModuleName){
			//  case "ContactAddresses" :
			//	$tpl = "RelatedListContactAddresses.tpl";
			//	break;
			//  case "ContactEmails" :
			//	$tpl = "RelatedListContactEmails.tpl";
			//	break;
			  case "Invoice" :
			  case "PurchaseOrder" :
			  case "SalesOrder" :
				$models = $this->addRelatedProducts($models, $header);
				$this->addRelatedProductsDetailsHeaders($header);
				$tpl = 'PrintRelation.tpl';
				break;
			  default:
				$tpl = 'PrintRelation.tpl';
				break;
			}
			break;
		
		//case "RSNMediaContacts" :
		//case "RSNMedias" :
		//	switch($relatedModuleName){
		//	  case "RSNMediaRelations":
		//		$tpl = "RelatedListRSNMediaRelations.tpl";
		//		break;
		//	  default:
		//		$tpl = 'PrintRelation.tpl';
		//		break;
		//	}
		//	break;
		
		default:
			$tpl = 'PrintRelation.tpl';
			break;
		}
		

		$viewer->assign('RELATED_RECORDS' , $models);
		$viewer->assign('PARENT_RECORD', $parentRecordModel);
		$viewer->assign('RELATED_HEADERS', $header);
		$viewer->assign('RELATED_MODULE', $relatedModuleModel);
		$viewer->assign('RELATED_ENTIRES_COUNT', $noOfEntries);
		$viewer->assign('RELATION_FIELD', $relationField);
		
		/*ED140926*/
		$viewer->assign('FROM_TAB_LABEL', $label);
		
		$viewer->assign('UNKNOWN_FIELD_RETURNS_VALUE', $unknown_field_returns_value); /*ED140907*/

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('PAGING', $pagingModel);

		$viewer->assign('ORDER_BY',$orderBy);
		$viewer->assign('SORT_ORDER',$sortOrder);
		$viewer->assign('NEXT_SORT_ORDER',$nextSortOrder);
		$viewer->assign('SORT_IMAGE',$sortImage);
		$viewer->assign('COLUMN_NAME',$orderBy);

		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('VIEW', $request->get('view'));

		
		echo $viewer->view($tpl, $moduleName, 'true');
	}
	
	/** ED150812
	 * Ajoute les lignes de produits
	 *
	 * Cette boucle redéfinit l'ordre des factures car on reconstruit le tableau. TODO
	 *
	 */
	function addRelatedProducts($parentRecords, $headers){
		$newParentRecords = array();
		$parentIds = array_keys($parentRecords);
		
		$query = 'SELECT vtiger_crmentity.crmid, vtiger_inventoryproductrel.id AS invoiceid, vtiger_crmentity.label
			, vtiger_inventoryproductrel.quantity, IFNULL(vtiger_products.productcode, vtiger_service.productcode) AS productcode, vtiger_inventoryproductrel.description
			, vtiger_inventoryproductrel.sequence_no
			FROM vtiger_inventoryproductrel
			JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_inventoryproductrel.productid
			LEFT JOIN vtiger_products
				ON vtiger_crmentity.crmid = vtiger_products.productid
			LEFT JOIN vtiger_service
				ON vtiger_crmentity.crmid = vtiger_service.serviceid
			WHERE vtiger_crmentity.deleted = 0
			AND vtiger_inventoryproductrel.id IN ('. generateQuestionMarks($parentIds) . ')
			AND NOT (vtiger_products.productcode IS NULL AND vtiger_service.productcode IS NULL)
			ORDER BY vtiger_inventoryproductrel.id DESC, vtiger_inventoryproductrel.sequence_no
		';
		$params = array();
		$params = array_merge($parentIds);
		
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, $params);
		if(!$result){
			$db->echoError();
		}
		$numOfrows = $db->num_rows($result);
		$previousParentId = false;
		for($i=0; $i<$numOfrows; $i++) {
			$parentId = $db->query_result($result, $i, 'invoiceid');
			$parentRecord = $parentRecords[$parentId];
			if(!$parentRecord)
				continue;
			if($previousParentId == $parentId){
				//duplicate de l'enregistrement
				//$parentRecord = new Vtiger_Record_Model($parentRecord->getModuleName());
				$parentRecord = clone $parentRecord;
				//Clone mais efface les champs visible
				foreach($headers as $headerName => $header)
					$parentRecord->set($headerName, '');
				
			}
			
			$fieldName = '_products';
			$parentRecord->set($fieldName, $db->query_result($result, $i, 'label') . ' - ' . $db->query_result($result, $i, 'productcode')
					   . ($db->query_result($result, $i, 'description') ? '<br>' . htmlentities($db->query_result($result, $i, 'description')) : ''));
			
			$fieldName = '_quantity';
			$parentRecord->set($fieldName, $db->query_result($result, $i, 'quantity'));
			
			$newParentRecords[$parentId . '|' . $db->query_result($result, $i, 'sequence_no')] = $parentRecord;
			
			$previousParentId = $parentId;
		}
		return $newParentRecords;
	}
	
	function addRelatedProductsDetailsHeaders(&$headers){

	    $fieldName = '_products';
	    $field = new Vtiger_Field_Model();
	    $field->set('name', $fieldName);
	    $field->set('column', $fieldName);
	    $field->set('label', 'Produits');
	    $field->set('typeofdata', 'V~0');
	    $field->set('uitype', 1);
	    $headers[$field->get('name')] = $field;
		
	    $fieldName = '_quantity';
	    $field = new Vtiger_Field_Model();
	    $field->set('name', $fieldName);
	    $field->set('column', $fieldName);
	    $field->set('label', 'Quantité');
	    $field->set('typeofdata', 'V~0');
	    $field->set('uitype', 7);	    
	    $headers[$field->get('name')] = $field;
		
	}
}
