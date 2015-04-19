<?php
/* +***********************************************************************************
 * ED150418
 * Actions suite à la validation d'une facture
 * Règle de gestion
 * 	- Le montant de la facture dépasse 35 € : un n° de la revue "Merci de votre soutien" offert, création d'un record RSNAboRevues
 * 	- Article d'abonnement : création d'un record RSNAboRevues
 * 	- Article d'adhésion : TODO
 * *********************************************************************************** */

// Method registered as EntityMethod. See modules\RSN\RSN.php->add_invoice_handler()
// Method used in Workflow (Liste des gestionnaires de flux)
function handleRSNInvoiceSaved($entity){
	//var_dump($entity->data['LineItems']);
	//die(__FILE__.'  ICICI CI CI CI CI C');
}

// 2nd method : handler
require_once 'include/events/VTEventHandler.inc';
class RSNInvoiceHandler extends VTEventHandler {

	function handleEvent($eventName, $entity) {
		
		global $log, $adb;

		switch($eventName){
		case 'vtiger.entity.aftersave':
			$moduleName = $entity->getModuleName();
			switch ($moduleName){
			case 'Invoice' :
				$invoiceId = $entity->getId();
				$data = $entity->getData();
				//var_dump($data);
				//die(__FILE__.'  ICICI CI CI CI CI C');
				break;
			}
			break;
		}
	}
}