<?php

class RSNStatisticsFields_Record_Model extends Vtiger_Record_Model {
	
	/**
	 * Contrôles que les champs sont correctement paramétrés par rapport à leur requête
	 *
	 */
	public function getErrors(){
		$queryRecordModel = $this->getQueryRecordModel();
		if(!$queryRecordModel)
			return 'Aucune requête n\'est pas associée';
		$resultFields = $queryRecordModel->getQueryResultFieldsDefinition();
		if( ! array_key_exists($this->get('uniquecode'), $resultFields) ){
			return 'Le champ `'.$this->get('uniquecode').'` n\'existe pas en retour de la requête "'.$queryRecordModel->getName().'".'
				.' Valeurs possibles : '. implode(', ', array_keys($resultFields));
		}
		if( ! array_key_exists('crmid', $resultFields) ){
			return 'Le champ `crmid` n\'existe pas en retour de la requête "'.$queryRecordModel->getName().'".'
				.' Inclure systématiquement `vtiger_crmentity`.`crmid` ou `vtiger_contactdetails`.`contactid` AS `crmid` dans les requêtes.';
		}
	}
	
	public function getQueryRecordModel(){
		if($this->get('rsnsqlqueriesid'))
			return Vtiger_Record_Model::getInstanceById($this->get('rsnsqlqueriesid'), 'RSNSQLQueries');
	}
}
