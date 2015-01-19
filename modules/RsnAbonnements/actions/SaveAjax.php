<?php
/*+***********************************************************************************
 * ED141022
 *
 * Attention hŽritage de Save_Action et non SaveAjax_Action
 *************************************************************************************/

class RsnAbonnements_SaveAjax_Action extends RsnDons_SaveAjax_Action {

	var $serviceType = 'Abonnement';
	var$invoicedate_field = 'dateabonnement';
	
}
