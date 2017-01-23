<?php
/*+***********************************************************************************
 * Exportation des factures vers la compta
 *************************************************************************************/
if(1){ //0 pour Debug
	define('ROWSEPAR', "\r\n");
	define('COLSEPAR', "\t");
} else {//debug
	define('ROWSEPAR', '<tr><td>');
	define('COLSEPAR', '<td>');
}

class Invoice_Send2Compta_View extends Vtiger_MassActionAjax_View {
	function __construct() {
		parent::__construct();
		$this->exposeMethod('showSend2ComptaForm');
		$this->exposeMethod('downloadSend2Compta');
		$this->exposeMethod('validateSend2Compta');
	}

	/**
	 * Function returns the mass edit form
	 * @param Vtiger_Request $request
	 */
	function showSend2ComptaForm (Vtiger_Request $request){
		$moduleName = $request->getModule();
		$cvId = $request->get('viewname');
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');

		$viewer = $this->getViewer($request);

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		if(!$this->validateInvoicesData($request))
			return false;
		
		$this->initSend2ComptaForm ($request);

		$viewer->assign('MODE', 'send2compta');
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('CVID', $cvId);
		$viewer->assign('MODULE_MODEL',$moduleModel); 
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('MODULE_MODEL', $moduleModel);
	
		echo $viewer->view('Send2ComptaForm.tpl',$moduleName,true);
	}
	
	function initSend2ComptaForm (Vtiger_Request $request){
		
		$this->initSend2ComptaFormExportableInvoices ($request);
		$this->initSend2ComptaFormValidatableInvoices ($request);
		$this->initSend2ComptaInvoicesWithoutRegulation($request);
	}

	function initSend2ComptaInvoicesWithoutRegulation (Vtiger_Request $request){
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		
		$controller = new Vtiger_MassSave_Action();
		
		$query = $controller->getRecordsQueryFromRequest($request);
		
		$onlyInvoicestatus = array('Created');
		
		//$query retourne autant de lignes que de lignes de factures
		$query = 'SELECT DISTINCT invoiceid
				FROM ('.$query.') _source_';
		$query = 'SELECT vtiger_invoice.invoice_no, vtiger_invoice.balance
			FROM ('.$query.') _source_ids_
			JOIN vtiger_invoice
				ON vtiger_invoice.invoiceid = _source_ids_.invoiceid
			JOIN vtiger_invoicecf
				ON vtiger_invoicecf.invoiceid = vtiger_invoice.invoiceid
			WHERE vtiger_invoicecf.sent2compta IS NULL
			AND NOT vtiger_invoice.invoicestatus IN ('.generateQuestionMarks($excludeInvoicestatus).')
			AND vtiger_invoice.balance > 0
			LIMIT 200 /*too long URL*/
		';
		
		$total = 0;
		$invoices = [];
		
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, $onlyInvoicestatus);

		if(!$result){
			$db->echoError();
			echo "<pre>$query</pre>";
			var_dump($params);
		}
		else {
			while($invoice = $db->fetch_row($result)){
				$invoices[] = array(
					invoice_no => $invoice['invoice_no'],
					balance => round($invoice['balance'], 2)
				);
			}
		}

