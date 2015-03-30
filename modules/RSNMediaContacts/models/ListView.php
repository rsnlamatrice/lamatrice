<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class RSNMediaContacts_ListView_Model extends Vtiger_ListView_Model {

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
		
		$massActionLink = array(
			'linktype' => 'LISTVIEWMASSACTION',
			'linklabel' => 'LBL_SHOW_EMAILS_LIST',
			'linkurl' => 'javascript:Vtiger_List_Js.triggerShowEmailList("index.php?module='.$this->getModule()->getName().'&view=MassActionAjax&mode=showEmailList","Emails");',
			'linkicon' => ''
		);
		$massActionLinks['LISTVIEWMASSACTION'][] = Vtiger_Link_Model::getInstanceFromValues($massActionLink);
		
		return $massActionLinks;
	}
	
	

	/**
	 * Function to get the list view header
	 * @return <Array> - List of Vtiger_Field_Model instances
	 *
	 * ED150325 adds 'satisfaction' field
	 */
	public function getListViewHeaders() {
		$headerFieldModels = parent::getListViewHeaders();
		
		$relationsModel = Vtiger_Module_Model::getInstance('RSNMediaRelations');
		//array_unshift( $headerFieldModels, $relationsModel->getField('satisfaction'));
		array_push( $headerFieldModels, $relationsModel->getField('satisfaction'));
		
		return $headerFieldModels;
	}
	
	/**
	 * Function to get the list view query
	 * @return <Array> - SQL query
	 *
	 * ED150325 adds 'satisfaction' column
	 */
	public function getQuery(){
		$listQuery = parent::getQuery();
		$listQuery = preg_replace('/^\s*SELECT\s/i'
					, 'SELECT IFNULL((SELECT ROUND(AVG(vtiger_rsnmediarelations.satisfaction)/50) * 50
					  FROM vtiger_rsnmediarelations
					  WHERE vtiger_rsnmediarelations.mediacontactid = vtiger_rsnmediacontacts.rsnmediacontactsid
					  AND vtiger_rsnmediarelations.daterelation > CURRENT_DATE - 365 * 2
					), \'-\') AS satisfaction, '
					, $listQuery);
		return $listQuery;
	}
}