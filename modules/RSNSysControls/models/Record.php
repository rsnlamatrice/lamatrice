<?php
/*+***********************************************************************************
 * ED150817
 *
 * Attention, dans le champ query, l'initialisation remplace < et > par &lt; et &gt;, ainsi que &quot;
 *************************************************************************************/

/**
 * Vtiger Entity Record Model Class
 */
class RSNSysControls_Record_Model extends Vtiger_Record_Model {
	
	public static function getDefautEmail(){
		global $HELPDESK_SUPPORT_EMAIL_ID;
		return $HELPDESK_SUPPORT_EMAIL_ID;
	}
	
	public function getResultsUrl(){
		global $site_URL;
		return $site_URL."index.php"
			."?module=".$this->getModuleName()
			."&view=Detail"
			."&relatedModule=".$this->get('relatedmodule')
			."&record=".$this->getId()
			."&mode=showSysControlsResult";
	}
	
	/** ED150609
	 * return SELECT COUNT(*) query
	 */
	public function getSysControlCountQuery(){
		return 'SELECT COUNT(*) FROM (' . $this->getQueryFieldValue() . ') _sysctrlqueryforcount_';
	}
	
	public function getQueryFieldValue(){
		$ctrlQuery = decode_html(from_html($this->get('query')));
		
		return $ctrlQuery;
		
	}
	
	public function getAssignedUser(){
		$user = Vtiger_Cache::get('assigned_user', $this->get('assigned_user_id'));
		if(!$user){
			$user = Vtiger_Record_Model::getInstanceById($this->get('assigned_user_id'), 'Users');
			if(!$user){
				$user = Vtiger_Record_Model::getInstanceById(1, 'Users');
			}
			Vtiger_Cache::set('assigned_user', $this->get('assigned_user_id'), $user);
		}
		
		return $user;
	}
	
	public function getDestinationEmails(){
		$emails = array(self::getDefautEmail());
		if($GLOBALS['dev_title'])
			return $emails;
		$strEmails = $this->get('destinationemails');
		if($strEmails)
			foreach(preg_split('/[,;]/', $strEmails) as $email)
				$emails[$email] = $email;
		return $emails;
	}
}
