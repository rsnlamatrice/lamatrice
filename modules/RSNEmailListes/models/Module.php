<?php
/*+***********************************************************************************
 * 
 *************************************************************************************/

class RSNEmailListes_Module_Model extends Vtiger_Module_Model {
	
	//TODO a chie...
	
	///* ED150323
	// * Provides the ability for Document / Related contacts / Campaigns to show dateapplication data
	// * see /modules/Vtiger/models/RelationListView.php, function getEntries($pagingModel)
	//*/
	//public function getConfigureRelatedListFields(){
	//	$ListFields = parent::getConfigureRelatedListFields();
	//	// Documents
	//	$ListFields['datesubscribe'] = 'datesubscribe';
	//	$ListFields['dateunsubscribe'] = 'dateunsubscribe';
	//	$ListFields['data'] = 'data';
	//	return $ListFields;
	//}
	//   
	///* ED141223
	// * fields de la table vtiger_emaillistesrel
	//*/
	//public function getRelationHeaders(){
	//	$headerFields = array();
	//	
	//	//Added to support dateapplication
	//	$field = new Vtiger_Field_Model();
	//	$field->set('name', 'datesubscribe');
	//	$field->set('column', strtolower( 'datesubscribe' ));
	//	$field->set('label', 'Date d\'inscription');
	//	$field->set('typeofdata', 'DATETIME');
	//	$field->set('uitype', 6);
	//	$headerFields[$field->get('name')] = $field;
	//	
	//	//Added to support dateapplication
	//	$field = new Vtiger_Field_Model();
	//	$field->set('name', 'dateunsubscribe');
	//	$field->set('column', strtolower( 'dateunsubscribe' ));
	//	$field->set('label', 'Date de dsinscription');
	//	$field->set('typeofdata', 'DATETIME');
	//	$field->set('uitype', 6);		
	//	$headerFields[$field->get('name')] = $field;
	//	
	//	return $headerFields;
	//}
}