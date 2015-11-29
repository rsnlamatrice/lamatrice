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
 * Vtiger ListView Model Class
 */
class Contacts_DuplicatesListView_Model extends Vtiger_DuplicatesListView_Model {
	

	//TODO en théorie, le nom de la table vtiger_duplicateentities dépend du module
	function getQuery($startIndex = 0, $pageLimit = 30) {
		$queryGenerator = $this->get('query_generator');
		$queryGenerator->addField('mailingstreet2');
		
		return parent::getQuery($startIndex, $pageLimit);
	}
	
	public function getListViewEntries($pagingModel) {
		$listViewEntries = parent::getListViewEntries($pagingModel);
		foreach($listViewEntries as $entryKey => $listViewEntry){
			if($listViewEntry->rawData['isgroup'] > 0
			&& $listViewEntry->get('mailingstreet2')){
				$listViewEntry->set('lastname', $listViewEntry->get('lastname') . ' - ' .  $listViewEntry->get('mailingstreet2'));
				$listViewEntries[$entryKey] = $listViewEntry;
			}
		}
		return $listViewEntries;		
	}
	
}
