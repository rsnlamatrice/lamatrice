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

}
