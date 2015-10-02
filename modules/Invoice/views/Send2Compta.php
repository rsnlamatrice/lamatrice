<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

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
		
		$controler = new Vtiger_MassSave_Action();
		$query = $controler->getRecordsQueryFromRequest($request);
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
			AND vtiger_invoice.total <> 0
			AND vtiger_invoice.invoicestatus IN (?)
			LIMIT 200 /*too long URL*/
		';
		$params = array('Paid');
		
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
		
		$selectedIds = $request->get('selected_ids');
		$query = 'SELECT vtiger_invoice.*
			, IFNULL(vtiger_notescf.codeaffaire, vtiger_campaignscf.codeaffaire) AS codeaffaire
			FROM vtiger_invoice
			JOIN vtiger_invoicecf
				ON vtiger_invoicecf.invoiceid = vtiger_invoice.invoiceid
			LEFT JOIN vtiger_notescf /*coupon*/
				ON vtiger_invoicecf.notesid = vtiger_notescf.notesid
			LEFT JOIN vtiger_campaignscf /*campagne*/
				ON vtiger_invoicecf.campaign_no = vtiger_campaignscf.campaignid
			WHERE vtiger_invoice.invoiceid IN ('. generateQuestionMarks( $selectedIds ) . ')
			AND vtiger_invoicecf.sent2compta IS NULL
			AND vtiger_invoice.total <> 0
			AND vtiger_invoice.invoicestatus IN (?)
			
			ORDER BY vtiger_invoice.invoicedate ASC
		';
		$params = $selectedIds;
		$params[] = 'Paid';
		
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
			$fileName = 'LAM2Cogilog.Factures.Compta.'.date('YmdHis');
			$exportType = 'text/csv';
			header("Content-Disposition:attachment;filename=$fileName.csv");
			header("Content-Type:$exportType;charset=UTF-8");
			header("Expires: Mon, 31 Dec 2000 00:00:00 GMT" );
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT" );
			header("Cache-Control: post-check=0, pre-check=0", false );
			
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
			 * 
			 */
			
			
			//$prevInvoiceId = 0;
			$prevDate = '';
			$totalPerDate = array();
			while($invoice = $db->fetch_row($result)){
				echo "\r\n";
				/*if($prevInvoiceId != $invoice['invoiceid']){
					$prevInvoiceId = $invoice['invoiceid'];
					foreach($invoice as $value)
						echo $value ."\t";
					echo "\r\n";
				}*/
				//ligne de facture
				$codeAffaire = $invoice['codeaffaire'];
				$piece = $invoice['invoice_no'];
				$compteVente = self::getCodeAffaireCompteVente($codeAffaire);
				$codeAnal = self::getCodeAffaireCodeAnal($codeAffaire);
				$journal = self::getCodeAffaireJournal($codeAffaire);
				$amount= str_replace('.', ',', $invoice['total']);
				$subject = preg_replace('/[\r\n\t]/', ' ', html_entity_decode( $invoice['subject']) . ' - ' . $codeAffaire);
				echo $journal
					."\t".self::formatDateForCogilog($invoice['invoicedate'])
					."\t".$piece
					."\t".$compteVente
					."\t".$codeAnal
					."\t".$subject
					."\t".''
					."\t".''
					."\t".$amount
				;
				$key = self::formatDateForCogilog($invoice['invoicedate']) . '-' . $journal . '-' . $codeAnal;
				if(!$totalPerDate[$key])
					$totalPerDate[$key] = array(
								    'journal' => $journal,
								    'codeAnal' => $codeAnal,
								    'date' => self::formatDateForCogilog($invoice['invoicedate']),
								    'total' => 0.0
								);
				$totalPerDate[$key]['total'] += str_to_float($invoice['total']);
			}
			
			//Lignes de soldes
			$compteEnc = self::getCodeAffaireCompteEncaissement(''); 
			foreach($totalPerDate as $key => $data){
				$total = $data['total'];
				$journal = $data['journal'];
				$date = $data['date'];
				$codeAnal = $data['codeAnal'];
				echo "\r\n";
				$amount= str_replace('.', ',', $total);
				$piece = $key;
				$descriptif = 'Paiements du ' . $date;
				echo $journal
					."\t".$date
					."\t".$piece
					."\t".$compteEnc
					."\t".$codeAnal
					."\t".$descriptif
					."\t".''
					."\t".$amount
				;
				
			}
		}
	}
	
	private static function getCodeAffaireCompteVente($codeAffaire){
		switch(strtoupper($codeAffaire)){
		case 'PAYBOX' :// (dons réguliers)  
			return '511104';
		case 'PAYBOXP' :
			return '511400';// (dons ponctuels)  
		case 'PAYPAL' :
			return '511300';
		case 'BOU' :
		case 'BOUTIQUE' :
			return '511101';
		default:
			return '511101';
		}
	}
	
	private static function getCodeAffaireCompteEncaissement($codeAffaire){
		switch(strtoupper($codeAffaire)){
		default :
			return '512107';
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
		case 'PAYPAL' :
			return 'VT';
		default :
			return 'BFC';
		}
	}
	
	private static function formatDateForCogilog($myDate){
		$parts = explode('-', $myDate);
		return $parts[2] . '/' . $parts[1] . '/' . $parts[0];
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
			AND vtiger_invoice.total <> 0
			AND vtiger_invoice.invoicestatus IN (?)
		';
		$params = $selectedIds;
		$params[] = 'Paid';
		
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
