<?php
/*+***********************************************************************************
 * ED150829
 *************************************************************************************/

/**
 * Contacts Field Model Class
 */
class Contacts_Field_Model extends Vtiger_Field_Model {

	/* ED150829
	 * Nom du picklist correspond au champ
	*/
	public function getPickListName() {
		switch($this->getName()){
		 case 'mailingstate': //module Contacts
		 case 'otherstate': //module Contacts
		 case 'bill_state': //modules
		 case 'ship_state': //modules
		 case 'address_state': //module Users 
		 case 'state': //module Contacts
			return 'rsnregion';
		 case 'bill_country':
		 case 'ship_country':
		 case 'country':
		 case 'mailingcountry':
		 case 'othercountry':
		 case 'address_country':
			return 'rsncountry';
		 case 'bill_city':
		 case 'ship_city':
		 case 'city':
		 case 'mailingcity':
		 case 'othercity':
		 case 'address_city':
		 	return 'rsncity';
		 case 'mailingzip':
		 	return 'rsnzipcode';
		 case 'rsnmoderegl':
		 	return 'receivedmoderegl';
		
		 default:
			return $this->getName();
		}
	}

}