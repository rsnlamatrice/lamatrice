<?php
/*+***********************************************************************************
 * ED141021
 *************************************************************************************/

/**
 * RsnReglements Entity Record Model Class
 */
class RsnPrelVirement_Record_Model extends Vtiger_Record_Model {

	
	/**
	 * Function to get the Display Name for the record
	 * @return <String> - Entity Display Name for the record
	 */
	public function getDisplayName() {
		$name = number_format( $this->get('montant'), 2);
		$name .= ' &euro;';
		$name .= ' - ' . $this->get('rsnprelvirstatus');
		$date  = new DateTime($this->get('dateexport'));
		$name .= ' - ' . $date->format('d M Y');//date('d M Y', $this->get('dateregl'));
		return $name;
	}

	var $RsnPrelevement;
	public function getRsnPrelevement(){
		if(!$this->RsnPrelevement || $this->RsnPrelevement->get('rsnprelevementsid') != $this->get('rsnprelevementsid')){
			$this->RsnPrelevement = Vtiger_Record_Model::getInstanceById($this->get('rsnprelevementsid'), 'RsnPrelevements');
		}
		return $this->RsnPrelevement;
	}

	var $Contact;
	public function getContact(){
		$rsnPrelevement = $this->getRsnPrelevement();
		if(!$this->Contact || $this->Contact->get('accountid') != $rsnPrelevement->get('accountid')){
			$account = Vtiger_Record_Model::getInstanceById($rsnPrelevement->get('accountid'), 'Accounts');
			$this->Contact = $account->getRelatedMainContact();
		}
		return $this->Contact;
	}
}
