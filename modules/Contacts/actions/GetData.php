<?php
/*+***********************************************************************************
 * ED150813
 *************************************************************************************/

class Contacts_GetData_Action extends Vtiger_GetData_Action {

	function __construct() {
		parent::__construct();
		$this->exposeMethod('assignRelatedEntities');
		$this->exposeMethod('unassignRelatedEntities');
		$this->exposeMethod('showModuleInfosForCopy');
	}
	
	public function process(Vtiger_Request $request) {
		$record = $request->get('record');
		if(!is_numeric($record)){
			global $adb;
			$query = 'SELECT crmid
				FROM vtiger_crmentity
				JOIN vtiger_contactdetails
					ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid
				WHERE vtiger_crmentity.deleted = 0
				AND vtiger_contactdetails.contact_no = ?
				LIMIT 1';
			
			$res = $adb->pquery($query, array($record));
			//var_dump($query, $record, $adb->query_result($res, 0, 'crmid'), $res);
			//die();
			if(!$adb->query_result($res, 0, 'crmid'))
				$request->set('record', -1); //TODO throw error
			else
				$request->set('record', $adb->query_result($res, 0, 'crmid'));
		}
		parent::process($request);
	}
}
