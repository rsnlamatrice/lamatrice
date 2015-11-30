<?php
/*+***********************************************************************************
 * ED141021
 *************************************************************************************/

/**
 * RsnReglements Entity Record Model Class
 */
class RsnPrelevements_Record_Model extends Vtiger_Record_Model {	

	/**
	 * Function to get URL for Export the record as PDF
	 * @return <type>
	 */
	public function getExportPDFUrl() {
		return "index.php?module=".$this->getModuleName()."&action=ExportPDF&record=".$this->getId();
	}

	/**
	 * Function to get this record and details as PDF
	 */
	public function getPDF($filePath = false) {
		
		include_once('modules/RsnPrelevements/pdf/PDFController.php');
		
		$recordId = $this->getId();
		$moduleName = $this->getModuleName();

		$controllerClassName = "Vtiger_RsnPrelevements_PDFController";
		
		$controller = new $controllerClassName($moduleName);
		$controller->loadRecord($recordId);

		if($filePath){
			$fileName = $filePath . '/' . remove_accent(vtranslate('SINGLE_'.$moduleName, $moduleName)).'_'.$recordId.'.pdf';
			$controller->Output($fileName, 'F');
			return $fileName;
		}
		else{
			$fileName = remove_accent(vtranslate('SINGLE_'.$moduleName, $moduleName)).'_'.$recordId.'.pdf';
			$controller->Output($fileName, 'D');
		}
		return $fileName;
	}
	
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
		
		if(!$this->get('dejapreleve') || $this->get('dejapreleve') == '0000-00-00'){
			$this->set('mode', 'edit');
			$this->set('dejapreleve', date('d-m-Y'));
			$this->set('montant', str_replace('.', ',', $this->get('montant')));//bug de prise en compte de la virgule comme séparateur de millier
			$this->save();
		}
		//S'assure de l'abonnement à la revue
		$this->ensureAboRevue();
		
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
		return $this->getRelatedPrelVirementsCount() > 0;
	}

	/**
	 * ED151130
	 * @return related RsnPrelVirement count.
	 */
	public function getRelatedPrelVirementsCount() {
		$relationModel = Vtiger_Relation_Model::getInstance($this->getModule(), Vtiger_Module_Model::getInstance('RsnPrelVirement'));

		return $relationModel->countRecords($this);
	}

	/**
	 * AV151008
	 * @return false if there is one ore more related RsnPrelVirement.
	 *
	 * ED151130
	 * Désolé, mais on peut modifierr le prélèvement : changement d'état et de montant
	 */
	public function isEditable() {
		//return !$this->hasRelatedPrelVirements();
		return true;
	}

	/**
	 * AV151008
	 * @return false if there is one ore more related RsnPrelVirement.
	 */
	public function isDeletable() {
		return !$this->hasRelatedPrelVirements();
	}
	
	//Vérifie que le contact a bien un abonnement actif en remerciement
	function ensureAboRevue(){
		if($this->get('etat') == 0){
			$aboRevueModuleModel = Vtiger_Module_Model::getInstance('RSNAboRevues');
			$periodicite = preg_replace('/^(\D+)(\s\d+)?$/', '$1', $this->get('periodicite'));
			switch($periodicite){
			case 'Annuel':
				$months = 12;
				break;
			case 'Mensuel':
				$months = 3;
				break;
			case 'Une seule fois':
				//TODO arbitraire...
				if((float)$this->get('montant') >= 48)
					$months = 12;
				elseif((float)$this->get('montant') >= 12)
					$months = 6;
				else
					$months = 3;
				break;
			default:
				$months = 6;
				break;
			}
			$aboRevueModuleModel->ensureAboRevue($this->get('accountid'), $months, RSNABOREVUES_TYPE_NUM_MERCI, $this);
		}
	}
}
