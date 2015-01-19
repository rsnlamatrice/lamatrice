<?php
/*+***********************************************************************************
 * 
 *************************************************************************************/
/***
 * 
 * Vtiger Module Model Class
 */
class RSNMediaRelations_Module_Model extends Vtiger_Module_Model {
	
	
	/**
	 * Function to save a given record model of the current module
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function saveRecord(Vtiger_Record_Model $recordModel) {
		if( ! $recordModel->get('rsnmediaid')){
			$mediaContactRecordModel = Vtiger_Record_Model::getInstanceById($recordModel->get('mediacontactid'), 'RSNMediaContacts');
			$recordModel->set('rsnmediaid', $mediaContactRecordModel->get('rsnmediaid'));
		}
		if( ! $recordModel->get('byuserid')){
			$currentUser = Users_Record_Model::getCurrentUserModel();
			$recordModel->set('byuserid', $currentUser->getId());
		}
		if( $recordModel->get('daterelation') ){
			// TODO Avec l'heure
			$date = preg_replace('/^(\d{1,2})\D(\d{1,2})\D(\d{2,4})(\s.*)?$/', '$3-$2-$1', $recordModel->get('daterelation'));
			$recordModel->set('daterelation', $date);
		}
		//die($recordModel->get('daterelation'));
		return parent::saveRecord($recordModel);
	}
	
	/** 
	* Function to get orderby sql from orderby field 
	*/ 
	public function getOrderBySql($orderBy){
		if($orderBy == 'daterelation'){
			//TODO le tri n'incluant pas l'heure, il faudrait l'ajouter mais le DESC ou ASC n'est pas géré à ce niveau
			$orderByField = $this->getFieldByColumn($orderBy); 
			return $orderByField->get('table') . '.' . $orderBy; 
		}
		return parent::getOrderBySql($orderBy); 
	} 
}
