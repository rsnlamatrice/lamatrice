<?php

/* type abonnement 0=standard, 1=ne pas abonner, 2=reçu fiscal seul, 3=Abonné gratuit, 4=Abonnement groupé */
define('_4D_TYPEABONNE_STANDARD', '0');
define('TYPEABONNE_STANDARD', 'Abonné(e)');
define('_4D_TYPEABONNE_NEPASABONNER', '1');
define('TYPEABONNE_NEPASABONNER', 'Ne pas abonner');
define('_4D_TYPEABONNE_RECUFISCALSEUL', '2');
define('TYPEABONNE_RECUFISCALSEUL', 'Reçu fiscal seul');
define('_4D_TYPEABONNE_ABOGRATUIT', '3');
define('TYPEABONNE_ABOGRATUIT', 'Abonné(e) à vie');
define('_4D_TYPEABONNE_ABOGROUPE', '4');
define('TYPEABONNE_ABOGROUPE', 'Abonnement groupé');
define('TYPE_CONTACT_SUPPRIME', 'SUPPRIMÉ');
/* Phase de migration
 * Importation des contacts Web depuis le fichier provenant de 4D
 */
class RSNImportSources_ImportContactsFrom4D_View extends RSNImportSources_ImportFromFile_View {
        
	/**
	 * Method to get the source import label to display.
	 * @return string - The label.
	 */
	public function getSource() {
		return 'LBL_CONTACTS_4D';
	}

	/**
	 * Method to get the modules that are concerned by the import.
	 * @return array - An array containing concerned module names.
	 */
	public function getImportModules() {
		return array('Contacts');
	}

	/**
	 * Method to default file enconding for this import.
	 * @return string - the default file encoding.
	 */
	public function getDefaultFileEncoding() {
		return 'ISO-8859-1';
	}

	/**
	 * Method to get the source type label to display.
	 * @return string - The label.
	 */
	public function getSourceType() {
		return 'LBL_CSV_FILE';
	}

	/**
	 * Method to get the suported file delimiters for this import.
	 * @return array - an array of string containing the supported file delimiters.
	 */
	public function getDefaultFileDelimiter() {
		return '	';
	}

	/**
	 * Method to get the suported file extentions for this import.
	 * @return array - an array of string containing the supported file extentions.
	 */
	public function getSupportedFileExtentions() {
		return array('csv');
	}

	/**
	 * Method to default file extention for this import.
	 * @return string - the default file extention.
	 */
	public function getDefaultFileType() {
		return 'csv';
	}
	
