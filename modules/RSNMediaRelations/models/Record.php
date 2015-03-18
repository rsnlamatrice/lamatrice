<?php
/*+***********************************************************************************
 * Prises de contact avec un journaliste de média
 *************************************************************************************/

class RSNMediaRelations_Record_Model extends Vtiger_Record_Model {

	
	/**
	 * ED141109
	 * getPicklistValuesDetails
	 */
	public function getPicklistValuesDetails($fieldname){
		switch($fieldname){
			case 'satisfaction':
				return array(
					'100' => array( 'label' => '', 'icon' => 'icon-rsn-small-smiley-100' ),
					'50' => array( 'label' => '', 'icon' => 'icon-rsn-small-smiley-50' ),
					'0' => array( 'label' => '', 'icon' => 'icon-rsn-small-smiley-0' ),
					'-50' => array( 'label' => '', 'icon' => 'icon-rsn-small-smiley--50' ),
					'-100' => array( 'label' => '', 'icon' => 'icon-rsn-small-smiley--100' ),
					
				);
			case 'initiateur':
				return array(
					'CONT' => array( 'label' => 'Elle/lui', 'icon' => 'ui-icon ui-icon-arrowthick-1-w' ),
					'WE' => array( 'label' => 'Nous', 'icon' => 'ui-icon ui-icon-arrowthick-1-e' ),
					'AR' => array( 'label' => 'A/R', 'icon' => 'ui-icon ui-icon-transferthick-e-w' ),
					' ' => array( 'label' => '', 'icon' => 'ui-icon ui-icon-help' ),
					
				);
			default:
				//die($fieldname);
				return array();
		}
	}
	
	/**
	 * Function to set the entity instance of the record
	 * @param CRMEntity $entity
	 * @return Vtiger_Record_Model instance
	 *
	 * ED141126
	 * Affectation des valeurs par défaut avant l'affichage d'un nouvel enregistrement
	 */
	public function setEntity($entity) {
		parent::setEntity($entity);
		
		/* nouvel enregistrement */
		if(empty($this->get('id'))){
			/* valeur par dÈfaut du champ create_user_id */
			global $current_user;
			$this->set('byuserid', $current_user->id);
			
			$this->set('daterelation', date('Y-m-d'));
		}
		
		return $this;
	}
}
