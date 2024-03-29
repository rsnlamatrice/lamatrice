<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Vtiger Entity Record Model Class
 */
class Vtiger_Record_Model extends Vtiger_Base_Model {

	protected $module = false;

	/**
	 * Function to get the id of the record
	 * @return <Number> - Record Id
	 */
	public function getId() {
		return $this->get('id');
	}

	/**
	 * Function to set the id of the record
	 * @param <type> $value - id value
	 * @return <Object> - current instance
	 */
	public function setId($value) {
		return $this->set('id',$value);
	}

	/**
	 * Fuction to get the Name of the record
	 * @return <String> - Entity Name of the record
	 */
	public function getName() {
		$displayName = $this->get('label');
		if(empty($displayName)) {
			$displayName = $this->getDisplayName();
		}
		return Vtiger_Util_Helper::toSafeHTML(decode_html($displayName));
	}

	/**
	 * Function to get the Module to which the record belongs
	 * @return Vtiger_Module_Model
	 */
	public function getModule() {
		return $this->module;
	}

	/**
	 * Function to set the Module to which the record belongs
	 * @param <String> $moduleName
	 * @return Vtiger_Record_Model or Module Specific Record Model instance
	 */
	public function setModule($moduleName) {
		//ED150507
		if($moduleName instanceof Vtiger_Module_Model)
			$this->module = $moduleName;
		else
			$this->module = Vtiger_Module_Model::getInstance($moduleName);
		return $this;
	}

	/**
	 * Function to set the Module to which the record belongs from the Module model instance
	 * @param <Vtiger_Module_Model> $module
	 * @return Vtiger_Record_Model or Module Specific Record Model instance
	 */
	public function setModuleFromInstance($module) {
		$this->module = $module;
		return $this;
	}

	/**
	 * Function to get the entity instance of the recrod
	 * @return CRMEntity object
	 */
	public function getEntity() {
		//ED151203 : initialize a newly created CRMEntity (needed to get invoice_no in ImportInvoicesFromPrestashop.php)
		if($this->getId() && $this->getId() != $this->entity->id){
			$this->entity->id = $this->getId();
			foreach($this->entity->column_fields as $fieldName => $value)
				if($value = $this->get($fieldName))
					$this->entity->column_fields[$fieldName] = $value;
		}
		return $this->entity;
	}

	/**
	 * Function to set the entity instance of the record
	 * @param CRMEntity $entity
	 * @return Vtiger_Record_Model instance
	 */
	public function setEntity($entity) {
		$this->entity = $entity;
		return $this;
	}

	/**
	 * Function to get raw data
	 * @return <Array>
	 */
	public function getRawData() {
		return $this->rawData;
	}

	/** ED150526
	 * Function to get raw data
	 * @return <Array>
	 */
	public function getRawDataFieldValue($field) {
		return $this->rawData[$field];
	}

	/**
	 * Function to set raw data
	 * @param <Array> $data
	 * @return Vtiger_Record_Model instance
	 */
	public function setRawData($data) {
		
		/*echo "<BR><BR><BR><BR><BR><code>".print_r($data, true)."</code>";
		echo_callstack();*/
		$this->rawData = $data;
		return $this;
	}

	/**
	 * Function to get the Detail View url for the record
	 * @return <String> - Record Detail View Url
	 */
	public function getDetailViewUrl() {
		$module = $this->getModule();
		return 'index.php?module='.$this->getModuleName().'&view='.$module->getDetailViewName().'&record='.$this->getId();
	}

	/**
	 * Function to get the complete Detail View url for the record
	 * @return <String> - Record Detail View Url
	 */
	public function getFullDetailViewUrl() {
		$module = $this->getModule();
		return 'index.php?module='.$this->getModuleName().'&view='.$module->getDetailViewName().'&record='.$this->getId().'&mode=showDetailViewByMode&requestMode=full';
	}

	/**
	 * Function to get the Edit View url for the record
	 * @return <String> - Record Edit View Url
	 */
	public function getEditViewUrl() {
		$module = $this->getModule();
		return 'index.php?module='.$this->getModuleName().'&view='.$module->getEditViewName().'&record='.$this->getId();
	}