	/**
	 * Method to get the imported fields for the contacts module.
	 * @return array - the imported fields for the contacts module.
	 */
	function getContactsFieldsMapping() {
		//laisser exactement les colonnes du fichier, dans l'ordre 
		return array (
			'nom' => 'lastname', 
			'prenom' => 'firstname', 
			'complementgeographique' => 'mailingstreet3', 
			'numeroetlibelledelavoie' => 'mailingstreet', 
			'codepostal' => 'mailingzip', 
			'ville' => 'mailingcity', 
			'ligne5' => 'mailingpobox', 
			'adresseligne2' => 'mailingstreet2', //ou nom si groupe et nom manquant. Attention C4213 est un particulier qui n'a pas de 'associationcourt', adresseligne2 vaut 'Appartement 51'
			'telephone' => 'phone',//mobile ?
			'fax' => 'fax',
			'groupe' => '',//NON SIGTR, PROSPECT : quasi sûr que ça ne serve pas
			'nomassoentreprisealaplacedenomp' => 'mailingaddressformat', //ckeck it
			'reffiche' => '', //REF UNIQUE -> contact_no
			'nbabosgroupes' => '', //REVUES EN PLUS DE L'ABONNEMENT
			'remarque' => 'description',
			'associationcourt' => 'grpnomllong',
			'datecreation' => '',//updated post creation
			'conventionnef' => '',//0 ou 1. accounttype += 'Convention NEF'
			'datemodification' => '',//updated post creation. TODO ? MAX(datemodifadresse, datemodification)
			'presse' => '',//type de contact += 'Média'
			'p_militante' => '',//type de contact += 'Militant'
			'abonne_rezo' => '',//0 ou 1. RSNAboRevues->isabonne
			'date_abn_rezo' => '',//debutabo
			'signataire' => 'signcharte',
			'cumul_don' => '',
			'cumul_donsprlvts' => '',
			'sympathisant' => '',//type de contact +=
			'don_etale' => '',
			'pasderecufiscal' => '',//TODO 
			'cumul_prelevement' => '',
			'portable' => 'mobile',
			'prelevement' => '',
			'pays' => 'mailingcountry', 
			'ancien_totaldon' => '',
			'ancien_cumul_prlvmts' => '',
			'apartirnumerorevueabn' => '',
			'departement' => 'mailingstate',  // mailingstate ?
			'sitewebgroupe' => 'websiteurl',
			'origine' => 'leadsource',//TODO translate
			'typeabonne' => '',//RSNAboRevues->type abonnement 0=standard, 1=ne pas abonner, 2=reçu fiscal seul, 3=Abonné gratuit, 4=Abonnement groupé
			'typedegroupe' => 'rsngrptypes',
			'descriptifgroupe' => 'grpdescriptif',
			'cumul_nef' => '',
			'nombredadherentsdugroupe' => 'grpnbremembres',
			'evaladr' => '',
			'charade' => '',
			'statutnpai' => 'rsnnpai',
			'datestatutnpai' => 'rsnnpaicomment', //bof
			'date_finabn_rezo' => '',//RSNAboRevues finabo
			'depotvente' => '',//type de contact += 'Dépôt-vente'
			'datemodifadresse' => '',//TODO ? MAX(datemodifadresse, datemodification)
			'typecontact' => 'isgroup',//
			'pasautornvp' => '',// TODO
			'pasrelancefinancierecourrier' => 'donotappeldoncourrier',
			'pasrelanceabo' => 'donotrelanceabo',
			'dateexportcogilog' => '',
			'datetraitementrnvp' => '',
			'top_rnvp' => '',
			'pasderelancefinanciereweb' => 'donotappeldonweb',
			'vtiger' => '',
			'partenaire' => '',//si vrai accounttype += 'Partenaire'
			'partenairedate' => 'datepartenariat',//à mettre dans partenairedescription si ! partenaire 
			'signatairedate' => 'datesigncharte',
			
			//champ supplémentaire
			'_contactid' => '', //Massively updated after preImport
		);
			//donotcourrierag, rsnwebhide dans import Groupe ?
	}
	
	function getContactsDateFields(){
		return array(
			'datecreation', 'datemodification', 'date_abn_rezo', 'datestatutnpai', 'date_finabn_rezo'
			, 'datemodifadresse'
			, 'dateexportcogilog', 'datetraitementrnvp'
			,'partenairedate', 'signatairedate'
		);
	}
	
	/**
	 * Method to get the imported fields for the contacts module.
	 * @return array - the imported fields for the contacts module.
	 */
	function getContactsFields() {
		//laisser exactement les colonnes du fichier
		return array_keys($this->getContactsFieldsMapping());
	}

