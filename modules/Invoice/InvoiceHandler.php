<?php
/* +***********************************************************************************
 * ED150418
 * Actions suite à la validation d'une facture
 * Règle de gestion
 * 	- Le montant de la facture dépasse 35 € : un n° de la revue "Merci de votre soutien" offert, création d'un record RSNAboRevues
 * 	- Article d'abonnement : création d'un record RSNAboRevues
 * 	- Article d'adhésion : TODO
 * *********************************************************************************** */

// Method used in Workflow (Liste des gestionnaires de flux)
//function handleRSNInvoiceSaved($entity){
	//var_dump($entity->data['LineItems']);
	//die(__FILE__.'  ICICI CI CI CI CI C');
//}

// 2nd method : handler
// Method registered as EntityMethod. See modules\RSN\RSN.php->add_invoice_handler()
require_once 'include/events/VTEventHandler.inc';
require_once 'modules/RSNAboRevues/models/Record.php';
class RSNInvoiceHandler extends VTEventHandler {

	function handleEvent($eventName, $entity) {
		
		global $log, $adb;

		switch($eventName){
		case 'vtiger.entity.aftersave':
			$moduleName = $entity->getModuleName();
			switch ($moduleName){
			case 'Invoice' :
				$this->handleAfterSaveInvoiceEvent($entity, $moduleName);
				break;
			}
			break;
		}
	}
	    
	/* ED150507 Règles de gestion lors de la validation d'une facture
	*/
	public function handleAfterSaveInvoiceEvent($entity, $moduleName){
		global $log;
		$log->debug("IN handleAfterSaveInvoiceEvent");
		$invoiceId = $entity->getId();
		$data = $entity->getData();
		$invoice = Vtiger_Record_Model::getInstanceById($invoiceId, $moduleName);
		$lineItems = $invoice->getProducts();
		$log->debug("handleAfterSaveInvoiceEvent lineItems " . print_r($lineItems, true));
		$categories = array(); //items by category
		$invoiceData = false; //first item
		foreach($lineItems as $nLine => $lineItem){
			if(!$invoiceData)
				$invoiceData = $lineItem;
			$productCategory = $lineItem['hdnProductCategory'.$nLine];
			if(!array_key_exists($productCategory, $categories))
				$categories[$productCategory] = array();
			$categories[$productCategory][$nLine] = $lineItem;
		}
		
		$log->debug("handleAfterSaveInvoiceEvent Categories " . print_r(array_keys($categories), true));
		
		foreach($categories as $productCategory => $categoryItems)
			switch($productCategory){
			case 'Abonnement' :
				$this->handleAfterSaveInvoiceAbonnementsEvent($invoice, $categoryItems);
				break;
			default:
				break;
			}
		if($invoiceData){
			$this->handleAfterSaveInvoiceTotalEvent($invoice, $invoiceData, $lineItems);
		}
		$log->debug("OUT handleAfterSaveInvoiceEvent");
	}
	
