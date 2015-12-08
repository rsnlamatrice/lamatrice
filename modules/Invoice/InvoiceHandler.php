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
require_once 'modules/RSNAboRevues/models/Module.php';
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
		
		$account = Vtiger_Record_Model::getInstanceById($invoice->get('account_id'), 'Accounts');
		if(!$account){
			$log->debug("handleAfterSaveInvoiceEvent, pas de compte défini");
			//TODO Alert
			return;
		}
		
		$lineItems = $invoice->getProducts();
		$log->debug("handleAfterSaveInvoiceEvent lineItems " . print_r($lineItems, true));
		$categories = array(); //items by category
		$invoiceData = false; //first item
		$totalDons = 0;
		foreach($lineItems as $nLine => $lineItem){
			if(!$invoiceData)
				$invoiceData = $lineItem;
			$productCategory = html_entity_decode($lineItem['hdnProductCategory'.$nLine]);
			if(!array_key_exists($productCategory, $categories))
				$categories[$productCategory] = array();
			$categories[$productCategory][$nLine] = $lineItem;
				
			if($productCategory == 'Dons'){
				$totalDons += (float)$lineItem[ 'netPrice'.$nLine ];
			}
		}
		
		$log->debug("handleAfterSaveInvoiceEvent Categories " . print_r(array_keys($categories), true));
		
		//Traitement par catégorie de produits
		//Abonnements
		foreach($categories as $productCategory => $categoryItems)
			switch($productCategory){
			case 'Abonnement' :
				$this->handleAfterSaveInvoiceAbonnementsEvent($invoice, $categoryItems, $account);
				break;
			default:
				break;
			}
		//Autres, après les abonnements
		foreach($categories as $productCategory => $categoryItems)
			switch($productCategory){
			case 'Adhésion' :
				$this->handleAfterSaveInvoiceAdhesionEvent($invoice, $categoryItems, $account);
				break;
			default:
				break;
			}
		//Sur le critère du montant total de la facture
		if($invoiceData
		&& $invoice->get('typedossier') !== 'Facture de dépôt-vente'
		&& $invoice->get('typedossier') !== to_html('Facture de dépôt-vente')){
			$this->handleAfterSaveInvoiceTotalEvent($invoice, $invoiceData, $lineItems, $account, $totalDons);
		}
		$log->debug("OUT handleAfterSaveInvoiceEvent");
	}
	
	/* ED150507 Règles de gestion lors de la validation d'une facture, article d'adhésion
	 * Abonnement d'un an
	 */
	public function handleAfterSaveInvoiceAdhesionEvent($invoice, $categoryItems, $account){
		global $log;
		$log->debug("IN handleAfterSaveInvoiceAdhesionEvent");
		
		$toDay = new DateTime();
		$invoiceDate = new DateTime($invoice->get('invoicedate'));
		$rsnAboRevues = $account->getRSNAboRevues();
		
		//Contrôle si cette facture a déjà généré un abonnement
		if(self::isInvoiceAlreadyRelatedWithAboRevue($rsnAboRevues, $invoice->getId())){
			$log->debug("handleAfterSaveInvoiceAdhesionEvent, cette facture a déjà généré un abonnement");
			return;
		}
		
		//Parcourt l'historique pour clôturer les en-cours périmés
		self::check_IsAbonne_vs_DateFin($rsnAboRevues);
		
		//Parcourt l'historique par date décroissante
		foreach($rsnAboRevues as $rsnaborevuesId=>$rsnAboRevue){
			if($rsnAboRevue->isAbonne()){
				if($rsnAboRevue->isTypeAbonneAVie()){
					$log->debug("handleAfterSaveInvoiceAdhesionEvent, isTypeAbonneAVie");
					return;
				}
				else {
					$rsnAboRevueCourant = $rsnAboRevue;
					break;
				}
			}
			elseif($rsnAboRevue->isTypeNePasAbonner()){
				$log->debug("handleAfterSaveInvoiceAdhesionEvent, isTypeNePasAbonner");
				return;
			}
		}
		if($rsnAboRevueCourant){
			$dateDebut = $rsnAboRevueCourant->getFinAbo();
		}
		else {
			$dateDebut = $invoiceDate;
		}
		$dateFin = self::getDateFinAbo($dateDebut, 12);
		
		if($dateFin && $dateFin > $toDay){
			$aboRevue = $this->createAboRevue($invoice, $account, $dateDebut, $dateFin, $nbExemplaires, RSNABOREVUES_TYPE_ABO_ADHERENT);
		}
		$log->debug("OUT handleAfterSaveInvoiceAdhesionEvent");
	}
	
	/**
	 * Création d'un abonnement
	 */
	public function createAboRevue($invoice, $account, $dateDebut, $dateFin, $nbExemplaires, $aboType){
		$aboRevueModuleModel = Vtiger_Module_Model::getInstance('RSNAboRevues');
		return $aboRevueModuleModel->createAboRevue($invoice, $account, $dateDebut, $dateFin, $nbExemplaires, $aboType);
	}
	
	/* ED150507 Règles de gestion lors de la validation d'une facture, article d'abonnement
	*/
	public function handleAfterSaveInvoiceAbonnementsEvent($invoice, $categoryItems, $account){
		global $log;
		$log->debug("IN handleAfterSaveInvoiceAbonnementsEvent");
		
		$invoiceId = $invoice->getId();
		$rsnAboRevues = $account->getRSNAboRevues();
		if($rsnAboRevues){
			//Contrôle si cette facture a déjà généré un abonnement
			if(self::isInvoiceAlreadyRelatedWithAboRevue($rsnAboRevues, $invoiceId)){
				$log->debug("handleAfterSaveInvoiceAdhesionEvent, cette facture a déjà généré un abonnement");
				return;
			}
		}
		$productModel = Vtiger_Module_Model::getInstance('Products');
		$prochaineRevue = $productModel->getProchaineRevue();
		if(!$prochaineRevue){
			$log->debug("handleAfterSaveInvoiceAbonnementsEvent, pas de prochaineRevue définie");
			//TODO Alert
			return;
		}
		$toDay = new DateTime();
		$invoiceDate = new DateTime($invoice->get('invoicedate'));
		$abonneAVie = false;
		$startDateOfNextAbo = false;
		$rsnAboRevueCourant = false;
		if($rsnAboRevues){
			
			//Parcourt l'historique pour clôturer les en-cours périmés
			self::check_IsAbonne_vs_DateFin($rsnAboRevues);
			
			//Parcourt l'historique par date décroissante
			foreach($rsnAboRevues as $rsnaborevuesId=>$rsnAboRevue){
				if($rsnAboRevue->isAbonne()){
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
			$productCode = $lineItem['hdnProductcode'.$nLine];
			if($productCode === 'RSGR'){ //Abonnements groupés (supplémentaires)
				$nbExemplairesGroupes += $lineItem['qty'.$nLine];
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
				$dateFin = self::getDateFinAbo($dateDebut, 12);
				
				$log->debug("handleAfterSaveInvoiceAbonnementsEvent startDateOfNextAbo = " . ($startDateOfNextAbo ? $startDateOfNextAbo->format('d/m/Y') : 'false')
					    . ", dateDebut = " . $dateDebut->format('d/m/Y') . ' (' . ($dateDebut === $dateFin ? "===" : "!!!") . ')'
					    . ", dateFin = " . $dateFin->format('d/m/Y') . ' (' . get_class($dateFin) . ')');
				
				if($abonneAVie){
					//Puisque le contact paye un abonnement, on arrête son abonnement à vie
					$rsnAboRevueCourant->set('mode', 'edit');
					$rsnAboRevueCourant->set('comment', $rsnAboRevueCourant->get('comment') . '- Fin suite à facture ' . $invoice->get('invoice_no'));
					$rsnAboRevueCourant->setAbonne(false);
					$rsnAboRevueCourant->save();
				}
				elseif($rsnAboRevueCourant
					&& $rsnAboRevueCourant->isAbonne()
					&& (   $rsnAboRevueCourant->isTypeDecouverte()
					    || $rsnAboRevueCourant->isTypeMerciSoutien()
					    || !$rsnAboRevueCourant->getFinAbo()
					    || $rsnAboRevueCourant->getFinAbo() < $dateDebut)
					){
						//Clôture pour laisser la place au payant
						$rsnAboRevueCourant->set('mode', 'edit');
						$rsnAboRevueCourant->setAbonne(false);
						if(!$rsnAboRevueCourant->getFinAbo())
							$rsnAboRevueCourant->setFinAbo($dateDebut);
						$log->debug("handleAfterSaveInvoiceAbonnementsEvent rsnAboRevueCourant => fin d'abonnement " . $rsnAboRevueCourant->getId()
							    . ", mode : " . $rsnAboRevueCourant->get('mode')
							    . ", date : " . $rsnAboRevueCourant->getFinAbo()->format('d/m/Y'));
						$rsnAboRevueCourant->set('comment', $rsnAboRevueCourant->get('comment') . '- Suite à facture ' . $invoice->get('invoice_no'));
						$rsnAboRevueCourant->save();
						
						$dateDebut = $invoiceDate;
						$dateFin = self::getDateFinAbo($dateDebut, 12);
					}
				break;
			case 'RSGR': //Abonnements groupés
				//compté au préalable
				continue;
			case 'RABOG': //Abonnement gratuit d'un n° à la revue
				if($abonneAVie){
					$dateFin = false;
					continue;//! ne sort pas du foreach
				}
				//TODO vérifier si c'est un n° ou 1 an
				$aboType = RSNABOREVUES_TYPE_NUM_DECOUVERTE;
				$dateDebut = $startDateOfNextAbo ? $startDateOfNextAbo : $invoiceDate;
				$dateFin = self::getDateFinAbo($toDay, 3);
				//L'abonnement actuel finit dans plus de 3 mois
				if($dateDebut > $dateFin){
					$dateFin = false;
					continue;//! ne sort pas du foreach
				}
				break;
			case 'RPARR': //Abonnement de parrainage d'un an à la revue
				$addMonths = 12;
				$aboType = RSNABOREVUES_TYPE_ABO_PARRAINE;
				//TODO Create Contact + Account + RSNAboRevue
				//TODO Add Flag "Parrain" to Contact
				$dateFin = false;
				continue;//! ne sort pas du foreach
			default:
				//TODO What to do ?
				$dateFin = false;
				continue;//! ne sort pas du foreach
			}
			if($dateFin){
				$aboRevue = $this->createAboRevue($invoice, $account, $dateDebut, $dateFin, $nbExemplaires, $aboType);
				break;
			}
		}
		
		//Il est important d'avoir un abonnement pour chaque facture (via le champ sourceid)
		// pour que le prochain enregistrement de la facture ne recrée pas une modif cumulative
		if($nbExemplairesGroupes){
			//TODO 
			$log->debug("handleAfterSaveInvoiceAbonnementsEvent : Attention, abonnements supplémentaires sans abonnement principal");
			
			$aboType = RSNABOREVUES_TYPE_ABO_GROUPE;
					
			//Parcourt l'historique par date décroissante pour trouver un abonnement groupé
			$rsnAboRevueCourant = false;
			$dateDebut = false;
			foreach($rsnAboRevues as $rsnaborevuesId=>$rsnAboRevue){
				$isAbonne = $rsnAboRevue->isAbonne();
				if($isAbonne){
					if($rsnAboRevue->isTypeAboGroupe()
					|| $rsnAboRevue->getNbExemplaires() > 1
					|| $dateDebut == $invoiceDate){
						$dateDebut = $rsnAboRevue->getDebutAbo();
						$dateFin = $rsnAboRevue->getFinAbo();
						if(!$dateFin){
							$log->debug("handleAfterSaveInvoiceAbonnementsEvent, un abonnement groupé existe sans date de fin");
							$dateDebut = $invoiceDate;
						}
						//A 3 mois de la fin d'un précédent abonnement, on prolonge
						elseif(abs($rsnAboRevue->getFinAbo() - $invoiceDate) < 3*24*3600){
							$dateDebut = $invoiceDate;
							$dateFin = self::getDateFinAbo($dateDebut, 12);
							$rsnAboRevue->set('mode', 'edit');
							if($rsnAboRevue->getNbExemplaires() < $nbExemplairesGroupes)
								$rsnAboRevue->setNbExemplaires($nbExemplairesGroupes);
							$rsnAboRevue->setFinAbo($invoiceDate);
							$rsnAboRevue->save();
							
							$rsnAboRevueCourant = $rsnAboRevue;
						}
						else {//sinon , on crée un abonnement parallèle
							$dateDebut = $invoiceDate;
						}
						$log->debug("handleAfterSaveInvoiceAbonnementsEvent, isTypeAboGroupe");
						break;
					}
				}
			}
			if($nbExemplairesGroupes
			&& (!$dateFin || $dateFin > $toDay)){
				if(!$dateDebut){
					if($rsnAboRevueCourant){
						$startDateOfNextAbo = $rsnAboRevueCourant->getStartDateOfNextAbo($prochaineRevue, $invoiceDate);
						$log->debug("handleAfterSaveInvoiceAbonnementsEvent startDateOfNextAbo = " .($startDateOfNextAbo->format('d/m/Y')));
						$dateDebut = $startDateOfNextAbo && $startDateOfNextAbo > $invoiceDate ? $startDateOfNextAbo : $invoiceDate;
					}
					else {
						$dateDebut = $invoiceDate;
					}
				}
				$dateFin = self::getDateFinAbo($dateDebut, 12);
				if($dateFin > $toDay){
					$aboRevue = $this->createAboRevue($invoice, $account, $dateDebut, $dateFin, $nbExemplairesGroupes, $aboType);
				}
			}
			$this->addRelatedTask($invoice, "Veuillez contrôler l'abonnement groupé du contact.");
		}
		
		
		$log->debug("OUT handleAfterSaveInvoiceAbonnementsEvent");
		return $aboRevue;
	}
	
	/**
	 * Déjà traité
	 */
	public static function isInvoiceAlreadyRelatedWithAboRevue($rsnAboRevues, $invoiceId){
		$aboRevueModuleModel = Vtiger_Module_Model::getInstance('RSNAboRevues');
		return $aboRevueModuleModel->isRecordAlreadyRelatedWithAboRevue($rsnAboRevues, $invoiceId);
	}
	
	//Parcourt l'historique pour clôturer les en-cours périmés
	public static function check_IsAbonne_vs_DateFin($rsnAboRevues){
		$aboRevueModuleModel = Vtiger_Module_Model::getInstance('RSNAboRevues');
		$aboRevueModuleModel->check_IsAbonne_vs_DateFin($rsnAboRevues);
	}
	
	public static function getDateFinAbo($dateDebut, $nbMonths){
		$dateFin = clone $dateDebut; 
		$dateFin->modify('first day of this month')->modify( '+' . ($nbMonths) . ' month' );
		return $dateFin;
	}
	
	/* ED150507 Règles de gestion lors de la validation d'une facture, d'après le montant total
	 * 
	 * Rien de gratuit si un abonnement est en cours pendant encore 3 mois
	 * 			
	 */
	public function handleAfterSaveInvoiceTotalEvent($invoice, $invoiceData, $lineItems, $account, $totalDons){
		global $log;
		$log->debug("IN handleAfterSaveInvoiceTotalEvent");
		
		$grandTotal = (float)$lineItems[1]['final_details']['grandTotal'];
		
		$toDay = new DateTime();
		
		$country = $account->get('billcountry');
		$foreigner = $country && strcasecmp($country, 'France') !== 0;
		
		$nbTrimestresGratos = false; //càd, nbre de revues
		$aboType = RSNABOREVUES_TYPE_NUM_MERCI;
		
		//TODO : prélèvement == 1 an
		
		// Les étrangers, pour moins de 20€ n'ont le droit à rien
		if($grandTotal < 20 && $foreigner){
			//walou
		}
		// Pour moins de 4, nada  TODO A vérifier avec Bate
		elseif($grandTotal < 4){
			//walou
		}
		// Pour plus de 48 € de commande ou de dons, un an
		elseif($grandTotal >= 48){
			$nbTrimestresGratos = 4;
		}
		// Pour des dons de moins de 48 €, un n° de découverte si le dernier n° envoyé date d'un an au moins
		elseif($grandTotal >= 10 && $grandTotal < 48 && $totalDons >= 10 && $totalDons < 48){
			$nbTrimestresGratos = 1;
			//TODO
		}
		// Pour des dons de moins de 10 €, un n° de découverte si le dernier n° envoyé date d'un an au moins
		elseif($grandTotal >= 10 && $grandTotal < 48 && $totalDons < 10){
			$nbTrimestresGratos = 1;
			//TODO
		}
		// Pour moins de 48 € de commande, un n° de découverte si le dernier n° envoyé date d'un an au moins
		elseif($grandTotal >= 10 && $grandTotal < 48){
			$nbTrimestresGratos = 1;
			//TODO
		}
		// Pour moins de 10 €, un n° de découverte si le dernier n° envoyé date d'un an au moins
		elseif($grandTotal < 10){
			$nbTrimestresGratos = 1;
			//TODO
		}
		// Abonnement d'un an
		else {
			$nbTrimestresGratos = 4;
		}
		
		$rsnAboRevues = $account->getRSNAboRevues();
		
		//Contrôle si cette facture a déjà généré un abonnement
		if(self::isInvoiceAlreadyRelatedWithAboRevue($rsnAboRevues, $invoice->getId())){
			$log->debug("handleAfterSaveInvoiceTotalEvent, cette facture a déjà généré un abonnement");
			return;
		}
		//Parcourt l'historique pour clôturer les en-cours périmés
		self::check_IsAbonne_vs_DateFin($rsnAboRevues);
		
		//Parcourt l'historique par date décroissante
		foreach($rsnAboRevues as $rsnaborevuesId=>$rsnAboRevue){
			if($rsnAboRevue->isAbonne()){
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
					//Rien de gratuit si un abonnement est en cours pendant encore 3 mois
					if($startDateOfNextAbo > self::getDateFinAbo($toDay, 3))
						$nbTrimestresGratos = 0;
					break;
				}
			}
			elseif($rsnAboRevue->isTypeNePasAbonner()){
				$log->debug("handleAfterSaveInvoiceAbonnementsEvent, isTypeNePasAbonner");
				$nbTrimestresGratos = 0;
				break;
			}
		}
		
		
		if($nbTrimestresGratos && !$abonneAVie){
			$invoiceDate = new DateTime($invoice->get('invoicedate'));
			$dateFin = self::getDateFinAbo($invoiceDate, $nbTrimestresGratos * 3 + 1);
			if($rsnAboRevueCourant){
				$dateDebut = $rsnAboRevueCourant->getFinAbo();
			}
			else {
				$dateDebut = $invoiceDate;
			}
			if($dateFin > $dateDebut && $dateFin > $toDay)
				$aboRevue = $this->createAboRevue($invoice, $account, $dateDebut, $dateFin, 1, $aboType);
		}
		
		$log->debug("OUT handleAfterSaveInvoiceTotalEvent");
	}
	
	//Ajoute une tâche d'alerte pour prévenir l'utilisateur
	public function addRelatedTask($invoice, $message){
		global $log;
		
		$log->debug("handleAfterSaveInvoice addRelatedTask IN");
		
		$task = Vtiger_Record_Model::getCleanInstance('Calendar');
		
		$task->set('mode', 'create');
		$task->set('date_start', date('Y-m-d'));
		$task->set('contact_id', $invoice->get('contact_id'));
		$task->set('parent_id', $invoice->getId());
		$task->set('parent_type', 'Invoice');
		$task->set('subject', $message);
		$task->set('eventstatus', 'Planned');
		$task->set('activitytype', 'Contrôle des données');
		if(strlen($message) > 255)
			$task->set('description', $message);
		
		$task->save();
		$task->set('mode', '');
		$log->debug("handleAfterSaveInvoice addRelatedTask OUT");
		
		return $task;
	}
	
}