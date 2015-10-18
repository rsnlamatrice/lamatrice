<?php

class RSNSQLQueryExecutionController {
	
	static $_stack = array();
	
	public static function getCallKey($query){
		return '_' . $query->getId()
			//. (count(self::$_stack) ? '@' . $_stack[count($_stack) - 1] : '')
		;
	}
	
	public static function stack($query){
		$key = self::getCallKey($query);
		if(isset(self::$_stack[$key])){
			echo '<code># Récursivité infinie pour "' . $query->get('name') . '" à <pre>' . print_r(self::$_stack, true) . '</pre> #</code>';
			return FALSE;
		}
		self::$_stack[$key] = $query->get('name');
		return TRUE;
	}
	public static function unstack($query){
		if(count(self::$_stack)){
			unset(self::$_stack[self::getCallKey($query)]);
		}
	}
}
/**
 * Vtiger Entity Record Model Class
 */
class RSNSQLQueries_Record_Model extends Vtiger_Record_Model {

	/**
	 * retourne la requête d'après le champ query
	 *
	 */
	public function getQuery(){
		return str_replace(array('&lt;', '&gt;','&#039;')
				   , array('<', '>', "'")
				   , $this->get('query'));
	}
	
	public function updateQueryStringVariable($currentVariable, $newVariable) {
		//TMP !!
		$query = $this->get('query');
		$this->set('doNotUpdateVariables', true);

		if (preg_match('/\[\[(\?{1,2}|\=|QUERY\s|IN\s)\s?' . $currentVariable->get('name') . '((\s|\|)(.*))?\]\]/', $query)) {
			$query = preg_replace('/\[\[(\?{1,2}|\=|QUERY\s|IN\s)\s?' . $currentVariable->get('name') . '((\s|\|)(.*))?\]\]/', '[[$1 ' . trim($newVariable->get('name')) . ' $2]]', $query);//tmp espace en trop !!!
			$this->set('query', $query);
			$this->set('mode', 'edit');
			$this->save();
		} else {
			//echo 'not found !!';
		}
	}
	
// 	/**
// 	 * Traitement des variables imbriquées dans la requête
// 	 * 	transforme les éléments de la forme [[Title | field/type | defaultValue ]]
// 	 * 	en RSNQueriesVariables
// 	 * @param Boolean $associative : returns an associative array
// 	 *
// 	 * format :
// 	 * 	[[<operation> <name> | {field="<field>", default="<default value>"}]]
// 	 * opérateurs :
// 	 * 	?	valeur passée par paramètre. Le SQL comporte un ?.
// 	 * 	??	opérateur inséré dans le SQL et valeur passée par paramètre. Le SQL comporte un ?.
// 	 * 	=	valeur insérée dans le SQL.
// 	 * 	IN	série insérée dans le sql. Les paramètres sont ajoutés.
// 	 * 	QUERY	la requête d'une sous-query est inséré dans le SQL. Les paramètres sont ajoutés.
// 	 *
// 	 * QUERY
// 	 * 	les paramètres suivants le <fieldid> peuvent être de la forme <var name>:=<set prior value>
// 	 * 	[[QUERY NPAI Ok | Adresses/NPAI Ok | NPAI maxi := 2]]
// 	 */
	public function getVariablesFromQuery($associative = FALSE) {//tmp -> the syntaxe may change...?
		$query = $this->getQuery();
		$variables = array();
		if(preg_match_all('/\[\[(?<op>\?{1,2}|\=|QUERY\s|IN\s)(?<var>(?<!\]\])([^|\]]*))(\s*\|\s*(?<params>\{([^\}]*)\}))?\s?\]\]/', $query, $variables)) {
			$variablesData = array();
			$varQuantity = sizeof($variables['var']);
			for($i = 0; $i < $varQuantity; ++$i){
				$operation = $variables['op'][$i];
				$variableName = $variables['var'][$i];
				$parameters = json_decode(decode_html($variables['params'][$i]), true);

				// tmp auto merge variableData and parametersarray -> usefull in case of new parameter !
				$variableData = array(
					'operation' => trim($operation),
					'name' => trim($variableName),
					'field' => trim($parameters['field']),//tmp name !!
					'defaultvalue' => trim($parameters['default']),//tmp name !!
					'sequence' => $i + 1,
				);
				if ($associative) {
					if(!isset($variablesData[$variableData['name']]))
						$variablesData[$variableData['name']] = $variableData;
				}
				else {
					$variablesData[] = $variableData;
				}
			}
			return $variablesData;
		}

		return array();
	}

