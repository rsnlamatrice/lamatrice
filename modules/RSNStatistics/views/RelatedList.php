<?php
/*+***********************************************************************************
 * 
 *************************************************************************************/

class RSNStatistics_RelatedList_View extends Vtiger_RelatedList_View {
	function process(Vtiger_Request $request) {
		$relatedModuleName = $request->get('relatedModule');
		$orderBy = $request->get('orderby');
		if(empty($orderBy)) {
			if($relatedModuleName === 'RSNStatisticsFields'){
				$request->set('orderby', 'sequence');
			}
		}
		return parent::process($request);
	}
}
