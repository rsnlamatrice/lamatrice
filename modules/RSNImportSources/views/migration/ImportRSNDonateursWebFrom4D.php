<?php


/* Phase de migration
 * Importation des donateurs Web depuis le fichier provenant de 4D
 */
class RSNImportSources_ImportRSNDonateursWebFrom4D_View extends RSNImportSources_ImportRSNDonateursWebFromSite_View {
    

	/**
	 * Method to get the modules that are concerned by the import.
	 * @return array - An array containing concerned module names.
	 */
	public function getImportModules() {
		//La désactivation de l'import de contact fait qu'on ne voit pas ImportStatus depuis for_module=Contacts
		return array(/*'Contacts', */'RSNDonateursWeb');
	}

	/**
	 * Method to get the modules that are concerned by the import.
	 * @return array - An array containing concerned module names.
	 */
	public function getLockModules() {
		return array_merge($this->getImportModules(), array('ContactEmails'));
	}
	
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
		return 'WINDOWS-1252';
	}
	
	/**
	 * Function that returns if the import controller has a validating step of pre-import data
	 */
	public function hasValidatingStep(){
		return false;
	}
	/**
	 * After preImport validation, and before real import, the controller needs a validation step of pre-imported data
	 */
	public function needValidatingStep(){
		return false;
	}

	/**
	 * Method that check if a line of the file is a client information line.
	 *  It assume that the line is a client information line only and only if the first data is a date.
	 * @param array $line : the data of the file line.
	 * @return boolean - true if the line is a client information line.
	 */
	function isRecordHeaderInformationLine($line) {
		if (sizeof($line) > 0 && is_numeric($line[0]) && $this->isDate($line[14])
		&& !($line[0] >= 5363 && $line[0] <= 5365)// 'sabine.li@sortirdunucleaire.Fr' && $line[9] === '2015-06-12')
			) {
			return true;
		}

		return false;
	}
	
	

	/**
	 * Method to process to the import of a one donateur.
	 * @param $rsndonateurswebData : the data of the donateur to import
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importOneRSNDonateursWeb($rsndonateurswebData, $importDataController) {
		global $VTIGER_BULK_SAVE_MODE;
		$VTIGER_BULK_SAVE_MODE = true;
					
		global $log;
		
		$sourceId = $rsndonateurswebData[0]['externalid'];

		//test sur externalid == $sourceId
		$query = "SELECT crmid, vtiger_rsndonateursweb.contactid
			FROM vtiger_rsndonateursweb
			JOIN vtiger_crmentity
				ON vtiger_rsndonateursweb.rsndonateurswebid = vtiger_crmentity.crmid
			WHERE externalid = ? AND deleted = FALSE
			LIMIT 1
		";
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, array($sourceId));
		if($db->num_rows($result) == 0){
			//Missing
			foreach ($rsndonateurswebData as $rsndonateurswebLine) {
				$entityInfo = array(
					'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
				);
				
				//TODO update all with array
				$importDataController->updateImportStatus($rsndonateurswebLine[id], $entityInfo);
			}
		}
		else {
			$row = $db->fetch_row($result, 0);
			$rsndonateurswebId = $row['crmid'];
			$entryId = $this->getEntryId("RSNDonateursWeb", $rsndonateurswebId);
			$contactId = $row['contactid'];
			
			$record = Vtiger_Record_Model::getInstanceById($rsndonateurswebId, 'RSNDonateursWeb');
			$record->set('mode', 'edit');
			foreach($rsndonateurswebData[0] as $fieldName => $value)
				if(!is_numeric($fieldName) && $fieldName != 'id')
					$record->set($fieldName, $value);
			
			$fieldName = 'amount';
			$value = str_replace('.', ',', $rsndonateurswebData[0][$fieldName]);
			$record->set($fieldName, $value);
			
			//$db->setDebug(true);
			$record->save();

			if(!$rsndonateurswebId){
				//TODO: manage error
				echo "<pre><code>Impossible d'enregistrer le nouvel RSNDonateursWeb</code></pre>";
				foreach ($rsndonateurswebData as $rsndonateurswebLine) {
					$entityInfo = array(
						'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
					);
					
					//TODO update all with array
					$importDataController->updateImportStatus($rsndonateurswebLine[id], $entityInfo);
				}

				return false;
			}
			
			
			$entryId = $this->getEntryId("RSNDonateursWeb", $rsndonateurswebId);
			foreach ($rsndonateurswebData as $rsndonateurswebLine) {
				$entityInfo = array(
					'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_UPDATED,
					'id'		=> $entryId
				);
				$importDataController->updateImportStatus($rsndonateurswebLine[id], $entityInfo);
			}
			
			$record->set('mode','edit');
			$query = "UPDATE vtiger_crmentity
				JOIN vtiger_rsndonateursweb
					ON vtiger_crmentity.crmid = vtiger_rsndonateursweb.rsndonateurswebid
				SET smownerid = ?
				, createdtime = ?
				WHERE vtiger_crmentity.crmid = ?
			";
			$result = $db->pquery($query, array(ASSIGNEDTO_ALL
							, $rsndonateurswebData[0]['datedon']
							, $rsndonateurswebId));
			
			$log->debug("" . basename(__FILE__) . " update imported rsndonateursweb (id=" . $record->getId() . ", sourceId=$sourceId , date=" . $rsndonateurswebData[0]['datedon']
					. ", result=" . ($result ? " true" : "false"). " )");
			if( ! $result)
				$db->echoError();
				
				
			
			return $record;
		}
		

		return true;
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
			'contacttype'		=> 'Donateur Web',
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
		$paiementerror = $rsndonateursweb['donInformations'][23];
		if(!$paiementerror)
			$paiementerror = 0;
		$amount = trim($rsndonateursweb['donInformations'][15]);
		if(strpos($amount, ' '))
			$amount = explode(' ', $amount)[0];
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
			'paiementerror' 	=> $paiementerror,
			'modepaiement' 		=> $rsndonateursweb['donInformations'][25],
			'amount' 		=> $amount,
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
	
	/**
	 * Method called before the data are really imported.
	 *  This method must be overload in the child class.
	 *
	 */
	function beforeImportRSNDonateursWeb() {
	}
}