	/**
	 * Traitement des variables imbriques dans la requte
	 * 	transforme les lments de la forme [[Title | field/type | defaultValue ]]
	 * 	en RSNQueriesVariables
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function checkVariables() {
		if (!$this->get('doNotUpdateVariables')) {
			$this->variablesData = $this->getVariablesFromQuery(true);
			$this->cleanQueryStringVariables();
		}
	}

	public function cleanQueryStringVariables() {
		$query0 = $query = $this->get('query');
		foreach ($this->variablesData as $variableData) {
			//tmp use real type (query, ?, ...)
			$regex = '/\[\[(\?{1,2}|\=|QUERY\s|IN\s)\s?' . $variableData['name'] . '((\s.|\|)(?!\[\[))?\]\]/';
			$query = preg_replace($regex, '[[$1 ' . trim($variableData['name']) . ' ]]', $query);
			//var_dump('name', $variableData['name'], $regex, $query);
		}
//exit;
//var_dump($query0, $query);
//		die();
		$this->set('query', $query);
	}

	/**
	 * Traitement des variables imbriques dans la requte
	 * 	transforme les lments de la forme [[Title | field/type | defaultValue ]]
	 * 	en RSNQueriesVariables
	 * @param Vtiger_Record_Model $recordModel
	 */
	public function saveVariables() {
		if (!$this->get('doNotUpdateVariables')) {
			$query = $this->get('query');
			$strVariables = array();
			//if(preg_match_all('/\[\[(?<op>\?|\=|IN\s)(?<var>(?<!\]\]).*?(?=\]\]))\]\]/', $query, $strVariables)){
			$variablesData =  ($this->variablesData) ? $this->variablesData : $this->getVariablesFromQuery(true);
			//var_dump($variablesData);

			//if($variablesData){
				//Variables existantes
				$existingVariables = $this->getRelatedVariables();


				$newVariables = array();
				$changedVariables = array();
				//contrôle si la varaible est connue ou si une propriété change
				foreach($existingVariables as $existingVariable) {
					$name = $existingVariable->get('name');
					if(isset($variablesData[$name])){
						$strVar = $variablesData[$name];
						//var_dump("existe", $name);
						//existe
						//corrige disabled
						if($existingVariable->get('disabled') == '1'){
							$existingVariable->set('disabled', '0');
							$changedVariables[] = $existingVariable;
						}
						if($existingVariable->get('sequence') != $strVar['sequence']){
							$existingVariable->set('sequence', $strVar['sequence']);
							$changedVariables[] = $existingVariable;
						}
						if($existingVariable->get('type') != $strVar['operation']) {//tmp rename type to operator !!
							$existingVariable->set('type', $strVar['operation']);
							$changedVariables[] = $existingVariable;
						}
						if($strVar['field'] && $existingVariable->get('referencefield') != $strVar['field']){//tmp rename reference field to type !!
							$existingVariable->set('referencefield', $strVar['field']);
							$changedVariables[] = $existingVariable;
						}
						if($strVar['defaultvalue'] && $existingVariable->get('defaultvalue') != $strVar['defaultvalue']){
							$existingVariable->set('defaultvalue', $strVar['defaultvalue']);
							$changedVariables[] = $existingVariable;
						}
						/*if($existingVariable->get('rsnvariabletype') != $strVar['operation']){ // TMP
							$existingVariable->set('rsnvariabletype', $strVar['operation']);
							$changedVariables[$existingVariable->getId()] = $existingVariable;
						}*/
					}
					else {
						if($existingVariable->get('disabled') != '1'){
							$existingVariable->set('disabled', '1');
							$existingVariable->set('sequence', null);
							$changedVariables[] = $existingVariable;
						}
					}
					$variablesData[$name]['_exists_'] = $existingVariable;
				}

				foreach($variablesData as $strVarName => $strVar) {
					if(!isset($strVar['_exists_'])) {
						//nouvelle variable
						//var_dump("nouvelle variable", $strVarName);
						$newVariable = Vtiger_Record_Model::getCleanInstance('RSNQueriesVariables');
						$newVariable->set('name', $strVarName);
						$newVariable->set('variablelabel', $strVarName);
						//$newVariable->set('rsnqueryid', $this->getId());
						$newVariable->set('defaultvalue', $strVar['defaultvalue']);
						$newVariable->set('disabled', 0);
						//$newVariable->set('fieldid', $strVar['field']);
						$newVariable->set('referencefield', $strVar['field']);
						$newVariable->set('sequence', $strVar['sequence']);
						//$newVariable->set('rsnvariableoperator', 'égal');
						$newVariable->set('type', $strVar['operation']);//tmp
						$newVariable->save();
						
						//add relation
						$relationModel = Vtiger_Relation_Model::getInstance($this->getModule(), $newVariable->getModule());
						$relationModel->addRelation($this->getId(), $newVariable->getId());
					}
				}

				// variables modifiées
				foreach($changedVariables as $existingVariable){
					$existingVariable->set('mode', 'edit');
					$existingVariable->set('doNotUpdateQueryString', true);
					$existingVariable->save();
					
				}
			//}
		}
		
	}

