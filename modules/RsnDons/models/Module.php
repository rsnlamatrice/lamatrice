<?php
/*+***********************************************************************************
 * 
 *************************************************************************************/
/***
 * Hérité par RsnAbonnements et RsnAdhesions
 */
//vimport('~~/vtlib/Vtiger/Module.php');

/**
 * Vtiger Module Model Class
 */
class RsnDons_Module_Model extends Vtiger_Module_Model {
	
	
	var $serviceType = 'Dons';
	
	/**
	 * Function to check whether the entity has an quick create menu
	 * @return <Boolean> true/false
	 * ED141024
	 */
	public function isQuickCreateMenuVisible() {
		return false ;
	}

	/**
	 * Function to save a given record model of the current module
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function saveRecord(Vtiger_Record_Model $recordModel) {
		$moduleName = $this->get('name');
		$focus = CRMEntity::getInstance($moduleName);
		$fields = $focus->column_fields;
		foreach($fields as $fieldName => $fieldValue) {
			$fieldValue = $recordModel->get($fieldName);
			if(is_array($fieldValue)){
				$focus->column_fields[$fieldName] = $fieldValue;
			}else if($fieldValue !== null) {
				$focus->column_fields[$fieldName] = decode_html($fieldValue);
			}
		}
		$focus->mode = $recordModel->get('mode');
		$focus->id = $recordModel->getId();
		$focus->save($moduleName);
		return $recordModel->setId($focus->id);
	}

	/**
	 * Function to delete a given record model of the current module
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function deleteRecord(Vtiger_Record_Model $recordModel) {
		$moduleName = $this->get('name');
		$focus = CRMEntity::getInstance($moduleName);
		$focus->trash($moduleName, $recordModel->getId());
		if(method_exists($focus, 'transferRelatedRecords')) {
			if($recordModel->get('transferRecordIDs'))
				$focus->transferRelatedRecords($moduleName, $recordModel->get('transferRecordIDs'), $recordModel->getId());
		}
	}


	/**
	 * Function to get the list of recently visisted records
	 * @param <Number> $limit
	 * @return <Array> - List of Vtiger_Record_Model or Module Specific Record Model instances
	 */
	public function getRecentRecords($limit=10) {
		$db = PearDatabase::getInstance();

		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		$deletedCondition = $this->getDeletedRecordCondition();
		$nonAdminQuery .= Users_Privileges_Model::getNonAdminAccessControlQuery($this->getName());
		$query = 'SELECT * FROM vtiger_crmentity '.$nonAdminQuery.' WHERE setype=? AND '.$deletedCondition.' AND modifiedby = ? ORDER BY modifiedtime DESC LIMIT ?';
		$params = array($this->getName(), $currentUserModel->id, $limit);
		$result = $db->pquery($query, $params);
		$noOfRows = $db->num_rows($result);

		$recentRecords = array();
		for($i=0; $i<$noOfRows; ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			$row['id'] = $row['crmid'];
			$recentRecords[$row['id']] = $this->getRecordFromArray($row);
		}
		return $recentRecords;
	}

	/**
	 * Function returns export query - deprecated
	 * @param <String> $where
	 * @return <String> export query
	 */
	public function getExportQuery($where) {
		$focus = CRMEntity::getInstance($this->getName());
		$query = $focus->create_export_query($where);
		return $query;
	}


	/**
	 * Function returns query for module record's search
	 * @param <String> $searchValue - part of record name (label column of crmentity table)
	 * @param <Integer> $parentId - parent record id
	 * @param <String> $parentModule - parent module name
	 * @return <String> - query
	 */
	public function getSearchRecordsQuery($searchValue, $parentId=false, $parentModule=false) {
		return "SELECT * FROM vtiger_crmentity WHERE label LIKE '%$searchValue%' AND vtiger_crmentity.deleted = 0";
	}

