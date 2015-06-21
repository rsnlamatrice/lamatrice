<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ************************************************************************************/

class Accounts_Module_Model extends Vtiger_Module_Model {
	/**
	 * Function to check whether the module is an entity type module or not
	 * @return <Boolean> true/false
	 */
	public function isQuickCreateSupported($menubar_quickCreate = false) {
		return $menubar_quickCreate ? false : true ;
	}
	
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
	 * Function to get list view query for popup window
	 * @param <String> $sourceModule Parent module
	 * @param <String> $field parent fieldname
	 * @param <Integer> $record parent id
	 * @param <String> $listQuery
	 * @return <String> Listview Query
	 */
	public function getQueryByModuleField($sourceModule, $field, $record, $listQuery) {
		if (($sourceModule == 'Accounts' && $field == 'account_id' && $record)
				|| in_array($sourceModule, array('Campaigns', 'Products', 'Services', 'Emails'))) {

			if ($sourceModule === 'Campaigns') {
				$condition = " vtiger_account.accountid NOT IN (SELECT accountid FROM vtiger_campaignaccountrel WHERE campaignid = '$record')";
			} elseif ($sourceModule === 'Products') {
				$condition = " vtiger_account.accountid NOT IN (SELECT crmid FROM vtiger_seproductsrel WHERE productid = '$record')";
			} elseif ($sourceModule === 'Services') {
				$condition = " vtiger_account.accountid NOT IN (SELECT relcrmid FROM vtiger_crmentityrel WHERE crmid = '$record' UNION SELECT crmid FROM vtiger_crmentityrel WHERE relcrmid = '$record') ";
			} elseif ($sourceModule === 'Emails') {
				$condition = ' vtiger_account.emailoptout = 0';
			} else {
				$condition = " vtiger_account.accountid != '$record'";
			}

			$split = preg_split('/\sWHERE\s/i', $listQuery);
			if($split) {
				$overRideQuery = $split[0] . ' WHERE ' . $split[1] . ' AND ' . $condition;
			} else {
				$overRideQuery = $listQuery. ' WHERE ' . $condition;
			}
			return $overRideQuery;
		}
	}

