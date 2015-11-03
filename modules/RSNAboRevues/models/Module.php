<?php
/*+***********************************************************************************
 * 
 *************************************************************************************/

require_once 'modules/RSNAboRevues/models/Record.php';//constantes
/**
 * RSNAboRevues Module Model Class
 */
class RSNAboRevues_Module_Model extends Vtiger_Module_Model {
	/**
	 * Function to get Alphabet Search Field 
	 */
	public function getAlphabetSearchField(){
		return 'rsnabotype,isabonne';
	}
	
	
	
	/**
	 * Parcourt une collection d'abonnement pour repérer une association préalable à un enregistrement source
	 * Par exemple, une facture qui aurait déjà générer un abonnement.
	 */
	public function isRecordAlreadyRelatedWithAboRevue(&$rsnAboRevues, $recordId){
		//Contrôle si cette facture a déjà généré un abonnement
		foreach($rsnAboRevues as $rsnaborevuesId=>$rsnAboRevue){
			if($rsnAboRevue->get('sourceid') == $recordId){
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Parcourt l'historique pour clôturer les en-cours périmés
	 */
	public function check_IsAbonne_vs_DateFin(&$rsnAboRevues){
		global $log;
		$toDay = new DateTime();
		foreach($rsnAboRevues as $rsnaborevuesId=>$rsnAboRevue){
			if( $rsnAboRevue->isAbonne()
			&& $rsnAboRevue->getFinAbo()
			&& $rsnAboRevue->getFinAbo() < $toDay){
				$log->debug("RSNAboRevues_Module_Model::check_IsAbonne_vs_DateFin abonnement périmé");
				$rsnAboRevue->setAbonne(false);
				$rsnAboRevue->set('mode', 'edit');
				$rsnAboRevue->save();
			}
		}
	}	
	
	/**
	 * Création d'un abonnement
	 */
	public function createAboRevue($sourceRecordModel, $account, $dateDebut = false, $dateFin = false, $nbExemplaires = 1, $aboType = false){
		global $log;
		$toDay = new DateTime();
		
		if(!$dateDebut)
			$dateDebut = $toDay;
		
		if(is_numeric($dateFin)){
			$nbMonths = $dateFin;
			$dateFin = clone $dateDebut;
			$dateFin->modify( '+' . $nbMonths . ' month' );
		}
		
		if(is_object($sourceRecordModel))
			$sourceId = $sourceRecordModel->getId();
		else
			$sourceId = $sourceRecordModel;
			
		if(is_object($account))
			$accountId = $account->getId();
		else
			$accountId = $account;
		if(!$aboType)
			$aboType = RSNABOREVUES_TYPE_NUM_DECOUVERTE;
			
		$aboRevue = Vtiger_Record_Model::getCleanInstance('RSNAboRevues');
		$aboRevue->set('mode', 'create');
		$aboRevue->set('account_id', $accountId);
		$aboRevue->set('sourceid', $sourceId);
		$aboRevue->setAbonne(!$dateFin || ($dateFin >= $toDay && $dateFin > $dateDebut));
		$aboRevue->setDebutAbo($dateDebut);
		$aboRevue->setFinAbo($dateFin);
		$aboRevue->set('rsnabotype', $aboType);
		$aboRevue->set('nbexemplaires', $nbExemplaires);
		
		$aboRevue->save();
		$aboRevue->set('mode', 'edit');
		$log->debug("RSNAboRevues_Module_Model::createAboRevue 'nouveau $aboRevue->getLabel()'");
		return $aboRevue;
	}
	
	/** Génération d'un abonnement pour les personnes en prélèvement
	 */
	public function ensureAboRevue($account, $nbMonths, $aboType, $sourceRecordModel = false){
		$toDay = new DateTime();
		$dateFin = new DateTime();
		$dateFin->modify( '+' . $nbMonths . ' month' );
		
		if(is_numeric($account))
			$account = Vtiger_Record_Model::getInstanceById($account, 'Accounts');
			
		$rsnAboRevues = $account->getRSNAboRevues();
		self::check_IsAbonne_vs_DateFin($rsnAboRevues);
		
		foreach($rsnAboRevues as $rsnaborevuesId=>$rsnAboRevue){//first only
			if($rsnAboRevue->isTypeNePasAbonner())
				return false;
			if( $rsnAboRevue->isAbonne()){
				if($rsnAboRevue->getFinAbo()
				&& $rsnAboRevue->getFinAbo() < $dateFin){
					$rsnAboRevue->set('mode', 'edit');
					$rsnAboRevue->setFinAbo($dateFin);
					$rsnAboRevue->save();
				}
				return $rsnAboRevue;
			}
			break;
		}
			
		return $this->createAboRevue($sourceRecordModel, $account, $toDay, $dateFin, 1, $aboType);
	}
}
