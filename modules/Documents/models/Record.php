<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Documents_Record_Model extends Vtiger_Record_Model {

	/**
	 * Function to get the Display Name for the record
	 * @return <String> - Entity Display Name for the record
	 */
	function getDisplayName() {
		//Since vtiger_entityname name field is made as title instead of notes_title
		return $this->get('notes_title');
	}

	function getDownloadFileURL() {
		if ($this->get('filelocationtype') == 'I') {
			$fileDetails = $this->getFileDetails();
			return 'index.php?module='. $this->getModuleName() .'&action=DownloadFile&record='. $this->getId() .'&fileid='. $fileDetails['attachmentsid'];
		} else {
			return $this->get('filename');
		}
	}

	function checkFileIntegrityURL() {
		return "javascript:Documents_Detail_Js.checkFileIntegrity('index.php?module=".$this->getModuleName()."&action=CheckFileIntegrity&record=".$this->getId()."')";
	}

	function checkFileIntegrity() {
		$recordId = $this->get('id');
		$downloadType = $this->get('filelocationtype');
		$returnValue = false;

		if ($downloadType == 'I') {
			$fileDetails = $this->getFileDetails();
			if (!empty ($fileDetails)) {
				$filePath = $fileDetails['path'];

				$savedFile = $fileDetails['attachmentsid']."_".$this->get('filename');

				if(fopen($filePath.$savedFile, "r")) {
					$returnValue = true;
				}
			}
		}
		return $returnValue;
	}

	function getFileDetails() {
		$db = PearDatabase::getInstance();
		$fileDetails = array();

		$result = $db->pquery("SELECT * FROM vtiger_attachments
							INNER JOIN vtiger_seattachmentsrel ON vtiger_seattachmentsrel.attachmentsid = vtiger_attachments.attachmentsid
							WHERE crmid = ?", array($this->get('id')));

		if($db->num_rows($result)) {
			$fileDetails = $db->query_result_rowdata($result);
		}
		return $fileDetails;
	}

	function downloadFile() {
		$fileDetails = $this->getFileDetails();
		$fileContent = false;

		if (!empty ($fileDetails)) {
			$filePath = $fileDetails['path'];
			$fileName = $fileDetails['name'];

			if ($this->get('filelocationtype') == 'I') {
				$fileName = html_entity_decode($fileName, ENT_QUOTES, vglobal('default_charset'));
				$savedFile = $fileDetails['attachmentsid']."_".$fileName;

				$fileSize = filesize($filePath.$savedFile);
				$fileSize = $fileSize + ($fileSize % 1024);

				if (fopen($filePath.$savedFile, "r")) {
					$fileContent = fread(fopen($filePath.$savedFile, "r"), $fileSize);

					header("Content-type: ".$fileDetails['type']);
					header("Pragma: public");
					header("Cache-Control: private");
					header("Content-Disposition: attachment; filename=$fileName");
					header("Content-Description: PHP Generated Data");
				}
			}
		}
		echo $fileContent;
	}

	function updateFileStatus() {
		$db = PearDatabase::getInstance();

		$db->pquery("UPDATE vtiger_notes SET filestatus = 0 WHERE notesid= ?", array($this->get('id')));
	}

	function updateDownloadCount() {
		$db = PearDatabase::getInstance();
		$notesId = $this->get('id');

		$result = $db->pquery("SELECT filedownloadcount FROM vtiger_notes WHERE notesid = ?", array($notesId));
		$downloadCount = $db->query_result($result, 0, 'filedownloadcount') + 1;

		$db->pquery("UPDATE vtiger_notes SET filedownloadcount = ? WHERE notesid = ?", array($downloadCount, $notesId));
	}

	function getDownloadCountUpdateUrl() {
		return "index.php?module=Documents&action=UpdateDownloadCount&record=".$this->getId();
	}
	
	function get($key) {
		$value = parent::get($key);
		if ($key === 'notecontent') {
			return decode_html($value);
		}
		return $value;
	}


	function getRelatedCampaigns($codeAffaire = false){
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', 1);
		
		$relatedModuleName = 'Campaigns';
		$relationListView = Vtiger_RelationListView_Model::getInstance($this, $relatedModuleName, '');
		/* no use
		$relationListView->set('orderby', 'createdtime');
		$relationListView->set('sortorder', 'desc');*/
		if($codeAffaire){ //TODO non testé
			$relationListView->set('searchkey', 'code_affaire');
			$relationListView->set('searchvalue', $codeAffaire);
		}
		return $relationListView->getEntries($pagingModel);
	}
	
	/** ED150708
	 * Function to get List of Fields which are related from Documents to Inventory Record
	 * @return <array>
	 */
	public function getInventoryMappingFields() {
		$mapping = array();
		$campaigns = $this->getRelatedCampaigns();
		foreach($campaigns as $campaign){
			$mapping[] = array('inventoryField'=>'campaign_no', 'defaultValue'=>$campaign->getId());
			break;
		}
		return $mapping;
	}
	
	/** ED150708
	 *
	 */
	function getRelatedProductsAndServices(){
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', 1);
		
		$relatedModuleName = 'Products';
		$relationListView = Vtiger_RelationListView_Model::getInstance($this, $relatedModuleName, '');
		$entries = $relationListView->getEntries($pagingModel);
		
		$relatedModuleName = 'Services';
		$relationListView = Vtiger_RelationListView_Model::getInstance($this, $relatedModuleName, '');
		
		$entries = array_merge($entries, $relationListView->getEntries($pagingModel));
		
		return $entries;
	}
	
	/** ED150708
	 * Retourne le tableau des tous les produits et services associés au coupon
	 * Affecte les quantités à 0
	 * Utilisé pour initialiser une nouvelle facture à partir d'un coupon
	 */
	public function getRelatedProductsDetailsForInventoryModule($inventory) {
		$products = $this->getRelatedProductsAndServices();
		$index = 1;
		$relatedProducts = array();
		foreach($products as $product){
			$productDetails = $product->getDetailsForInventoryModule($inventory);
			if($productDetails){
				$productDetails[1]['qty1'] = 0;
				if($index === 1){
					$relatedProducts = $productDetails;
				}
				else {
					//rename labels ending with '1'
					foreach($productDetails[1] as $detailIndex => $detail){
						if($detailIndex[strlen($detailIndex)-1] === '1'){
							unset($productDetails[1][$detailIndex]);
							$detailIndex = substr($detailIndex, 0, strlen($detailIndex)-1) . $index;
							$productDetails[1][$detailIndex] = $detail;
						}
					}
					$relatedProducts[$index] = $productDetails[1];
				}
				++$index;
			}
		}
		return $relatedProducts;
	}
	
	/**
	 * ED151106
	 * Affecte le prochain n° du compteur de relations
	 * Par exemple, n° de reçu fiscal
	 *
	 */
	public function getNexRelatedCounterValue(){
		global $adb;
		//get num 
		$query = "SELECT MAX(relatedcounter)
			FROM vtiger_notes
			WHERE notesid = ?";
		$params = array(
			$this->getId(),
		);
		$result = $adb->pquery($query, $params);
		if(!$result){
			echo "<pre>$query</pre>";
			var_dump($params);
			$adb->echoError();
			return false;
		}
		
		$relatedCounter = $adb->query_result($result, 0, 0);
		if(!$relatedCounter)
			$relatedCounter = 1;
		else
			$relatedCounter++;
		
		//mise à jour du compteur
		$query = "UPDATE vtiger_notes
			SET relatedcounter = ?
			WHERE notesid = ?";
		$params = array(
			$relatedCounter,
			$this->getId(),
		);
		$result = $adb->pquery($query, $params);
		if(!$result){
			echo "<pre>$query</pre>";
			var_dump($params);
			$adb->echoError();
			return false;
		}
		
		$this->set('relatedcounter', $relatedCounter);
		
		return $relatedCounter;
	}
}