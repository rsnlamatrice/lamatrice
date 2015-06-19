<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ************************************************************************************/

class Contacts_Module_Model extends Vtiger_Module_Model {
	/**
	 * Function to get the Quick Links for the module
	 * @param <Array> $linkParams
	 * @return <Array> List of Vtiger_Link_Model instances
	 */
	public function getSideBarLinks($linkParams) {
		$parentQuickLinks = parent::getSideBarLinks($linkParams);

		$quickLink = array(
				'linktype' => 'SIDEBARLINK',
				'linklabel' => 'LBL_DASHBOARD',
				'linkurl' => $this->getDashBoardUrl(),
				'linkicon' => '',
		);

		//Check profile permissions for Dashboards
		$moduleModel = Vtiger_Module_Model::getInstance('Dashboard');
		$userPrivilegesModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$permission = $userPrivilegesModel->hasModulePermission($moduleModel->getId());
		if($permission) {
			$parentQuickLinks['SIDEBARLINK'][] = Vtiger_Link_Model::getInstanceFromValues($quickLink);
		}

		return $parentQuickLinks;
	}

	/**
	 * Function returns the Calendar Events for the module
	 * @param <Vtiger_Paging_Model> $pagingModel
	 * @return <Array>
	 */
	public function getCalendarActivities($mode, $pagingModel, $user, $recordId = false) {
		$currentUser = Users_Record_Model::getCurrentUserModel();
		$db = PearDatabase::getInstance();

		if (!$user) {
			$user = $currentUser->getId();
		}

		$nowInUserFormat = Vtiger_Datetime_UIType::getDisplayDateValue(date('Y-m-d H:i:s'));
		$nowInDBFormat = Vtiger_Datetime_UIType::getDBDateTimeValue($nowInUserFormat);
		list($currentDate, $currentTime) = explode(' ', $nowInDBFormat);

		$query = "SELECT vtiger_crmentity.crmid, crmentity2.crmid AS contact_id, vtiger_crmentity.smownerid, vtiger_crmentity.setype, vtiger_activity.* FROM vtiger_activity
					INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_activity.activityid
					INNER JOIN vtiger_cntactivityrel ON vtiger_cntactivityrel.activityid = vtiger_activity.activityid
					INNER JOIN vtiger_crmentity AS crmentity2 ON vtiger_cntactivityrel.contactid = crmentity2.crmid AND crmentity2.deleted = 0 AND crmentity2.setype = ?
					LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid";

		$query .= Users_Privileges_Model::getNonAdminAccessControlQuery('Calendar');

		$query .= " WHERE vtiger_crmentity.deleted=0
					AND (vtiger_activity.activitytype NOT IN ('Emails'))
					AND (vtiger_activity.status is NULL OR vtiger_activity.status NOT IN ('Completed', 'Deferred'))
					AND (vtiger_activity.eventstatus is NULL OR vtiger_activity.eventstatus NOT IN ('Held'))";

		if ($recordId) {
			$query .= " AND vtiger_cntactivityrel.contactid = ?";
		} elseif ($mode === 'upcoming') {
			$query .= " AND due_date >= '$currentDate'";
		} elseif ($mode === 'overdue') {
			$query .= " AND due_date < '$currentDate'";
		}

		$params = array($this->getName());
		if ($recordId) {
			array_push($params, $recordId);
		}

		if($user != 'all' && $user != '') {
			if($user === $currentUser->id) {
				$query .= " AND vtiger_crmentity.smownerid = ?";
				array_push($params, $user);
			}
		}

		$query .= " ORDER BY date_start, time_start LIMIT ". $pagingModel->getStartIndex() .", ". ($pagingModel->getPageLimit()+1);

		$result = $db->pquery($query, $params);
		$numOfRows = $db->num_rows($result);

		$activities = array();
		for($i=0; $i<$numOfRows; $i++) {
			$row = $db->query_result_rowdata($result, $i);
			$model = Vtiger_Record_Model::getCleanInstance('Calendar');
			$model->setData($row);
			$model->setId($row['crmid']);
			$activities[] = $model;
		}

		$pagingModel->calculatePageRange($activities);
		if($numOfRows > $pagingModel->getPageLimit()){
			array_pop($activities);
			$pagingModel->set('nextPageExists', true);
		} else {
			$pagingModel->set('nextPageExists', false);
		}

		return $activities;
	}

