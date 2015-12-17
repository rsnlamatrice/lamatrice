<?php
/*+***********************************************************************************
 * ED150507
 *
 * Le champ sourceid doit être défini avec 
 * UPDATE `vtiger_field` SET `summaryfield` = '1' WHERE `vtiger_field`.`fieldid` = 1087;
 * 
 *************************************************************************************/

//Valeurs exactes des items de la picklist rsnabotype
define('RSNABOREVUES_TYPE_ABONNE', 'Abonné(e)');
define('RSNABOREVUES_TYPE_ABO_PAYANT', 'Abonné(e) payant');
define('RSNABOREVUES_TYPE_ABO_A_VIE', 'Abonné(e) à vie');
define('RSNABOREVUES_TYPE_ABO_ADHERENT', 'Abonné(e) comme adhérent(e)');
define('RSNABOREVUES_TYPE_NE_PAS_ABONNER', 'Ne pas abonner');
define('RSNABOREVUES_TYPE_NON_ABONNE', 'Non abonné');
define('RSNABOREVUES_TYPE_NUM_DECOUVERTE', 'Un n° découverte');
define('RSNABOREVUES_TYPE_NUM_MERCI', 'Un n° de remerciement');
define('RSNABOREVUES_TYPE_ABO_GROUPE', 'Abonnement groupé');
define('RSNABOREVUES_TYPE_ABO_PARRAINE', 'Abonné(e) parrainé(e)');


/**
 * RSNAboRevues Entity Record Model Class
 */
class RSNAboRevues_Record_Model extends Vtiger_Record_Model {

	/**
	 * Function to set the entity instance of the record
	 * @param CRMEntity $entity
	 * @return Vtiger_Record_Model instance
	 */
	public function setEntity($entity) {
		parent::setEntity($entity);
		if(!$this->getId())
			$this->setDebutAbo(date('Y-m-d'));
		return $this;
	}
	
	/**
	 * Function to test if is abonné
	 * @return <Boolean> - 
	 */
	public function isAbonne() {
		return $this->get('isabonne');
	}

	/**
	 * Function to set is abonné
	 * @param $isAbonne
	 * @return <Boolean> - 
	 */
	public function setAbonne($isAbonne) {
		return $this->set('isabonne', $isAbonne ? 1 : 0);
	}

	/**
	 * Function to test if abotype equals 'Abonné(e) à vie'
	 * @return <Boolean> - 
	 */
	public function isTypeAbonneAVie() {
		return $this->isTypeAbo(RSNABOREVUES_TYPE_ABO_A_VIE);
	}

	/**
	 * Function to test if abotype equals 'Abonnement groupé'
	 * @return <Boolean> - 
	 */
	public function isTypeAboGroupe() {
		return $this->isTypeAbo(RSNABOREVUES_TYPE_ABO_GROUPE);
	}
	
	/**
	 * Function to test if abotype equals 'Ne pas abonner'
	 * @return <Boolean> - 
	 */
	public function isTypeNePasAbonner() {
		return $this->isTypeAbo(RSNABOREVUES_TYPE_NE_PAS_ABONNER);
	}
	
	/**
	 * Function to test if abotype equals 'Revue découverte'
	 * @return <Boolean> - 
	 */
	public function isTypeDecouverte() {
		return $this->isTypeAbo(RSNABOREVUES_TYPE_NUM_DECOUVERTE);
	}
	
	/**
	 * Function to test if abotype equals 'Merci de votre soutien'
	 * @return <Boolean> - 
	 */
	public function isTypeMerciSoutien() {
		return $this->isTypeAbo(RSNABOREVUES_TYPE_NUM_MERCI);
	}
	
	/**
	 * Function to test if abotype equals a type
	 * @return <Boolean> - 
	 */
	public function isTypeAbo($abotype) {
		return $this->getTypeAbo() == $abotype
		    || $this->getTypeAbo() == to_html($abotype);
	}
	
	/**
	 * Function to get abotype
	 * @return <string> - 
	 */
	public function getTypeAbo() {
		return $this->get('rsnabotype');
	}
	
	/**
	 * Function to set abotype
	 * @return $this 
	 */
	public function setTypeAbo($abotype) {
		return $this->set('rsnabotype', $abotype);
	}
	
	/**
	 * Function to get start date
	 * @return <DateTime> - 
	 */
	public function getDebutAbo() {
		if(!$this->get('debutabo'))
			return false;
		return new DateTime($this->get('debutabo'));
	}
	/**
	 * Function to set start date
	 * @return $this 
	 */
	public function setDebutAbo($value) {
		return $this->set('debutabo', $value instanceOf DateTime ? $value->format('Y-m-d') : $value);
	}
	
	/**
	 * Function to get end date
	 * @return <DateTime> - 
	 */
	public function getFinAbo() {
		if(!$this->get('finabo'))
			return false;
		return new DateTime($this->get('finabo'));
	}
	/**
	 * Function to set end date
	 * @return $this 
	 */
	public function setFinAbo($value) {
		return $this->set('finabo', $value instanceOf DateTime ? $value->format('Y-m-d') : $value);
	}
	
	/**
	 * Function to get le nombre d'exemplaires
	 * @return <DateTime> - 
	 */
	public function getNbExemplaires() {
		return $this->get('nbexemplaires');
	}
	/**
	 * Function to set le nombre d'exemplaires
	 * @return $this 
	 */
	public function setNbExemplaires($value) {
		return $this->set('nbexemplaires', $value);
	}
	
	/**
	 * Retourne la date à partir de laquelle un nouvel abonnement peut être ajouté à la suite
	 * Teste que l'abonnement n'est pas en cours ou que le type permet de commencer un nouvel abonnement
	 * @return <DateTime> - 
	 */
	public function getStartDateOfNextAbo($prochaineRevue = false, $toDay = false) {
		if(!$prochaineRevue){
			$productModel = Vtiger_Module_Model::getInstance('Products');
			$prochaineRevue = $productModel->getProchaineRevue();
			if(!$prochaineRevue)
				return false;
		}
		if(!$toDay)
			$toDay = new DateTime();
		if(!$this->isAbonne()
		|| $this->isTypeAbonneAVie())
			return $toDay;
		switch($this->get('rsnabotype')){
		case RSNABOREVUES_TYPE_NE_PAS_ABONNER:
		case RSNABOREVUES_TYPE_NON_ABONNE:
		case RSNABOREVUES_TYPE_NUM_DECOUVERTE:
		case RSNABOREVUES_TYPE_NUM_MERCI:
			return $toDay;
		default:
			break;
		}
		if($this->getFinAbo() < $toDay)
			return $toDay;
		return $this->getFinAbo();
	}
	
	/**
	 * ED141005
	 * getPicklistValuesDetails
	 */
	public function getPicklistValuesDetails($fieldname){
		switch($fieldname){
			case 'isabonne':
				return array(
					'0' => array( 'label' => 'Non', 'icon' => 'ui-icon square-red' ),
					'1' => array( 'label' => 'Oui', 'icon' => 'ui-icon square-green' )
				);
			
			default:
				return parent::getPicklistValuesDetails($fieldname);
		}
	}
	

}
