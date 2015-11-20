<?php
/*+***********************************************************************************
 * ED141021
 *************************************************************************************/

/**
 * RsnReglements Entity Record Model Class
 */
class RsnPrelVirement_Record_Model extends Vtiger_Record_Model {

	public function getExportRejetPrelvntPDFURL(){
		return "index.php?module=".$this->getModuleName()."&action=PrintRejetPrelvnt&record=".$this->getId();
	}
	
	/**
	 * Function to get the Display Name for the record
	 * @return <String> - Entity Display Name for the record
	 */
	public function getDisplayName() {
		$name = number_format( $this->get('montant'), 2);
		$name .= ' &euro;';
		$name .= ' - ' . $this->get('rsnprelvirstatus');
		$date  = new DateTime($this->get('dateexport'));
		$name .= ' - ' . $date->format('d M Y');//date('d M Y', $this->get('dateregl'));
		return $name;
	}

	var $RsnPrelevement;
	public function getRsnPrelevement(){
		if(!$this->RsnPrelevement || $this->RsnPrelevement->get('rsnprelevementsid') != $this->get('rsnprelevementsid')){
			$this->RsnPrelevement = Vtiger_Record_Model::getInstanceById($this->get('rsnprelevementsid'), 'RsnPrelevements');
		}
		return $this->RsnPrelevement;
	}

	var $Account;
	public function getAccount(){
		$rsnPrelevement = $this->getRsnPrelevement();
		if(!$this->Account || $this->Account->getId() != $rsnPrelevement->get('accountid')){
			$account = Vtiger_Record_Model::getInstanceById($rsnPrelevement->get('accountid'), 'Accounts');
			$this->Account = $account;
		}
		return $this->Account;
	}

	var $Contact;
	public function getContact(){
		$account = $this->getAccount();
		if(!$this->Contact || $this->Contact->get('accountid') != $account->getId()){
			$this->Contact = $account->getRelatedMainContact();
		}
		return $this->Contact;
	}
	
	
	
	//Génération du PDF du rejet
	public function getRejetPrelvntPDF($filePath){
		
		$rsnPrelevement = $this->getRsnPrelevement();
		$accountRecordModel = $this->getAccount();
		$contactRecordModel = $this->getContact();
		
		include_once('modules/RsnPrelVirement/pdf/RejetPrelvnt/PDFController.php');
		
		$recordId = $this->getId();
		$moduleName = $this->getModuleName();

		$controllerClassName = "Vtiger_RejetPrelvnt_PDFController";
		
		//RsnPrelevements définit un en-tête spécifique relatif à Annie (cf Lettre de remerciement)
		$controller = new $controllerClassName('RsnPrelevements');
		$controller->loadRecord($rsnPrelevement->getId());
		
		foreach($this->getDisplayableValues() as $fieldName=>$value)
			$controller->setColumnValue($fieldName, $value);
		
		$controller->setColumnValue('contact_name', $contactRecordModel->getName());
		
			
		$date = str_replace(' 00:00:00', '', $this->get('dateexport'));
		$fileName = 'RejetPrelvnt_'.$date.'_'.$contactRecordModel->get('contact_no') . '.pdf';
		if($filePath){
			$fileName = $filePath . '/' . $fileName;
			$controller->Output($fileName, 'F');
		}
		else{
			$controller->Output($fileName, 'D');
		}
		return $fileName;
	}
}
