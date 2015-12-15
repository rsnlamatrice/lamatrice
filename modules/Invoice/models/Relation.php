<?php
/*+***********************************************************************************
 * ED150417
 *************************************************************************************/

class Invoice_Relation_Model extends Inventory_Relation_Model {

	public function addRelation($sourcerecordId, $destinationRecordId) {
		$destinationModuleName = $this->getRelationModuleModel()->get('name');
		if($destinationModuleName === 'RsnReglements'){
			self::addInvoiceReglementRelation($sourcerecordId, $destinationRecordId);
		}
		else
			parent::addRelation($sourcerecordId, $destinationRecordId);
	}

	
	/**
	 * Associe le réglement à la facture.
	 * Met à jour la facture
	 */
	public static function addInvoiceReglementRelation($invoiceId, $rsnReglementsId, $receivedComments = false){
		if(!$rsnReglementsId)
			return;
		if(is_numeric($rsnReglementsId))
			$reglement = Vtiger_Record_Model::getInstanceById($rsnReglementsId, 'RsnReglements');
		else{
			$reglement = $rsnReglementsId;
			$rsnReglementsId = $reglement->getId();
		}
		if(!$reglement){
			echo("\nImpossible de retrouver le règlement " . $rsnReglementsId);
			return;
		}
		if(is_numeric($invoiceId))
			$invoice = Vtiger_Record_Model::getInstanceById($invoiceId, 'Invoice');
		else{
			$invoice = $invoiceId;
			$invoiceId = $invoice->getId();
		}
		if(!$invoice){
			echo("\nImpossible de retrouver la facture " . $invoiceId);
			return;
		}
		
		global $adb;
		$query = 'INSERT INTO vtiger_crmentityrel (crmid, module, relcrmid, relmodule)
			VALUES ( ?, \'Invoice\', ?, \'RsnReglements\')';
		$params = array($invoice->getId(), $reglement->getId());
		$result = $adb->pquery($query, $params);
		if(!$result) {//duplicate
			$adb->echoError();
			return false;
		}
		
		//TODO mieux gérer les règlements multiples
		
		//mise à jour de la facture
		$query = 'UPDATE vtiger_invoice
			JOIN vtiger_invoicecf
				ON vtiger_invoicecf.invoiceid = vtiger_invoice.invoiceid
			SET received = LEAST(total, IFNULL(received,0) + ?)
			, balance = received - total
			WHERE vtiger_invoice.invoiceid = ?
			AND (IFNULL(receivedreference, \'\') = \'\' OR (receivedreference <> ? AND receivedreference <> ?))';
			
		$amount = str_to_float($reglement->get('amount'));		
		$numpiece = decode_html($reglement->get('numpiece'));
		$params = array(
			$amount
			, $invoice->getId()
			, $reglement->get('numpiece'), $numpiece
		);
		
		$result = $adb->pquery($query, $params);
		
		if(!$result) {
			echo "<pre>$query</pre>";
			var_dump(/*$query,*/ $params);
			$adb->echoError();
			return false;
		}
		
		//mise à jour de la facture
		$query = 'UPDATE vtiger_invoicecf
			SET receivedreference = IF(receivedreference IS NULL OR receivedreference = \'\' OR (receivedreference = ? OR receivedreference = ?), ?, CONCAT(receivedreference, \', \', ?))
		';
		if($receivedComments !== false)
			$query .= ', receivedcomments = IF(receivedcomments IS NULL OR receivedcomments = \'\', ?, CONCAT(receivedcomments, \'\\n\', ?))
			';
		$query .= ', receivedmoderegl = ?
			, rsnbanque = IF(rsnbanque IS NULL OR rsnbanque = "" OR rsnbanque = "0", ?, rsnbanque)
			WHERE invoiceid = ?
			AND (IFNULL(receivedreference, \'\') = \'\' OR (receivedreference = ? AND receivedreference = ?))';
		
		$rsnbanque = decode_html($reglement->get('rsnbanque'));
		$rsnmoderegl = decode_html($reglement->get('rsnmoderegl'));
		$params = array($numpiece, $reglement->get('numpiece'), $numpiece, $numpiece);
		if($receivedComments !== false)
			array_push($params, $receivedComments, $receivedComments);
		array_push($params
			, $rsnmoderegl
			, $rsnbanque
			, $invoice->getId()
			, $reglement->get('numpiece'), $numpiece
		);
		
		$result = $adb->pquery($query, $params);
		
		if(!$result) {
			var_dump(/*$query,*/ $params);
			$adb->echoError();
			return false;
		}
		
		//Affectation du compte et passage au statut Validated
		$reglement->set('mode', 'edit');
		$reglement->set('account_id', $invoice->get('account_id'));
		if($reglement->get('reglementstatus') === 'Created')
			$reglement->set('reglementstatus', 'Validated');
		$reglement->save();
		
		return true;
	}
}