		$viewer->assign('INVOICE_WITHOUT_REGULATION_LIST', $invoices);
		$viewer->assign('INVOICE_WITHOUT_REGULATION', (count($invoices) > 0));
	}
	
	function initSend2ComptaFormExportableInvoices(Vtiger_Request $request){
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		
		$controller = new Vtiger_MassSave_Action();
		
		$query = $controller->getRecordsQueryFromRequest($request);
		
		$excludeInvoicestatus = array('Created', 'Cancelled');
		
		//$query retourne autant de lignes que de lignes de factures
		$query = 'SELECT DISTINCT invoiceid
				FROM ('.$query.') _source_';
		$query = 'SELECT vtiger_invoice.invoiceid, vtiger_invoice.total
			FROM ('.$query.') _source_ids_
			JOIN vtiger_invoice
				ON vtiger_invoice.invoiceid = _source_ids_.invoiceid
			JOIN vtiger_invoicecf
				ON vtiger_invoicecf.invoiceid = vtiger_invoice.invoiceid
			WHERE vtiger_invoicecf.sent2compta IS NULL
			AND NOT vtiger_invoice.invoicestatus IN ('.generateQuestionMarks($excludeInvoicestatus).')
			LIMIT 200 /*too long URL*/
		';
		
		$selectedIds = array();
		$total = 0;
		
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, $excludeInvoicestatus);
		if(!$result){
			$db->echoError();
			echo "<pre>$query</pre>";
			var_dump($params);
		}
		else {
			while($invoice = $db->fetch_row($result)){
				$total += $invoice['total'];
				$selectedIds[] = $invoice['invoiceid'];
			}
			
			$viewer->assign('INVOICES_TOTAL', $total);
			$viewer->assign('INVOICES_COUNT', count($selectedIds));
		}
		$viewer->assign('SELECTED_IDS', $selectedIds);
	}
		
	//Compte les factures "En cours" qui pourraient passer en Validé
	function initSend2ComptaFormValidatableInvoices (Vtiger_Request $request){
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		
		$controller = new Vtiger_MassSave_Action();
		
		$query = $controller->getRecordsQueryFromRequest($request);
		
		$onlyInvoicestatus = array('Created');
		
		//$query retourne autant de lignes que de lignes de factures
		$query = 'SELECT DISTINCT invoiceid
				FROM ('.$query.') _source_';
		$query = 'SELECT COUNT(*) AS `count`
			, SUM(vtiger_invoice.total) AS `amount`
			, MIN(vtiger_invoice.invoicedate) AS `datemini`
			, MAX(vtiger_invoice.invoicedate) AS `datemaxi`
			FROM ('.$query.') _source_ids_
			JOIN vtiger_invoice
				ON vtiger_invoice.invoiceid = _source_ids_.invoiceid
			JOIN vtiger_invoicecf
				ON vtiger_invoicecf.invoiceid = vtiger_invoice.invoiceid
			WHERE vtiger_invoice.invoicestatus IN ('.generateQuestionMarks($onlyInvoicestatus).')
		';
		
		$total = 0;
		
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, $onlyInvoicestatus);
		if(!$result){
			$db->echoError();
			echo "<pre>$query</pre>";
			var_dump($params);
		}
		else {
			$row = $db->fetch_row($result);
			
			$viewer->assign('VALIDATABLE_TOTAL', $row['amount']);
			$viewer->assign('VALIDATABLE_COUNT', $row['count']);
			$viewer->assign('VALIDATABLE_DATEMINI', $row['datemini']);
			$viewer->assign('VALIDATABLE_DATEMAXI', $row['datemaxi']);
		}
	}
	
	//Controle des données avant affichage
	function validateInvoicesData(Vtiger_Request $request){
		
		$controller = new Vtiger_MassSave_Action();
		$query = $controller->getRecordsQueryFromRequest($request);
		
		//Contrôle que tous les produits et services on
		$query = 'SELECT DISTINCT vtiger_products.productid
			, IF(vtiger_products.productid IS NULL, "Service", "Produit") as module
			, IFNULL(productname, servicename) AS productname
			, IFNULL(vtiger_products.productcode, vtiger_service.productcode) AS productcode
			FROM ('.$query.') _source_ids_
			JOIN vtiger_invoicecf
				ON vtiger_invoicecf.invoiceid = _source_ids_.invoiceid
			JOIN vtiger_inventoryproductrel
				ON vtiger_inventoryproductrel.id = _source_ids_.invoiceid
			LEFT JOIN vtiger_products
				ON vtiger_products.productid = vtiger_inventoryproductrel.productid
			LEFT JOIN vtiger_service
				ON vtiger_service.serviceid = vtiger_inventoryproductrel.productid
			LEFT JOIN vtiger_servicecf
				ON vtiger_servicecf.serviceid = vtiger_inventoryproductrel.productid
			WHERE ((vtiger_products.productid IS NOT NULL AND IFNULL(vtiger_products.glacct, "") = "")
				OR (vtiger_servicecf.serviceid IS NOT NULL AND IFNULL(vtiger_servicecf.glacct, "") = ""))
			AND vtiger_inventoryproductrel.listprice <> 0
			AND vtiger_invoicecf.sent2compta IS NULL
		';
		$params = array();
		
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, $params);
		if(!$result){
			$db->echoError();
			echo "<pre>$query</pre>";
			return false;
		}
		elseif($db->getRowCount($result)) {
			echo "<ul><h4 style=\"color: red;\">Produits ou services sans compte de vente !!!</h4>";
			while($product = $db->fetch_row($result)){
				echo "<li>".$product['module']." - ".$product['productname']." (".$product['productcode'].")</li>";
			}
			echo "</ul>";
			return false;
		}
		return true;
	}
	
	/******************************************
	 *
	 *	downloadSend2Compta
	 *
	 *	les données doivent être exportées triées par journal et par mois, sinon COgilog refuse l'import
	 ******************************************/
	
	var $exportBuffers = array();
	function addExportRow($journal, $date = '', $row = ''){
		$dateYM = substr($date, 3);
		if(!$this->exportBuffers[$dateYM])
			$this->exportBuffers[$dateYM] = array();
		if(!$this->exportBuffers[$dateYM][$journal])
			$this->exportBuffers[$dateYM][$journal] = array();
		$this->exportBuffers[$dateYM][$journal][] = $journal . COLSEPAR . $date . COLSEPAR . $row;
	}
	function sanitizeExport($str){
		return str_replace(array(',', ';', "\t", "\r", "\n",), '-', $str);
	}
	function echoExportBuffer(){
		foreach($this->exportBuffers as $dateYM => $journaux){
			foreach($journaux as $exportBuffer){
				echo implode(ROWSEPAR, $exportBuffer);
				echo ROWSEPAR;
			}
		}
	}
	function downloadSend2Compta (Vtiger_Request $request, $setHeaders = true){
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		$no_of_decimal_places = getCurrencyDecimalPlacesForOutput();
			
		$taxes = self::getAllTaxes();
			
		$excludeInvoicestatus = array('Created', 'Cancelled');
		
		$selectedIds = $request->get('selected_ids');
		$query = 'SELECT vtiger_invoice.*
			, vtiger_invoicecf.receivedmoderegl
			, vtiger_invoicecf.receivedreference
			, vtiger_campaignscf.codeaffaire codeaffaire_campaign
			, vtiger_notescf.codeaffaire AS codeaffaire_coupon
			, IFNULL(vtiger_products.productname, vtiger_service.servicename) AS productname
			, IFNULL(vtiger_products.productcode, vtiger_service.productcode) AS productcode
			, IFNULL(vtiger_products.glacct, vtiger_servicecf.glacct) AS productglacct
			, IFNULL(vtiger_productcf.sectionanal, vtiger_servicecf.sectionanal) AS productsectionanal
			, vtiger_inventoryproductrel.quantity
			, vtiger_inventoryproductrel.listprice
			, vtiger_inventoryproductrel.discount_percent
			, vtiger_inventoryproductrel.discount_amount
			, vtiger_account.account_type
		';
		
		for($nTax = 0; $nTax < count($taxes); $nTax++){
			$query .= ', vtiger_inventoryproductrel.tax' . $taxes[$nTax]['taxid'];
		}
		$query .= '
			, reglements.rsnreglementsid
			, reglements.reglementamount
			, reglements.reglementstatus
			, reglements.dateoperation
			, reglements.dateregl
			, reglements.typeregl';
		
		$query .= '
			FROM vtiger_invoice
			JOIN vtiger_invoicecf
				ON vtiger_invoicecf.invoiceid = vtiger_invoice.invoiceid
			JOIN vtiger_account
				ON vtiger_account.accountid = vtiger_invoice.accountid
			LEFT JOIN vtiger_notescf /*coupon*/
				ON vtiger_invoicecf.notesid = vtiger_notescf.notesid
			LEFT JOIN vtiger_campaignscf /*campagne*/
				ON vtiger_invoicecf.campaign_no = vtiger_campaignscf.campaignid
			LEFT JOIN vtiger_inventoryproductrel
				ON vtiger_inventoryproductrel.id = vtiger_invoice.invoiceid
			LEFT JOIN vtiger_products
				ON vtiger_inventoryproductrel.productid = vtiger_products.productid
			LEFT JOIN vtiger_productcf
				ON vtiger_productcf.productid = vtiger_products.productid
			LEFT JOIN vtiger_service
				ON vtiger_inventoryproductrel.productid = vtiger_service.serviceid
			LEFT JOIN vtiger_servicecf
				ON vtiger_servicecf.serviceid = vtiger_service.serviceid';
		
		// Réglements (en tant que vtiger_rsnreglements) associés.
		// rappel : les réglements ne sont pas forcément définis dans vtiger_rsnreglements, ils peuvent étre uniquement présent par invoice.received et invoice.receivedmoderegl
		// il peut y avoir plusieurs vtiger_rsnreglements pour une facture, et inversement
		$query .= '
			LEFT JOIN (SELECT invoiceid
				, GROUP_CONCAT(rsnreglementsid) AS rsnreglementsid
				, GROUP_CONCAT(reglementstatus) AS reglementstatus
				, SUM(vtiger_rsnreglements.amount) AS reglementamount
				, vtiger_rsnreglements.dateoperation
				, vtiger_rsnreglements.dateregl
				, vtiger_rsnreglements.typeregl
				FROM vtiger_rsnreglements
				JOIN vtiger_crmentity
					ON vtiger_crmentity.crmid = vtiger_rsnreglements.rsnreglementsid
				JOIN vtiger_crmentityrel
					ON vtiger_crmentityrel.crmid = vtiger_rsnreglements.rsnreglementsid
					OR vtiger_crmentityrel.relcrmid = vtiger_rsnreglements.rsnreglementsid
				JOIN vtiger_invoice
					ON vtiger_crmentityrel.crmid = vtiger_invoice.invoiceid
					OR vtiger_crmentityrel.relcrmid = vtiger_invoice.invoiceid
				WHERE vtiger_crmentity.deleted = 0
				AND vtiger_rsnreglements.reglementstatus != "Cancelled"
				AND vtiger_invoice.invoiceid IN ('. generateQuestionMarks( $selectedIds ) . ')
				GROUP BY invoiceid
			) reglements
				ON reglements.invoiceid = vtiger_invoice.invoiceid';
			
		$query .= '
			WHERE vtiger_invoice.invoiceid IN ('. generateQuestionMarks( $selectedIds ) . ')
			AND vtiger_invoicecf.sent2compta IS NULL
			AND vtiger_invoice.total <> 0
			AND vtiger_inventoryproductrel.listprice <> 0
			AND NOT vtiger_invoice.invoicestatus IN ('.generateQuestionMarks($excludeInvoicestatus).')
		';
		
		if(FALSE){
			$query .= " AND vtiger_invoice.invoice_no = 'COG1506635'";
		
		}
		
		if(FALSE){
			$query .= " AND vtiger_invoice.invoicedate > '2015-09-29'";
		
		}
			
		$query .= '
			ORDER BY vtiger_invoice.invoicedate ASC
			, vtiger_invoice.invoiceid
			, vtiger_inventoryproductrel.sequence_no
		';
		$params = $selectedIds;//reglements
		$params = array_merge($params, $selectedIds);//invoices
		$params = array_merge($params, $excludeInvoicestatus);
		
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, $params);
		if(!$result){
			$db->echoError();
			echo "<pre>$query</pre>";
			var_dump($params);
			$response = new Vtiger_Response();
			$response->setError('Erreur de requête');
			$response->emit();
		}
		else {
			
			$isDebug = COLSEPAR;
			$isDebug = $isDebug[0] === '<';
			
			$fileName = 'EC_'.date('Y-m-d').'.csv';
			$exportType = 'text/csv';
			if($isDebug)
				echo '<table border="1"><tr><td>';//debug
			elseif($setHeaders) {
				header("Content-Disposition:attachment;filename=$fileName");
				header("Content-Type:$exportType;charset=UTF-8");
				header("Expires: Mon, 31 Dec 2000 00:00:00 GMT" );
				header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT" );
				header("Cache-Control: post-check=0, pre-check=0", false );
			}
						
			$this->addExportRow( "**Compta\tEcritures" );
			
			
			/* NOTE : les commentaires suivants ne sont pas forcément valables, ils datent du début du dév.
			 * 
			 * 1) Factures et le détail des lignes
			 * Journal : "VT"
			 * 1 ligne par ligne d'article
			 * 	Compte : issu du produit ou service
			 * 	Section analytique : issu du code affaire de la facture (coupon ou campagne)
			 * 	N° de pièce : n° de facture
			 * 	Montant hors-taxe en crédit
			 * 
			 * 1 ligne de TVA par facture et par taux utilisé : n° de compte 445xxx dans la table des taxes
			 *
			 * 1 ligne par facture pour le total, en débit
			 * 	Si payé, compte 511xxx (selon mode de réglement : PayPal, PayBox, Chèque, ...)
			 * 	Sinon, compte 411xxx (selon type de facture : dépôt-vente, boutique)
			 * 
			 * 2) Encaissements pour les factures payées
			 * Journal : "BFC" (compte bancaire NEF)
			 * 1 ligne par facture
			 * 	Compte 511xxx : issu du code affaire de la facture (coupon ou campagne)
			 * 	Section analytique : issu du code affaire de la facture (coupon ou campagne)
			 * 	N° de pièce : n° de facture
			 * 	Montant TTC en crédit
			 * 	
			 * 1 ligne par jour 
			 * 	Compte 512107 : compte bancaire NEF
			 * 	Compte 514000 : compte banque postale pour les chèques
			 * 	Section analytique : ?
			 * 	Au débit
			 * 
			 * 
			 * 3) Factures fournisseurs (commissions PayPal, ...)
			 * 
			 * ******
			 * ******
			 * Seuls les comptes en 6 ou en 7 on une section analytique
			 *
			 * Le type de compte est prioritaire pour définir le compte de Vente :
			 * 	- Depot-vente 411DEP
			 * 
			 */
			
			$journalVente = 'VT';//TODO Paramétrable
			$prevInvoice = false;
			$prevInvoiceId = 0;
			$prevDate = '';
			$totalPerDate = array();
			$num_rows = $db->num_rows($result);
			for ($i=0; $i < $num_rows; ++$i) {
			//while($invoice = $db->fetch_row($result, false)){
				$invoice = $db->query_result_rowdata($result, $i, false);
				$isInvoiceHeader = $prevInvoiceId != $invoice['invoiceid'];
				if($isInvoiceHeader){
					/* En-tête de facture */		
					$invoiceTaxes = array();
					$invoiceTotal = 0;
					
					//ligne de facture de l'encaissement
					$codeAffaire = self::getInvoiceCodeAffaire($invoice['codeaffaire_campaign'], $invoice['codeaffaire_coupon']);
					$piece = $invoice['invoice_no'];
					$invoiceModeRegl = $invoice['receivedmoderegl'];
					$invoiceCompteVente = self::getInvoiceCompteVenteSolde($invoice);
					$invoiceCodeAnal = self::getCodeAffaireCodeAnal($codeAffaire);
					$invoiceJournal = self::getCodeAffaireJournal($codeAffaire, $invoiceModeRegl);
					$invoiceAmount = self::formatAmountForCogilog($invoice['total']);
					$invoiceReceived = self::formatAmountForCogilog($invoice['received']);
					$encaissementJournal = self::getModeReglJournal($invoiceModeRegl); 
					$basicSubject = preg_replace('/[\r\n\t]/', ' ', html_entity_decode( $invoice['subject']));
					$invoiceSubject = $basicSubject . ($codeAffaire ? ' - ' . $codeAffaire : '');
					$date = self::formatDateForCogilog($invoice['invoicedate']);
					if ($invoice['dateoperation']) {
						$collectionDate = self::formatDateForCogilog($invoice['dateoperation']);
						$EffectiveCollectionDateTime = new DateTime($invoice['dateoperation']);//DateTime::createFromFormat ( "Y-m-d" , $invoice['dateoperation']);
						$EffectiveCollectionDateTime->add(new DateInterval('P1D'));
						$EffectiveCollectionDate = $EffectiveCollectionDateTime->format("d/m/Y");
					} else if ($invoice['dateregl']) {
						$EffectiveCollectionDate = $collectionDate = self::formatDateForCogilog($invoice['dateregl']);//tmp to check !!!
					} else {
						$collectionDate = $EffectiveCollectionDate = $date;
					}
					//La facture est réglée
					if($invoiceReceived){
						//Pas de compte de vente, pas d'encaissement (ce qui devrait être le cas de PayPal)
						if(!$encaissementJournal)
							$invoiceReceived = false;
						//Si un règlement est associé et qu'il est déjà en compta, on ne traite pas le règlement (ce qui devrait être le cas de PayPal)
						elseif(false)//(strpos($invoice['reglementstatus'], 'Compta') !== false)
							//tmp check if it's OK -> si la facture est comptabilisé alors,  elle ne devrait pas être recomptabilisé,
							//                       alors que lorsque que l'on repasse une facture en status en cours, on souheite regénérer les encaissement ??
							//       OU alors repasser les règlement en en cours
							$invoiceReceived = false;
						//Si le montant des règlements associés n'est pas celui de la facture, on ne traite pas le règlement
						//TODO cf validateSend2ComptaReglements() si on prend bien en compte les écritures
						elseif($invoice['reglementamount'] !== null && $invoice['reglementamount'] != $invoice['received'])
							$invoiceReceived = false;
					}
					//Cumuls des règlements par mode de règlement
					if($invoiceReceived && !$invoice['iscredit']){
						$key = $invoiceModeRegl . '-' . $EffectiveCollectionDate;
					
						if(!$totalPerDate[$key])
							$totalPerDate[$key] = array(
										    'codeAnal' => $invoiceCodeAnal,
										    'moderegl' => $invoiceModeRegl,
										    'date' => $date,
										    'EffectiveCollectionDate' => $EffectiveCollectionDate,
										    'collectionDate'=> $collectionDate,
										    'total' => 0.0
										);
						$totalPerDate[$key]['total'] += str_to_float($invoiceReceived);
					}
					
					$prevInvoiceId = $invoice['invoiceid'];
					$prevInvoice = $invoice;
					
				}
				
				/* ligne de produit */
				$amountHT = $invoice['quantity'] * $invoice['listprice'];//HT
				if($invoice['discount_amount']){
					$amountHT -= $invoice['discount_amount'];
				}
				if($invoice['discount_percent']){
					$amountHT *= (1 - $invoice['discount_percent']/100);
				}
				$amountHTRounded = round($amountHT, 2);//HT
				
				//Taxe utilisée
				$invoiceTotalTaxes = 0.0;
				$amountTTC = $amountHTRounded;
				for($nTax = 0; $nTax < count($taxes); $nTax++){
					$taxId = $taxes[$nTax]['taxid'];
					if($invoice['tax'.$taxId]){
						if(!array_key_exists("$taxId", $invoiceTaxes))
							$invoiceTaxes["$taxId"] = 0.0;

						$amountTTC = $amountHT * (1 + $invoice['tax'.$taxId] / 100);
						//Passage par le TTC, arrondi à 2 chiffres et retrait du HT pour éviter les écarts d'arrondis
						$value = round($amountTTC, 2) - $amountHTRounded;
						$invoiceTotalTaxes += $value;
						$invoiceTaxes["$taxId"] += $value;
						$amountTTC = round($amountTTC, 2);
						break;
					}
				}
				
				$compteVente = $invoice['productglacct'];
				if($compteVente[0] === '7' || $compteVente[0] === '6')
					$codeAnal = self::getCodeAffaireCodeAnal( $invoiceCodeAnal, $invoice['productsectionanal']);
				else	$codeAnal = '';
				//$productName = $invoice['productname'];
				$productCode = $invoice['productcode'];
				$this->addExportRow($journalVente,
					$date,
					$this->sanitizeExport($piece)
					.COLSEPAR.$compteVente
					.COLSEPAR.$codeAnal
					.COLSEPAR.$this->sanitizeExport($basicSubject . ($productCode ? ' - ' . $productCode : ''))
					.COLSEPAR.''
					.COLSEPAR.''
					.COLSEPAR.self::formatAmountForCogilog($amountHTRounded)
				);
				$invoiceTotal += $amountHTRounded + $invoiceTotalTaxes;

				$factureEnded = (($i + 1 >= $num_rows) || ($db->query_result_rowdata($result, $i+1)['invoiceid'] != $invoice['invoiceid']));
				if($factureEnded){
					/* En-tête de facture */
					
					if($invoiceTaxes) {
						$this->exportInvoiceTaxes($invoiceTaxes, $journalVente, $date, $piece, $invoiceSubject);
					}
				
					if($invoiceTotal){
						$this->exportInvoiceSolde($invoiceTotal, $journalVente, $date, $piece, $invoiceSubject, $invoice);
					}

					if($invoiceJournal && $invoiceReceived && !$invoice['iscredit']){
						/* Ligne d'encaissement de la Facture */
						$piece_for_encaissement = $this->getPieceLabel($invoice);
						$this->exportEncaissement($invoiceJournal, $EffectiveCollectionDate, $piece_for_encaissement, $invoiceCompteVente, $invoiceCodeAnal, $invoiceSubject, $invoiceReceived);//tmp ??
					}
				}
			}
			
			/* Lignes des encaissements par jour */
			foreach($totalPerDate as $key => $data){
				$total = $data['total'];
				$date = $data['date'];
				$collectionDate = $data['collectionDate'];
				$EffectiveCollectionDate = $data['EffectiveCollectionDate'];
				$modeRegl = $data['moderegl'];
				$compteEnc = self::getModeReglCompteEncaissement($modeRegl); 
				$journal = self::getModeReglJournal($modeRegl); 
				//$journal = self::getCompteEncaissementJournal($compteEnc);
				if($compteEnc[0] === '7' || $compteEnc[0] === '6')
					$codeAnal = $data['codeAnal'];
				else
					$codeAnal = '';
				$amount= self::formatAmountForCogilog($total);
				$piece = 'ENC-' . $modeRegl . /*($codeAnal ? '-' . $codeAnal : '') .*/ '-' . $date;//tmp code anal ???
				$descriptif = 'Paiements par ' . $this->sanitizeExport($modeRegl). ' du ' . $collectionDate;
				$this->addExportRow($journal,
					$EffectiveCollectionDate,
					$piece
					.COLSEPAR.$compteEnc
					.COLSEPAR.$codeAnal
					.COLSEPAR.$descriptif
					.COLSEPAR.''
					.COLSEPAR.$amount
				);
				
			}
		}
		$this->echoExportBuffer();
		if($isDebug)
			echo '</table>';//debug
		
		return $fileName;
	}

	private function getPieceLabel($invoice) {
		if ($invoice["receivedmoderegl"]) {
			$data = explode(" ", $invoice["receivedmoderegl"]);
			if ($data[1]) {
				return $data[1] . "-" . self::formatDateForLabel($invoice['invoicedate']);
			}
		}

		return $invoice['invoice_no'];
	}
	
	private function exportEncaissement($invoiceJournal, $date, $piece, $compteVente, $invoiceCodeAnal, $invoiceSubject, $invoiceAmount){
		/* Ligne d'encaissement de la Facture */
		$this->addExportRow($invoiceJournal,
			$date,
			$this->sanitizeExport($piece)
			.COLSEPAR.$compteVente
			.COLSEPAR.$invoiceCodeAnal
			.COLSEPAR.$this->sanitizeExport($invoiceSubject)
			.COLSEPAR.''
			.COLSEPAR.''
			.COLSEPAR.$invoiceAmount
		);
	}
	
	private function exportInvoiceSolde($invoiceTTC, $journal, $date, $piece, $invoiceSubject, $invoiceData){
		$amount= self::formatAmountForCogilog($invoiceTTC);
		$account = self::getInvoiceCompteVenteSolde($invoiceData);
		$this->addExportRow($journal,
			$date,
			$this->sanitizeExport($piece)
			.COLSEPAR.$account
			.COLSEPAR
			.COLSEPAR.$this->sanitizeExport($invoiceSubject)
			.COLSEPAR.''
			.COLSEPAR.$amount
		);
	}
	
	private function exportInvoiceTaxes($invoiceTaxes, $journal, $date, $piece, $invoiceSubject){
		foreach($invoiceTaxes as $taxId => $amount){
			$amount = self::formatAmountForCogilog($amount);
			$tax = self::getTax($taxId);
			$account = $tax['account'];
			$this->addExportRow($journal,
				$date,
				$this->sanitizeExport($piece)
				.COLSEPAR.$account
				.COLSEPAR
				.COLSEPAR.$this->sanitizeExport($invoiceSubject)
				.COLSEPAR.''
				.COLSEPAR.''
				.COLSEPAR.$amount
			);
		}
	}
	
	static $allTaxes = false;//cache
	private static function getAllTaxes(){
		if(!self::$allTaxes)
			self::$allTaxes = getAllTaxes();
		return self::$allTaxes;
	}
	private static function getTax($taxId){
		foreach( self::getAllTaxes() as $tax)
			if($tax['taxid'] == $taxId)
				return $tax;
		return false;
	}
	
	private static function getInvoiceCompteVenteSolde($invoiceData){
		$accountType = $invoiceData['account_type'];
		switch($accountType){
		case 'Depot-vente' :
			//L'encaissement des adhésions
			if($invoiceData['codeaffaire_coupon'] === 'ADH')
				return '411000';
			return '411DEP';
		
		default :
			$modeRegl = $invoiceData['receivedmoderegl'];
			switch($invoiceData['typedossier']){
			case 'Facture de dépôt-vente' :
				return '411DEP';
		
			case 'Credit Invoice' ://Avoir
			case 'Avoir' :
			case 'Remboursement' :
				return '511200';//TODO
			default:
					
				return getModeReglementInfo($modeRegl, 'comptevente');
			
				//switch($modeRegl){
				//case 'PayBox' :
				//	return '511101';
				//case 'PayPal' :
				//	return '511300';
				//case 'Espèces' :
				//	return '511103';
				//case 'CB' :
				//	return '511102';
				//case 'Virement' :
				//	return '511106';
				//case 'Mandat' :
				//	return '511106';//TODO
				//default:
				//	return '511200';//LBP
				//}
			}
		}
	}
	
	private static function getModeReglCompteEncaissement($modeRegl){
		return getModeReglementInfo($modeRegl, 'compteencaissement');
	
		//switch($modeRegl){
		//case 'PayBox' :
		//	return '512107';
		//case 'PayPal' :
		//	return '514000';
		//case 'Espèces' :
		//	return '511103';
		//case 'CB' :
		//	return '514000';
		//case 'Virement' :
		//	return '514000';
		//case 'Mandat' :
		//	return '514000';//TODO
		//default:
		//	return '514000';//LBP
		//}
	}
	private static function getModeReglJournal($modeRegl){
		return getModeReglementInfo($modeRegl, 'journalencaissement');
	}
	//private static function getCompteEncaissementJournal($compte){
	//	switch(strtoupper($compte)){
	//	case '511103' :
	//		return 'CS';//caisse
	//	case '512107' :
	//		return 'BFC';
	//	default :
	//		return 'LBP';
	//	}
	//}
	
	private static function getCodeAffaireCodeAnal($codeAffaire, $codeAffaire2 = false){
		if(!$codeAffaire)
			return $codeAffaire2;
		if($codeAffaire2
		&& (substr($codeAffaire, 0, 3) === 'BOU'
		 || substr($codeAffaire, 0, 6) === 'PAYBOX'
		 || substr($codeAffaire, 0, 6) === 'PAYPAL'
		 || substr($codeAffaire, 0, 3) === '***')
		){
			return $codeAffaire2;
		}
		return $codeAffaire;
	}
	
	private static function getInvoiceCodeAffaire($codeaffaire_campaign, $codeaffaire_coupon){
		return self::getCodeAffaireCodeAnal($codeaffaire_campaign, $codeaffaire_coupon);
	}
	
	private static function getCodeAffaireJournal($codeAffaire, $modeRegl = false){
		return getModeReglementInfo($modeRegl, 'journalencaissement');
		//
		//switch(strtoupper($codeAffaire)){
		//case 'PAYBOX BOU' :
		//case 'PAYBOX DR' :
		//case 'PAYBOX DP' :
		//case 'PAYBOX' :
		//case 'PAYBOXP' :
		//	return 'BFC';
		//default :
		//	if($modeRegl)
		//		switch(strtoupper($modeRegl)){
		//		case 'PAYBOX' :
		//		case 'PAYBOXP' :
		//			return 'BFC';
		//		case 'ESPèCES' :
		//		case 'ESPÈCES' :
		//		case 'ESPECES' :
		//			return 'CS';
		//		}
		//	return 'LBP';
		//}
	}
	
	private static function formatDateForCogilog($myDate){
		$parts = explode('-', $myDate);
		return $parts[2] . '/' . $parts[1] . '/' . $parts[0];
	}

	private static function formatDateForLabel($myDate){
		$parts = explode('-', $myDate);
		return $parts[0] . '-' . $parts[1] . '-' . $parts[2];
	}
	
	private static function formatAmountForCogilog($amount){
		return str_replace('.', ',', round($amount, 2));
	}
	
	/*********************************
	 **		validateSend2Compta
	 ********************************/
	//Marque les factures et les règlements comme étant envoyées en compta (champs sent2compta)
	function validateSend2Compta(Vtiger_Request $request){
		//d'abord les règlements, pour garder les factures dans le statut valable
		$this->validateSend2ComptaReglements($request);
		$this->validateSend2ComptaCheques($request);
		$this->validateSend2ComptaInvoices($request);
	}
	
	//Solde les factures réglées par chèque
	function validateSend2ComptaCheques(Vtiger_Request $request){
		
		$excludeInvoicestatus = array('Created', 'Cancelled', 'Compta');
		$selectedIds = $request->get('selected_ids');
		
		$query = 'UPDATE vtiger_invoicecf
			JOIN vtiger_invoice
				ON vtiger_invoicecf.invoiceid = vtiger_invoice.invoiceid
			SET vtiger_invoice.received = vtiger_invoice.balance
			, vtiger_invoice.balance = 0
			, vtiger_invoicecf.receivedcomments = IF(IFNULL(vtiger_invoicecf.receivedcomments, "") = "", CONCAT("Validation du ", NOW()), vtiger_invoicecf.receivedcomments)
			WHERE vtiger_invoice.invoiceid IN ('. generateQuestionMarks( $selectedIds ) . ')
			AND vtiger_invoicecf.sent2compta IS NULL
			AND vtiger_invoicecf.receivedmoderegl = "Chèque"
			AND (vtiger_invoice.received IS NULL OR vtiger_invoice.received = 0)
			AND NOT vtiger_invoice.invoicestatus IN ('.generateQuestionMarks($excludeInvoicestatus).')
		';
		$params = array();
		$params = array_merge($params, $selectedIds);
		$params = array_merge($params, $excludeInvoicestatus);
		
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, $params);
		if(!$result){
			$db->echoError();
			echo "<pre>$query</pre>";
			var_dump($params);
			$response = new Vtiger_Response();
			$response->setError('Erreur de requête de Solde');
			$response->emit();
			exit;
		}
	}
	
	//Marque les factures comme étant envoyées en compta (champ sent2compta)
	function validateSend2ComptaInvoices(Vtiger_Request $request){
		
		$excludeInvoicestatus = array('Created', 'Cancelled', 'Compta');
		$selectedIds = $request->get('selected_ids');
		//Changement du statut en Compta
		$query = 'UPDATE vtiger_invoicecf
			JOIN vtiger_invoice
				ON vtiger_invoicecf.invoiceid = vtiger_invoice.invoiceid
			SET vtiger_invoicecf.sent2compta = NOW()
			, vtiger_invoice.invoicestatus = ?
			WHERE vtiger_invoice.invoiceid IN ('. generateQuestionMarks( $selectedIds ) . ')
			AND vtiger_invoicecf.sent2compta IS NULL
			AND NOT vtiger_invoice.invoicestatus IN ('.generateQuestionMarks($excludeInvoicestatus).')
		';
		$params = array('Compta');
		$params = array_merge($params, $selectedIds);
		$params = array_merge($params, $excludeInvoicestatus);
		
		if($selectedIds)
			$this->storeFile($request);
		
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, $params);
		if(!$result){
			$db->echoError();
			echo "<pre>$query</pre>";
			var_dump($params);
			$response = new Vtiger_Response();
			$response->setError('Erreur de requête de changement de statut. Attention les chèques sont validés.');
			$response->emit();
			exit;
		}
	}
	
	//Marque les règlements comme étant envoyées en compta (champ sent2compta)
	//TODO A vérifier pour 2 règlements sur la même facture ou un règlement pour 2 factures. cf plus haut dans l'export où on annule l'export des écritures d'encaissement
	function validateSend2ComptaReglements(Vtiger_Request $request){
		
		$excludeInvoicestatus = array('Created', 'Cancelled', 'Compta');
		$selectedIds = $request->get('selected_ids');
		//
		// Réglements (en tant que vtiger_rsnreglements) associés.
		// rappel : les réglements ne sont pas forcément définis dans vtiger_rsnreglements, ils peuvent étre uniquement présent par invoice.received et invoice.receivedmoderegl
		// il peut y avoir plusieurs vtiger_rsnreglements pour une facture, et inversement
		$query .= 'SELECT vtiger_invoice.invoiceid, vtiger_invoice.received
			, GROUP_CONCAT(rsnreglementsid) AS rsnreglementsid
			, GROUP_CONCAT(reglementstatus) AS reglementstatus
			, SUM(vtiger_rsnreglements.amount) AS reglementamount
			FROM vtiger_rsnreglements
			JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_rsnreglements.rsnreglementsid
			JOIN vtiger_crmentityrel
				ON vtiger_crmentityrel.crmid = vtiger_rsnreglements.rsnreglementsid
				OR vtiger_crmentityrel.relcrmid = vtiger_rsnreglements.rsnreglementsid
			JOIN vtiger_invoice
				ON vtiger_crmentityrel.crmid = vtiger_invoice.invoiceid
				OR vtiger_crmentityrel.relcrmid = vtiger_invoice.invoiceid
			WHERE vtiger_crmentity.deleted = 0
			AND vtiger_rsnreglements.reglementstatus != "Cancelled"
			AND vtiger_invoice.invoiceid IN ('. generateQuestionMarks( $selectedIds ) . ')
			AND NOT vtiger_invoice.invoicestatus IN ('.generateQuestionMarks($excludeInvoicestatus).')
			GROUP BY vtiger_invoice.invoiceid, vtiger_invoice.received
			HAVING SUM(vtiger_rsnreglements.amount) = vtiger_invoice.received
			AND NOT GROUP_CONCAT(reglementstatus) LIKE "%Compta%"
		';
		$params = array();
		$params = array_merge($params, $selectedIds);
		$params = array_merge($params, $excludeInvoicestatus);
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, $params);
		if(!$result){
			$db->echoError();
			echo "<pre>$query</pre>";
			var_dump($params);
			$response = new Vtiger_Response();
			$response->setError('Erreur de requête');
			$response->emit();
			exit;
		}
		//reconstitue le tableau des règlements à valider
		$reglementIds = array();
		while($invoice = $db->getNextRow($result)){
			$reglementIds = array_merge($reglementIds, explode(',', $invoice['rsnreglementsid']));
		}
		if($reglementIds){
			
			$query = 'UPDATE vtiger_rsnreglements
				SET vtiger_rsnreglements.sent2compta = NOW()
				, vtiger_rsnreglements.reglementstatus = ?
				WHERE vtiger_rsnreglements.rsnreglementsid IN ('. generateQuestionMarks( $reglementIds ) . ')
				AND vtiger_rsnreglements.sent2compta IS NULL
			';
			$params = array('Compta');
			$params = array_merge($params, $reglementIds);
			$result = $db->pquery($query, $params);
			if(!$result){
				$db->echoError();
				echo "<pre>$query</pre>";
				var_dump($params);
				$response = new Vtiger_Response();
				$response->setError('Erreur de requête');
				$response->emit();
				exit;
			}
		}
		
	}
	/*********************************
	 fin de validateSend2Compta		**
	 ********************************/
	
	//regénère le fichier et l'enregistre dans le répertoire storage
	function storeFile(Vtiger_Request $request){
		ob_start();
		$fileName = $this->downloadSend2Compta ($request, false);
		$fileContent =  ob_get_contents();
		ob_end_clean();
		global $root_directory;
		$firstDayOfMonth = strtotime(date("Y-m-01", time()));
		$weekNum = date("W") - date("W", $firstDayOfMonth) + (date('w', $firstDayOfMonth) === '0' ? 0 : 1);
		$path = $root_directory . '/storage/'.date('Y').'/'.date('F').'/week'.$weekNum;
		if(!file_exists($path))
			mkdir($path);
		file_put_contents($path.'/'.$fileName, $fileContent);
	}
	
	
}
