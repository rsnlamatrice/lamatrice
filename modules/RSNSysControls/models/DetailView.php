<?php
/*+***********************************************************************************
 * ED150817
 *************************************************************************************/

class RSNSysControls_DetailView_Model extends Vtiger_DetailView_Model {

	/**
	 * Function to get the detail view related links
	 * @return <array> - list of links parameters
	 */
	public function getDetailViewRelatedLinks($countRelatedEntity = false) {
		$relatedLinks = parent::getDetailViewRelatedLinks($countRelatedEntity);
		$recordModel = $this->getRecord();
		$moduleName = $recordModel->getModuleName();
		
		//link which shows the summary information(generally detail of record)
		$relatedLinks[] = array(
				'linktype' => 'DETAILVIEWTAB',
				'linklabel' => 'Résultats de la requête',
				'linkurl' => $this->getSysControlResultViewUrl($recordModel),
				'linkicon' => ''
		);

		return $relatedLinks;
	}
	
	function getSysControlResultViewUrl($parentRecordModel){
		return 'module='.$parentRecordModel->getModuleName().'&relatedModule='.$parentRecordModel->get('relatedmodule').
				'&view=Detail&record='.$parentRecordModel->getId().'&mode=showSysControlsResult';
	}
}