	/**
	 * Variables lies
	 */
	public function getRelatedVariables(Vtiger_Request $request = NULL) {
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

		$relatedModuleName = 'RSNQueriesVariables';
		$relationListView = Vtiger_RelationListView_Model::getInstance($this, $relatedModuleName);
		$relationListView->set('orderby', 'sequence');
		$relationListView->set('sortorder', 'ASC');
		$variables = $relationListView->getEntries($pagingModel);// Warning, take care of the limite of the pagin model ....

		return $variables;
	}

	public function getRelatedVariablesNames() {
		
		$result = array();		
		
		$relatedVariables = $this->getRelatedVariables();

		foreach ($relatedVariables as $key => $variable) {
			$result[] = $variable->get('name');
		}

		return $result;
	}

	public function getRelatedVariableDefaultValue($variableName) {
		// TODO faster and cache
		$relatedVariables = $this->getRelatedVariables();
		
		foreach ($relatedVariables as $variable) {
			if($variableName == $variable->get('name'))
				return $variable->get('defaultvalue');
		}

		return null;
	}

	public function getExecutionQuery($paramValues) {//tmp !! 
		$sql = $this->getQuery();
		$regex_end = '\s*(\|[^\]|]*)*\]\]/';
		$SQLParam = array();
		$queryVariables = $this->getVariablesFromQuery(false);// array() extrait de la requête
		foreach($queryVariables as $queryVariable){
			$paramName = $queryVariable['name'];
			if(!array_key_exists($paramName, $paramValues)){
				$paramValue = $this->getRelatedVariableDefaultValue($paramName);
			}
			else
				$paramValue = $paramValues[$paramName];
			//var_dump(__FILE__.'.getExecutionQuery()', $paramName, $paramValue);
			if($paramName === 'crmid'
			&& is_string($paramValue)
			&& !is_numeric($paramValue)){
				if(strcasecmp(substr($paramValue, 0, 7), 'SELECT ') === 0){
					$replacement = $paramValue;
				}
				elseif( strpos($paramValue, ',') !== FALSE){
					$paramValue = explode(',', $paramValue);
					$replacement = generateQuestionMarks($paramValue);
					$SQLParam = array_merge($SQLParam, $paramValue);
				}
			}
			elseif(is_array($paramValue)){
				$replacement = generateQuestionMarks($paramValue);
				$SQLParam = array_merge($SQLParam, $paramValue);
			}
			else{
				$replacement = '?';
				$SQLParam[] = $paramValue;
			}
			$sql = preg_replace('/\[\[\?\s*' . preg_quote($paramName) . $regex_end, $replacement, $sql);
		}
		/*foreach($paramValues as $paramName => $paramValue) {
			$sql = preg_replace('/\[\[\?\s*' . preg_quote($paramName) . $regex_end, '?', $sql);
			$SQLParam[] = $paramValue;
		}*/
		return array('sql' => $sql,
				'params' => $SQLParam);
	}
	
// 	public static function queryParams_encode($array){
// 		return json_encode($array);
// 	}
	
// 	public static function queryParams_decode($string, &$variables = FALSE){
// 		if(!is_array($variables))
// 			$variables = array();
// 		if($string){
// 			$array = json_decode(decode_html($string));
// 			if(!$array){
// 				var_dump($string);
// 			}
// 			else
// 				foreach($array as $variable)
// 					$variables[] = array(
// 						'name' => $variable[0],
// 						'value' => $variable[1]
// 					);
// 		}
// 		return $variables;
// 	}
	
