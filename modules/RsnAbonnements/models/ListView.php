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
 * RsnAbonnements ListView Model Class
 *
 * La table vtiger_rsnabonnements n'est pas utilisée.
 * Les dons sont des lignes de facture (table vtiger_inventoryproductrel)
 */
class RsnAbonnements_ListView_Model extends Vtiger_ListView_Model {

	function getQuery() {
		
		$listQuery = 'SELECT f.invoicedate AS dateabonnement, f.accountid as compte, lg.`listprice` as montant
		, p.productcode as origine, f.invoiceid as rsnabonnementsid
		FROM `vtiger_inventoryproductrel` lg
		INNER JOIN `vtiger_service` p
			ON lg.productid = p.serviceid
			AND p.servicecategory = \'Abonnement\'
		INNER JOIN `vtiger_invoice` f
			ON lg.id = f.invoiceid
		INNER JOIN `vtiger_crmentity` e
			ON e.crmid = f.invoiceid
		';
		//var_dump($listQuery);
		return $listQuery;
	}
}