	/**
	 * Method to process to the import of the Contacts module.
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importContacts($importDataController) {
		global $VTIGER_BULK_SAVE_MODE;
		$VTIGER_BULK_SAVE_MODE = true;
		$config = new RSNImportSources_Config_Model();
		
		$adb = PearDatabase::getInstance();
		$tableName = Import_Utils_Helper::getDbTableName($this->user, 'Contacts');
		$sql = 'SELECT * FROM ' . $tableName . ' WHERE status = '. RSNImportSources_Data_Action::$IMPORT_RECORD_NONE . ' ORDER BY id';

		$result = $adb->query($sql);
		$numberOfRecords = $adb->num_rows($result);

		if ($numberOfRecords <= 0) {
			return;
		}
		if($numberOfRecords == $config->get('importBatchLimit')){
			$this->keepScheduledImport = true;
		}

		$perf = new RSN_Performance_Helper($numberOfRecords);
		for ($i = 0; $i < $numberOfRecords; ++$i) {
			$row = $adb->raw_query_result_rowdata($result, $i);
			$this->importOneContacts(array($row), $importDataController);
			$perf->tick();
			if(Import_Utils_Helper::isMemoryUsageToHigh()){
				$this->skipNextScheduledImports = true;
				$keepScheduledImport = true;
				$size = RSN_Performance_Helper::getMemoryUsage();
				echo '
<pre>
	<b> '.vtranslate('LBL_MEMORY_IS_OVER', 'Import').' : '.$size.' </b>
</pre>
';
				break;
			}
		}
		$perf->terminate();
		
		if(isset($keepScheduledImport))
			$this->keepScheduledImport = $keepScheduledImport;
		elseif($numberOfRecords == $config->get('importBatchLimit')){
			$this->keepScheduledImport = $this->getNumberOfRecords() > 0;
		}
	}

	/**
	 * Method to process to the import of a one prelevement.
	 * @param $contactsData : the data of the prelevement to import
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importOneContacts($contactsData, $importDataController) {
					
		global $log;
		
		$sourceId = $contactsData[0]['reffiche'];
		$entryId = $contactsData[0]['_contactid']; // initialisé dans le postPreImportData
		if(!$entryId){
			//Contrôle des doublons dans la source
			if(false) // parce que [Migration]
				$entryId = $this->getContactIdFromRef4D($sourceId);
		}
		if($entryId){
			//already imported !!
			foreach ($contactsData as $contactsLine) {
				$entityInfo = array(
					'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_SKIPPED,
					'id'		=> $entryId
				);
				
				//TODO update all with array
				$importDataController->updateImportStatus($contactsLine[id], $entityInfo);
			}
		}
		else {
			$record = Vtiger_Record_Model::getCleanInstance('Contacts');
			$record->set('mode', 'create');
			
			$this->updateContactRecordModelFromData($record, $contactsData);
			
			//$db->setDebug(true);
			$record->save();
			$contactId = $record->getId();
			
			if(!$contactId){
				//TODO: manage error
				echo "<pre><code>Impossible d'enregistrer le contact</code></pre>";
				foreach ($contactsData as $contactsLine) {
					$entityInfo = array(
						'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
					);
					
					//TODO update all with array
					$importDataController->updateImportStatus($contactsLine[id], $entityInfo);
				}

				return false;
			}
			
			$entryId = $this->getEntryId("Contacts", $contactId);
			foreach ($contactsData as $contactsLine) {
				$entityInfo = array(
					'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_CREATED,
					'id'		=> $entryId
				);
				$importDataController->updateImportStatus($contactsLine[id], $entityInfo);
			}
			
			$record->set('mode','edit');
				
			$this->createInitialModComment($record, $contactsData);
				
			//Abonnement à la revue (peut générer le compte du contact)
			$fieldName = 'accounttype';
			$contactType = TYPE_CONTACT_SUPPRIME;
			if($record->get($fieldName) !== $contactType){
				$rsnAboRevue = $this->importRSNAboRevuesForContact($record, $contactsData);
				//type de contact [ancien] Abonné
				if($rsnAboRevue
				&& $rsnAboRevue->get('rsnabotype') != TYPEABONNE_NEPASABONNER){
					$record->set('mode', 'edit');
					$contactType = $record->get('accounttype');
					$addType = $rsnAboRevue->get('isabonne') ? 'Abonné' : 'Ancien abonné';
					if($contactType)
						$record->set('accounttype', $contactType . ' |##| ' . $addType);
					else
						$record->set('accounttype', $addType);
					$record->save();
				}
			}
			
			$db = PearDatabase::getInstance();
			$query = "UPDATE vtiger_crmentity
				JOIN vtiger_contactdetails
					ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid
				SET smownerid = ?
				, modifiedtime = ?
				, createdtime = ?
				, label = ?
				, contact_no = CONCAT('C', ?)
				WHERE vtiger_crmentity.crmid = ?
			";
			$result = $db->pquery($query, array(ASSIGNEDTO_ALL
								, $contactsData[0]['datemodification']
								, $contactsData[0]['datecreation']
								, trim($record->get('firstname') . ' ' . $record->get('lastname'))
								, $contactsData[0]['reffiche']
								, $contactId));
			
			$log->debug("" . basename(__FILE__) . " update imported contacts (id=" . $record->getId() . ", Ref 4D=$sourceId , date=" . $contactsData[0]['datecreation']
					. ", result=" . ($result ? " true" : "false"). " )");
			if( ! $result)
				$db->echoError();
			return $record;
		}

		return true;
	}

	//Mise à jour des données du record model nouvellement créé à partir des données d'importation
	protected function updateContactRecordModelFromData($record, $contactsData){
		
		$fieldsMapping = $this->getContactsFieldsMapping();
		foreach($contactsData[0] as $fieldName => $value)
			if(!is_numeric($fieldName) && $fieldName != 'id'){
				$vField = $fieldsMapping[$fieldName];
				if($vField)
					$record->set($vField, $value);
			}
			
		$fieldName = 'lastname';
		if(!$contactsData[0]['nom']){
			if( $contactsData[0]['nomassoentreprisealaplacedenomp']
			&& $record->get('grpnomllong')){
				$value = $record->get('grpnomllong');
			}
			else {
				$value = $contactsData[0]['adresseligne2'];
				if($value){
					$record->set('mailingstreet2', '');
					$record->set('mailingaddressformat', '');
				}
				else {
					$value = $contactsData[0]['associationcourt'];
					if(!$value){
						$value = $contactsData[0]['prenom'];
						if(!$value)
							$value = '_ref4D_'.$contactsData[0]['reffiche'];
					}
				}
			}
			$record->set($fieldName, $value);
		}
		if($record->get($fieldName) == $record->get('mailingstreet2'))
			$record->set('mailingstreet2', '');
		
			
		$typeAbonne = $contactsData[0]['typeabonne'];
		/* reçu fiscal seul */
		if($typeAbonne == _4D_TYPEABONNE_RECUFISCALSEUL){
			/* "ne pas" partout */
			foreach( array('emailoptout', 'donotcall', 'donotprospect', 'donotrelanceadh', 'donotappeldoncourrier', 'donotrelanceabo', 'donotappeldonweb')
					as $fieldName){
				$record->set($fieldName, null);
			}
			
			$fieldName = 'donototherdocuments';
			$value = TYPEABONNE_RECUFISCALSEUL;
			$record->set($fieldName, $value);
		}
		elseif($typeAbonne == _4D_TYPEABONNE_NEPASABONNER){
			/* "ne pas" partout */
			$fieldName = 'donotrelanceabo';
			$record->set($fieldName, 1);
		}
		
