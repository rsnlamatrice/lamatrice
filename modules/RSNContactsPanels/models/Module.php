<?php
/*+***********************************************************************************
 * ED14150102
 *************************************************************************************/
//vimport('~~/vtlib/Vtiger/Module.php');
/**
 * Vtiger Module Model Class
 */
class RSNContactsPanels_Module_Model extends Vtiger_Module_Model {
	/**
	 * Function to check whether the entity has an quick create menu
	 * @return <Boolean> true/false
	 * ED141024
	 */
	public function isQuickCreateMenuVisible() {
		return false ;
	}
	
	/**
	 * Function to save a given record model of the current module
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function saveRecord(Vtiger_Record_Model $recordModel) {
		$result = parent::saveRecord($recordModel);
		$this->checkVariables($recordModel);
		return $result;
	}
	
	/**
	 * Traitement des variables imbriques dans la requte
	 * 	transforme les lments de la forme [[Title | field/type | defaultValue ]]
	 * 	en RSNPanelsVariables
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function checkVariables(Vtiger_Record_Model $recordModel) {
		$query = $recordModel->get('query');
		$strVariables = array();
		//if(preg_match_all('/\[\[(?<op>\?|\=|IN\s)(?<var>(?<!\]\]).*?(?=\]\]))\]\]/', $query, $strVariables)){
		$variablesData = $recordModel->getVariablesFromQuery($query, true);
		//var_dump($variablesData);
		if($variablesData){
			/*$variablesData = array();
			$varIndex = 0;
			foreach($strVariables['var'] as $strVar){
				$strParams = explode('|', $strVar);
				for($paramIndex = 0; $paramIndex < count($strParams); $paramIndex++){
					$strParams[$paramIndex] = trim($strParams[$paramIndex]);
				}
				$variablesData[$strParams[0]] = array(
					'operation' => $strVariables['op'][$varIndex],
					'name' => $strParams[0],
					'field' => $strParams[1],
					'value' => $strParams[2],
					'sequence' => $varIndex++,
				);
			}*/
			
			//var_dump($variablesData);
			//var_dump('inputs : ', array_keys($variablesData));
			
			//Variables existantes
			$existingVariables = $this->getRelatedPanelVariables($recordModel);
			//var_dump('existings : ', array_keys($existingVariables));
			$newVariables = array();
			$changedVariables = array();
			//contrôle si la varaible est connue ou si une propriété change
			foreach($existingVariables as $existingVariable){
				$name = $existingVariable->get('name');
				if(isset($variablesData[$name])){
					$strVar = $variablesData[$name];
					//var_dump("existe", $name);
					//existe
					//corrige disabled
					if($existingVariable->get('disabled') == '1'){
						$existingVariable->set('disabled', '0');
						$changedVariables[$existingVariable->getId()] = $existingVariable;
					}
					if($existingVariable->get('sequence') != $strVar['sequence']){
						$existingVariable->set('sequence', $strVar['sequence']);
						$changedVariables[$existingVariable->getId()] = $existingVariable;
					}
					if($existingVariable->get('rsnvariabletype') != $strVar['operation']){
						$existingVariable->set('rsnvariabletype', $strVar['operation']);
						$changedVariables[$existingVariable->getId()] = $existingVariable;
					}
				}
				else {
					//var_dump("n'existe plus", $name);
					//n'existe plus
					//corrige disabled
					if($existingVariable->get('disabled') != '1'){
						$existingVariable->set('disabled', '1');
						$changedVariables[] = $existingVariable;
					}
				}
				$variablesData[$name]['_exists_'] = $existingVariable;
			}
			//var_dump(array_keys($changedVariables));
			// nouvelles variables
			foreach($variablesData as $strVarName => $strVar){
				if(!isset($strVar['_exists_']))	{
					//nouvelle variable
					//var_dump("nouvelle variable", $strVarName);
					$newVariable = Vtiger_Record_Model::getCleanInstance('RSNPanelsVariables');
					$newVariable->set('name', $strVarName);
					$newVariable->set('rsnpanelid', $recordModel->getId());
					$newVariable->set('defaultvalue', $strVar['value']);
					$newVariable->set('disabled', 0);
					$newVariable->set('fieldid', $strVar['field']);
					$newVariable->set('sequence', $strVar['sequence']);
					$newVariable->set('rsnvariableoperator', 'égal');
					$newVariable->set('rsnvariabletype', $strVar['operation']);
					$newVariable->save();
					
					//add relation
					$relationModel = Vtiger_Relation_Model::getInstance($recordModel->getModule(), $newVariable->getModule());
					$relationModel->addRelation($recordModel->getId(), $newVariable->getId());
				}
			}
			//$db = PearDatabase::getInstance();
			//$db->setDebug(true);
			// variables modifiées
			foreach($changedVariables as $existingVariable){
				
				//$modelData = $existingVariable->getData();
				//$recordModel->set('id', $recordId);
				$existingVariable->set('mode', 'edit');
				
				//var_dump('save : ', $existingVariable->get('name'));
				$existingVariable->save();
				
			}
			//$db->setDebug(false);
		}
		
	}
	
	/**
	 * Variables lies
	 */
	public function getRelatedPanelVariables(Vtiger_Record_Model $recordModel){
		$pagingModel = new Vtiger_Paging_Model();
		$relatedModuleName = 'RSNPanelsVariables';
		$relationListView = Vtiger_RelationListView_Model::getInstance($recordModel, $relatedModuleName);
		
		$relationListView->set('orderby', 'sequence');
		$relationListView->set('sortorder', 'ASC');
		
		$variables = $relationListView->getEntries($pagingModel);
		
		//var_dump($variables);
		return $variables;
	}

}
