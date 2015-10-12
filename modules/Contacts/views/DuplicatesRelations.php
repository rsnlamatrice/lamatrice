<?php
/**************************************************************************************
 * ED151009
 **************************************************************************************/

class Contacts_DuplicatesRelations_View extends Vtiger_DuplicatesRelations_View {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('saveRelations');
	}
	
	/**
	 * Function to get the list of Script models to be included
	 * @param Vtiger_Request $request
	 * @return <Array> - List of Vtiger_JsScript_Model instances
	 */
	function getHeaderScripts(Vtiger_Request $request) {
		$headerScriptInstances = parent::getHeaderScripts($request);
		$moduleName = $request->getModule();

		$jsFileNames = array(
			"modules.$moduleName.resources.DuplicatesRelations",
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}
	
	function saveRelations(Vtiger_Request $request){
		//var_dump($request);
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		
		$queryParams = array();
		$query = 'INSERT INTO vtiger_contactscontrel (`contactid`, `relcontid`, `contreltype`, `dateapplication`, `data`)
			VALUES ';
		$clearQueryParams = array();
		$clearQuery = 'DELETE FROM vtiger_duplicateentities
			WHERE ';
		
		$nRelation = 0;
		$nCreateRelation = 0;
		$relData = false;
		$mergeRecords = array();
		foreach($request->getAll() as $param => $value){
			if(preg_match('/^createRelation-\d+-\d+$/', $param)){
				$recordIds = array();
				preg_match_all('/^createRelation-(\d+)-(\d+)$/', $param, $recordIds);
				if($value == 'relation') {
					$relData = $request->get('reldata-'.$recordIds[1][0].'-'.$recordIds[2][0]);
					if($nCreateRelation > 0)
						$query .= ', ';
					$query .= '(?, ?, ?, NOW(), NULL)';
					$params[] = $recordIds[1][0];
					$params[] = $recordIds[2][0];
					$params[] = $relData;
					$nCreateRelation++;
				}
				elseif($value == 'commonAccount'){
					//TODO compte-commun
					$view = new Contacts_MergeAccounts_View();
					$mergeRecords[$recordIds[1][0]] = $recordIds[1][0];
					$mergeRecords[$recordIds[2][0]] = $recordIds[2][0];
					
				}
				else {
					var_dump(__FILE__, $value);
				}
				
				//Clear
				if($nRelation++ > 0)
					$clearQuery .= ' OR ';
				$clearQuery .= '(crmid1 = ? AND crmid2 = ?) OR (crmid2 = ? AND crmid1 = ?)
				';
				$clearQueryParams[] = $recordIds[1][0];
				$clearQueryParams[] = $recordIds[2][0];
				$clearQueryParams[] = $recordIds[1][0];
				$clearQueryParams[] = $recordIds[2][0];
			}
		}
		
		$db = PearDatabase::getInstance();
		
		if($nCreateRelation){
			$result = $db->pquery($query,$params);
			if(!$result){
				var_dump($query, $params);
				$db->echoError();
				return;
			}
			$updated = $db->getAffectedRowCount($result);
		}
		
		if($nRelation){
			$result = $db->pquery($clearQuery,$clearQueryParams);
			if(!$result){
				var_dump($clearQuery,$clearQueryParams);
				$db->echoError();
				return;
			}
		}
		
		if($mergeRecords){
			$request->set('records', array_keys($mergeRecords));
			$view->preProcess($request);
			$view->process($request);
			$view->postProcess($request);
		}
		else {
			//die('Modifications : '.$updated);
			$response = new Vtiger_Response();
			$response->setResult(array('Modifications' => $updated));
			$response->emit();
		}
		
	}
}
