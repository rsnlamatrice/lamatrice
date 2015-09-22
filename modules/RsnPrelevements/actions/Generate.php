<?php
/*+***********************************************************************************
 * Génération des ordres de virement
 *************************************************************************************/

class RsnPrelevements_Generate_Action extends Vtiger_Action_Controller {
	
	public function checkPermission(){
		return true;
	}
	
	public function process(Vtiger_Request $request) {
		
		$moduleName = $request->get('module');
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		
		$dateVir = $moduleModel->getNextDateToGenerateVirnts($request->get('date_virements'));
		
		$prelevements = $moduleModel->getPrelevementsToGenerateVirnts( $dateVir );
		foreach($prelevements as $prelvnt){
			$prelVirnt = $prelvnt->createPrelVirement($dateVir);
			if(!$prelvnt->get('dejapreleve')){
				$prelvnt->set('mode', 'edit');
				$prelvnt->set('dejapreleve', date('d-m-Y'));
				$prelvnt->set('montant', decimalFormat($prelvnt->get('montant')));//bug de prise en compte de la virgule comme séparateur de millier
				$prelvnt->save();
			}
			//break;//debug
		}
		$loadUrl = $moduleModel->getGenererPrelVirementsUrl();
		header("Location: $loadUrl");
	}
}
