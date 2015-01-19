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
 * RSNMediaRelations ListView Model Class
 */
class RSNMediaRelations_ListView_Model extends Vtiger_ListView_Model {


	function getQuery() {
		$queryGenerator = $this->get('query_generator');
		$listQuery = $queryGenerator->getQuery();
		$listQuery = str_replace('vtiger_rsnmediarelations.daterelation'
					 , "vtiger_rsnmediarelations.daterelation"
					 , $listQuery);
		return $listQuery;
	}

}
