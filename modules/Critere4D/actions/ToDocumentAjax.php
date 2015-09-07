<?php
/*+***********************************************************************************
 * ED150907
 *************************************************************************************/

class Critere4D_ToDocumentAjax_Action extends Vtiger_MassDelete_Action {

	public function __construct() {
		parent::__construct();
		$this->exposeMethod('tranferToDocument');
		$this->exposeMethod('tranformAsNewDocument');
	}

	public function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}
	
	/**
	 * Function to transfer a Critere4D in a Document
	 * Transfer contact relations too.
	 * Finaly delete the Critere4D
	 * @param Vtiger_Request $request
	 */
	public function tranformAsNewDocument(Vtiger_Request $request) {
		$documentsModuleName = 'Documents';
		$moduleName = $request->getModule();
		if($request->get('viewname')){//From List view
			$recordIds = $this->getDocumentsListViewEntries($request);
		}
		else { //From Detail view
			$recordId = $request->get('record');
			$recordIds = $request->get('record');
			if(is_string($recordId))
				$recordIds = explode(',', str_replace(' ', '', $recordIds));
			if(!$recordIds && $recordId)
				$recordIds = array($recordId);
		}
		//var_dump($prev_value);
		
		$response = new Vtiger_Response();
		
		$folderId = $request->get('folderId');
		if(!$folderId){
			$response->setError('tranformAsNewDocument : folderId is missing');
			$response->emit();
			return;
		}

		if ($recordIds) {
			$db = PearDatabase::getInstance();
			$documentsModuleModel = Vtiger_Module_Model::getInstance($documentsModuleName);

			$responseData = array();
			foreach($recordIds as $recordId){
				if(is_object($recordId))
					$sourceRecordModel = $recordId;
				else
					$sourceRecordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
				
				$document = $this->cloneToDocument($sourceRecordModel, $documentsModuleName, $folderId);
				
				if(!$document){
					$response->setError("Impossible de créer un document");
					$response->emit();
					return;
				}
				
				if(!$this->transferRelationsToDocument($sourceRecordModel, $document->getId())){
					$response->setError("Impossible de transférer les relations");
					$response->emit();
					return;
				}
				
				//Suppression du critère
				$sourceRecordModel->delete();
			}

			$response->setResult(array(
							'href' => $document->getDetailViewUrl(),
							'data' => $responseData,
						));
		} else {
			$response->setError($code);
		}
		$response->emit();
	}
	
	/**
	 * Function to transfer a Critere4D in a Document
	 * @param Vtiger_Request $request
	 */
	public function tranferToDocument(Vtiger_Request $request) {
		$documentsModuleName = 'Documents';
		$moduleName = $request->getModule();
			
		$recordId = $request->get('record');
		$recordIds = $request->get('record');
		if(is_string($recordId))
			$recordIds = explode(',', str_replace(' ', '', $recordIds));
		if(!$recordIds && $recordId)
			$recordIds = array($recordId);
		
		//var_dump($prev_value);
		
		$response = new Vtiger_Response();
		
		$documentId = $request->get('documentId');
		if(!$documentId){
			$response->setError('tranferToDocument : documentId is missing');
			$response->emit();
			return;
		}
		$document = Vtiger_Record_Model::getInstanceById($documentId, $documentsModuleName);
		if(!$document){
			$response->setError("tranferToDocument : document $documentId does not exist");
			$response->emit();
			return;
		}
		
		if ($recordIds) {
			$db = PearDatabase::getInstance();
			$documentsModuleModel = Vtiger_Module_Model::getInstance($documentsModuleName);

			$responseData = array();
			foreach($recordIds as $recordId){
				if(is_object($recordId))
					$sourceRecordModel = $recordId;
				else
					$sourceRecordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
				
				if(!$document){
					$response->setError("Impossible de créer un document");
					$response->emit();
					return;
				}
				
				if(!$this->transferRelationsToDocument($sourceRecordModel, $documentId)){
					$response->setError("Impossible de transférer les relations");
					$response->emit();
					return;
				}
			}

			$response->setResult(array(
					'href' => $document->getDetailViewUrl(),
					'data' => $responseData,
				));
		} else {
			$response->setError($code);
		}
		$response->emit();
	}
	
	function getDocumentsListViewEntries(Vtiger_Request $request){
		
		$moduleName = 'Documents';
		$request->set('module', $moduleName);
		$records = $this->getRecordsListFromRequest($request);
		return $records;
	
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		$pagingModel->set('limit', 9999);
		
		$viewId = $request->get('viewname');
		$listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $viewId);
		
		$searchKey = $request->get('search_key');
		$searchValue = $request->get('search_value');
		$searchInput = $request->get('search_input');
		$operator = $request->get('operator');
			
		if(!empty($operator)) {
			$listViewModel->set('operator', $operator);
		}
		//ED150414 $searchValue == 0 is acceptable
		if(!empty($searchKey) && (!empty($searchValue) || ($searchValue == '0'))) {
			$listViewModel->set('search_key', $searchKey);
			$listViewModel->set('search_value', $searchValue);
			$listViewModel->set('search_input', $searchInput);
		}
		
		return $listViewModel->getListViewEntries($pagingModel);
	}
	
	function cloneToDocument($sourceRecordModel, $documentsModuleName, $folderId){
		$db = PearDatabase::getInstance();
		
		$document = Vtiger_Record_Model::getCleanInstance($documentsModuleName);
		$fieldMapping = array(
			array('nom', 'notes_title'),
			array('nom', 'critere4d'),
			array('commentaire', 'notecontent'),
		);
		foreach($fieldMapping as $map)
			$document->set($map[1], $sourceRecordModel->get($map[0]));
		$document->set('folderid', $folderId);
		$document->set('filestatus', 1);
		$document->saveInBulkMode();
		$documentId = $document->getId();
		
		//set original dates
		$query = "UPDATE vtiger_crmentity
			SET createdtime = ?
			, modifiedtime = ?
			WHERE crmid = ?
		";
		$result = $db->pquery($query, array(
							$sourceRecordModel->get('createdtime')
							, $sourceRecordModel->get('modifiedtime')
							, $documentId));
		if(!$result){
			return;
		}
		return $document;
	}
	
	function transferRelationsToDocument($sourceRecordModel, $documentId){
		$db = PearDatabase::getInstance();
		
		//transfer Related
		$query = "INSERT INTO `vtiger_senotesrel`(`crmid`, `notesid`, `dateapplication`, `data`)
			SELECT `contactid`, ?, `dateapplication`, `data`
				FROM `vtiger_critere4dcontrel` 
				WHERE `critere4did` = ?
			ON DUPLICATE KEY
				UPDATE data = `vtiger_critere4dcontrel`.data
		";
		$result = $db->pquery($query, array(
							$documentId,
							$sourceRecordModel->getId()
							));
		if(!$result){
			return;
		}
		
		//delete Related
		$query = "DELETE FROM `vtiger_critere4dcontrel` 
				WHERE `critere4did` = ?
		";
		$result = $db->pquery($query, array(
								$sourceRecordModel->getId(),
							));
		if(!$result){
			return;
		}
		return true;
	}

	
	
}
