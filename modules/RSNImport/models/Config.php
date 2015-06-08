<?php

class RSNImport_Config_Model extends Vtiger_Base_Model {

	function __construct() {
		$ImportConfig = array(
			'importTypes' => array(
								'csv' => array('reader' => 'RSNImport_CSVFileReader_Reader', 'classpath' => 'modules/RSNImport/readers/CSVFileReader.php'),
								'default' => array('reader' => 'RSNImport_FileReader_Reader', 'classpath' => 'modules/RSNImport/readers/FileReader.php')
							),

			'userImportTablePrefix' => 'vtiger_import_',
			// Individual batch limit - Specified number of records will be imported at one shot and the cycle will repeat till all records are imported
			'importBatchLimit' => '1000',
			// Threshold record limit for immediate import. If record count is more than this, then the import is scheduled through cron job
			'immediateImportLimit' => '1000',
		);

		$this->setData($ImportConfig);
	}
}