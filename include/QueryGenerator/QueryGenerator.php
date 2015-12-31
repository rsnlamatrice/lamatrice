<?php
/*+*******************************************************************************
 *  The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *
 *********************************************************************************/

require_once 'data/CRMEntity.php';
require_once 'modules/CustomView/CustomView.php';
require_once 'include/Webservices/Utils.php';
require_once 'include/Webservices/RelatedModuleMeta.php';

/**
 * Description of QueryGenerator
 *
 * @author MAK
 */
class QueryGenerator {
	private $module;
	private $customViewColumnList;
	private $stdFilterList;
	private $conditionals;
	private $manyToManyRelatedModuleConditions;
	private $groupType;
	private $whereFields;
	
	private $statistictsRelations;
	private $statisticsFields;
	
	/**
	 *
	 * @var VtigerCRMObjectMeta
	 */
	private $meta;
	/**
	 *
	 * @var Users
	 */
	private $user;
	private $advFilterList;
	
	//ED150308 : more advanced filters (given by code)
	private $advFilterListMore;
	
	private $fields;
	private $referenceModuleMetaInfo;
	private $moduleNameFields;
	private $referenceFieldInfoList;
	private $referenceFieldList;
	private $ownerFields;
	private $columns;
	private $fromClause;
	private $whereClause;
	private $query;
	private $groupInfo;
	public $conditionInstanceCount;
	private $conditionalWhere;
	public static $AND = 'AND';
	public static $OR = 'OR';
	private $customViewFields;
	/**
	 * Import Feature
	 */
	private $ignoreComma;
	public function __construct($module, $user) {
		$db = PearDatabase::getInstance();
		$this->module = $module;
		$this->customViewColumnList = null;
		$this->stdFilterList = null;
		$this->conditionals = array();
		$this->user = $user;
		$this->advFilterList = null;
		$this->fields = array();
		$this->referenceModuleMetaInfo = array();
		$this->moduleNameFields = array();
		$this->whereFields = array();
		$this->groupType = self::$AND;
		$this->meta = $this->getMeta($module);
		$this->moduleNameFields[$module] = $this->meta->getNameFields();
		$this->referenceFieldInfoList = $this->meta->getReferenceFieldDetails();
		$this->referenceFieldList = array_keys($this->referenceFieldInfoList);;
		$this->ownerFields = $this->meta->getOwnerFields();
		$this->columns = null;
		$this->fromClause = null;
		$this->whereClause = null;
		$this->query = null;
		$this->conditionalWhere = null;
		$this->groupInfo = '';
		$this->manyToManyRelatedModuleConditions = array();
		$this->conditionInstanceCount = 0;
		$this->customViewFields = array();
	}

	/**
	 *
	 * @param String:ModuleName $module
	 * @return EntityMeta
	 */
	public function getMeta($module) {
		$db = PearDatabase::getInstance();
		if (empty($this->referenceModuleMetaInfo[$module])) {
			$handler = vtws_getModuleHandlerFromName($module, $this->user);
			$meta = $handler->getMeta();
			$this->referenceModuleMetaInfo[$module] = $meta;
			$this->moduleNameFields[$module] = $meta->getNameFields();
		}
		return $this->referenceModuleMetaInfo[$module];
	}

	public function reset() {
		$this->fromClause = null;
		$this->whereClause = null;
		$this->columns = null;
		$this->query = null;
	}

	public function setFields($fields) {
		$this->fields = $fields;
	}

	public function getCustomViewFields() {
		return $this->customViewFields;
	}

	public function getFields() {
		return $this->fields;
	}

	/*ED150928*/
	public function addField($fieldName) {
		if(! in_array( $fieldName, $this->fields ))
			$this->fields[] = $fieldName;
	}

	public function getWhereFields() {
		return $this->whereFields;
	}

	public function addWhereField($fieldName) {
	    $this->whereFields[] = $fieldName;
	}

	public function getOwnerFieldList() {
		return $this->ownerFields;
	}

	public function getModuleNameFields($module) {
		return $this->moduleNameFields[$module];
	}

	public function getReferenceFieldList() {
		return $this->referenceFieldList;
	}

	public function getReferenceFieldInfoList() {
		return $this->referenceFieldInfoList;
	}

	public function getModule () {
		return $this->module;
	}

	public function getConditionalWhere() {
		return $this->conditionalWhere;
	}

	public function getDefaultCustomViewQuery() {
		$customView = new CustomView($this->module);
		$viewId = $customView->getViewId($this->module);
		return $this->getCustomViewQueryById($viewId);
	}

	public function initForDefaultCustomView() {
		$customView = new CustomView($this->module);
		$viewId = $customView->getViewId($this->module);
		$this->initForCustomViewById($viewId);
	}

	/** ED150910
	 * Initialize for the "All" custom view
	 */
	public function initForAllCustomView() {
		$customView = new CustomView($this->module);
		$viewId = $customView->getViewIdByName('All', $this->module);
		$this->initForCustomViewById($viewId);
	}

	public function initForCustomViewById($viewId) {
		
		/* ED150121
		 * on ne passe jamais ici lors du chargement initial de la page
		 * ED150910
		 * 	SIC...
		 */
					
		$customView = new CustomView($this->module);
		$this->customViewColumnList = $customView->getColumnsListByCvid($viewId);
		foreach ($this->customViewColumnList as $customViewColumnInfo) {
			$details = explode(':', $customViewColumnInfo);
			if(empty($details[2]) && $details[1] == 'crmid' && $details[0] == 'vtiger_crmentity') {
				$name = 'id';
				$this->customViewFields[] = $name;
			} else {
				$this->fields[] = $details[2];
				$this->customViewFields[] = $details[2];
			}
		}

		if($this->module == 'Calendar' && !in_array('activitytype', $this->fields)) {
			$this->fields[] = 'activitytype';
		}

		if($this->module == 'Documents') {
			if(in_array('filename', $this->fields)) {
				if(!in_array('filelocationtype', $this->fields)) {
					$this->fields[] = 'filelocationtype';
				}
				if(!in_array('filestatus', $this->fields)) {
					$this->fields[] = 'filestatus';
				}
			}
		}
		$this->fields[] = 'id';

		$this->stdFilterList = $customView->getStdFilterByCvid($viewId);
		$this->advFilterList = $customView->getAdvFilterByCvid($viewId);

		if(is_array($this->stdFilterList)) {
			$value = array();
			if(!empty($this->stdFilterList['columnname'])) {
				$this->startGroup('');
				$name = explode(':',$this->stdFilterList['columnname']);
				$name = $name[2];
				$value[] = $this->fixDateTimeValue($name, $this->stdFilterList['startdate']);
				$value[] = $this->fixDateTimeValue($name, $this->stdFilterList['enddate'], false);
				$this->addCondition($name, $value, 'BETWEEN');
			}
		}
		/* ED150308*/
		if($this->getAdvFilterListMore()){
			//var_dump('OUIOUIOUIOUIOUIOUIOUI $this->getAdvFilterListMore()', $this->getAdvFilterListMore());
			if(!is_array($this->advFilterList)){
				$this->advFilterList = array();
				$key = "1";
			}
			else {
				//last
				foreach($this->advFilterList as $key=>$item){}
				if($item){
					$this->advFilterList[$key]['condition'] = self::$AND;
					$key = ($key + 1).'';
				}
				else
					$key = "1";
			}
			//echo '<pre>';
			//echo json_encode($this->advFilterList);
			$this->advFilterList[$key] = array('columns'=>$this->getAdvFilterListMore());
			//echo '<br><br><br>';
			//echo json_encode($this->advFilterList);
			//echo '</pre>';
			$this->setAdvFilterListMore(false);
		}
		
		if($this->conditionInstanceCount <= 0 && is_array($this->advFilterList) && count($this->advFilterList) > 0) {
			$this->startGroup('');
		} elseif($this->conditionInstanceCount > 0 && is_array($this->advFilterList) && count($this->advFilterList) > 0) {
			$this->addConditionGlue(self::$AND);
		}		
		if(is_array($this->advFilterList) && count($this->advFilterList) > 0) {
			$this->parseAdvFilterList($this->advFilterList);
		}
		
		if($this->conditionInstanceCount > 0) {
			$this->endGroup();
		}
	}

