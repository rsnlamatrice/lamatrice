<?php
/*+***********************************************************************************
 * Génération des ordres de virement
 *************************************************************************************/

class RsnPrelevements_Generate_Action extends Vtiger_Action_Controller {
	
	public function checkPermission(){
		return true;
	}
	
	public function process(Vtiger_Request $request) {
		             
		global $php_max_memory_limit;
		ini_set("memory_limit", empty($php_max_memory_limit) ? "8G" : $php_max_memory_limit);

		$moduleName = $request->get('module');
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		
		$dateVir = $moduleModel->getNextDateToGenerateVirnts($request->get('date_virements'));
		
		$prelevements = $moduleModel->getPrelevementsToGenerateVirnts( $dateVir );
		foreach($prelevements as $prelvnt){
			$prelVirnt = $prelvnt->createPrelVirement($dateVir);
			if(!$prelvnt->get('dejapreleve')){
				$prelvnt->set('mode', 'edit');
				$prelvnt->set('dejapreleve', date('d-m-Y'));
				$prelvnt->set('montant', str_replace('.', ',', $prelvnt->get('montant')));//bug de prise en compte de la virgule comme séparateur de millier
				$prelvnt->save();
			}
			//break;//debug
		}
		$loadUrl = $moduleModel->getGenererPrelVirementsUrl();
		header("Location: $loadUrl");
	}
}
