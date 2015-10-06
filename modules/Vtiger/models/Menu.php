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
 * Vtiger Menu Model Class
 */
class Vtiger_Menu_Model extends Vtiger_Module_Model {

    /**
     * Static Function to get all the accessible menu models with/without ordering them by sequence
     * @param <Boolean> $sequenced - true/false
     * @param <String> $roleid
     * @return <Array> - List of Vtiger_Menu_Model instances
     */
    public static function getAll($sequenced = false, $roleid = null) {
        $currentUser = Users_Record_Model::getCurrentUserModel();
        $userPrivModel = Users_Privileges_Model::getCurrentUserPrivilegesModel();
        $restrictedModulesList = array('Emails', 'ProjectMilestone', 'ProjectTask', 'ModComments', 'Rss', 'Portal',
					'Integration', 'PBXManager', 'Dashboard', 'Home', 'vtmessages', 'vttwitter', 'RsnPrelevements');
	/*echo __FILE__."<br><br><br><br><br>";
	var_dump($roleid);
	echo_callstack();
$db = PearDatabase::getInstance();
$db->setDebug(true);*/
        $allModules = parent::getAll(array('0','2'), array(), $roleid);
//$db->setDebug(false);
	$menuModels = array();
        $moduleSeqs = Array();
        $moduleNonSeqs = Array();
	//Attention, doublon sur tabSequence qui fait disparaitre des modules
        foreach($allModules as $module){
            if($module->get('tabsequence') != -1){
		$tabsequence = $module->get('tabsequence');
		while(isset($moduleSeqs[$tabsequence]))
		    $tabsequence += count($allModules);
                $moduleSeqs[$tabsequence] = $module;
            } else {
                $moduleNonSeqs[] = $module;
            }
        }
	ksort($moduleSeqs);
        $modules = array_merge($moduleSeqs, $moduleNonSeqs);

	foreach($modules as $module) {
            if (($userPrivModel->isAdminUser() ||
                    $userPrivModel->hasGlobalReadPermission() ||
                    $userPrivModel->hasModulePermission($module->getId()))
		&& !in_array($module->getName(), $restrictedModulesList) && $module->get('parent') != '') {
                    $menuModels[$module->getName()] = $module;
            }
        }

        return $menuModels;
    }

}
