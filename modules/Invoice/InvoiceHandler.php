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
	
	
    
	/* ED150507 Règles de gestion lors de la validation d'une facture */
	public function handleAfterSaveInvoiceEvent($entity, $moduleName){
		$invoiceId = $entity->getId();
		$data = $entity->getData();
		$invoice = Vtiger_Record_Model::getInstanceById($invoiceId, $moduleName);
		$lineItems = $invoice->getProducts();
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
		die();
	}
	/* ED150507 Règles de gestion lors de la validation d'une facture, article d'abonnement */
	public function handleAfterSaveInvoiceAbonnementsEvent($invoice, $categoryItems){
		$productModel = Vtiger_Module_Model::getInstance('Products');
		$prochaineRevue = $productModel->getProchaineRevue();
		if(!$prochaineRevue){
			//TODO Alert
			return;
		}
		$account = Vtiger_Record_Model::getInstanceById($invoice->get('account_id'), 'Accounts');
		if(!$account){
			//TODO Alert
			return;
		}
		$invoiceId = $invoice->getId();
		$invoiceDate = new DateTime($invoice->get('invoicedate'));
		$rsnAboRevues = $account->getRSNAboRevues();
		$abonneAVie = false;
		$startDateOfNextAbo = false;
		$rsnAboRevueCourant = false;
		var_dump('$account->getRSNAboRevues()', count($rsnAboRevues));
		if($rsnAboRevues){
			foreach($rsnAboRevues as $rsnaborevuesId=>$rsnAboRevue){
				if($rsnAboRevue->get('sourceid') == $invoiceId){
					//TODO Alert
					return;
				}
			}
			foreach($rsnAboRevues as $rsnaborevuesId=>$rsnAboRevue){
				$isAbonne = $rsnAboRevue->isAbonne();
				if($isAbonne){
					if($rsnAboRevue->isTypeAbonneAVie()){
						$rsnAboRevueCourant = $rsnAboRevue;
						$abonneAVie = true;
						break;
					}
					else {
						$rsnAboRevueCourant = $rsnAboRevue;
						$startDateOfNextAbo = $rsnAboRevueCourant->getStartDateOfNextAbo($prochaineRevue, $invoiceDate);
						var_dump('$startDateOfNextAbo', $startDateOfNextAbo);
						break;
					}
				}
				elseif($rsnAboRevue->isNePasAbonner()){
					return;
				}
			}
		}
		var_dump('$rsnAboRevueCourant', $rsnAboRevueCourant ? 'oui' : 'non');
		foreach($categoryItems as $nLine => $lineItem){
			$productCode = $lineItem['hdnProductcode'.$nLine];
			$addMonths = 0;
			$addMaxDate = false;
			$aboType = $productCode;
			switch($productCode){
			case 'RABO': //Abonnement d'un an à la revue
			case 'RABOS': //Abonnement de soutien d'un an à la revue
				$aboType = RSNABOREVUES_TYPE_ABO_PAYANT; //TODO Distinguer "de soutien" ?
				//TODO $dateFin dépend de l'historique actuel
				$dateDebut = $startDateOfNextAbo ? $startDateOfNextAbo : new DateTime();
				$dateFin = clone new $dateDebut; // $prochaineRevue->get('sales_start_date')
				$dateFin->modify( '+1 month' )->modify( '+1 year' );
				
				if($abonneAVie){
					$rsnAboRevueCourant->set('isabonne', false);
					$rsnAboRevueCourant->save();
				}
				else if($rsnAboRevueCourant
					&& $rsnAboRevueCourant->isAbonne()
					&& (!$rsnAboRevueCourant->getFinAbo()
					    || $rsnAboRevueCourant->getFinAbo() <= $dateDebut)){
						$rsnAboRevueCourant->set('isabonne', 0);
						if(!$rsnAboRevueCourant->getFinAbo())
							$rsnAboRevueCourant->setFinAbo($dateDebut);
						$rsnAboRevueCourant->set('comment', $rsnAboRevueCourant->get('comment') . '- Suite à facture ' . $invoice->get('invoice_no'));
						$rsnAboRevueCourant->save();
					}
				break;
			case 'RSGR': //Abonnements groupés
				//TODO Que faire si abonnement en cours avec un autre nombre d'exemplaires ?
				$addMonths = 12;
				$aboType = RSNABOREVUES_TYPE_ABO_GROUPE;
				break;
			case 'RABOG': //Abonnement gratuit d'un n° à la revue
				if($abonneAVie)
					continue;
				//TODO vérifier si c'est un n° ou 1 an
				$aboType = RSNABOREVUES_TYPE_NUM_DECOUVERTE;
				$dateDebut = $startDateOfNextAbo ? $startDateOfNextAbo : new DateTime();
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
				$aboRevue->set('isabonne', 1);
				$aboRevue->setDebutAbo($dateDebut);
				$aboRevue->setFinAbo($dateFin);
				$aboRevue->set('rsnabotype', $aboType);
				$aboRevue->set('nbexemplaires', 1);
				
				$aboRevue->save();
				$aboRevue->set('mode', '');
				var_dump('$aboRevue', 'nouveau $aboRevue');
				break;
			}
		}
		return $aboRevue;
	}
	
	/* ED150507 Règles de gestion lors de la validation d'une facture, d'après le montant total */
	public function handleAfterSaveInvoiceTotalEvent($invoice, $invoiceData, $lineItems){
	}
}