<?php
/*+***********************************************************************************
 * ED141022
 *
 * Attention hŽritage de Save_Action et non SaveAjax_Action
 *************************************************************************************/

class RSNMediaRelations_SaveAjax_Action extends Vtiger_SaveAjax_Action {

	public function process(Vtiger_Request $request) {
		//die("EN COURS");
		if ($_REQUEST['action'] == 'SaveAjax') {
			

		}

		return parent::process($request);
	}
}