	public function parseAdvFilterList($advFilterList, $glue=''){
		if(!empty($glue)) $this->addConditionGlue($glue);

		$customView = new CustomView($this->module);
		$moduleModel = Vtiger_Module_Model::getInstance($this->module);
		$relationModels = Vtiger_Relation_Model::getAllRelations($moduleModel);
		//var_dump($relationModels);
		$dateSpecificConditions = $customView->getStdFilterConditions();
		foreach ($advFilterList as $groupindex=>$groupcolumns) {
			$filtercolumns = $groupcolumns['columns'];
			//var_dump('parseAdvFilterList', $filtercolumns);
			//echo_callstack();
			if(count($filtercolumns) > 0) {
				$this->startGroup($groupindex == 1 ? '' : "/* group $groupindex */");
				$skipIndexes = array();
				
				//1er passage pour parser
				foreach ($filtercolumns as $index => &$filter){
					$this->parseFilterInfos($filter, $customView, $dateSpecificConditions);
					//var_dump('after parseFilterInfos $filter', $filter);
				}
				
				foreach ($filtercolumns as $index => &$filter){
					if(in_array($index, $skipIndexes)){
						$filter['skip'] = true;
						continue;
					}
					$columncondition = false;
					/*ED150226
					 * related module view field
					 * ou statistique
					 */
					//die('<pre>'.print_r($filter['columnname'], true).'</pre>');
					//echo('<pre>'.print_r($filter['columnname'], true).'</pre>');
					if($filter['isSubQuery']){
						if($filter['relatedmodulename'] === 'RSNStatisticsResults'){//RSNStatisticsResults
							//RSNStatisticsResults::stats_periodicite::statId::stats_periodicite_fieldId
							//RSNStatisticsResults::statFieldName::statId::statFieldId
							//echo "<code>TODO parseAdvFilterList for statistics</code>";
							//var_dump($viewName);
							$statId = $filter['statid'];
							if($filter['fieldName'] === 'stats_periodicite'){//1ere ligne
								//$statisticRelation = $this->addStatisticsTable($viewName[2]);
								$columncondition = $filter['column_condition'];
								
								$statisticRelation = $this->addStatisticsPeriodeCondition($statId, $filter['comparator'], $filter['value'], $columncondition);
								
								if(!empty($columncondition)) {
									//Pour le groupe OR, les champs de stat qui suivent la période de stat est toujours en AND
									if(strcasecmp($columncondition, 'or') === 0
									&& count($filtercolumns) > $index + 1
												//stat : d'un autre champ que la période : même stat id
									&& preg_match('/^\[RSNStatisticsResults\:(?!stats_periodicite)[^:]+\:'.$statId.'/', $filtercolumns[$index+1]['columnname'])
									){
										//et c'est vrai pour les suivants aussi
										for($nextFilterIndex = $index + 1; $nextFilterIndex < count($filtercolumns); $nextFilterIndex++) {
											if(preg_match('/^\[RSNStatisticsResults\:(?!stats_periodicite)[^:]+\:'.$statId.'/', $filtercolumns[$nextFilterIndex]['columnname'])){
												$filtercolumns[$nextFilterIndex - 1]['column_condition'] = 'AND';
											}
											else //fin du groupe de cette stat
												break;
										}
										$columncondition = $filter['column_condition'];
									}
									
									$this->addConditionGlue($columncondition);
								}
							}
							else{
								$statisticRelation = $this->addStatisticsCondition($statId, $filter['fieldName'], $filter['columnId'], $filter['comparator'], $filter['value']);
									
								$columncondition = $filter['column_condition'];
								if(!empty($columncondition)) {
									$this->addConditionGlue($columncondition);
								}
							}
							continue;
						}
						else { //CustomView ou Panel
							
							$relationFilters = array();
									
							if(!$filter['subQueryColumn']){//[xx:xx:xx]:subQueryColumn...
								//echo '<br><br><br><br>'.__FILE__;
								$relationModel = false;
								$relatedSql_OR = false;
								
								/* RSNContactsPanels */
								if($filter['relatedIsPanel']){
									$sourceFieldName = $this->getSQLColumn('id');
									$viewFilters = array();
									$panelRecord = Vtiger_Record_Model::getInstanceById($filter['viewid'], $filter['relatedmodulename']);
									
									
									/*next conditions*/
									$paramsValues = array();
									for($iNext = $index+1; $iNext < count($filtercolumns); $iNext++) {
										$nextFilter = $filtercolumns[$iNext];
										//same panel, memorize and skip
										if($nextFilter['isSubQuery'] && $nextFilter['viewid'] == $filter['viewid']){
											//champ du module lié
											if($nextFilter['subQueryColumn'] && $nextFilter['subQueryColumn']['isPanelVariable']){
												$paramsValues[$nextFilter['subQueryColumn']['variableName']] = array(
													'name' => $nextFilter['subQueryColumn']['variableName'],
													'value' => $nextFilter['subQueryColumn']['value'],
													'comparator' => $nextFilter['subQueryColumn']['comparator'],
												);
												
												$viewFilters[] = $nextFilter['subQueryColumn'];
												$skipIndexes[] = $iNext;
											}
											else
												break;
										}
											else
												break;
									}
									//echo "<br><br><br><br>";
									//var_dump('panel '.$filter['columnname'].' $relatedSql', $paramsValues);
									$relatedSql = $panelRecord->getExecutionQuery($paramsValues);
									//echo "<pre>$relatedSql</pre>";
								}
								
								/* CustomView */
								else { //"normal" relation
									foreach($relationModels as $model)
										if($model->getRelationModuleName() == $filter['relatedmodulename']){
											$relationModel = $model;
											break;
										}
									if($relationModel){
										$relationsInfos = $relationModel->getModulesInfoForDetailView();
										//var_dump($relationsInfos);
									}
									if(!$relationModel
									|| !isset($relationsInfos[$filter['relatedmodulename']])){
										$relationInfos = array('fieldName' => $this->getSQLColumn('id')
													   , 'tableName' => ' vtiger_crmentityrel');
									}
									else
										$relationInfos = $relationsInfos[$filter['relatedmodulename']];
									
									//var_dump($relationInfos);
									//$relationModel = Vtiger_Relation_Model::getInstance($this->module, $filter['relatedmodulename']);
									//$value = $relationModel->getQuery();
									//$this->addRelatedModuleCondition($filter['relatedmodulename'], $this->getSQLColumn('id'), $value, 'IN');
									
									/*next conditions*/
									$viewFilters = array();
									for($iNext = $index+1; $iNext < count($filtercolumns); $iNext++) {
										$nextFilter = $filtercolumns[$iNext];
										//same view, memorize and skip
										if($nextFilter['isSubQuery'] && $nextFilter['relatedmodulename'] == $filter['relatedmodulename']){
											//Il faut différencier les champs du module lié et les champs de la table de relation
											//champ du module lié
											if($nextFilter['subQueryColumn']){
												$viewFilters[] = $nextFilter['subQueryColumn'];
												$skipIndexes[] = $iNext;
											}
											//Champ de la table de relation
											else {
												$relationFilters[] = $nextFilter['relationColumn'];
												$skipIndexes[] = $iNext;
												//var_dump('Champ de la table de relation', $nextFilter['relationColumn']);
											}
										}
											
										//TODO modifier le column_condition pour ne pas avoir un AND final
									}
									
									//sub view
									$listView = Vtiger_ListView_Model::getInstance($filter['relatedmodulename'], $filter['viewid'], $viewFilters);
									//get view query, adding filters
									$relatedSql = $listView->getQuery();
									//SELECT `id`  only
									$newQuery = preg_split('/\sFROM\s/i', $relatedSql); //ED150226
									if(strpos($relationInfos['fieldName'], '.') === FALSE)
										$relationInfos['fieldName'] = $relationInfos['tableName'] .'.'. $relationInfos['fieldName'];
									else
										$relationInfos['fieldName'] = $relationInfos['tableName'] .'.'. substr($relationInfos['fieldName'], strpos($relationInfos['fieldName'], '.')+1);
									
									if(isset($relationInfos['relationTableName'])){
										//salement fait pour Invoice / RsnReglements. cf modules/Inventory/models/Relation.php
										$subQueryTable = uniqid('subq_');
										$subQueryField =  $subQueryTable . '.' . $relationInfos['relatedFieldName'];
										$relSourceFieldName = isset($relationInfos['sourceFieldNameInRelation']) ? $relationInfos['sourceFieldNameInRelation'] : $relationInfos['fieldName'];
										
										$relationTableName = $relationInfos['relationTableName'];
										$joinField = $relationTableName . '.' . $relationInfos['relatedSourceFieldName'];
										
										$subRelatedSql = $relatedSql;
										
										$relatedSql = 'SELECT ' . $relSourceFieldName . ' 
											FROM ' . $relationTableName . '
											JOIN ( ' . $subRelatedSql . ') ' . $subQueryTable . '
											 	ON ' . $joinField . '
													= ' . $subQueryField
										;
										if($relationFilters)
											$relatedSql .= $this->getRelationFiltersSQLWhere($relationTableName, $relationFilters);
										//Même modules (Contact->Contacts, Document->Documents) : reverse SELECT et JOIN
										if($this->module == $filter['relatedmodulename']){
											$relatedSql_OR = '
											SELECT ' . $joinField . ' 
											FROM ' . $relationTableName . '
											JOIN ( ' . $subRelatedSql . ') ' . $subQueryTable . '
											 	ON ' . $relSourceFieldName . '
													= ' . $subQueryField
											;
											if($relationFilters)
												$relatedSql_OR .= $this->getRelationFiltersSQLWhere($relationTableName, $relationFilters);
											
										}
										
									}
									else {
										$selectColumnSql = 'SELECT ' . $relationInfos['fieldName'];
										$newQuery[0] = $selectColumnSql.' ';
										
										$relatedSql = implode("\nFROM ", $newQuery);
										
									}
									
									//echo '<br><br><br><br><pre>'; print_r($relatedSql); echo '</pre>';
									
									
									$sourceFieldName = isset($relationInfos['sourceFieldName']) ? $relationInfos['sourceFieldName'] : $this->getSQLColumn('id');
								}
								
								//print_r('<pre>$this->getSQLColumn(id) : '.$this->getSQLColumn('id').'</pre>');
								//print_r('<pre>$relatedSql : '.$relatedSql.'</pre>');
								//$this->addRelatedModuleCondition($filter['relatedmodulename'], $this->getSQLColumn('id'), $relatedSql, 'IN');
								//echo str_repeat('<br>',5).__FILE__.'<br>';
								//var_dump($this->getSQLColumn('id'), "/*BEGIN ".$filter['viewname']."*/\n\t$relatedSql\n\t/*END ".$filter['viewname']."*/", 'IN', 'AND');
								//TODO $field =	var_dump($this->meta->getFieldByColumnName("contact_id"));
								
								//TODO bugg vu avec un filtre juste avant
								
								$this->startGroup('', $filter['comparator'] . ' [' . $filter['viewname'] . ']');
								$this->addCondition($sourceFieldName
											, $relatedSql
											, $filter['comparator']);
								if($relatedSql_OR){
									$this->addConditionGlue('OR');
									$this->addCondition($sourceFieldName
												, $relatedSql_OR
												, $filter['comparator']);
								}
								$this->endGroup($filter['viewname']);
									
								//column_condition
								//must use the last filter, even if skipped
								if(count($viewFilters) || count($relationFilters)){
									for($nextFilterIndex = $index + 1; $nextFilterIndex < count($filtercolumns); $nextFilterIndex++){
										if(in_array($nextFilterIndex, $skipIndexes)){
											//var_dump('ICICI CI C I');
											$columncondition = $filtercolumns[$nextFilterIndex]['column_condition'];
										}
										else
											break;
									}
								}
								else{
									//var_dump('LAALAAL');
									$columncondition = $filter['column_condition'];
								}
								//echo "<br><br><br><br>";
								//var_dump('column_condition', $columncondition, '$index', $index, '$filtercolumns', $filtercolumns, '$viewFilters', $viewFilters, '$filter', $filter, '$relationFilters', $relationFilters, '$skipIndexes', $skipIndexes);
								if(!empty($columncondition)) {
									$this->addConditionGlue($columncondition);
								}
							}
							//Filtre sur un champ de relation
							else {
								
								var_dump('subQueryColumn->columnname', $filter['subQueryColumn']['columnname']);
								
							}
							continue;
						}
					}
			
					//ED151911 toute l'analyse mise en commentaire a été basculée dans parseFilterInfos
					
					//$nameComponents = explode(':',$filter['columnname']);
					//if(empty($nameComponents[2]) && $nameComponents[1] == 'crmid' && $nameComponents[0] == 'vtiger_crmentity') {
					//	$name = $this->getSQLColumn('id');
					//} else {
					//	$name = $nameComponents[2];
					//}
					//if(($nameComponents[4] === 'D' || $nameComponents[4] === 'DT') && in_array($filter['comparator'], $dateSpecificConditions)) {
					//	$filter['stdfilter'] = $filter['comparator'];
					//	$valueComponents = explode(',',$filter['value']);
					//	if($filter['comparator'] === 'custom') {
					//		if($nameComponents[4] === 'DT') {
					//			$startDateTimeComponents = explode(' ',$valueComponents[0]);
					//			$endDateTimeComponents = explode(' ',$valueComponents[1]);
					//			$filter['startdate'] = DateTimeField::convertToDBFormat($startDateTimeComponents[0]);
					//			$filter['enddate'] = DateTimeField::convertToDBFormat($endDateTimeComponents[0]);
					//		} else {
					//			$filter['startdate'] = DateTimeField::convertToDBFormat($valueComponents[0]);
					//			$filter['enddate'] = DateTimeField::convertToDBFormat($valueComponents[1]);
					//		}
					//	}
					//
					//	$dateFilterResolvedList = $customView->resolveDateFilterValue($filter);
					//	$value[] = $this->fixDateTimeValue($name, $dateFilterResolvedList['startdate']);
					//	$value[] = $this->fixDateTimeValue($name, $dateFilterResolvedList['enddate'], false);
					//	$this->addCondition($name, $value, 'BETWEEN');
					//} else if($nameComponents[4] === 'DT' && ($filter['comparator'] == 'e' || $filter['comparator'] == 'n')) {
					//	$filter['stdfilter'] = $filter['comparator'];
					//	$dateTimeComponents = explode(' ',$filter['value']);
					//	$filter['startdate'] = DateTimeField::convertToDBFormat($dateTimeComponents[0]);
					//	$filter['enddate'] = DateTimeField::convertToDBFormat($dateTimeComponents[0]);
					//	$dateTimeFilterResolvedList = $customView->resolveDateFilterValue($filter);
					//	$value[] = $this->fixDateTimeValue($name, $dateTimeFilterResolvedList['startdate']);
					//	$value[] = $this->fixDateTimeValue($name, $dateTimeFilterResolvedList['enddate'], false);
					//	if($filter['comparator'] == 'n') {
					//		$this->addCondition($name, $value, 'NOTEQUAL');
					//	} else {
					//		$this->addCondition($name, $value, 'BETWEEN');
					//	}
					//} else if($nameComponents[4] == 'DT' && $filter['comparator'] == 'a') {
					//	$dateTime = explode(' ', $filter['value']);
					//	$value[] = $this->fixDateTimeValue($name, $dateTime[0], false);
					//	$this->addCondition($name, $value, $filter['comparator']);
					//} else{
					//	$this->addCondition($name, $filter['value'], $filter['comparator']);
					//}
					//remplace ce qui précède et qui a été déplacé dans parseFilterInfos
					$this->addCondition($filter['condition_fieldname'], $filter['value'], $filter['comparator']);
					
					$columncondition = $filter['column_condition'];
					if(!empty($columncondition)) {
						$this->addConditionGlue($columncondition);
					}
				} //foreach
				$this->endGroup();
				$groupConditionGlue = $groupcolumns['condition'];
				if(!empty($groupConditionGlue))
					$this->addConditionGlue($groupConditionGlue);
			}
		}
	}

