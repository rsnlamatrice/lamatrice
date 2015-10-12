<?php
/**************************************************************************************
 * ED151009
 **************************************************************************************/

class Vtiger_DuplicatesRelations_View extends Vtiger_Popup_View {
	
	function process(Vtiger_Request $request) {
		
		$mode = $request->get('mode');
		if(!empty($mode)) {
			return $this->invokeExposedMethod($mode, $request);
		}
		
		$records = $request->get('records');
		$records = explode(',', $records);
		$module = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($module);
		$fieldModels =  $moduleModel->getFields();

		$recordModelsByType = array(
			'individual' => array(),
			'group' => array(),
		);
		foreach($records as $recordId) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $module);
			$group_type = $recordModel->get('isgroup') ? 'group' : 'individual';
			$recordModelsByType[$group_type][] = $recordModel;
		}
		$recordModels = array_merge($recordModelsByType['group'], $recordModelsByType['individual']);//sorted
		
		if($recordModel)
			$recordStrucure = Vtiger_RecordStructure_Model::getInstanceFromRecordModel($recordModel, Vtiger_RecordStructure_Model::RECORD_STRUCTURE_MODE_SUMMARY);

		$headerFields = array();
		if(count($recordModelsByType['group']))
			$headerFields['mailingstreet2'] = array('class'=>'span2');
		$headerFields['accounttype'] = array('class'=>'span2');
		$headerFields['rsnnpai'] = array('class'=>'span1');
		$headerFields['mailingstreet'] = array('class'=>'span2');
		$headerFields['mailingzip'] = array('class'=>'span1');
		$headerFields['mailingcity'] = array('class'=>'span2');
		$headerFields['email'] = array('class'=>'span2');
		$headerFields['createdtime'] = array('class'=>'span2');
		$headerFields['modifiedtime'] = array('class'=>'span2');
		
		$reldataValues = array('Famille', 'Adhérent', 'Membre du CA', 'Relation', 'Aucune relation', 'Sans relation', 'Salarié', 'Ancien');
		
		$viewer = $this->getViewer($request);
		$viewer->assign('RECORDS', $records);
		$viewer->assign('RECORDMODELS', $recordModels);
		$viewer->assign('RECORDS', $records);
		$viewer->assign('FIELDS', $fieldModels);
		if($recordStrucure)
			$viewer->assign('SUMMARY_RECORD_STRUCTURE', $recordStrucure->getStructure());
		$viewer->assign('HEADER_FIELDS', $headerFields);
		$viewer->assign('RELDATA_VALUES', $reldataValues);
		$viewer->assign('MODULE', $module);
		$viewer->view('DuplicatesRelations.tpl', $module);
	}



	
	
	
}
