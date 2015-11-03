<?php

require_once 'include/events/VTEventHandler.inc';

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
			$records = ($relationModel->getRecords($accountsRecord));
			foreach ($records as $record) {
				if ($record->get("etat") == 0 && $record->get('separum')) {
					$this->updateSeparum($prelevementId, $record->get("separum"));
					return;
				}
			}

			$mainContactRecord = $accountsRecord->getRelatedMainContact();
			$periodicite = preg_replace('/^(\w)\D+(\d*)$/', '$1$2', $prelevement->get('periodicite'));
			$separum = 'RSDN-' . $mainContactRecord->get('contact_no') . '-' . $periodicite;
			$this->updateSeparum($prelevementId, $separum);

		}
	}

	function updateSeparum($recordId, $newSeparum) {
		$sql = "UPDATE `vtiger_rsnprelevements`
				SET `separum` = ?
				WHERE `rsnprelevementsid` = ?";
		$db = PearDatabase::getInstance();
		$db->pquery($sql, array($newSeparum, $recordId));
	}
}