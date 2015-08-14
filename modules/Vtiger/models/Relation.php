<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_Relation_Model extends Vtiger_Base_Model{

	protected $parentModule = false;
	protected $relatedModule = false;

	protected $relationType = false;

	//one to many
	const RELATION_DIRECT = 1;

	//Many to many and many to one
	const RELATION_INDIRECT = 2;
	
	/**
	 * Function returns the relation id
	 * @return <Integer>
	 */
	public function getId(){
		return $this->get('relation_id');
	}

	/**
	 * Function sets the relation's parent module model
	 * @param <Vtiger_Module_Model> $moduleModel
	 * @return Vtiger_Relation_Model
	 */
	public function setParentModuleModel($moduleModel){
		$this->parentModule = $moduleModel;
		return $this;
	}

	/**
	 * Function that returns the relation's parent module model
	 * @return <Vtiger_Module_Model>
	 */
	public function getParentModuleModel(){
		if(empty($this->parentModule)){
			$this->parentModule = Vtiger_Module_Model::getInstance($this->get('tabid'));
		}
		return $this->parentModule;
	}

	public function getRelationModuleModel(){
		if(empty($this->relatedModule)){
			$this->relatedModule = Vtiger_Module_Model::getInstance($this->get('related_tabid'));
		}
		return $this->relatedModule;
	}
    
	public function getRelationModuleName() {
		$relationModuleName = $this->get('relatedModuleName');
		if(!empty($relationModuleName)) {
		    return $relationModuleName;
		}
		return $this->getRelationModuleModel()->getName();
	}

	public function getListUrl($parentRecordModel) {
		return 'module='.$this->getParentModuleModel()->get('name').'&relatedModule='.$this->get('modulename').
				'&view=Detail&record='.$parentRecordModel->getId().'&mode=showRelatedList';
	}

	public function setRelationModuleModel($relationModel){
		$this->relatedModule = $relationModel;
		return $this;
	}

	public function isActionSupported($actionName){
		$actionName = strtolower($actionName);
		$actions = $this->getActions();
		foreach($actions as $action) {
			if(strcmp(strtolower($action), $actionName)== 0){
				return true;
			}
		}
		return false;
	}

	public function isSelectActionSupported() {
		return $this->isActionSupported('select');
	}

	public function isAddActionSupported() {
		return $this->isActionSupported('add');
	}

	/* ED150124 */
	public function isDeleteActionSupported() {
		return $this->isActionSupported('delete');
	}

	/* ED150811 */
	public function isPrintActionSupported() {
		return $this->isActionSupported('print');
	}

	public function getActions(){
		$actionString = $this->get('actions');
		$label = $this->get('label');
		// No actions for Activity history
		if($label == 'Activity History') {
			return array();
		}

		return explode(',', $actionString);
	}

	public function getQuery($parentRecord, $actions=false){
		$parentModuleModel = $this->getParentModuleModel();
		$relatedModuleModel = $this->getRelationModuleModel();
		$parentModuleName = $parentModuleModel->getName();
		$relatedModuleName = $relatedModuleModel->getName();
		$functionName = $this->get('name');
		$query = $parentModuleModel->getRelationQuery($parentRecord->getId(), $functionName, $relatedModuleModel);
		
		return $query;
	}

	public function addRelation($sourcerecordId, $destinationRecordId) {
		$sourceModule = $this->getParentModuleModel();
		$sourceModuleName = $sourceModule->get('name');
		$sourceModuleFocus = CRMEntity::getInstance($sourceModuleName);
		$destinationModuleName = $this->getRelationModuleModel()->get('name');
		relateEntities($sourceModuleFocus, $sourceModuleName, $sourcerecordId, $destinationModuleName, $destinationRecordId);
	}

	/* 
	 * ED150124 : $relatedRecordId == '*' : all relations are deleted
	 */
	public function deleteRelation($sourceRecordId, $relatedRecordId){
		$sourceModule = $this->getParentModuleModel();
		$sourceModuleName = $sourceModule->get('name');
		$destinationModuleName = $this->getRelationModuleModel()->get('name');
		$destinationModuleFocus = CRMEntity::getInstance($destinationModuleName);
		DeleteEntity($destinationModuleName, $sourceModuleName, $destinationModuleFocus, $relatedRecordId, $sourceRecordId);
		return true;
	}

	public function isDirectRelation() {
		return ($this->getRelationType() == self::RELATION_DIRECT);
	}

	public function getRelationType(){
		if(empty($this->relationType)){
			if ($this->getRelationField()) 
				$this->relationType = self::RELATION_DIRECT;
			else
				$this->relationType = self::RELATION_INDIRECT;
		}
		return $this->relationType;
	}
    
	/**
	 * Function which will specify whether the relation is editable
	 * @return <Boolean>
	 */
	public function isEditable() {
	    return $this->getRelationModuleModel()->isPermitted('EditView');
	}
	
	/**
	 * Function which will specify whether the relation is deletable
	 * @return <Boolean>
	 */
	public function isDeletable() {
	    return $this->getRelationModuleModel()->isPermitted('Delete');
	}

	public static function getInstance($parentModuleModel, $relatedModuleModel, $label=false) {
		if(!is_object($parentModuleModel))
			var_dump("Erreur, parentModuleModel n'est pas un objet : ", $parentModuleModel);
		if(!is_object($relatedModuleModel)){
			echo_callstack();
			var_dump("Erreur, relatedModuleModel n'est pas un objet : ", $relatedModuleModel);
		}
		$db = PearDatabase::getInstance();

		//ED150704 : search prefered label
		$query = 'SELECT vtiger_relatedlists.*,vtiger_tab.name as modulename, IF(label = ?, 0, 1) AS SearchedLabel
					FROM vtiger_relatedlists
					INNER JOIN vtiger_tab on vtiger_tab.tabid = vtiger_relatedlists.related_tabid AND vtiger_tab.presence != 1
					WHERE vtiger_relatedlists.tabid = ? AND related_tabid = ?
					ORDER BY SearchedLabel
					LIMIT 1';
		$params = array();
		$params[] = empty($label) ? '' : $label;
		$params[] = $parentModuleModel->getId();
		$params[] = $relatedModuleModel->getId();

		
		$result = $db->pquery($query, $params);
		if($db->num_rows($result)) {
			$row = $db->query_result_rowdata($result, 0);
			$relationModelClassName = Vtiger_Loader::getComponentClassName('Model', 'Relation', $parentModuleModel->get('name'));
			$relationModel = new $relationModelClassName();
			$relationModel->setData($row)->setParentModuleModel($parentModuleModel)->setRelationModuleModel($relatedModuleModel);
			return $relationModel;
		}
		
		return false;
	}

	public static function getAllRelations($parentModuleModel, $selected = true, $onlyActive = true) {
		//ED150207
		if(is_string($parentModuleModel))
			$parentModuleModel = Vtiger_Module_Model::getInstance($parentModuleModel);
		$db = PearDatabase::getInstance();

		$skipRelationsList = array('get_history');
		$query = 'SELECT vtiger_relatedlists.*,vtiger_tab.name as modulename
			FROM vtiger_relatedlists 
			INNER JOIN vtiger_tab on vtiger_relatedlists.related_tabid = vtiger_tab.tabid
			WHERE vtiger_relatedlists.tabid = ? AND related_tabid != 0';

		if ($selected) {
			$query .= ' AND vtiger_relatedlists.presence <> 1';
		}
		if($onlyActive){
		    $query .= ' AND vtiger_tab.presence <> 1 ';
		}
		$query .= ' AND vtiger_relatedlists.name NOT IN ('.generateQuestionMarks($skipRelationsList).') ORDER BY sequence'; // TODO: Need to handle entries that has related_tabid 0
	
		$result = $db->pquery($query, array($parentModuleModel->getId(), $skipRelationsList));

		$relationModels = array();
		$relationModelClassName = Vtiger_Loader::getComponentClassName('Model', 'Relation', $parentModuleModel->get('name'));
		for($i=0; $i<$db->num_rows($result); $i++) {
			$row = $db->query_result_rowdata($result, $i);
			//$relationModuleModel = Vtiger_Module_Model::getCleanInstance($moduleName);
			// Skip relation where target module does not exits or is no permitted for view.
			if (!Users_Privileges_Model::isPermitted($row['modulename'],'DetailView')) {
				continue;
			}
			$relationModel = new $relationModelClassName();
			$relationModel->setData($row)->setParentModuleModel($parentModuleModel)->set('relatedModuleName',$row['modulename']);
			$relationModels[] = $relationModel;
		}
		return $relationModels;
	}

	/**
	 * Function to get relation field for relation module and parent module
	 * @return Vtiger_Field_Model
	 */
	public function getRelationField() {
		$relationField = $this->get('relationField');
		if (!$relationField) {
			$relationField = false;
			$relatedModel = $this->getRelationModuleModel();
			$parentModule = $this->getParentModuleModel();
			$relatedModelFields = $relatedModel->getFields();

			//echo '<br>$parentModule->getName() '; var_dump($parentModule->getName());
			foreach($relatedModelFields as $fieldName => $fieldModel) {
				if($fieldModel->getFieldDataType() == Vtiger_Field_Model::REFERENCE_TYPE) {
					//echo '<br>$fieldName '; var_dump($fieldName);
					$referenceList = $fieldModel->getReferenceList();
					//echo '<br>$referenceList '; var_dump($referenceList);
					if(in_array($parentModule->getName(), $referenceList)) {
						//echo '<br>OK $fieldModel '; var_dump($fieldModel->getName());
						$this->set('relationField', $fieldModel);
						$relationField = $fieldModel;
						break;
					}
				}
			}
		}
		//echo '<br>return $relationField '; var_dump(get_class($relationField));
		return $relationField;
	}
    
	public static  function updateRelationSequenceAndPresence($relatedInfoList, $sourceModuleTabId) {
	    $db = PearDatabase::getInstance();
	    $query = 'UPDATE vtiger_relatedlists SET sequence=CASE ';
	    $relation_ids = array();
	    foreach($relatedInfoList as $relatedInfo){
		$relation_id = $relatedInfo['relation_id'];
		$relation_ids[] = $relation_id;
		$sequence = $relatedInfo['sequence'];
		$presence = $relatedInfo['presence'];
		$query .= ' WHEN relation_id='.$relation_id.' THEN '.$sequence;
	    }
	    $query.= ' END , ';
	    $query.= ' presence = CASE ';
	    foreach($relatedInfoList as $relatedInfo){
		$relation_id = $relatedInfo['relation_id'];
		$relation_ids[] = $relation_id;
		$sequence = $relatedInfo['sequence'];
		$presence = $relatedInfo['presence'];
		$query .= ' WHEN relation_id='.$relation_id.' THEN '.$presence;
	    }
	    $query .= ' END WHERE tabid=? AND relation_id IN ('.  generateQuestionMarks($relation_ids).')';
	    $result = $db->pquery($query, array($sourceModuleTabId,$relation_ids));
	}
	
	public function isActive() {
		return $this->get('presence') == 0 ? true : false;
	}
	
	/* Returns fields structure defining relation between modules
	 * e.g : date of contact related campaign
	 * ED150212
	 */
	public function getRelationViews() {
		$module = Vtiger_Module_Model::getInstance($this->getRelationModuleName());
		$views = CustomView_Record_Model::getAll($this->getRelationModuleName());
		$structures = array();
		if($views){
			$fields = $this->getRelationFields();
			foreach($views as $viewName => $view){
				$structures[$view->get('viewname')] = array(
							'id' => $view->getId(),
							'name' => $view->get('viewname'),
							'fields' => $fields
						);
			}
			foreach($fields as $field)
				$field->setModule($module);
		}
		//var_dump($structures);
		return $structures; 
		//return Vtiger_RecordStructure_Model::getInstanceForModule($this->getRelationModuleModel())->getStructure();
	}
	/* Returns fields defining relation between modules
	 * Should be overrided (e.g. modules/Contacts/models/Relation.php)
	 * ED150212
	 */
	public function getRelationFields() {
		$fields = array();
		$field = $this->getRelationField();
		if($field)
			$fields[$field->getName()] = $field;
		return $fields;
	}
}
