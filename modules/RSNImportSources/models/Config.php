<?php

class RSNImportSources_Config_Model extends Vtiger_Base_Model {

	function __construct() {
		$ImportConfig = array(
			'importTypes' => array(
				'csv' => array('reader' => 'RSNImportSources_CSVFileReader_Reader', 'classpath' => 'modules/RSNImportSources/readers/CSVFileReader.php'),
				'default' => array('reader' => 'RSNImportSources_FileReader_Reader', 'classpath' => 'modules/RSNImportSources/readers/FileReader.php')
			),

			'userImportTablePrefix' => 'vtiger_import_',
			// Individual batch limit - Specified number of records will be imported at one shot and the cycle will repeat till all records are imported
			'importBatchLimit' => '2000',
			// Threshold record limit for immediate import. If record count is more than this, then the import is scheduled through cron job
			'immediateImportLimit' => '200',
		);

		$this->setData($ImportConfig);
	}
}