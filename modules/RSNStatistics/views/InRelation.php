<?php
/*+***********************************************************************************
 * AV150000
 *
 * ED151017
 * 	La relation RSNStatistics <-> RSNStatistics indique un calcul global par les fonctions d'aggrégation
 *************************************************************************************/

class RSNStatistics_InRelation_View extends Vtiger_RelatedList_View {
	function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$relatedModuleName = $request->get('relatedModule');
		$parentId = $request->get('record');
		$label = $request->get('tab_label');
		$requestedPage = $request->get('page');
		if(empty ($requestedPage)) {
			$requestedPage = 1;
		}
		
		//CustomView filtrant les records liés
		$relatedViewName = $request->get('related_viewname');

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page',$requestedPage);
		//ED $pagingModel->set('limit', 10);//AUR_TMP : not good -> le nombre de page n'est pas correctement calculé si je ne commente pas la cond performancePref...

		$parentRecordModel = Vtiger_Record_Model::getInstanceById($parentId, $moduleName);
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName, $label);
		$orderBy = $request->get('orderby');
		$sortOrder = $request->get('sortorder');
		if($sortOrder == "ASC") {
			$nextSortOrder = "DESC";
			$sortImage = "icon-chevron-down";
		} else {
			$nextSortOrder = "ASC";
			$sortImage = "icon-chevron-up";
		}
		if(!empty($orderBy)) {
			$relationListView->set('orderby', $orderBy);
			$relationListView->set('sortorder',$sortOrder);
		}
		if($relatedViewName){ //Filtre sur les éléments liés
			$relationListView->set('related_viewname', $relatedViewName);
		}
		
		$models = $relationListView->getEntries($pagingModel, $parentId);//tmp do not use that ??
		
		//var_dump($models->list_fields);
		$links = $relationListView->getLinks();
		$header = $relationListView->getHeaders();
		$noOfEntries = count($models);

		$relationModel = $relationListView->getRelationModel();
		$relatedModuleModel = $relationModel->getRelationModuleModel();
		$relationField = $relationModel->getRelationField();

		$viewer = $this->getViewer($request);
		$viewer->assign('CRMID' , $parentId);
		$viewer->assign('RELATED_RECORDS' , $models);//tmp rename record to something more explicit !!!
		$viewer->assign('PARENT_RECORD', $parentRecordModel);
		$viewer->assign('RELATED_LIST_LINKS', $links);
		$viewer->assign('RELATED_HEADERS', $header);
		$viewer->assign('RELATED_MODULE', $relatedModuleModel);
		$viewer->assign('RELATED_ENTIRES_COUNT', $noOfEntries);
		$viewer->assign('RELATION_FIELD', $relationField);
		$viewer->assign('UPDATE_STATS_URL', $relatedModuleModel->getUpdateValuesUrl($moduleName === 'RSNStatistics' ? '*' : $parentId, $moduleName, $moduleName === 'RSNStatistics' ? $parentRecordModel->getId() : ''));
		$viewer->assign('UPDATE_STATS_THIS_YEAR_URL', $relatedModuleModel->getUpdateValuesUrl($moduleName === 'RSNStatistics' ? '*' : $parentId, $moduleName, $moduleName === 'RSNStatistics' ? $parentRecordModel->getId() : '', 'this year'));
		
		//if (PerformancePrefs::getBoolean('LISTVIEW_COMPUTE_PAGE_COUNT', false)) {
			$totalCount = $relationListView->getRelatedEntriesCount();
			$pageLimit = $pagingModel->getPageLimit();
			$pageCount = ceil((int) $totalCount / (int) $pageLimit);

			if($pageCount == 0){
				$pageCount = 1;
			}
			$viewer->assign('PAGE_COUNT', $pageCount);
			$viewer->assign('TOTAL_ENTRIES', $totalCount);
			$viewer->assign('PERFORMANCE', true);
		//}

		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('PAGING', $pagingModel);

		$viewer->assign('ORDER_BY',$orderBy);
		$viewer->assign('SORT_ORDER',$sortOrder);
		$viewer->assign('NEXT_SORT_ORDER',$nextSortOrder);
		$viewer->assign('SORT_IMAGE',$sortImage);
		$viewer->assign('COLUMN_NAME',$orderBy);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('IS_EDITABLE', $relationModel->isEditable());
		$viewer->assign('IS_DELETABLE', $relationModel->isDeletable());
        $viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('VIEW', $request->get('view'));

		if($relatedViewName)
			$viewer->assign('RELATED_VIEWNAME', $relatedViewName);
			
		//var_dump($relatedModuleName);
		if($relatedModuleName === 'RSNStatistics'){
			//var_dump($recordModel->get('relmodule'), CustomView_Record_Model::getAllByGroup($recordModel->get('relmodule')));
			$viewer->assign('RELATED_VIEWNAME', $relatedViewName);
			$viewer->assign('CUSTOM_VIEWS', CustomView_Record_Model::getAllByGroup($parentRecordModel->get('relmodule')));
		}
		
		return $viewer->view('RelatedStats.tpl', 'RSNStatistics', 'true');
	}
}