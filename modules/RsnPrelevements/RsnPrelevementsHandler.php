<?php

require_once 'include/events/VTEventHandler.inc';
require_once 'modules/RSNAboRevues/models/Record.php';

class RsnPrelevementsHandler extends VTEventHandler {

	function handleEvent($eventName, $entity) {
		global $log, $adb;

		$moduleName = $entity->getModuleName();
		switch ($moduleName){
		case 'RsnPrelevements' :
			switch($eventName){
			case 'vtiger.entity.aftersave':
				$this->handleAfterSaveRsnPrelevementsEvent($entity, $moduleName);
				break;
			}
		break;
		}
	}

	//TODO: manage when there is more than one enabled RsnPrelevement and when it is a NEW RsnPrelevement (and not a RIB changed)...
	function handleAfterSaveRsnPrelevementsEvent($entity, $moduleName) {
		$prelevementId = $entity->getId();
		$data = $entity->getData();
		$prelevement = Vtiger_Record_Model::getInstanceById($prelevementId, $moduleName);
		$separum = $prelevement->get('separum');
		
		if (!$separum) {
			$relationModel = Vtiger_Relation_Model::getInstance(Vtiger_Module_Model::getInstance("Accounts"), Vtiger_Module_Model::getInstance($moduleName));
			$accountsRecord = Vtiger_Record_Model::getInstanceById($prelevement->get('accountid'), 'Accounts');
			$existingPrelevements = ($relationModel->getRecords($accountsRecord));
			foreach ($existingPrelevements as $record) {
				if ($record->get("etat") == 0 && $record->get('separum') && strlen($record->get('separum')) > 8) {
					$fieldNames = array('numcompte', 'codebanque', 'codeguichet', 'sepaibanpays', 'sepaibanbban', 'sepabic');
					$allFieldsAreEqual = true;
					foreach($fieldNames as $fieldName)
						if($record->get($fieldName) != $prelevement->get($fieldName)){
							$allFieldsAreEqual = false;
							break;
						}
					if($allFieldsAreEqual){
						$this->updateSeparum($prelevement, $record->get("separum"), $record->get("dejapreleve"));
						return;
					}
				}
			}
			$separum = $this->getNewRUM($prelevement, $accountsRecord, $existingPrelevements);
			$this->updateSeparum($prelevement, $separum);
		}
		$this->ensureAboRevue($prelevement);
	}
	
	function getNewRUM($prelevement, $accountsRecord, $existingPrelevements){
		$mainContactRecord = $accountsRecord->getRelatedMainContact();
		$periodicite = preg_replace('/^(\w)\D+(\d*)$/', '$1$2', $prelevement->get('periodicite'));
		$existingRUMs = array();
		foreach ($existingPrelevements as $record) {
			$existingRUMs[$record->get('separum')] = true;
		}
		for($nIndex = 0; $nIndex < 255; $nIndex++){
			$separum = 'RSDN-' . $mainContactRecord->get('contact_no') . '-' . $periodicite . ($nIndex ? '#' . $nIndex : '');
			if(!array_key_exists($separum, $existingRUMs))
				break;
		}
		return $separum;
	}
	
	function ensureAboRevue($prelevement){
		$aboRevueModuleModel = Vtiger_Module_Model::getInstance('RSNAboRevues');
		if($prelevement->get('etat') == 0){
			$periodicite = preg_replace('/^(\D+)\s\d*$/', '$1', $prelevement->get('periodicite'));
			switch($periodicite){
			case 'Annuel':
				$months = 12;
				break;
			case 'Mensuel':
				$months = 3;
				break;
			default:
				$months = 6;
				break;
			}
			$aboRevueModuleModel->ensureAboRevue($prelevement->get('accountid'), $months, RSNABOREVUES_TYPE_NUM_MERCI, $prelevement);
		}
	}

	function updateSeparum($prelevement, $newSeparum, $dejapreleve = null) {
		$sql = "UPDATE `vtiger_rsnprelevements`
				SET `separum` = ?
				, dejapreleve = ?
				WHERE `rsnprelevementsid` = ?";
		$db = PearDatabase::getInstance();
		$result = $db->pquery($sql, array($newSeparum, $dejapreleve, $prelevement->getId()));
		$prelevement->set('separum', $newSeparum);
		$prelevement->set('dejapreleve', $dejapreleve);
	}
}