	/* ED150507 Règles de gestion lors de la validation d'une facture, article d'abonnement
	*/
	public function handleAfterSaveInvoiceAbonnementsEvent($invoice, $categoryItems){
		global $log;
		$log->debug("IN handleAfterSaveInvoiceAbonnementsEvent");
		
		$productModel = Vtiger_Module_Model::getInstance('Products');
		$prochaineRevue = $productModel->getProchaineRevue();
		if(!$prochaineRevue){
			$log->debug("handleAfterSaveInvoiceAbonnementsEvent, pas de prochaineRevue définie");
			//TODO Alert
			return;
		}
		$account = Vtiger_Record_Model::getInstanceById($invoice->get('account_id'), 'Accounts');
		if(!$account){
			$log->debug("handleAfterSaveInvoiceAbonnementsEvent, pas de compte défini");
			//TODO Alert
			return;
		}
		$invoiceId = $invoice->getId();
		$toDay = new DateTime();
		$invoiceDate = new DateTime($invoice->get('invoicedate'));
		$rsnAboRevues = $account->getRSNAboRevues();
		$abonneAVie = false;
		$startDateOfNextAbo = false;
		$rsnAboRevueCourant = false;
		if($rsnAboRevues){
			//Contrôle si cette facture a déjà généré un abonnement
			foreach($rsnAboRevues as $rsnaborevuesId=>$rsnAboRevue){
				if($rsnAboRevue->get('sourceid') == $invoiceId){
					$log->debug("handleAfterSaveInvoiceAbonnementsEvent, cette facture a déjà généré un abonnement");
					//TODO Alert
					return;
				}
			}
			//Parcourt l'historique pour clôturer les en-cours périmés
			foreach($rsnAboRevues as $rsnaborevuesId=>$rsnAboRevue){
				if( $rsnAboRevue->isAbonne()
				&& $rsnAboRevue->getFinAbo()
				&& $rsnAboRevue->getFinAbo() < $toDay){
					$log->debug("handleAfterSaveInvoiceAbonnementsEvent rsnAboRevue abonnement périmé");
					$rsnAboRevue->setAbonne(false);
					$rsnAboRevue->set('mode', 'edit');
					$rsnAboRevue->save();
				}
			}
			//Parcourt l'historique par date décroissante
			foreach($rsnAboRevues as $rsnaborevuesId=>$rsnAboRevue){
				$isAbonne = $rsnAboRevue->isAbonne();
				if($isAbonne){
					if($rsnAboRevue->isTypeAbonneAVie()){
						$rsnAboRevueCourant = $rsnAboRevue;
						$abonneAVie = true;
						$log->debug("handleAfterSaveInvoiceAbonnementsEvent, abonneAVie");
						break;
					}
					else {
						$rsnAboRevueCourant = $rsnAboRevue;
						$startDateOfNextAbo = $rsnAboRevueCourant->getStartDateOfNextAbo($prochaineRevue, $invoiceDate);
						$log->debug("handleAfterSaveInvoiceAbonnementsEvent startDateOfNextAbo = " .($startDateOfNextAbo->format('d/m/Y')));
						break;
					}
				}
				elseif($rsnAboRevue->isTypeNePasAbonner()){
					$log->debug("handleAfterSaveInvoiceAbonnementsEvent, isTypeNePasAbonner");
					return;
				}
			}
		}
		
		//Décompte des abonnements groupés
		$nbExemplairesGroupes = 0;
		foreach($categoryItems as $nLine => $lineItem){
			switch($productCode){
			case 'RABO': //Abonnement d'un an à la revue
			case 'RABOS': //Abonnement de soutien d'un an à la revue				
				break;
			case 'RSGR': //Abonnements groupés
				$nbExemplairesGroupes += $lineItem['qty'.$nLine];
				break;
			default:
				//TODO What to do ?
				continue;
			}
		}
		
		$log->debug("handleAfterSaveInvoiceAbonnementsEvent rsnAboRevueCourant = " .($rsnAboRevueCourant ? 'oui' : 'non'));
		foreach($categoryItems as $nLine => $lineItem){
			$productCode = $lineItem['hdnProductcode'.$nLine];
			$addMonths = 0;
			$addMaxDate = false;
			$aboType = $productCode;
			$nbExemplaires = 1;
			switch($productCode){
			case 'RABO': //Abonnement d'un an à la revue
			case 'RABOS': //Abonnement de soutien d'un an à la revue
				if($nbExemplairesGroupes){
					$nbExemplaires += $nbExemplairesGroupes;
					$nbExemplairesGroupes = 0;
					$aboType = RSNABOREVUES_TYPE_ABO_GROUPE;
				}
				else
					$aboType = RSNABOREVUES_TYPE_ABO_PAYANT; //TODO Distinguer "de soutien" ?
				//TODO $dateFin dépend de l'historique actuel
				$dateDebut = $startDateOfNextAbo && $startDateOfNextAbo > $invoiceDate ? $startDateOfNextAbo : $invoiceDate;
				$dateFin = clone $dateDebut; // $prochaineRevue->get('sales_start_date')
				$dateFin->modify( '+1 month' )->modify( '+1 year' );
				
				$log->debug("handleAfterSaveInvoiceAbonnementsEvent startDateOfNextAbo = " . ($startDateOfNextAbo ? $startDateOfNextAbo->format('d/m/Y') : 'false')
					    . ", dateDebut = " . $dateDebut->format('d/m/Y') . ' (' . ($dateDebut === $dateFin ? "===" : "!!!") . ')'
					    . ", dateFin = " . $dateFin->format('d/m/Y') . ' (' . get_class($dateFin) . ')');
				
				//Puisque le contact paye un abonnement, on arrête son abonnement à vie
				if($abonneAVie){
					$rsnAboRevueCourant->set('mode', 'edit');
					$rsnAboRevueCourant->set('comment', $rsnAboRevueCourant->get('comment') . '- Fin suite à facture ' . $invoice->get('invoice_no'));
					$rsnAboRevueCourant->setAbonne(false);
					$rsnAboRevueCourant->save();
				}
				else if($rsnAboRevueCourant
					&& $rsnAboRevueCourant->isAbonne()
					&& (   !$rsnAboRevueCourant->getFinAbo()
					    || $rsnAboRevueCourant->getFinAbo() < $dateDebut)){
						$rsnAboRevueCourant->set('mode', 'edit');
						$rsnAboRevueCourant->setAbonne(false);
						if(!$rsnAboRevueCourant->getFinAbo())
							$rsnAboRevueCourant->setFinAbo($dateDebut);
						$log->debug("handleAfterSaveInvoiceAbonnementsEvent rsnAboRevueCourant => fin d'abonnement " . $rsnAboRevueCourant->getId()
							    . ", mode : " . $rsnAboRevueCourant->get('mode')
							    . ", date : " . $rsnAboRevueCourant->getFinAbo()->format('d/m/Y'));
						$rsnAboRevueCourant->set('comment', $rsnAboRevueCourant->get('comment') . '- Suite à facture ' . $invoice->get('invoice_no'));
						$rsnAboRevueCourant->save();
					}
				break;
			case 'RSGR': //Abonnements groupés
				//compté au préalable
				continue;
			case 'RABOG': //Abonnement gratuit d'un n° à la revue
				if($abonneAVie)
					continue;
				//TODO vérifier si c'est un n° ou 1 an
				$aboType = RSNABOREVUES_TYPE_NUM_DECOUVERTE;
				$dateDebut = $startDateOfNextAbo ? $startDateOfNextAbo : $invoiceDate;
				$dateFin = new DateTime();
				$dateFin->modify( '+3 month' );
				//L'abonnement actuel finit dans plus de 3 mois
				if($dateDebut > $dateFin)
					continue;
				break;
			case 'RPARR': //Abonnement de parrainage d'un an à la revue
				$addMonths = 12;
				$aboType = RSNABOREVUES_TYPE_ABO_PARRAINE;
				//TODO Create Contact + Account + RSNAboRevue
				//TODO Add Flag "Parrain" to Contact
				continue;
			default:
				//TODO What to do ?
				continue;
			}
			if($dateFin){
				
				$aboRevue = Vtiger_Record_Model::getCleanInstance('RSNAboRevues');
				$aboRevue->set('mode', 'create');
				$aboRevue->set('account_id', $account->getId());
				$aboRevue->set('sourceid', $invoiceId);
				$aboRevue->setAbonne(!$dateFin || $dateFin > $today);
				$aboRevue->setDebutAbo($dateDebut);
				$aboRevue->setFinAbo($dateFin);
				$aboRevue->set('rsnabotype', $aboType);
				$aboRevue->set('nbexemplaires', $nbExemplaires);
				
				$aboRevue->save();
				$aboRevue->set('mode', '');
				$log->debug("handleAfterSaveInvoiceAbonnementsEvent 'nouveau $aboRevue->getLabel()'");
				break;
			}
		}
		
		if($nbExemplairesGroupes){
			//TODO 
			$log->debug("handleAfterSaveInvoiceAbonnementsEvent : Attention, abonnements supplémentaires sans abonnement principal");
			
		}
		
		
		$log->debug("OUT handleAfterSaveInvoiceAbonnementsEvent");
		return $aboRevue;
	}
	
	/* ED150507 Règles de gestion lors de la validation d'une facture, d'après le montant total */
	public function handleAfterSaveInvoiceTotalEvent($invoice, $invoiceData, $lineItems){
		global $log;
		$log->debug("IN handleAfterSaveInvoiceTotalEvent");
		
		$log->debug("OUT handleAfterSaveInvoiceTotalEvent");
	}
}