	/**
	 * Function returns query for module record's search
	 * @param <String> $searchValue - part of record name (label column of crmentity table)
	 * @param <Integer> $parentId - parent record id
	 * @param <String> $parentModule - parent module name
	 * @return <String> - query
	 */
	function getSearchRecordsQuery($searchValue, $parentId=false, $parentModule=false) {
		if($parentId && $parentModule == 'Accounts') {
			$query = "SELECT * FROM vtiger_crmentity
						INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
						WHERE deleted = 0 AND vtiger_contactdetails.accountid = $parentId AND label like '%$searchValue%'";
			return $query;
		} else if($parentId && $parentModule == 'Potentials') {
			$query = "SELECT * FROM vtiger_crmentity
						INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
						INNER JOIN vtiger_contpotentialrel on vtiger_contpotentialrel.contactid = vtiger_contactdetails.contactid
						WHERE deleted = 0 AND vtiger_contpotentialrel.potentialid = $parentId AND label like '%$searchValue%'";
			return $query;
		} else if ($parentId && $parentModule == 'HelpDesk') {
			$query = "SELECT * FROM vtiger_crmentity
				INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
				INNER JOIN vtiger_troubletickets ON vtiger_troubletickets.contact_id = vtiger_contactdetails.contactid
				WHERE deleted=0 AND vtiger_troubletickets.ticketid  = $parentId  AND label like '%$searchValue%'";

		return $query;
        } else if($parentId && $parentModule == 'Campaigns') {
            $query = "SELECT * FROM vtiger_crmentity
                        INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
                        INNER JOIN vtiger_campaigncontrel ON vtiger_campaigncontrel.contactid = vtiger_contactdetails.contactid
                        WHERE deleted=0 AND vtiger_campaigncontrel.campaignid = $parentId AND label like '%$searchValue%'";

            return $query;
        } else if($parentId && $parentModule == 'Critere4D') {
            $query = "SELECT * FROM vtiger_crmentity
                        INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
                        INNER JOIN vtiger_critere4dcontrel ON vtiger_critere4dcontrel.contactid = vtiger_contactdetails.contactid
                        WHERE deleted=0 AND vtiger_critere4dcontrel.critere4did = $parentId AND label like '%$searchValue%'";

            return $query;
        } else if($parentId && $parentModule == 'Vendors') {
            $query = "SELECT vtiger_crmentity.* FROM vtiger_crmentity
                        INNER JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
                        INNER JOIN vtiger_vendorcontactrel ON vtiger_vendorcontactrel.contactid = vtiger_contactdetails.contactid
                        WHERE deleted=0 AND vtiger_vendorcontactrel.vendorid = $parentId AND label like '%$searchValue%'";

            return $query;
        }

		return parent::getSearchRecordsQuery($parentId, $parentModule);
	}


