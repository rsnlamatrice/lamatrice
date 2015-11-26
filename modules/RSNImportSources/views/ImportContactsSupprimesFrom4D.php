<?php


/* Phase de migration
 * Importation des contacts SUPPRIMES Web depuis le fichier provenant de 4D
 */
class RSNImportSources_ImportContactsSupprimesFrom4D_View extends RSNImportSources_ImportContactsFrom4D_View {
        
	/**
	 * Method to get the source import label to display.
	 * @return string - The label.
	 */
	public function getSource() {
		return 'LBL_CONTACTSSUPPRIMES_4D';
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
			
			//absents de la source pour les adresses supprimées
			
			'typecontact' => 'isgroup',//
			'pasautornvp' => '',// TODO
			'pasrelancefinancierecourrier' => '',
			'pasrelanceabo' => '',
			'dateexportcogilog' => '',
			'datetraitementrnvp' => '',
			'top_rnvp' => '',
			'pasderelancefinanciereweb' => '',
			'vtiger' => '',
			'partenaire' => '',//si vrai accounttype += 'Partenaire'
			'partenairedate' => '',//à mettre dans partenairedescription si ! partenaire 
			'signatairedate' => '',
			
			//champ supplémentaire
			'_contactid' => '', //Massively updated after preImport
		);
			//donotcourrierag, rsnwebhide dans import Groupe ?
	}
	
	function getContactsDateFields(){
		return array(
			'datecreation', 'datemodification', 'date_abn_rezo', 'datestatutnpai', 'date_finabn_rezo'
			, 'datemodifadresse'
		);
	}
	

	//Mise à jour des données du record model nouvellement créé à partir des données d'importation
	protected function updateContactRecordModelFromData($record, $contactsData){
		$contactsData[0]['typeabonne'] = _4D_TYPEABONNE_RECUFISCALSEUL;
		$contactsData[0]['typecontact'] = $contactsData[0]['typedegroupe'] ? 1 : 0;
		
		parent::updateContactRecordModelFromData($record, $contactsData);
		
		$fieldName = 'accounttype';
		$contactType = TYPE_CONTACT_SUPPRIME;
		//set
		$record->set($fieldName, $contactType);
		
		/* "ne pas" partout */
		foreach( array('emailoptout', 'donotcall', 'donotprospect', 'donotrelanceadh', 'donotappeldoncourrier', 'donotrelanceabo', 'donotappeldonweb')
				as $fieldName){
			$record->set($fieldName, 1);
		}
		
		$fieldName = 'lastname';
		if($record->get($fieldName) && $record->get('firstname') != $record->get($fieldName))
			$record->set('firstname', trim( $record->get('firstname') . ' ' . $record->get($fieldName)));
		$record->set($fieldName, '[SUPPRIMÉ(E)] '.$contactsData[0]['reffiche']);
		
		
		$fieldName = 'mailingzip';
		$record->set($fieldName, null);
		
		$fieldName = 'mailingcity';
		$record->set($fieldName, null);
	}
}