	/**
	 * Function searches the records in the module, if parentId & parentModule
	 * is given then searches only those records related to them.
	 * @param <String> $searchValue - Search value
	 * @param <Integer> $parentId - parent recordId
	 * @param <String> $parentModule - parent module name
	 * @return <Array of Vtiger_Record_Model>
	 */
	public function searchRecord($searchValue, $parentId=false, $parentModule=false, $relatedModule=false) {
		if(!empty($searchValue) && empty($parentId) && empty($parentModule)) {
			$matchingRecords = Vtiger_Record_Model::getSearchResult($searchValue, $this->getName());
		} else if($parentId && $parentModule) {
			$db = PearDatabase::getInstance();
			$result = $db->pquery($this->getSearchRecordsQuery($searchValue, $parentId, $parentModule), array());
			$noOfRows = $db->num_rows($result);

			$moduleModels = array();
			$matchingRecords = array();
			for($i=0; $i<$noOfRows; ++$i) {
				$row = $db->query_result_rowdata($result, $i);
				if(Users_Privileges_Model::isPermitted($row['setype'], 'DetailView', $row['crmid'])){
					$row['id'] = $row['crmid'];
					$moduleName = $row['setype'];
					if(!array_key_exists($moduleName, $moduleModels)) {
						$moduleModels[$moduleName] = Vtiger_Module_Model::getInstance($moduleName);
					}
					$moduleModel = $moduleModels[$moduleName];
					$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', $moduleName);
					$recordInstance = new $modelClassName();
					$matchingRecords[$moduleName][$row['id']] = $recordInstance->setData($row)->setModuleFromInstance($moduleModel);
				}
			}
		}

		return $matchingRecords;
	}
	/** 
	* Function to get orderby sql from orderby field 
	*/ 
	public function getOrderBySql($orderBy){ 
		 $orderByField = $this->getFieldByColumn($orderBy); 
		 return $orderByField->get('table') . '.' . $orderBy; 
	}
	
	/** 
	* Function to get Services of category $this->serviceType ('Dons', 'Adhésion', 'Abonnement', ...)
	*/ 
	public function getServicesList(){
		include_once('modules/Services/Services.php');
		$servicesEntity = new Services();
		$query = $servicesEntity->getListQuery('Services'," AND servicecategory = '" . $this->serviceType . "'
						       AND discontinued = 1", TRUE);
		$query .= " ORDER BY sortindex, label";
		//echo "<pre>$query</pre>";
		$params = array();
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, $params);
		$noOfRows = $db->num_rows($result);

		$items = array();
		for($i=0; $i<$noOfRows; ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			$row['id'] = $row['crmid'];
			if($row['taxpercentage'])
				$row['unit_price_ttc'] = $row['unit_price'] * (1 + $row['taxpercentage'] / 100);
			else
				$row['unit_price_ttc'] = $row['unit_price'];
			$items[$row['id']] = $row;
		}
		
		return $items; 
	} 
	
	/** 
	* Function to get Campaigns
	*/ 
	public function getCampaignsList(){
		//require_once('Modules/Campaigns/Campaigns.php');
		$entity = new CRMEntity();
		if(isset($entity->getListQuery))
			$query = $entity->getListQuery('Campaigns');
		else
			$query = getListQuery('Campaigns');
		$query .= " ORDER BY createdtime DESC";
		$params = array();
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, $params);
		$noOfRows = $db->num_rows($result);

		$items = array();
		for($i=0; $i<$noOfRows; ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			$row['id'] = $row['crmid'];
			$date = new DateTimeField($row['createdtime']);
			$row['label'] .= ' (' . $date->getDisplayDate() . ')';
			$items[$row['id']] = $row;
		}
		
		return $items; 
	} 
	
	/** 
	* Function to get Coupons
	*/ 
	public function getCouponsList(){
		//include_once('Modules/Documents/Documents.php');
		$entity = new CRMEntity();
		if(isset($entity->getListQuery))
			$query = $entity->getListQuery('Documents');
		else {
			$FOLDER_COUPON_ID = 9; //TODO CONSTANTE FOLDER_COUPON_ID
			$query = getListQuery('Documents', ' AND vtiger_notes.folderid=' . $FOLDER_COUPON_ID);
		}
		//Ajoute la jointure avec les campagnes rattachées aux coupons
		$query = explode(' WHERE ', $query);
		$query[0] .= '
			LEFT JOIN ( SELECT vtiger_senotesrel.notesid, vtiger_campaign.campaignid
				FROM vtiger_senotesrel
				INNER JOIN vtiger_campaign
					ON vtiger_senotesrel.crmid=vtiger_campaign.campaignid
			) related_campaigns
			ON related_campaigns.notesid=vtiger_crmentity.crmid
		';
		$query = implode(' WHERE ', $query);
		$query = explode(' FROM ', $query);
		$query[0] .= '
			, related_campaigns.campaignid
		';
		$query = implode(' FROM ', $query);
		
		$query .= " ORDER BY createdtime DESC";
		//die($query);
		$params = array();
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, $params);
		$noOfRows = $db->num_rows($result);

		$items = array();
		for($i=0; $i<$noOfRows; ++$i) {
			$row = $db->query_result_rowdata($result, $i);
			$row['id'] = $row['crmid'];
			$row['label'] = $row['title'];
			$items[$row['id']] = $row;
		}
		
		return $items; 
	} 

}