	/**
	 * Function to get relation query for particular module with function name
	 * @param <record> $recordId
	 * @param <String> $functionName
	 * @param Vtiger_Module_Model $relatedModule
	 * @return <String>
	 */
	public function getRelationQuery($recordId, $functionName, $relatedModule) {
		switch ($functionName) {
		case 'get_activities':
			$focus = CRMEntity::getInstance($this->getName());
			$focus->id = $recordId;
			$entityIds = $focus->getRelatedContactsIds();
			$entityIds = implode(',', $entityIds);

			$userNameSql = getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');

			$query = "SELECT CASE WHEN (vtiger_users.user_name not like '') THEN $userNameSql ELSE vtiger_groups.groupname END AS user_name,
						vtiger_crmentity.*, vtiger_activity.activitytype, vtiger_activity.subject, vtiger_activity.date_start, vtiger_activity.time_start,
						vtiger_activity.recurringtype, vtiger_activity.due_date, vtiger_activity.time_end, vtiger_seactivityrel.crmid AS parent_id,
						CASE WHEN (vtiger_activity.activitytype = 'Task') THEN (vtiger_activity.status) ELSE (vtiger_activity.eventstatus) END AS status
						FROM vtiger_activity
						INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_activity.activityid
						LEFT JOIN vtiger_seactivityrel ON vtiger_seactivityrel.activityid = vtiger_activity.activityid
						LEFT JOIN vtiger_cntactivityrel ON vtiger_cntactivityrel.activityid = vtiger_activity.activityid
						LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
						LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
							WHERE vtiger_crmentity.deleted = 0 AND vtiger_activity.activitytype <> 'Emails'
								AND (vtiger_seactivityrel.crmid = ".$recordId;
			if($entityIds) {
				$query .= " OR vtiger_cntactivityrel.contactid IN (".$entityIds."))";
			} else {
				$query .= ")";
			}

			$relatedModuleName = $relatedModule->getName();
			$query .= $this->getSpecificRelationQuery($relatedModuleName);
			$nonAdminQuery = $this->getNonAdminAccessControlQueryForRelation($relatedModuleName);
			if ($nonAdminQuery) {
				$query = appendFromClauseToQuery($query, $nonAdminQuery);
			}

			// There could be more than one contact for an activity.
			$query .= ' GROUP BY vtiger_activity.activityid';
			
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
			
		case 'get_rsnprelevements' :
			
			$userNameSql = getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');

			$query = 'SELECT CASE WHEN (vtiger_users.user_name not like "") THEN '.$userNameSql.' ELSE vtiger_groups.groupname END AS user_name
				, vtiger_crmentity.*, p.*
				/*, vtiger_crmentity.smownerid AS assigned_user_id*/
				FROM `vtiger_rsnprelevements` p
				INNER JOIN `vtiger_crmentity`
					ON vtiger_crmentity.crmid = p.rsnprelevementsid
				INNER JOIN `vtiger_account` a
					ON a.accountid = p.accountid
				LEFT JOIN vtiger_users
					ON vtiger_users.id = vtiger_crmentity.smownerid
				LEFT JOIN vtiger_groups
					ON vtiger_groups.groupid = vtiger_crmentity.smownerid
				WHERE vtiger_crmentity.deleted = 0
				AND a.accountid = '.$recordId;
			$relatedModuleName = $relatedModule->getName();
			$query .= $this->getSpecificRelationQuery($relatedModuleName);
			$nonAdminQuery = $this->getNonAdminAccessControlQueryForRelation($relatedModuleName);
			if ($nonAdminQuery) {
				$query = appendFromClauseToQuery($query, $nonAdminQuery);
			}
			
			break;
		
		case 'get_invoices':
			
			$userNameSql = getSqlForNameInDisplayFormat(array('first_name' => 'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');

			/*$query = "SELECT CASE WHEN (vtiger_users.user_name not like "") THEN CONCAT(vtiger_users.first_name,' ',vtiger_users.last_name) ELSE vtiger_groups.groupname END AS user_name , vtiger_crmentity.*
			 , p.*
			  FROM `vtiger_rsnprelevements` p INNER JOIN `vtiger_crmentity` ON vtiger_crmentity.crmid = p.rsnprelevementsid
			  INNER JOIN `vtiger_account` a ON a.accountid = p.accountid
			  LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
			  LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
			  WHERE vtiger_crmentity.deleted = 0
			  AND a.accountid = ".$recordId;*/
			$query = 'SELECT vtiger_crmentity.crmid,vtiger_invoice.subject, vtiger_invoice.invoice_no, vtiger_invoicecf.typedossier
			, vtiger_invoice.invoicestatus, vtiger_invoice.invoicedate, vtiger_invoicecf.campaign_no, vtiger_invoicecf.notesid, vtiger_invoice.total
			FROM vtiger_invoice
			INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_invoice.invoiceid
			LEFT OUTER JOIN vtiger_invoicecf ON vtiger_invoicecf.invoiceid = vtiger_crmentity.crmid
			LEFT OUTER JOIN vtiger_account ON vtiger_account.accountid = vtiger_invoice.accountid
			LEFT OUTER JOIN vtiger_salesorder ON vtiger_salesorder.salesorderid = vtiger_invoice.salesorderid
			LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
			LEFT JOIN vtiger_users ON vtiger_crmentity.smownerid = vtiger_users.id
			WHERE vtiger_crmentity.deleted = 0 AND (vtiger_invoice.accountid = '.$recordId.')'
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
			$query = preg_replace('/^\s*SELECT\s/', 'SELECT vtiger_senotesrel.dateapplication, vtiger_senotesrel.data, ', $query);
			//echo('<pre>APRES '.$query . '</pre>');
			break;
		default:
			$query = parent::getRelationQuery($recordId, $functionName, $relatedModule);
			
			break;
		}

		return $query;
	}


	

	/* 
	 * Cas particuliers de la fonction ci-dessus pour les modules affichant une seule catégorie de service
	 * ED150203
	 */
	public function getRelationQuery_RsnServices($recordId, $functionName, $relatedModule, $servicecategory) {
		$focus = CRMEntity::getInstance($this->getName());
		$focus->id = $recordId;
		$entityIds = $focus->getRelatedContactsIds();
		$entityIds = implode(',', $entityIds);

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
			, f.invoiceid
			, p.servicecategory
			, fcf.campaign_no
			, fcf.notesid
			, vtiger_crmentity.smownerid AS assigned_user_id
			FROM `vtiger_inventoryproductrel` lg
			INNER JOIN `vtiger_service` p
				ON lg.productid = p.serviceid
				AND p.servicecategory = \''.$servicecategory.'\'
			INNER JOIN `vtiger_invoice` f
				ON lg.id = f.invoiceid
			INNER JOIN `vtiger_crmentity`
				ON vtiger_crmentity.crmid = f.invoiceid
			INNER JOIN `vtiger_account` a
				ON a.accountid = f.accountid   
			LEFT JOIN `vtiger_invoicecf` fcf
				ON fcf.invoiceid = f.invoiceid
			LEFT JOIN vtiger_users
				ON vtiger_users.id = vtiger_crmentity.smownerid
			LEFT JOIN vtiger_groups
				ON vtiger_groups.groupid = vtiger_crmentity.smownerid
			WHERE vtiger_crmentity.deleted = 0
			AND ( a.accountid = '.$recordId;
		if($entityIds) {
			$query .= " OR f.contactid IN (".$entityIds."))";
		} else {
			$query .= ")";
		}
		$relatedModuleName = $relatedModule->getName();
		$query .= $this->getSpecificRelationQuery($relatedModuleName);
		$nonAdminQuery = $this->getNonAdminAccessControlQueryForRelation($relatedModuleName);
		if ($nonAdminQuery) {
			$query = appendFromClauseToQuery($query, $nonAdminQuery);
		}
		//echo('<pre>'.$query . '</pre>');
		return $query;
	}
	

	
	/** ED150619
	 * Function to get relation query for particular module with function name
	 * Similar to getRelationQuery but overridable.
	 * @param <record> $recordId
	 * @param <String> $functionName
	 * @param Vtiger_Module_Model $relatedModule
	 * @return <String>
	 */
	public function getRelationCounterQuery($recordId, $functionName, $relatedModule) {
				
		switch($relatedModule->getName()){
		 case 'ContactAddresses' :
		 case 'ContactEmails' :
		 case 'Contacts' :
				//don't show if not > 1
				$query = parent::getRelationCounterQuery($recordId, $functionName, $relatedModule);
				$query = preg_replace('/^SELECT\sCOUNT\(\*\)/', 'SELECT IF(COUNT(*)>1, COUNT(*), 0)', $query);
				return $query;
		 default:
			return parent::getRelationCounterQuery($recordId, $functionName, $relatedModule);
		}
	}
	
	/**
	 * Function to save a given record model of the current module
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function saveRecord(Vtiger_Record_Model $recordModel) {
		// ED141016 : majuscules obligatoires
		$recordModel->set('accountname', mb_strtoupper(remove_accent($recordModel->get('accountname'))));
		
		return parent::saveRecord($recordModel);
	}
}