	/**
	 * Function to get relation query for particular module with function name
	 * @param <record> $recordId
	 * @param <String> $functionName
	 * @param Vtiger_Module_Model $relatedModule
	 * @return <String>
	 */
	public function getRelationQuery($recordId, $functionName, $relatedModule) {
		
		switch ($functionName){
		case 'get_activities' :
			$userNameSql = getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');

			$query = "SELECT CASE WHEN (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END AS user_name,
						vtiger_cntactivityrel.contactid, vtiger_seactivityrel.crmid AS parent_id,
						vtiger_crmentity.*, vtiger_activity.activitytype, vtiger_activity.subject, vtiger_activity.date_start, vtiger_activity.time_start,
						vtiger_activity.recurringtype, vtiger_activity.due_date, vtiger_activity.time_end,
						CASE WHEN (vtiger_activity.activitytype = 'Task') THEN (vtiger_activity.status) ELSE (vtiger_activity.eventstatus) END AS status
						FROM vtiger_activity
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_activity.activityid
						INNER JOIN vtiger_cntactivityrel ON vtiger_cntactivityrel.activityid = vtiger_activity.activityid
						LEFT JOIN vtiger_seactivityrel ON vtiger_seactivityrel.activityid = vtiger_activity.activityid
						LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
						LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
							WHERE vtiger_cntactivityrel.contactid = ".$recordId." AND vtiger_crmentity.deleted = 0
								AND vtiger_activity.activitytype <> 'Emails'";

			$relatedModuleName = $relatedModule->getName();
			$query .= $this->getSpecificRelationQuery($relatedModuleName);
			$nonAdminQuery = $this->getNonAdminAccessControlQueryForRelation($relatedModuleName);
			if ($nonAdminQuery) {
				$query = appendFromClauseToQuery($query, $nonAdminQuery);
			}
			
			break;
			
		case 'get_rsndons':
			$servicecategory = 'Dons';
			return $this->getRelationQuery_RsnServices($recordId, $functionName, $relatedModule, $servicecategory);
		case 'get_rsnadhesions':
			$servicecategory = 'Adhésion';
			return $this->getRelationQuery_RsnServices($recordId, $functionName, $relatedModule, $servicecategory);
		case 'get_rsnabonnements':
			$servicecategory = 'Abonnement';
			return $this->getRelationQuery_RsnServices($recordId, $functionName, $relatedModule, $servicecategory);
		
		case 'get_rsnprelevements':
			
			$userNameSql = getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
			$query = 'SELECT CASE WHEN (vtiger_users.user_name not like "") THEN '.$userNameSql.' ELSE vtiger_groups.groupname END AS user_name
				, vtiger_crmentity.*, p.*
				/*, vtiger_crmentity.smownerid AS assigned_user_id*/
				FROM `vtiger_rsnprelevements` p
				INNER JOIN `vtiger_crmentity`
					ON vtiger_crmentity.crmid = p.rsnprelevementsid
				JOIN vtiger_contactdetails cd
					ON cd.accountid = p.accountid
				LEFT JOIN vtiger_users
					ON vtiger_users.id = vtiger_crmentity.smownerid
				LEFT JOIN vtiger_groups
					ON vtiger_groups.groupid = vtiger_crmentity.smownerid
				WHERE vtiger_crmentity.deleted = 0
				AND cd.contactid = '.$recordId;
				
			$relatedModuleName = $relatedModule->getName();
			$query .= $this->getSpecificRelationQuery($relatedModuleName);
			$nonAdminQuery = $this->getNonAdminAccessControlQueryForRelation($relatedModuleName);
			if ($nonAdminQuery) {
				$query = appendFromClauseToQuery($query, $nonAdminQuery);
			}
			//echo('<pre>'.$query . '</pre>');
			break;
		
				
		case 'get_invoices':
			
			$userNameSql = getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
			$query = 'SELECT vtiger_crmentity.crmid,vtiger_invoice.subject, vtiger_invoice.invoice_no, vtiger_invoicecf.typedossier
			, vtiger_invoice.invoicestatus, vtiger_invoice.invoicedate, vtiger_invoicecf.campaign_no, vtiger_invoicecf.notesid, vtiger_invoice.total
			FROM vtiger_invoice
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_invoice.invoiceid
			LEFT OUTER JOIN vtiger_invoicecf ON vtiger_invoicecf.invoiceid = vtiger_crmentity.crmid
			LEFT OUTER JOIN vtiger_account ON vtiger_account.accountid = vtiger_invoice.accountid
			LEFT OUTER JOIN vtiger_contactdetails ON vtiger_account.accountid = vtiger_contactdetails.accountid
			LEFT OUTER JOIN vtiger_salesorder ON vtiger_salesorder.salesorderid = vtiger_invoice.salesorderid
			LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
			LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id
			WHERE vtiger_crmentity.deleted = 0
			AND (vtiger_contactdetails.contactid = '.$recordId.')'
			;
			$relatedModuleName = $relatedModule->getName();
			$query .= $this->getSpecificRelationQuery($relatedModuleName);
			$nonAdminQuery = $this->getNonAdminAccessControlQueryForRelation($relatedModuleName);
			if ($nonAdminQuery) {
				$query = appendFromClauseToQuery($query, $nonAdminQuery);
			}
			//echo('<pre>'.$query . '</pre>');
			break;
		case 'get_attachments':
			$query = parent::getRelationQuery($recordId, $functionName, $relatedModule);
			//ED141223 : ajout des champs de la relation
			$query = preg_replace('/(^\s*|\sUNION\s+)SELECT\s/i', '$1SELECT vtiger_senotesrel.dateapplication, vtiger_senotesrel.data, ', $query);
			//echo('<pre>APRES '.$query . '</pre>');
			//echo_callstack();
			break;
		case 'get_campaigns':
			$query = parent::getRelationQuery($recordId, $functionName, $relatedModule);
			//ED141223 : ajout des champs de la relation
			$query = preg_replace('/(^\s*|\sUNION\s+)SELECT\s/i', '$1SELECT vtiger_campaigncontrel.dateapplication, vtiger_campaigncontrel.data, ', $query);
			//echo('<pre>APRES '.$query . '</pre>');
			//echo_callstack();
			break;
		default:
			//echo('<pre>'.__FILE__.' '.$functionName . '</pre>');
			$query = parent::getRelationQuery($recordId, $functionName, $relatedModule);
			//echo('<pre>'.$query . '</pre>');
			
			break;			
		}

		return $query;
	}

