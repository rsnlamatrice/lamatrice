<?php


/* Phase de migration
 * Importation des donateurs Web depuis le fichier provenant de 4D
 */
class RSNImportSources_ImportRSNDonateursWebFrom4D_View extends RSNImportSources_ImportRSNDonateursWebFromSite_View {
        
	/**
	 * Method to get the source import label to display.
	 * @return string - The label.
	 */
	public function getSource() {
		return 'LBL_DONATEURSWEB_4D';
	}

	/**
	 * Method to default file enconding for this import.
	 * @return string - the default file encoding.
	 */
	public function getDefaultFileEncoding() {
		return 'ISO-8859-1';
	}

	/**
	 * Method that check if a line of the file is a client information line.
	 *  It assume that the line is a client information line only and only if the first data is a date.
	 * @param array $line : the data of the file line.
	 * @return boolean - true if the line is a client information line.
	 */
	function isRecordHeaderInformationLine($line) {
		if (sizeof($line) > 0 && is_numeric($line[0]) && $this->isDate($line[14])) {
			return true;
		}

		return false;
	}
	
	/**
	 * Method that return the formated information of a contact found in the file.
	 * @param $rsndonateurswebInformations : the invoice informations data found in the file.
	 * @return array : the formated data of the contact.
	 */
	function getContactValues($rsndonateurswebInformations) {
		if(preg_match('/^0?6/', $rsndonateurswebInformations[10]))
			$mobile = $rsndonateurswebInformations[10];
		else
			$phone = $rsndonateurswebInformations[10];
			
		$country = $rsndonateurswebInformations[8];
		
		$prenom = $rsndonateurswebInformations[3];
		if($prenom == strtoupper($prenom))
			$prenom = ucfirst( mb_strtolower($prenom) );
		else
			$prenom = ucfirst( $prenom );
		
					
		$contactMapping = array(
			'lastname'		=> mb_strtoupper($rsndonateurswebInformations[2]),
			'firstname'		=> $prenom,
			'email'			=> $rsndonateurswebInformations[9],
			'mailingstreet'		=> $rsndonateurswebInformations[5],
			'mailingstreet3'	=> $rsndonateurswebInformations[4],
			'mailingzip'		=> $rsndonateurswebInformations[6],
			'mailingcity'		=> mb_strtoupper($rsndonateurswebInformations[7]),
			'mailingcountry' 	=> $country == 'FR' ? '' : $country,
			'phone'			=> isset($phone) ? $phone : '',
			'mobile'		=> isset($mobile) ? $mobile : '',
			'accounttype'		=> 'Donateur Web',
			'leadsource'		=> $this->getContactOrigine($rsndonateurswebInformations),
		);
		return $contactMapping;
	}

	/**
	 * Retourne l'origine du contact suivant le mode de paiement
	 */
	function getContactOrigine($data){
		$col_modepaiement = 25;
		$col_frequency = 16;
		switch($data[$col_modepaiement]){				
			case 'cb':
				if($data[$col_frequency]=='1')
					return "DON_ETALE";
				return 'PayBox';
				
			case 'paypal':
				return 'PayPal';
				
			case 'cheque':
			case 'virement':
			case 'prelevement':
			default:
				return 'Donateur web';
		}
	}
	
	/**
	 * Method that return the formated information of an invoice found in the file.
	 * @param $rsndonateursweb : the invoice data found in the file.
	 * @return array : the formated data of the invoice.
	 */
	function getRSNDonateursWebValues($rsndonateursweb) {
	//TODO end implementation of this method
		$rsndonateurswebValues = array();
		$date = $this->getMySQLDate($rsndonateursweb['donInformations'][14]);
		$dateEndAbo = $this->getMySQLDateAboEnd($rsndonateursweb['donInformations'][19]);
		$country = $rsndonateursweb['donInformations'][8];
		$prenom = $rsndonateursweb['donInformations'][3];
		if($prenom == strtoupper($prenom))
			$prenom = ucfirst( mb_strtolower($prenom) );
		else
			$prenom = ucfirst( $prenom );
			
		$rsndonateurswebHeader = array(
			'externalid'		=> $rsndonateursweb['donInformations'][0],
			'lastname'		=> mb_strtoupper($rsndonateursweb['donInformations'][2]),
			'firstname'		=> $prenom,
			'email'			=> $rsndonateursweb['donInformations'][9],
			'address'		=> $rsndonateursweb['donInformations'][5],
			'address3'		=> $rsndonateursweb['donInformations'][4],
			'zip'			=> $rsndonateursweb['donInformations'][6],
			'city'			=> mb_strtoupper($rsndonateursweb['donInformations'][7]),
			'country' 		=> $country == 'FR' ? '' : $country,
			'datedon'		=> $date,
			//'contactid',
			//'relatedmoduleid',
			'phone' 		=> $rsndonateursweb['donInformations'][10],
			'frequency' 		=> $rsndonateursweb['donInformations'][16],
			'paiementid' 		=> $rsndonateursweb['donInformations'][17],
			'abocode' 		=> $rsndonateursweb['donInformations'][18],
			'dateaboend' 		=> $dateEndAbo,
			'paiementerror' 	=> $rsndonateursweb['donInformations'][23],
			'modepaiement' 		=> $rsndonateursweb['donInformations'][25],
			'amount' 		=> $rsndonateursweb['donInformations'][15],
			'recu' 			=> $rsndonateursweb['donInformations'][20],
			'revue' 		=> $rsndonateursweb['donInformations'][21],
			'clicksource' 		=> $rsndonateursweb['donInformations'][26],
			'listesn' 		=> $rsndonateursweb['donInformations'][1],
			//'assigned_user_id',
			//'createdtime',
			//'modifiedtime',
			'autorisation' 		=> $rsndonateursweb['donInformations'][22],
			'ipaddress' 		=> $rsndonateursweb['donInformations'][13],
			'isvalid' 		=> 1,
		);
		if($rsndonateurswebHeader['modepaiement'] == 'cb'
		&& $rsndonateurswebHeader['paiementerror'] != '0')
			$rsndonateurswebHeader['isvalid'] = 0;
		return $rsndonateurswebHeader;
	}
	
}