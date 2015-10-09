<?php
/*+**********************************************************************************
 * ED151008
 ************************************************************************************/

require_once 'vtlib/Vtiger/Utils.php';
require_once 'data/CRMEntity.php';
require_once 'include/QueryGenerator/QueryGenerator.php';

class Vtiger_FindDuplicate_Action extends Vtiger_Action_Controller {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('runScheduledSearch');
	}
	public function process(Vtiger_Request $request) {
		$mode = $request->get('mode');
		if(!empty($mode)) {
			$this->invokeExposedMethod($mode, $request);
			return;
		}
	}

	/* ED151008
	 * run by cron
	 */ 
	public function runScheduledSearch(){
		$moduleNames = Vtiger_FindDuplicate_Model::getScheduledSearchModules();
		foreach($moduleNames as $moduleName){
			$dataModelInstance = Vtiger_FindDuplicate_Model::getInstance($moduleName);
			$dataModelInstance->runScheduledSearch();
		}
	}
}