	/* 
	 * Cas particuliers de la fonction ci-dessus pour les modules affichant une seule catégorie de service
	 * ED150203
	 * AV150619
	 */
	public function getRelationQuery_RsnServices($recordId, $functionName, $relatedModule, $servicecategory) {
		$focus = CRMEntity::getInstance($this->getName());
		$focus->id = $recordId;

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');

		$query = 'SELECT CASE WHEN (vtiger_users.user_name not like "") THEN '.$userNameSql.' ELSE vtiger_groups.groupname END AS user_name
			, vtiger_crmentity.*, f.invoicedate, f.accountid as compte
			, lg.`listprice` * ( 1 + CASE
					WHEN NOT tax1 IS NULL THEN tax1 / 100
					WHEN NOT tax2 IS NULL THEN tax2 / 100
					WHEN NOT tax3 IS NULL THEN tax3 / 100
					WHEN NOT tax4 IS NULL THEN tax4 / 100
					ELSE 0 END
				) as montant
			, p.servicename as origine, "" as origine_detail
			, p.serviceid
			, p.servicecategory
			, fcf.campaign_no
			, fcf.notesid
			, f.invoiceid as vtiger_rsndonsid
			, vtiger_crmentity.smownerid AS assigned_user_id
			FROM `vtiger_inventoryproductrel` lg
			JOIN `vtiger_service` p
				ON lg.productid = p.serviceid
				AND p.servicecategory = \''. $servicecategory .'\'
			JOIN `vtiger_invoice` f
				ON lg.id = f.invoiceid
			JOIN `vtiger_crmentity`
				ON vtiger_crmentity.crmid = f.invoiceid
			JOIN vtiger_contactdetails cd
				ON cd.accountid = f.accountid
			LEFT JOIN `vtiger_invoicecf` fcf
				ON fcf.invoiceid = f.invoiceid
			LEFT JOIN vtiger_users
				ON vtiger_users.id = vtiger_crmentity.smownerid
			LEFT JOIN vtiger_groups
				ON vtiger_groups.groupid = vtiger_crmentity.smownerid
			WHERE vtiger_crmentity.deleted = 0
			AND cd.contactid = '.$recordId;
			
		$relatedModuleName = $relatedModule->getName();
		$query .= $this->getSpecificRelationQuery($relatedModuleName);
		$nonAdminQuery = $this->getNonAdminAccessControlQueryForRelation($relatedModuleName);
		if ($nonAdminQuery) {
			$query = appendFromClauseToQuery($query, $nonAdminQuery);
		}
		//echo('<pre>'.$query . '</pre>');
		return $query;
	}
	
