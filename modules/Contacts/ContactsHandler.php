<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class ContactsHandler extends VTEventHandler {

	function handleEvent($eventName, $entityData) {
		global $log, $adb;

		if($eventName == 'vtiger.entity.beforesave') {
			$moduleName = $entityData->getModuleName();
			if ($moduleName == 'Contacts') {
				if (!$entityData->get("manualgpscoordinates")) {
					$address = Contacts_Utils_Helper::getFormatedAddress($entityData);
					$GPSCoordinate = Contacts_Utils_Helper::getGPSCoordinate($address);
					
					if ($GPSCoordinate['status'] && ( !$GPSCoordinate["partial_match"] || !$entityData->get("latitude") || !$entityData->get("longitude"))) {
						$entityData->set("latitude", $GPSCoordinate["latitude"]);
						$entityData->set("longitude", $GPSCoordinate["longitude"]);
					}
				}
			}
		}
	}
}


function Contacts_sendCustomerPortalLoginDetails($entityData){
	$adb = PearDatabase::getInstance();
	$moduleName = $entityData->getModuleName();
	$wsId = $entityData->getId();
	$parts = explode('x', $wsId);
	$entityId = $parts[1];

	$email = $entityData->get('email');

	if ($entityData->get('portal') == 'on' || $entityData->get('portal') == '1') {
		$sql = "SELECT id, user_name, user_password, isactive FROM vtiger_portalinfo WHERE id=?";
		$result = $adb->pquery($sql, array($entityId));
		$insert = false;
		if($adb->num_rows($result) == 0){
			$insert = true;
		}else{
			$dbusername = $adb->query_result($result,0,'user_name');
			$isactive = $adb->query_result($result,0,'isactive');
			if($email == $dbusername && $isactive == 1 && !$entityData->isNew()){
				$update = false;
			} else if($entityData->get('portal') == 'on' ||  $entityData->get('portal') == '1'){
				$sql = "UPDATE vtiger_portalinfo SET user_name=?, isactive=1 WHERE id=?";
				$adb->pquery($sql, array($email, $entityId));
				$password = $adb->query_result($result,0,'user_password');
				$update = true;
			} else {
				$sql = "UPDATE vtiger_portalinfo SET user_name=?, isactive=? WHERE id=?";
				$adb->pquery($sql, array($email, 0, $entityId));
				$update = false;
			}
		}
		if($insert == true){
			$password = makeRandomPassword();
			$sql = "INSERT INTO vtiger_portalinfo(id,user_name,user_password,type,isactive) VALUES(?,?,?,?,?)";
			$params = array($entityId, $email, $password, 'C', 1);
			$adb->pquery($sql, $params);
		}

		if($insert == true || $update == true) {
			global $current_user,$HELPDESK_SUPPORT_EMAIL_ID, $HELPDESK_SUPPORT_NAME;
			require_once("modules/Emails/mail.php");
			$emailData = Contacts::getPortalEmailContents($entityData,$password,'LoginDetails');
			$subject = $emailData['subject'];
			$contents = $emailData['body'];
			send_mail('Contacts', $entityData->get('email'), $HELPDESK_SUPPORT_NAME, $HELPDESK_SUPPORT_EMAIL_ID, $subject, $contents,'','','','','',true);
		}
	} else {
		$sql = "UPDATE vtiger_portalinfo SET user_name=?,isactive=0 WHERE id=?";
		$adb->pquery($sql, array($email, $entityId));
	}
}

?>