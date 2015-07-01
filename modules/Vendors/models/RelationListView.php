<?php
/*+***********************************************************************************
 * ED150701
 *************************************************************************************/

class Vendors_RelationListView_Model extends Vtiger_RelationListView_Model {

	//ED150701 : override
	public function getAddRelationLinks() {
		$relationModel = $this->getRelationModel();
		$relatedModel = $relationModel->getRelationModuleModel();
		if($relatedModel->getName() === 'PurchaseOrder')
			return $this->getPurchaseOrderAddRelationLinks($relationModel, $relatedModel);
		return parent::getAddRelationLinks();
	}

	//ED150701 : définit autant de boutons Ajouter que de type de documents fournisseur
	private function getPurchaseOrderAddRelationLinks($relationModel, $relatedModel) {
		
		$addLinkModel = array();

		if(!$relationModel->isAddActionSupported()) {
			return $addLinkModel;
		}
		$relatedModel = $relationModel->getRelationModuleModel();
		
		$addLinkList = array();
		foreach(array('order', 'receipt', 'invoice') as $potype)
			$addLinkList[] = array(
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => vtranslate('LBL_ADD')." ".vtranslate('LBL_POTYPE_' . $potype, 'PurchaseOrder'),
				'linkurl' => $this->getCreateViewUrl() . '&potype=' . $potype,
				'linkicon' => '',
			);
	
		foreach($addLinkList as $addLink) {
			$addLinkModel[] = Vtiger_Link_Model::getInstanceFromValues($addLink);
		}
		return $addLinkModel;
	}

}