	/** TMP !!!!
	 * Function returns query execution result widget
	 * @param array &$params : current sql query parameters array
	 * @param array &$paramsDetails : for each parameter, add details
	 * @param array &$paramsPriorValues : valeurs de variables à affecter
	 * @return <type>
	 *
	 * TODO
	 */
	function getExecutionSQL(&$params = FALSE, &$paramsDetails = FALSE, &$paramsPriorValues = FALSE, &$callStask = FALSE) {
		if(!RSNSQLQueryExecutionController::stack($this)) //do not forget to unstack
			return null;
		if(!is_array($params))
			$params = array();
		if(!is_array($paramsDetails))
			$paramsDetails = array();
		
		$sql = $this->getQuery();
		$queryVariables = $this->getVariablesFromQuery(false);// array() extrait de la requête
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
		
		
		$variablesPrevValues = array();
					
		//affectation de valeur aux variables
		// provient de la syntaxe [[QUERY <subquery> | <domain>/<subqueryname> | Nom_Var:=Value | Nom_Var2 := Value]] // TMP Syntaxe !!!!!!
		if(is_array($paramsPriorValues)){
			$followParamsPriorValues = array();
			$thisInstanceName = $this->get('instanceName');
			foreach($paramsPriorValues as $paramPriorValue){
				//le nom de la variable contient un /
				if(strpos($paramPriorValue['name'], '/')){
					//Destiné à une sous-requête
					$followParamPriorValue = array_merge(array(), $paramPriorValue, array(
						'path' => $thisPath,
						'parent' => substr($paramPriorValue['name'], 0, strpos($paramPriorValue['name'], '/')),
						'name' => substr($paramPriorValue['name'], strpos($paramPriorValue['name'], '/')+1),
					));
					$followParamsPriorValues[] = $followParamPriorValue;
				}
				//le nom de la variable est connu
				elseif(isset($variables[$paramPriorValue['name']])){
					$variable = $variables[$paramPriorValue['name']];
					//valeur précédente
					if(!isset($variablesPrevValues[$paramPriorValue['name']]))
						$variablesPrevValues[$paramPriorValue['name']] = $variable->get('defaultvalue'); 
					//affectation de la valeur pour son usage ci-dessous
					$variable
						//valeur à utiliser
						->set('defaultvalue', $paramPriorValue['value'])
						// Chemin des appels
						//TODO path ne fonctionne pas, l'objet est partagé, c'est donc purement cumulatif
						->set('path', //utilisé pour le commentaire
							$thisPath
							. '/')
					;
					//var_dump($paramPriorValue->name, $paramPriorValue->value);
				}
				else
					var_dump('<br><code># paramètre de requête "' . $paramPriorValue['name'] . '" introuvable #<code><br>');
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
				$paramsDetails[] = array(
					'name'=>$queryVariable['name'],
					'variable'=> null,
					'value'=> $value
				);
			}
			else {
				$variable = $variables[$variableName];
				$value = decode_html( $variable->get('defaultvalue') );
				//Variable déjà traitée
				if(!isset($variablesId[$variable->getId()])){
					if($queryVariable['name'] === 'crmid'
					&& is_string($value)
					&& !is_numeric($value)){
						if(strcasecmp(substr($value, 0, 7), 'SELECT ') === 0){
							$queryVariable['operation'] = '=/?';
						}
						elseif(strpos($value, ',') !== false){
							$value = explode(',', $value);
						}
						elseif(trim($value) === '*'){
							$queryVariable['operation'] = '=/?';
							$value = $this->getSelectAllFromModuleQuery();
						}
					}
					$paramsDetails[] = array(
						'operation'=> $queryVariable['operation'],
						'name'=> $queryVariable['name'],
						'variable'=> $variable,
						'value'=> $value,
						'depth'=> $thisDepth,
					);
					$variablesId[$variable->getId()] = true;
				}
			}
			// commentaire en préfixe
			$comment = ' /*[[' . str_replace('?', '!', $queryVariable['operation'] . ' ' . $variable->get('path') . $variableName) . ']]*/ ';
			
			//TODO le regex de fin s'arrête dès le 1er ] existant : faire pour ]]
			//$regex_end = '\s*(\|[^\]|]*)*\]\]/'; cf plus haut
			
			//selon opération
			switch(strtoupper($queryVariable['operation'])){
			case '?':
				if(is_array($value)){
					$params = array_merge($params, $value);
					$questionMark = generateQuestionMarks($value);
				}
				else{
					$params[] = $value;
					$questionMark = '?';
				}
				/* injection dans le sql d'un paramètre */
				$sql = preg_replace('/\[\[\?\s*' . preg_quote($variableName) . $regex_end, 
						    $comment
						    . $questionMark, $sql);
				break;
			case '??':
				if($variable)
					$sqlOperation = $variable->getSQLOperation($value, $params);
				else
					$sqlOperation = '= ?';
				$params[] = $value;
				/* injection dans le sql d'un paramètre */
				$sql = preg_replace('/\[\[\?\?\s*' . preg_quote($variableName) . $regex_end,
						    $comment
						    . $sqlOperation, $sql);
				break;
			
			case 'IN':
				$value = explode(' |##| ', $value);
				$params = array_merge($params, $value);
				$value = generateQuestionMarks($value);
				/* injection dans le sql */
				$sql = preg_replace('/\[\[IN\s+' . preg_quote($variableName) . $regex_end, 
						    $comment
						    . ' IN (' . $value . ')', $sql);
				break;
			case 'QUERY':
				$instanceName = $variable->get('name');
				$queryName = $variable->get('fieldid');
				$subQueryRecord = self::getInstanceByNamePath($queryName, $this->get('rsncontactspanelsdomains'));// tmp !!!!
				if($subQueryRecord){
					/* affectation des valeurs passées par paramètres */
					$paramsPriorValues = self::queryParams_decode( $value );
					/* arguments suivis */
					if(isset($followParamsPriorValues)){
						foreach($followParamsPriorValues as $followParamPriorValue){
							//var_dump($followParamPriorValue, $instanceName);
							// contrôle le parent
							if($followParamPriorValue['parent'] == $instanceName){
								$paramsPriorValues[] = $followParamPriorValue;
							}
						}
						//var_dump($paramsPriorValues, $instanceName);
					}
					/* sous-requête d'exécution */
					$index = 0;
					//foreach($paramsPriorValues as $paramPriorValue){
					//	$paramPriorValue['prev_path'] = $paramPriorValue['path'];
					//	$paramPriorValue['path'] = ($paramPriorValue['path'] ? $paramPriorValue['path'] . '/' : '')
					//		. $thisPath
					//		. '/'
					//	;
					//	$paramsPriorValues[$index++] = $paramPriorValue;
					//}
					//getExecutionSQL
					$value = $subQueryRecord->getExecutionSQL($params, $paramsDetails, $paramsPriorValues, $callStask);
					//restaure
					//foreach($paramsPriorValues as $paramsPriorValue){
					//	$paramsPriorValue['path'] = $paramsPriorValue['prev_path'];
					//}
				} else
					$value = '<code># Requête "'.$queryName.'" introuvable #</code>';
				$variableName = str_replace('/', '\\/', (preg_quote($variableName)));
				/* injection dans le sql */
				$sql = preg_replace('/\[\[QUERY\s+' . ($variableName) . $regex_end,
						    $comment . ' (
'							 . $value . '
'							 . '/* [[FIN DE ' . $variableName . ']] */
						    )'
						    , $sql);
				break;
			case '=':
				/* injection dans le sql */
				$sql = preg_replace('/\[\[\=\s*' . preg_quote($variableName) . $regex_end,
						    //$comment . pas de commentaire, car peut changer la requête
						    $value, $sql);
				break;
			
			case '=/?': 
				/* injection dans le sql à la place d'un paramètre avec l'opérateur ? */
				$sql = preg_replace('/\[\[\?\s*' . preg_quote($variableName) . $regex_end,
						    //$comment . pas de commentaire, car peut changer la sous requête
						    $value, $sql);
				break;
			default:
				/* injection dans le sql */
				$sql = preg_replace('/\[\[' . preg_quote($queryVariable['operation']) .'\s*' . preg_quote($variableName) . $regex_end,
						    $comment .
						    $value, $sql);
				break;
			}
			
		}
		//echo "<pre>$sql</pre>"; var_dump($params);
		// rétablit les valeurs d'orgine aux variables
		foreach($variablesPrevValues as $variableName => $variablePrevValue){
			$variables[$variableName]->set('defaultvalue', $variablePrevValue);
		}
		RSNSQLQueryExecutionController::unstack($this);
		return $sql;
	}
	
	function getSelectAllFromModuleQuery(){
		$relatedModuleName = $this->get('relmodule');
		return 'SELECT vtiger_crmentity.crmid
			FROM vtiger_crmentity
			WHERE vtiger_crmentity.deleted = 0
			AND vtiger_crmentity.setype = \''.$relatedModuleName.'\'';
	}
	
