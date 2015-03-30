<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ************************************************************************************/

class RSNMediaContacts_Module_Model extends Vtiger_Module_Model {
	

	/**
	 * Function to get relation query for particular module with function name
	 * @param <record> $recordId
	 * @param <String> $functionName
	 * @param Vtiger_Module_Model $relatedModule
	 * @return <String>
	 */
	public function getRelationQuery($recordId, $functionName, $relatedModule) {
		if ($functionName === 'get_rsnmedias') {
			//$query = parent::getRelationQuery($recordId, $functionName, $relatedModule);
			$query = "SELECT vtiger_crmentity.crmid
			, vtiger_rsnmedias.*
			, vtiger_crmentity.createdtime
			FROM vtiger_rsnmedias
			INNER JOIN vtiger_crmentity
				on vtiger_crmentity.crmid = vtiger_rsnmedias.rsnmediasid
			INNER JOIN vtiger_rsnmediascf
				ON vtiger_rsnmedias.rsnmediasid = vtiger_rsnmediascf.rsnmediasid
			LEFT JOIN vtiger_groups
				ON vtiger_groups.groupid=vtiger_crmentity.smownerid
			LEFT JOIN vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid
			WHERE (vtiger_crmentity.crmid IN (
					SELECT vtiger_rsnmediacontacts.rsnmediaid
					FROM vtiger_rsnmediacontacts
					WHERE vtiger_rsnmediacontacts.rsnmediacontactsid=" . $recordId . "
				)
				OR vtiger_crmentity.crmid IN (
					SELECT vtiger_crmentityrel.relcrmid
					FROM vtiger_crmentityrel
					WHERE vtiger_crmentityrel.crmid=" . $recordId . "
					OR vtiger_crmentityrel.relcrmid=" . $recordId . "
				)
			)
			AND vtiger_crmentity.deleted=0";
			//$query = parent::getRelationQuery($recordId, $functionName, $relatedModule);
			//print_r($query);
		
		} else {
			$query = parent::getRelationQuery($recordId, $functionName, $relatedModule);
						
		}

		return $query;
	}
	
	/**
	 * Function to get a Vtiger Record Model instance from an array of key-value mapping
	 * @param <Array> $valueArray
	 * @return Vtiger_Record_Model or Module Specific Record Model instance
	 *
	 * ED150325 adds column 'satisfaction' calculated at 
	 */
	public function getRecordFromArray($valueArray, $rawData=false) {
		$modelClassName = Vtiger_Loader::getComponentClassName('Model', 'Record', $this->get('name'));
		$recordInstance = new $modelClassName();
		if(isset($rawData['satisfaction']))
			$valueArray['satisfaction'] = $rawData['satisfaction'];
		return $recordInstance->setData($valueArray)->setModuleFromInstance($this)->setRawData($rawData);
	}
}