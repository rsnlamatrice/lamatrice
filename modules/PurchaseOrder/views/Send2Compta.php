<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
if(1){
	define('ROWSEPAR', "\r\n");
	define('COLSEPAR', "\t");
} else { //debug
	define('ROWSEPAR', '<tr><td>');
	define('COLSEPAR', '<td>');
}
class PurchaseOrder_Send2Compta_View extends Invoice_Send2Compta_View {
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
	
		echo $viewer->view('Send2ComptaForm.tpl','Invoice',true);
	}
	//Controle des données avant affichage
	function validateInvoicesData(Vtiger_Request $request){
		
		$controller = new Vtiger_MassSave_Action();
		$query = $controller->getRecordsQueryFromRequest($request);
		//$query retourne autant de lignes que de lignes de factures
		$query = 'SELECT DISTINCT purchaseorderid
				FROM ('.$query.') _source_';
		$query = 'SELECT DISTINCT vtiger_purchaseorder.subject,
			IF(IFNULL(vtiger_productcf.compteachat, "") = "" AND IFNULL(vtiger_servicecf.compteachat, "") = ""
				, CONCAT(IF(vtiger_products.productid IS NULL, "Service", "Produit"), " : ", IFNULL(vtiger_products.productcode, vtiger_service.productcode), " - ", IFNULL(productname, servicename)), "") AS "produit",
			IF(IFNULL(vtiger_vendor.glacct, "") = "", vtiger_vendor.vendorname, "") AS "fournisseur"
			FROM ('.$query.') _source_ids_
			JOIN vtiger_purchaseorder
				ON vtiger_purchaseorder.purchaseorderid = _source_ids_.purchaseorderid
			JOIN vtiger_purchaseordercf
				ON vtiger_purchaseordercf.purchaseorderid = vtiger_purchaseorder.purchaseorderid
			LEFT JOIN vtiger_vendor /*fournisseur*/
				ON vtiger_vendor.vendorid = vtiger_purchaseorder.vendorid
			LEFT JOIN vtiger_inventoryproductrel
				ON vtiger_inventoryproductrel.id = vtiger_purchaseorder.purchaseorderid
			LEFT JOIN vtiger_products
				ON vtiger_inventoryproductrel.productid = vtiger_products.productid
			LEFT JOIN vtiger_productcf
				ON vtiger_productcf.productid = vtiger_products.productid
			LEFT JOIN vtiger_service
				ON vtiger_service.serviceid = vtiger_inventoryproductrel.productid
			LEFT JOIN vtiger_servicecf
				ON vtiger_servicecf.serviceid = vtiger_inventoryproductrel.productid
			WHERE vtiger_purchaseordercf.sent2compta IS NULL
			AND vtiger_purchaseorder.total <> 0
			AND vtiger_purchaseorder.postatus IN (?)
			AND vtiger_purchaseorder.potype = \'invoice\'
			AND ((vtiger_products.productid IS NOT NULL AND IFNULL(vtiger_productcf.compteachat, "") = "")
					OR (vtiger_servicecf.serviceid IS NOT NULL AND IFNULL(vtiger_servicecf.compteachat, "") = "")
				OR IFNULL(vtiger_vendor.glacct, "") = "")
		';
		$params = array('Approved');
		
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, $params);
		if(!$result){
			$db->echoError();
			echo "<pre>$query</pre>";
			return false;
		}
		elseif($db->getRowCount($result)) {
			echo "<ul><h4 style=\"color: red;\">Produits ou fournisseurs sans compte !!!</h4>";
			while($row = $db->fetch_row($result)){
				//var_dump($row);
				if($row['produit'])
					echo "<li>".$row['produit']
					." (dans la facture ".$row['subject'].")"
					."</li>";
				if($row['fournisseur'])
					echo "<li>Fournisseur : ".$row['fournisseur']
					." (dans la facture ".$row['subject'].")"
					."</li>";
				if(!$row['produit'] && !$row['fournisseur'])
					echo "<li>Facture vide : ".$row['subject']."</li>";
			}
			echo "</ul>";
			return false;
		}
		return true;
	}
	
	function initSend2ComptaForm (Vtiger_Request $request){
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		
		$controller = new Vtiger_MassSave_Action();
		$query = $controller->getRecordsQueryFromRequest($request);
		//$query retourne autant de lignes que de lignes de factures
		$query = 'SELECT DISTINCT purchaseorderid
				FROM ('.$query.') _source_';
		$query = 'SELECT vtiger_purchaseorder.purchaseorderid, vtiger_purchaseorder.total
			FROM ('.$query.') _source_ids_
			JOIN vtiger_purchaseorder
				ON vtiger_purchaseorder.purchaseorderid = _source_ids_.purchaseorderid
			JOIN vtiger_purchaseordercf
				ON vtiger_purchaseordercf.purchaseorderid = vtiger_purchaseorder.purchaseorderid
			WHERE vtiger_purchaseordercf.sent2compta IS NULL
			AND vtiger_purchaseorder.total <> 0
			AND vtiger_purchaseorder.postatus IN (?)
			AND vtiger_purchaseorder.potype = \'invoice\'
			LIMIT 200 /*too long URL*/
		';
		$params = array('Approved');
		
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
				$selectedIds[] = $invoice['purchaseorderid'];
			}
			
			$viewer->assign('INVOICES_TOTAL', $total);
			$viewer->assign('INVOICES_COUNT', count($selectedIds));
		}
		$viewer->assign('SELECTED_IDS', $selectedIds);
	}
	
	/******************************************
	 *
	 *	downloadSend2Compta
	 *
	 *	les données doivent être exportées triées par journal et par mois, sinon Cogilog refuse l'import
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
	
	function downloadSend2Compta (Vtiger_Request $request){
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
			
		$taxes = self::getAllTaxes();
			
		$selectedIds = $request->get('selected_ids');
		$query = 'SELECT vtiger_purchaseorder.*
			, vtiger_vendor.glacct AS vendor_account
			, vendorcode
			, vendorname
			, IFNULL(vtiger_products.productname, vtiger_service.servicename) AS productname
			, IFNULL(vtiger_products.productcode, vtiger_service.productcode) AS productcode
			, IFNULL(vtiger_productcf.compteachat, vtiger_servicecf.compteachat) AS compteachat
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
			FROM vtiger_purchaseorder
			JOIN vtiger_purchaseordercf
				ON vtiger_purchaseordercf.purchaseorderid = vtiger_purchaseorder.purchaseorderid
			/*fournisseur*/
			LEFT JOIN vtiger_vendor
				ON vtiger_vendor.vendorid = vtiger_purchaseorder.vendorid
			LEFT JOIN vtiger_vendorcf
				ON vtiger_vendorcf.vendorid = vtiger_vendor.vendorid
			
			LEFT JOIN vtiger_inventoryproductrel
				ON vtiger_inventoryproductrel.id = vtiger_purchaseorder.purchaseorderid
				
			LEFT JOIN vtiger_products
				ON vtiger_inventoryproductrel.productid = vtiger_products.productid
			LEFT JOIN vtiger_productcf
				ON vtiger_productcf.productid = vtiger_products.productid
				
			LEFT JOIN vtiger_service
				ON vtiger_service.serviceid = vtiger_inventoryproductrel.productid
			LEFT JOIN vtiger_servicecf
				ON vtiger_servicecf.serviceid = vtiger_inventoryproductrel.productid
				
			WHERE vtiger_purchaseorder.purchaseorderid IN ('. generateQuestionMarks( $selectedIds ) . ')
			AND vtiger_purchaseordercf.sent2compta IS NULL
			AND vtiger_purchaseorder.total <> 0
			AND vtiger_inventoryproductrel.listprice <> 0
			AND vtiger_purchaseorder.postatus IN (?)
			AND vtiger_purchaseorder.potype = \'invoice\'
		';
		
		if(FALSE){
			$query .= " AND vtiger_purchaseorder.invoice_no = 'COG1506635'";
		
		}
		
		if(FALSE){
			$query .= " AND vtiger_purchaseorder.invoicedate > '2015-09-29'";
		
		}
		
		
			
		$query .= '
			ORDER BY vtiger_purchaseorder.purchaseorderid ASC
			, vtiger_inventoryproductrel.sequence_no
		';
		$params = $selectedIds;
		$params[] = 'Approved';
		
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
			
			$fileName = 'EcrituresCompta.FFournisseurs.Compta.'.date('Ymd_His').'.csv';
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
						
			$this->addExportRow( "**Compta\tEcritures" );
			
			
			/*
			 * 1) Factures et le détail des lignes
			 * Journal : "AC"
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
			 * non géré
			 * 
			 * ******
			 * ******
			 * Seuls les comptes en 6 ou en 7 on une section analytique
			 *
			 *
			 * ATTENTION tout ceci est une vieille copie de invoice::send2compta : A REFAIRE !
			 */
			
			$journalAchat = 'AC';//TODO Paramétrable
			$prevpurchaseorderid = 0;
			$prevDate = '';
			while($invoice = $db->fetch_row($result, false)){
				$isInvoiceHeader = $prevpurchaseorderid != $invoice['purchaseorderid'];
				if($isInvoiceHeader){
					/* En-tête de facture */
					$prevpurchaseorderid = $invoice['purchaseorderid'];
				
					if($invoiceTaxes)//précédente facture
						$this->exportInvoiceTaxes($invoiceTaxes, $journalAchat, $date, $piece, $invoiceSubject);
				
					if($invoiceTotal)//précédente facture
						$this->exportInvoiceVendorAccount($invoiceTotal, $journalAchat, $date, $piece, $invoiceSubject, $vendorAccount);
				
					$invoiceTaxes = array();
					$invoiceTotal = 0;
					
					//ligne de facture de l'encaissement
					$piece = $invoice['purchaseorder_no'];
					$invoiceAmount = self::formatAmountForCogilog($invoice['total']);
					$vendorAccount = self::getVendorAccount($invoice);
					$vendorCode = !$invoice['vendorcode'] || stricmp($invoice['vendorcode'], $invoice['vendor_account']) === 0 ? $invoice['vendorname'] : $invoice['vendorcode'];
					$invoiceSubject = preg_replace('/[\r\n\t]/', ' ', html_entity_decode( $invoice['subject']));
					if(stripos($invoiceSubject, $vendorCode) === false)
						$invoiceSubject = $vendorCode . ' - ' . $invoiceSubject;
					$date = self::formatDateForCogilog($invoice['duedate']);
				}
				
				/* ligne de produit */
				/* ligne de produit */
				
				$amountHT = round($invoice['quantity'] * $invoice['listprice'], 2);//HT
				
				//Taxe utilisée
				$invoiceTotalTaxes = 0.0;
				for($nTax = 0; $nTax < count($taxes); $nTax++){
					$taxId = $taxes[$nTax]['taxid'];
					if($invoice['tax'.$taxId]){
						if(!array_key_exists("$taxId", $invoiceTaxes))
							$invoiceTaxes["$taxId"] = 0.0;
						//Passage par le TTC, arrondi à 2 chiffres et retrait du HT pour éviter les écarts d'arrondis
						$value = round((1 + $invoice['tax'.$taxId] / 100) * $invoice['quantity'] * $invoice['listprice'], 2) - $amountHT;
						$invoiceTotalTaxes += $value;
						$invoiceTaxes["$taxId"] += $value;
						break;
					}
				}
				
				$compteAchat = $invoice['compteachat'];
				if($compteAchat[0] === '7' || $compteAchat[0] === '6')
					$codeAnal = $invoiceCodeAnal ? $invoiceCodeAnal : $invoice['productsectionanal'];
				else	$codeAnal = '';
					$amount = self::formatAmountForCogilog($invoice['quantity'] * $invoice['listprice']);//HT
				$lineItem = $vendorCode
					. " - " . (stripos($invoice['productname'], 'Frais de port') === false ? self::formatAmountForCogilog($invoice['quantity']) . " ex " : '')
					. $invoice['productname'];
				$this->addExportRow($journalAchat,
					$date,
					$piece
					.COLSEPAR.$compteAchat
					.COLSEPAR.$codeAnal
					.COLSEPAR.$this->sanitizeExport($lineItem)
					.COLSEPAR.''
					.COLSEPAR.self::formatAmountForCogilog($amountHT)
					.COLSEPAR.''
				);
				$invoiceTotal += $amountHT + $invoiceTotalTaxes;
			}
			
			if($invoiceTaxes)//dernière facture
				$this->exportInvoiceTaxes($invoiceTaxes, $journalAchat, $date, $piece, $invoiceSubject);
				
			if($invoiceTotal)//précédente facture
				$this->exportInvoiceVendorAccount($invoiceTotal, $journalAchat, $date, $piece, $invoiceSubject, $vendorAccount);
			
		}
		$this->echoExportBuffer();
		if($isDebug)
			echo '</table>';//debug
			
	}
	
	private function exportInvoiceVendorAccount($invoiceTTC, $journal, $date, $piece, $invoiceSubject, $vendorAccount){
		$amount= self::formatAmountForCogilog($invoiceTTC);
		$this->addExportRow($journal,
			$date,
			$piece
			.COLSEPAR.$vendorAccount
			.COLSEPAR
			.COLSEPAR.$invoiceSubject
			.COLSEPAR.''
			.COLSEPAR.''
			.COLSEPAR.$amount
		);
	}
	
	private function exportInvoiceTaxes($invoiceTaxes, $journal, $date, $piece, $invoiceSubject){
		$taxes = self::getAllTaxes();
		foreach($invoiceTaxes as $invoiceTaxe => $amount){
			$amount= self::formatAmountForCogilog($amount);
			$tax = $taxes[$invoiceTaxe];
			
			$account = '445641'; //TODO ajouter un compte TVA d'achat mais il faut peut être aussi ajouter la saisie d'une taxe d'achat différente de la vente. $tax['compteachat'];
			
			$this->addExportRow($journal,
				$date,
				$piece
				.COLSEPAR.$account
				.COLSEPAR
				.COLSEPAR.$invoiceSubject
				.COLSEPAR.''
				.COLSEPAR.$amount
				.COLSEPAR.''
			);
		}
	}
	
	static $allTaxes = false;//cache
	private static function getAllTaxes(){
		if(!self::$allTaxes)
			self::$allTaxes = getAllTaxes();
		return self::$allTaxes;
	}
	
	private static function getVendorAccount($invoiceData){
		if($invoiceData['vendor_account'])
			return $invoiceData['vendor_account'];
		return 411000;
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
		$query = 'UPDATE vtiger_purchaseordercf
			JOIN vtiger_purchaseorder
				ON vtiger_purchaseordercf.purchaseorderid = vtiger_purchaseorder.purchaseorderid
			SET vtiger_purchaseordercf.sent2compta = NOW()
			, vtiger_purchaseorder.postatus = "Compta"
			WHERE vtiger_purchaseorder.purchaseorderid IN ('. generateQuestionMarks( $selectedIds ) . ')
			AND vtiger_purchaseordercf.sent2compta IS NULL
			AND vtiger_purchaseorder.total <> 0
			AND vtiger_purchaseorder.postatus IN (?)
			AND vtiger_purchaseorder.potype = \'invoice\'
		';
		$params = $selectedIds;
		$params[] = 'Approved';
		
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
