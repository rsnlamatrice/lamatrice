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
class Vtiger_RelatedList_View extends Vtiger_Index_View {
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
		if($request->get('limit')) /* ED140921 */
			$pagingModel->set('limit',$request->get('limit'));
			
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
		
		//ED150812
		switch($relatedModuleName){
		  case "Services":
		  case "Products":
			
			switch($moduleName){
			  case "PurchaseOrder":
			  case "Invoice":
			  case "SalesOrder":
				$this->setSoldPrice_to_UnitPrice($parentRecordModel, $models, $header);
				break;
			  default:
				$this->addVAT_to_UnitPrice($models);
				break;
			}
			break;
		  default:
			break;
		}

		$viewer->assign('RELATED_RECORDS' , $models);
		$viewer->assign('PARENT_RECORD', $parentRecordModel);
		$viewer->assign('RELATED_LIST_LINKS', $links);
		$viewer->assign('RELATED_HEADERS', $header);
		$viewer->assign('RELATED_MODULE', $relatedModuleModel);
		$viewer->assign('RELATED_ENTIRES_COUNT', $noOfEntries);
		$viewer->assign('RELATION_FIELD', $relationField);
		
		/*ED140926*/
		$viewer->assign('FROM_TAB_LABEL', $label);
		
		$viewer->assign('UNKNOWN_FIELD_RETURNS_VALUE', $unknown_field_returns_value); /*ED140907*/

		if (PerformancePrefs::getBoolean('LISTVIEW_COMPUTE_PAGE_COUNT', false)) {
			$totalCount = $relationListView->getRelatedEntriesCount();
			$pageLimit = $pagingModel->getPageLimit();
			$pageCount = ceil((int) $totalCount / (int) $pageLimit);

			if($pageCount == 0){
				$pageCount = 1;
			}
			$viewer->assign('PAGE_COUNT', $pageCount);
			$viewer->assign('TOTAL_ENTRIES', $totalCount);
			$viewer->assign('PERFORMANCE', true);
		}

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('PAGING', $pagingModel);

		$viewer->assign('ORDER_BY',$orderBy);
		$viewer->assign('SORT_ORDER',$sortOrder);
		$viewer->assign('NEXT_SORT_ORDER',$nextSortOrder);
		$viewer->assign('SORT_IMAGE',$sortImage);
		$viewer->assign('COLUMN_NAME',$orderBy);

		$viewer->assign('IS_EDITABLE', $relationModel->isEditable());
		$viewer->assign('IS_DELETABLE', $relationModel->isDeletable());
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('VIEW', $request->get('view'));

		/*ED140907*/
		$viewer->assign('WIDGET_INSIDE', $request->get('widget_inside'));
		
		switch($moduleName){
		case "Contacts" :
			switch($relatedModuleName){
			  case "Critere4D":
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
				break;
			  default:
				$tpl = 'RelatedList.tpl';
				break;
			}
			break;
		
		case "Accounts" :
			switch($relatedModuleName){
			  case "ContactAddresses" :
				$tpl = "RelatedListContactAddresses.tpl";
				break;
			  case "ContactEmails" :
				$tpl = "RelatedListContactEmails.tpl";
				break;
			  default:
				$tpl = 'RelatedList.tpl';
				break;
			}
			break;
		
		case "RSNMediaContacts" :
		case "RSNMedias" :
			switch($relatedModuleName){
			  case "RSNMediaRelations":
				$tpl = "RelatedListRSNMediaRelations.tpl";
				break;
			  default:
				$tpl = 'RelatedList.tpl';
				break;
			}
			break;
		
		default:
			$tpl = 'RelatedList.tpl';
			break;
		}
		
		return $viewer->view($tpl, $moduleName, 'true');
	}
	
	//Change la valeur du prix unitaire pour afficher le prix TTC
	function addVAT_to_UnitPrice(&$records){
		global $adb;
		$productIds = array_keys($records);
		
		$query = 'SELECT vtiger_producttaxrel.productid, MAX(vtiger_producttaxrel.taxpercentage) AS percentage
			FROM vtiger_inventorytaxinfo
			LEFT JOIN vtiger_producttaxrel
				ON vtiger_inventorytaxinfo.taxid = vtiger_producttaxrel.taxid
			WHERE vtiger_producttaxrel.productid IN (' . generateQuestionMarks($productIds) . ')
			AND vtiger_inventorytaxinfo.deleted=0
			GROUP BY vtiger_producttaxrel.productid';
		
		$params = $productIds;
		$res = $adb->pquery($query, $params);
		if(!$res)
			$adb->echoError();
		for($i=0;$i<$adb->num_rows($res);$i++){
			$productId = $adb->query_result($res,$i,'productid');
			$record = $records[$productId];
			if(!$record)
				continue;
			$price = $record->get('unit_price');
			$tax = $adb->query_result($res,$i,'percentage');
			if(!$price || !$tax)
				continue;
			$price += $price * $tax/100;
			$record->set('unit_price', $price);
		}
	}
	
	//Change la valeur du prix unitaire pour afficher le prix TTC vendu
	function setSoldPrice_to_UnitPrice($parentRecordModel, &$records, &$headers){
		
		if(array_key_exists('qty_per_unit', $headers))
			unset($headers['qty_per_unit']);
		if(!array_key_exists('quantity', $headers)){
			$field = new Vtiger_Field_Model();
			$field->set('name', 'quantity');
			$field->set('column', 'vtiger_inventoryproductrel:quantity');
			$field->set('label', 'Quantité');
			$field->set('typeofdata', 'V~O');
			$field->set('uitype', 7);
			$headers['quantity'] = $field;
		}
		global $adb;
		$productIds = array_keys($records);
		
		$query = 'SELECT productid, quantity, IFNULL(tax1, IFNULL(tax2, IFNULL(tax3, IFNULL(tax4, IFNULL(tax5, tax6))))) AS percentage
			FROM vtiger_inventoryproductrel
			WHERE vtiger_inventoryproductrel.id = ?';
		
		$params = array(
			$parentRecordModel->getId()
		);
		//var_dump($query, $params);
		$res = $adb->pquery($query, $params);
		if(!$res)
			$adb->echoError();
		for($i=0;$i<$adb->num_rows($res);$i++){
			$productId = $adb->query_result($res,$i,'productid');
			$record = $records[$productId];
			if(!$record)
				continue;
			$record->set('quantity', $adb->query_result($res,$i,'quantity'));
			$price = $record->get('unit_price');
			$tax = $adb->query_result($res,$i,'percentage');
			//var_dump($tax);
			if(!$price || !$tax)
				continue;
			$price += $price * $tax/100;
			$record->set('unit_price', $price);
		}
	}
}
