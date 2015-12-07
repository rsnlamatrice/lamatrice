<?php
/*+***********************************************************************************
 * 
 *************************************************************************************/

class RsnPrelevements_DetailView_Model extends Vtiger_DetailView_Model {
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
				'linklabel' => vtranslate('LBL_EXPORT_TO_PDF', $moduleName),
				'linkurl' => $recordModel->getExportPDFUrl(),
				'linkicon' => ''
			);
			$linkModelList['DETAILVIEW'][] = Vtiger_Link_Model::getInstanceFromValues($detailViewLinks);
		}
		
		foreach($linkModelList['DETAILVIEW'] as &$linkModel){
			if($linkModel->linklabel === 'LBL_DUPLICATE' ){
				$linkModel->linklabel = 'Arr&eacute;ter et dupliquer';
				$linkModel->linkurl .= '&closeOriginal=1';
			}
		}
		return $linkModelList;
	}
}