	/* ED151119
	* Related module view ou Statistics
	* - Existence d'une relation avec la vue d'un autre module
	* 	[RelatedModule:ViewName:ViewId]
	* - Test sur un champ de la table de relation par la vue d'un autre module
	*  [RelatedModule:ViewName:ViewId::relation table:relation column:relation field:label]
	* - Test sur un champ d'un autre module via une vue de cet autre module
	*  [RelatedModule:ViewName:ViewId]:field related table:column:field:label (TODO check column:field)
	* - Relation à un RSNContactsPanels (idem que les related module custom views)
	* 	[RSNContactsPanels:PanelName:PanelId]
	* - Test sur l'existence d'une stat pour une période donnée 
	*  [RSNStatisticsResults:stats_periodicite:StatId:stats_periodicite fieldId === 1380]
	* - Test sur un champ d'une statistique pour une période péalablement donnée 
	*  [RSNStatisticsResults:stat column:StatId:Stat fieldId]
	*
	* - Test sur un panel
	*  [RSNContactsPanels:Panel name:Panel Id]
	* - Définition de la valeur d'une variable de panel
	*  [RSNContactsPanels:Panel name:Panel Id]:as field name:variableId:variableName:dataType
	*
	*  Tout ceci à comparer avec modules\CustomView\models\Record.php  save
	*/
	private function parseFilterInfos(&$filter, $customView, $dateSpecificConditions){
		if($filter['isParsed'])
			return;
		$filter['isParsed'] = true;
		
		
		if($filter['columnname'][0] === '['){
			$filter['isSubQuery'] = true;
			$posClosingBracket = strpos($filter['columnname'], ']', 1);
			$viewName = explode(":", substr($filter['columnname'], 1, $posClosingBracket-1));
			$filter['relatedmodulename'] = $viewName[0];
			if($viewName[0] === 'RSNStatisticsResults'){//RSNStatisticsResults
				//RSNStatisticsResults::stats_periodicite::statId::stats_periodicite_fieldId
				//RSNStatisticsResults::statFieldName::statId::statFieldId
				//echo "<code>TODO parseAdvFilterList for statistics</code>";
				//var_dump($viewName);
				$filter['statid'] = $viewName[2];
				$filter['fieldName'] = $viewName[1];
				$filter['columnId'] = $viewName[3];
				if($filter['fieldName'] === 'stats_periodicite'){
				} else {
				}
			}
			else { //CustomView ou Panel
				$filter['viewid'] = $viewName[2];
				$filter['viewname'] = $viewName[1];
				$filter['relatedmodule'] = Vtiger_Module_Model::getInstance($filter['relatedmodulename']);
				
				//Champ de la table de relation
				if(count($viewName) > 3){//relation table:relation column:relation field:label
					$filter['relationColumn'] = array_merge($filter, array(
						'table' => $viewName[4],
						'column' => $viewName[5],
						'field' => $viewName[6],
						'fieldLabel' => $viewName[7],
						'dataType' => explode('~', $viewName[8])[0],
					));
					$this->initFilterConditionValue($filter['relationColumn'], $viewName[5], $customView, $dateSpecificConditions);
				}

				$filter['relatedIsPanel'] = $filter['relatedmodulename'] === 'RSNContactsPanels';
				
				//Données après le ]
				//Champ du module lié
				$subQueryColumnName = trim(substr($filter['columnname'], $posClosingBracket + 1));
				if(strpos($subQueryColumnName, ':') !== false){
					if($subQueryColumnName[0] === ':')
						$subQueryColumnName = substr($subQueryColumnName,1);
					$subQueryColumnInfos = explode(':', $subQueryColumnName);
					
					if($filter['relatedIsPanel']){
						$filter['subQueryColumn'] = array_merge($filter, array(
							'columnname' => $subQueryColumnName,
							'variableId' => $subQueryColumnInfos[1],
							'variableName' => $subQueryColumnInfos[2],
							'dataType' => explode('~', $subQueryColumnInfos[3])[0],
							'isPanelVariable' => true,
						));
					} else {
						$filter['subQueryColumn'] = array_merge($filter, array(
							'columnname' => $subQueryColumnName,
							'table' => $subQueryColumnInfos[0],
							'column' => $subQueryColumnInfos[1],
							'field' => $subQueryColumnInfos[2],
							'dataType' => explode('~', $subQueryColumnInfos[3])[0],
						));
					}
					$this->initFilterConditionValue($filter['subQueryColumn'], $subQueryColumnInfos[1], $customView, $dateSpecificConditions);
				}
			}
		}
		else {	
			$nameComponents = explode(':',$filter['columnname']);
			if(empty($nameComponents[2]) && $nameComponents[1] == 'crmid' && $nameComponents[0] == 'vtiger_crmentity') {
				$name = $this->getSQLColumn('id');
			} else {
				$name = $nameComponents[2];
			}
			$filter['dataType'] = explode('~', $nameComponents[4])[0];
			$this->initFilterConditionValue($filter, $name, $customView, $dateSpecificConditions);
		}
	}
	
	private function initFilterConditionValue(&$filter, $name, $customView, $dateSpecificConditions){
		//Traitement des dates et des opérateurs spécifiques
		//Les 3 champs utilisés pour la fonction addCondition
		$filter['condition_fieldname'] = $name;
		$isPanelVariable = $filter['isPanelVariable'];
		
		if(($filter['dataType'] === 'D' || $filter['dataType'] === 'DT') && in_array($filter['comparator'], $dateSpecificConditions)) {
			$filter['stdfilter'] = $filter['comparator'];
			$valueComponents = explode(',',$filter['value']);
			if($filter['comparator'] === 'custom') {
				if($filter['dataType'] === 'DT') {
					$startDateTimeComponents = explode(' ',$valueComponents[0]);
					$endDateTimeComponents = explode(' ',$valueComponents[1]);
					$filter['startdate'] = DateTimeField::convertToDBFormat($startDateTimeComponents[0]);
					$filter['enddate'] = DateTimeField::convertToDBFormat($endDateTimeComponents[0]);
				} else {
					$filter['startdate'] = DateTimeField::convertToDBFormat($valueComponents[0]);
					$filter['enddate'] = DateTimeField::convertToDBFormat($valueComponents[1]);
				}
			}

			$dateFilterResolvedList = $customView->resolveDateFilterValue($filter);
			$value[] = $this->fixDateTimeValue($name, $dateFilterResolvedList['startdate']);
			$value[] = $this->fixDateTimeValue($name, $dateFilterResolvedList['enddate'], false);
			$filter['value'] = $value;
			$filter['comparator'] = 'BETWEEN';
		
		} else if($filter['dataType'] === 'DT' && ($filter['comparator'] == 'e' || $filter['comparator'] == 'n') && !$isPanelVariable) {
			$filter['stdfilter'] = $filter['comparator'];
			$dateTimeComponents = explode(' ',$filter['value']);
			$filter['startdate'] = DateTimeField::convertToDBFormat($dateTimeComponents[0]);
			$filter['enddate'] = DateTimeField::convertToDBFormat($dateTimeComponents[0]);
			$dateTimeFilterResolvedList = $customView->resolveDateFilterValue($filter);
			$value[] = $this->fixDateTimeValue($name, $dateTimeFilterResolvedList['startdate']);
			$value[] = $this->fixDateTimeValue($name, $dateTimeFilterResolvedList['enddate'], false);
			$filter['value'] = $value;
			if($filter['comparator'] == 'n') {
				$filter['comparator'] = 'NOTEQUAL';
			} else {
				$filter['comparator'] = 'BETWEEN';
			}
		} else if($filter['dataType'] == 'DT' && $filter['comparator'] == 'a') {
			$dateTime = explode(' ', $filter['value']);
			$value[] = $this->fixDateTimeValue($name, $dateTime[0], false);
			$filter['value'] = $value;
		}
	}
	
	/**
	 * Inclut une table de résultats de stats dans le from
	 *
	 *
	 * @param $groupCondition : définit si il faut un INNER JOIN ou un LEFT JOIN
	 */
	private function addStatisticsPeriodeCondition($statId, $comparator = false, $condition = false, $groupCondition = 'and'){
		if(!$this->statistictsRelations)
			$this->statistictsRelations = array();
		$alias = $this->getStatisticsPeriodeAlias($statId, $comparator, $condition);
		if(!array_key_exists($alias, $this->statistictsRelations)){
			$relationInfos = array(
				'id' => $statId,
				'tableName' => RSNStatistics_Utils_Helper::getStatsTableNameFromId($statId),
				'alias' => $alias,
				'groupCondition' => $groupCondition,
			);
			$this->statistictsRelations[$alias] = $relationInfos;
			$this->addMetaStatFields($statId, $alias);
		}
		else
			$relationInfos = $this->statistictsRelations[$alias];
		if($condition){
			$this->addStatisticsCondition($statId, 'code', null, $comparator, $condition);
		}
		return $relationInfos;
	}
	