		//nomassoentreprisealaplacedenomp => mailingaddressformat
		//'associationcourt' => 'grpnomllong',
		$fieldName = 'mailingaddressformat';
		if( $contactsData[0]['nomassoentreprisealaplacedenomp']){
			if($record->get('mailingstreet2')){
				if($record->get('grpnomllong')
				&& $record->get('grpnomllong') != $record->get('mailingstreet2')){
					/* TODO Alerter que l'adresse est à contrôler */
					$value = 'NC1';
				}
				else
					$value = 'CN1';
			}
			elseif($record->get('grpnomllong') && $record->get('grpnomllong') != $record->get('lastname')){
				$record->set('mailingstreet2', $record->get('grpnomllong'));
				$value = 'CN1';
			}
			else {
				$value = 'NC1';
			}
			$record->set($fieldName, $value);
		}
		
		//cast des DateTime
		foreach($this->getContactsDateFields() as $fieldName){
			$value = $record->get($fieldName);
			if( is_object($value) )
				$record->set($fieldsMapping[$fieldName], $value->format('Y-m-d'));
		}
		
		$fieldName = 'rsnnpaicomment';//on y a stocké la date de dernière modif du NPAI
		if($record->get($fieldName)){//is date
			$dateTime = new DateTime($record->get($fieldName));
			$record->set($fieldName, $dateTime->format('d/m/Y'));
		}
		
		//TODO Check exist in picklist known values -> generic function to insert new value
		//'typedegroupe' => 'rsngrptypes',
		$fieldName = 'rsngrptypes';
		if($record->get($fieldName))
			RSNImportSources_Utils_Helper::checkPickListValue('Contacts', $fieldName, $fieldName, $record->get($fieldName));
		
		//'origine' => 'leadsource',//TODO translate
		$fieldName = 'leadsource';
		if($record->get($fieldName))
			RSNImportSources_Utils_Helper::checkPickListValue('Contacts', $fieldName, $fieldName, $record->get($fieldName));
		
		//'departement' => 'mailingstate',
		$fieldName = 'mailingstate';
		if($record->get($fieldName)){
			//2 chiffres pour le département
			$record->set($fieldName, str_pad($record->get($fieldName), 2, "0", STR_PAD_LEFT));
			$result = RSNImportSources_Utils_Helper::checkPickListValue('Contacts', $fieldName, 'rsnregion', $record->get($fieldName));
		}
		
