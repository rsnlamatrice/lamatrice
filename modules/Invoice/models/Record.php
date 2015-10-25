<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

/**
 * Inventory Record Model Class
 */
class Invoice_Record_Model extends Inventory_Record_Model {

	
	/**
	 * ED151022
	 * Mise à jour des éléments de réglements d'après les RsnReglements liés
	 */
	public function updateReceivedFromRelated(){
		if(str_to_float($this->get('received')))
			return 'Cette facture contient déjà un montant de réglement. Veuillez modifier manuellement le montant.'; //déjà une affectation
		
		$invoiceAmount = str_to_float($this->get('hdnGrandTotal'));
		if(!$invoiceAmount)
			return;
		
		$relatedModule = 'RsnReglements';
		//Recherche des réglements relatifs qui ne soient pas liés à d'autres factures
		$query = 'SELECT vtiger_rsnreglements.rsnreglementsid, vtiger_rsnreglements.numpiece, vtiger_rsnreglements.rsnmoderegl, vtiger_rsnreglements.dateregl
			, vtiger_rsnreglements.amount
			, vtiger_rsnreglements.amount - IFNULL(others_invoices.total, 0) AS available_amount
			FROM vtiger_rsnreglements
			JOIN vtiger_crmentity
				ON vtiger_rsnreglements.rsnreglementsid = vtiger_crmentity.crmid
			JOIN vtiger_crmentityrel
				ON vtiger_crmentityrel.relcrmid = vtiger_rsnreglements.rsnreglementsid
			JOIN vtiger_crmentity vtiger_crmentity_invoice
				ON vtiger_crmentityrel.crmid = vtiger_crmentity_invoice.crmid
			LEFT JOIN ( /* Calcul des utilisations des mêmes réglements pour d autres factures */
				SELECT vtiger_crmentityrel.relcrmid AS rsnreglementsid
				, SUM(vtiger_invoice.total) AS total
				FROM vtiger_crmentityrel
				JOIN vtiger_crmentity
					ON vtiger_crmentityrel.crmid = vtiger_crmentity.crmid
				JOIN vtiger_invoice
					ON vtiger_invoice.invoiceid = vtiger_crmentity.crmid
				WHERE vtiger_crmentity.deleted = 0
				AND vtiger_crmentityrel.crmid <> ?
				GROUP BY vtiger_crmentityrel.relcrmid
			) others_invoices
				ON vtiger_rsnreglements.rsnreglementsid = others_invoices.rsnreglementsid
			WHERE vtiger_crmentity.deleted = 0
			AND vtiger_crmentity_invoice.deleted = 0
			AND vtiger_crmentity_invoice.crmid = ?
			AND ABS(vtiger_rsnreglements.amount - IFNULL(others_invoices.total, 0)) >= 0.01
		';
		$params = array($this->getId(), $this->getId());
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, $params);
		if(!$result){
			echo "<pre>$query</pre>";
			var_dump($params);
			$db->echoError('updateReceivedFromRelated : Erreur');
			return;
		}
		
		$rowsCount = $db->num_rows($result);
		$reglTotalAmount = 0.0;
		$reglComments = array();
		$reglRefs = array();
		$reglTypeRegl = '';
		for($nRow = 0; $nRow < $rowsCount; $nRow++){
			$reglAmount = str_to_float($db->query_result($result, $nRow, 'available_amount'));
			if($nRow === 0)
				$reglTypeRegl = $db->query_result($result, $nRow, 'rsnmoderegl');
			$reglRef = $db->query_result($result, $nRow, 'numpiece');
			$reglRefs[] = $reglRef;
			if(($reglTotalAmount + $reglAmount - $invoiceAmount) > -0.01 ){
				$reglTotalAmount = $invoiceAmount;
				break;
			}
			$reglTotalAmount += $reglAmount;
				
		}
		if(!$reglTotalAmount)
			return;
		$this->set('mode', 'edit');
		$this->set('received', $reglTotalAmount);
		$this->set('receivedmoderegl', $reglTypeRegl);
		$this->set('receivedreference', implode(', ', $reglRefs));
		$this->set('receivedcomments', implode(', ', $reglComments));
		//$this->save(); ne fonctionne pas, il faut initialiser toutes les données des lignes
		
		$query = "UPDATE vtiger_invoice
            JOIN vtiger_invoicecf
                ON vtiger_invoice.invoiceid = vtiger_invoicecf.invoiceid
            SET vtiger_invoice.received = ?
            , vtiger_invoicecf.receivedmoderegl = ?
            , vtiger_invoicecf.receivedreference = ?
            , vtiger_invoicecf.receivedcomments = ?
            , vtiger_invoice.balance = vtiger_invoice.total - vtiger_invoice.received
            , vtiger_invoice.invoicestatus = IF(ABS(vtiger_invoice.balance) < 0.01, ?, vtiger_invoice.invoicestatus)
            WHERE vtiger_invoice.invoiceid = ?
        ";
        $params = array($reglTotalAmount
                        , $reglTypeRegl
                        , implode(', ', $reglRefs)
                        , implode(', ', $reglComments)
                        , 'Paid'
                        , $this->getId());
		$result = $db->pquery($query, $params);
		if(!$result){
			echo "<pre>$query</pre>";
			var_dump($params);
			$db->echoError('updateReceivedFromRelated : Erreur');
			return;
		}
	}
}
