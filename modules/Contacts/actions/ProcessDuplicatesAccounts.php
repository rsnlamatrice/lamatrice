<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/

//Coming after FindDuplicates and MergeRecord
class Contacts_ProcessDuplicatesAccounts_Action extends Vtiger_ProcessDuplicates_Action {
	
	function process (Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		$records = $request->get('records');
		
		//ED150911
		$reference_contactid = $request->get('referent_contactid');
		if($reference_contactid)
			$primaryRecord = $reference_contactid;
		else
			$primaryRecord = $request->get('primaryRecord');
			
		$primaryRecordModel = Vtiger_Record_Model::getInstanceById($primaryRecord, $moduleName);

		$mergeContacts = array_diff($records, array($primaryRecord));
		$allContacts = array_merge(array($primaryRecord), $mergeContacts);
						
		$fieldName = 'account_id';
		$fieldValue = $request->get($fieldName);
		$oldValue = $primaryRecordModel->get($fieldName);
		
		$steps = array();
		
		//Aucun compte existant
		if( ! $fieldValue && ! $oldValue ){
			//Création d'un nouveau compte
			$steps[] = '<br>Création du compte de référence '.$primaryRecordModel->getName();
			$mainAccountModel = $primaryRecordModel->getAccountRecordModel(true);
		}
		//Changement 
		elseif($fieldValue){
			$mainAccountModel = Vtiger_Record_Model::getInstanceById($fieldValue, 'Accounts');
			if($mainAccountModel){
				$steps[] ='<br>Compte de référence '.$mainAccountModel->getName();
			}
		}
		else
			$mainAccountModel = false;
		if($mainAccountModel){
			//Pour chacun des enregistrements en cause
			foreach($records as $recordId){
				if($recordId == $primaryRecord)
					$recordModel = $primaryRecordModel;
				else
					$recordModel = Vtiger_Record_Model::getInstanceById($recordId, $moduleName);
				
				$oldValue = $recordModel->get('account_id');
				if($fieldValue == $oldValue){
					$steps[] = '<br>Contact '.$recordModel->getName().' #'.$recordModel->getId();
					if($recordModel->getId() == $reference_contactid && !$recordModel->get('reference')){
						$recordModel->set('mode', 'edit');
						$recordModel->set('reference', 1);
						$recordModel->save();
					}
					elseif($recordModel->getId() != $reference_contactid
					   && (!$oldValue || $recordModel->get('reference'))){
						$recordModel->set('mode', 'edit');
						$recordModel->set('account_id', $mainAccountModel->getId());
						$recordModel->set('reference', 0);
						$recordModel->save();
					}
					continue;
				}
				
				if($oldValue){
					$oldAccount = $recordModel->getAccountRecordModel(false);
					//Etait référent de son compte
					if($oldAccount && $oldAccount->getId()
					   && $oldAccount->getId() != $mainAccountModel->getId()
					   && $recordModel->get('reference')){
						//Merge des Accounts
						$steps[] = '<br>Transfert des élément du compte vers '.$mainAccountModel->getName();
						$mainAccountModel->transferRelationInfoOfRecords(array($oldAccount->getId()));
						
						//below, others contacts of this account change also of account
					}
					else
						$oldAccount = false;//stand-by
				}
				else
					$oldAccount = false;
					
				//save account_id and reference flag
				$steps[] = '<br>Mise à jour du contact '.$recordModel->getName().' #'.$recordModel->getId();
				$recordModel->set('mode', 'edit');
				$recordModel->set('account_id', $mainAccountModel->getId());
				if($recordModel->getId() == $reference_contactid)
					$recordModel->set('reference', 1);
				else
					$recordModel->set('reference', 0); //TODO le compte a t'il tout de même un référent ?
				$recordModel->save();
				
				//Suppression du compte isolé
				if($oldValue && $oldAccount){
					$steps[] = '<br>Suppression du compte '.$oldAccount->getName();
					$this->deleteOrphanAccount($oldAccount);
				}
			}
		}
		else{
			$steps[] = '<br>Pas de compte de référence !';
		}
		
		if(!$request->get('isAjax')){
			echo implode('<br>', $steps);
			echo "Ok";
			return;
		}

		$response = new Vtiger_Response();
		$response->setResult(implode(",", $steps));
		$response->emit();
	}
	
	private function deleteOrphanAccount($oldAccount){
		$oldAccountContactsRecordModels = $oldAccount->getContactsRecordModels();
		if(count($oldAccountContactsRecordModels) === 0){
			//ED150911 TODO comme c'est dangereux, je prefère laisser la requête de contrôle signaler un Account sans Contact
			//$oldAccount->delete();
			$oldAccount->set('mode', 'edit');
			$oldAccount->set('accountname', '[SUPPR]'.$oldAccount->get('name'));
			$oldAccount->save();
		}
		else{
			//Contrôle qu'un contact est bien référent
			$contactReferent = false;
			
			foreach($oldAccountContactsRecordModels as $oldAccountContactsRecordModel){
				if($oldAccountContactsRecordModel->get('reference')){
					//Un contact est bien référent
					$contactReferent = $oldAccountContactsRecordModel;
					break;
				}
			}
			//Pas de contact référent
			if(!$contactReferent){
				$nContact = 0;
				foreach($oldAccountContactsRecordModels as $oldAccountContactsRecordModel){
					//Affectation au premier venu
					$oldAccountContactsRecordModel->set('mode', 'edit');
					$oldAccountContactsRecordModel->set('reference', $nContact++ ? 1 : 0);
					$oldAccountContactsRecordModel->save();
				}
				//ED150911 TODO comme c'est dangereux, je prefère laisser la requête de contrôle signaler un Account sans Contact
				//$oldAccount->delete();
				$oldAccount->set('mode', 'edit');
				$oldAccount->set('accountname', '[SUPPR]'.$oldAccount->get('name'));
				$oldAccount->save();
			}
		}
		
	}
}