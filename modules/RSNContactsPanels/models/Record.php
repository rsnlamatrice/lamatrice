<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class RSNContactsPanelsExecutionController {
	
	static $_stack = array();
	
	public static function getCallKey($panel){
		return '_' . $panel->getId()
			//. (count(self::$_stack) ? '@' . $_stack[count($_stack) - 1] : '')
		;
	}
	
	public static function stack($panel){
		$key = self::getCallKey($panel);
		if(isset(self::$_stack[$key])){
			echo '<code># Récursivité infinie pour "' . $panel->get('name') . '" à <pre>' . print_r(self::$_stack, true) . '</pre> #</code>';
			return FALSE;
		}
		self::$_stack[$key] = $panel->get('name');
		return TRUE;
	}
	public static function unstack($panel){
		if(count(self::$_stack)){
			unset(self::$_stack[self::getCallKey($panel)]);
		}
	}
}
/**
 * Vtiger Entity Record Model Class
 */
class RSNContactsPanels_Record_Model extends Vtiger_Record_Model {

	/**
	 * retourne la requête d'après le champ query
	 *
	 */
	public function getPanelQuery(){
		return str_replace(array('&lt;', '&gt;','&#039;')
				   , array('<', '>', "'")
				   , $this->get('query'));
	}
	
	
	
	/**
	 * Traitement des variables imbriques dans la requête
	 * 	transforme les éléments de la forme [[Title | field/type | defaultValue ]]
	 * 	en RSNPanelsVariables
	 * @param Boolean $associative : returns an associative array
	 *
	 * format :
	 * 	[[<operator><name> | <field> | <default value>)]]
	 * opérateurs :
	 * 	?	valeur passée par paramètre. Le SQL comporte un ?.
	 * 	??	opérateur inséré dans le SQL et valeur passée par paramètre. Le SQL comporte un ?.
	 * 	=	valeur insérée dans le SQL.
	 * 	IN	série insérée dans le sql. Les paramètres sont ajoutés.
	 * 	PANEL	la requête d'un sous-panel est inséré dans le SQL. Les paramètres sont ajoutés.
	 *
	 * PANEL
	 * 	les paramètres suivants le <fieldid> peuvent être de la forme <var name>:=<set prior value>
	 * 	[[PANEL NPAI Ok | Adresses/NPAI Ok | NPAI maxi := 2]]
	 */
	public function getVariablesFromQuery($query, $associative = FALSE) {
		$strVariables = array();
		if(preg_match_all('/\[\[(?<op>\?+|\=|PANEL\s|IN\s)(?<var>(?<!\]\]).*?(?=\]\]))\]\]/', $query, $strVariables)){
			$variablesData = array();
			$varIndex = 0;
			foreach($strVariables['var'] as $strVar){
				$strParams = explode('|', $strVar);//TODO escape |##|
				for($paramIndex = 0; $paramIndex < count($strParams); $paramIndex++){
					$strParams[$paramIndex] = trim($strParams[$paramIndex]);
				}
				$strVariables['op'][$varIndex] = strtoupper(trim($strVariables['op'][$varIndex]));
				if($strVariables['op'][$varIndex] == 'PANEL'){
					
					$params = array();
					for($nParam = 2; $nParam < count($strParams); $nParam++){
						$pos = strpos($strParams[$nParam], ':=');
						if($pos){
							$params[] = array(trim(substr($strParams[$nParam], 0, $pos)),	//name
									trim(substr($strParams[$nParam], $pos + 2))	//value
								);
							array_splice($strParams, $nParam, 1);
							$nParam--;
						}
					}
					//var_dump($params);
					$strParams[2] = self::queryParams_encode($params);
				}
				$variableData = array(
					'operation' => $strVariables['op'][$varIndex],
					'name' => $strParams[0],
					'field' => $strParams[1],
					'value' => $strParams[2],
					'sequence' => $varIndex++,
				);
				if($associative){
					if(!isset($variablesData[$variableData['name']]))
						$variablesData[$variableData['name']] = $variableData;
				}
				else			
					$variablesData[] = $variableData;
			}
			//var_dump($variablesData);
			return $variablesData;
		}
		return array();
		
	}
	
	public static function queryParams_encode($array){
		return json_encode($array);
	}
	
