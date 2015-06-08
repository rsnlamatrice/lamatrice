<?php

class RSNImport {
	//tmp add and remove table / Schedule rox on install enable / uninstall disable

	public function vtlib_handler($moduleName, $eventType) {
		if ($eventType == 'module.postinstall') {
			self::addRsnImportSource();
			self::initImportSources();
			self::addScheduleTask();
		} else if ($eventType == 'module.enabled') {
			self::enableScheduleTask();
		} else if ($eventType == 'module.disabled') {
			self::disableScheduleTask();
		}
	}

	static function addRsnImportSource() {
		$db = PearDatabase::getInstance();
		$sql = "CREATE TABLE IF NOT EXISTS `vtiger_rsnimportsources` (
			`importsourcesid` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
			  `tabid` int(19) NOT NULL,
			  `class` varchar(64) NOT NULL,
			  `disabled` tinyint(1) NOT NULL DEFAULT '0',
			  `sortorderid` int(11) NOT NULL DEFAULT '0',
			  `creationdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4;";
		$db->pquery($sql);
	}

	static function initImportSources() {
		$db = PearDatabase::getInstance();
		$sql = "TRUNCATE `vtiger_rsnimportsources`;";
		$db->pquery($sql);
		$sql = "INSERT INTO `vtiger_rsnimportsources` (`tabid`, `class`, `disabled`, `sortorderid`, `creationdate`) VALUES
			(23, 'ImportInvoicesFromPrestashop', 0, 0, '2015-05-06 16:35:19'),
			(23, 'ImportInvoicesFromCogilog', 0, 0, '2015-05-06 16:41:12');";
		$db->pquery($sql);
	}

	static function addScheduleTask() {
		$db = PearDatabase::getInstance();
		$sql = "DELETE FROM `vtiger_cron_task` WHERE name = 'Schedule RSNImport';";
		$db->pquery($sql);
		$sql = "INSERT INTO `vtiger_cron_task` (`name`, `handler_file`, `frequency`, `laststart`, `lastend`, `status`, `module`, `sequence`, `description`) VALUES
			('Schedule RSNImport', 'cron/modules/RSNImport/ScheduledImport.service', 900, 0, 0, 1, 'RSNImport', 7, 'Recommended frequency for RSNImport is 15 mins');";
		$db->pquery($sql);
	}

	static function enableScheduleTask() {
		$db = PearDatabase::getInstance();
		$sql = "UPDATE `vtiger_cron_task`
			SET status = 1
			WHERE name = 'Schedule RSNImport'";
		$db->pquery($sql);
	}

	static function disableScheduleTask() {
		$db = PearDatabase::getInstance();
		$sql = "UPDATE `vtiger_cron_task`
			SET status = 0
			WHERE name = 'Schedule RSNImport'";
		$db->pquery($sql);
	}
}
