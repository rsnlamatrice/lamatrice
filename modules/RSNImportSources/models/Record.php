<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Vtiger Entity Record Model Class
 */
class RSNImportSources_Record_Model extends Vtiger_Record_Model {

	/** ED150827
	 * Static Function to get the instance of the Vtiger Record Model given the className and the module name
	 * @param <String> $className
	 * @param <String> $moduleName
	 * @return Vtiger_Record_Model or Module Specific Record Model instance
	 */
	public static function getInstanceByClassName($className, $module=null) {
		$query = 'SELECT vtiger_crmentity.crmid
		FROM vtiger_crmentity
		JOIN vtiger_rsnimportsources
			ON vtiger_crmentity.crmid
		WHERE vtiger_crmentity.deleted = 0
		AND class = ?';
		global $adb;
		$result = $adb->pquery($query, array($className));
		if(!$result){
			$adb->echoError($query);
			echo_callstack();
			return;
		}
		$id = $adb->query_result($result, 0);
		if($id)
			return self::getInstanceById($id, 'RSNImportSources');
	}
	
	/** ED150827
	 * Static Function to get the instance of the Vtiger Record Model given the id of a queue record
	 * @param <Number> $queueId (field importid)
	 * @param <String> $moduleName
	 * @return Vtiger_Record_Model or Module Specific Record Model instance
	 */
	public static function getInstanceByQueueId($queueId) {
		$query = 'SELECT vtiger_crmentity.crmid
		FROM vtiger_crmentity
		JOIN vtiger_rsnimportsources
			ON vtiger_crmentity.crmid = vtiger_rsnimportsources.rsnimportsourcesid
		JOIN vtiger_rsnimport_queue
			ON vtiger_rsnimport_queue.importsourceclass = vtiger_rsnimportsources.class
		WHERE vtiger_crmentity.deleted = 0
		AND vtiger_rsnimport_queue.importid = ?';
		global $adb;
		$result = $adb->pquery($query, array($queueId));
		if(!$result){
			$adb->echoError($query);
			echo_callstack();
			return;
		}
		$id = $adb->query_result($result, 0);
		if($id)
			return self::getInstanceById($id, 'RSNImportSources');
	}
	
	
	/**
	 * Method to get an instance of the import controller. It use the ImportSource parameter to retreive the name of the import class.
	 * Vtiger_Request $request est initialisé sur la base des données du champ autosourcedata
	 * @return Import - An instance of the import controller, or null.
	 */
	function getImportController($request = false) {
		
		$className = $this->get('class');
		
		if(!$request)
			$request = $this->getRequest();
			
		if(!$className){
			$className = $request->get('ImportSource');
		}
		
		if ($className) {
			$importClass = RSNImportSources_Utils_Helper::getClassFromName($className);
			$user = Users_Record_Model::getCurrentUserModel();
			$importController = new $importClass($request, $user);
			if($importController){
				$importController->recordModel = $this;
			}
			return $importController;
		}

		return null;
	}
	
	/* Retourne un object Vtiger_Request initialisé sur la base des données du champ autosourcedata */
	function getRequest(){
		//Conversion
		$params = array();
		
		if($this->get('autoenabled') && $this->get('autosourcedata')){
			$params_src = array();
			preg_match_all('/(^|[\r\n])\s*(?<param>\w+)\s*=\s*(?<value>[^()\r\n]*)/', $this->get('autosourcedata'), $params_src);
			//var_dump($params_src);
			for($i = 0; $i < count($params_src); $i++)
				if($params_src['param'][$i]
				&& strpos($params_src['param'][$i][0], ';/') === false)
					$params[$params_src['param'][$i]] = $params_src['value'][$i];
		}
		
		if(!array_key_exists('ImportSource', $params))
			$params['ImportSource'] = $this->get('class');
		return new Vtiger_Request($params, $params, false);
	}
}