	private function addMetaStatFields($statId, $tableAlias){
		$tableFields = RSNStatistics_Utils_Helper::getRelatedStatsFieldsVtigerFieldModels($statId);
		
		$tableFields['code'] = RSNStatisticsResults_Field_Model::getInstanceForPeriodField();
		
		$fields = array();
		foreach($tableFields as $field){
			$field->set('table', $tableAlias);
			$field->set('table', $tableAlias);
			$fields[$tableAlias . '_' . $field->getName()] = $field;
		}
		if(!$this->statisticsFields)
			$this->statisticsFields = $fields;
		else
			$this->statisticsFields = array_merge($this->statisticsFields, $fields);
	}
	
	//Construit un alias de table selon les conditions fournies
	private function getStatisticsPeriodeAlias($statId, $comparator, $condition){
		$alias = 'stat'.$statId;
		if(!$comparator){
			//dernier fourni pour cette stat
			$lastAlias = false;
			foreach($this->statistictsRelations as $alias => $relationsInfos)
				if(substr($alias, strlen('stat'.$statId)+1) == 'stat'.$statId.'_')
				   $lastAlias = $alias;
			if($lastAlias)
				return $lastAlias;
		}
		if($comparator && $comparator != 'e')
			$alias .= '_' . $comparator;
		if($condition){
			if(is_array($condition))
				$alias .= '_' . implode('_', $condition);
			else
				$alias .= '_' . preg_replace('/\W/', '_', $condition);
		}
		return $alias;
	}
	//Inclut une condition de résultats de stats dans le where
	private function addStatisticsCondition($statId, $fieldName, $columnId, $comparator, $condition){
		$relationInfos = $this->addStatisticsPeriodeCondition($statId);
		$tableAlias = $relationInfos['alias'];
		$this->startGroup('', $comparator . ' [' . $fieldName . ']');
		$this->addCondition('stat::' . $tableAlias . '_' . $fieldName
					, $condition
					, $comparator);
		$this->endGroup($fieldName);
	}
	
	public function getCustomViewQueryById($viewId) {
		$this->initForCustomViewById($viewId);
		return $this->getQuery();
	}

	public function getQuery() {
		
		if(empty($this->query)) {
			$conditionedReferenceFields = array();
			$allFields = array_merge($this->whereFields,$this->fields);
			foreach ($allFields as $fieldName) {
				if(in_array($fieldName,$this->referenceFieldList)) {
					$moduleList = $this->referenceFieldInfoList[$fieldName];
					foreach ($moduleList as $module) {
						if(empty($this->moduleNameFields[$module])) {
							$meta = $this->getMeta($module);
						}
					}
				} elseif(in_array($fieldName, $this->ownerFields )) {
					$meta = $this->getMeta('Users');
					$meta = $this->getMeta('Groups');
				}
			}

			$query = "SELECT ";
			$query .= $this->getSelectClauseColumnSQL();
			$query .= $this->getFromClause();
			$query .= $this->getWhereClause();
			$this->query = $query;
			
//			print_r('<pre style="margin-top:4em;">'.__FILE__.'->getQuery $this->query = $query;<br>'.$query.'</pre>');
			
			//echo_callstack();
			
			return $query;
		} else {
			return $this->query;
		}
	}

	public function getSQLColumn($name) {
		if ($name == 'id') {
			$baseTable = $this->meta->getEntityBaseTable();
			$moduleTableIndexList = $this->meta->getEntityTableIndexList();
			$baseTableIndex = $moduleTableIndexList[$baseTable];
			return $baseTable.'.'.$baseTableIndex;
		}

		$moduleFields = $this->meta->getModuleFields();
		$field = $moduleFields[$name];
		$sql = '';
		//TODO optimization to eliminate one more lookup of name, incase the field refers to only
		//one module or is of type owner.
		$column = $field->getColumnName();
		return $field->getTableName().'.'.$column;
	}

	public function getSelectClauseColumnSQL(){
		$columns = array();
		$moduleFields = $this->meta->getModuleFields();
		$accessibleFieldList = array_keys($moduleFields);
		$accessibleFieldList[] = 'id';
		$this->fields = array_intersect($this->fields, $accessibleFieldList);
		foreach ($this->fields as $field) {
			$sql = $this->getSQLColumn($field);
			$columns[] = $sql;
			//To merge date and time fields
			if($this->meta->getEntityName() == 'Calendar' && ($field == 'date_start' || $field == 'due_date' || $field == 'taskstatus' || $field == 'eventstatus')) {
				if($field=='date_start') {
					$timeField = 'time_start';
					$sql = $this->getSQLColumn($timeField);
				} else if ($field == 'due_date') {
					$timeField = 'time_end';
					$sql = $this->getSQLColumn($timeField);
				} else if ($field == 'taskstatus' || $field == 'eventstatus') {
					//In calendar list view, Status value = Planned is not displaying
					$sql = "CASE WHEN (vtiger_activity.status not like '') THEN vtiger_activity.status ELSE vtiger_activity.eventstatus END AS ";
					if ( $field == 'taskstatus') {
						$sql .= "status";
					} else {
						$sql .= $field;
					}
				}
				$columns[] = $sql;
			}
		}
		$this->columns = implode(', ', $columns);
		return $this->columns;
	}

	public function getFromClause() {
		global $current_user;
		if(!empty($this->query) || !empty($this->fromClause)) {
			return $this->fromClause;
		}
		$baseModule = $this->getModule();
		$moduleFields = $this->meta->getModuleFields();
		$tableList = array();
		$tableJoinMapping = array();
		$tableJoinCondition = array();
		$i =1;
		$moduleTableIndexList = $this->meta->getEntityTableIndexList();
		foreach ($this->fields as $fieldName) {
			if ($fieldName == 'id') {
				continue;
			}

			$field = $moduleFields[$fieldName];
			$baseTable = $field->getTableName();
			$tableIndexList = $this->meta->getEntityTableIndexList();
			$baseTableIndex = $tableIndexList[$baseTable];
			if($field->getFieldDataType() == 'reference') {
				$moduleList = $this->referenceFieldInfoList[$fieldName];
				$tableJoinMapping[$field->getTableName()] = 'INNER JOIN';
				foreach($moduleList as $module) {
					if($module == 'Users' && $baseModule != 'Users') {
						$tableJoinCondition[$fieldName]['vtiger_users'.$fieldName] = $field->getTableName().
								".".$field->getColumnName()." = vtiger_users".$fieldName.".id";
						$tableJoinCondition[$fieldName]['vtiger_groups'.$fieldName] = $field->getTableName().
								".".$field->getColumnName()." = vtiger_groups".$fieldName.".groupid";
						$tableJoinMapping['vtiger_users'.$fieldName] = 'LEFT JOIN vtiger_users AS';
						$tableJoinMapping['vtiger_groups'.$fieldName] = 'LEFT JOIN vtiger_groups AS';
						$i++;
					}
				}
			} elseif($field->getFieldDataType() == 'owner') {
				$tableList['vtiger_users'] = 'vtiger_users';
				$tableList['vtiger_groups'] = 'vtiger_groups';
				$tableJoinMapping['vtiger_users'] = 'LEFT JOIN';
				$tableJoinMapping['vtiger_groups'] = 'LEFT JOIN';
			}
			$tableList[$field->getTableName()] = $field->getTableName();
				$tableJoinMapping[$field->getTableName()] =
						$this->meta->getJoinClause($field->getTableName());
		}
		$baseTable = $this->meta->getEntityBaseTable();
		$baseTableIndex = $moduleTableIndexList[$baseTable];
		foreach ($this->whereFields as $fieldName) {
			if(empty($fieldName)) {
				continue;
			}
			$field = $moduleFields[$fieldName];
			if(empty($field)) {
				// not accessible field.
				continue;
			}
			$baseTable = $field->getTableName();
			// When a field is included in Where Clause, but not is Select Clause, and the field table is not base table,
			// The table will not be present in tablesList and hence needs to be added to the list.
			if(empty($tableList[$baseTable])) {
				$tableList[$baseTable] = $field->getTableName();
				$tableJoinMapping[$baseTable] = $this->meta->getJoinClause($field->getTableName());
			}
			if($field->getFieldDataType() == 'reference') {
				$moduleList = $this->referenceFieldInfoList[$fieldName];
				$tableJoinMapping[$field->getTableName()] = 'INNER JOIN';
				foreach($moduleList as $module) {
					$meta = $this->getMeta($module);
					$nameFields = $this->moduleNameFields[$module];
					$nameFieldList = explode(',',$nameFields);
					foreach ($nameFieldList as $index=>$column) {
						$referenceField = $meta->getFieldByColumnName($column);
						$referenceTable = $referenceField->getTableName();
						$tableIndexList = $meta->getEntityTableIndexList();
						$referenceTableIndex = $tableIndexList[$referenceTable];

						$referenceTableName = "$referenceTable $referenceTable$fieldName";
						$referenceTable = "$referenceTable$fieldName";
						//should always be left join for cases where we are checking for null
						//reference field values.
						if(!array_key_exists($referenceTable, $tableJoinMapping)) {		// table already added in from clause
							$tableJoinMapping[$referenceTableName] = 'LEFT JOIN';
							$tableJoinCondition[$fieldName][$referenceTableName] = $baseTable.'.'.
								$field->getColumnName().' = '.$referenceTable.'.'.$referenceTableIndex;
						}
					}
				}
			} elseif($field->getFieldDataType() == 'owner') {
				$tableList['vtiger_users'] = 'vtiger_users';
				$tableList['vtiger_groups'] = 'vtiger_groups';
				$tableJoinMapping['vtiger_users'] = 'LEFT JOIN';
				$tableJoinMapping['vtiger_groups'] = 'LEFT JOIN';
			} else {
				$tableList[$field->getTableName()] = $field->getTableName();
				$tableJoinMapping[$field->getTableName()] =
						$this->meta->getJoinClause($field->getTableName());
			}
		}

		$defaultTableList = $this->meta->getEntityDefaultTableList();
		foreach ($defaultTableList as $table) {
			if(!in_array($table, $tableList)) {
				$tableList[$table] = $table;
				$tableJoinMapping[$table] = 'INNER JOIN';
			}
		}
		$ownerFields = $this->meta->getOwnerFields();
		if (count($ownerFields) > 0) {
			$ownerField = $ownerFields[0];
		}
		$baseTable = $this->meta->getEntityBaseTable();
		$sql = " FROM $baseTable ";
		unset($tableList[$baseTable]);
		foreach ($defaultTableList as $tableName) {
			$sql .= " $tableJoinMapping[$tableName] $tableName ON $baseTable.".
					"$baseTableIndex = $tableName.$moduleTableIndexList[$tableName]";
			unset($tableList[$tableName]);
		}
		foreach ($tableList as $tableName) {
			if($tableName == 'vtiger_users') {
				$field = $moduleFields[$ownerField];
				$sql .= " $tableJoinMapping[$tableName] $tableName ON ".$field->getTableName().".".
					$field->getColumnName()." = $tableName.id";
			} elseif($tableName == 'vtiger_groups') {
				$field = $moduleFields[$ownerField];
				$sql .= " $tableJoinMapping[$tableName] $tableName ON ".$field->getTableName().".".
					$field->getColumnName()." = $tableName.groupid";
			} else {
				$sql .= " $tableJoinMapping[$tableName] $tableName ON $baseTable.".
					"$baseTableIndex = $tableName.$moduleTableIndexList[$tableName]";
			}
		}

		if( $this->meta->getTabName() == 'Documents') {
			$tableJoinCondition['folderid'] = array(
				'vtiger_attachmentsfolderfolderid'=>"$baseTable.folderid = vtiger_attachmentsfolderfolderid.folderid"
			);
			$tableJoinMapping['vtiger_attachmentsfolderfolderid'] = 'INNER JOIN vtiger_attachmentsfolder';
		}

		foreach ($tableJoinCondition as $fieldName=>$conditionInfo) {
			foreach ($conditionInfo as $tableName=>$condition) {
				if(!empty($tableList[$tableName])) {
					$tableNameAlias = $tableName.'2';
					$condition = str_replace($tableName, $tableNameAlias, $condition);
				} else {
					$tableNameAlias = '';
				}
				$sql .= " $tableJoinMapping[$tableName] $tableName $tableNameAlias ON $condition";
			}
		}

		foreach ($this->manyToManyRelatedModuleConditions as $conditionInfo) {
			$relatedModuleMeta = RelatedModuleMeta::getInstance($this->meta->getTabName(),
					$conditionInfo['relatedModule']);
			//var_dump($relatedModuleMeta);
			$relationInfo = $relatedModuleMeta->getRelationMeta();
			$relatedModule = $this->meta->getTabName();
			$relatedModuleField = isset($relationInfo['relatedField']) ? $relationInfo['relatedField'] : $relationInfo[$relatedModule];
			//TODO : LEFT JOIN if needed
			$sql .= ' INNER JOIN '.$relationInfo['relationTable'].
				' ON '.$relationInfo['relationTable'].".$relatedModuleField = $baseTable.$baseTableIndex";
		}

		// Adding support for conditions on reference module fields
		if($this->referenceModuleField) {
			$referenceFieldTableList = array();
			foreach ($this->referenceModuleField as $index=>$conditionInfo) {

				$handler = vtws_getModuleHandlerFromName($conditionInfo['relatedModule'], $current_user);
				$meta = $handler->getMeta();
				$tableList = $meta->getEntityTableIndexList();
				$fieldName = $conditionInfo['fieldName'];
				$referenceFieldObject = $moduleFields[$conditionInfo['referenceField']];
				$fields = $meta->getModuleFields();
				$fieldObject = $fields[$fieldName];

				if(empty($fieldObject)) continue;

				$tableName = $fieldObject->getTableName();
				if(!in_array($tableName, $referenceFieldTableList)) {
					if($this->getModule() == 'Calendar' || $this->getModule() == 'Events'){
						switch($referenceFieldObject->getFieldName()){
						case 'parent_id':
							$sql .= ' LEFT JOIN vtiger_seactivityrel ON vtiger_seactivityrel.activityid = vtiger_activity.activityid ';
							break;
						//TODO : this will create duplicates, need to find a better way
						case 'contact_id':
							$sql .= ' LEFT JOIN vtiger_cntactivityrel ON vtiger_cntactivityrel.activityid = vtiger_activity.activityid ';
							break;
						}
					}
					$sql .= " LEFT JOIN ".$tableName.' AS '.$tableName.$conditionInfo['referenceField'].' ON
							'.$tableName.$conditionInfo['referenceField'].'.'.$tableList[$tableName].'='.
						$referenceFieldObject->getTableName().'.'.$referenceFieldObject->getColumnName();
					$referenceFieldTableList[] = $tableName;
				}
			}
		}

		//RSNStatistics
		if($this->statistictsRelations)
			foreach ($this->statistictsRelations as $statisticId => $relationInfos) {
				//TODO : LEFT JOIN if needed
				$joinType = strcasecmp($relationInfos['groupCondition'], 'or') === 0 ? 'LEFT' : 'INNER';
				$sql .= ' '.$joinType.' JOIN '.$relationInfos['tableName'].' `'.$relationInfos['alias'].'`
					 ON `'.$relationInfos['alias'].'`.crmid = '.$baseTable.'.'.$baseTableIndex;
			}
		
		$sql .= $this->meta->getEntityAccessControlQuery();
		$this->fromClause = $sql;
		return $sql;
	}

