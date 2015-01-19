<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Settings_Folders_Save_Action extends Settings_Vtiger_Index_Action {

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule(false);
		$foldersModuleModel = Settings_Vtiger_Module_Model::getInstance($moduleName);

		$deleted = $request->get('deleted');
		$changed = $request->get('changed');
		$folderid = $request->get('folderid');
		$foldername = $request->get('foldername');
		$description = $request->get('description');
		$sequence = $request->get('sequence');
		$uicolor = $request->get('uicolor');
		
		$folders = array();
		for($i = 0; $i < count($folderid); $i++){
			if($deleted[$i] || $changed[$i]){
				$folders[] = array(
					'folderid' => $folderid[$i],
					'deleted' => $deleted[$i],
					'changed' => $changed[$i],
					'foldername' => str_replace('&gt;', '>', $foldername[$i]),
					'description' => str_replace('&gt;', '>', str_replace('&lt;', '<', $description[$i])),
					'sequence' => $sequence[$i],
					'uicolor' => $uicolor[$i],
				);
			}
		}
		
		$foldersModuleModel->saveFolders($folders);
		
		$loadUrl = $foldersModuleModel->getIndexViewUrl();
		header("Location: $loadUrl");
	}

}
