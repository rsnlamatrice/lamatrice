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

	/** ED150609
	 * return SELECT COUNT(*) query
	 */
	public function getSysControlCountQuery(){
		return 'SELECT COUNT(*) FROM (' . $this->getQueryFieldValue() . ') _sysctrlqueryforcount_';
	}
	
	//L'initialisation remplace < et > par &lt; et &gt;
	//TODO éviter ça
	public function getQueryFieldValue(){
		$ctrlQuery = from_html($this->get('query'));
		
		//TODO
		if(preg_match('/\&.*\;/', $ctrlQuery))
			$ctrlQuery = str_replace('&gt;', '>',
					str_replace('&lt;', '<',
					str_replace('&quot;', '"',
					$ctrlQuery)));
		return $ctrlQuery;
		
	}
	
	public function getAssignedUser(){
		$user = Vtiger_Cache('assigned_user', $this->get('assigned_user_id'));
		if(!$user){
			$user = Vtiger_Record_Model::getInstanceById($this->get('assigned_user_id'), 'Users');
			if(!$user){
				$user = Vtiger_Record_Model::getInstanceById(1, 'Users');
			}
			Vtiger_Cache('assigned_user', $this->get('assigned_user_id'), $user);
		}
		
		return $user;
	}
}
