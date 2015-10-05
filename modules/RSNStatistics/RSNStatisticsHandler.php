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
			$sql = "RENAME TABLE `" . RSNStatistics_Utils_Helper::getStatsTableNameFromId($id) . "` TO `" . RSNStatistics_Utils_Helper::getStatsTableName($id, $data) . "`";

			$db = PearDatabase::getInstance();
			$db->pquery($sql);
		}
	}

	//create a new table
	function handleAfterSaveRSNStatisticsEvent($entity, $moduleName) {
		if ($entity->isNew()) {
			$data = $entity->getData();
			$sql = "CREATE TABLE IF NOT EXISTS `" . RSNStatistics_Utils_Helper::getStatsTableName($entity->getId(), $data) . "` (
				  `id` int(19) NOT NULL AUTO_INCREMENT,
				  `crmid` int(19) NOT NULL,
				  `name` varchar(30) NOT NULL,
				  `code` varchar(30) NOT NULL,
				  `begin_date` TIMESTAMP NULL DEFAULT NULL,
				  `end_date` TIMESTAMP NULL DEFAULT NULL,
				  `last_update` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB  DEFAULT CHARSET=utf8";

			$db = PearDatabase::getInstance();
			$db->pquery($sql);
		}
	}

	//remove the table
	function handleBeforeDeleteRSNStatisticsEvent($entity, $moduleName) {
		$data = $entity->getData();
		$sql = "DROP TABLE `" . RSNStatistics_Utils_Helper::getStatsTableName($entity->getId(), $data) . "`";
		$db = PearDatabase::getInstance();
		$db->pquery($sql);
	}
}