<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

class Vtiger_Popup_View extends Vtiger_Footer_View {
	protected $listViewEntries = false;
	protected $listViewHeaders = false;

	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$currentUserPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPrivilegesModel->hasModulePermission($moduleModel->getId())) {
			throw new AppException(vtranslate($moduleName).' '.vtranslate('LBL_NOT_ACCESSIBLE'));
		}
	}

	/**
	 * Function returns the module name for which the popup should be initialized
	 * @param Vtiger_request $request
	 * @return <String>
	 */
	function getModule(Vtiger_request $request) {
		$moduleName = $request->getModule();
		return $moduleName;
	}

	/* ED160101 */
	function preProcess(Vtiger_Request $request) {		
		$this->ensureSearchParamsAreArray($request);
		parent::preProcess($request);
	}
	
	function process (Vtiger_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $this->getModule($request);
		$companyDetails = Vtiger_CompanyDetails_Model::getInstanceById();
		$companyLogo = $companyDetails->getLogo();

		$this->initializeListViewContents($request, $viewer);

		$viewer->assign('COMPANY_LOGO',$companyLogo);

		$template = $this->getTemplateName($request);
		
		$viewer->view($template, $moduleName);
	}
	
	function getTemplateName (Vtiger_Request $request) {
		return 'Popup.tpl';
	}

	function postProcess(Vtiger_Request $request) {
		$viewer = $this->getViewer ($request);
		$moduleName = $this->getModule($request);
		$viewer->view('PopupFooter.tpl', $moduleName);
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
			'modules.Vtiger.resources.Popup',
			"modules.$moduleName.resources.Popup",
			'modules.Vtiger.resources.BaseList',
			"modules.$moduleName.resources.BaseList",
			'libraries.jquery.jquery_windowmsg',
			'modules.Vtiger.resources.validator.BaseValidator',
			'modules.Vtiger.resources.validator.FieldValidator',
			"modules.$moduleName.resources.validator.FieldValidator"
		);

		$jsScriptInstances = $this->checkAndConvertJsScripts($jsFileNames);
		$headerScriptInstances = array_merge($headerScriptInstances, $jsScriptInstances);
		return $headerScriptInstances;
	}

	/*
	 * Function to initialize the required data in smarty to display the List View Contents
	 */
	public function initializeListViewContents(Vtiger_Request $request, Vtiger_Viewer $viewer) {
		$moduleName = $this->getModule($request);
		$cvId = $request->get('cvid');
		$pageNumber = $request->get('page');
		$orderBy = $request->get('orderby');
		$sortOrder = $request->get('sortorder');
		$sourceModule = $request->get('src_module');
		$sourceField = $request->get('src_field');
		$sourceRecord = $request->get('src_record');
		$searchKey = $request->get('search_key');
		$searchValue = $request->get('search_value');
		$searchInput = $request->get('search_input');
		$searchOperator = $request->get('operator');
		$currencyId = $request->get('currency_id');

		//To handle special operation when selecting record from Popup
		$getUrl = $request->get('get_url');

		//Check whether the request is in multi select mode
		$multiSelectMode = $request->get('multi_select');
		if(empty($multiSelectMode)) {
			$multiSelectMode = false;
		}

		if(empty($cvId)) {
			$cvId = '0';
		}
		if(empty ($pageNumber)){
			$pageNumber = '1';
		}

		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$listViewModel = Vtiger_ListView_Model::getInstanceForPopup($moduleName);
		$recordStructureInstance = Vtiger_RecordStructure_Model::getInstanceForModule($moduleModel);
		
		if(!empty($orderBy)) {
			$listViewModel->set('orderby', $orderBy);
			$listViewModel->set('sortorder', $sortOrder);
		}
		if(!empty($sourceModule)) {
			$listViewModel->set('src_module', $sourceModule);
			$listViewModel->set('src_field', $sourceField);
			$listViewModel->set('src_record', $sourceRecord);
		}
		if((!empty($searchKey)) && (!empty($searchValue)))  {
			$listViewModel->set('search_key', $searchKey);
			$listViewModel->set('search_value', $searchValue);
			$listViewModel->set('search_input', $searchInput);
			$listViewModel->set('operator', $searchOperator);//ED150628
		}
		
		//ED150628 : for related only
		$src_viewname = $request->get('src_viewname');
		if(!empty($src_viewname))  {
			$listViewModel->set('src_viewname', $src_viewname);
			$listViewModel->set('src_selected_ids', $request->get('src_selected_ids'));
			$listViewModel->set('src_excluded_ids', $request->get('src_excluded_ids'));
		}
		$src_search_key = $request->get('src_search_key');
		if(!empty($src_search_key))  {
			$listViewModel->set('src_search_key', $src_search_key);
			$listViewModel->set('src_search_value', $request->get('src_search_value'));
			$listViewModel->set('src_search_input', $request->get('src_search_input'));
			$listViewModel->set('src_operator', $request->get('src_operator'));
		}

		if(!$this->listViewHeaders){
			//en général, c'est dans module->getPopupFields() { return CRMEntity->search_fields_name;}
			$this->listViewHeaders = $listViewModel->getListViewHeaders();
		}
		
		//ED151105
		$alphabetFields = $listViewModel->getAlphabetFieldsForPopup($this->listViewHeaders);
		
		//var_dump(get_class($listViewModel));
		if(!$this->listViewEntries){
			$this->listViewEntries = $listViewModel->getListViewEntries($pagingModel);
		}
		//var_dump($this->listViewEntries);
		$noOfEntries = count($this->listViewEntries);

		if(empty($sortOrder)){
			$sortOrder = "ASC";
		}
		if($sortOrder == "ASC"){
			$nextSortOrder = "DESC";
			$sortImage = "downArrowSmall.png";
		}else{
			$nextSortOrder = "ASC";
			$sortImage = "upArrowSmall.png";
		}
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('MODULE_NAME',$moduleName);

		$viewer->assign('SOURCE_MODULE', $sourceModule);
		$viewer->assign('SOURCE_FIELD', $sourceField);
		$viewer->assign('SOURCE_RECORD', $sourceRecord);

		$viewer->assign('SEARCH_KEY', $searchKey);
		$viewer->assign('SEARCH_VALUE', $searchValue);
		$viewer->assign('SEARCH_INPUT', $searchInput);
		$viewer->assign('SEARCH_OPERATOR', $searchOperator);

		$viewer->assign('ORDER_BY',$orderBy);
		$viewer->assign('SORT_ORDER',$sortOrder);
		$viewer->assign('NEXT_SORT_ORDER',$nextSortOrder);
		$viewer->assign('SORT_IMAGE',$sortImage);
		$viewer->assign('GETURL', $getUrl);
		$viewer->assign('CURRENCY_ID', $currencyId);

		$viewer->assign('RECORD_STRUCTURE_MODEL', $recordStructureInstance);
		/* ED141223 *///$viewer->assign('RECORD_STRUCTURE', $recordStructureInstance->getStructure());
		$viewer->assign('RECORD_STRUCTURE', $this->getOrderedStructure($moduleName, $recordStructureInstance));
		
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('PAGE_NUMBER',$pageNumber);

		$viewer->assign('LISTVIEW_ENTIRES_COUNT',$noOfEntries);
		$viewer->assign('LISTVIEW_HEADERS', $this->listViewHeaders);
		$viewer->assign('LISTVIEW_ENTRIES', $this->listViewEntries);
		
		/*ED151105*/
		$viewer->assign('ALPHABET_FIELDS', $alphabetFields);
		
		if (PerformancePrefs::getBoolean('LISTVIEW_COMPUTE_PAGE_COUNT', false)) {
			if(!$this->listViewCount){
				$this->listViewCount = $listViewModel->getListViewCount();
			}
			$totalCount = $this->listViewCount;
			$pageLimit = $pagingModel->getPageLimit();
			$pageCount = ceil((int) $totalCount / (int) $pageLimit);

			if($pageCount == 0){
				$pageCount = 1;
			}
			$viewer->assign('PAGE_COUNT', $pageCount);
			$viewer->assign('LISTVIEW_COUNT', $totalCount);
		}

		$viewer->assign('MULTI_SELECT', $multiSelectMode);
		$viewer->assign('CURRENT_USER_MODEL', Users_Record_Model::getCurrentUserModel());
	}
	
	/* ED141224
	 * tri des champs selon CRMEntity::$search_fields_name
	 * liste utilisée pour la recherche dans les popups
	 * TODO : reorder blocks
	 */
	function getOrderedStructure($focus, $recordStructureInstance){
		$blocks = $recordStructureInstance->getStructure();
		
		if(is_string($focus))
			$focus = CRMEntity::getInstance($focus);
		$sortedFields = $focus->search_fields_name;
		if(is_array($sortedFields))
			foreach($blocks as $blockkey => $fields){
				$newfields = null;
				foreach($sortedFields as $fieldkey => $fieldname){
					if(isset( $fields[$fieldname] )){
						$field = $fields[$fieldname];
						unset($fields[$fieldname]);
						if($newfields === null) $newfields = array();
						$newfields[$fieldname] = $field;
						unset($sortedFields[$fieldkey]);
					}
				}
				if($newfields !== null) 
					// passent en tête de liste
					$blocks[$blockkey] = array_merge($newfields, $fields);
			}
		return $blocks;
	}
	
	/**
	 * Function to get listView count
	 * @param Vtiger_Request $request
	 */
	function getListViewCount(Vtiger_Request $request){
		$moduleName = $this->getModule($request);
		$sourceModule = $request->get('src_module');
		$sourceField = $request->get('src_field');
		$sourceRecord = $request->get('src_record');
		$orderBy = $request->get('orderby');
		$sortOrder = $request->get('sortorder');

		$searchKey = $request->get('search_key');
		$searchValue = $request->get('search_value');

		$listViewModel = Vtiger_ListView_Model::getInstanceForPopup($moduleName);
		if(!empty($sourceModule)) {
			$listViewModel->set('src_module', $sourceModule);
			$listViewModel->set('src_field', $sourceField);
			$listViewModel->set('src_record', $sourceRecord);
		}
		
		if(!empty($orderBy)) {
			$listViewModel->set('orderby', $orderBy);
			$listViewModel->set('sortorder', $sortOrder);
		}
		if((!empty($searchKey)) && (!empty($searchValue)))  {
			$listViewModel->set('search_key', $searchKey);
			$listViewModel->set('search_value', $searchValue);
		}
		$count = $listViewModel->getListViewCount();

		return $count;
	}
	
	/**
	 * Function to get the page count for list
	 * @return total number of pages
	 */
	function getPageCount(Vtiger_Request $request){
		$listViewCount = $this->getListViewCount($request);
		$pagingModel = new Vtiger_Paging_Model();
		$pageLimit = $pagingModel->getPageLimit();
		$pageCount = ceil((int) $listViewCount / (int) $pageLimit);

		if($pageCount == 0){
			$pageCount = 1;
		}
		$result = array();
		$result['page'] = $pageCount;
		$result['numberOfRecords'] = $listViewCount;
		$response = new Vtiger_Response();
		$response->setResult($result);
		$response->emit();
	}
}