	/**
	 * Function to get the Update View url for the record
	 * @return <String> - Record Upadte view Url
	 */
	public function getUpdatesUrl() {
		return $this->getDetailViewUrl()."&mode=showRecentActivities&page=1&tab_label=LBL_UPDATES";
	}

	/**
	 * Function to get the Delete Action url for the record
	 * @return <String> - Record Delete Action Url
	 */
	public function getDeleteUrl() {
		$module = $this->getModule();
		return 'index.php?module='.$this->getModuleName().'&action='.$module->getDeleteActionName().'&record='.$this->getId();
	}

	/**
	 * Function to get the name of the module to which the record belongs
	 * @return <String> - Record Module Name
	 */
	public function getModuleName() {
		return $this->getModule()->get('name');
	}

	/**
	 * Function to get the Display Name for the record
	 * @return <String> - Entity Display Name for the record
	 */
	public function getDisplayName() {
		return getFullNameFromArray($this->getModuleName(),$this->getData());
	}

	/**
	 * Function to retieve display value for a field
	 * @param <String> $fieldName - field name for which values need to get
	 * @param <Integer> $recordId - record
	 * @param <Boolean> if field is unknown, returns the value otherwise FALSE value ED140907
	 *	in .tpl : {$RELATED_RECORD->getDisplayValue($RELATED_HEADERNAME, false, true)}
	 * @return <String>
	 */
	public function getDisplayValue($fieldName, $recordId = false, $unknown_field_returns_value = false) {
		if(empty($recordId)) {
			$recordId = $this->getId();
		}
		if(is_a($fieldName, "Vtiger_Field_Model")){
			$fieldModel = $fieldName;
			$fieldName = $fieldModel->getName();
		} else {
			$fieldModel = $this->getModule()->getField($fieldName);
		}
		
		if($fieldModel) {
			return $fieldModel->getDisplayValue($this->get($fieldName), $recordId, $this);
		}
		if($unknown_field_returns_value)
			return $this->get($fieldName);
		return false;
	}

	/**
	 * Function returns the Vtiger_Field_Model
	 * @param <String> $fieldName - field name
	 * @return <Vtiger_Field_Model>
	 */
	public function getField($fieldName) {
		return $this->getModule()->getField($fieldName);
	}

	/**
	 * Function returns all the field values in user format
	 * @return <Array>
	 */
	public function getDisplayableValues() {
		$displayableValues = array();
		$data = $this->getData();
		foreach($data as $fieldName=>$value) {
			$fieldValue = $this->getDisplayValue($fieldName);
			$displayableValues[$fieldName] = ($fieldValue) ? $fieldValue : $value;
		}
		return $displayableValues;
	}

	/**
	 * Function to save the current Record Model
	 */
	public function save() {
		$this->getModule()->saveRecord($this);
	}

	/** ED150721
	 * Function to save the current Record Model
	 * set global Bulk mode to true to skip handler
	 */
	public function saveInBulkMode() {
		global $VTIGER_BULK_SAVE_MODE;
		$previousBulkSaveMode = $VTIGER_BULK_SAVE_MODE;
		$VTIGER_BULK_SAVE_MODE = true;
		$this->save();
		$VTIGER_BULK_SAVE_MODE = $previousBulkSaveMode;
	}

	/**
	 * Function to delete the current Record Model
	 */
	public function delete() {
		$this->getModule()->deleteRecord($this);
	}

	/**
	 * Static Function to get the instance of a clean Vtiger Record Model for the given module name
	 * @param <String> $moduleName
	 * @return Vtiger_Record_Model or Module Specific Record Model instance
	 */
	public static function getCleanInstance($moduleName) {
		//TODO: Handle permissions
		$focus = CRMEntity::getInstance($moduleName);
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', $moduleName);
		$instance = new $modelClassName();
		return $instance->setData($focus->column_fields)->setModule($moduleName)->setEntity($focus);
	}

