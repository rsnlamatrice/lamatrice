<?php

require_once 'include/events/VTEventHandler.inc';

class RSNStatisticsHandler extends VTEventHandler {

	function handleEvent($eventName, $entity) {
		global $log, $adb;

		$moduleName = $entity->getModuleName();
		switch ($moduleName){
		case 'RSNStatistics' :
			switch($eventName){
			case 'vtiger.entity.beforesave':
				$this->handleBeforeSaveRSNStatisticsEvent($entity, $moduleName);
				break;
			case 'vtiger.entity.aftersave':
				$this->handleAfterSaveRSNStatisticsEvent($entity, $moduleName);
				break;
			case 'vtiger.entity.beforedelete':
				$this->handleBeforeDeleteRSNStatisticsEvent($entity, $moduleName);
				break;
			}
		break;
		}
	}

	//update the table name ??
	function handleBeforeSaveRSNStatisticsEvent($entity, $moduleName) {
		if (!$entity->isNew()) {
			$data = $entity->getData();
			$id = $entity->getId();
			
			$oldName = RSNStatistics_Utils_Helper::getStatsTableNameFromId($id);
			$newName = RSNStatistics_Utils_Helper::getStatsTableName($id, $data);
			if($oldName != $newName){
				$sql = "RENAME TABLE `$oldName` TO `$newName`";
	
				$db = PearDatabase::getInstance();
				$db->query($sql);
			}
		}
	}

	//create a new table
	function handleAfterSaveRSNStatisticsEvent($entity, $moduleName) {
		//ED151017 if ($entity->isNew()) {
			$data = $entity->getData();
			$sql = "CREATE TABLE IF NOT EXISTS `" . RSNStatistics_Utils_Helper::getStatsTableName($entity->getId(), $data) . "` (
				  `id` int(19) NOT NULL AUTO_INCREMENT,
				  `crmid` int(19) NOT NULL,
				  `name` varchar(30) NOT NULL,
				  `code` varchar(30) NOT NULL,
				  `begin_date` TIMESTAMP NULL DEFAULT NULL,
				  `end_date` TIMESTAMP NULL DEFAULT NULL,
				  `rsnfiltrestatistiqueid` INT(11) NOT NULL DEFAULT '0',
				  `filterid` INT(19) NOT NULL DEFAULT '0',
				  `last_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  PRIMARY KEY (`id`),
				  UNIQUE( `crmid`, `code`, `rsnfiltrestatistiqueid`, `filterid`)
				) DEFAULT CHARSET=utf8";

			$db = PearDatabase::getInstance();
			$db->query($sql);
			//if($result){ IF NOT EXISTS fait qu'on a jamais d'erreur
				//Add fields
				$entity->focus->check_statistic_table_fields($moduleName);
			//}
		
		//ED151017 }
	}

	//remove the table
	function handleBeforeDeleteRSNStatisticsEvent($entity, $moduleName) {
		$data = $entity->getData();
		$sql = "DROP TABLE `" . RSNStatistics_Utils_Helper::getStatsTableName($entity->getId(), $data) . "`";
		$db = PearDatabase::getInstance();
		$db->pquery($sql);
	}
}
