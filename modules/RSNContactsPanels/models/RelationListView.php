<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class RSNContactsPanels_RelationListView_Model extends Vtiger_RelationListView_Model {

	public function getHeaders() {
		$headers = parent::getHeaders();
		//retire la colonne Panel puisqu'on est en relatedlist
		unset($headers['rsnpanelid']);
		return $headers;
	}

	public function getLinks(){
		$relatedLink = parent::getLinks();
		
		 /*ED141016*/
		if($relatedLink == null) return null;

		//$resetLinks = $this->getResetRelationLink();
		//$relatedLink['LISTVIEWBASIC'] = array_merge($relatedLink['LISTVIEWBASIC'], $resetLinks);
		return $relatedLink;
	}

	public function getDeleteRelationLinks() {
		$relationModel = $this->getRelationModel();
		$deleteLinkModel = array();

		switch($relationModel->getRelationModuleModel()->getName()){
			case "RSNPanelsVariables" :
				
				$deleteLinkList = parent::getDeleteRelationLinks();
				foreach($deleteLinkList as $deleteLink) {
					$deleteLink->set('linklabel', vtranslate('LBL_RESET_ALL_VARIABLES', $relationModel->getParentModuleModel()->getName()));
					$deleteLink->set('linkicon', 'icon-refresh');
				}
				return $deleteLinkList;
			default:
				return parent::getDeleteRelationLinks();
		}

		foreach($deleteLinkList as $deleteLink) {
			$deleteLinkModel[] = Vtiger_Link_Model::getInstanceFromValues($deleteLink);
		}
		return $deleteLinkModel;
	}
}