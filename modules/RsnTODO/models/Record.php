<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class RsnTODO_Record_Model extends Vtiger_Record_Model {
	
	/**
	 * Function to set the entity instance of the record
	 * @param CRMEntity $entity
	 * @return Vtiger_Record_Model instance
	 *
	 * ED141004
	 * Affectation des valeurs par défaut avant l'affichage d'un nouvel enregistrement
	 */
	public function setEntity($entity) {
		parent::setEntity($entity);
		
		/* nouvel enregistrement */
		if(empty($this->get('id'))){
			/* valeur par défaut du champ create_user_id */
			global $current_user;
			$this->set('create_user_id', $current_user->id);
		}
		
		return $this;
	}
	
	/**
	 * Function to save the current Record Model
	 * ED141004
	 * Contrôle des valeurs de champs à l'enregistrement
	 */
	public function save() {
		/* sécurité en double */
		if(empty($this->get('create_user_id'))){
			global $current_user;
			$this->set('create_user_id', $current_user->id);
		}
		return parent::save();
	}
}

