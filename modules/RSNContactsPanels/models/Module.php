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
	 * Function to save a given record model of the current module
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function saveRecord(Vtiger_Record_Model $recordModel) {
		$result = parent::saveRecord($recordModel);
		$this->checkVariables($recordModel);
		return $result;
	}
	
	/**
	 * Traitement des variables imbriquŽes dans la requte
	 * 	transforme les ŽlŽments de la forme [[Title | field/type | defaultValue ]]
	 * 	en RSNPanelsVariables
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function checkVariables(Vtiger_Record_Model $recordModel) {
		$query = $recordModel->get('query');
		$strVariables = array();
		if(preg_match_all('/\[\[(?<var>(?<!\]\]).*?(?=\]\]))\]\]/', $query, $strVariables)){
			$variablesData = array();
			$varIndex = 0;
			foreach($strVariables['var'] as $strVar){
				$strParams = explode('|', $strVar);
				for($paramIndex = 0; $paramIndex < count($strParams); $paramIndex++){
					$strParams[$paramIndex] = trim($strParams[$paramIndex]);
				}
				$variablesData[$strParams[0]] = array(
					'name' => $strParams[0],
					'field' => $strParams[1],
					'value' => $strParams[2],
					'sequence' => $varIndex++,
				);
			}
			
			//var_dump($variablesData);
			var_dump('inputs : ', array_keys($variablesData));
			
			//Variables existantes
			$existingsVariables = $this->getRelatedPanelVariables($recordModel);
			//var_dump('existings : ', array_keys($existingsVariables));
			$newVariables = array();
			$changedVariables = array();
			foreach($existingsVariables as $existingsVariable){
				if(isset($variablesData[$existingsVariable->get('name')])){
					$strVar = $variablesData[$existingsVariable->get('name')];
					//var_dump("existe", $existingsVariable->get('name'));
					//existe
					//corrige disabled
					if($existingsVariable->get('disabled') == '1'){
						$existingsVariable->set('disabled', '0');
						$changedVariables[$existingsVariable->getId()] = $existingsVariable;
					}
					if($existingsVariable->get('sequence') != $strVar['sequence']){
						$existingsVariable->set('sequence', $strVar['sequence']);
						$changedVariables[$existingsVariable->getId()] = $existingsVariable;
					}
				}
				else {
					//var_dump("n'existe plus", $existingsVariable->get('name'));
					//n'existe plus
					//corrige disabled
					if($existingsVariable->get('disabled') != '1'){
						$existingsVariable->set('disabled', '1');
						$changedVariables[] = $existingsVariable;
					}
				}
				$variablesData[$existingsVariable->get('name')]['_exists_'] = $existingsVariable;
			}
			//var_dump(array_keys($changedVariables));
			// nouvelles variables
			foreach($variablesData as $strVarName => $strVar){
				if(!isset($strVar['_exists_']))	{
					//nouvelle variable
					var_dump("nouvelle variable", $strVarName);
					$newVariable = Vtiger_Record_Model::getCleanInstance('RSNPanelsVariables');
					$newVariable->set('name', $strVarName);
					$newVariable->set('rsnpanelid', $recordModel->getId());
					$newVariable->set('defaultvalue', $strVar['value']);
					$newVariable->set('disabled', 0);
					$newVariable->set('fieldid', $strVar['field']);
					$newVariable->set('sequence', $strVar['sequence']);
					$newVariable->save();
					
					//add relation
					$relationModel = Vtiger_Relation_Model::getInstance($recordModel->getModule(), $newVariable->getModule());
					$relationModel->addRelation($recordModel->getId(), $newVariable->getId());
				}
			}
			//$db = PearDatabase::getInstance();
			//$db->setDebug(true);
			// variables modifiŽes
			foreach($changedVariables as $existingsVariable){
				
				//$modelData = $existingsVariable->getData();
				//$recordModel->set('id', $recordId);
				$existingsVariable->set('mode', 'edit');
				
				//var_dump('save : ', $existingsVariable->get('name'));
				//var_dump($existingsVariable->save());
				
			}
			//$db->setDebug(false);
		}
		
	}
	
	/**
	 * Variables liŽes
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
