<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

Class CustomView_EditAjax_View extends Vtiger_IndexAjax_View {

	public function process(Vtiger_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $request->get('source_module');
		$module = $request->getModule();
		$record = $request->get('record');

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel);

		if(!empty($record)) {
			$customViewModel = CustomView_Record_Model::getInstanceById($record);
			$viewer->assign('MODE', 'edit');
		} else {
			$customViewModel = new CustomView_Record_Model();
			$customViewModel->setModule($moduleName);
			$viewer->assign('MODE', '');
		}
		
		
		$advanceCriteria = $customViewModel->transformToNewAdvancedFilter();
		if(!array_key_exists('1', $advanceCriteria))
			$advanceCriteria['1'] = array();
		if(!array_key_exists('2', $advanceCriteria))
			$advanceCriteria['2'] = array();
		
		$viewer->assign('ADVANCE_CRITERIA', $advanceCriteria);
		$viewer->assign('CURRENTDATE', date('Y-n-j'));
		$viewer->assign('DATE_FILTERS', Vtiger_Field_Model::getDateFilterTypes());
		
		if($moduleName == 'Calendar'){
			$advanceFilterOpsByFieldType = Calendar_Field_Model::getAdvancedFilterOpsByFieldType();
		} else{
			$advanceFilterOpsByFieldType = Vtiger_Field_Model::getAdvancedFilterOpsByFieldType();
		}
		$viewer->assign('ADVANCED_FILTER_OPTIONS', Vtiger_Field_Model::getAdvancedFilterOptions());
		$viewer->assign('ADVANCED_FILTER_OPTIONS_BY_TYPE', $advanceFilterOpsByFieldType);
		$dateFilters = Vtiger_Field_Model::getDateFilterTypes();
		foreach($dateFilters as $comparatorKey => $comparatorInfo) {
		    $comparatorInfo['startdate'] = DateTimeField::convertToUserFormat($comparatorInfo['startdate']);
		    $comparatorInfo['enddate'] = DateTimeField::convertToUserFormat($comparatorInfo['enddate']);
		    $comparatorInfo['label'] = vtranslate($comparatorInfo['label'],$module);
		    $dateFilters[$comparatorKey] = $comparatorInfo;
		}
		$viewer->assign('DATE_FILTERS', $dateFilters);
		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		$viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());
		$viewer->assign('CUSTOMVIEW_MODEL', $customViewModel);
		$viewer->assign('RECORD_ID', $record);
		$viewer->assign('MODULE', $module);
		$viewer->assign('SOURCE_MODULE',$moduleName);
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('CV_PRIVATE_VALUE', CustomView_Record_Model::CV_STATUS_PRIVATE);
		$viewer->assign('CV_PENDING_VALUE', CustomView_Record_Model::CV_STATUS_PENDING);
		$viewer->assign('CV_PUBLIC_VALUE', CustomView_Record_Model::CV_STATUS_PUBLIC);
		$viewer->assign('MODULE_MODEL',$moduleModel);

		/* ED150212
		 * Related record structures
		 */
		$relationModels = $moduleModel->getRelations();
		$relatedViews = array();
		foreach($relationModels as $relationModel){
			$relatedViews[$relationModel->getRelationModuleName()] = $relationModel->getRelationViews();
		}
		/* ED150507
		 * RSNContactsPanels
		 */
		if($moduleName == 'Contacts'){
			$contactsPanels = array();
			$contactsPanelsRecords = RSNContactsPanels_Record_Model::getAllForCustomViewEditor();
			foreach($contactsPanelsRecords as $contactsPanel){
				$contactsPanels[$contactsPanel->getName()] = $contactsPanel;
			}
			$viewer->assign('CONTACTS_PANELS',$contactsPanels);
		}

		/* ED151025
		 * RSNStatistics
		 */
		$relatedStats = array();
		$statRecords = RSNStatistics_Utils_Helper::getRelatedStatisticsRecordModels($moduleName);
		if($statRecords){
			$statsModuleModel = Vtiger_Module_Model::getInstance('RSNStatistics');
			$periodicityField = false;
			foreach($statRecords as $statRecord){
				if(!$periodicityField){
					$periodicityField = $statsModuleModel->getField('stats_periodicite');
				} else {
					$periodicityField = clone $periodicityField;
				}
				$periodicityField->set('label', '[' . $statRecord->getName() . '] Période');
				$periodicityField->set('parentid', $statRecord->getId());
				//Champs d'une stat
				$relatedStatsFields = array();
				//1er champ : la période
				$relatedStatsFields[$periodicityField->getId()] = $periodicityField;
				$relatedStatsFields = array_merge($relatedStatsFields, RSNStatistics_Utils_Helper::getRelatedStatsFieldsVtigerFieldModels($statRecord->getId(),$moduleName));
				$relatedStats[$statRecord->getId()] = array(
					'recordModel' => $statRecord,
					'fields' => $relatedStatsFields,
				);
			}
			$viewer->assign('RELATED_STATISTICS',$relatedStats);
		}
		//var_dump($relatedStructures);
		$viewer->assign('RELATED_MODELS',$relationModels);
		$viewer->assign('RELATED_MODELS_VIEWS',$relatedViews);
		
		echo $viewer->view('EditView.tpl', $module, true);
	}
}