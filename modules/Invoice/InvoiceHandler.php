<?php
/* +***********************************************************************************
 * ED150418
 * Actions suite à la validation d'une facture
 * Règle de gestion
 *     - Le montant de la facture dépasse 35 € : un n° de la revue "Merci de votre soutien" offert, création d'un record RSNAboRevues
 *     - Article d'abonnement : création d'un record RSNAboRevues
 *     - Article d'adhésion : TODO
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

        $this->updateAllTotal($invoice->getId());

        $lineItems = $invoice->getProducts();
        $log->debug("handleAfterSaveInvoiceEvent lineItems " . print_r($lineItems, true));
        $categories = array(); //items by category
        $invoiceData = false; //first item
        $totalDons = 0;
        $totalProduit = 0;
        $totalService = 0;
        $totalFDP = 0;
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
            // case 'Adhésion' : // plus d'abonnement automatique en cas d'adhésion ...
            //     $this->handleAfterSaveInvoiceAdhesionEvent($invoice, $categoryItems, $account);
            //     break;
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

    public function updateAllTotal($invoiceId) {
        $adb = PearDatabase::getInstance();;

        $sql = "UPDATE vtiger_invoice
                SET vtiger_invoice.total_don = (

                SELECT
                ROUND(SUM( (vtiger_inventoryproductrel.quantity*vtiger_inventoryproductrel.listprice-ROUND(vtiger_inventoryproductrel.quantity*vtiger_inventoryproductrel.listprice*(IFNULL(vtiger_inventoryproductrel.discount_percent, 0)/100), 2) ) * (IFNULL ( tax1 , IFNULL ( tax2 , IFNULL ( tax3 , IFNULL ( tax4 , IFNULL ( tax5 , IFNULL ( tax6 , IFNULL ( tax7 , 0 ) ) ) ) ) ) ) / 100 + 1)), 2) AS prix_ttc
                FROM vtiger_inventoryproductrel

                JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_inventoryproductrel.id

                JOIN vtiger_service ON vtiger_inventoryproductrel.productid = vtiger_service.serviceid
                JOIN vtiger_crmentity vtiger_service_crmentity ON vtiger_service_crmentity.crmid = vtiger_service.serviceid

                WHERE vtiger_inventoryproductrel.id = vtiger_invoice.invoiceid
                AND vtiger_crmentity.deleted = 0
                AND vtiger_service_crmentity.deleted = 0

                AND vtiger_inventoryproductrel.quantity != 0
                AND vtiger_inventoryproductrel.listprice != 0

                AND vtiger_service.servicecategory = 'Dons'

                GROUP BY vtiger_crmentity.crmid
                )

                WHERE vtiger_invoice.invoiceid=$invoiceId;";
        $result = $adb->query($sql);

        $sql = "UPDATE vtiger_invoice

                SET vtiger_invoice.total_service = (

                SELECT
                ROUND(SUM( (vtiger_inventoryproductrel.quantity*vtiger_inventoryproductrel.listprice-ROUND(vtiger_inventoryproductrel.quantity*vtiger_inventoryproductrel.listprice*(IFNULL(vtiger_inventoryproductrel.discount_percent, 0)/100), 2) ) * (IFNULL ( tax1 , IFNULL ( tax2 , IFNULL ( tax3 , IFNULL ( tax4 , IFNULL ( tax5 , IFNULL ( tax6 , IFNULL ( tax7 , 0 ) ) ) ) ) ) ) / 100 + 1)), 2) AS prix_ttc
                FROM vtiger_inventoryproductrel

                JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_inventoryproductrel.id

                JOIN vtiger_service ON vtiger_inventoryproductrel.productid = vtiger_service.serviceid
                JOIN vtiger_crmentity vtiger_service_crmentity ON vtiger_service_crmentity.crmid = vtiger_service.serviceid

                WHERE vtiger_inventoryproductrel.id = vtiger_invoice.invoiceid
                AND vtiger_crmentity.deleted = 0
                AND vtiger_service_crmentity.deleted = 0

                AND vtiger_inventoryproductrel.quantity != 0
                AND vtiger_inventoryproductrel.listprice != 0

                GROUP BY vtiger_crmentity.crmid
                )

                WHERE vtiger_invoice.invoiceid=$invoiceId;";
        $result = $adb->query($sql);

        $sql = "UPDATE vtiger_invoice

                SET vtiger_invoice.total_product = (

                SELECT
                ROUND(SUM( (vtiger_inventoryproductrel.quantity*vtiger_inventoryproductrel.listprice-ROUND(vtiger_inventoryproductrel.quantity*vtiger_inventoryproductrel.listprice*(IFNULL(vtiger_inventoryproductrel.discount_percent, 0)/100), 2) ) * (IFNULL ( tax1 , IFNULL ( tax2 , IFNULL ( tax3 , IFNULL ( tax4 , IFNULL ( tax5 , IFNULL ( tax6 , IFNULL ( tax7 , 0 ) ) ) ) ) ) ) / 100 + 1)), 2) AS prix_ttc
                FROM vtiger_inventoryproductrel

                JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_inventoryproductrel.id

                JOIN vtiger_products ON vtiger_inventoryproductrel.productid = vtiger_products.productid
                JOIN vtiger_crmentity vtiger_product_crmentity ON vtiger_product_crmentity.crmid = vtiger_products.productid

                WHERE vtiger_inventoryproductrel.id = vtiger_invoice.invoiceid
                AND vtiger_crmentity.deleted = 0
                AND vtiger_product_crmentity.deleted = 0

                AND vtiger_inventoryproductrel.quantity != 0
                AND vtiger_inventoryproductrel.listprice != 0

                GROUP BY vtiger_crmentity.crmid
                )

                WHERE vtiger_invoice.invoiceid=$invoiceId;";
        $result = $adb->query($sql);

        $sql = "UPDATE vtiger_invoice

                SET vtiger_invoice.total_fdp = (

                SELECT
                ROUND(SUM( (vtiger_inventoryproductrel.quantity*vtiger_inventoryproductrel.listprice-ROUND(vtiger_inventoryproductrel.quantity*vtiger_inventoryproductrel.listprice*(IFNULL(vtiger_inventoryproductrel.discount_percent, 0)/100), 2) ) * (IFNULL ( tax1 , IFNULL ( tax2 , IFNULL ( tax3 , IFNULL ( tax4 , IFNULL ( tax5 , IFNULL ( tax6 , IFNULL ( tax7 , 0 ) ) ) ) ) ) ) / 100 + 1)), 2) AS prix_ttc
                FROM vtiger_inventoryproductrel

                JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_inventoryproductrel.id

                JOIN vtiger_service ON vtiger_inventoryproductrel.productid = vtiger_service.serviceid
                JOIN vtiger_crmentity vtiger_service_crmentity ON vtiger_service_crmentity.crmid = vtiger_service.serviceid

                WHERE vtiger_inventoryproductrel.id = vtiger_invoice.invoiceid
                AND vtiger_crmentity.deleted = 0
                AND vtiger_service_crmentity.deleted = 0

                AND vtiger_inventoryproductrel.quantity != 0
                AND vtiger_inventoryproductrel.listprice != 0

                AND vtiger_service.productcode LIKE '%ZFPORT%'

                GROUP BY vtiger_crmentity.crmid
                )

                WHERE vtiger_invoice.invoiceid=$invoiceId;";
        $result = $adb->query($sql);

        $sql = "UPDATE vtiger_invoice

				SET vtiger_invoice.isgroup = (

				SELECT
				CASE vtiger_contactdetails.isgroup
				       WHEN 0 THEN 0
				       ELSE 1
				END
				FROM vtiger_contactdetails

				JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_contactdetails.contactid

				WHERE vtiger_contactdetails.contactid = vtiger_invoice.contactid
				AND vtiger_crmentity.deleted = 0
				)

				WHERE vtiger_invoice.invoiceid=$invoiceId;";
        $result = $adb->query($sql);
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
            self::check_IsAbonne_vs_DateFin($rsnAboRevues, true);

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
            //$log->debug("handleAfterSaveInvoiceAbonnementsEvent productCode = $productCode : nbExemplairesGroupes = $nbExemplairesGroupes");
        }

        $log->debug("handleAfterSaveInvoiceAbonnementsEvent rsnAboRevueCourant = " .($rsnAboRevueCourant ? 'oui' : 'non'));
        foreach($categoryItems as $nLine => $lineItem){//tmp mettre fin aux numero découverte / offert en cas d'abo payant !!!
            $productCode = $lineItem['hdnProductcode'.$nLine];
            $addMonths = 0;
            $addMaxDate = false;
            $aboType = $productCode;
            $nbExemplaires = 1;
            switch($productCode){
            case 'RABOGROU': //Abonnement d'un an à la revue a 10 euro pour les groupes // check if it is a group
                if (! $account->getRelatedMainContact()->get('isgroup') ) {
                    break;
                }
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
            case 'RABOG': //Abonnement gratuit d'un an à la revue
                if($abonneAVie){
                    $dateFin = false;
                    continue;//! ne sort pas du foreach
                }
                $aboType = RSNABOREVUES_TYPE_ABONNE;
                $dateDebut = $startDateOfNextAbo ? $startDateOfNextAbo : $invoiceDate;
                $dateFin = self::getDateFinAbo($toDay, 12);
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
    public static function check_IsAbonne_vs_DateFin($rsnAboRevues, $closeAllFree){
        $aboRevueModuleModel = Vtiger_Module_Model::getInstance('RSNAboRevues');
        $aboRevueModuleModel->check_IsAbonne_vs_DateFin($rsnAboRevues, $closeAllFree);
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
        $totalAdh = 0;
        foreach($lineItems as $nLine => $lineItem){//remove ADH from total!!!
            $productCode = html_entity_decode($lineItem['hdnProductcode'.$nLine]);

            if (strstr($productCode, "ADH") !== false) {
                $totalAdh += ($lineItem['netPrice'.$nLine]);
            }
        }

        $grandTotal -= $totalAdh;

        $toDay = new DateTime();

        $rsnAboRevues = $account->getRSNAboRevues();

        //Contrôle si cette facture a déjà généré un abonnement
        if(self::isInvoiceAlreadyRelatedWithAboRevue($rsnAboRevues, $invoice->getId())){
            $log->debug("handleAfterSaveInvoiceTotalEvent, cette facture a déjà généré un abonnement");
            return;
        }
        //Parcourt l'historique pour clôturer les en-cours périmés
        self::check_IsAbonne_vs_DateFin($rsnAboRevues);

        $country = $account->get('billcountry');
        $foreigner = $country && strcasecmp($country, 'France') !== 0;

        $nbTrimestresGratos = false; //càd, nbre de revues
        $aboType = RSNABOREVUES_TYPE_NUM_MERCI;

        $amount_to_check = ($totalDons > 0) ? $totalDons : $grandTotal;

        //TODO : prélèvement == 1 an

        //TMP -> cumuler grandTotal et totalDons ?????

        // Les étrangers, pour moins de 20€ n'ont le droit à rien
        if($grandTotal < 20 && $foreigner){
            //walou
        }
        // Pour moins de 4, nada  TODO A vérifier avec Bate
        elseif($grandTotal < 12){
            //walou
        }
        // Pour plus de 48 € de dons, un an
        elseif($amount_to_check >= 48){
            $nbTrimestresGratos = 4;
            $aboType = RSNABOREVUES_TYPE_NUM_MERCI;
        }
        // Pour plus de 36 € de dons, 3 trimestres
        elseif($amount_to_check >= 36){
            $nbTrimestresGratos = 3;
            $aboType = RSNABOREVUES_TYPE_NUM_MERCI;
        }
        // Pour plus de 24 € de dons, 2 trimestres
        elseif($amount_to_check >= 24){
            $nbTrimestresGratos = 2;
            $aboType = RSNABOREVUES_TYPE_NUM_MERCI;
        }
        // Pour plus de 12 € de dons, 1 trimestre
        elseif($amount_to_check >= 12){
            $nbTrimestresGratos = 1;
            $aboType = RSNABOREVUES_TYPE_NUM_MERCI;
        }

        $IsNonGivedAbo = false;

        if($nbTrimestresGratos){
            //Parcourt l'historique par date décroissante
            foreach($rsnAboRevues as $rsnaborevuesId=>$rsnAboRevue){
                if($rsnAboRevue->isAbonne()){
                    $IsNonGivedAbo = ! ($rsnAboRevue->isTypeMerciSoutien() || $rsnAboRevue->isTypeDecouverte());
                    if($rsnAboRevue->isTypeAbonneAVie()){
                        $rsnAboRevueCourant = $rsnAboRevue;
                        $abonneAVie = true;
                        $log->debug("handleAfterSaveInvoiceTotalEvent, abonneAVie");
                        break;
                    }
                    else {
                        $rsnAboRevueCourant = $rsnAboRevue;
                        $startDateOfNextAbo = $rsnAboRevueCourant->getStartDateOfNextAbo($prochaineRevue, $invoiceDate);
                        $log->debug("handleAfterSaveInvoiceTotalEvent startDateOfNextAbo = " .($startDateOfNextAbo->format('d/m/Y')));
                        //Rien de gratuit si un abonnement est en cours jusqu'à la fin
                        if($startDateOfNextAbo > self::getDateFinAbo($toDay, 3 * $nbTrimestresGratos))
                            $nbTrimestresGratos = 0;
                        break;
                    }
                }
                elseif($rsnAboRevue->isTypeNePasAbonner()){
                    $log->debug("handleAfterSaveInvoiceTotalEvent, isTypeNePasAbonner");
                    $nbTrimestresGratos = 0;
                    break;
                }
            }
        }

        if($nbTrimestresGratos && !$abonneAVie && !$IsNonGivedAbo) {
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