// 	/** ED150507
// 	 * Function returns query execution result widget
// 	 * Replaces question mark with param value
// 	 * @return <string>
// 	 */
// 	function getExecutionSQLWithIntegratedParams(){
// 		$params = array();
// 		$paramsDetails = array();
// 		$sql = $this->getExecutionSQL($params, $paramsDetails);
// 		if(!$sql)
// 			return $sql;
// 		for($i = 0; $i < count($params); $i++){
// 			// replace /*[[operator xxx]]*/ ? with /*[[operator xxx]]*/ 'value'
// 			$sql = preg_replace('/(\/\*\[\[.*'.preg_quote($paramsDetails[$i]['name']) . '.*\]\]\*\/\s)\?/'
// 					    , '$1\'' . str_replace('\'', '\\\'', $params[$i]) . '\''
// 					    , $sql);
// 		}
// 		return $sql;
// 	}
	
// 	/**
// 	 * Fonction qui retourne une instance de record d'après son chemin.
// 	 * Un chemin est la combinaison du domaine et du nom
// 	 */
// 	public static function getInstanceByNamePath($path, $root = ''){
// 		if(is_string($path))
// 			$path = explode('/', $path);
// 		if(count($path) == 1){
// 			$path[] = $path[0];
// 			$path[0] = $root;
// 		}
// 		$recordObject = Vtiger_Cache::get('rsncontactspanel', implode('/', $path));
// 		if($recordObject)
// 			return $recordObject;
		
