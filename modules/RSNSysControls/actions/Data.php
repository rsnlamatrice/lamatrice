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
		//un tableau par utilisateur concerné
		$mailBodies = array( 1 => "");
		$mailBodyPos = 0;
		$users = array();
		foreach ($scheduledSysControls as $scheduledId => $sysControl) {
			
			$user = $sysControl->getAssignedUser();
			$users[$user->getId()] = $user;
			
			self::executeSysControl($sysControl, $mailBodies);
			if($verbose){
				if($mailBodyPos < strlen($mailBodies[1])){
					echo '<h3>'.$sysControl->getName().'</h3>';
					echo '<pre>'.substr($mailBodies[1], $mailBodyPos).'</pre>';
					$mailBodyPos = strlen($mailBodies[1]);
				}
			}
			if($checkLastTestTime){
				$sysControl	->set('mode', 'edit')
						->set('lasttest', date('Y-m-d H:i:s'))
						->save();
			}
		}
		if($mailBodies[1] && !$verbose){
			
			global $HELPDESK_SUPPORT_EMAIL_ID ;
			
			$server = $GLOBALS['dev_title'];
			if($server)
				$server = 'La Matrice - '.$server;
			else
				$server = 'La Matrice';
			
			foreach($mailBodies as $userId => $mailBody){
				$user = $users[$userId];
				if($user->getId() == 1){
					$address = $HELPDESK_SUPPORT_EMAIL_ID;
					$destName = 'Administrateur';
				}
				else{
					$address = $user->get('email1');
					$destName = $user->get('user_name');
				}
				echo "
				Envoi d'un email à $address suite aux contrôles périodiques du système de données.
				";
				$vtigerMailer = new Vtiger_Mailer();
				$vtigerMailer->initialize();
				$vtigerMailer->IsHTML(false);
				$vtigerMailer->AddAddress($address, $destName);
				$vtigerMailer->Subject ='['.$server.'] Requetes de controle : Alerte !';
				$vtigerMailer->Body = "Bonjour $destName, \r\n".$mailBody;
				$vtigerMailer->Send();
			}
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
	
	/**
	 * Exécute la requête en mode comptage
	 * @param $sysControl
	 * @param &$mailBodies : tableau du contenu de mail par utilisateur assigné à la requête
	 */
	static function executeSysControl($sysControl, &$mailBodies){		
		$mailBody = '';
		
		$db = PearDatabase::getInstance();
		
		$query = $sysControl->getSysControlCountQuery();
		
		$result = $db->query($query);
		if(!$result){
			$mailBody.=$db->echoError("
************
Erreur dans la requête de contrôle \"" . $sysControl->getName() . "\"
***********", true);
		}
		else {
			$result = $db->query_result($result, 0);
			if($result > 0){
				$mailBody .= "
************
La requête de contrôle \"" . $sysControl->getName() . "\" retourne $result enregistrement(s).
***********";
			}
		}
		if($mailBody){
			$user = $sysControl->getAssignedUser();
			if(!$mailBodies[$user->getId()])
				$mailBodies[$user->getId()] = $mailBody;
			else{
				$mailBodies[$user->getId()] .= $mailBody;
			}
			if($user->getId() != 1)
				$mailBodies[1] .= $mailBody
					. "\r\n(suivi par ". $user->getName() . ")\r\n\r\n";
		}
	}
}

?>