<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Contacts_SaveAjax_Action extends Vtiger_SaveAjax_Action {

	public function process(Vtiger_Request $request) {
		if($request->get('mode')){
			$func = $request->get('mode');
			$this->$func($request);
			return;
		}
		parent::process($request);
	}
	
	public function saveNPAICriteres(Vtiger_Request $request) {
	
		$result = array();
		
		$document = false;
		$critereNPAI = false;

		foreach($request->get('contacts') as $contactId=>$contactData){
			foreach($contactData as $critereId => $critereData){
				$contact = Vtiger_Record_Model::getInstanceById($contactId, 'Contacts');
				if($critereId == 'NPAI'){
					$npaiChanged = $contact->get('rsnnpai') != $critereData['value']
						&& !($contact->get('rsnnpai') ===null && $critereData['value'] ==0);
					$notesId = $critereData['notesid'];
					$this->getNPAIDocumentAndCritere4D($notesId, $document, $critereNPAI);
					if($npaiChanged || $critereData['comment']){
						$contact->set('mode', 'edit');
						$contact->set('rsnnpai', $critereData['value']);
						if($npaiChanged)
							$contact->set('rsnnpaidate', date('Y-m-d'));
						if(array_key_exists('comment', $critereData))
							$contact->set('rsnnpaicomment', $critereData['comment']);
						$contact->save();
					}
					$this->createContactRelationNPAI($contact, $document, $critereNPAI);
				}
				elseif(is_numeric($critereId)){
					$contact->assignRelatedCritere4D($critereId, $critereData['date'], $critereData['data']);
				}
			}
				
			$result[$contactId] = true;
		}
		$result['contacts'] = str_replace("\n", "<br>", print_r($contactData, true));
		
		$response = new Vtiger_Response();
		$response->setEmitType(Vtiger_Response::$EMIT_JSON);
		$response->setResult($result);
		$response->emit();
	}
	
	//Retrouve le document et le critère de contact à associer
	private function getNPAIDocumentAndCritere4D($notesId, &$document, &$critereNPAI){
		if($document && $document->getId() == $notesId)
			return true;
		$document = Vtiger_Record_Model::getInstanceById($notesId, 'Documents');
		if(!$document)
			return false;
		global $adb;
		$query = 'SELECT crmid
			FROM vtiger_critere4d
			JOIN vtiger_crmentity
				ON vtiger_critere4d.critere4did = vtiger_crmentity.crmid
			WHERE vtiger_crmentity.deleted = false
			AND vtiger_critere4d.notesid = ?
			AND categorie = ?
			ORDER BY createdtime DESC
			LIMIT 1';
		$result = $adb->pquery($query, array($notesId, 'NPAI'));
		if(!$result){
			$adb->echoError();
			die();
		}
		if($adb->getRowCount($result) === 0){
			$critereNPAI = $this->createCritere4dNPAIFForDocument($notesId, $document);
		}
		else
			$critereNPAI = Vtiger_Record_Model::getInstanceById($adb->query_result($result, 0, 0), 'Critere4D');
	}
	//Retrouve le document et le critère de contact à associer
	private function createCritere4dNPAIFForDocument($notesId, &$document){
		$critereNPAI = Vtiger_Record_Model::getCleanInstance('Critere4D');
		if($document->get('codeaffaire'))
			$name = 'NPAI ' . $document->get('codeaffaire');
		else
			$name = 'NPAI ' . $document->getName();
		$critereNPAI->set('nom', $name);
		$critereNPAI->set('categorie', 'NPAI');
		$critereNPAI->set('ordredetri', 99);
		$critereNPAI->set('usage_debut', $document->get('createdtime'));
		$critereNPAI->set('notesid', $document->getId());
		$critereNPAI->save();
		if(!$critereNPAI->getId()){
			die("Le critère n'a pas pu être créé !");
		}
		return $critereNPAI;
	}
	
	//Retrouve le document et le critère de contact à associer
	private function createContactRelationNPAI(&$contact, &$document, &$critereNPAI){
		$contact->assignRelatedCritere4D($critereNPAI->getId(), date('d-m-Y'), $document->getName());
		
		global $adb;
		$query = "UPDATE vtiger_senotesrel
			SET data = CONCAT(IFNULL(data, ''), IF(IFNULL(data, '') = '', '', ' - '), ?)
			WHERE crmid = ?
			AND notesid = ?
		";
		$result = $adb->pquery($query, array('NPAI', $contact->getId(), $document->getId()));
		if(!$result){
			$adb->echoError();
			die();
		}
	}

	/** ED151208 depuis le QuickCreate, la sélection de "Ne pas prospecter" impacte les autres Ne Pas
	 * 
	 * Function to get the record model based on the request parameters
	 * @param Vtiger_Request $request
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	public function getRecordModelFromRequest(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$recordId = $request->get('record');

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		if(!empty($recordId) && $request->get('fields')) {
			$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
			$fields = $request->get('fields');
			if(array_key_exists('email', $fields)){
				$oldEmail = $recordModel->get('email');
			}
			if(array_key_exists('rsnnpai', $fields)){
				$oldNPAI = $recordModel->get('rsnnpai');
			}
		}
		
		$recordModel = parent::getRecordModelFromRequest($request);
		
		if(empty($recordId) && $recordModel->get('donotprospect')) {
			
			/* "ne pas" partout */
			foreach( array('emailoptout', 'donotcall', 'donotrelanceadh', 'donotappeldoncourrier', 'donotrelanceabo', 'donotappeldonweb')
					as $fieldName){
				$recordModel->set($fieldName, 1);
			}
			$fieldName = 'donototherdocuments';
			$recordModel->set($fieldName, 'Reçu fiscal seul');
		}
		if(!empty($recordId) && $request->get('fields')) {
			if(isset($oldEmail) && $oldEmail != $recordModel->get('email')){
				$recordModel->set('email_add_history', $oldEmail);
			}
			if(isset($oldNPAI) && $oldNPAI != $recordModel->get('rsnnpai')
			   && !(!$oldNPAI && !$recordModel->get('rsnnpai'))){
				$recordModel->set('rsnnpaidate', date('Y-m-d'));
			}
		}
		return $recordModel;
	}
}
