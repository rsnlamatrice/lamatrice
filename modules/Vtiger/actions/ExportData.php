<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_ExportData_Action extends Vtiger_Mass_Action {

	function checkPermission(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		$currentUserPriviligesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		if(!$currentUserPriviligesModel->hasModuleActionPermission($moduleModel->getId(), 'Export')) {
			throw new AppException('LBL_PERMISSION_DENIED');
		}
	}

	/**
	 * Function is called by the controller
	 * @param Vtiger_Request $request
	 */
	function process(Vtiger_Request $request) {
		$this->ExportData($request);
	}

	protected $moduleInstance;
	protected $focus;

	/**
	 * Function exports the data based on the mode
	 * @param Vtiger_Request $request
	 */
	function ExportData(Vtiger_Request $request) {
		$db = PearDatabase::getInstance();
		$isPreview = $request->get('preview');
		$moduleName = $request->get('source_module');

		$this->moduleInstance = Vtiger_Module_Model::getInstance($moduleName);
		$this->moduleFieldInstances = $this->moduleInstance->getFields();
		$this->focus = CRMEntity::getInstance($moduleName);

		$query = $this->getExportQuery($request);
		$result = $db->pquery($query, array());

		$headers = array();
		//Query generator set this when generating the query
		if(!empty($this->accessibleFields)) {
			$accessiblePresenceValue = array(0,2);
			foreach($this->accessibleFields as $fieldName) {
				$fieldModel = $this->moduleFieldInstances[$fieldName];
				// Check added as querygenerator is not checking this for admin users
				$presence = $fieldModel->get('presence');
				if(in_array($presence, $accessiblePresenceValue)) {
					$headers[] = $fieldModel->get('label');
				}
			}
		} else {
			foreach($this->moduleFieldInstances as $field) $headers[] = $field->get('label');
		}
		$translatedHeaders = array();
		foreach($headers as $header) $translatedHeaders[] = vtranslate(html_entity_decode($header, ENT_QUOTES), $moduleName);

		$entries = array();
		for($j=0; $j<$db->num_rows($result); $j++) {
			$entries[] = $this->sanitizeValues($db->fetchByAssoc($result, $j));
		}

		if ($isPreview) {
			$this->displayPrewiew($request, $translatedHeaders, $entries);
		} else {
			$this->output($request, $translatedHeaders, $entries);
		}
	}

	/**
	 * Function that generates Export Query based on the mode
	 * @param Vtiger_Request $request
	 * @return <String> export query
	 */
	function getExportQuery(Vtiger_Request $request) {//depand of the export type...
		$queryorderby = "";
		$querylimit = "";
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$mode = $request->getMode();
		$cvId = $request->get('viewname');
		$moduleName = $request->get('source_module');
		$isPreview = $request->get('preview');
		$searchKey = $request->get('search_key');
		$searchValue = $request->get('search_value');
		$operator = $request->get('operator');

		$queryGenerator = new QueryGenerator($moduleName, $currentUser);
		$queryGenerator->initForCustomViewById($cvId);
		$fieldInstances = $this->moduleFieldInstances;

        $accessiblePresenceValue = array(0,2);
		foreach($fieldInstances as $field) {
            // Check added as querygenerator is not checking this for admin users
            $presence = $field->get('presence');
            if(in_array($presence, $accessiblePresenceValue)) {
                $fields[] = $field->getName();
            }
        }
		$queryGenerator->setFields($fields);
		
		if(!empty($searchKey)) {
			$queryGenerator->addUserSearchConditions(array('search_field' => $searchKey, 'search_text' => $searchValue, 'operator' => $operator));
		}

		$query = $queryGenerator->getQuery();

		if(in_array($moduleName, getInventoryModules())){
			$query = $this->moduleInstance->getExportQuery($this->focus, $query);
		}

		$this->accessibleFields = $queryGenerator->getFields();


		switch($mode) {
			case 'ExportAllData' :	break;

			case 'ExportCurrentPage' :
										$pagingModel = new Vtiger_Paging_Model();
										$limit = $pagingModel->getPageLimit();

										$currentPage = $request->get('page');
										if(empty($currentPage)) $currentPage = 1;

										$currentPageStart = ($currentPage - 1) * $limit;
										if ($currentPageStart < 0) $currentPageStart = 0;
										$querylimit = ' LIMIT '.$currentPageStart.','.$limit;
										break;

			case 'ExportSelectedRecords' :	$idList = $this->getRecordsListFromRequest($request);
											$baseTable = $this->moduleInstance->get('basetable');
											$baseTableColumnId = $this->moduleInstance->get('basetableid');
											if(!empty($idList)) {
												if(!empty($baseTable) && !empty($baseTableColumnId)) {
													$idList = implode(',' , $idList);
													$query .= ' AND '.$baseTable.'.'.$baseTableColumnId.' IN ('.$idList.')';
												}
											} else {
												$query .= ' AND '.$baseTable.'.'.$baseTableColumnId.' NOT IN ('.implode(',',$request->get('excluded_ids')).')';
											}

											break;


			default :	break;
		}

		$queryorderby = $this->getQueryOrderBy($request);

		if ($isPreview && !$querylimit) {
			$querylimit = ' LIMIT 0, 20';
		}

		$query .= $queryorderby . $querylimit;

		return $query;
	}

	function getQueryOrderBy($request) {
		$moduleName = $request->get('source_module');
		$orderBy = Vtiger_Util_Helper::validateStringForSql($request->get('orderby'));
		$sortOrder = Vtiger_Util_Helper::validateStringForSql($request->get('sortorder'));

		//List view will be exported on recently created/modified records
		if(empty($orderBy) && empty($sortOrder)){
			switch($moduleName){
			case "Users":
				break;
			case "RSNMediaRelations":
				$orderBy = 'daterelation';
				$sortOrder = 'DESC';
				break;
			default:
				$orderBy = 'modifiedtime';
				$sortOrder = 'DESC';
				break;
			}
		}

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		if(!empty($orderBy)){
		    $columnFieldMapping = $moduleModel->getColumnFieldMapping();
		    $orderByFieldName = $columnFieldMapping[$orderBy];
		    $orderByFieldModel = $moduleModel->getField($orderByFieldName);
		    if($orderByFieldModel &&
				(	$orderByFieldModel->getFieldDataType() == Vtiger_Field_Model::REFERENCE_TYPE
				||	preg_match('/cf$/', $orderByFieldModel->table)//ED150622 TODO more than *cf
				)
			){
				//IF it is reference add it in the where fields so that from clause will be having join of the table
				$queryGenerator = $this->get('query_generator');
				$queryGenerator->addWhereField($orderByFieldName);
				//$queryGenerator->whereFields[] = $orderByFieldName;
		    }
		}

	    if ($orderByFieldModel && $orderByFieldModel->isReferenceField()) {
			$referenceModules = $orderByFieldModel->getReferenceList();
			$referenceNameFieldOrderBy = array();

			foreach ($referenceModules as $referenceModuleName) {
			    $referenceModuleModel = Vtiger_Module_Model::getInstance($referenceModuleName);
			    $referenceNameFields = $referenceModuleModel->getNameFields();
			    $columnList = array();

			    foreach ($referenceNameFields as $nameField) {
					$fieldModel = $referenceModuleModel->getField($nameField);
					$columnList[] = $fieldModel->get('table').$orderByFieldModel->getName().'.'.$fieldModel->get('column');
			    }

			    if (count($columnList) > 1) {
					$referenceNameFieldOrderBy[] = getSqlForNameInDisplayFormat(array('first_name'=>$columnList[0],'last_name'=>$columnList[1]),'Users').' '.$sortOrder;
			    } else {
					$referenceNameFieldOrderBy[] = implode('', $columnList).' '.$sortOrder ;
			    }
			}

			return ' ORDER BY '. implode(',',$referenceNameFieldOrderBy);
	    }

		return ' ORDER BY '. $orderBy . ' ' .$sortOrder;
	}

	/**
	 * Function returns the export type - This can be extended to support different file exports
	 * @param Vtiger_Request $request
	 * @return <String>
	 */
	function getExportContentType(Vtiger_Request $request) {
		$type = $request->get('export_type');
		if(empty($type)) {
			return 'text/csv';
		}
	}

	/**
	 * AV1511
	 */
	function getExportFileName($request) {
		$moduleName = $request->get('source_module');
		return str_replace(' ','_',vtranslate($moduleName, $moduleName));
	}

	/**
	 * Function that create the exported file
	 * @param Vtiger_Request $request
	 * @param <Array> $headers - output file header
	 * @param <Array> $entries - outfput file data
	 */
	function output($request, $headers, $entries) {
		$fileName = $this->getExportFileName($request);
		$exportType = $this->getExportContentType($request);

		//ED150922
		$csvseparator = $this->getCSVSeparator();
		
		header("Content-Disposition:attachment;filename=$fileName.csv");
		header("Content-Type:$exportType;charset=UTF-8");
		header("Expires: Mon, 31 Dec 2000 00:00:00 GMT" );
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT" );
		header("Cache-Control: post-check=0, pre-check=0", false );

		//AV151026
		$out = fopen('php://output', 'w');
		fputcsv($out, $headers, $csvseparator);

		foreach($entries as $row) {
			fputcsv($out, $row, $csvseparator);
		}

		fclose($out);
	}

	function displayPrewiew($request, $headers, $entries) {
		// echo 'display preview';
		// var_dump($headers);
		// var_dump($entries);

		$module = $request->get('module');

		$viewer = new Vtiger_Viewer();
		$viewer->assign('DISPLAY_HEADER', (is_array($headers) && sizeof($headers) > 0));
		$viewer->assign('HEADERS', $headers);
		$viewer->assign('ENTRIES', $entries);

		$viewer->view('ExportPreview.tpl', $module);

		// fputcsv($out, $headers, $csvseparator);
		// foreach($entries as $row) {
		// 	fputcsv($out, $row, $csvseparator);
		// }
	}	
	
	//ED150922
	function getCSVSeparator(){
		return "\t";	
	}

	//ED150922
	function escapeForCSV($string){
		if(strpos($string, "\t") !== FALSE)
			$string = str_replace("\t", "\\t", $string);
		if(strpos($string, '"') !== FALSE)
			$string = str_replace('"', '\'\'', $string);
		return $string;
	}

	protected $picklistValues;
	protected $fieldArray;
	protected $fieldDataTypeCache = array();
	/**
	 * this function takes in an array of values for an user and sanitizes it for export
	 * @param array $arr - the array of values
	 * @param exportStructure because of inheritance
	 */
	function sanitizeValues($arr, $exportStructure = false){
		$db = PearDatabase::getInstance();
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$roleid = $currentUser->get('roleid');
		if(empty ($this->fieldArray)){
			$this->fieldArray = $this->moduleFieldInstances;
			foreach($this->fieldArray as $fieldName => $fieldObj){
				//In database we have same column name in two tables. - inventory modules only
				if($fieldObj->get('table') == 'vtiger_inventoryproductrel' && ($fieldName == 'discount_amount' || $fieldName == 'discount_percent')){
					$fieldName = 'item_'.$fieldName;
					$this->fieldArray[$fieldName] = $fieldObj;
				} else {
					$columnName = $fieldObj->get('column');
					$this->fieldArray[$columnName] = $fieldObj;
				}
			}
		}
		$moduleName = $this->moduleInstance->getName();
		foreach($arr as $fieldName=>&$value){
			if(isset($this->fieldArray[$fieldName])){
				$fieldInfo = $this->fieldArray[$fieldName];
			}else {
				unset($arr[$fieldName]);
				continue;
			}
			$value = decode_html($value);
			$uitype = $fieldInfo->get('uitype');
			$fieldname = $fieldInfo->get('name');

			if(!$this->fieldDataTypeCache[$fieldName]) {
				$this->fieldDataTypeCache[$fieldName] = $fieldInfo->getFieldDataType();
			}
			$type = $this->fieldDataTypeCache[$fieldName];
			
			if($fieldname != 'hdnTaxType' && ($uitype == 15 || $uitype == 16 || $uitype == 33)){
				if(empty($this->picklistValues[$fieldname])){
					$this->picklistValues[$fieldname] = $this->fieldArray[$fieldname]->getPicklistValues();
				}
				// If the value being exported is accessible to current user
				// or the picklist is multiselect type.
				if($uitype == 33 || $uitype == 16 || in_array($value,$this->picklistValues[$fieldname])){
					// NOTE: multipicklist (uitype=33) values will be concatenated with |# delim
					$value = trim($value);
				} else {
					$value = '';
				}
			} elseif($uitype == 52 || $type == 'owner') {
				$value = Vtiger_Util_Helper::getOwnerName($value);
			}elseif($type == 'reference'){
				$value = trim($value);
				if(!empty($value)) {
					$parent_module = getSalesEntityType($value);
					$displayValueArray = getEntityName($parent_module, $value);
					if(!empty($displayValueArray)){
						foreach($displayValueArray as $k=>$v){
							$displayValue = $v;
						}
					}
					if(!empty($parent_module) && !empty($displayValue)){
						$value = $parent_module."::::".$displayValue;
					}else{
						$value = "";
					}
				} else {
					$value = '';
				}
			} elseif($uitype == 72 || $uitype == 71) {
                $value = CurrencyField::convertToUserFormat($value, null, true, true);
			} elseif($uitype == 7 && $fieldInfo->get('typeofdata') == 'N~O' || $uitype == 9){
				$value = decimalFormat($value);
			}
			if($moduleName == 'Documents' && $fieldname == 'description'){
				$value = strip_tags($value);
				$value = str_replace('&nbsp;','',$value);
				array_push($new_arr,$value);
			}
		}

		return $arr;
	}
}