	public function getWhereClause() {
		global $current_user;
		if(!empty($this->query) || !empty($this->whereClause)) {
			return $this->whereClause;
		}
		$deletedQuery = $this->meta->getEntityDeletedQuery();
		$sql = '';
		if(!empty($deletedQuery)) {
			$sql .= " WHERE $deletedQuery";
		}
		if($this->conditionInstanceCount > 0) {
			$sql .= ' AND ';
		} elseif(empty($deletedQuery)) {
			$sql .= ' WHERE ';
		}
		$baseModule = $this->getModule();
		//var_dump($this->meta);//class VtigerCRMObjectMeta
		$moduleFieldList = $this->meta->getModuleFields();
		$baseTable = $this->meta->getEntityBaseTable();
		$moduleTableIndexList = $this->meta->getEntityTableIndexList();
		$baseTableIndex = $moduleTableIndexList[$baseTable];
		$groupSql = $this->groupInfo;
		$fieldSqlList = array();
		foreach ($this->conditionals as $index=>$conditionInfo) {
			//echo '<br><br><br><br><br>'; var_dump($index, $conditionInfo);
			
			$fieldName = $conditionInfo['name'];
			$field = $moduleFieldList[$fieldName];
			if(empty($field)){
				if(substr($fieldName, 0, strlen('stat::')) === 'stat::')
					$field = $this->statisticsFields[substr($fieldName, strlen('stat::'))];
				elseif($conditionInfo['dataType'])
					$field = $this->getGenericField($fieldName, $conditionInfo['dataType']);
			}
			if(empty($field) || $conditionInfo['operator'] == 'None') {
				/* ED150307 IN, NOT IN
				 * sub view
				 */
				if($this->isViewOperators($conditionInfo['operator'])) {
					//ED150628
					if($fieldName == 'id')
						$fieldName = $this->getSQLColumn('id');
					$valueSql = $conditionInfo['value'];
					if($valueSql[0] != '(')
						$valueSql = "($valueSql)";
					switch($conditionInfo['operator']) {
					case 'vwi':
						$sqlOperator = " IN ";
						break;
					case 'vwx':
						$sqlOperator = " NOT IN ";
						break;
					}
					$fieldGlue = ''; //ED151028 on est dans entre ( ) A voir si il ne faut pas le faire aussi ci-dessous
					
					$fieldSqlList[$index] = "$fieldGlue $fieldName $sqlOperator \n\t$valueSql\n";
				}
				elseif($conditionInfo['operator'] == 'IN' && $fieldName == 'id'){
					$fieldGlue = ''; //ED151028 on est dans entre ( ) A voir si il ne faut pas le faire aussi ci-dessous
					
					$fieldName = $this->getSQLColumn('id');
					$valueSql = $conditionInfo['value'];
					if($valueSql[0] != '(')
						$valueSql = "($valueSql)";
					$sqlOperator = " IN ";
					$fieldSqlList[$index] = "$fieldGlue $fieldName $sqlOperator \n\t$valueSql\n";
				}
				continue;
			}
			$fieldSql = '(';
			$fieldGlue = '';
			$valueSqlList = $this->getConditionValue($conditionInfo['value'],
				$conditionInfo['operator'], $field);
			
			if(!is_array($valueSqlList)) {
				$valueSqlList = array($valueSqlList);
			}
			foreach ($valueSqlList as $valueSql) {
				if (in_array($fieldName, $this->referenceFieldList)) {
					if($conditionInfo['operator'] == 'y'){
						$columnName = $field->getColumnName();
						$tableName = $field->getTableName();
						// We are checking for zero since many reference fields will be set to 0 if it doest not have any value
						$fieldSql .= "$fieldGlue $tableName.$columnName $valueSql OR $tableName.$columnName = '0'";
						$fieldGlue = ' OR';
					}elseif($conditionInfo['operator'] == 'ny'){
						$columnName = $field->getColumnName();
						$tableName = $field->getTableName();
						// We are checking for zero since many reference fields will be set to 0 if it doest not have any value
						$fieldSql .= "$fieldGlue NOT($tableName.$columnName $valueSql OR $tableName.$columnName = '0')";
						$fieldGlue = ' OR';
					}else{
						$moduleList = $this->referenceFieldInfoList[$fieldName];
						foreach($moduleList as $module) {
							$nameFields = $this->moduleNameFields[$module];
							$nameFieldList = explode(',',$nameFields);
							$meta = $this->getMeta($module);
							$columnList = array();
							foreach ($nameFieldList as $column) {
								if($module == 'Users') {
									$instance = CRMEntity::getInstance($module);
									$referenceTable = $instance->table_name;
									if(count($this->ownerFields) > 0 ||
											$this->getModule() == 'Quotes') {
										$referenceTable .= $fieldName;
									}
								} else {
									$referenceField = $meta->getFieldByColumnName($column);
									$referenceTable = $referenceField->getTableName().$fieldName;
								}
								if(isset($moduleTableIndexList[$referenceTable])) {
									$referenceTable = "$referenceTable$fieldName";
								}
								$columnList[] = "$referenceTable.$column";
							}
							if(count($columnList) > 1) {
								$columnSql = getSqlForNameInDisplayFormat(array('first_name'=>$columnList[0],'last_name'=>$columnList[1]),'Users');
							} else {
								$columnSql = implode('', $columnList);
							}

							$fieldSql .= "$fieldGlue trim($columnSql) $valueSql";
							$fieldGlue = ' OR';
						}
					}
				} elseif (in_array($fieldName, $this->ownerFields)) {
					$concatSql = getSqlForNameInDisplayFormat(array('first_name'=>"vtiger_users.first_name",'last_name'=>"vtiger_users.last_name"), 'Users');
					$fieldSql .= "$fieldGlue (trim($concatSql) $valueSql or "."vtiger_groups.groupname $valueSql)";
				} elseif($field->getFieldDataType() == 'date' && ($baseModule == 'Events' || $baseModule == 'Calendar') && ($fieldName == 'date_start' || $fieldName == 'due_date')) {
					$value = $conditionInfo['value'];
					$operator = $conditionInfo['operator'];
					if($fieldName == 'date_start') {
						$dateFieldColumnName = 'vtiger_activity.date_start';
						$timeFieldColumnName = 'vtiger_activity.time_start';
					} else {
						$dateFieldColumnName = 'vtiger_activity.due_date';
						$timeFieldColumnName = 'vtiger_activity.time_end';
					}
					if($operator == 'bw') {
						$values = explode(',', $value);
						$startDateValue = explode(' ', $values[0]);
						$endDateValue = explode(' ', $values[1]);
						if(count($startDateValue) == 2 && count($endDateValue) == 2) {
							$fieldSql .= " CAST(CONCAT($dateFieldColumnName,' ',$timeFieldColumnName) AS DATETIME) $valueSql";
						} else {
							$fieldSql .= "$dateFieldColumnName $valueSql";
						}
					} else {
						if(is_array($value)){
						    $value = $value[0];
						}
						$values = explode(' ', $value);
						if(count($values) == 2) {
							$fieldSql .= "$fieldGlue CAST(CONCAT($dateFieldColumnName,' ',$timeFieldColumnName) AS DATETIME) $valueSql ";
						} else {
							$fieldSql .= "$fieldGlue $dateFieldColumnName $valueSql";
						}
					}
				} elseif($field->getFieldDataType() == 'datetime') {
					$value = $conditionInfo['value'];
					$operator = strtolower($conditionInfo['operator']);
					if($operator == 'bw') {
						$values = explode(',', $value);
						$startDateValue = explode(' ', $values[0]);
						$endDateValue = explode(' ', $values[1]);
						if($startDateValue[1] == '00:00:00' && $endDateValue[1] == '00:00:00') {
							$fieldSql .= "$fieldGlue CAST(".$field->getTableName().'.'.$field->getColumnName()." AS DATE) $valueSql";
						} else {
							$fieldSql .= "$fieldGlue ".$field->getTableName().'.'.$field->getColumnName().' '.$valueSql;
						}
					} elseif($operator == 'between' || $operator == 'notequal' || $operator == 'a' || $operator == 'b') {
						$fieldSql .= "$fieldGlue ".$field->getTableName().'.'.$field->getColumnName().' '.$valueSql;
					} else {
						$values = explode(' ', $value);
						if($values[1] == '00:00:00') {
							$fieldSql .= "$fieldGlue CAST(".$field->getTableName().'.'.$field->getColumnName()." AS DATE) $valueSql";
						} else {
							$fieldSql .= "$fieldGlue ".$field->getTableName().'.'.$field->getColumnName().' '.$valueSql;
						}
					}
				} else {
					if($fieldName == 'birthday' && !$this->isRelativeSearchOperators($conditionInfo['operator'])) {
						$fieldSql .= "$fieldGlue DATE_FORMAT(".$field->getTableName().'.'.
							$field->getColumnName().",'%m%d') ".$valueSql;
					} else {
						$fieldSql .= "$fieldGlue ".$field->getTableName().'.'.
							$field->getColumnName().' '.$valueSql;
					}
				}
				if($conditionInfo['operator'] == 'n' && ($field->getFieldDataType() == 'owner' || $field->getFieldDataType() == 'picklist') ) {
					$fieldGlue = ' AND';
				}
				//ED150622
				elseif($conditionInfo['operator'] == 'ca' || $conditionInfo['operator'] == 'ka') {
					$fieldGlue = ' AND';
				} else {
					$fieldGlue = ' OR';
				}
			}
			$fieldSql .= ')';
			$fieldSqlList[$index] = $fieldSql;
		}
		foreach ($this->manyToManyRelatedModuleConditions as $index=>$conditionInfo) {
			$relatedModuleMeta = RelatedModuleMeta::getInstance($this->meta->getTabName(),
					$conditionInfo['relatedModule']);
			$relationInfo = $relatedModuleMeta->getRelationMeta();
			$relatedModule = $this->meta->getTabName();
			$fieldSql = "(".
				$relationInfo['relationTable'].'.'.$relationInfo[$conditionInfo['column']].
				$conditionInfo['SQLOperator'].
				$conditionInfo['value'].
				")";
			$fieldSqlList[$index] = $fieldSql;
		}

		// This is added to support reference module fields
		if($this->referenceModuleField) {
			foreach ($this->referenceModuleField as $index=>$conditionInfo) {
				$handler = vtws_getModuleHandlerFromName($conditionInfo['relatedModule'], $current_user);
				$meta = $handler->getMeta();
				$fieldName = $conditionInfo['fieldName'];
				$fields = $meta->getModuleFields();
				$fieldObject = $fields[$fieldName];
				$columnName = $fieldObject->getColumnName();
				$tableName = $fieldObject->getTableName();
				$valueSQL = $this->getConditionValue($conditionInfo['value'], $conditionInfo['SQLOperator'], $fieldObject);
				$fieldSql = "(".$tableName.$conditionInfo['referenceField'].'.'.$columnName.' '.$valueSQL[0].")";
				$fieldSqlList[$index] = $fieldSql;
			}
		}
		
		//echo('<br>$fieldSqlList'); var_dump($fieldSqlList);
		// This is needed as there can be condition in different order and there is an assumption in makeGroupSqlReplacements API
		// that it expects the array in an order and then replaces the sql with its the corresponding place
		ksort($fieldSqlList);
		//echo('<br>$groupSql'); var_dump($groupSql);
		$groupSql = $this->makeGroupSqlReplacements($fieldSqlList, $groupSql);
		//echo('<br>$groupSql'); var_dump($groupSql);
		if($this->conditionInstanceCount > 0) {
			$this->conditionalWhere = $groupSql;
			$sql .= $groupSql;
		}
		$sql .= " AND $baseTable.$baseTableIndex > 0";
		$this->whereClause = $sql;
		return $sql;
	}