	public static function queryParams_decode($string, &$variables = FALSE){
		if(!is_array($variables))
			$variables = array();
		if($string){
			$array = json_decode(decode_html($string));
			if(!$array){
				echo_callstack();
				var_dump(__FILE__ . ' ERREUR dans queryParams_decode(), variables non interprétables par json_decode', $string, $array);
			}
			elseif(!is_array($array)){
				echo_callstack();
				var_dump(__FILE__ . ' ERREUR dans queryParams_decode(), variables !is_array', $string, $array);
			}
			else
				foreach($array as $variable)
					$variables[$variable[0]] = array(
						'name' => $variable[0],
						'value' => $variable[1]
					);
		}
		return $variables;
	}
	
//	/**
//	 * Function returns query execution result widget
//	 * @param array &$params : current sql query parameters array
//	 * @param array &$paramsDetails : for each parameter, add details
//	 * @param array &$paramsPriorValues : valeurs de variables à affecter
//	 * @return <type>
//	 */
//	function getExecutionSQL(&$params = FALSE, &$paramsDetails = FALSE, &$paramsPriorValues = FALSE, &$callStask = FALSE) {
//		if(!RSNContactsPanelsExecutionController::stack($this)) //do not forget to unstack
//			return null;
//		if(!is_array($params))
//			$params = array();
//		if(!is_array($paramsDetails))
//			$paramsDetails = array();
//		
//		$sql = $this->getPanelQuery();
//		$queryVariables = $this->getVariablesFromQuery($sql);// array() extrait de la requête
//		//var_dump($queryVariables);
//		//variables connues et déjà liées
//		$relatedVariables = $this->getRelatedVariables();
//		
//		//variables liées filtrées par (disabled == 0)
//		$variables = array();
//		foreach($relatedVariables as $variable)
//			if(!$variable->get('disabled'))
//				$variables[$variable->get('name')] = $variable;
//		
//		
//		$thisPath = $this->getName();
//		if(!is_array($callStask))
//			$callStask = array($thisPath=>$thisPath);
//		else
//			$callStask[$thisPath] = $thisPath;
//		$thisPath = implode('/', $callStask);
//		$thisDepth = count($callStask);
//		
//		
//		$variablesPrevValues = array();
//					
//		//affectation de valeur aux variables
//		// provient de la syntaxe [[PANEL <subpanel> | <domain>/<subpanelname> | Nom_Var:=Value | Nom_Var2 := Value]]
//		// ou des valeurs de customview
//		if(is_array($paramsPriorValues)){
//			$followParamsPriorValues = array();
//			$thisInstanceName = $this->get('instanceName');
//			//$paramsPriorValues est un array( array('name'=>name, 'value'=>value), ... ) ou array(name => value, ...)
//			foreach($paramsPriorValues as $paramName => $paramPriorInfos){
//				if(is_array($paramPriorInfos) && $paramPriorInfos['name']){
//					$paramName = $paramPriorInfos['name'];
//					$paramPriorValue = $paramPriorInfos['value'];
//				}
//				else
//					$paramPriorValue = $paramPriorInfos;
//				//le nom de la variable contient un /
//				if(strpos($paramName, '/')){
//					//Destiné à un sous-panel
//					var_dump('sous panel', $paramPriorInfos, $paramPriorValue);
//					$followParamPriorValue = array_merge(array(), $paramPriorInfos, array(
//						'path' => $thisPath,
//						'parent' => substr($paramName, 0, strpos($paramName, '/')),
//						'name' => substr($paramName, strpos($paramName, '/')+1),
//						'value' => $paramPriorValue,
//					));
//					$followParamsPriorValues[substr($paramName, strpos($paramName, '/')+1)] = $followParamPriorValue;
//				}
//				//le nom de la variable est connu
//				elseif(isset($variables[$paramName])){
//					$variable = $variables[$paramName];
//					//valeur précédente
//					if(!isset($variablesPrevValues[$paramName]))
//						$variablesPrevValues[$paramName] = $variable->get('defaultvalue'); 
//					//affectation de la valeur pour son usage ci-dessous
//					$variable
//						//valeur à utiliser
//						->set('defaultvalue', $paramPriorValue)
//						// Chemin des appels
//						//TODO path ne fonctionne pas, l'objet est partagé, c'est donc purement cumulatif
//						->set('path', //utilisé pour le commentaire
//							$thisPath
//							. '/')
//					;
//					if($paramPriorInfos['comparator'])
//						$variable->set('rsnvariableoperator', $paramPriorInfos['comparator']);
//					//var_dump($paramPriorValue->name, $paramPriorValue->value);
//				}
//				else
//					var_dump('<br><code># paramètre de panel "' . $paramName . '" introuvable #<code><br>');
//			}
//		}
//		//var_dump($variablesPrevValues);
//		
//		$variablesId = array();
//		
//		//fin du regex 
//		$regex_end = '\s*(\|[^\]|]*)*\]\]/';
//			
//		// pour chaque variable de la requête
//		foreach($queryVariables as $queryVariable){
//			$variableName = $queryVariable['name'];
//			if(!isset($variables[$variableName])){
//				// Erreur
//				$value = '[[# Variable "' . $variableName . '" inconnue ! #]]';
//				$paramsDetails[$queryVariable['name']] = array(
//					'name'=>$queryVariable['name'],
//					'variable'=> null,
//					'value'=> $value
//				);
//			}
//			else {
//				$variable = $variables[$variableName];
//				$value = str_replace('&quot;', '"', $variable->get('defaultvalue'));
//				//Variable déjà traitée
//				if(!isset($variablesId[$variable->getId()])){
//					$paramsDetails[$queryVariable['name']] = array(
//						'operation'=> $queryVariable['operation'],
//						'name'=> $queryVariable['name'],
//						'variable'=> $variable,
//						'value'=> $value,
//						'depth'=> $thisDepth,
//					);
//					$variablesId[$variable->getId()] = 1;
//				}
//			}
//			// commentaire en préfixe
//			$comment = ' /*[[' . str_replace('?', '!', $queryVariable['operation'] . ' ' . $variable->get('path') . $variableName) . ']]*/ ';
//			
//			//TODO le regex de fin s'arrête dès le 1er ] existant : faire pour ]]
//			//$regex_end = '\s*(\|[^\]]*)*\]\]/'; cf plus haut
//			
//			//selon opération
//			switch(strtoupper($queryVariable['operation'])){
//			case '?':
//				$params[] = $value;
//				/* injection dans le sql d'un paramètre */
//				$sql = preg_replace('/\[\[\?\s*' . preg_quote($variableName) . $regex_end, 
//						    $comment
//						    . '?', $sql);
//				break;
//			case '??':
//				if($variable)
//					$sqlOperation = $variable->getSQLOperation($value, $params);
//				else
//					$sqlOperation = '= ?';
//				$params[] = $value;
//				$paramsDetails[$queryVariable['name']]['value'] = $value;
//				/* injection dans le sql d'un paramètre */
//				$sql = preg_replace('/\[\[\?\?\s*' . preg_quote($variableName) . $regex_end,
//						    $comment
//						    . $sqlOperation, $sql);
//				break;
//			
//			case 'IN':
//				$value = explode(' |##| ', $value);
//				$params = array_merge($params, $value);
//				$value = generateQuestionMarks($value);
//				/* injection dans le sql */
//				$sql = preg_replace('/\[\[IN\s+' . preg_quote($variableName) . $regex_end, 
//						    $comment
//						    . ' IN (' . $value . ')', $sql);
//				break;
//			case 'PANEL':
//				$instanceName = $variable->get('name');
//				$panelName = $variable->get('fieldid');
//				$subPanelRecord = self::getInstanceByNamePath($panelName, $this->get('rsncontactspanelsdomains'));
//				if($subPanelRecord){
//					/* affectation des valeurs passées par paramètres */
//					$paramsPriorValues = self::queryParams_decode( $value );
//					/* arguments suivis */
//					if(isset($followParamsPriorValues)){
//						foreach($followParamsPriorValues as $followParamName => $followParamPriorValue){
//							//var_dump($followParamPriorValue, $instanceName);
//							// contrôle le parent
//							if($followParamPriorValue['parent'] == $instanceName){
//								$paramsPriorValues[$followParamName] = $followParamPriorValue;
//							}
//						}
//						//var_dump($paramsPriorValues, $instanceName);
//					}
//					/* sous-requête d'exécution */
//					$index = 0;
//					//foreach($paramsPriorValues as $paramPriorValue){
//					//	$paramPriorValue['prev_path'] = $paramPriorValue['path'];
//					//	$paramPriorValue['path'] = ($paramPriorValue['path'] ? $paramPriorValue['path'] . '/' : '')
//					//		. $thisPath
//					//		. '/'
//					//	;
//					//	$paramsPriorValues[$index++] = $paramPriorValue;
//					//}
//					//getExecutionSQL
//					//var_dump($params, $paramsDetails, $paramsPriorValues);
//					$value = $subPanelRecord->getExecutionSQL($params, $paramsDetails, $paramsPriorValues, $callStask);
//					//restaure
//					//foreach($paramsPriorValues as $paramsPriorValue){
//					//	$paramsPriorValue['path'] = $paramsPriorValue['prev_path'];
//					//}
//					
//				}
//				else
//					$value = '<code># Panel "'.$panelName.'" introuvable #</code>';
//				$variableName = str_replace('/', '\\/', (preg_quote($variableName)));
//				/* injection dans le sql */
//				$sql = preg_replace('/\[\[PANEL\s+' . ($variableName) . $regex_end,
//						    $comment . ' (
//'							 . $value . '
//'							 . '/* [[FIN DE ' . $variableName . ']] */
//						    )'
//						    , $sql);
//				break;
//			case '=':
//				/* injection dans le sql */
//				$sql = preg_replace('/\[\[\=\s*' . preg_quote($variableName) . $regex_end,
//						    //$comment . pas de commentaire, car peut changer la requête
//						    $value, $sql);
//				break;
//			default:
//				/* injection dans le sql */
//				$sql = preg_replace('/\[\[' . preg_quote($queryVariable['operation']) .'\s*' . preg_quote($variableName) . $regex_end,
//						    $comment .
//						    $value, $sql);
//				break;
//			}
//			
//		}
//		//echo("<pre>$sql</pre>");
//		//var_dump($params);
//		
//		// rétablit les valeurs d'orgine aux variables
//		foreach($variablesPrevValues as $variableName => $variablePrevValue){
//			$variables[$variableName]->set('defaultvalue', $variablePrevValue);
//		}
//		RSNContactsPanelsExecutionController::unstack($this);
//		return $sql;
//	}
	
