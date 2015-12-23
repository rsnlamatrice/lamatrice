<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class RSNAboRevues_Save_Action extends Vtiger_Save_Action {
	
	/**
	 * Function to get the record model based on the request parameters
	 * @param Vtiger_Request $request
	 * @return Vtiger_Record_Model or Module specific Record Model instance
	 */
	public function getRecordModelFromRequest(Vtiger_Request $request) {

		$recordModel = parent::getRecordModelFromRequest($request);
		
		RSNAboRevues_Save_Action::checkAboStatus($request, $recordModel);
		
		return $recordModel;
	}
	
	/**
	 * Vérifie la cohérence entre la date de fin d'abo et le statut Abonné
	 */
	public static function checkAboStatus(Vtiger_Request $request, &$recordModel) {

		$moduleModel = Vtiger_Module_Model::getInstance($request->getModule());
		$aboType = $recordModel->getTypeAbo();
		$typeAbonnable = $moduleModel->isTypeAbonnable($aboType);
		if(!$typeAbonnable){
			$recordModel->setAbonne(false);
		}
		else {
			$toDay = new DateTime();
			$dateDebut = $recordModel->getDebutAbo();
			$dateFin = $recordModel->getFinAbo();
			if($dateDebut <= $toDay
			&& (!$dateFin || $dateFin >= $toDay)){
				$recordModel->setAbonne(true);
			} else {
				$recordModel->setAbonne(false);
			}
		}
		return $recordModel;
	}
}