// 		$sql = "SELECT rsncontactspanelsid
// 			FROM vtiger_rsncontactspanels
// 			WHERE rsncontactspanelsdomains = ?
// 			AND name = ?
// 			LIMIT 1";
// 		$db = PearDatabase::getInstance();
// 		//$db->setDebug(true);
// 		$result = $db->pquery($sql, $path);
// 		if($db->num_rows($result) == 0)
// 			return null;
// 		$id = $db->query_result($result,0,0);
// 		//TODO éviter une second requête sql sur le même enregistrement, tout en gardant l'utilisation du cache
// 		$recordObject = RSNContactsPanels_Record_Model::getInstanceById($id, 'RSNContactsPanels');
// 		Vtiger_Cache::set('rsncontactspanel', implode('/', $path), $recordObject);
// 		return $recordObject;
// 	}
	

// 	/**
// 	 * Function to get List of RSNContactsPanels records
// 	 * @return <Array> List of record models <RSNContactsPanels>
// 	 */
// 	public static function getAllForCustomViewEditor() {
// 		$db = PearDatabase::getInstance();
// 		$moduleModel = Vtiger_Module_Model::getInstance('RSNContactsPanels');

// 		//TODO get only the ones visible for current user
		
// 		$result = $db->pquery('SELECT vtiger_crmentity.crmid as id, vtiger_crmentity.*, vtiger_rsncontactspanels.*
// 				      FROM vtiger_rsncontactspanels
// 				      JOIN vtiger_crmentity
// 					ON vtiger_rsncontactspanels.rsncontactspanelsid = vtiger_crmentity.crmid
// 				      WHERE vtiger_crmentity.deleted = 0', array());
// 		$numOfRows = $db->num_rows($result);

// 		$recordModelsList = array();
// 		for ($i=0; $i<$numOfRows; $i++) {
// 			$rowData = $db->query_result_rowdata($result, $i);
// 			$recordModel = new self();
// 			$recordModelsList[$rowData['crmid']] = $recordModel->setData($rowData)->setModule($moduleModel);
// 		}
// 		return $recordModelsList;
// 	}

	/**
	 * Returns the fields returned by query
	 *
	 */
	public function getQueryResultFieldsDefinition(){
		$fields = array();
		$params = array();
		$sql = $this->getExecutionSQL($params);
		$sql = str_replace('?', 'NULL', $sql);
		$sql = str_replace(' WHERE ', ' WHERE 0 = 1 AND ', $sql);
		$sql .= ' LIMIT 0';
		$db = PearDatabase::getInstance();
		$result = $db->query($sql);
		if(!$result){
			echo "<pre>$sql</pre>";
			$db->echoError();
		}
		else{
			foreach($db->getFieldsDefinition($result) as $field){
				$fieldName = $field->name;
				while(array_key_exists($fieldName, $fields))
					$fieldName .= '+';
				$fields[$fieldName] = $field;
			}
		}
		return $fields;
	}
}
