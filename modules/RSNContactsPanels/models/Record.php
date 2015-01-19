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
 * Vtiger Entity Record Model Class
 */
class RSNContactsPanels_Record_Model extends Vtiger_Record_Model {

	/**
	 * retourne la requête d'après le champ query
	 *
	 */
	public function getPanelQuery(){
		return str_replace(array('&lt;', '&gt;','&#039;')
				   , array('<', '>', "'")
				   , $this->get('query'));
	}
	
	
	
	/**
	 * Traitement des variables imbriques dans la requte
	 * 	transforme les lments de la forme [[Title | field/type | defaultValue ]]
	 * 	en RSNPanelsVariables
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function getVariablesFromQuery($query) {
		$strVariables = array();
		if(preg_match_all('/\[\[(?<var>(?<!\]\]).*?(?=\]\]))\]\]/', $query, $strVariables)){
			$variablesData = array();
			$varIndex = 0;
			foreach($strVariables['var'] as $strVar){
				$strParams = explode('|', $strVar);
				for($paramIndex = 0; $paramIndex < count($strParams); $paramIndex++){
					$strParams[$paramIndex] = trim($strParams[$paramIndex]);
				}
				$variablesData[] = array(
					'name' => $strParams[0],
					'field' => $strParams[1],
					'value' => $strParams[2],
					'sequence' => $varIndex++,
				);
			}
			
			return $variablesData;
		}
		return array();
		
	}
}
