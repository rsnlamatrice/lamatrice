<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Accounts_Record_Model extends Vtiger_Record_Model {

	/**
	 * Function returns the details of Accounts Hierarchy
	 * @return <Array>
	 */
	function getAccountHierarchy() {
		$focus = CRMEntity::getInstance($this->getModuleName());
		$hierarchy = $focus->getAccountHierarchy($this->getId());
		$i=0;
		foreach($hierarchy['entries'] as $accountId => $accountInfo) {
			preg_match('/<a href="+/', $accountInfo[0], $matches);
			if($matches != null) {
				preg_match('/[.\s]+/', $accountInfo[0], $dashes);
				preg_match("/<a(.*)>(.*)<\/a>/i",$accountInfo[0], $name);

				$recordModel = Vtiger_Record_Model::getCleanInstance('Accounts');
				$recordModel->setId($accountId);
				$hierarchy['entries'][$accountId][0] = $dashes[0]."<a href=".$recordModel->getDetailViewUrl().">".$name[2]."</a>";
			}
		}
		return $hierarchy;
	}
	/** ED150515
	 * Function returns the account contacts record models
	 * @return <Array>
	 */
	function getContactsRecordModels() {
		
		$moduleName = $this->getModuleName();
		$relatedModuleName = 'Contacts';
		$parentId = $this->getId();
		
		$query = "SELECT vtiger_crmentity.crmid, vtiger_crmentity.label, vtiger_contactdetails.*, vtiger_contactscf.*, vtiger_contactaddress.*
			FROM vtiger_contactdetails
			JOIN vtiger_crmentity
				ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
			LEFT JOIN vtiger_contactscf
				ON vtiger_contactscf.contactid = vtiger_crmentity.crmid
			LEFT JOIN vtiger_contactaddress
				ON vtiger_contactaddress.contactaddressid = vtiger_crmentity.crmid
			WHERE vtiger_crmentity.deleted = 0
			AND vtiger_contactdetails.accountid = ?
		";
		global $adb;
		$entries = array();
		$result = $adb->pquery($query, array($this->getId()));
		while(!$result->EOF){
			$row = $adb->fetchByAssoc($result);
			$record = Vtiger_Record_Model::getCleanInstance($relatedModuleName)->setRawData($row);
			$entries[$row['crmid']] = $record;
		}
		return $entries;
	}
	
	/** ED150515
	 * Function returns the account main contact
	 * @return <Array>
	 */
	function getRelatedMainContacts() {
		
		$moduleName = $this->getModuleName();
		$relatedModuleName = 'Contacts';
		$parentId = $this->getId();
		
		$query = "SELECT vtiger_crmentity.crmid, vtiger_crmentity.label, vtiger_contactdetails.*, vtiger_contactaddress.*
			FROM vtiger_contactdetails
			JOIN vtiger_crmentity
				ON vtiger_contactdetails.contactid = vtiger_crmentity.crmid
			LEFT JOIN vtiger_contactaddress
				ON vtiger_contactaddress.contactaddressid = vtiger_crmentity.crmid
			WHERE vtiger_crmentity.deleted = 0
			AND vtiger_contactdetails.accountid = ?
			AND vtiger_contactdetails.reference = 1
		";
		global $adb;
		$entries = array();
		$result = $adb->pquery($query, array($this->getId()));
		while(!$result->EOF){
			$row = $adb->fetchByAssoc($result);
			$entries[$row['crmid']] = $row;
		}
		return $entries;
	}
	
	/** ED150821
	 * Function returns the account main contact
	 * @return contact record
	 */
	function getRelatedMainContact() {
		foreach($this->getRelatedMainContacts() as $contactId => $contact){
			return Vtiger_Record_Model::getInstanceById($contactId, 'Contacts');
		}
		return false;
	}
	
	/**
	 * Function returns the url for create event
	 * @return <String>
	 */
	function getCreateEventUrl() {
		$calendarModuleModel = Vtiger_Module_Model::getInstance('Calendar');
		return $calendarModuleModel->getCreateEventRecordUrl().'&parent_id='.$this->getId();
	}

	/**
	 * Function returns the url for create todo
	 * @retun <String>
	 */
	function getCreateTaskUrl() {
		$calendarModuleModel = Vtiger_Module_Model::getInstance('Calendar');
		return $calendarModuleModel->getCreateTaskRecordUrl().'&parent_id='.$this->getId();
	}

	/**
	 * Function to check duplicate exists or not
	 * @return <boolean>
	 */
	public function checkDuplicate() {
		$db = PearDatabase::getInstance();

		$query = "SELECT 1 FROM vtiger_crmentity WHERE setype = ? AND label = ? AND deleted = 0";
		$params = array($this->getModule()->getName(), decode_html($this->getName()));

		$record = $this->getId();
		if ($record) {
			$query .= " AND crmid != ?";
			array_push($params, $record);
		}

		$result = $db->pquery($query, $params);
		if ($db->num_rows($result)) {
			return true;
		}
		return false;
	}

	/**
	 * Function to get List of Fields which are related from Accounts to Inventory Record.
	 * @return <array>
	 */
	public function getInventoryMappingFields() {
		return array(	
				//Billing Address Fields
				array('parentField'=>'bill_city', 'inventoryField'=>'bill_city', 'defaultValue'=>''),
				array('parentField'=>'bill_street', 'inventoryField'=>'bill_street', 'defaultValue'=>''),
				array('parentField'=>'bill_street2', 'inventoryField'=>'bill_street2', 'defaultValue'=>''),
				array('parentField'=>'bill_street3', 'inventoryField'=>'bill_street3', 'defaultValue'=>''),
				array('parentField'=>'bill_state', 'inventoryField'=>'bill_state', 'defaultValue'=>''),
				array('parentField'=>'bill_code', 'inventoryField'=>'bill_code', 'defaultValue'=>''),
				array('parentField'=>'bill_country', 'inventoryField'=>'bill_country', 'defaultValue'=>''),
				array('parentField'=>'bill_pobox', 'inventoryField'=>'bill_pobox', 'defaultValue'=>''),
				array('parentField'=>'bill_addressformat', 'inventoryField'=>'bill_addressformat', 'defaultValue'=>''),

				//Shipping Address Fields
				array('parentField'=>'ship_city', 'inventoryField'=>'ship_city', 'defaultValue'=>''),
				array('parentField'=>'ship_street', 'inventoryField'=>'ship_street', 'defaultValue'=>''),
				array('parentField'=>'ship_street2', 'inventoryField'=>'ship_street2', 'defaultValue'=>''),
				array('parentField'=>'ship_street3', 'inventoryField'=>'ship_street3', 'defaultValue'=>''),
				array('parentField'=>'ship_state', 'inventoryField'=>'ship_state', 'defaultValue'=>''),
				array('parentField'=>'ship_code', 'inventoryField'=>'ship_code', 'defaultValue'=>''),
				array('parentField'=>'ship_country', 'inventoryField'=>'ship_country', 'defaultValue'=>''),
				array('parentField'=>'ship_pobox', 'inventoryField'=>'ship_pobox', 'defaultValue'=>''),
				array('parentField'=>'ship_addressformat', 'inventoryField'=>'ship_addressformat', 'defaultValue'=>''),
		);
	}
	
	
	
	/** ED150507
	 * Function to get RSNAboRevues array for this account, order by decreasing date
	 */
	public function getRSNAboRevues($isabonneOnly = false, $dateAbo = false){
		
		$moduleName = $this->getModuleName();
		$relatedModuleName = 'RSNAboRevues';
		$parentId = $this->getId();
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page',1);
		$pagingModel->set('limit', $isabonneOnly ? 8 : 99); //TODO 99?!
			
		$parentRecordModel = $this;
		$relationListView = Vtiger_RelationListView_Model::getInstance($parentRecordModel, $relatedModuleName, null);

		$orderBy = 'debutabo';
		$sortOrder = 'DESC';
			
		$relationListView->set('orderby', $orderBy);
		$relationListView->set('sortorder',$sortOrder);

//$db = PearDatabase::getInstance();
//$db->setDebug(true);
		$allEntries = $relationListView->getEntries($pagingModel);
		
		$entries = array();
		if($dateAbo){
			if(is_string($dateAbo))
				$dateAbo = new DateTime($dateAbo);
			foreach($allEntries as $id=>$entry)
				if($entry->getDebutAbo() <= $dateAbo
				&& (!$entry->getFinAbo() || $entry->getFinAbo() >= $dateAbo)
				&& (!$isabonneOnly || $entry->get('isabonne')))
					$entries[$id] = $entry;
				else
					break;
			
		}
		elseif(!$isabonneOnly){
			return $allEntries;
		}
		else {
			foreach($allEntries as $id=>$entry)
				if($entry->get('isabonne'))
					$entries[$id] = $entry;
				else
					break;
		}
		return $entries;
	}
	
	//Génération du PDF du reçu fiscal
	public function getRecuFiscalPDF($filePath, $documentRecordModel, $contactRecordModel = false){
		$year = preg_replace('/^.*(20\d+).*$/', '$1', $documentRecordModel->get('notes_title'));
		
		if(!$contactRecordModel)
			$contactRecordModel = $this->getRelatedMainContact();
		
		$infos = $this->getInfosRecuFiscal($year, $documentRecordModel, $contactRecordModel);
		if(!$infos)
			return $infos;
		
		include_once('modules/Accounts/pdf/RecuFiscal/PDFController.php');
		
		$recordId = $this->getId();
		$moduleName = $this->getModuleName();

		$controllerClassName = "Vtiger_RecuFiscal_PDFController";
		
		$controller = new $controllerClassName($moduleName);
		$controller->loadRecord($recordId);
		$controller->setColumnValue('year', $year);
		$controller->setColumnValue('contactid', $contactRecordModel->getId());
		
		$controller->setColumnValue('montant', $infos['montant']);
		$controller->setColumnValue('recu_fiscal_num', $infos['recu_fiscal_num']);
		$controller->setColumnValue('recu_fiscal_date', $infos['date_edition']);
		
		$fileName = preg_replace('/\W/', '_', remove_accent($this->getName()));
		$fileName = 'RecuFiscal_'.$year.'_'.$fileName.'_'.$contactRecordModel->get('contact_no') . '.pdf';
		if($filePath){
			$fileName = $filePath . '/' . $fileName;
			$controller->Output($fileName, 'F');
			return $fileName;
		}
		else{
			$controller->Output($fileName, 'D');
		}
		return $fileName;
	}
	
	function getInfosRecuFiscal($year, $documentRecordModel, $contactRecordModel){
		
		global $adb;
		$query = "SELECT dateapplication, data
			FROM vtiger_senotesrel
			WHERE notesid = ?
			AND crmid IN ( ?, ?)
			AND IFNULL(data, '') LIKE '%Montant : %'
			ORDER BY dateapplication DESC
			LIMIT 1";
		$params = array($documentRecordModel->getId(), $this->getId(), $contactRecordModel->getId());
		$result = $adb->pquery($query, $params);
		
		if($adb->num_rows($result) === 0){
			return $this->createRecuFiscalRelation($year, $documentRecordModel);
		}
		$row = $adb->fetchByAssoc($result, 0, false);
		$dateApplication = preg_replace('/^(\d+)\-(\d+)\-(\d+)(\s.*)?$/', '$3/$2/$1', $row['dateapplication']);
		$data = $row['data'];
		
		//Montant : 
		$matches = array();
		if(! preg_match_all('/Montant : (?<montant>\d+([,\.]\d+)?)(\D|$)/', $data, $matches)){
			die('Erreur regex Montant');
		}
		$montant = $matches['montant'][0];
		
		//Reçu n° : n'existe pas sur les anciens
		//je n'ai pas réussi à ne faire qu'un seul regex pour <montant> et <num>
		$matches = array();
		if(preg_match_all('/'.preg_quote('Reçu n° : ').'(?<num>\d+)/', $data, $matches))
			$numRecu = $matches['num'][0];
		else
			$numRecu = '';
		return array(
			'montant' => $montant,
			'recu_fiscal_num' => $numRecu,
			'date_edition' => $dateApplication,
		);
	}
	
	/**
	 * Calcul du reçu fiscal et création de la relation
	 *
	 * @return true if needed (> 3€) and exists
	 */
	function createRecuFiscalRelation($year, $documentRecordModel){
		
		global $adb;
		$query = "
		SELECT SUM(montant) AS montant
		FROM (
			SELECT SUM(vtiger_inventoryproductrel.quantity * vtiger_inventoryproductrel.listprice) AS montant
				FROM vtiger_inventoryproductrel
				JOIN vtiger_invoice
					ON vtiger_invoice.invoiceid = vtiger_inventoryproductrel.id
				JOIN vtiger_crmentity as vtiger_crmentity_invoice
					ON vtiger_invoice.invoiceid = vtiger_crmentity_invoice.crmid
				JOIN vtiger_service
					ON vtiger_service.serviceid = vtiger_inventoryproductrel.productid
				JOIN vtiger_crmentity as vtiger_crmentity_service
					ON vtiger_service.serviceid = vtiger_crmentity_service.crmid
				WHERE vtiger_crmentity_invoice.deleted = false
				AND vtiger_crmentity_service.deleted = false
				AND vtiger_service.servicecategory = 'Dons'
				AND vtiger_invoice.accountid = ?
				AND vtiger_invoice.invoicedate BETWEEN ? AND ?
			UNION
				SELECT SUM(vtiger_rsnprelvirement.montant)
				FROM vtiger_rsnprelvirement
				JOIN vtiger_crmentity as vtiger_crmentity_prelvir
					ON vtiger_rsnprelvirement.rsnprelvirementid = vtiger_crmentity_prelvir.crmid
				JOIN vtiger_rsnprelevements
					ON vtiger_rsnprelvirement.rsnprelevementsid = vtiger_rsnprelevements.rsnprelevementsid
				JOIN vtiger_crmentity as vtiger_crmentity_prelevements
					ON vtiger_rsnprelevements.rsnprelevementsid = vtiger_crmentity_prelevements.crmid
				WHERE vtiger_crmentity_prelvir.deleted = false
				AND vtiger_crmentity_prelevements.deleted = false
				AND vtiger_rsnprelvirement.rsnprelvirstatus = 'Ok'
				AND vtiger_rsnprelevements.prelvtype = 'Prélèvement périodique'
				AND vtiger_rsnprelevements.accountid = ?
				AND vtiger_rsnprelvirement.dateexport BETWEEN ? AND ?
		) _source
		";
		$params = array(
				$this->getId(),
				"$year-01-01",
				"$year-12-31 23:59:59",
				$this->getId(),
				"$year-01-01",
				"$year-12-31 23:59:59",
		);
		$result = $adb->pquery($query, $params);
		if(!$result){
			echo "<pre>$query</pre>";
			var_dump($params);
			$adb->echoError();
			return false;
		}
		$montant = $adb->query_result($result, 0, 0);
		if(!$montant){ // || $montant < 3 on génère quand même le reçu, on ne l'envoie pas 
			return false;
		}
		$montant = round($montant, 2);
		
		$numRecu = $documentRecordModel->getNexRelatedCounterValue();
		
		//create relation
		
		$data = "Montant : $montant €, Reçu n° : $numRecu";
		
		$query = "INSERT INTO vtiger_senotesrel (notesid, crmid, dateapplication, data)
			VALUES(?, ?, CURRENT_DATE, ?)
			ON DUPLICATE KEY UPDATE data = ?";
		$params = array(
			$documentRecordModel->getId(),
			$this->getId(),
			$data,
			$data,
		);
		$result = $adb->pquery($query, $params);
		if(!$result){
			echo "<pre>$query</pre>";
			var_dump($params);
			$adb->echoError();
			return false;
		}
		return array(
			'montant' => $montant,
			'recu_fiscal_num' => $numRecu,
			'date_edition' => date('d-m-Y'),
		);
	}
	
	/**
	 * ED141005
	 * getListViewPicklistValues
	 */
	public function getPicklistValuesDetails($fieldname){
		switch($fieldname){
			case 'accounttype':
				return array(
					'' => array( 'label' => 'Normal'),
					'Dépôt-vente' => array( 'label' => 'Dépôt-vente' ),
				);
			default:
				return parent::getPicklistValuesDetails($fieldname);
		}
	}
}