		$fieldName = 'accounttype';
		$contactType = '';
		//'signataire' => 'signcharte',
		if($contactsData[0]['signataire'])
			$contactType .= ($contactType ? ' |##| ' : '') . 'Signataire';
		//'presse' => '',//type de contact += 'Média'
		if($contactsData[0]['presse'])
			$contactType .= ($contactType ? ' |##| ' : '') . 'Média';
		//'p_militante' => '',//type de contact += 'Militant'
		if($contactsData[0]['p_militante'])
			$contactType .= ($contactType ? ' |##| ' : '') . 'Militant';
		//'depotvente' => '',//type de contact += 'Dépôt-vente'
		if($contactsData[0]['depotvente'])
			$contactType .= ($contactType ? ' |##| ' : '') . 'Dépôt-vente';
		//'conventionnef' => '',//0 ou 1. accounttype += 'Convention NEF'
		if($contactsData[0]['conventionnef'])
			$contactType .= ($contactType ? ' |##| ' : '') . 'Convention NEF';
		//'partenaire' => '',//si vrai accounttype += 'Partenaire'
		if($contactsData[0]['partenaire'])
			$contactType .= ($contactType ? ' |##| ' : '') . 'Partenaire';
		//set
		if($contactType){
			$record->set($fieldName, $contactType);
		}
			
			
		// copie depuis tout en haut
		//
		//'nom' => 'lastname', 
		//'prenom' => 'firstname', 
		//'complementgeographique' => 'mailingstreet3', 
		//'numeroetlibelledelavoie' => 'mailingstreet', 
		//'codepostal' => 'mailingzip', 
		//'ville' => 'mailingcity', 
		//'ligne5' => 'mailingpobox', 
		//'adresseligne2' => 'mailingstreet2', //ou nom si groupe et nom manquant. Attention C4213 est un particulier qui n'a pas de 'associationcourt', adresseligne2 vaut 'Appartement 51'
		//'telephone' => 'phone',//mobile ?
		//'fax' => 'fax',
		//'groupe' => '',//NON SIGTR, PROSPECT : quasi sûr que ça ne serve pas
		//'nomassoentreprisealaplacedenomp' => 'mailingaddressformat', //ckeck it
		//'reffiche' => 'contact_no', //REF UNIQUE, + préfixe
		//'nbabosgroupes' => '', //REVUES EN PLUS DE L'ABONNEMENT
		//'remarque' => 'description',
		//'associationcourt' => 'grpnomllong',
		//'datecreation' => '',//updated post creation
		//'conventionnef' => '',//0 ou 1. accounttype += 'Convention NEF'
		//'datemodification' => '',//updated post creation. TODO ? MAX(datemodifadresse, datemodification)
		//'presse' => '',//type de contact += 'Média'
		//'p_militante' => '',//type de contact += 'Militant'
		//'abonne_rezo' => '',//0 ou 1. RSNAboRevues->isabonne
		//'date_abn_rezo' => '',//debutabo
		//'signataire' => 'signcharte',
		//'cumul_don' => '',
		//'cumul_donsprlvts' => '',
		//'sympathisant' => '',//type de contact +=
		//'don_etale' => '',
		//'pasderecufiscal' => '',//TODO 
		//'cumul_prelevement' => '',
		//'portable' => 'mobile',
		//'prelevement' => '',
		//'pays' => 'mailingcountry', 
		//'ancien_totaldon' => '',
		//'ancien_cumul_prlvmts' => '',
		//'apartirnumerorevueabn' => '',
		//'departement' => 'mailingstate',  // mailingstate ?
		//'sitewebgroupe' => 'websiteurl',
		//'origine' => 'leadsource',//TODO translate
		//'typeabonne' => '',//RSNAboRevues->type abonnement 0=standard, 1=ne pas abonner, 2=reçu fiscal seul, 3=Abonné gratuit, 4=Abonnement groupé
		//'typedegroupe' => 'rsngrptypes',
		//'descriptifgroupe' => 'grpdescriptif',
		//'cumul_nef' => '',
		//'nombredadherentsdugroupe' => 'grpnbremembres',
		//'evaladr' => '',
		//'charade' => '',
		//'statutnpai' => 'rsnnpai',
		//'datestatutnpai' => 'rsnnpaicomment', //bof
		//'date_finabn_rezo' => '',//RSNAboRevues finabo
		//'depotvente' => '',//type de contact += 'Dépôt-vente'
		//'datemodifadresse' => '',//TODO ? MAX(datemodifadresse, datemodification)
		//'typecontact' => 'isgroup',//
		//'pasautornvp' => '',// TODO
		//'pasrelancefinancierecourrier' => 'donotappeldoncourrier',
		//'pasrelanceabo' => 'donotrelanceabo',
		//'dateexportcogilog' => '',
		//'datetraitementrnvp' => '',
		//'top_rnvp' => '',
		//'pasderelancefinanciereweb' => 'donotappeldonweb',
		//'vtiger' => '',
		//'partenaire' => '',//si vrai accounttype += 'Partenaire'
		//'partenairedate' => 'datepartenariat',//à mettre dans partenairedescription si ! partenaire 
		//'signatairedate' => 'datesigncharte',
		//
	}
	
	/**
	 * Création de l'éventuel abonnement revue du contact nouvellement importé.
	 * @param $contactsData : the contact record model
	 * @param $contactsData : the data of the abonnement revue to import
	 */
	function importRSNAboRevuesForContact($contact, $contactsData) {
		global $VTIGER_BULK_SAVE_MODE;
		$VTIGER_BULK_SAVE_MODE = true;
	
		$fieldName = 'accounttype';
		$contactType = TYPE_CONTACT_SUPPRIME;
		if($record->get($fieldName) === $contactType) return;
				
		$typeAbonne = $contactsData[0]['typeabonne'];
		
		$doNotAbonnerToDay = $typeAbonne == _4D_TYPEABONNE_NEPASABONNER
				|| $typeAbonne == _4D_TYPEABONNE_RECUFISCALSEUL;
		$isAbonne = !$doNotAbonnerToDay && $contactsData[0]['abonne_rezo'];
		
		if($contactsData[0]['date_abn_rezo']){
			$dateDebut = $contactsData[0]['date_abn_rezo'];
			$dateDebut = new DateTime($dateDebut);
			$dateFin = $contactsData[0]['date_finabn_rezo'];
			if($dateFin){
				$dateFin = new DateTime($dateFin);
				if($isAbonne){
					$toDay = new DateTime('today');
					$isAbonne = $dateFin > $toDay;
				}
			}
			$aboRevue = $this->createRSNAboRevue($contact, $contactsData, TYPEABONNE_STANDARD, $isAbonne, $dateDebut, $dateFin);
		}
		elseif($typeAbonne == _4D_TYPEABONNE_ABOGRATUIT){
			$dateDebut = $contactsData[0]['datecreation'];
			$dateDebut = new DateTime($dateDebut);
			$isAbonne = true;
			$dateFin = null;
			$aboRevue = $this->createRSNAboRevue($contact, $contactsData, TYPEABONNE_ABOGRATUIT, $isAbonne, $dateDebut, $dateFin);
		}
		
		if($doNotAbonnerToDay){
			//ferme le précédent si il existe
			if(isset($aboRevue)
			&& $aboRevue)
				if($aboRevue->get('isabonne')){
					$aboRevue->set('mode','edit');
					$aboRevue->set('isabonne', false);
					$aboRevue->save();
					
					$prevDateFin = date('Y-m-d');
				}
				else
					$prevDateFin = $aboRevue->get('finabo');
			else
				$prevDateFin = false;
				
			//Abonnement de Non abonnement
			return $this->createRSNAboRevue($contact, $contactsData, TYPEABONNE_NEPASABONNER, false, $prevDateFin);
		}
		return $aboRevue;
	}
	
	/**
	 *
	 * @return Record_Model
	 */
	function createRSNAboRevue($contact, $contactsData, $typeAbo, $isAbonne, $dateDebut=false, $dateFin=false){
		
		$account = $contact->getAccountRecordModel();
		
		$record = Vtiger_Record_Model::getCleanInstance('RSNAboRevues');
		$record->set('mode', 'create');
		
		$record->set('assigned_user_id', ASSIGNEDTO_ALL);
		
		$fieldName = 'rsnabotype';
		$value = $typeAbo;
		$record->set($fieldName, $value);
		
		$fieldName = 'account_id';
		$value = $account->getId();
		$record->set($fieldName, $value);
		
		$fieldName = 'isabonne';
		$value = $isAbonne ? '1' : '0';
		$record->set($fieldName, $value);
		
		$fieldName = 'debutabo';
		$value = $dateDebut
			? (is_string($dateDebut)
			   ? $dateDebut
			   : $dateDebut->format('Y-m-d')
			) : null;
		$record->set($fieldName, $value);
		
		$fieldName = 'finabo';
		$value = $dateFin
			? (is_string($dateFin)
			   ? $dateFin
			   : $dateFin->format('Y-m-d')
			) : null;
		$record->set($fieldName, $value);
		
		$fieldName = 'finabo';
		$value = $dateFin ? $dateFin : null;
		$record->set($fieldName, $value);
		
		$fieldName = 'nbexemplaires';
		if ($typeAbo == 'Ne pas abonner')
			$value =0 ;
		else
			$value = $contactsData[0]['nbabosgroupes'] + 1;
		$record->set($fieldName, $value);
				
		$record->save();
		$record->set('mode', 'edit');
		
		return $record;
	}
	
	/**
	 * Crée un commentaire avec les données initiales de 4D
	 */
	function createInitialModComment($contact, $contactsData){
		$currentUserModel = Users_Record_Model::getCurrentUserModel();
		
		$record = Vtiger_Record_Model::getCleanInstance('ModComments');
		$record->set('mode', 'create');
		
		$text = 'Données 4D : ';
		
		$fieldName = 'accounttype';
		$contactType = TYPE_CONTACT_SUPPRIME;
		if($record->get($fieldName) == $contactType){
			$text .= "\nADRESSE SUPPRIMEE";
		}
		
		$fieldsMapping = $this->getContactsFieldsMapping();
		foreach($fieldsMapping as $srcFieldName => $fieldName)
			$text .= "\n- $srcFieldName = " . print_r($contactsData[0][$srcFieldName], true);
		
		$record->set('commentcontent', $text);
		$record->set('related_to', $contact->getId());
		
		$record->set('assigned_user_id', ASSIGNEDTO_ALL);
		$record->set('userid', $currentUserModel->getId());
		
		$record->save();
		
		return $record;
	}
	
	
	/**
	 * Method that pre import an invoice.
	 *  It adds one row in the temporary pre-import table by invoice line.
	 * @param $contactsData : the data of the invoice to import.
	 */
	function preImportContact($contactsData) {
		
		$contactsValues = $this->getContactsValues($contactsData);
		
		$contacts = new RSNImportSources_Preimport_Model($contactsValues, $this->user, 'Contacts');
		$contacts->save();
	}

	/**
	 * Method that retrieve a contact.
	 * @param string $ref4d : the ref4D ou le tableau de valeurs du contact
	 * @return the row data of the contact | null if the contact is not found.
	 */
	function getContact($ref4d) {
		$id = false;
		if(is_array($ref4d)){
			//$ref4d is $rsnprelvirementsData
			if($ref4d[0]['_contactid'])
				$id = $ref4d[0]['_contactid'];
			else{
				$ref4d = $ref4d[0]['reffiche'];
			}
		}
		if(!$id)
			$id = $this->getContactIdFromRef4D($ref4d);
		if($id){
			return Vtiger_Record_Model::getInstanceById($id, 'Contacts');
		}

		return null;
	}
	
	/**
	 * Method to parse the uploaded file and save data to the temporary pre-import table.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - true if pre-import is ended successfully
	 */
	function parseAndSaveFile(RSNImportSources_FileReader_Reader $fileReader) {
		$this->clearPreImportTable();
		
		if($fileReader->open()) {
			if ($this->moveCursorToNextContact($fileReader)) {
				$i = 0;
				do {
					$contact = $this->getNextContact($fileReader);
					if ($contact != null) {
						$this->preImportContact($contact);
					}
				} while ($contact != null);

			}

			$fileReader->close(); 
			return true;
		} else {
			//TODO: manage error
			echo "<code>le fichier n'a pas pu être ouvert...</code>";
		}
		return false;
	}

	/**
	 * Method called after the file is processed.
	 *  This method must be overload in the child class.
	 */
	function postPreImportData() {
		// Pré-identifie les contacts
		
		RSNImportSources_Utils_Helper::setPreImportDataContactIdByRef4D(
			$this->user,
			'Contacts',
			'reffiche',
			'_contactid'
		);
		
		return true;
	}
        
	/**
	 * Method that check if a string is a formatted date (DD/MM/YYYY).
	 * @param string $string : the string to check.
	 * @return boolean - true if the string is a date.
	 */
	function isDate($string) {
		//TODO do not put this function here ?
		return preg_match("/^[0-3]?[0-9][-\/][0-1]?[0-9][-\/](20)?[0-9][0-9]/", $string);//only true for french format
	}
	/**
	 * Method that returns a formatted date for mysql (Y-m-d).
	 * @param string $string : the string to format.
	 * @return string - formated date.
	 */
	function getMySQLDate($string) {
		if(!$string || $string === '00/00/00')
			return null;
		$dateArray = preg_split('/[-\/]/', $string);
		return '20'.$dateArray[2] . '-' . $dateArray[1] . '-' . $dateArray[0];
	}

	/**
	 * Method that check if a line of the file is a contact information line.
	 *  It assume that the line is a client information line only and only if the first data is a date.
	 * @param array $line : the data of the file line.
	 * @return boolean - true if the line is a contact information line.
	 */
	function isRecordHeaderInformationLine($line) {
		if (sizeof($line) > 0 && is_numeric($line[12]) && $this->isDate($line[16])) {
			return true;
		}

		return false;
	}

	/**
	 * Method that move the cursor of the file reader to the beginning of the next found invoice.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return boolean - false if error or if no invoice found.
	 */
	function moveCursorToNextContact(RSNImportSources_FileReader_Reader $fileReader) {
		do {
			$cursorPosition = $fileReader->getCurentCursorPosition();
			$nextLine = $fileReader->readNextDataLine($fileReader);
					
			if ($nextLine == false) {
				return false;
			}

		} while(!$this->isRecordHeaderInformationLine($nextLine));

		$fileReader->moveCursorTo($cursorPosition);

		return true;
	}

	/**
	 * Method that return the information of the next first invoice found in the file.
	 * @param RSNImportSources_FileReader_Reader $filereader : the reader of the uploaded file.
	 * @return the invoice information | null if no invoice found.
	 */
	function getNextContact(RSNImportSources_FileReader_Reader $fileReader) {
		$nextLine = $fileReader->readNextDataLine($fileReader);
		if ($nextLine != false) {
			$contact = array(
				'header' => $nextLine,
				'detail' => array());
			do {
				$cursorPosition = $fileReader->getCurentCursorPosition();
				$nextLine = $fileReader->readNextDataLine($fileReader);

				if (!$this->isRecordHeaderInformationLine($nextLine)) {
					if ($nextLine[1] != null && $nextLine[1] != '') {
						//impossible ici array_push($contact['detail'], $nextLine);
					}
				} else {
					break;
				}

			} while ($nextLine != false);

			if ($nextLine != false) {
				$fileReader->moveCursorTo($cursorPosition);
			}
			return $contact;
		}

		return null;
	}
	
	/**
	 * Method that return the formated information of an invoice found in the file.
	 * @param $contacts : the invoice data found in the file.
	 * @return array : the formated data of the invoice.
	 */
	function getContactsValues($contacts) {

		$fields = $this->getContactsFields();
		
		// contrôle l'égalité des tailles de tableaux
		if(count($fields) != count($contacts['header'])){
			if(count($fields) > count($contacts['header']))
				$contacts['header'] = array_merge($contacts['header'], array_fill (0, count($fields) - count($contacts['header']), null));
			else
				$contacts['header'] = array_slice($contacts['header'], 0, count($fields));
		}
		//tableau associatif dans l'ordre fourni
		$contactsHeader = array_combine($fields, $contacts['header']);
		
		//Parse dates
		foreach($this->getContactsDateFields() as $fieldName)
			$contactsHeader[$fieldName] = $this->getMySQLDate($contactsHeader[$fieldName]);
		
		$fieldName = 'remarque';
		if($contactsHeader[$fieldName])
			$contactsHeader[$fieldName] = str_replace(array("\\r", "\\t"), array("\r", "\t"), $contactsHeader[$fieldName]);
		
		$fieldName = 'datecreation';
		if(!$contactsHeader[$fieldName])
			$contactsHeader[$fieldName] = '2000-01-01';
		
		return $contactsHeader;
	}
	
	function getTypeAbonneFrom4D($typeAbonne){
		switch($typeAbonne){
			case _4D_TYPEABONNE_STANDARD:
				return TYPEABONNE_STANDARD;
			case _4D_TYPEABONNE_NEPASABONNER:
				return TYPEABONNE_NEPASABONNER;
			case _4D_TYPEABONNE_RECUFISCALSEUL:
				return TYPEABONNE_RECUFISCALSEUL;
			case _4D_TYPEABONNE_ABOGRATUIT:
				return TYPEABONNE_ABOGRATUIT;
			case _4D_TYPEABONNE_ABOGROUPE:
				return TYPEABONNE_ABOGROUPE;
			default:
				return $typeAbonne;
		}
	}
}
