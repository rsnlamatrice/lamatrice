<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Contacts_ListView_Model extends Vtiger_ListView_Model {

	/**
	 * Function to get the list of Mass actions for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associative array of Link type to List of  Vtiger_Link_Model instances for Mass Actions
	 */
	public function getListViewMassActions($linkParams) {
		$massActionLinks = parent::getListViewMassActions($linkParams);

		$currentUserModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
		$emailModuleModel = Vtiger_Module_Model::getInstance('Emails');

		if($currentUserModel->hasModulePermission($emailModuleModel->getId())) {
			$massActionLink = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_SEND_EMAIL',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerSendEmail("index.php?module='.$this->getModule()->getName().'&view=MassActionAjax&mode=showComposeEmailForm&step=step1","Emails");',
				'linkicon' => ''
			);
			$massActionLinks['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}

		$SMSNotifierModuleModel = Vtiger_Module_Model::getInstance('SMSNotifier');
		if($currentUserModel->hasModulePermission($SMSNotifierModuleModel->getId())) {
			$massActionLink = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_SEND_SMS',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerSendSms("index.php?module='.$this->getModule()->getName().'&view=MassActionAjax&mode=showSendSMSForm","SMSNotifier");',
				'linkicon' => ''
			);
			$massActionLinks['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}
		
		$moduleModel = $this->getModule();
		if($currentUserModel->hasModuleActionPermission($moduleModel->getId(), 'EditView')) {
			$massActionLink = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_TRANSFER_OWNERSHIP',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerTransferOwnership("index.php?module='.$moduleModel->getName().'&view=MassActionAjax&mode=transferOwnership")',
				'linkicon' => ''
			);
			$massActionLinks['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}
		
		//ED150628
		$moduleModel = $this->getModule();
		if($currentUserModel->hasModuleActionPermission($moduleModel->getId(), 'EditView')) {
			$massActionLink = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_ASSIGN_CRITERE4D',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerAssignCritere4D("index.php?module='.$moduleModel->getName().'&view=MassActionAjax&mode=assignCritere4D")',
				'linkicon' => ''
			);
			$massActionLinks['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}
		$moduleModel = $this->getModule();
		if($currentUserModel->hasModuleActionPermission($moduleModel->getId(), 'EditView')) {
			$massActionLink = array(
				'linktype' => 'LISTVIEWMASSACTION',
				'linklabel' => 'LBL_UNASSIGN_CRITERE4D',
				'linkurl' => 'javascript:Vtiger_List_Js.triggerUnassignCritere4D("index.php?module='.$moduleModel->getName().'&view=MassActionAjax&mode=unassignCritere4D")',
				'linkicon' => ''
			);
			$massActionLinks['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		}

		return $massActionLinks;
	}

	/**
	 * Function to get the list of listview links for the module
	 * @param <Array> $linkParams
	 * @return <Array> - Associate array of Link Type to List of Vtiger_Link_Model instances
	 */
	function getListViewLinks($linkParams) {
		$links = parent::getListViewLinks($linkParams);

		$index=0;
		foreach($links['LISTVIEWBASIC'] as $link) {
			if($link->linklabel == 'Send SMS') {
				unset($links['LISTVIEWBASIC'][$index]);
			}
			$index++;
		}
		return $links;
	}
	
	
	/** ED15052
	 * ajoute des champs accessibles par {$LISTVIEW_ENTRY->getRawDataFieldValue('mailingstreet2')}
	 */
	function getQuery() {
		//Ajout systématique du champ vtiger_contactaddress.mailingstreet2
		//Ajout systématique du champ vtiger_contactdetails.isgroup
		// et ce, si la table est présente dans la jointure
		//TODO add join vtiger_contactaddress, vtiger_contactdetails
		//$queryGenerator = $this->get('query_generator');
		$listQuery = parent::getQuery();
		foreach(array('vtiger_contactaddress.mailingstreet2', 'vtiger_contactdetails.isgroup') as $fieldName){
			if(!preg_match('/SELECT.+'.preg_quote($fieldName).'.+FROM\s/i', $listQuery)
			&& strpos($listQuery, explode('.',$fieldName)[0]))
				$listQuery = preg_replace('/^\s*SELECT\s/i', 'SELECT '.$fieldName.',', $listQuery);
		}
		return $listQuery;
	}

	/** 
	 * Function to get the list view entries
	 * @param Vtiger_Paging_Model $pagingModel
	 * @return <Array> - Associative array of record id mapped to Vtiger_Record_Model instance.
	 *
	 * ED150424 : add module prefix char ('C') when searching on 'contact_no' field
	 */
	public function getListViewEntries($pagingModel) {
		//TODO add mailingstreet2
		
		$searchKey = $this->get('search_key');
		$searchValue = $this->get('search_value');
		
		if(is_array($searchKey)){
			$operators = $this->get('operator');
			for($i = 0; $i < count($searchKey); $i++){
				//add module prefix char ('C') when searching on 'contact_no' field
				if($searchKey[$i] == 'contact_no' && $searchValue[$i] && is_numeric($searchValue[$i][0])){
					$searchValue[$i] = $this->getModuleCustomNumberingPrefix() . $searchValue[$i];
					$this->set('search_value', $searchValue);
				}
				//une recherche sur le nom s'effectue aussi sur le mailingstreet2 si c'est un groupe
				// lastname LIKE % OR (mailingstreet2 LIKE % OR isgroup == 0 )
				else if($searchKey[$i] == 'lastname' && $searchValue[$i]){
					/* un sous-tableau
					*/
					$searchKey[$i] = array('lastname', null, array('mailingstreet2', null, 'isgroup'));
					$searchValue[$i] = array( $searchValue[$i], null, array($searchValue[$i], null, '1'));
					$operators[$i] = array( $operators[$i], 'OR', array($operators[$i], 'AND', 'e'));
					$this->set('search_key', $searchKey);
					$this->set('search_value', $searchValue);
					$this->set('operator', $operators);
				}
			}
		}
		elseif($searchKey == 'contact_no' && $searchValue && is_numeric($searchValue[0])){
			//add module prefix char ('C') when searching on 'contact_no' field
			$this->set('search_value', $this->getModuleCustomNumberingPrefix() . $searchValue);
		}
		return parent::getListViewEntries($pagingModel);
	}
	
	/* ED150424 */
	private function getModuleCustomNumberingPrefix(){
		$model = Settings_Vtiger_CustomRecordNumberingModule_Model::getInstance($this->getModule()->getName());
		$data = $model->getModuleCustomNumberingData();
		return $data['prefix'];
	}
}