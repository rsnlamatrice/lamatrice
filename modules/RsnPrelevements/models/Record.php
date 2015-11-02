<?php
/*+***********************************************************************************
 * ED141021
 *************************************************************************************/

/**
 * RsnReglements Entity Record Model Class
 */
class RsnPrelevements_Record_Model extends Vtiger_Record_Model {

	
	/**
	 * ED141109
	 * getPicklistValuesDetails
	 */
	public function getPicklistValuesDetails($fieldname){
		switch($fieldname){
			case 'etat':
				return array(
					'0' => array( 'label' => 'Actif', 'icon' => 'ui-icon ui-icon-check darkgreen' ),
					'1' => array( 'label' => 'Suspendu', 'icon' => 'ui-icon ui-icon-close darkred' ),
					'2' => array( 'label' => 'Arrété', 'icon' => 'ui-icon ui-icon-locked darkred' ),
				);
			default:
				return parent::getPicklistValuesDetails($fieldname);
		}
	}
	
	/**
	 * Function to get the Display Name for the record
	 * @return <String> - Entity Display Name for the record
	 */
	public function getDisplayName() {
		$name = number_format( $this->get('montant'), 2);
		$name .= ' &euro;';
		$name .= ' ' . $this->get('periodicite');
		$date  = new DateTime($this->get('createdtime'));
		$name .= ' - ' . $date->format('d M Y');//date('d M Y', $this->get('dateregl'));
		return $name;
	}

	/**
	 * Génère un enregistrement RsnPrelVirement
	 */
	public function createPrelVirement($dateVir){
		$recordModel = Vtiger_Record_Model::getCleanInstance('RsnPrelVirement');
		$recordModel->set('rsnprelevementsid', $this->getId());
		$recordModel->set('is_first', !$this->get('dejapreleve') || $this->get('dejapreleve') === '0000-00-00' ? 1 : 0);
		$recordModel->set('dateexport', $dateVir->format('d-m-Y'));
		$recordModel->set('montant', str_replace('.', ',', ($this->get('montant'))));
		$recordModel->set('separum', $this->get('separum'));
		$recordModel->set('iban', $this->get('sepaibanbban'));
		$recordModel->set('bic', $this->get('sepabic'));
		$recordModel->set('rsnprelvirstatus', 'Ok');
		$recordModel->save();
		return $recordModel;
	}
	
	/**
	 * Retourne le label de la périodicité, sans le n° d ela période et en minuscule
	 */
	public function getPeriodiciteLabel(){
		return strtolower(preg_replace('/^(.*)\d+$/', '$1', $this->get('periodicite')));
	}
	
	/**
	 * Retourne la date de début de ce prélèvement
	 */
	public function getDateDebut(){
		$date = new DateTime($this->get('sepadatesignature'));
		return $date->format('d/m/Y');
	}

	/**
	 * AV151008
	 * @return true if there is one ore more related RsnPrelVirement.
	 */
	public function hasRelatedPrelVirements() {
		$relationModel = Vtiger_Relation_Model::getInstance($this->getModule(), Vtiger_Module_Model::getInstance('RsnPrelVirement'));

		return $relationModel->countRecords($this) > 0;
	}

	/**
	 * AV151008
	 * @return false if there is one ore more related RsnPrelVirement.
	 */
	public function isEditable() {
		return !$this->hasRelatedPrelVirements();
	}

	/**
	 * AV151008
	 * @return false if there is one ore more related RsnPrelVirement.
	 */
	public function isDeletable() {
		return !$this->hasRelatedPrelVirements();
	}
}