	/**
	 * Function to get list view query for popup window
	 * @param <String> $sourceModule Parent module
	 * @param <String> $field parent fieldname
	 * @param <Integer> $record parent id
	 * @param <String> $listQuery
	 * @return <String> Listview Query
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, $listQuery) {
		if (in_array($sourceModule, array('Campaigns', 'Critere4D', 'Potentials', 'Vendors', 'Products', 'Services', 'Emails'))
				|| ($sourceModule === 'Contacts' && $field === 'contact_id' && $record)) {
			switch ($sourceModule) {
				case 'Campaigns'	: $tableName = 'vtiger_campaigncontrel';	$fieldName = 'contactid';	$relatedFieldName ='campaignid';	break;
				case 'Critere4D'	: $tableName = 'vtiger_critere4dcontrel';	$fieldName = 'contactid';	$relatedFieldName ='critere4did';	break;
				case 'Contacts'		: $tableName = 'vtiger_contactscontrel';	$fieldName = 'contactid';	$relatedFieldName ='relcontid';		break;
				case 'Potentials'	: $tableName = 'vtiger_contpotentialrel';	$fieldName = 'contactid';	$relatedFieldName ='potentialid';	break;
				case 'Vendors'		: $tableName = 'vtiger_vendorcontactrel';	$fieldName = 'contactid';	$relatedFieldName ='vendorid';		break;
				case 'Products'		: $tableName = 'vtiger_seproductsrel';		$fieldName = 'crmid';		$relatedFieldName ='productid';		break;
			}

			if ($sourceModule === 'Services') {
				$condition = " vtiger_contactdetails.contactid NOT IN (SELECT relcrmid FROM vtiger_crmentityrel WHERE crmid = '$record' UNION SELECT crmid FROM vtiger_crmentityrel WHERE relcrmid = '$record') ";
			} elseif ($sourceModule === 'Emails') {
				$condition = ' vtiger_contactdetails.emailoptout = 0';
			} elseif ($sourceModule === 'Contacts' && $field === 'contact_id') {
				$condition = " vtiger_contactdetails.contactid != '$record'";
			} elseif ($sourceModule === 'Critere4D') { /* ED140907 les contacts peuvent avoir plusieurs fois le même critère */
				$condition = false; 
			} elseif ($sourceModule === 'Campaigns') { /* ED150331 les contacts peuvent avoir plusieurs fois la mêmes campagnes */
				$condition = false; 
			} else {
				$condition = " vtiger_contactdetails.contactid NOT IN (SELECT $fieldName FROM $tableName WHERE $relatedFieldName = '$record')";
			}
			
			if(!$condition)
				return $listQuery;

			$position = stripos($listQuery, 'where');
			if($position) {
				$split = spliti('where', $listQuery);
				$overRideQuery = $split[0] . ' WHERE ' . $split[1] . ' AND ' . $condition;
			} else {
				$overRideQuery = $listQuery. ' WHERE ' . $condition;
			}
			return $overRideQuery;
		}
	}
	
	/**
	 * Function to save a given record model of the current module
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function saveRecord(Vtiger_Record_Model $recordModel) {
		// ED141016 : majuscules obligatoires
		$recordModel->set('lastname', mb_strtoupper(remove_accent(decode_html($recordModel->get('lastname')))));
		
		$return = parent::saveRecord($recordModel);
		
		// ED150205 : synchronisation de l'adresse vers le compte et les autres contacts en compte commun
		$recordModel->synchronizeAddressToOthers();
		
		// ED15015 : un seul contact peut être référent du compte
		$recordModel->ensureAccountHasOnlyOneMainContact();
		
		return $return;
	}
	
	
	/* ED150323
	 * Provides the ability for Document / Related contacts / Campaigns to show dateapplication data
	 * see /modules/Vtiger/models/RelationListView.php, function getEntries($pagingModel)
	*/
	public function getConfigureRelatedListFields(){
		$ListFields = parent::getConfigureRelatedListFields();
		// Documents
		$ListFields['dateapplication'] = 'dateapplication';
		$ListFields['data'] = 'data';
		return $ListFields;
	}
	
	/**
	 * Function to get list of field for summary view
	 * @return <Array> list of field models <Vtiger_Field_Model>
	 *
	 * ED150515 : overrided to set 'reference' field after 'account_id'
	 */
	public function getSummaryViewFieldsList() {
		if (!$this->summaryFields) {
			$summaryFields = array();
			$fields = parent::getSummaryViewFieldsList();
			$fieldReference = $fields['reference'];
			if($fieldReference){
				unset($fields['reference']);
				foreach ($fields as $fieldName => $fieldModel) {
					$summaryFields[$fieldName] = $fieldModel;
					if($fieldName == 'account_id')
						$summaryFields[$fieldReference->getName()] = $fieldReference;
				}
			}
			$this->summaryFields = $summaryFields;
		}
		return $this->summaryFields;
	}
}