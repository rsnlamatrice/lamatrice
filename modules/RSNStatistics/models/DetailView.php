<?php
/*+***********************************************************************************
 * 
 *************************************************************************************/

class RSNStatistics_DetailView_Model extends Vtiger_DetailView_Model {
    	/**
	 * Function to get the detail view links (links and widgets)
	 * @param <array> $linkParams - parameters which will be used to calculate the params
	 * @param boolean $countRelatedEntity - AV150619
	 * @return <array> - array of link models in the format as below
	 *                   array('linktype'=>list of link models);
	 */
	public function getDetailViewLinks($linkParams, $countRelatedEntity = false) {
		$linkModelList = parent::getDetailViewLinks($linkParams, $countRelatedEntity);
		$recordModel = $this->getRecord();
		$moduleName = $recordModel->getmoduleName();

		if(Users_Privileges_Model::isPermitted($moduleName, 'DetailView', $recordModel->getId())) {
			$detailViewLinks = array(
				'linklabel' => vtranslate('LBL_CALCULATE_FOR_ALL_RELATED', $moduleName),
				'linkurl' => $recordModel->getUpdateValuesUrl(),
				'linkicon' => ''
			);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($detailViewLinks);
			
			$detailViewLinks = array(
				'linklabel' => vtranslate('LBL_CALCULATE_FOR_ALL_RELATED_THIS_YEAR', $moduleName),
				'linkurl' => $recordModel->getUpdateValuesUrl() . "&begin_date=year",
				'linkicon' => ''
			);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($detailViewLinks);
		}
		return $linkModelList;
	}
}
