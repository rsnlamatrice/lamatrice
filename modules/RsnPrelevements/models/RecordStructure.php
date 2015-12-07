<?php
/*+***********************************************************************************
 * 
 *************************************************************************************/

/**
 * Vtiger Record Structure Model
 */
class RsnPrelevements_RecordStructure_Model extends Vtiger_RecordStructure_Model {

	/**
	 * Function to get the values in stuctured format
	 * @return <array> - values in structure array('block'=>array(fieldinfo));
	 * ED151204 verrouille les champs si un ordre de prŽlvement existe djˆ
	 */
	public function getStructure() {
		$structuredValues = parent::getStructure();
		self::checkEditableStructure($this->record, $structuredValues);
		return $structuredValues;
	}
	
	public static function checkEditableStructure($recordModel, &$structuredValues){
		$changes = 0;
		if($recordModel && $recordModel->hasRelatedPrelVirements()){
			$lockedFields = self::getLockedFieldNamesIfHasRelatedPrelVirements();
			foreach($structuredValues as &$block)
				foreach($block as $field_name => &$field)
					if(in_array($field_name, $lockedFields)){
						$changes++;
						$field->set('displaytype', 2);
					}
		}
	}
	
	public static function getLockedFieldNamesIfHasRelatedPrelVirements(){
		return array('nom', 'numcompte', 'codebanque', 'codeguichet', 'clerib', 'sepaibanpays', 'sepaibancle', 'sepaibanbban', 'sepabic', 'sepadatesignature', 'separum');
	}
}