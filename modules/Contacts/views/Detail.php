<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Contacts_Detail_View extends Accounts_Detail_View {

	
	public function showModuleDetailView(Vtiger_Request $request) {
		$recordId = $request->get('record');
		$moduleName = $request->getModule();
		
		$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
		
		/* ED141005
		ne fonctionne pas pour que la valeur soit récupérée dans les .tpl 
		$values = $recordModel->getPicklistValuesDetails('isgroup');
		$key = $recordModel->get('isgroup');
		$recordModel->set('isgroup', $values[$key]['label']);*/
		
		$viewer = $this->getViewer($request);
		$viewer->assign('IMAGE_DETAILS', $recordModel->getImageDetails());

		return parent::showModuleDetailView($request);
	}
	
	/**
	 * ED141210
	 * en ajoutant NO_ACTIVITIES_WIDGET on désactive le chargement des Events
	 */
	public function process(Vtiger_Request $request) {
		
		$viewer = $this->getViewer($request);
		/* ED141210 court-circuite les activités
		TODO : mettre en paramètre */
		$viewer->assign('NO_ACTIVITIES_WIDGET', true);

		return parent::process($request);
	}
	
	function preProcessDisplay(Vtiger_Request $request) {
		$this->initStatisticsResults($request);
		parent::preProcessDisplay($request);
	}
	
	function initStatisticsResults(Vtiger_Request $request){
		$recordId = $request->get('record');
		$moduleName = $request->getModule();
		
		$relatedStatsTablesNames = RSNStatistics_Utils_Helper::getRelatedStatsTablesNames($moduleName);
		$statsFieldInfos = RSNStatistics_Utils_Helper::getRelatedStatsFields(false, $moduleName, 'moduleheader');

		$year = date('Y');
		$years = array($year, $year - 1);
		$yearCodes = array();
		$yearsEmptyValues = array();
		foreach($years as $year){
			$yearCodes[] = 'Annuelle-' . $year;
			$yearsEmptyValues[$year] = null;
		}
		
		$query = "SELECT *
			FROM " . $relatedStatsTablesNames[0] . "
			WHERE crmid = ?
			AND code IN (".generateQuestionMarks($yearCodes).")
			AND rsnfiltrestatistiqueid = 0
			AND filterid = 0
			ORDER BY code DESC
			LIMIT ".count($yearCodes)."
		";
			
		$params = array($recordId);
		$params = array_merge($params, $yearCodes);
		global $adb;
		$result = $adb->pquery($query, $params);
		if(!$result)
			$adb->echoError();
		$statsFields = array();
		$rowIndex = 0;
		while ($row = $adb->fetch_row($result, $rowIndex++)){
			foreach($statsFieldInfos as $statsFieldInfo)
				//TODO if (showInDetailViewHeader) par exemple, le panier moyen, c'est moyen...
				if(array_key_exists($statsFieldInfo['uniquecode'], $row)){
					$unit = '';
					$value = RSNStatisticsResults_Field_Model::getDisplayValueForFieldType($row[$statsFieldInfo['uniquecode']], $statsFieldInfo['fieldtype'], $unit);
					if(!$statsFields[$statsFieldInfo['fieldname']])
						$statsFields[$statsFieldInfo['fieldname']] = $yearsEmptyValues;
					$statsFields[$statsFieldInfo['fieldname']][$row['name']] = array('value' => $value
																					 , 'unit' => $unit);
				}
		}
		
		$viewer = $this->getViewer($request);
		$viewer->assign('STATISTICS_FIELDS', $statsFields);
		
	}
	
	
	
	/* ED151116
	 * Permet le tri des champs
	 */
	function getRecordStructure($recordStructure){
		$structure = $recordStructure->getStructure();
		uksort($structure['SUMMARY_FIELDS'], array('Contacts_Detail_View', 'uksortSummaryFields'));
		return $structure;
	}
	static function uksortSummaryFields($a, $b){
		$orderedFieldNames = array(
			'contact_no',
			'lastname',
			'firstname',
			'isgroup',
			'account_id',
			'reference',
			'contacttype',
			'leadsource',
			'email',
			'phone',
			'modbile',
			'homephone',
			'emailoptout',
			'donotcall',
			'donotprospect',
			'donotrelanceadh',
			'donotappeldoncourrier',
			'donotrelanceabo',
			'donotappeldonweb',
			'donototherdocuments',
			'description',
			'rsnnpai',
			'mailingzip',
			'mailingstreet2',
			'mailingcity',
			'mailingcountry',
			'longitude',
			'latitude',
		);
		$apos  = array_search($a, $orderedFieldNames);
		$bpos  = array_search($b, $orderedFieldNames);
	
		if ($apos === false) return PHP_INT_MAX;
		if ($bpos === false) return -1*PHP_INT_MAX;
	
		return ($apos - $bpos);
	}
}