	/** ED151119
	 * @param $fieldName === $tableName.$fieldName
	 */
	private function getGenericField($fieldName, $dataType){
		$fieldName = explode('.', $fieldName);
		global $adb;
		$field = WebserviceField::fromArray($adb, array(
			'tablename' => $fieldName[0],
			'columnname' => $fieldName[1],
			'fieldlabel' => $fieldName[1],
			'typeofdata' => $dataType . '~O',
		));
		return $field;
	}
	
	/** ED151119
	 * Retourne un WHERE pour les filtres sur les champs de table de relation
	 *
	 */
	private function getRelationFiltersSQLWhere($relationTableName, $relationFilters){
		if(!$relationFilters)
			return '';
		
		$queryGenerator = new QueryGenerator($this->module, $this->user);
		
		$query = '';
		$glue = false;
		foreach($relationFilters as $relationFilter){
			if($relationTableName !== $relationFilter['table']){
				echo "<pre>ERREUR in ".__FILE__.'::getRelationFiltersSQLWhere() : $relationTableName !== $relationFilter[\'table\']'.
				' ('.$relationTableName.' !== '.$relationFilter['table']
				."</pre>";
				continue;
			}
			if(!$glue)
				$glue = 'WHERE';
			else
				$glue = 'AND';
			//Ajoute une condition de jointure
			$queryGenerator->addSQLCondition(
				$query,
				$glue,
				'`'.$relationTableName.'`.'.$relationFilter['column'],
				$relationFilter['value'],
				$relationFilter['comparator'],
				$relationFilter['dataType']
			);
		}
		$queryGenerator->groupInfo = $query;
		//Traitement
		$queryGenerator->getWhereClause();
		
		$query = $queryGenerator->conditionalWhere;
		return $query;
	}
	
	/**
	 *
	 * @param mixed $value
	 * @param String $operator
	 * @param WebserviceField $field
	 */
	private function getConditionValue($value, $operator, $field) {
		$operator = strtolower($operator);
		$db = PearDatabase::getInstance();
		$fieldDataType = $field->getFieldDataType();//ED150302
		if(is_string($value)
		&& $this->ignoreComma == false) {
			//ED150302 sic
			$valueArray = explode(',' , $value);
			if ($fieldDataType === 'multipicklist' && in_array($operator, array('e', 'n'))) {
				$valueArray = getCombinations($valueArray);
				foreach ($valueArray as $key => $value) {
					$valueArray[$key] = ltrim($value, ' |##| ');
				}
			}
		} elseif(is_array($value)) {
			$valueArray = $value;
		} else{
			$valueArray = array($value);
		}
		$sql = array();
		if($operator == 'between' || $operator == 'bw' || $operator == 'notequal') {
			if($field->getFieldName() == 'birthday') {
				$valueArray[0] = getValidDBInsertDateTimeValue($valueArray[0]);
				$valueArray[1] = getValidDBInsertDateTimeValue($valueArray[1]);
				$sql[] = "BETWEEN DATE_FORMAT(".$db->quote($valueArray[0]).", '%m%d') AND ".
						"DATE_FORMAT(".$db->quote($valueArray[1]).", '%m%d')";
			} else {
				if($this->isDateType($fieldDataType)) {
					$valueArray[0] = getValidDBInsertDateTimeValue($valueArray[0]);
					$dateTimeStart = explode(' ',$valueArray[0]);
					if($dateTimeStart[1] == '00:00:00' && $operator != 'between') {
						$valueArray[0] = $dateTimeStart[0];
					}
					$valueArray[1] = getValidDBInsertDateTimeValue($valueArray[1]);
					$dateTimeEnd = explode(' ', $valueArray[1]);
					if($dateTimeEnd[1] == '00:00:00') {
						$valueArray[1] = $dateTimeEnd[0];
					}
				}
				
				if($operator == 'notequal') {
					$sql[] = "NOT BETWEEN ".$db->quote($valueArray[0])." AND ".
							$db->quote($valueArray[1]);
				} else {
					$sql[] = "BETWEEN ".$db->quote($valueArray[0])." AND ".
							$db->quote($valueArray[1]);
				}
			}
			return $sql;
		}
		foreach ($valueArray as $value) {
			if(!$this->isStringType($fieldDataType)) {
				$value = trim($value);
			}
			if ($operator == 'empty' || $operator == 'y' || $operator == 'ny') {
				if($operator == 'ny')
					$sql[] = sprintf("IS NOT NULL AND %s <> ''", $this->getSQLColumn($field->getFieldName()));
				else
				$sql[] = sprintf("IS NULL OR %s = ''", $this->getSQLColumn($field->getFieldName()));
				continue;
			}
			if((strtolower(trim($value)) == 'null')
			|| (trim($value) == '' && !$this->isStringType($fieldDataType))
			&& ($operator == 'e' || $operator == 'n')) {
				if($operator == 'e'){
					$sql[] = "IS NULL";
					continue;
				}
				$sql[] = "IS NOT NULL";
				continue;
			} elseif($fieldDataType == 'boolean') {
				$value = strtolower($value);
				if ($value == 'yes') {
					$value = 1;
				} elseif($value == 'no') {
					$value = 0;
				}
			} elseif($this->isDateType($fieldDataType)) {
				
				//ED150429 :
				// Date "<2015" === "< 2015-01-01"
				// Date ">2015" === "> 2015-12-31"
				// Date ">=2015" === ">= 2015-01-01"
				if(is_numeric($value) && $value > 2000){
					//$fieldName = "YEAR($fieldName)";
					//TODO non satisfaisant, il faudrait le traiter dans le getWhereClause
					$value = $value . '-01-01';
				}
				else {
					$value = getValidDBInsertDateTimeValue($value);
					$dateTime = explode(' ', $value);
					if($dateTime[1] == '00:00:00') {
						$value = $dateTime[0];
					}
				}
			}
			//ED151005
			elseif($this->isNumericType($fieldDataType)) {
				switch($operator) {
				case 's': 
					$operator = 'e';
					break;
				case 'k':
					$operator = 'n';
					break;
				}
			}

			if($field->getFieldName() == 'birthday' && !$this->isRelativeSearchOperators($operator)) {
				$value = "DATE_FORMAT(".$db->quote($value).", '%m%d')";
			} else {
				$value = $db->sql_escape_string($value);
			}
			if(trim($value) == '' && ($operator == 's' || $operator == 'ew' || $operator == 'c' || $operator == 'ct' || $operator == 'ca') //ED150619 ct
				&& ($this->isStringType($fieldDataType) ||
					$fieldDataType == 'picklist' ||
					$fieldDataType == 'multipicklist' ||
					$fieldDataType == 'buttonset')) {
				$sql[] = "LIKE '%'";
				continue;
			}

			if(trim($value) == '' && ($operator == 'k' || $operator == 'kt' || $operator == 'ka') && //ED150619 kt
					$this->isStringType($fieldDataType)) {
				$sql[] = "NOT LIKE ''";
				continue;
			}

			switch($operator) {
				case 'e': $sqlOperator = "=";
					break;
				case 'n': $sqlOperator = "<>";
					break;
				case 's': $sqlOperator = "LIKE";
					$value = "$value%";
					break;
				case 'ew': $sqlOperator = "LIKE";
					$value = "%$value";
					break;
				case 'ct'://ED150619
				case 'ca'://ED150619
				case 'c': $sqlOperator = "LIKE";
					$value = "%$value%";
					break;
				case 'kt'://ED150619
				case 'ka'://ED150619
				case 'k': $sqlOperator = "NOT LIKE";
					$value = "%$value%";
					break;
				case 'l': $sqlOperator = "<";
					break;
				case 'g': $sqlOperator = ">";
					break;
				case 'm': $sqlOperator = "<=";
					break;
				case 'h': $sqlOperator = ">=";
					break;
				case 'a': $sqlOperator = ">";
					break;
				case 'b': $sqlOperator = "<";
					break;
				/*ED150307*/
				case 'vwi': $sqlOperator = " IN ";
					break;
				case 'vwx': $sqlOperator = " NOT IN ";
					break;
			}
			if(!$this->isNumericType($fieldDataType)
				&& !$this->isViewOperators($operator) //ED150307
				&& ($field->getFieldName() != 'birthday'
				   || ($field->getFieldName() == 'birthday' && $this->isRelativeSearchOperators($operator)))){
				$value = "'$value'";
			}
			if($this->isNumericType($fieldDataType) && empty($value)) {
				$value = '0';
			}
			$sql[] = "$sqlOperator $value";
		}
		return $sql;
	}

