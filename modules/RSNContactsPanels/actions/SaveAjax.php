<?php
/*+***********************************************************************************
 * ED141022
 *
 * Attention hŽritage de Save_Action et non SaveAjax_Action
 *************************************************************************************/

class RSNContactsPanels_SaveAjax_Action extends Vtiger_SaveAjax_Action {

	
	public function process(Vtiger_Request $request) {
		return parent::process($request);
	}
}
