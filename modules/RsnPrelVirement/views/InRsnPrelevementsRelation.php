<?php
/*+***********************************************************************************
 * ED150826
 *************************************************************************************/

class RsnPrelVirement_InRsnPrelevementsRelation_View extends Vtiger_RelatedList_View {
	function process(Vtiger_Request $request) {
		$relatedModuleName= $request->getModule();
		$moduleName = $request->get('relatedModule');
		
		$orderBy = $request->get('orderby');
		if(!$orderBy){
			$request->set('orderby', 'dateexport');
			$request->set('sortorder','DESC');
		}
		return parent::process($request);
	}
}
