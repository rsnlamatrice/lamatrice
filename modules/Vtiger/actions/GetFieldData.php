<?php
/*+***********************************************************************************
 * AV150415
 *************************************************************************************/

class Vtiger_GetFieldData_Action extends Vtiger_Action_Controller {

	function checkPermission(Vtiger_Request $request) {
		return;
	}

	public function process(Vtiger_Request $request) {
		$searchValue 			= $request->get('search_value');
		$originalSearchField 	= $request->get('search_field');
		$relatedModule 			= $request->get('module');
		$searchFieldId 			= $request->get('search_field_id');
		$relatedFields 			= $this->getRelatedFieldsData($searchFieldId);
		$neededFields 			= explode(':', $request->get('needed_fields'));
		$searchFieldDBInfo 		= $this->getFieldDatabaseInfo($searchFieldId, $originalSearchField);
		$joinedTables 			= array($searchFieldDBInfo['tablename']);

		$db = PearDatabase::getInstance();
		$query = 'SELECT '. $searchFieldDBInfo['tablename'] . '.' .$searchFieldDBInfo['fieldname'];

		foreach ($neededFields as $neededField) {
			if ($neededField) {
				$query .= ', ' . Vtiger_Util_Helper::getRelatedFieldName($neededField);
			}
		}

		$query .= ' FROM ' . $searchFieldDBInfo['tablename'];

		foreach ($relatedFields as $relatedField) {
			if (!in_array($relatedField['relation'][3], $joinedTables)) {
				$query .= " JOIN ". $relatedField['relation'][3] . " ON " . $searchFieldDBInfo['tablename'] . "." . $relatedField['relation'][1] . "=" . $relatedField['relation'][3] . "." . $relatedField['relation'][2];
				array_push($joinedTables, $relatedField['relation'][3]);
			}
		}

		$query .= ' WHERE '.$searchFieldDBInfo['fieldname'].' LIKE "'.$searchValue.'%"';

		foreach ($relatedFields as $relatedField) {
			if ($request->get($relatedField['fieldname'])) {
				$query .= " AND ". $relatedField['relation'][3] . "." . $relatedField['dbinfo']['fieldname'] . " LIKE '" . $request->get($relatedField['fieldname']) . "%'";
			}
		}

		$result = $db->pquery($query, array());
        $picklistValues = array();
        $num_rows = $db->num_rows($result);
        for($i=0; $i<$num_rows; $i++) {
			$picklistValues[] = array($originalSearchField => decode_html($db->query_result($result,$i,$searchFieldDBInfo['fieldname'])));
            foreach ($neededFields as $neededField) {
            	if ($neededField) {
					$picklistValues[$i][$neededField] = decode_html($db->query_result($result,$i,Vtiger_Util_Helper::getRelatedFieldName($neededField)));
				}
			}
        }

		$response = new Vtiger_Response();
		$response->setResult($picklistValues);
		$response->emit();
	}

	public function getRelatedFieldsData($fieldId, $fieldname) {
		$db = PearDatabase::getInstance();
		$query = "SELECT a.related_field, a.relation, f.fieldname FROM vtiger_fielduirelation a";
		$query .= " JOIN vtiger_field f ON a.related_field=f.fieldid";
		$query .= " WHERE field =" . $fieldId;
		$result = $db->pquery($query, array());
		$values = array();
        $num_rows = $db->num_rows($result);

        for($i=0; $i<$num_rows; $i++) {
        	$fieldId = decode_html($db->query_result($result,$i, 'related_field'));
        	$fieldName = decode_html($db->query_result($result,$i, 'fieldname'));
            $values[] = array(
            	'fieldid' => $fieldId,
            	'fieldname' => $fieldName,
            	'relation' => explode(":", decode_html($db->query_result($result,$i, 'relation'))),
            	'dbinfo' => $this->getFieldDatabaseInfo($fieldId, $fieldName));
        }

		return $values;
	}

	public function getFieldDatabaseInfo($fieldId, $fieldName) {
		$db = PearDatabase::getInstance();
		$query = "SELECT relation FROM vtiger_fielduirelation";
		$query .= " WHERE field =" . $fieldId . " AND related_field IS NULL";

		$result = $db->pquery($query, array());
		$values = array();
        if ($db->num_rows($result) > 0) {
			$info = explode(':', decode_html($db->query_result($result,0, 'relation')));

			return array(
				'tablename' => $info[0],
				'fieldname' => $info[1]);
        }

		return array(
			'tablename' => 'vtiger_' . Vtiger_Util_Helper::getRelatedFieldName($fieldName),
			'fieldname' => Vtiger_Util_Helper::getRelatedFieldName($fieldName));
	}
}