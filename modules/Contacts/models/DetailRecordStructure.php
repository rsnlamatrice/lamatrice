<?php
/*+***********************************************************************************
 * ED151126
 *
 * Surcharge pour trier les champs d'adresses
 * 
 *************************************************************************************/

/**
 * Contacts Record Detail Structure Model
 */
class Contacts_DetailRecordStructure_Model extends Vtiger_DetailRecordStructure_Model {


	/**
	 * Function to get the values in stuctured format
	 * @return <array> - values in structure array('block'=>array(fieldinfo));
	 */
	public function getStructure() {
		$structure = parent::getStructure();
		//sort fields for detail view
		foreach($structure as $blockId => &$block){
			if($blockId === 'LBL_ADDRESS_INFORMATION'
			|| $blockId === 'Adresse secondaire')
				uksort($block, array('Contacts_DetailRecordStructure_Model', 'uksortDetailViewFields'));
		}
		return $structure;
	}
	
	static function uksortDetailViewFields($a, $b){
		$orderedFieldNames = array(
			'rsnnpai',
			'rsnnpaicomment',
			'mailingaddressformat',
			'mailingzip',
			'mailingstreet2',
			'mailingcity',
			'mailingstreet',
			'mailingcountry',
			'mailingstreet3',
			'mailingstate',
			'mailingpobox',
			
			'use_address2_for_revue',
			'use_address2_for_recu_fiscal',
			'otheraddressformat',
			'otherzip',
			'otherstreet2',
			'othercity',
			'otherstreet',
			'othercountry',
			'otherstreet3',
			'otherstate',
			'otherpobox',
		);
		$apos  = array_search($a, $orderedFieldNames);
		if ($apos === false) return 0;
		$bpos  = array_search($b, $orderedFieldNames);
		if ($bpos === false) return 0;
		return ($apos - $bpos);
	}
}