	/**
	 * Static Function to get the instance of the Vtiger Record Model given the recordid and the module name
	 * @param <Number> $recordId
	 * @param <String> $moduleName
	 * @return Vtiger_Record_Model or Module Specific Record Model instance
	 */
	public static function getInstanceById($recordId, $module=null) {
		//TODO: Handle permissions
		if(is_object($module) && is_a($module, 'Vtiger_Module_Model')) {
			$moduleName = $module->get('name');
		} elseif (is_string($module)) {
			$module = Vtiger_Module_Model::getInstance($module);
			$moduleName = $module->get('name');
		} elseif(empty($module)) {
			$moduleName = getSalesEntityType($recordId);
			$module = Vtiger_Module_Model::getInstance($moduleName);
		}

		$focus = CRMEntity::getInstance($moduleName);
		$focus->id = $recordId;
		$focus->retrieve_entity_info($recordId, $moduleName);
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', $moduleName);
		$instance = new $modelClassName();
		return $instance->setData($focus->column_fields)->set('id',$recordId)->setModuleFromInstance($module)->setEntity($focus);
	}

	/**
	 * Static Function to get the list of records matching the search key
	 * @param <String> $searchKey
	 * @return <Array> - List of Vtiger_Record_Model or Module Specific Record Model instances
	 */
	public static function getSearchResult($searchKey, $module=false) {
		$db = PearDatabase::getInstance();

		/* ED150122
		 * recherche d'un nombre, sans prcision de module ou Contacts : on cherche dans ref 4D
		 * si le nombre est préfixé de 'C', on cherche dans vtiger_contactdetails.contact_no
		 */
		if((!$module || $module == 'Contacts')
		&& ($searchKey
		    && (is_numeric(trim($searchKey))
			|| (($searchKey[0] == 'c' || $searchKey[0] == 'C')
			    && is_numeric(substr(trim($searchKey),1))))
		)){
			$query = 'SELECT CONCAT(vtiger_crmentity.label, " (", vtiger_contactdetails.contact_no, ")") AS label
				, vtiger_crmentity.crmid, vtiger_crmentity.setype, vtiger_crmentity.createdtime
				FROM vtiger_crmentity
				JOIN vtiger_contactdetails
					ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
				WHERE (vtiger_crmentity.crmid = ?
					OR vtiger_contactdetails.contact_no = CONCAT(\'C\', ?))
				AND vtiger_crmentity.deleted = 0';
			if(!is_numeric(trim($searchKey)))
				$searchKey = substr($searchKey,1);
			$params = array(trim($searchKey), trim($searchKey));
		}
		//ED150720 : @ => email
		elseif((!$module || $module == 'Contacts')
		&& ($searchKey
		    && (strpos($searchKey, '@') !== FALSE)
		)){
			$query = 'SELECT CONCAT(vtiger_crmentity.label, " (", vtiger_contactdetails.contact_no, ")") AS label
				, vtiger_crmentity.crmid, vtiger_crmentity.setype, vtiger_crmentity.createdtime
				FROM vtiger_crmentity
				JOIN vtiger_contactdetails
					ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
				LEFT JOIN vtiger_contactemails
					ON vtiger_contactemails.contactid = vtiger_crmentity.crmid
				WHERE vtiger_crmentity.deleted = 0
				AND (vtiger_contactdetails.email ' .
					(strpos($searchKey, '%') !== FALSE ? ' LIKE CONCAT(\'%\', ?, \'%\')' : '= ?') .'
				   OR vtiger_contactemails.email ' .
					(strpos($searchKey, '%') !== FALSE ? ' LIKE CONCAT(\'%\', ?, \'%\')' : '= ?') .')
				';
			$params = array(trim($searchKey), trim($searchKey));
		}
		elseif((!$module || $module === 'Contacts')
		&& strpos($searchKey, ',') !== FALSE) {	/* séparation du nom et prénom */
			$query = 'SELECT CONCAT(vtiger_crmentity.label, " (", vtiger_contactdetails.contact_no, ")") AS label
					, crmid, setype, createdtime
				FROM vtiger_crmentity
				JOIN vtiger_contactdetails
					ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
				WHERE (label LIKE ?
				OR label LIKE ?)
				AND vtiger_crmentity.deleted = 0';
			$searchKeys = preg_split('/\s*,\s*/', $searchKey);
			$params = array();
			$params[] = $searchKeys[0].'% '.$searchKeys[1].'%';
			$params[] = $searchKeys[1].'% '.$searchKeys[0].'%';
		
			if(!$module)
				$module = 'Contacts';
			$query .= ' AND setype = ?';
			$params[] = $module;
		}
		else {	/* requête générale sur le champ label */
			$query = 'SELECT label, crmid, setype, createdtime
				FROM vtiger_crmentity
				WHERE label LIKE ?
				AND vtiger_crmentity.deleted = 0';
			if(strpos($searchKey, ',') !== FALSE)
				$searchKey = preg_replace('/\s*,\s*/', '%', $searchKey);
			$params = array("%$searchKey%");
		
			if($module !== false) {
				$query .= ' AND setype = ?';
				$params[] = $module;
			}
		}
		//var_dump($query, $params);
		//Remove the ordering for now to improve the speed
		//$query .= ' ORDER BY createdtime DESC';

		$result = $db->pquery($query, $params);
		/*if(!$result)
			$db->echoError();*/
		$noOfRows = $db->num_rows($result);

		$moduleModels = $matchingRecords = $leadIdsList = array();
		for($i=0; $i<$noOfRows; ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			if ($row['setype'] === 'Leads') {
				$leadIdsList[] = $row['crmid'];
			}
		}
		$convertedInfo = Leads_Module_Model::getConvertedInfo($leadIdsList);

		for($i=0, $recordsCount = 0; $i<$noOfRows && $recordsCount<100; ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			if ($row['setype'] === 'Leads' && $convertedInfo[$row['crmid']]) {
				continue;
			}
			if(Users_Privileges_Model::isPermitted($row['setype'], 'DetailView', $row['crmid'])) {
				$row['id'] = $row['crmid'];
				$moduleName = $row['setype'];
				if(!array_key_exists($moduleName, $moduleModels)) {
					$moduleModels[$moduleName] = Vtiger_Module_Model::getInstance($moduleName);
				}
				$moduleModel = $moduleModels[$moduleName];
				$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', $moduleName);
				$recordInstance = new $modelClassName();
				$matchingRecords[$moduleName][$row['id']] = $recordInstance->setData($row)->setModuleFromInstance($moduleModel);
				$recordsCount++;
			}
		}
		return $matchingRecords;
	}

	/**
	 * Function to get details for user have the permissions to do actions
	 * @return <Boolean> - true/false
	 */
	public function isEditable() {
		return Users_Privileges_Model::isPermitted($this->getModuleName(), 'EditView', $this->getId());
	}

	/**
	 * Function to get details for user have the permissions to do actions
	 * @return <Boolean> - true/false
	 */
	public function isDeletable() {
		return Users_Privileges_Model::isPermitted($this->getModuleName(), 'Delete', $this->getId());
	}

	/** ED150521
	 * Function to get details for user have the permissions to do duplicate
	 * @return <Boolean> - true/false
	 */
	public function isDuplicatable() {
		//TODO specific action 'Duplicate'
		return Users_Privileges_Model::isPermitted($this->getModuleName(), 'EditView', $this->getId());
	}

	/**
	 * Funtion to get Duplicate Record Url
	 * @return <String>
	 */
	public function getDuplicateRecordUrl() {
		$module = $this->getModule();
		return 'index.php?module='.$this->getModuleName().'&view='.$module->getEditViewName().'&record='.$this->getId().'&isDuplicate=true';

	}

	/**
	 * Function to get Display value for RelatedList
	 * @param <String> $value
	 * @return <String>
	 */
	public function getRelatedListDisplayValue($fieldName) {
		$fieldModel = $this->getModule()->getField($fieldName);
		return $fieldModel->getRelatedListDisplayValue($this->get($fieldName));
	}

	/**
	 * Function to delete corresponding image
	 * @param <type> $imageId
	 */
	public function deleteImage($imageId) {
		$db = PearDatabase::getInstance();

		$checkResult = $db->pquery('SELECT crmid FROM vtiger_seattachmentsrel WHERE attachmentsid = ?', array($imageId));
		$crmId = $db->query_result($checkResult, 0, 'crmid');

		if ($this->getId() === $crmId) {
			$db->pquery('DELETE FROM vtiger_attachments WHERE attachmentsid = ?', array($imageId));
			$db->pquery('DELETE FROM vtiger_seattachmentsrel WHERE attachmentsid = ?', array($imageId));
			return true;
		}
		return false;
	}

	/**
	 * Function to get Descrption value for this record
	 * @return <String> Descrption
	 */
	public function getDescriptionValue() {
		$description = $this->get('description');
		if(empty($description)) {
			$db = PearDatabase::getInstance();
			$result = $db->pquery("SELECT description FROM vtiger_crmentity WHERE crmid = ?", array($this->getId()));
			$description =  $db->query_result($result, 0, "description");
		}
		return $description;
	}

	/**
	 * Function to transfer related records of parent records to this record
	 * @param <Array> $recordIds
	 * @return <Boolean> true/false
	 */
	public function transferRelationInfoOfRecords($recordIds = array()) {
		if ($recordIds) {
			$moduleName = $this->getModuleName();
			$focus = CRMEntity::getInstance($moduleName);
			if (method_exists($focus, 'transferRelatedRecords')) {
				$focus->transferRelatedRecords($moduleName, $recordIds, $this->getId());
			}
		}
		return true;
	}
	
	/**
	 * ED141109
	 * getPicklistValuesDetails
	 */
	public function getPicklistValuesDetails($fieldname){
		switch($fieldname){
			case 'error'://rsnreglements
				return array(
					'0' => array( 'label' => 'non', 'icon' => 'ui-icon ui-icon-check green' ),
					'1' => array( 'label' => 'Erreur', 'icon' => 'ui-icon ui-icon-close red' ),
				);
			case 'disabled':
				return array(
					'0' => array( 'label' => 'non', 'icon' => 'ui-icon ui-icon-check green' ),
					'1' => array( 'label' => 'Désactivé', 'icon' => 'ui-icon ui-icon-close red' ),
				);
			case 'enabled':
				return array(
					'0' => array( 'label' => 'Erreur', 'icon' => 'ui-icon ui-icon-locked red' ),
					'1' => array( 'label' => 'Ok', 'icon' => 'ui-icon ui-icon-unlocked green' ),
				);
			case 'emailoptout'://contacts, rsnmediacontacts
				return array(
					'0' => array( 'label' => 'si, on peut', 'icon' => 'ui-icon ui-icon-unlocked darkgreen' ),
					'1' => array( 'label' => 'Pas d\'email', 'icon' => 'ui-icon ui-icon-locked darkred' ),
				);
			case 'rsnnpai':
				return array(
					'0' => array( 'label' => 'Ok', 'icon' => 'ui-icon ui-icon-check green' ),
					'1' => array( 'label' => 'Supposée', 'icon' => 'ui-icon ui-icon-check darkgreen' ),
					'2' => array( 'label' => 'Confirmée', 'icon' => 'ui-icon ui-icon-close orange' ),
					'3' => array( 'label' => 'Définitive', 'icon' => 'ui-icon ui-icon-close darkred' ),
					'4' => array( 'label' => 'Incomplète', 'icon' => 'ui-icon ui-icon-close darkred' ),
				);
	
			case 'discounttype':
			case 'accountdiscounttype':
				return array(
					'0' => array( 'label' => 'Normal' ),
					'dv' => array( 'label' => 'Dépôt-vente'),
					'grp' => array( 'label' => 'Groupe'),
				);
			
			case 'duplicatestatus':
				return array(
					'0' => array( 'label' => vtranslate('LBL_DUPLICATES_STATUS_0')/*'A valider'*/, 'icon' => 'ui-icon ui-icon-alert green' ),
					'2' => array( 'label' => vtranslate('LBL_DUPLICATES_STATUS_2')/*'Plus tard'*/, 'icon' => 'ui-icon ui-icon-close green'),
					'1' => array( 'label' => vtranslate('LBL_DUPLICATES_STATUS_1')/*'Ignorer'*/, 'icon' => 'ui-icon ui-icon-close darkred'),
				);
			case 'duplicatefields':
				return array();
			
			default:
				if(strpos($fieldname,'addressformat') !== false)
					return array(
						'NC1' => array( 'title' => 'Normal', 'icon' => 'icon-rsn-small-formataddress-NC1' ),
						'CN1' => array( 'title' => 'Groupe avant', 'icon' => 'icon-rsn-small-formataddress-CN1' ),
						'C1' => array( 'title' => 'Sans le nom', 'icon' => 'icon-rsn-small-formataddress-C1' ),
						'N1' => array( 'title' => 'Sans le groupe', 'icon' => 'icon-rsn-small-formataddress-N1' ),
					);
				return array();
		}
	}
	
	/** ED150828 for abstract
	* getPicklistValues called on HeaderFilter context
	* see modules\Contacts\models\Record.php
	*/
	public function getPicklistValuesDetailsForHeaderFilter($fieldname){
		return $this->getPicklistValuesDetails($fieldname);
	}
	
	
	/**
	 * ED141109
	 * getListViewPicklistValues
	 */
	public function getListViewPicklistValues($fieldname){
		return $this->getPicklistValuesDetails($fieldname);
	}

	/* ED150207
	 * Duplication des enregistrements liés sur le modèle d'un autre enregistrement
	 *
	 * @param $templateId : template id 
	 * @param $destRecord : destination record model or id
	 * @param $relatedModules : array of related module names. TRUE for all.
	 */
	public function duplicateRelatedRecords($templateId, $destRecord, $relatedModules = true){
		if(!$relatedModules) return;
		if(!is_array($relatedModules)){
			//related modules 
			$moduleName = $this->getModuleName();
			$relatedModules = Vtiger_Relation_Model::getAllRelations($moduleName);
		}
		else {
			//related modules object filtred by name
			$moduleName = $this->getModuleName();
			$relationModels = Vtiger_Relation_Model::getAllRelations($moduleName);
			$relatedModuleNames = array_combine($relatedModules, $relatedModules);
			$relatedModules = array();
			foreach($relationModels as $relationModel)
				if(array_key_exists($relationModel->getRelationModuleName(),$relatedModuleNames))
					$relatedModules[] = $relationModel;
		}
		foreach($relatedModules as $relationModel)
			$this->duplicateRelatedModuleRecords($templateId, $destRecord, $relationModel);
	}

	/* ED150207
	 * Duplication des enregistrements liés sur le modèle d'un autre enregistrement pour un module
	 * @param $templateId : template id 
	 * @param $destRecord : destination record model or id
	 * @param $relationModel : related module name. 
	 */
	public function duplicateRelatedModuleRecords($templateId, $destRecord, $relationModel){
		if(!$relationModel)
			return;
		//var_dump($relationModel);
		
		if(is_object($destRecord))
			$destRecord = $destRecord->getId();
			
		$sql = array();
		if(is_object($relationModel)){
			$relatedModuleName = $relationModel->getRelationModuleName();
			//var_dump($relationModel);
		}
		else
			$relatedModuleName = $relationModel;
		
		switch($relatedModuleName){
		case "Calendar":
			return;
		
		case "Documents":
			//vtiger_seattachmentsrel
			$sql[] = "INSERT INTO vtiger_seattachmentsrel (`crmid`, `attachmentsid`)
					SELECT $destRecord, `attachmentsid`
					FROM vtiger_seattachmentsrel src
					WHERE src.crmid = $templateId
					ON DUPLICATE KEY UPDATE vtiger_seattachmentsrel.crmid = vtiger_seattachmentsrel.crmid
			";
			$sql[] = "INSERT INTO vtiger_senotesrel (`crmid`, `notesid`)
					SELECT $destRecord, `notesid`
					FROM vtiger_senotesrel src
					WHERE src.crmid = $templateId
					ON DUPLICATE KEY UPDATE vtiger_senotesrel.crmid = vtiger_senotesrel.crmid
			";
			break;
		case "Contacts":
			switch($this->getModuleName()){
			case "Campaigns":
				//vtiger_seattachmentsrel
				$sql[] = "INSERT INTO vtiger_campaigncontrel (`campaignid`, `contactid`, `campaignrelstatusid`)
						SELECT $destRecord, `contactid`, `campaignrelstatusid`
						FROM vtiger_campaigncontrel src
						WHERE src.campaignid = $templateId
						ON DUPLICATE KEY UPDATE vtiger_campaigncontrel.campaignid = vtiger_campaigncontrel.campaignid
				";
				break;
			default:
				break;
			}
			break;
		case "Campaigns":
			switch($this->getModuleName()){
			case "Contacts":
				//vtiger_seattachmentsrel
				$sql[] = "INSERT INTO vtiger_campaigncontrel (`campaignid`, `contactid`, `campaignrelstatusid`)
						SELECT $destRecord, `campaignid`, `campaignrelstatusid`
						FROM vtiger_campaigncontrel src
						WHERE src.contactid = $templateId
						ON DUPLICATE KEY UPDATE vtiger_campaigncontrel.contactid = vtiger_campaigncontrel.contactid
				";
				break;
			default:
				break;
			}
			break;
		default:
			//vtiger_crmentityrel
			$sql[] = "INSERT INTO vtiger_crmentityrel (crmid, module, relcrmid, relmodule)
					SELECT $destRecord, module, relcrmid, relmodule
					FROM vtiger_crmentityrel src
					WHERE src.crmid = $templateId
					AND src.relmodule = '$relatedModuleName'
					ON DUPLICATE KEY UPDATE vtiger_crmentityrel.crmid = vtiger_crmentityrel.crmid
			";
			$sql[] = "INSERT INTO vtiger_crmentityrel (crmid, module, relcrmid, relmodule)
					SELECT crmid, module, $destRecord, relmodule
					FROM vtiger_crmentityrel src
					WHERE src.relcrmid = $templateId
					AND src.module = '$relatedModuleName'
					ON DUPLICATE KEY UPDATE vtiger_crmentityrel.crmid = vtiger_crmentityrel.crmid
			";
			break;
		}
		
		$db = PearDatabase::getInstance();
		//$db->setDebug(true);
		foreach($sql as $query){
			//var_dump($query);
			$db->query($query);
		}
		//$db->setDebug(false);
	}
	
	/* ED150515
	 * Returns related data to this record
	 * @param $dataNames : array or string width ',' separator
	 * 	module name or suffix of function name (getRelated<dataName>)
	 * @returns an associative array of entries
	 *
	 * Used from modules\Vtiger\actions\GetData.php, responding to vlayout\modules\Vtiger\resources\Edit.js, getRecordDetails({related_data : dataNames})
	 */
	public function getRelatedData($dataNames){
		if(is_string($dataNames))
			$dataNames = explode(',', $dataNames);
		$data = array();
		foreach($dataNames as $dataName){
			//Method 1 : specific getRelatedXXXXX function exists
			$functionName = "getRelated$dataName";
			if(method_exists($this, $functionName)){
				$data[$dataName] = $this->$functionName();
				continue;
			}
			//Method 2 : $dataName is a ModuleName
			$pagingModel = new Vtiger_Paging_Model();
			$pagingModel->set('page', 1);
			
			$relatedModuleName = $dataName;
			$relationListView = Vtiger_RelationListView_Model::getInstance($this, $relatedModuleName, '');
			$data[$dataName] = $relationListView->getEntries($pagingModel);
		}
		// converts record models as raw data
		foreach($data as $dataName => $entries){
			$rawData = false;
			foreach($entries as $id => $entry){
				if(!(is_a($entry, Vtiger_Record_Model )))
					break;
				if(!$rawData)
					$rawData = array();
				$rawData[$id] = $entry->getData();
			}
			if($rawData)
				$data[$dataName] = $rawData;
		}
		return $data;
	}
	
	/** ED150609
	 * trigger events
	 */
	public function triggerEvent($eventName = 'vtiger.entity.aftersave, vtiger.entity.aftersave.final'){
		$focus = $this->getCRMEntity();
		$focus->triggerEvent($eventName);
	}
	
	/** ED150609
	 * return CRMEntity intitialized with this record data
	 */
	public function getCRMEntity(){
		//Get CRMEntity
		$moduleName = $this->getModuleName();
		$focus = CRMEntity::getInstance($moduleName);
		//Set fields values
		$fields = $focus->column_fields;
		foreach($fields as $fieldName => $fieldValue) {
			$fieldValue = $this->get($fieldName);
			//echo '<pre>'; var_dump($fieldName, $fieldValue);echo '</pre>'; 
			if(is_array($fieldValue)){
				$focus->column_fields[$fieldName] = $fieldValue;
			}else if($fieldValue !== null) {
				$focus->column_fields[$fieldName] = decode_html($fieldValue);
			}
		}
		$focus->mode = $this->get('mode');
		$focus->id = $this->getId();
		return $focus;
	}

	//AV150813
	public function isUnsavedRecord() {
		return (!$this->getId());
	}
	
	//ED151109
	public function getHtmlLabel(){
		return $this->getName();
	}
}