	private function makeGroupSqlReplacements($fieldSqlList, $groupSql) {
		$pos = 0;
		$nextOffset = 0;
		foreach ($fieldSqlList as $index => $fieldSql) {
			
			//ED150522 builded with $this->groupInfo .= " /*VAR*/$conditionNumber/*/VAR*/ ";/*ED150522 adds /*VAR*/
			$key = " /*VAR*/$index/*/VAR*/ ";
			$pos = strpos($groupSql, $key, $nextOffset);
			if($pos !== false) {
				$beforeStr = substr($groupSql,0,$pos);
				$afterStr = substr($groupSql, $pos + strlen($key));
				$nextOffset = strlen($beforeStr.$fieldSql);
				$groupSql = $beforeStr.$fieldSql.$afterStr;
			}
		}
		return $groupSql;
	}

	private function isRelativeSearchOperators($operator) {
		$nonDaySearchOperators = array('l','g','m','h');
		return in_array($operator, $nonDaySearchOperators);
	}
	/* ED150307*/
	private function isViewOperators($operator) {
		$viewOperators = array('vwi','vwx');
		return in_array($operator, $viewOperators);
	}
	private function isNumericType($type) {
		return ($type == 'integer' || $type == 'double' || $type == 'currency');
	}

	private function isStringType($type) {
		return ($type == 'string' || $type == 'text' || $type == 'email' || $type == 'reference');
	}

	/** ED150904
	 * picklist, multipicklist and buttonSet
	 */
	private function isEnumerableType($type) {
		return ($type == 'picklist' || $type == 'multipicklist' || $type == 'buttonSet');
	}

	private function isDateType($type) {
		return ($type == 'date' || $type == 'datetime');
	}
	
	public function fixDateTimeValue($name, $value, $first = true) {
		$moduleFields = $this->meta->getModuleFields();
		$field = $moduleFields[$name];
		$type = $field ? $field->getFieldDataType() : false;
		if($type == 'datetime') {
			if(strrpos($value, ' ') === false) {
				if($first) {
					return $value.' 00:00:00';
				}else{
					return $value.' 23:59:59';
				}
			}
		}
		return $value;
	}

	public function addCondition($fieldname,$value,$operator,$glue= null,$newGroup = false,
		$newGroupType = null, $ignoreComma = false) {
		$conditionNumber = $this->conditionInstanceCount++;
		if($glue != null && $conditionNumber > 0){
			$this->addConditionGlue ($glue);
		}
		//else
		//	var_dump($conditionNumber, $fieldname, $glue, $operator, $value);
		$this->groupInfo .= " /*VAR*/$conditionNumber/*/VAR*/ ";/*ED150522 adds /*VAR*/
		$this->whereFields[] = $fieldname;
		$this->ignoreComma = $ignoreComma;
		$this->reset();
		$this->conditionals[$conditionNumber] = $this->getConditionalArray($fieldname,
				$value, $operator);
	}

	public function addRelatedModuleCondition($relatedModule, $column, $value, $SQLOperator) {
		$conditionNumber = $this->conditionInstanceCount++;
		$this->groupInfo .= " /*VAR*/$conditionNumber/*/VAR*/ ";/*ED150522 adds /*VAR*/
		$this->manyToManyRelatedModuleConditions[$conditionNumber] = array('relatedModule'=>
			$relatedModule,'column'=>$column,'value'=>$value,'SQLOperator'=>$SQLOperator);
	}

	public function addReferenceModuleFieldCondition($relatedModule, $referenceField, $fieldName, $value, $SQLOperator, $glue=null) {
		$conditionNumber = $this->conditionInstanceCount++;
		if($glue != null && $conditionNumber > 0)
			$this->addConditionGlue($glue);

		$this->groupInfo .= " /*VAR*/$conditionNumber/*/VAR*/ ";/*ED150522 adds /*VAR*/
		$this->referenceModuleField[$conditionNumber] = array('relatedModule'=> $relatedModule,'referenceField'=> $referenceField,'fieldName'=>$fieldName,'value'=>$value,
			'SQLOperator'=>$SQLOperator);
	}

	/** ED151119
	 * Add a new condition value to a string
	 */
	public function addSQLCondition(&$sqlString, $glue, $fieldname, $value, $operator, $dataType) {
		$conditionNumber = $this->conditionInstanceCount++;
		if($glue)
			$sqlString .= " $glue ";
		$sqlString .= " /*VAR*/$conditionNumber/*/VAR*/ ";/*ED150522 adds /*VAR*/
		$this->conditionals[$conditionNumber] = $this->getConditionalArray(
				$fieldname, $value, $operator, $dataType);
	}

	/**
	 * @param $dataType ED151119
	 */
	private function getConditionalArray($fieldname,$value,$operator, $dataType = false) {
		if(is_string($value)) {
			$value = trim($value);
		} elseif(is_array($value)) {
			$value = array_map(trim, $value);
		}
		return array('name'=>$fieldname,'value'=>$value,'operator'=>$operator,'dataType'=>$dataType);
	}

	private $groupCounter = 0;
	public function startGroup($groupType, $debug = '') {
		$this->groupCounter++;
		$this->groupInfo .= " $groupType /*startGroup $debug*/(";
	}

	public function endGroup($debug = '') {
		$this->groupInfo .= ")/*endGroup $debug*/";
		$this->groupCounter--;
	}

	public function addConditionGlue($glue) {
		$this->groupInfo .= " $glue ";
	}

	public function addUserSearchConditions($input) {
		global $log,$default_charset;
		if($input['searchtype']=='advance') {

			$json = new Zend_Json();
			$advft_criteria = $_REQUEST['advft_criteria'];
			if(!empty($advft_criteria))	$advft_criteria = $json->decode($advft_criteria);
			$advft_criteria_groups = $_REQUEST['advft_criteria_groups'];
			if(!empty($advft_criteria_groups))	$advft_criteria_groups = $json->decode($advft_criteria_groups);

			if(empty($advft_criteria) || count($advft_criteria) <= 0) {
				return ;
			}

			$advfilterlist = getAdvancedSearchCriteriaList($advft_criteria, $advft_criteria_groups, $this->getModule());

			if(empty($advfilterlist) || count($advfilterlist) <= 0) {
				return ;
			}
			if($this->conditionInstanceCount > 0) {
				$this->startGroup(self::$AND);
			} else {
				$this->startGroup('');
			}
			
			foreach ($advfilterlist as $groupindex=>$groupcolumns) {
				$filtercolumns = $groupcolumns['columns'];
				if(count($filtercolumns) > 0) {
					$this->startGroup('');
					foreach ($filtercolumns as $index=>$filter) {
						$name = explode(':',$filter['columnname']);
						if(empty($name[2]) && $name[1] == 'crmid' && $name[0] == 'vtiger_crmentity') {
							$name = $this->getSQLColumn('id');
						} else {
							$name = $name[2];
						}
						$this->addCondition($name, $filter['value'], $filter['comparator']);
						$columncondition = $filter['column_condition'];
						if(!empty($columncondition)) {
							$this->addConditionGlue($columncondition);
						}
					}
					$this->endGroup();
					$groupConditionGlue = $groupcolumns['condition'];
					if(!empty($groupConditionGlue))
						$this->addConditionGlue($groupConditionGlue);
				}
			}
			$this->endGroup();
		} elseif($input['type']=='dbrd') {
			if($this->conditionInstanceCount > 0) {
				$this->startGroup(self::$AND);
			} else {
				$this->startGroup('');
			}
			$allConditionsList = $this->getDashBoardConditionList();
			$conditionList = $allConditionsList['conditions'];
			$relatedConditionList = $allConditionsList['relatedConditions'];
			$noOfConditions = count($conditionList);
			$noOfRelatedConditions = count($relatedConditionList);
			foreach ($conditionList as $index=>$conditionInfo) {
				$this->addCondition($conditionInfo['fieldname'], $conditionInfo['value'],
						$conditionInfo['operator']);
				if($index < $noOfConditions - 1 || $noOfRelatedConditions > 0) {
					$this->addConditionGlue(self::$AND);
				}
			}
			foreach ($relatedConditionList as $index => $conditionInfo) {
				$this->addRelatedModuleCondition($conditionInfo['relatedModule'],
						$conditionInfo['conditionModule'], $conditionInfo['finalValue'],
						$conditionInfo['SQLOperator']);
				if($index < $noOfRelatedConditions - 1) {
					$this->addConditionGlue(self::$AND);
				}
			}
			$this->endGroup();
		} else {
			if(isset($input['search_field']) && $input['search_field'] != "") {
				$search_fields = $input['search_field'];
				$search_texts = $input['search_text'];
				$operators = $input['operator'];
				//ED150414 may be array of fields, then values and operators are also arrays
				if(is_array($search_fields)){
					if(!is_array($search_texts))
						$search_texts = array($search_texts);
					if(!is_array($operators))
						$operators = array($operators);
					$startingGroup = false;
					for($i = 0; $i < count($search_fields) && $i < count($search_texts); $i++)
						if(!$search_fields[$i]){
							$this->addConditionGlue($operators[$i]);
							$startingGroup = true;
						}
						elseif(!($search_texts[$i] == '' && $operators[$i] == 'c')){
							$this->addUserSearchConditionUnique($search_fields[$i], $search_texts[$i], $operators[$i], $startingGroup);
							$startingGroup = false;
						}
				}
				else	//original
					$this->addUserSearchConditionUnique($search_fields, $search_texts, $operators);
			}
		}
	}
	
