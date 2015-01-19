<?php
/*+***********************************************************************************
 * ED141022
 *
 * Attention héritage de Save_Action et non SaveAjax_Action
 *************************************************************************************/

class RsnAdhesions_SaveAjax_Action extends RsnDons_SaveAjax_Action {

	var $serviceType = 'Adhésion';
	var $invoicedate_field = 'dateadhesion';
	
}
