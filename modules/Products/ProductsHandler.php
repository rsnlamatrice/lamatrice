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
class ProductsHandler extends VTEventHandler {

	function handleEvent($eventName, $entity) {		
		global $log, $adb;

		switch($eventName){
		case 'vtiger.entity.aftersave':
			$moduleName = $entity->getModuleName();
			switch ($moduleName){
			case 'Products' :
				$this->handleAfterSaveProductEvent($entity, $moduleName);
				break;
			}
			break;
		}
	}
	    
	/* ED150507 Règles de gestion lors de la validation d'une facture
	*/
	//INSERT INTO `lamatrice`.`vtiger_eventhandlers` (`eventhandler_id`, `event_name`, `handler_path`, `handler_class`, `cond`, `is_active`, `dependent_on`) VALUES (NULL, 'vtiger.entity.aftersave', 'modules/Products/ProductsHandler.php', 'ProductsHandler', NULL, '1', '[]');
	public function handleAfterSaveProductEvent($entity, $moduleName){
		$productId = $entity->getId();

		autoUpdateLotQtyInStock($productId);
		$product = Vtiger_Record_Model::getInstanceById($productId, $moduleName);
		$parentProducts = $product->getParentProducts();
		foreach($parentProducts as $parentProduct) {
			autoUpdateLotQtyInStock($parentProduct->getId());
		}
	}
}