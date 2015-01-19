<?php
/*+***********************************************************************************
 * 
 *************************************************************************************/

class RSNMediaContacts_Relation_Model extends Vtiger_Relation_Model{

	// La suppression de la relation entre tables conduit ˆ la suppression de l'entit 
	public function deleteRelation($sourceRecordId, $relatedRecordId){
		if(parent::deleteRelation($sourceRecordId, $relatedRecordId)){
			
			$record = Vtiger_Record_Model::getInstanceById($relatedRecordId, 'RSNMediaRelations');
			$record->delete();
			
			return true;
		}
		else
			return false;
	}
}
