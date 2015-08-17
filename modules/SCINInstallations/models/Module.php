<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class SCINInstallations_Module_Model extends Vtiger_Module_Model{

	/**
	 * Function to check whether the entity has an quick create menu
	 * @return <Boolean> true/false
	 * ED141024
	 */
	public function isQuickCreateMenuVisible() {
		return false ;
	}
	
	/** ED150619
	 * Function to get relation query for particular module with function name
	 * Similar to getRelationQuery but overridable.
	 * @param <record> $recordId
	 * @param <String> $functionName
	 * @param Vtiger_Module_Model $relatedModule
	 * @return <String>
	 */
	public function getRelationCounterQuery($recordId, $functionName, $relatedModule) {
		
		$relationQuery = $this->getRelationQuery($recordId, $functionName, $relatedModule);

		switch($relatedModule->getName()){
		 case 'SCINEvents':
			//MySQL ne tolère la duplication de nom de champ (ici 'status') dans une sous-requête, alors qu'il le tolère en requête principale
			$relationQuery = str_replace('vtiger_crmentity.*, vtiger_scinevents.*'
				,'1'
				,$relationQuery);
			break;
		}
		
		return 'SELECT COUNT(*) quantity'
			. ', \'' . $relatedModule->getName() . '\' module'
			. ', \'' . $functionName . '\' functionName'
			. ' FROM (' . 
					$relationQuery .
			') `' . $relatedModule->getName() . '_' . $functionName . '_query`';
	}
}
?>
