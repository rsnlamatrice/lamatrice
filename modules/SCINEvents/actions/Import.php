<?php
/*+***********************************************************************************
 * ED150707
 * 
 ************************************************************************************ */

require_once 'include/Webservices/Create.php';
require_once 'include/Webservices/Update.php';
require_once 'include/Webservices/Delete.php';
require_once 'include/Webservices/Revise.php';
require_once 'include/Webservices/Retrieve.php';
require_once 'include/Webservices/DataTransform.php';
require_once 'vtlib/Vtiger/Utils.php';
require_once 'data/CRMEntity.php';
require_once 'include/QueryGenerator/QueryGenerator.php';
require_once 'vtlib/Vtiger/Mailer.php';
require_once 'modules/Vtiger/helpers/Util.php';
require_once 'includes/runtime/LanguageHandler.php';

require_once 'include/simple_html_dom.php';
require_once dirname(__FILE__) . '/ImportFromEDF.php';

define('ADMIN_USERID', '1');
define('ASSIGNEDTO_ALL', '7');

class SCINEvents_Import_Action extends Vtiger_Action_Controller {
	
	public function __construct(){
		$this->exposeMethod('runScheduledImport');
	}
	
	public function checkPermission(){
		return true;
	}
	
	public function process (Vtiger_Request $request){
		if($request->get('mode'))
			return $this->invokeExposedMethod($request->get('mode'), $request);
		return true;
	}
	
	/**
	 * @test : url : ?module=SCINEvents&action=Import&mode=runScheduledImport&installationId=0
	 * @param Vtiger_Request $request (optional)
	 */
	public function runScheduledImport($request = false) {
		global $current_user, $log;
		$log->debug('IN runScheduledImport');
		/*$current_user = new Users();
		$current_user->id = ADMIN_USERID;
		$current_user->retrieve_entity_info(ADMIN_USERID, 'Users');*/
		
		if($request)
			$installationUniqueId = $request->get('installationId');
		
		$scheduledImports = SCINSources::getSources($installationUniqueId);
		
		$log->debug('$scheduledImports = ' . print_r($scheduledImports, true));
		
		foreach ($scheduledImports as $scheduledId => $importDataController) {
			
			$log->debug('$scheduledId = ' . print_r($scheduledId, true));
			if(!$importDataController->initializeImport()) { continue; }
			$log->debug('importData');
			$importDataController->importData();
			$log->debug('finishImport');
			$importDataController->finishImport();
		}
		$log->debug('OUT runScheduledImport');
	}
}

// Sources d'import. Voir classes héritières.
class SCINSources {

	var $lastSCINEvent;

	// abstract
	public function __construct($scinInstallation) {
		$this->scinInstallation = $scinInstallation;
	}

	/**
	 * Retourne un tableau des sources x installations
	 * @param $installationUniqueId (optionel) : identifiant de la seule installation traitée
	 */
	public static function getSources($installationUniqueId = false) {
		$sources = array();
		$sources = array_merge($sources, SCINSourceEDF::getInstances($installationUniqueId));
		//$sources = array_merge($sources, SCINSourceAREVA::getInstances($installationUniqueId));
		//$sources = array_merge($sources, SCINSourceASN::getInstances($installationUniqueId));
		return $sources;
	}
	
	//Retourne un tableau d'instance d'installations concernées
	protected function getInstallations($pageCodeField = FALSE) {
		$adb = PearDatabase::getInstance();
		$sql = 'SELECT crmid
			FROM vtiger_scininstallations
			JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_scininstallations.scininstallationsid
			WHERE vtiger_crmentity.deleted = 0
		';
		if($pageCodeField)
			$sql .= ' AND IFNULL(vtiger_scininstallations.' . $pageCodeField . ', \'\') <> \'\'';
		$result = $adb->query($sql);
		$numberOfRecords = $adb->num_rows($result);
		$scinInstallations = array();
		
		for ($i = 0; $i < $numberOfRecords; ++$i) {
			$id = $adb->query_result($result, $i, 0);
			$scinInstallations[$id] = Vtiger_Record_Model::getInstanceById($id, 'SCINInstallations');
		}
		return $scinInstallations;
	}
	
	// Retourne le dernier événement de l'installation créé via la source
	protected function getLastSCINEvent(){
		
		if(!empty($this->lastSCINEvent))
			return $this->lastSCINEvent;
		
		$adb = PearDatabase::getInstance();
		$sql = 'SELECT crmid
			FROM vtiger_scinevents
			JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_scinevents.scineventsid
			WHERE vtiger_crmentity.deleted = 0
			AND scinsource = ?
			AND scininstallationsid = ?
			ORDER BY dateevent DESC
			LIMIT 1
		';
		$result = $adb->pquery($sql, array($this->getSourceName(), $this->scinInstallation->getId()));
		if(!$result)
			$adb->echoError();
		if($adb->num_rows($result) === 0)
			return false;
		$id = $adb->query_result($result, 0, 0);
		$this->lastSCINEvent = Vtiger_Record_Model::getInstanceById($id, 'SCINEvents');
		return $this->lastSCINEvent;
	}

	//abstract
	public static function getSourceName(){
	}
	public static function getPageCodeField(){
	}
	
	public function initializeImport(){
		return true;
	}

	public function importData(){
		return false;
	}

	public function finishImport(){
		return true;
	}
	
	public static function url_get_contents($url){
		
		$ctx = stream_context_create(array( 
			'http' => array( 
				'method'=>"GET", 
				'header'=>"Content-Type: text/html; charset=utf-8",
				'timeout' => 1,
				)
			)
		); 
		/*$data = @file_get_contents ($url, 0, $ctx);
		echo '<pre>' . htmlentities($data) . '</pre>';*/
		//$data = @file_get_contents ('c:\\temp\\edf.txt');
		//$data = file_get_html('c:\\temp\\edf.txt');
		$data = file_get_html($url);
		if(self::isValidPageData($data))
			return $data;
		var_dump($url, 'Aucun résultat');
		return false;
	}
	
	public static function isValidPageData($data){
		//echo '<pre>' . htmlentities($data) . '</pre>';
		return $data;
	}

}

?>
