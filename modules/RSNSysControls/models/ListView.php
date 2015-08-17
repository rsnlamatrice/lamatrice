<?php
/*+***********************************************************************************
 * ED150817
 *************************************************************************************/

/**
 * Vtiger ListView Model Class
 */
class RSNSysControls_ListView_Model extends Vtiger_ListView_Model {

	/*
	 * Function to give advance links of a module
	 *	@RETURN array of advanced links
	 */
	public function getAdvancedLinks(){
		$moduleModel = $this->getModule();
		$advancedLinks = array();
		
		//Ajoute un menu Executer pour tester ce qui est appelé par le cron : Data_Action::runScheduledSysControls
		$advancedLinks[] = array(
			'linktype' => 'LISTVIEW',
			'linklabel' => 'Exécuter',
			'linkurl' => 'index.php?module=RSNSysControls&action=Data&mode=runScheduledSysControls',
			'linkicon' => ''
		);
	
		return $advancedLinks;
	}
}
