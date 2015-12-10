<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 ********************************************************************************/
/*********************************************************************************
 * $Header$
 * Description:  Defines the Account SugarBean Account entity with the necessary
 * methods and variables.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

include_once('config.php');
require_once('include/logging.php');
require_once('include/database/PearDatabase.php');
require_once('include/utils/utils.php');
require_once('user_privileges/default_module_view.php');

// Account is used to store vtiger_account information.
class SalesOrder extends CRMEntity {
	var $log;
	var $db;

	var $table_name = "vtiger_salesorder";
	var $table_index= 'salesorderid';
	var $tab_name = Array('vtiger_crmentity','vtiger_salesorder','vtiger_sobillads','vtiger_soshipads','vtiger_salesordercf','vtiger_invoice_recurring_info','vtiger_inventoryproductrel');
	var $tab_name_index = Array('vtiger_crmentity'=>'crmid','vtiger_salesorder'=>'salesorderid','vtiger_sobillads'=>'sobilladdressid','vtiger_soshipads'=>'soshipaddressid','vtiger_salesordercf'=>'salesorderid','vtiger_invoice_recurring_info'=>'salesorderid','vtiger_inventoryproductrel'=>'id');
	/**
	 * Mandatory table for supporting custom fields.
	 */
	var $customFieldTable = Array('vtiger_salesordercf', 'salesorderid');
	var $entity_table = "vtiger_crmentity";

	var $billadr_table = "vtiger_sobillads";

	var $object_name = "SalesOrder";

	var $new_schema = true;

	var $update_product_array = Array();

	var $column_fields = Array();

	var $sortby_fields = Array('subject','smownerid','accountname','lastname');

	// This is used to retrieve related vtiger_fields from form posts.
	var $additional_column_fields = Array('assigned_user_name', 'smownerid', 'opportunity_id', 'case_id', 'contact_id', 'task_id', 'note_id', 'meeting_id', 'call_id', 'email_id', 'parent_name', 'member_id' );

	// This is the list of vtiger_fields that are in the lists.
	var $list_fields = Array(
				// Module Sequence Numbering
				//'Order No'=>Array('crmentity'=>'crmid'),
				'Order No'=>Array('salesorder','salesorder_no'),
				// END
				'Subject'=>Array('salesorder'=>'subject'),
				'Account Name'=>Array('account'=>'accountid'),
				'Quote Name'=>Array('quotes'=>'quoteid'),
				'Total'=>Array('salesorder'=>'total'),
				'Assigned To'=>Array('crmentity'=>'smownerid')
				);

	var $list_fields_name = Array(
				        'Order No'=>'salesorder_no',
				        'Subject'=>'subject',
				        'Account Name'=>'account_id',
				        'Quote Name'=>'quote_id',
					'Total'=>'hdnGrandTotal',
				        'Assigned To'=>'assigned_user_id'
				      );
	var $list_link_field= 'subject';

	var $search_fields = Array(
				'Order No'=>Array('salesorder'=>'salesorder_no'),
				'Subject'=>Array('salesorder'=>'subject'),
				'Account Name'=>Array('account'=>'accountid'),
				'Quote Name'=>Array('salesorder'=>'quoteid')
				);

	var $search_fields_name = Array(
					'Order No'=>'salesorder_no',
				        'Subject'=>'subject',
				        'Account Name'=>'account_id',
				        'Quote Name'=>'quote_id'
				      );

	// This is the list of vtiger_fields that are required.
	var $required_fields =  array("accountname"=>1);

	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'subject';
	var $default_sort_order = 'ASC';
	//var $groupTable = Array('vtiger_sogrouprelation','salesorderid');

	var $mandatory_fields = Array('subject','createdtime' ,'modifiedtime', 'assigned_user_id');

	// For Alphabetical search
	var $def_basicsearch_col = 'subject';

	// For workflows update field tasks is deleted all the lineitems.
	var $isLineItemUpdate = true;
	
	//ED50713 : liste des sostatus qui permettent le calcul des quantités en demande
	var $statusForQtyInDemand = array('Created', 'Approved', 'Delivered');
	
	/** Constructor Function for SalesOrder class
	 *  This function creates an instance of LoggerManager class using getLogger method
	 *  creates an instance for PearDatabase class and get values for column_fields array of SalesOrder class.
	 */
	function SalesOrder() {
		$this->log =LoggerManager::getLogger('SalesOrder');
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('SalesOrder');
	}

	function save_module($module)
	{

		//Checking if quote_id is present and updating the quote status
		if($this->column_fields["quote_id"] != ''){
			$qt_id = $this->column_fields["quote_id"];
			$query1 = "update vtiger_quotes set quotestage='Accepted' where quoteid=?";
			$this->db->pquery($query1, array($qt_id));
		}

		//ED50713 requested productid
		global $adb;
		$requestProductIdsList = $requestQuantitiesList = array();
		$totalNoOfProducts = $_REQUEST['totalProductCount'];
		for($i=1; $i<=$totalNoOfProducts; $i++) {
			$productId = $_REQUEST['hdnProductId'.$i];
			$productIdsList[$productId] = $productId;
		}
		//Old products
		$recordId = $this->id;
		$result = $adb->pquery("SELECT productid FROM vtiger_inventoryproductrel WHERE id = ?", array($recordId));
		$numOfRows = $adb->num_rows($result);
		for ($i=0; $i<$numOfRows; $i++) {
			$productId = $adb->query_result($result, $i, 'productid');
			$productIdsList[$productId] = $productId;
		}
		
		//in ajax save we should not call this function, because this will delete all the existing product values
		if($_REQUEST['action'] != 'SalesOrderAjax' && $_REQUEST['ajxaction'] != 'DETAILVIEW'
				&& $_REQUEST['action'] != 'MassEditSave' && $_REQUEST['action'] != 'ProcessDuplicates'
				&& $_REQUEST['action'] != 'SaveAjax' && $this->isLineItemUpdate != false) {
			
			//Based on the total Number of rows we will save the product relationship with this entity
			saveInventoryProductDetails($this, 'SalesOrder');
		}
		
		//ED151210 gestion du solde
		if($this->column_fields['typedossier'] !== 'Solde'){
			$contactId = $this->column_fields["contact_id"];
			$this->updateSaleOrderSolde($contactId);
		}
		
		//ED150708 refreshes qty in demand
		if($productIdsList)
			$this->refreshQtyInDemand($productIdsList);

		// Update the currency id and the conversion rate for the sales order
		$update_query = "update vtiger_salesorder set currency_id=?, conversion_rate=? where salesorderid=?";
		$update_params = array($this->column_fields['currency_id'], $this->column_fields['conversion_rate'], $this->id);
		$this->db->pquery($update_query, $update_params);
	}

	/** ED150713
	 * Recalcule les quantités demandées sur toutes les commandes client liées aux produits
	 * TODO sub products
	 */
	function refreshQtyInDemand($productIdsList = false, $recordId = false){
		global $adb, $log;
		$log->debug("Entering refreshQtyInDemand(".print_r($productIdsList, true).") method ...");
		if(!$recordId)
			$recordId = $this->id;
		/* Mise à jour de vtiger_products.qtyindemand pour tous les produits en question, même sans salesorder liée (d'où le LEFT) */
		$sql = "UPDATE vtiger_products
			LEFT JOIN (
				SELECT vtiger_inventoryproductrel.productid, SUM(vtiger_inventoryproductrel.quantity) AS quantity
				FROM (
					SELECT DISTINCT productid
					FROM vtiger_inventoryproductrel
					" . ($recordId ? "WHERE id = ?" : "")."
				) this_so_products
				JOIN vtiger_inventoryproductrel
					ON vtiger_inventoryproductrel.productid = this_so_products.productid
				JOIN vtiger_salesorder
					ON vtiger_salesorder.salesorderid = vtiger_inventoryproductrel.id
				JOIN vtiger_crmentity
					ON vtiger_salesorder.salesorderid = vtiger_crmentity.crmid
				WHERE vtiger_crmentity.deleted = 0
				AND vtiger_salesorder.typedossier = 'Solde'
				AND vtiger_salesorder.sostatus IN (" . generateQuestionMarks($this->statusForQtyInDemand) . ")
			".($productIdsList
				? " AND vtiger_inventoryproductrel.productid IN (" . generateQuestionMarks($productIdsList) . ")"
				: ""
			)."
				GROUP BY vtiger_inventoryproductrel.productid
			) _calculation_
			ON vtiger_products.productid = _calculation_.productid
			SET vtiger_products.qtyindemand = IFNULL(quantity, 0)
			".($productIdsList
				? " WHERE vtiger_products.productid IN (" . generateQuestionMarks($productIdsList) . ")"
				: ""
			)."
		";
		$params = array();
		if($recordId)
			$params[] = $recordId;
		$params = array_merge($params, $this->statusForQtyInDemand);
		if($productIdsList){
			$params = array_merge($params, $productIdsList);
			$params = array_merge($params, $productIdsList);
		}
		$result = $adb->pquery($sql, $params);
		if(!$result){
			$adb->echoError('Erreur de mise à jour des quantités demandées');
			echo "<pre>$sql</pre>";
			echo "<h5>Paramètres :</h5>";
			var_dump($params);
			echo_callstack();
			$log->debug("Exiting refreshQtyInDemand method : Erreur de mise à jour des quantités demandées");
			die();
		}
		$log->debug("Exiting refreshQtyInDemand method ...");
	}
	
	/*******************************
	 * GESTION DU DOSSIER DE SOLDE
	 *******************************/
	
	/**
	 * Création du dossier de solde (typeddosier === 'Solde')
	 * 1) le dossier de solde est celui qui a la date de création la plus récente
	 * 2) Si le saleorder le plus récent n'est pas de typedossier 'Solde', il est créé.
	 * 3) Le dossier de solde reprend le précédent dossier de solde et ajoute les produits des dossiers de type 'Variation' suivants
	 */
	function updateSaleOrderSolde($contactId){
		$dossiers = $this->getContactDepotsVentesInfos($contactId);
		$variationIds = $this->getVariationIds($dossiers);
		$soldeReferenceId = $this->getSoldeReferenceId($dossiers);
		$soldeActifId = $this->getSoldeActifId($dossiers);
		if(!$soldeReferenceId && !$soldeActifId
		|| $soldeReferenceId == $soldeActifId
		|| !$variationIds)
			//rien à faire
			return;
		$this->insertSaleOrderSoldeRows($soldeActifId, $soldeReferenceId, $variationIds);
		$this->setSaleOrderTotal($soldeActifId);
	}
	
	// reset des lignes dans vtiger_inventoryproductrel
	private function insertSaleOrderSoldeRows($soldeActifId, $soldeReferenceId, $variationIds){
		global $adb;
		//purge des produits du solde actif
		$query = 'DELETE FROM vtiger_inventoryproductrel
			WHERE id = ?';
		$adb->pquery($query, array($soldeActifId));
		$query = 'DELETE FROM vtiger_inventorysubproductrel
			WHERE id = ?';
		$adb->pquery($query, array($soldeActifId));
		
		//insertion des lignes de produits pour le solde actif
		
		//sélection des produits
		$params = $variationIds;
		if($soldeReferenceId)
			array_push($params, $soldeReferenceId);
		$query = 'SELECT `vtiger_inventoryproductrel`.`productid`
			, IFNULL(vtiger_products.unit_price, vtiger_service.unit_price) AS `listprice`
			, MAX(`vtiger_inventoryproductrel`.`sequence_no`) AS `sequence_no`
			, SUM(`vtiger_inventoryproductrel`.`quantity`) AS `quantity`
			, MAX(`vtiger_inventoryproductrel`.`discount_percent`)
			, SUM(`vtiger_inventoryproductrel`.`discount_amount`)
			, GROUP_CONCAT(DISTINCT `vtiger_inventoryproductrel`.`comment` SEPARATOR " - ") AS `comment`
			, GROUP_CONCAT(DISTINCT `vtiger_inventoryproductrel`.`description` SEPARATOR " - ") AS `description`
			, MAX(`vtiger_inventoryproductrel`.`incrementondel`) AS `incrementondel`
			
			/* pas sur que le nombre de taxes ne soit pas dynamique, mais a vrai dire, on s en fiche pour les salesorder */
			, MIN(`vtiger_inventoryproductrel`.`tax1`) AS `tax1`
			, MIN(`vtiger_inventoryproductrel`.`tax2`) AS `tax2`
			, MIN(`vtiger_inventoryproductrel`.`tax3`) AS `tax3`
			, MIN(`vtiger_inventoryproductrel`.`tax4`) AS `tax4`
			, MIN(`vtiger_inventoryproductrel`.`tax5`) AS `tax5`
			, MIN(`vtiger_inventoryproductrel`.`tax6`) AS `tax6`
			
			FROM `vtiger_inventoryproductrel`
			JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = `vtiger_inventoryproductrel`.id
			JOIN vtiger_crmentity AS vtiger_crmentity_products
				ON vtiger_crmentity_products.crmid = `vtiger_inventoryproductrel`.productid
			LEFT JOIN vtiger_products
				ON vtiger_products.productid = `vtiger_inventoryproductrel`.productid
			LEFT JOIN vtiger_service
				ON vtiger_service.serviceid = `vtiger_inventoryproductrel`.productid
			WHERE vtiger_crmentity_products.deleted = 0
			AND `vtiger_inventoryproductrel`.`id` IN ('.generateQuestionMarks($params).')
			
			GROUP BY `vtiger_inventoryproductrel`.`productid`, IFNULL(vtiger_products.unit_price, vtiger_service.unit_price)
			/* skip les quantites a 0 */
			HAVING SUM(`vtiger_inventoryproductrel`.`quantity`) != 0
			
			/* tri pour definir le sequence_no dans la reinsertion */
			ORDER BY vtiger_crmentity.createdtime, `sequence_no`
		';
		$result = $adb->pquery($query, $params);
		if(!$result){
			echo "<pre>$query</pre>";
			var_dump($params);
			$adb->echoError('Erreur dans updateSaleOrderSolde');
			die();
		}
		//insertion de chaque produit
		$fieldNames = array('productid', 'quantity', 'listprice', 'discount_percent', 'discount_amount', 'comment', 'description', 'incrementondel', 'tax1', 'tax2', 'tax3', 'tax4', 'tax5', 'tax6');

		$query = 'INSERT INTO vtiger_inventoryproductrel
			(`id`, sequence_no, '.implode(',', $fieldNames) . ')
		';
		$params = array();
		$sequence_no = 1;
		while($row = $adb->getNextRow($result, false)){
			if(count($params) === 0){
				$query .= ' VALUES(';
			} else {
				$query .= ', (';
			}
			$query .= '?, ?, '.generateQuestionMarks($fieldNames);
			$params[] = $soldeActifId;
			$params[] = $sequence_no++;
			foreach($fieldNames as $fieldName)
				$params[] = $row[$fieldName];
			$query .= ')';
		}
		if($params){
			$result = $adb->pquery($query, $params);
			if(!$result){
				echo "<pre>$query</pre>";
				var_dump($params);
				$adb->echoError('Erreur dans updateSaleOrderSolde');
				die();
			}
		}
	}
	
	/* Recalcul du solde global */
	private function setSaleOrderTotal($salesorderId){
		global $adb;
		$query = 'UPDATE vtiger_salesorder
			SET total = ROUND((
				SELECT SUM(quantity * (listprice * ( 1 - IFNULL(discount_percent, 0)/100) - IFNULL(discount_amount, 0))) AS total
				FROM vtiger_inventoryproductrel
				WHERE vtiger_inventoryproductrel.id = ?)
				 * ( 1 - IFNULL(discount_percent, 0)/100) - IFNULL(discount_amount, 0)
				, 2)
			, subtotal = total
			WHERE vtiger_salesorder.salesorderid = ?
		';
		$params = array($salesorderId, $salesorderId);
		$result = $adb->pquery($query, $params);
		if(!$result){
			echo "<pre>$query</pre>";
			var_dump($params);
			$adb->echoError('Erreur dans setSaleOrderTotal');
			die();
		}
	}
	
	/**
	 * Retourne les infos des derniers dossiers par ordre décroissant de date de création,
	 * et pas au-delà du dernier dossier de Solde clôt.
	 * On peut avoir
	 * 	1) Solde actif
	 * 	2) Variation
	 * 	3) Solde précédent clôt
	 * ou
	 * 	1) Variation
	 * 	2) Solde précédent actif
	 */
	private function getContactDepotsVentesInfos($contactId){
		$query = 'SELECT crmid, createdtime, typedossier, sostatus, contactid
			FROM vtiger_salesorder
			JOIN vtiger_crmentity
				ON vtiger_salesorder.salesorderid = vtiger_crmentity.crmid
			WHERE vtiger_crmentity.deleted = 0
			AND vtiger_salesorder.contactid = ?';
		$query .= ' ORDER BY createdtime DESC';
		
		global $adb;
		$result = $adb->pquery($query, array($contactId));
		if(!$result){
			$adb->echoError('Erreur dans getContactDepotsVentes');
			return;
		}
		$dossiers = array();
		$soldeInactif = false;
		while ($row = $adb->getNextRow($result, false)){
			$row['createdtime'] = new DateTime($row['createdtime']);
			if($row['typedossier'] === 'Solde'){
				if($row['sostatus'] === 'Cancelled'){
					if(count($dossiers) === 0){
						//le dossier est annulé mais c'est le premier (peut être le seul)
						$soldeInactif = $row;
						continue;
					}
					$dossiers[] = $row;
					break;
				}
			}
			elseif($row['sostatus'] === 'Cancelled')
				//skip dossier Annulé qui n'est pas de type Solde
				continue;
			$dossiers[] = $row;
		}
		if(count($dossiers) === 0 && $soldeInactif){
			//unique dossier mais annulé
			$dossiers[] = $soldeInactif;
		}
		return $dossiers;
	}
	
	/**
	 * Retourne l'identifiant du solde actif
	 * Crée le dossier si il n'est pas plus récent qu'un dossier de variation
	 * @param $dossiers : infos de salesorder triés par date de création décroissante
	 * @return : salesorderid
	 */
	private function getSoldeActifId($dossiers){
		//dossiers
		$soldeActif = false;
		$variation = false;
		$soldeInactif = false;
		//recherche des dossiers par type et statut
		foreach($dossiers as $dossier){
			if($dossier['typedossier'] === 'Solde'){
				if($dossier['sostatus'] === 'Cancelled'){
					$soldeInactif = $dossier;
				}
				else
					$soldeActif = $dossier;
			}
			elseif(!$variation){
				//1er dossier de variation
				$variation = $dossier;
			}
		}
		//analyse du dossier de solde actif
		if(!$variation && $soldeActif){
			return $soldeActif['crmid'];
		}
		elseif(!$variation && $soldeInactif){
			return $soldeInactif['crmid'];
		}
		elseif($variation && $soldeActif){
			if($soldeActif['createdtime'] >= $variation['createdtime'] ){
				//Solde déjà constitué
				return $soldeActif['crmid'];
			}
			//Le dossier de Solde est plus vieux que le dossier de variation
			//on ferme le dossier de solde
			//on duplique le dossier de variation (pour l'adresse qui est plus à jour) pour en créer le nouveau solde
			$this->setSalesOrderStatus($soldeActif['crmid'], 'Cancelled');
			$soldeRecordModel = $this->duplicateSalesOrder($variation['crmid'], 'Approved', 'Solde');
			return $soldeRecordModel->getId();
		}
		elseif($variation && $soldeInactif){
			//Le dossier de Solde n'est pas actif
			//on duplique le dossier le plus récent (pour l'adresse qui est plus à jour) pour en créer le nouveau solde
			if($soldeInactif['createdtime'] >= $variation['createdtime'] ){
				$soldeRecordModel = $this->duplicateSalesOrder($soldeInactif['crmid'], 'Approved', 'Solde');
			} else {
				$soldeRecordModel = $this->duplicateSalesOrder($variation['crmid'], 'Approved', 'Solde');
			}
			return $soldeRecordModel->getId();
		}
		elseif($variation){
			//Le dossier de Solde n'existe pas
			//on duplique le dossier de variation pour en créer le nouveau solde
			$soldeRecordModel = $this->duplicateSalesOrder($variation['crmid'], 'Approved', 'Solde');
			return $soldeRecordModel->getId();
		}
		else {
			die("SalesOrder::getSoldeActifId : qu'est ce qu'on fait là ?");
		}
	}
	
	/**
	 * Retourne l'identifiant du solde précédent (de référence pour les quantités de produits)
	 * @param $dossiers : infos de salesorder triés par date de création décroissante
	 * @return : salesorderid
	 */
	private function getSoldeReferenceId($dossiers){
		//dossiers
		$soldeActif = false;
		$variation = false;
		$soldeInactif = false;
		//recherche des dossiers par type et statut
		foreach($dossiers as $dossier){
			if($dossier['typedossier'] === 'Solde'){
				if($dossier['sostatus'] === 'Cancelled'){
					$soldeInactif = $dossier;
				}
				else
					$soldeActif = $dossier;
			}
			elseif(!$variation){
				//1er dossier de variation
				$variation = $dossier;
			}
		}
		//analyse du dossier de solde actif
		if($soldeInactif){
			return $soldeInactif['crmid'];
		}
		elseif(!$variation && $soldeActif){
			return $soldeActif['crmid'];
		}
		elseif($variation && $soldeActif){
			if($soldeActif['createdtime'] < $variation['createdtime'] ){
				//Solde ancien à fermer
				return $soldeActif['crmid'];
			}
			//Le dossier de Solde est plus récent que le dossier de variation et il n'y a pas de solde inactif !!!
			return $soldeActif['crmid'];
		}
		elseif($variation){
			//Le dossier de Solde n'existe pas
			return false;
		}
		else {
			die("SalesOrder::getSoldeReferenceId : qu'est ce qu'on fait là ?");
		}
	}
	
	/**
	 * Retourne les identifiants des variations entre la référence et le solde
	 * @param $dossiers : infos de salesorder triés par date de création décroissante
	 * @return : salesorderid[]
	 */
	private function getVariationIds($dossiers){
		//dossiers
		$variations = array();
		//recherche des dossiers par type et statut
		foreach($dossiers as $dossier){
			if($dossier['typedossier'] !== 'Solde'){
				$variations[] = $dossier['crmid'];
			}
		}
		return $variations;
	}
	
	private function setSalesOrderStatus($salesOrderId, $soStatus){
		global $adb;
		$query = 'UPDATE vtiger_salesorder
			JOIN vtiger_crmentity
				ON vtiger_salesorder.salesorderid = vtiger_crmentity.crmid
			SET vtiger_salesorder.sostatus = ?
			, vtiger_crmentity.modifiedtime = NOW()
			WHERE vtiger_crmentity.crmid = ?';
		$result = $adb->pquery($query, array($soStatus, $salesOrderId));
		if(!$result){
			$adb->echoError('Erreur dans setSalesOrderStatus');
			die();
		}
	}
	
	/**
	 * Duplicate record
	 * @return new record model
	 */
	private function duplicateSalesOrder($salesOrderId, $soStatus, $typeDossier){
		$sourceRecordModel = Vtiger_Record_Model::getInstanceById($salesOrderId, 'SalesOrder');
		$newRecordModel = Vtiger_Record_Model::getCleanInstance('SalesOrder');
		foreach($sourceRecordModel->getModule()->getFields() as $fieldModel){
			$newRecordModel->set($fieldModel->getName(), $sourceRecordModel->get($fieldModel->getName()));
		}
		$newRecordModel->set('sostatus', $soStatus);
		$newRecordModel->set('typedossier', $typeDossier);
		$newRecordModel->save();
		
		//Change la date de création, en ajoutant une minute pour s'assurer d'être en-tête de liste
		global $adb;
		$query = 'UPDATE vtiger_crmentity
			SET createdtime = ?
			WHERE crmid = ?';
		$createdTime = new DateTime();
		$createdTime->modify('+1 minutes');
		$createdTime = $createdTime->format('Y-m-d H:i:00');
		$params = array($createdTime, $newRecordModel->getId());
		$result = $adb->pquery($query, $params);
		if(!$result){
			var_dump($query, $params);
			$adb->echoError('Erreur dans duplicateSalesOrder');
			die();
		}
		return $newRecordModel;
	}
	
	/*******************************
	 * /fin de GESTION DU DOSSIER DE SOLDE
	 *******************************/
	
	
	/* ED150928
	 */
	function trash($module, $id) {
		parent::trash($module, $id);
		$this->refreshQtyInDemand(false, $id);
	}
	

	/** ED150928
	 * Function to restore a deleted record of specified module with given crmid
	 * @param $module -- module name:: Type varchar
	 * @param $entity_ids -- list of crmids :: Array
	 */
	function restore($module, $id) {
		parent::restore($module, $id);
		$this->refreshQtyInDemand(false, $id);
	}
	
	/** Function to get activities associated with the Sales Order
	 *  This function accepts the id as arguments and execute the MySQL query using the id
	 *  and sends the query and the id as arguments to renderRelatedActivities() method
	 */
	function get_activities($id, $cur_tab_id, $rel_tab_id, $actions=false) {
		global $log, $singlepane_view,$currentModule,$current_user;
		$log->debug("Entering get_activities(".$id.") method ...");
		$this_module = $currentModule;

		$related_module = vtlib_getModuleNameById($rel_tab_id);
		require_once("modules/$related_module/Activity.php");
		$other = new Activity();
		vtlib_setup_modulevars($related_module, $other);
		$singular_modname = vtlib_toSingular($related_module);

		$parenttab = getParentTab();

		if($singlepane_view == 'true')
			$returnset = '&return_module='.$this_module.'&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module='.$this_module.'&return_action=CallRelatedList&return_id='.$id;

		$button = '';

		$button .= '<input type="hidden" name="activity_mode">';

		if($actions) {
			if(is_string($actions)) $actions = explode(',', strtoupper($actions));
			if(in_array('ADD', $actions) && isPermitted($related_module,1, '') == 'yes') {
				if(getFieldVisibilityPermission('Calendar',$current_user->id,'parent_id', 'readwrite') == '0') {
					$button .= "<input title='".getTranslatedString('LBL_NEW'). " ". getTranslatedString('LBL_TODO', $related_module) ."' class='crmbutton small create'" .
						" onclick='this.form.action.value=\"EditView\";this.form.module.value=\"$related_module\";this.form.return_module.value=\"$this_module\";this.form.activity_mode.value=\"Task\";' type='submit' name='button'" .
						" value='". getTranslatedString('LBL_ADD_NEW'). " " . getTranslatedString('LBL_TODO', $related_module) ."'>&nbsp;";
				}
			}
		}

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT case when (vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname end as user_name,vtiger_contactdetails.lastname, vtiger_contactdetails.firstname, vtiger_contactdetails.contactid, vtiger_activity.*,vtiger_seactivityrel.crmid as parent_id,vtiger_crmentity.crmid, vtiger_crmentity.smownerid, vtiger_crmentity.modifiedtime from vtiger_activity inner join vtiger_seactivityrel on vtiger_seactivityrel.activityid=vtiger_activity.activityid inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_activity.activityid left join vtiger_cntactivityrel on vtiger_cntactivityrel.activityid= vtiger_activity.activityid left join vtiger_contactdetails on vtiger_contactdetails.contactid = vtiger_cntactivityrel.contactid left join vtiger_users on vtiger_users.id=vtiger_crmentity.smownerid left join vtiger_groups on vtiger_groups.groupid=vtiger_crmentity.smownerid where vtiger_seactivityrel.crmid=".$id." and activitytype='Task' and vtiger_crmentity.deleted=0 and (vtiger_activity.status is not NULL and vtiger_activity.status != 'Completed') and (vtiger_activity.status is not NULL and vtiger_activity.status !='Deferred')";

		$return_value = GetRelatedList($this_module, $related_module, $other, $query, $button, $returnset);

		if($return_value == null) $return_value = Array();
		$return_value['CUSTOM_BUTTON'] = $button;

		$log->debug("Exiting get_activities method ...");
		return $return_value;
	}

	/** Function to get the activities history associated with the Sales Order
	 *  This function accepts the id as arguments and execute the MySQL query using the id
	 *  and sends the query and the id as arguments to renderRelatedHistory() method
	 */
	function get_history($id)
	{
		global $log;
		$log->debug("Entering get_history(".$id.") method ...");
		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "SELECT vtiger_contactdetails.lastname, vtiger_contactdetails.firstname,
			vtiger_contactdetails.contactid,vtiger_activity.*, vtiger_seactivityrel.*,
			vtiger_crmentity.crmid, vtiger_crmentity.smownerid, vtiger_crmentity.modifiedtime,
			vtiger_crmentity.createdtime, vtiger_crmentity.description, case when
			(vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname
			end as user_name from vtiger_activity
				inner join vtiger_seactivityrel on vtiger_seactivityrel.activityid=vtiger_activity.activityid
				inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_activity.activityid
				left join vtiger_cntactivityrel on vtiger_cntactivityrel.activityid= vtiger_activity.activityid
				left join vtiger_contactdetails on vtiger_contactdetails.contactid = vtiger_cntactivityrel.contactid
                                left join vtiger_groups on vtiger_groups.groupid=vtiger_crmentity.smownerid
				left join vtiger_users on vtiger_users.id=vtiger_crmentity.smownerid
			where activitytype='Task'
				and (vtiger_activity.status = 'Completed' or vtiger_activity.status = 'Deferred')
				and vtiger_seactivityrel.crmid=".$id."
                                and vtiger_crmentity.deleted = 0";
		//Don't add order by, because, for security, one more condition will be added with this query in include/RelatedListView.php

		$log->debug("Exiting get_history method ...");
		return getHistory('SalesOrder',$query,$id);
	}



	/** Function to get the invoices associated with the Sales Order
	 *  This function accepts the id as arguments and execute the MySQL query using the id
	 *  and sends the query and the id as arguments to renderRelatedInvoices() method.
	 */
	function get_invoices($id)
	{
		global $log,$singlepane_view;
		$log->debug("Entering get_invoices(".$id.") method ...");
		require_once('modules/Invoice/Invoice.php');

		$focus = new Invoice();

		$button = '';
		if($singlepane_view == 'true')
			$returnset = '&return_module=SalesOrder&return_action=DetailView&return_id='.$id;
		else
			$returnset = '&return_module=SalesOrder&return_action=CallRelatedList&return_id='.$id;

		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>
							'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');
		$query = "select vtiger_crmentity.*, vtiger_invoice.*, vtiger_account.accountname,
			vtiger_salesorder.subject as salessubject, case when
			(vtiger_users.user_name not like '') then $userNameSql else vtiger_groups.groupname
			end as user_name from vtiger_invoice
			inner join vtiger_crmentity on vtiger_crmentity.crmid=vtiger_invoice.invoiceid
			inner join vtiger_invoicecf on vtiger_invoicecf.invoiceid=vtiger_invoice.invoiceid
			left outer join vtiger_account on vtiger_account.accountid=vtiger_invoice.accountid
			inner join vtiger_salesorder on vtiger_salesorder.salesorderid=vtiger_invoice.salesorderid
			left join vtiger_users on vtiger_users.id=vtiger_crmentity.smownerid
			left join vtiger_groups on vtiger_groups.groupid=vtiger_crmentity.smownerid
			where vtiger_crmentity.deleted=0 and vtiger_salesorder.salesorderid=".$id;

		$log->debug("Exiting get_invoices method ...");
		return GetRelatedList('SalesOrder','Invoice',$focus,$query,$button,$returnset);

	}

	/**	Function used to get the Status history of the Sales Order
	 *	@param $id - salesorder id
	 *	@return $return_data - array with header and the entries in format Array('header'=>$header,'entries'=>$entries_list) where as $header and $entries_list are arrays which contains header values and all column values of all entries
	 */
	function get_sostatushistory($id)
	{
		global $log;
		$log->debug("Entering get_sostatushistory(".$id.") method ...");

		global $adb;
		global $mod_strings;
		global $app_strings;

		$query = 'select vtiger_sostatushistory.*, vtiger_salesorder.salesorder_no from vtiger_sostatushistory inner join vtiger_salesorder on vtiger_salesorder.salesorderid = vtiger_sostatushistory.salesorderid inner join vtiger_crmentity on vtiger_crmentity.crmid = vtiger_salesorder.salesorderid where vtiger_crmentity.deleted = 0 and vtiger_salesorder.salesorderid = ?';
		$result=$adb->pquery($query, array($id));
		$noofrows = $adb->num_rows($result);

		$header[] = $app_strings['Order No'];
		$header[] = $app_strings['LBL_ACCOUNT_NAME'];
		$header[] = $app_strings['LBL_AMOUNT'];
		$header[] = $app_strings['LBL_SO_STATUS'];
		$header[] = $app_strings['LBL_LAST_MODIFIED'];

		//Getting the field permission for the current user. 1 - Not Accessible, 0 - Accessible
		//Account Name , Total are mandatory fields. So no need to do security check to these fields.
		global $current_user;

		//If field is accessible then getFieldVisibilityPermission function will return 0 else return 1
		$sostatus_access = (getFieldVisibilityPermission('SalesOrder', $current_user->id, 'sostatus') != '0')? 1 : 0;
		$picklistarray = getAccessPickListValues('SalesOrder');

		$sostatus_array = ($sostatus_access != 1)? $picklistarray['sostatus']: array();
		//- ==> picklist field is not permitted in profile
		//Not Accessible - picklist is permitted in profile but picklist value is not permitted
		$error_msg = ($sostatus_access != 1)? 'Not Accessible': '-';

		while($row = $adb->fetch_array($result))
		{
			$entries = Array();

			// Module Sequence Numbering
			//$entries[] = $row['salesorderid'];
			$entries[] = $row['salesorder_no'];
			// END
			$entries[] = $row['accountname'];
			$entries[] = $row['total'];
			$entries[] = (in_array($row['sostatus'], $sostatus_array))? $row['sostatus']: $error_msg;
			$date = new DateTimeField($row['lastmodified']);
			$entries[] = $date->getDisplayDateTimeValue();

			$entries_list[] = $entries;
		}

		$return_data = Array('header'=>$header,'entries'=>$entries_list);

	 	$log->debug("Exiting get_sostatushistory method ...");

		return $return_data;
	}

	/*
	 * Function to get the secondary query part of a report
	 * @param - $module primary module name
	 * @param - $secmodule secondary module name
	 * returns the query string formed on fetching the related data for report for secondary module
	 */
	function generateReportsSecQuery($module,$secmodule,$queryPlanner){
		$matrix = $queryPlanner->newDependencyMatrix();
		$matrix->setDependency('vtiger_crmentitySalesOrder', array('vtiger_usersSalesOrder', 'vtiger_groupsSalesOrder', 'vtiger_lastModifiedBySalesOrder'));		
		$matrix->setDependency('vtiger_inventoryproductrelSalesOrder', array('vtiger_productsSalesOrder', 'vtiger_serviceSalesOrder'));
		$matrix->setDependency('vtiger_salesorder',array('vtiger_crmentitySalesOrder', "vtiger_currency_info$secmodule",
				'vtiger_salesordercf', 'vtiger_potentialRelSalesOrder', 'vtiger_sobillads','vtiger_soshipads', 
				'vtiger_inventoryproductrelSalesOrder', 'vtiger_contactdetailsSalesOrder', 'vtiger_accountSalesOrder',
				'vtiger_invoice_recurring_info','vtiger_quotesSalesOrder'));
		
		if (!$queryPlanner->requireTable('vtiger_salesorder', $matrix)) {
			return '';
		}
		
		$query = $this->getRelationQuery($module,$secmodule,"vtiger_salesorder","salesorderid", $queryPlanner);
		if ($queryPlanner->requireTable("vtiger_crmentitySalesOrder",$matrix)){
			$query .= " left join vtiger_crmentity as vtiger_crmentitySalesOrder on vtiger_crmentitySalesOrder.crmid=vtiger_salesorder.salesorderid and vtiger_crmentitySalesOrder.deleted=0";
		}
		if ($queryPlanner->requireTable("vtiger_salesordercf")){
			$query .= " left join vtiger_salesordercf on vtiger_salesorder.salesorderid = vtiger_salesordercf.salesorderid";
		}
		if ($queryPlanner->requireTable("vtiger_sobillads")){
			$query .= " left join vtiger_sobillads on vtiger_salesorder.salesorderid=vtiger_sobillads.sobilladdressid";
		}
		if ($queryPlanner->requireTable("vtiger_soshipads")){
			$query .= " left join vtiger_soshipads on vtiger_salesorder.salesorderid=vtiger_soshipads.soshipaddressid";
		}
		if ($queryPlanner->requireTable("vtiger_currency_info$secmodule")){
			$query .= " left join vtiger_currency_info as vtiger_currency_info$secmodule on vtiger_currency_info$secmodule.id = vtiger_salesorder.currency_id";
		}
		if ($queryPlanner->requireTable("vtiger_inventoryproductrelSalesOrder", $matrix)){
			$query .= " left join vtiger_inventoryproductrel as vtiger_inventoryproductrelSalesOrder on vtiger_salesorder.salesorderid = vtiger_inventoryproductrelSalesOrder.id";
		}
		if ($queryPlanner->requireTable("vtiger_productsSalesOrder")){
			$query .= " left join vtiger_products as vtiger_productsSalesOrder on vtiger_productsSalesOrder.productid = vtiger_inventoryproductrelSalesOrder.productid";
		}
		if ($queryPlanner->requireTable("vtiger_serviceSalesOrder")){
			$query .= " left join vtiger_service as vtiger_serviceSalesOrder on vtiger_serviceSalesOrder.serviceid = vtiger_inventoryproductrelSalesOrder.productid";
		}
		if ($queryPlanner->requireTable("vtiger_groupsSalesOrder")){
			$query .= " left join vtiger_groups as vtiger_groupsSalesOrder on vtiger_groupsSalesOrder.groupid = vtiger_crmentitySalesOrder.smownerid";
		}
		if ($queryPlanner->requireTable("vtiger_usersSalesOrder")){
			$query .= " left join vtiger_users as vtiger_usersSalesOrder on vtiger_usersSalesOrder.id = vtiger_crmentitySalesOrder.smownerid";
		}
		if ($queryPlanner->requireTable("vtiger_potentialRelSalesOrder")){
			$query .= " left join vtiger_potential as vtiger_potentialRelSalesOrder on vtiger_potentialRelSalesOrder.potentialid = vtiger_salesorder.potentialid";
		}
		if ($queryPlanner->requireTable("vtiger_contactdetailsSalesOrder")){
			$query .= " left join vtiger_contactdetails as vtiger_contactdetailsSalesOrder on vtiger_salesorder.contactid = vtiger_contactdetailsSalesOrder.contactid";
		}
		if ($queryPlanner->requireTable("vtiger_invoice_recurring_info")){
			$query .= " left join vtiger_invoice_recurring_info on vtiger_salesorder.salesorderid = vtiger_invoice_recurring_info.salesorderid";
		}
		if ($queryPlanner->requireTable("vtiger_quotesSalesOrder")){
			$query .= " left join vtiger_quotes as vtiger_quotesSalesOrder on vtiger_salesorder.quoteid = vtiger_quotesSalesOrder.quoteid";
		}
		if ($queryPlanner->requireTable("vtiger_accountSalesOrder")){
			$query .= " left join vtiger_account as vtiger_accountSalesOrder on vtiger_accountSalesOrder.accountid = vtiger_salesorder.accountid";
		}
		if ($queryPlanner->requireTable("vtiger_lastModifiedBySalesOrder")){
			$query .= " left join vtiger_users as vtiger_lastModifiedBySalesOrder on vtiger_lastModifiedBySalesOrder.id = vtiger_crmentitySalesOrder.modifiedby ";
		}
		return $query;
	}

	/*
	 * Function to get the relation tables for related modules
	 * @param - $secmodule secondary module name
	 * returns the array with table names and fieldnames storing relations between module and this module
	 */
	function setRelationTables($secmodule){
		$rel_tables = array (
			"Calendar" =>array("vtiger_seactivityrel"=>array("crmid","activityid"),"vtiger_salesorder"=>"salesorderid"),
			"Invoice" =>array("vtiger_invoice"=>array("salesorderid","invoiceid"),"vtiger_salesorder"=>"salesorderid"),
			"Documents" => array("vtiger_senotesrel"=>array("crmid","notesid"),"vtiger_salesorder"=>"salesorderid"),
		);
		return $rel_tables[$secmodule];
	}

	// Function to unlink an entity with given Id from another entity
	function unlinkRelationship($id, $return_module, $return_id) {
		global $log;
		if(empty($return_module) || empty($return_id)) return;

		if($return_module == 'Accounts') {
			$this->trash('SalesOrder',$id);
		}
		elseif($return_module == 'Quotes') {
			$relation_query = 'UPDATE vtiger_salesorder SET quoteid=? WHERE salesorderid=?';
			$this->db->pquery($relation_query, array(null, $id));
		}
		elseif($return_module == 'Potentials') {
			$relation_query = 'UPDATE vtiger_salesorder SET potentialid=? WHERE salesorderid=?';
			$this->db->pquery($relation_query, array(null, $id));
		}
		elseif($return_module == 'Contacts') {
			$relation_query = 'UPDATE vtiger_salesorder SET contactid=? WHERE salesorderid=?';
			$this->db->pquery($relation_query, array(null, $id));
		} else {
			$sql = 'DELETE FROM vtiger_crmentityrel WHERE (crmid=? AND relmodule=? AND relcrmid=?) OR (relcrmid=? AND module=? AND crmid=?)';
			$params = array($id, $return_module, $return_id, $id, $return_module, $return_id);
			$this->db->pquery($sql, $params);
		}
	}

	public function getJoinClause($tableName) {
		if ($tableName == 'vtiger_invoice_recurring_info') {
			return 'LEFT JOIN';
		}
		return parent::getJoinClause($tableName);
	}

	function insertIntoEntityTable($table_name, $module, $fileid = '')  {
		//Ignore relation table insertions while saving of the record
		if($table_name == 'vtiger_inventoryproductrel') {
			return;
		}
		parent::insertIntoEntityTable($table_name, $module, $fileid);
	}

	/*Function to create records in current module.
	**This function called while importing records to this module*/
	function createRecords($obj) {
		$createRecords = createRecords($obj);
		return $createRecords;
	}

	/*Function returns the record information which means whether the record is imported or not
	**This function called while importing records to this module*/
	function importRecord($obj, $inventoryFieldData, $lineItemDetails) {
		$entityInfo = importRecord($obj, $inventoryFieldData, $lineItemDetails);
		return $entityInfo;
	}

	/*Function to return the status count of imported records in current module.
	**This function called while importing records to this module*/
	function getImportStatusCount($obj) {
		$statusCount = getImportStatusCount($obj);
		return $statusCount;
	}

	function undoLastImport($obj, $user) {
		$undoLastImport = undoLastImport($obj, $user);
	}

	/** Function to export the lead records in CSV Format
	* @param reference variable - where condition is passed when the query is executed
	* Returns Export SalesOrder Query.
	*/
	function create_export_query($where){
		global $log;
		global $current_user;
		$log->debug("Entering create_export_query(".$where.") method ...");

		include("include/utils/ExportUtils.php");

		//To get the Permitted fields query and the permitted fields list
		$sql = getPermittedFieldsQuery("SalesOrder", "detail_view");
		$fields_list = getFieldsListFromQuery($sql);
		$fields_list .= getInventoryFieldsForExport($this->table_name);
		$userNameSql = getSqlForNameInDisplayFormat(array('first_name'=>'vtiger_users.first_name', 'last_name' => 'vtiger_users.last_name'), 'Users');

		$query = "SELECT $fields_list FROM ".$this->entity_table."
				INNER JOIN vtiger_salesorder ON vtiger_salesorder.salesorderid = vtiger_crmentity.crmid
				LEFT JOIN vtiger_salesordercf ON vtiger_salesordercf.salesorderid = vtiger_salesorder.salesorderid
				LEFT JOIN vtiger_sobillads ON vtiger_sobillads.sobilladdressid = vtiger_salesorder.salesorderid
				LEFT JOIN vtiger_soshipads ON vtiger_soshipads.soshipaddressid = vtiger_salesorder.salesorderid
				LEFT JOIN vtiger_inventoryproductrel ON vtiger_inventoryproductrel.id = vtiger_salesorder.salesorderid
				LEFT JOIN vtiger_products ON vtiger_products.productid = vtiger_inventoryproductrel.productid
				LEFT JOIN vtiger_service ON vtiger_service.serviceid = vtiger_inventoryproductrel.productid
				LEFT JOIN vtiger_contactdetails ON vtiger_contactdetails.contactid = vtiger_salesorder.contactid
				LEFT JOIN vtiger_invoice_recurring_info ON vtiger_invoice_recurring_info.salesorderid = vtiger_salesorder.salesorderid
				LEFT JOIN vtiger_potential ON vtiger_potential.potentialid = vtiger_salesorder.potentialid
				LEFT JOIN vtiger_account ON vtiger_account.accountid = vtiger_salesorder.accountid
				LEFT JOIN vtiger_currency_info ON vtiger_currency_info.id = vtiger_salesorder.currency_id
				LEFT JOIN vtiger_quotes ON vtiger_quotes.quoteid = vtiger_salesorder.quoteid
				LEFT JOIN vtiger_groups ON vtiger_groups.groupid = vtiger_crmentity.smownerid
				LEFT JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid";

		$query .= $this->getNonAdminAccessControlQuery('SalesOrder',$current_user);
		$where_auto = " vtiger_crmentity.deleted=0";

		if($where != "") {
			$query .= " where ($where) AND ".$where_auto;
		} else {
			$query .= " where ".$where_auto;
		}

		$log->debug("Exiting create_export_query method ...");
		return $query;
	}
}

?>