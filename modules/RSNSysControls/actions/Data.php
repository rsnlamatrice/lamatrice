<?php

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

class RSNSysControls_Data_Action extends Vtiger_Action_Controller {
	
	public function checkPermission(){
		return true;
	}
	
	public function process(Vtiger_Request $request) {
		if($request->get('mode') == 'runScheduledSysControls')
			self::runScheduledSysControls(false, true);
		return;
	}

	/**
	 * Method called by the cron when there is scheduled sys control.
	 *  It process to the import of all sys controls.
	 */
	public static function runScheduledSysControls($checkLastTestTime = true, $verbose = false) {
		//TODO email or log when schedule control is running and ended !!
		global $current_user;
		$scheduledSysControls = self::getScheduledSysControls($checkLastTestTime);
		$mailBody = "";
		$mailBodyPos = 0;
		foreach ($scheduledSysControls as $scheduledId => $sysControl) {
			self::executeSysControl($sysControl, $mailBody);
			if($verbose){
				echo '<h3>'.$sysControl->getName().'</h3>';
				if($mailBodyPos < strlen($mailBody)){
					echo '<pre>'.substr($mailBody, $mailBodyPos).'</pre>';
					$mailBodyPos = strlen($mailBody);
				}
			}
			if($checkLastTestTime){
				$sysControl	->set('mode', 'edit')
						->set('lasttest', date('Y-m-d H:i:s'))
						->save();
			}
		}
		
		if($mailBody && !$verbose){
			
			global $HELPDESK_SUPPORT_EMAIL_ID ;
			
			$server = $GLOBALS['dev_title'];
			if($server)
				$server = 'La Matrice - '.$server;
			else
				$server = 'La Matrice';
				
			$vtigerMailer = new Vtiger_Mailer();
			$vtigerMailer->initialize();
			$vtigerMailer->IsHTML(false);
			$vtigerMailer->AddAddress($HELPDESK_SUPPORT_EMAIL_ID, 'Administrateur'); //TODO
			$vtigerMailer->Subject ='['.$server.'] Requetes de controle : Alerte !';
			$vtigerMailer->Body = $mailBody;
			$vtigerMailer->Send();
			//tmp mail
			Vtiger_Mailer::dispatchQueue(null);
		}
	}
	
	static function getScheduledSysControls($checkLastTestTime = true){
		
		$db = PearDatabase::getInstance();

		$query = 'SELECT vtiger_crmentity.crmid
			FROM vtiger_rsnsyscontrols
			JOIN vtiger_crmentity
				ON vtiger_rsnsyscontrols.rsnsyscontrolsid = vtiger_crmentity.crmid
			WHERE vtiger_crmentity.deleted = 0
			AND vtiger_rsnsyscontrols.enabled = 1';
		if($checkLastTestTime)
			$query .= ' AND (vtiger_rsnsyscontrols.lasttest IS NULL
				OR (NOW() - vtiger_rsnsyscontrols.lasttest) > vtiger_rsnsyscontrols.testperiod / 24)';
		$params = array();
		$result = $db->pquery($query, $params);
		if(!$result){
			$db->echoError($query);
			return;
		}
		$noOfRecords = $db->num_rows($result);
		$scheduledRecords = array();
		for ($i = 0; $i < $noOfRecords; ++$i) {
			$rowData = $db->raw_query_result_rowdata($result, $i);
			$recordInstance = Vtiger_Record_Model::getInstanceById($rowData['crmid'], 'RSNSysControls');
			$scheduledRecords[$recordInstance->getId()] = $recordInstance;
		}
		return $scheduledRecords;
	}
	
	static function executeSysControl($sysControl, &$mailBody){		
		$db = PearDatabase::getInstance();
		
		$query = $sysControl->getSysControlCountQuery();
		
		$result = $db->query($query);
		if(!$result){
			$mailBody.=$db->echoError("
************
Erreur dans la requête de contrôle \"" . $sysControl->getName() . "\"
***********", true);
			return false;
		}
		
		$result = $db->query_result($result, 0);
		if($result > 0){
			$mailBody .= "
************
La requête de contrôle \"" . $sysControl->getName() . "\" retourne $result enregistrement(s).
***********";
		}
	}
}

?>