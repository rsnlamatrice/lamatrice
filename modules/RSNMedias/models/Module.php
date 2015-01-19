<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * ************************************************************************************/

class RSNMedias_Module_Model extends Vtiger_Module_Model {
	

	/**
	 * Function to get relation query for particular module with function name
	 * @param <record> $recordId
	 * @param <String> $functionName
	 * @param Vtiger_Module_Model $relatedModule
	 * @return <String>
	 */
	public function getRelationQuery($recordId, $functionName, $relatedModule) {
		if ($functionName === 'get_rsnmediacontacts') {
			//$query = parent::getRelationQuery($recordId, $functionName, $relatedModule);
			$query = "SELECT vtiger_crmentity.crmid, vtiger_rsnmediacontacts.nom
			, vtiger_rsnmediacontacts.rsntypescontactmedia
			, vtiger_rsnmediacontacts.rsnthematiques
			, vtiger_rsnmediacontacts.rsnmediaid
			, vtiger_crmentity.createdtime
			FROM vtiger_rsnmediacontacts
			INNER JOIN vtiger_crmentity
				on vtiger_crmentity.crmid = vtiger_rsnmediacontacts.rsnmediacontactsid
			INNER JOIN vtiger_rsnmediacontactscf
				ON vtiger_rsnmediacontacts.rsnmediacontactsid = vtiger_rsnmediacontactscf.rsnmediacontactsid
			LEFT JOIN vtiger_groups
				ON vtiger_groups.groupid=vtiger_crmentity.smownerid
			LEFT JOIN vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid
			WHERE (vtiger_rsnmediacontacts.rsnmediaid=" . $recordId . "
				OR vtiger_crmentity.crmid IN (
					SELECT vtiger_rsnmediarelations.mediacontactid
					FROM vtiger_rsnmediarelations
					WHERE vtiger_rsnmediarelations.rsnmediaid=" . $recordId . "
				)
				OR vtiger_crmentity.crmid IN (
					SELECT vtiger_crmentityrel.relcrmid
					FROM vtiger_crmentityrel
					WHERE module = 'RSNMedias'
					AND vtiger_crmentityrel.crmid=" . $recordId . "
				)
				OR vtiger_crmentity.crmid IN (
					SELECT vtiger_crmentityrel.crmid
					FROM vtiger_crmentityrel
					WHERE relmodule = 'RSNMedias'
					AND vtiger_crmentityrel.relcrmid=" . $recordId . "
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

}