	/**
	 * Function returns query execution result widget
	 * @param array &$paramsPriorValues : valeurs de variables à affecter
	 * @param array &$paramsDetails : for each parameter, add details
	 * @return <type>
	 */
	function getExecutionQuery(&$paramsPriorValues = FALSE, &$paramsDetails = FALSE, &$callStask = FALSE) {
		if(!RSNContactsPanelsExecutionController::stack($this)) //do not forget to unstack
			return null;
		if(!is_array($paramsDetails))
			$paramsDetails = array();
		$sql = $this->getPanelQuery();
		$queryVariables = $this->getVariablesFromQuery($sql);// array() extrait de la requête
		//var_dump($queryVariables);
		//variables connues et déjà liées
		$relatedVariables = $this->getRelatedVariables();
		
		//variables liées filtrées par (disabled == 0)
		$variables = array();
		foreach($relatedVariables as $variable)
			if(!$variable->get('disabled'))
				$variables[$variable->get('name')] = $variable;
		
		
		$thisPath = $this->getName();
		if(!is_array($callStask))
			$callStask = array($thisPath=>$thisPath);
		else
			$callStask[$thisPath] = $thisPath;
		$thisPath = implode('/', $callStask);
		$thisDepth = count($callStask);
		
		//Valeurs des variables avant affectation (rétablissement des valeurs en fin de fonction)
		$variablesPrevValues = array();
					
		//affectation de valeur aux variables
		// provient d'une récursivité par la syntaxe [[PANEL <subpanel> | <domain>/<subpanelname> | Nom_Var:=Value | Nom_Var2 := Value]]
		// ou des valeurs de customview via le QueryGenerator
		if(is_array($paramsPriorValues)){
			$followParamsPriorValues = array();
			$thisInstanceName = $this->get('instanceName');
			//$paramsPriorValues est un array( array('name'=>name, 'value'=>value), ... ) ou array(name => value, ...)
			foreach($paramsPriorValues as $paramName => $paramPriorInfos){
				if(is_array($paramPriorInfos) && $paramPriorInfos['name']){
					$paramName = $paramPriorInfos['name'];
					$paramPriorValue = $paramPriorInfos['value'];
				}
				else
					$paramPriorValue = $paramPriorInfos;
				//le nom de la variable contient un /
				if(strpos($paramName, '/') !== false){
					//Destiné à un sous-panel
					var_dump('sous panel', $paramPriorInfos, $paramPriorValue);
					$followParamPriorValue = array_merge(array(), $paramPriorInfos, array(
						'path' => $thisPath,
						'parent' => substr($paramName, 0, strpos($paramName, '/')),
						'name' => substr($paramName, strpos($paramName, '/')+1),
						'value' => $paramPriorValue,
					));
					$followParamsPriorValues[substr($paramName, strpos($paramName, '/')+1)] = $followParamPriorValue;
				}
				//le nom de la variable est connu
				elseif(isset($variables[$paramName])){
					$variable = $variables[$paramName];
					//valeur précédente
					if(!isset($variablesPrevValues[$paramName]))
						$variablesPrevValues[$paramName] = $variable->get('defaultvalue'); 
					//affectation de la valeur pour son usage ci-dessous
					$variable
						//valeur à utiliser
						->set('defaultvalue', $paramPriorValue)
						// Chemin des appels
						//TODO path ne fonctionne pas, l'objet est partagé, c'est donc purement cumulatif
						->set('path', //utilisé pour le commentaire
							$thisPath
							. '/')
					;
					if($paramPriorInfos['comparator'])
						$variable->set('rsnvariableoperator', $paramPriorInfos['comparator']);
					//var_dump($paramPriorValue->name, $paramPriorValue->value);
				}
				else
					var_dump('<br><code># paramètre de panel "' . $paramName . '" introuvable #<code><br>');
			}
		}
		//var_dump($variablesPrevValues);
		
		$variablesId = array();
		
		//fin du regex 
		$regex_end = '\s*(\|[^\]|]*)*\]\]/';
			
		// pour chaque variable de la requête
		foreach($queryVariables as $queryVariable){
			$variableName = $queryVariable['name'];
			if(!isset($variables[$variableName])){
				// Erreur
				$value = '[[# Variable "' . $variableName . '" inconnue ! #]]';
				$paramsDetails[$queryVariable['name']] = array(
					'name'=>$queryVariable['name'],
					'variable'=> null,
					'value'=> $value
				);
			}
			else {
				$variable = $variables[$variableName];
				$value = $variable->get('defaultvalue');
				if(is_string($value))
					$value = decode_html($value); 
				//Variable déjà traitée
				if(!isset($variablesId[$variable->getId()])){
					$paramsDetails[$queryVariable['name']] = array(
						'operation'=> $queryVariable['operation'],
						'name'=> $queryVariable['name'],
						'variable'=> $variable,
						'value'=> $value,
						'depth'=> $thisDepth,
					);
					$variablesId[$variable->getId()] = 1;
				}
			}
			
			if(isset($variables[$variableName])){
				$value = $variables[$variableName]->getValueForSQL($value);
			}
			
			// commentaire en préfixe
			$comment = ' /*[[' . str_replace('?', '!', $queryVariable['operation'] . ' ' . $variable->get('path') . $variableName) . ']]*/ ';
			
			//TODO le regex de fin s'arrête dès le 1er ] existant : faire pour ]]
			//$regex_end = '\s*(\|[^\]]*)*\]\]/'; cf plus haut
			
			//selon opération
			switch(strtoupper($queryVariable['operation'])){
			case '?':
				/* injection dans le sql d'une valeur */
				$sql = preg_replace('/\[\[\?\s*' . preg_quote($variableName) . $regex_end, 
						    $comment
						    . "'$value'", $sql);
				break;
			case '??':
				/* injection dans le sql d'un opérateur et d'une valeur */
				if($variable){
					$sqlOperation = $variable->getSQLOperation($value, $params);
					$paramsDetails[$queryVariable['name']]['value'] = $value;//éventuelle modifiée dans la fonction
				}
				else
					$sqlOperation = "= '$value'";
				/* injection dans le sql d'un paramètre */
				$sql = preg_replace('/\[\[\?\?\s*' . preg_quote($variableName) . $regex_end,
						    $comment
						    . $sqlOperation, $sql);
				break;
			
			case 'IN':
				$values = explode(' |##| ', $value);
				$sqlValues = '';
				foreach($values as $value){
					if($sqlValues)
						$sqlValues .= ',';
					$sqlValues .= "'$value'";
				}
				/* injection dans le sql */
				$sql = preg_replace('/\[\[IN\s+' . preg_quote($variableName) . $regex_end, 
						    $comment
						    . ' IN (' . $sqlValues . ')', $sql);
				break;
			case 'PANEL':
				$instanceName = $variable->get('name');
				$panelName = $variable->get('fieldid');
				$subPanelRecord = self::getInstanceByNamePath($panelName, $this->get('rsncontactspanelsdomains'));
				if($subPanelRecord){
					/* affectation des valeurs passées par paramètres */
					/* TODO $value == 1 ou 0 provient de l'éditeur de CustomView qui n'a pas anticipé le type PANEL */
					if($value === 0 || $value === '0'){
						//TODO Exclude from PANEL
						$paramsPriorValues = array();
					}
					elseif($value == 1){
						$paramsPriorValues = array();
					}
					else{
						$paramsPriorValues = self::queryParams_decode( $value );
					}
					/* arguments suivis */
					if(isset($followParamsPriorValues)){
						foreach($followParamsPriorValues as $followParamName => $followParamPriorValue){
							//var_dump($followParamPriorValue, $instanceName);
							// contrôle le parent
							if($followParamPriorValue['parent'] == $instanceName){
								$paramsPriorValues[$followParamName] = $followParamPriorValue;
							}
						}
						//var_dump($paramsPriorValues, $instanceName);
					}
					/* sous-requête d'exécution */
					$index = 0;
					$value = $subPanelRecord->getExecutionQuery($paramsPriorValues, $paramsDetails, $callStask);
					
				}
				else
					$value = '<code># Panel "'.$panelName.'" introuvable #</code>';
				$variableName = str_replace('/', '\\/', (preg_quote($variableName)));
				/* injection dans le sql */
				$sql = preg_replace('/\[\[PANEL\s+' . ($variableName) . $regex_end,
						    $comment . ' (
'							 . $value . '
'							 . '/* [[FIN DE ' . $variableName . ']] */
						    )'
						    , $sql);
				break;
			case '=':
				/* injection brute dans le sql */
				$sql = preg_replace('/\[\[\=\s*' . preg_quote($variableName) . $regex_end,
						    //$comment . pas de commentaire, car peut changer la requête
						    $value, $sql);
				break;
			default:
				/* injection brute dans le sql */
				$sql = preg_replace('/\[\[' . preg_quote($queryVariable['operation']) .'\s*' . preg_quote($variableName) . $regex_end,
						    $comment .
						    $value, $sql);
				break;
			}
			
		}
		//echo("<pre>$sql</pre>");
		//var_dump($params);
		
