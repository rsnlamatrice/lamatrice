<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
define('ROWSEPAR', "\r\n");//'<tr><td>');
define('COLSEPAR', "\t");//'<td>');

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
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		
		$controller = new Vtiger_MassSave_Action();
		$query = $controller->getRecordsQueryFromRequest($request);
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
			AND NOT vtiger_invoice.invoicestatus IN (?)
			LIMIT 200 /*too long URL*/
		';
		$params = array('Cancelled');
		
		$selectedIds = array();
		$total = 0;
		
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, $params);
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
		
	function downloadSend2Compta (Vtiger_Request $request){
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
			
		$taxes = self::getAllTaxes();
			
		$selectedIds = $request->get('selected_ids');
		$query = 'SELECT vtiger_invoice.*
			, vtiger_invoicecf.receivedmoderegl
			, vtiger_invoicecf.receivedreference
			, IFNULL(vtiger_notescf.codeaffaire, vtiger_campaignscf.codeaffaire) AS codeaffaire
			, IFNULL(vtiger_products.productname, vtiger_service.servicename) AS productname
			, IFNULL(vtiger_products.productcode, vtiger_service.productcode) AS productcode
			, IFNULL(vtiger_products.glacct, vtiger_servicecf.glacct) AS productglacct
			, IFNULL(vtiger_productcf.sectionanal, vtiger_servicecf.sectionanal) AS productsectionanal
			, vtiger_inventoryproductrel.quantity
			, vtiger_inventoryproductrel.listprice
			, vtiger_inventoryproductrel.discount_percent
			, vtiger_inventoryproductrel.discount_amount
		';
		
		for($nTax = 0; $nTax < count($taxes); $nTax++){
			$query .= ', vtiger_inventoryproductrel.tax' . $taxes[$nTax]['taxid'];
		}
		$query .= '
			FROM vtiger_invoice
			JOIN vtiger_invoicecf
				ON vtiger_invoicecf.invoiceid = vtiger_invoice.invoiceid
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
				ON vtiger_servicecf.serviceid = vtiger_service.serviceid
			WHERE vtiger_invoice.invoiceid IN ('. generateQuestionMarks( $selectedIds ) . ')
			AND vtiger_invoicecf.sent2compta IS NULL
			AND vtiger_invoice.total <> 0
			AND vtiger_inventoryproductrel.listprice <> 0
			AND NOT vtiger_invoice.invoicestatus IN (?)
		';
		
		if(FALSE){
			$query .= " AND vtiger_invoice.invoice_no = 'COG1506635'";
		
		}
		
		if(FALSE){
			$query .= " AND vtiger_invoice.invoicedate > '2015-09-29'";
		
		}
		
		
			
		$query .= '
			ORDER BY vtiger_invoice.invoicedate ASC
			, vtiger_inventoryproductrel.sequence_no
		';
		$params = $selectedIds;
		$params[] = 'Cancelled';
		
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
			
			$fileName = 'LAM2Cogilog.Factures.Compta.'.date('YmdHis');
			$exportType = 'text/csv';
			if($isDebug)
				echo '<table border="1"><tr><td>';//debug
			else {
				header("Content-Disposition:attachment;filename=$fileName.csv");
				header("Content-Type:$exportType;charset=UTF-8");
				header("Expires: Mon, 31 Dec 2000 00:00:00 GMT" );
				header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT" );
				header("Cache-Control: post-check=0, pre-check=0", false );
			}
						
			echo "**Compta\tEcritures";
			
			
			/*
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
			 */
			
			$journalVente = 'VT';//TODO Paramétrable
			$prevInvoice = false;
			$prevInvoiceId = 0;
			$prevDate = '';
			$totalPerDate = array();
			while($invoice = $db->fetch_row($result)){
				$isInvoiceHeader = $prevInvoiceId != $invoice['invoiceid'];
				if($isInvoiceHeader){
					/* En-tête de facture */
					
					if($invoiceTaxes)//précédente facture
						$this->exportInvoiceTaxes($invoiceTaxes, $journalVente, $date, $piece, $invoiceSubject);
				
					if($invoiceTotal)//précédente facture
						$this->exportInvoiceSolde($invoiceTotal, $journalVente, $date, $piece, $invoiceSubject, $prevInvoice);
				
					/* En-tête de la précédente facture */
					if($invoiceJournal && $invoiceReceived){
						/* Ligne d'encaissement de la Facture */
						$this->exportEncaissement($invoiceJournal, $date, $piece, $invoiceCompteVente, $invoiceCodeAnal, $invoiceSubject, $invoiceReceived);
					}
						
					$invoiceTaxes = array();
					$invoiceTotal = 0;
					
					//ligne de facture de l'encaissement
					$codeAffaire = $invoice['codeaffaire'];
					$piece = $invoice['invoice_no'];
					$invoiceModeRegl = self::formatAmountForCogilog($invoice['receivedmoderegl']);
					$invoiceCompteVente = self::getCodeAffaireCompteVente($codeAffaire, $invoiceModeRegl);
					if($invoiceCompteVente[0] === '7' || $invoiceCompteVente[0] === '6')
						$invoiceCodeAnal = self::getCodeAffaireCodeAnal($codeAffaire);
					else	$invoiceCodeAnal = '';
					$invoiceJournal = self::getCodeAffaireJournal($codeAffaire);
					$invoiceAmount = self::formatAmountForCogilog($invoice['total']);
					$invoiceReceived = self::formatAmountForCogilog($invoice['received']);
					$invoiceSubject = preg_replace('/[\r\n\t]/', ' ', html_entity_decode( $invoice['subject']) . ' - ' . $codeAffaire);
					$date = self::formatDateForCogilog($invoice['invoicedate']);
					$key = $date . '-' . $invoiceJournal . '-' . $invoiceCodeAnal;
					if($invoiceReceived){
						if(!$totalPerDate[$key])
							$totalPerDate[$key] = array(
										    'journal' => $journalVente,
										    'codeAnal' => $invoiceCodeAnal,
										    'date' => $date,
										    'total' => 0.0
										);
						$totalPerDate[$key]['total'] += str_to_float($invoiceReceived);
					}
					
					$prevInvoiceId = $invoice['invoiceid'];
					$prevInvoice = $invoice;
					
				}
				
				/* ligne de produit */
				
				//Taxe utilisée
				$invoiceTotalTaxes = 0.0;
				for($nTax = 0; $nTax < count($taxes); $nTax++){
					$taxId = $taxes[$nTax]['taxid'];
					if($invoice['tax'.$taxId]){
						if(!array_key_exists("$taxId", $invoiceTaxes))
							$invoiceTaxes["$taxId"] = 0.0;
						$value = $invoice['tax'.$taxId] / 100 * $invoice['quantity'] * $invoice['listprice'];
						$invoiceTotalTaxes += $value;
						$invoiceTaxes["$taxId"] += $value;
						break;
					}
				}
				
				$compteVente = $invoice['productglacct'];
				if($compteVente[0] === '7' || $compteVente[0] === '6')
					$codeAnal = $invoiceCodeAnal ? $invoiceCodeAnal : $invoice['productsectionanal'];
				else	$codeAnal = '';
				$amount = self::formatAmountForCogilog($invoice['quantity'] * $invoice['listprice']);//HT
				//$productName = $invoice['productname'];
				echo ROWSEPAR.$journalVente
					.COLSEPAR.$date
					.COLSEPAR.$piece
					.COLSEPAR.$compteVente
					.COLSEPAR.$codeAnal
					.COLSEPAR.$invoiceSubject
					.COLSEPAR.''
					.COLSEPAR.''
					.COLSEPAR.$amount
				;
				$invoiceTotal += str_to_float($amount) + $invoiceTotalTaxes;
			}
		
			if($invoiceTaxes)//dernière facture
				$this->exportInvoiceTaxes($invoiceTaxes, $journalVente, $date, $piece, $invoiceSubject);
					
			if($invoiceTotal)//précédente facture
				$this->exportInvoiceSolde($invoiceTotal, $journalVente, $date, $piece, $invoiceSubject, $prevInvoice);
				
			/* En-tête de la dernière facture */
			if($invoiceJournal && $invoiceReceived){
				/* Ligne d'encaissement de la Facture */
				self::exportEncaissement($invoiceJournal, $date, $piece, $invoiceCompteVente, $invoiceCodeAnal, $invoiceSubject, $invoiceReceived);
			}
			
			/* Lignes des encaissements par jour */
			$compteEnc = self::getCodeAffaireCompteEncaissement(''); 
			foreach($totalPerDate as $key => $data){
				$total = $data['total'];
				$journal = $data['journal'];
				$date = $data['date'];
				if($compteEnc[0] === '7' || $compteEnc[0] === '6')
					$codeAnal = $data['codeAnal'];
				else	$codeAnal = '';
				$amount= self::formatAmountForCogilog($total);
				$piece = $key;
				$descriptif = 'Paiements du ' . $date;
				echo ROWSEPAR.$journal
					.COLSEPAR.$date
					.COLSEPAR.$piece
					.COLSEPAR.$compteEnc
					.COLSEPAR.$codeAnal
					.COLSEPAR.$descriptif
					.COLSEPAR.''
					.COLSEPAR.$amount
				;
				
			}
		}
		if($isDebug)
			echo '</table>';//debug
			
	}
	
	private function exportEncaissement($invoiceJournal, $date, $piece, $compteVente, $invoiceCodeAnal, $invoiceSubject, $invoiceAmount){
		/* Ligne d'encaissement de la Facture */
		echo ROWSEPAR.$invoiceJournal
			.COLSEPAR.$date
			.COLSEPAR.$piece
			.COLSEPAR.$compteVente
			.COLSEPAR.$invoiceCodeAnal
			.COLSEPAR.$invoiceSubject
			.COLSEPAR.''
			.COLSEPAR.''
			.COLSEPAR.$invoiceAmount
		;
	}
	
	private function exportInvoiceSolde($invoiceTTC, $journal, $date, $piece, $invoiceSubject, $invoiceData){
		$amount= self::formatAmountForCogilog($invoiceTTC);
		$account = self::getInvoiceCompteVenteSolde($invoiceData);
		echo ROWSEPAR.$journal
			.COLSEPAR.$date
			.COLSEPAR.$piece
			.COLSEPAR.$account
			.COLSEPAR
			.COLSEPAR.$invoiceSubject
			.COLSEPAR.''
			.COLSEPAR.$amount
		;
	}
	
	private function exportInvoiceTaxes($invoiceTaxes, $journal, $date, $piece, $invoiceSubject){
		foreach($invoiceTaxes as $taxId => $amount){
			$amount = self::formatAmountForCogilog($amount);
			$tax = self::getTax($taxId);
			$account = $tax['account'];
			echo ROWSEPAR.$journal
				.COLSEPAR.$date
				.COLSEPAR.$piece
				.COLSEPAR.$account
				.COLSEPAR
				.COLSEPAR.$invoiceSubject
				.COLSEPAR.''
				.COLSEPAR.''
				.COLSEPAR.$amount
			;
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
	
	private static function getCodeAffaireCompteVente($codeAffaire, $modeRegl){
		switch(strtoupper($codeAffaire)){
		case 'PAYBOX' :// (dons réguliers)  
			return '511104';
		case 'PAYBOXP' :
			return '511400';// (dons ponctuels)  
		case 'PAYPAL' :
			return '511300';
		default:
			switch($modeRegl){
			case 'PayPal' :
			case 'PayBox' :
				return '511101';
			}
			return '511200';//LBP
		}
	}
	
	private static function getInvoiceCompteVenteSolde($invoiceData){
		$modeRegl = $invoiceData['receivedmoderegl'];
		switch(strtoupper($invoiceData['invoicestatus'])){
		case 'PAID' :
			switch($modeRegl){
			case 'PayPal' :
			case 'PayBox' :
				return '511101';
			}
			return '511200';//LBP
		default:
			switch($invoiceData['typedossier']){
			case 'Facture de dépôt-vente' :
				return '411DEP';
		
			case 'CREDIT INVOICE' ://Avoir
			case 'Avoir' :
			case 'Remboursement' :
				return '511200';//TODO
			default:
				return '411000';
			}
		}
	}
	
	private static function getCodeAffaireCompteEncaissement($codeAffaire){
		switch(strtoupper($codeAffaire)){
		default :
			return '512107';//c/c Nef
		}
	}
	
	private static function getCodeAffaireCodeAnal($codeAffaire){
		switch(strtoupper($codeAffaire)){
		default :
			return strtoupper($codeAffaire);
		}
	}
	
	private static function getCodeAffaireJournal($codeAffaire){
		switch(strtoupper($codeAffaire)){
		case 'PAYBOX' :
		case 'PAYBOXP' :
			return 'BFC';
		default :
			return 'LBP';
		}
	}
	
	private static function formatDateForCogilog($myDate){
		$parts = explode('-', $myDate);
		return $parts[2] . '/' . $parts[1] . '/' . $parts[0];
	}
	
	private static function formatAmountForCogilog($amount){
		return str_replace('.', ',', round($amount, 3));
	}
	
	
	function validateSend2Compta(Vtiger_Request $request){
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		
		$selectedIds = $request->get('selected_ids');
		$query = 'UPDATE vtiger_invoicecf
			JOIN vtiger_invoice
				ON vtiger_invoicecf.invoiceid = vtiger_invoice.invoiceid
			SET vtiger_invoicecf.sent2compta = NOW()
			WHERE vtiger_invoice.invoiceid IN ('. generateQuestionMarks( $selectedIds ) . ')
			AND vtiger_invoicecf.sent2compta IS NULL
			AND NOT vtiger_invoice.invoicestatus IN (?)
		';
		$params = $selectedIds;
		$params[] = 'Cancelled';
		
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
	}
	
	
}
