<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

 /* ED 150124 copie depuis RelatedList.php
  * 
  * Affichage d'une confirmation de suppression de relation
  *
  * DeleteRelation n'existait pas, il n'est utilisé que par :
  * - Panel -> Variables : "Réinitialiser toutes les variables"
  */
class Vtiger_DeleteRelation_View extends Vtiger_Index_View {
	function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$relatedModuleName = $request->get('relatedModule');
		$parentId = $request->get('record');
		$label = $request->get('tab_label');
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page',$requestedPage);
		if($request->get('limit')) /* ED140921 */
			$pagingModel->set('limit',$request->get('limit'));
			
		$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName, $label);

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

		$models = $relationListView->getEntries($pagingModel);
		$links = $relationListView->getLinks();
		$header = $relationListView->getHeaders();
		$noOfEntries = count($models);
		
		$relationModel = $relationListView->getRelationModel();
		$relatedModuleModel = $relationModel->getRelationModuleModel();
		$relationField = $relationModel->getRelationField();

		$viewer = $this->getViewer($request);
		$viewer->assign('RELATED_RECORDS' , $models);
		$viewer->assign('PARENT_RECORD', $parentRecordModel);
		$viewer->assign('RELATED_LIST_LINKS', $links);
		$viewer->assign('RELATED_HEADERS', $header);
		$viewer->assign('RELATED_MODULE', $relatedModuleModel);
		$viewer->assign('RELATED_ENTIRES_COUNT', $noOfEntries);
		$viewer->assign('RELATION_FIELD', $relationField);
		
		/*ED140926*/
		$viewer->assign('FROM_TAB_LABEL', $label);
		
		$viewer->assign('UNKNOWN_FIELD_RETURNS_VALUE', true); /*ED140907*/

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
		$viewer->assign('MODULE_MODEL', Vtiger_Module_Model::getInstance($moduleName));
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
		
		// TODO : switch issu du copier-coller mais sans utilité
		switch($moduleName){
		case "Contacts" :
			switch($relatedModuleName){
			  case "Critere4D":
				$tpl = "DeleteRelationCritere4D.tpl";
				break;
			  case "Contacts" :
				$tpl = "DeleteRelationContacts.tpl";
				break;
			  case "Services" :
				$tpl = "DeleteRelationServices.tpl";
				break;
			  default:
				$tpl = 'DeleteRelation.tpl';
				break;
			}
			break;
		
		case "RSNMediaContacts" :
		case "RSNMedias" :
			switch($relatedModuleName){
			  case "RSNMediaRelations":
				$tpl = "DeleteRelationRSNMediaRelations.tpl";
				break;
			  default:
				$tpl = 'DeleteRelation.tpl';
				break;
			}
			break;
		
		default:
			$tpl = 'DeleteRelation.tpl';
			break;
		}
		$r = $viewer->view($tpl, $moduleName, 'true');
		
		echo $r;
		
		return $r;
	}
}