	/* ED150414
	 * Add a group
	 * (extracted from above)
	 *
	 * ED150527 : $search_field, $search_text, $operator may be arrays for sub conditions
	 * see modules/Contacts/models/ListView.php, function getListViewEntries()
	 * see modules/Inventory/views/ProductsPopup.php, function initializeListViewContents()
		$searchKey 	= array(array('productname', '', 'productcode'));
		$searchValue= array(array($searchValue, '', $searchValue));
		$operator 	= array(array('s', 'OR', 's'));
		
	 * see modules\Contacts\views\PopupAjax.php
		$searchKey 	= array(array('lastname', 'firstname'), null, array('lastname', 'firstname'));
		$searchValue= array(array($searchValues[0], $searchValues[1]), null, array($searchValues[1], $searchValues[0]));
		$operator 	= array(array('s', 's'), 'OR', array('s', 's'));
	 **/
	private function addUserSearchConditionUnique($search_field, $search_text, $operator, $startingGroup = null){
		//var_dump(__FILE__."::addUserSearchConditionsFromInput()", $search_field, $search_text, $operator, $startingGroup);
		if(!$search_field){
			echo 'addUserSearchConditionUnique : !$search_field !!!';
			echo_callstack();
			return;
		}
		if(is_array($search_field)){
			//Array means OR conditions
			if(!$startingGroup && $this->conditionInstanceCount > 0) {
				$this->startGroup(self::$AND);
			} else {
				$this->startGroup('');
			}
			$previousIsLogicalOperator = false;
			for($i = 0; $i < count($search_field); $i++){
				if(!$search_field[$i]){ //ONLY for logicial operator
					$this->addConditionGlue($operator[$i]);
					$previousIsLogicalOperator = true;
					continue;
				}
				elseif (!$previousIsLogicalOperator && $i > 0){
					$this->addConditionGlue(self::$AND);
					$previousIsLogicalOperator = false;
				}
				
				$this->addUserSearchConditionUnique($search_field[$i], $search_text[$i], $operator[$i], true);
			}
			
			$this->endGroup();
			return;
		}
		//var_dump(__FILE__."::addUserSearchConditionsFromInput()", $search_field, $search_text, $operator);
		$fieldName=vtlib_purify($search_field);
		if(!$startingGroup && $this->conditionInstanceCount > 0) {
			$this->startGroup(self::$AND);
		} else {
			$this->startGroup('');
		}
		$moduleFields = $this->meta->getModuleFields();
		$field = $moduleFields[$fieldName];
		
		//ED150628
		if(!$field && $fieldName == 'id'){
			//$fieldName = $this->meta->getIdColumn();
			$type = 'integer';
		}
		else
			$type = $field->getFieldDataType();
			
		if(isset($search_text) && $search_text!="") {
			// search other characters like "|, ?, ?" by jagi
			$value = $search_text;
			if(!$this->isStringType($type)
			&& !$this->isEnumerableType($type) //ED150904
			) {
				//var_dump($fieldName, $type, $value, $this->isStringType($type));
				$stringConvert = function_exists(iconv) ? @iconv("UTF-8",$default_charset,$value)
						: $value;
				if($stringConvert !== FALSE) { //ED150605 ne rien faire en cas d'erreur de traduction
				   $value=trim($stringConvert);
				}
			}

			switch($type){
			case 'picklist':
				//ED150605 : ici, vu bug où la valeur du pick list vaut le nom traduit du module (mais ça pourrait être n'importe quelle traduction) et pourrit la requête
				global $mod_strings;
				// Get all the keys for the for the Picklist value
				$mod_keys = array_keys($mod_strings, $value);
				if(sizeof($mod_keys) >= 1) {
					// Iterate on the keys, to get the first key which doesn't start with LBL_      (assuming it is not used in PickList)
					foreach($mod_keys as $mod_idx=>$mod_key) {
						$stridx = strpos($mod_key, 'LBL_');
						// Use strict type comparision, refer strpos for more details
						if ($stridx !== 0) {
							$value = $mod_key;
							break;
						}
					}
				}
				break;
			case 'currency' :
				// Some of the currency fields like Unit Price, Total, Sub-total etc of Inventory modules, do not need currency conversion
				if($field->getUIType() == '72') {
					$value = CurrencyField::convertToDBFormat($value, null, true);
				} else {
					$currencyField = new CurrencyField($value);
					$value = $currencyField->getDBInsertedValue();
				}
				break;
			}			
		}
		if(empty($operator)) {
			if(trim(strtolower($value)) == 'null'){
				$operator = 'e';
			} else {
				if(!$this->isNumericType($type) && !$this->isDateType($type)) {
					$operator = 'c';
				} else {
					$operator = 'h';
				}
			}
		}
		$this->addCondition($fieldName, $value, $operator);
		$this->endGroup();
	}

	public function getDashBoardConditionList() {
		if(isset($_REQUEST['leadsource'])) {
			$leadSource = $_REQUEST['leadsource'];
		}
		if(isset($_REQUEST['date_closed'])) {
			$dateClosed = $_REQUEST['date_closed'];
		}
		if(isset($_REQUEST['sales_stage'])) {
			$salesStage = $_REQUEST['sales_stage'];
		}
		if(isset($_REQUEST['closingdate_start'])) {
			$dateClosedStart = $_REQUEST['closingdate_start'];
		}
		if(isset($_REQUEST['closingdate_end'])) {
			$dateClosedEnd = $_REQUEST['closingdate_end'];
		}
		if(isset($_REQUEST['owner'])) {
			$owner = vtlib_purify($_REQUEST['owner']);
		}
		if(isset($_REQUEST['campaignid'])) {
			$campaignId = vtlib_purify($_REQUEST['campaignid']);
		}
		if(isset($_REQUEST['quoteid'])) {
			$quoteId = vtlib_purify($_REQUEST['quoteid']);
		}
		if(isset($_REQUEST['invoiceid'])) {
			$invoiceId = vtlib_purify($_REQUEST['invoiceid']);
		}
		if(isset($_REQUEST['purchaseorderid'])) {
			$purchaseOrderId = vtlib_purify($_REQUEST['purchaseorderid']);
		}

		$conditionList = array();
		if(!empty($dateClosedStart) && !empty($dateClosedEnd)) {

			$conditionList[] = array('fieldname'=>'closingdate', 'value'=>$dateClosedStart,
				'operator'=>'h');
			$conditionList[] = array('fieldname'=>'closingdate', 'value'=>$dateClosedEnd,
				'operator'=>'m');
		}
		if(!empty($salesStage)) {
			if($salesStage == 'Other') {
				$conditionList[] = array('fieldname'=>'sales_stage', 'value'=>'Closed Won',
					'operator'=>'n');
				$conditionList[] = array('fieldname'=>'sales_stage', 'value'=>'Closed Lost',
					'operator'=>'n');
			} else {
				$conditionList[] = array('fieldname'=>'sales_stage', 'value'=> $salesStage,
					'operator'=>'e');
			}
		}
		if(!empty($leadSource)) {
			$conditionList[] = array('fieldname'=>'leadsource', 'value'=>$leadSource,
					'operator'=>'e');
		}
		if(!empty($dateClosed)) {
			$conditionList[] = array('fieldname'=>'closingdate', 'value'=>$dateClosed,
					'operator'=>'h');
		}
		if(!empty($owner)) {
			$conditionList[] = array('fieldname'=>'assigned_user_id', 'value'=>$owner,
					'operator'=>'e');
		}
		$relatedConditionList = array();
		if(!empty($campaignId)) {
			$relatedConditionList[] = array('relatedModule'=>'Campaigns','conditionModule'=>
				'Campaigns','finalValue'=>$campaignId, 'SQLOperator'=>'=');
		}
		if(!empty($quoteId)) {
			$relatedConditionList[] = array('relatedModule'=>'Quotes','conditionModule'=>
				'Quotes','finalValue'=>$quoteId, 'SQLOperator'=>'=');
		}
		if(!empty($invoiceId)) {
			$relatedConditionList[] = array('relatedModule'=>'Invoice','conditionModule'=>
				'Invoice','finalValue'=>$invoiceId, 'SQLOperator'=>'=');
		}
		if(!empty($purchaseOrderId)) {
			$relatedConditionList[] = array('relatedModule'=>'PurchaseOrder','conditionModule'=>
				'PurchaseOrder','finalValue'=>$purchaseOrderId, 'SQLOperator'=>'=');
		}
		return array('conditions'=>$conditionList,'relatedConditions'=>$relatedConditionList);
	}

	public function initForGlobalSearchByType($type, $value, $operator='s') {
		
		$fieldList = $this->meta->getFieldNameListByType($type);
		if($this->conditionInstanceCount <= 0) {
			$this->startGroup('');
		} else {
			$this->startGroup(self::$AND);
		}
		$nameFieldList = explode(',',$this->getModuleNameFields($this->module));
		foreach ($nameFieldList as $nameList) {
			$field = $this->meta->getFieldByColumnName($nameList);
			$this->fields[] = $field->getFieldName();
		}
		foreach ($fieldList as $index => $field) {
			$fieldName = $this->meta->getFieldByColumnName($field);
			$this->fields[] = $fieldName->getFieldName();
			if($index > 0) {
				$this->addConditionGlue(self::$OR);
			}
			$this->addCondition($fieldName->getFieldName(), $value, $operator);
		}
		$this->endGroup();
		if(!in_array('id', $this->fields)) {
				$this->fields[] = 'id';
		}
	}
	
	//ED150308 : more advanced filters (given by code)
	public function setAdvFilterListMore($list){
		$this->advFilterListMore = $list;
	}
	public function getAdvFilterListMore(){
		return $this->advFilterListMore;
	}
}
?>