		// rétablit les valeurs d'orgine aux variables
		foreach($variablesPrevValues as $variableName => $variablePrevValue){
			$variables[$variableName]->set('defaultvalue', $variablePrevValue);
		}
		RSNContactsPanelsExecutionController::unstack($this);
		return $sql;
	}
	
	
	
	///** ED150507 OBSOLETE
	// * Function returns query execution result widget
	// * Replaces question mark with param value
	// * @param $paramsNameValuePairs : values to insert in query
	// * @return <string>
	// */
	//function getExecutionSQLWithIntegratedParams($paramsValues = false){
	//	$params = array();
	//	$paramsDetails = array();
	//	$sql = $this->getExecutionSQL($params, $paramsDetails, $paramsValues);
	//	/*if(count($params) !== count($paramsDetails)){
	//		echo "<br><br><br><br><br>getExecutionSQLWithIntegratedParams : Les tableaux sont de tailles différentes";
	//		var_dump($params, $paramsDetails);
	//	}*/
	//	if(!$sql)
	//		return $sql;
	//	//for($i = 0; $i < count($params); $i++){
	//	foreach($paramsDetails as $paramName => $paramDetails){
	//		// replace /*[[operator xxx]]*/ [operator] ? with /*[[operator xxx]]*/ 'value'
	//		$sql = preg_replace('/(\/\*\[\[.*'.preg_quote($paramName) . '[^\]]*\]\]\*\/\s[^?]*)\?/'
	//				    , '$1\'' . str_replace('\'', '\\\'', $paramDetails['value']) . '\''
	//				    , $sql);
	//	}
	//	return $sql;
	//}
	
	/**
	 * Fonction qui retourne une instance de record d'après son chemin.
	 * Un chemin est la combinaison du domaine et du nom
	 */
	public static function getInstanceByNamePath($path, $root = ''){
		if(is_string($path))
			$path = explode('/', $path);
		if(count($path) == 1){
			$path[] = $path[0];
			$path[0] = $root;
		}
		$recordObject = Vtiger_Cache::get('rsncontactspanel', implode('/', $path));
		if($recordObject)
			return $recordObject;
		
		$sql = "SELECT rsncontactspanelsid
			FROM vtiger_rsncontactspanels
			WHERE rsncontactspanelsdomains = ?
			AND name = ?
			LIMIT 1";
		$db = PearDatabase::getInstance();
		//$db->setDebug(true);
		$result = $db->pquery($sql, $path);
		if($db->num_rows($result) == 0)
			return null;
		$id = $db->query_result($result,0,0);
		//TODO éviter une second requête sql sur le même enregistrement, tout en gardant l'utilisation du cache
		$recordObject = RSNContactsPanels_Record_Model::getInstanceById($id, 'RSNContactsPanels');
		Vtiger_Cache::set('rsncontactspanel', implode('/', $path), $recordObject);
		return $recordObject;
	}
	
	/**
	 * Function returns variables widget
	 * @param Vtiger_Request $request
	 * @return <type>
	 */
	function getRelatedVariables(Vtiger_Request $request = NULL) {
		//return parent::showRelatedRecords($request);
	
		$relatedModuleName = 'RSNPanelsVariables';

		$pagingModel = new Vtiger_Paging_Model();
		if($request){
			$pageNumber = $request->get('page');
			if(empty($pageNumber)) {
				$pageNumber = 1;
			}
			$pagingModel->set('page', $pageNumber);
			
			$limit = $request->get('limit');
			if(!empty($limit)) {
				$pagingModel->set('limit', $limit);
			}
		}
		$relationListView = Vtiger_RelationListView_Model::getInstance($this, $relatedModuleName);
		
		$relationListView->set('orderby', 'sequence');
		$relationListView->set('sortorder', 'ASC');
		/* TODO get variables where rsnpanelid plutot que related list */
		return $relationListView->getEntries($pagingModel);
	}
	
	/**
	 * Liste des variables présentées comme des field models
	 *
	 */
	public function getVariablesRecordModels(){
		$existingVariables = $this->getModule()->getRelatedPanelVariables($this);
		return $existingVariables;
	}
	
	///**
	// * Liste des variables présentées comme des field models
	// *
	// */
	//public function getVariablesAsFields(){
	//	$existingVariables = $this->getModule()->getRelatedPanelVariables($this);
	//	$fields = array();
	//	foreach($existingVariables as $variable){
	//		$fields[$variable->getId] = $variable->getQueryField($variable->getName());
	//	}
	//	var_dump($fields);
	//	die();
	//	return $fields;
	//}

	/**
	 * Function to get List of RSNContactsPanels records
	 * @return <Array> List of record models <RSNContactsPanels>
	 */
	public static function getAllForCustomViewEditor() {
		$db = PearDatabase::getInstance();
		$moduleModel = Vtiger_Module_Model::getInstance('RSNContactsPanels');

		//TODO get only the ones visible for current user
		
		$result = $db->pquery('SELECT vtiger_crmentity.crmid as id, vtiger_crmentity.*, vtiger_rsncontactspanels.*
				      FROM vtiger_rsncontactspanels
				      JOIN vtiger_crmentity
					ON vtiger_rsncontactspanels.rsncontactspanelsid = vtiger_crmentity.crmid
				      WHERE vtiger_crmentity.deleted = 0', array());
		$numOfRows = $db->num_rows($result);

		$recordModelsList = array();
		for ($i=0; $i<$numOfRows; $i++) {
			$rowData = $db->query_result_rowdata($result, $i);
			$recordModel = new self();
			$recordModelsList[$rowData['crmid']] = $recordModel->setData($rowData)->setModule($moduleModel);
		}
		return $recordModelsList;
	}
}
