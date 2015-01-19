<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class RSNContactsPanels_Relation_Model extends Vtiger_Relation_Model{

	
	public function deleteRelation($sourceRecordId, $relatedRecordId){
		if(parent::deleteRelation($sourceRecordId, $relatedRecordId))
			return $this->deleteOrphans($sourceRecordId);
		return false;
	}
    
	
	/**
	 * Supprime les variables qui ne plus liees
	 */
	public function deleteOrphans($parentRecordId){
		
		$db = PearDatabase::getInstance();

		$query = 'UPDATE vtiger_crmentity
			JOIN vtiger_rsnpanelsvariables
				ON vtiger_crmentity.crmid = vtiger_rsnpanelsvariables.rsnpanelsvariablesid
			LEFT JOIN vtiger_crmentityrel
				ON vtiger_crmentityrel.crmid = vtiger_rsnpanelsvariables.rsnpanelsvariablesid
				OR vtiger_crmentityrel.relcrmid = vtiger_rsnpanelsvariables.rsnpanelsvariablesid
			SET vtiger_crmentity.deleted = 1
			WHERE vtiger_crmentityrel.crmid IS NULL
			AND vtiger_crmentity.deleted = 0';
		$params = array();

		//Remove the ordering for now to improve the speed
		//$query .= ' ORDER BY createdtime DESC';

		$result = $db->pquery($query, $params);
		
		return is_object($result);
	}
	
}
