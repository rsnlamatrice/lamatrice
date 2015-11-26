<?php

require_once('modules/RSN/helpers/Performance.php');

class Export_Utils_Helper {

	public static function getSourceList($moduleName) {//tmp use 
		$db = PearDatabase::getInstance();
		$return_values = array();

		array_push($return_values, array(
			id 			=> 0,
			module 		=> $moduleName,
			classname 	=> "default",
			exportname 	=> "Par default",
			description => "Export par default (toutes les informations).",
			exporttype 	=> "csv"
		));

		$params = array();
		$query = 'SELECT vtiger_crmentity.crmid/*, rpe.name*/, rpe.classname, rpe.exportname
				, rpe.description, rpe.exporttype
			FROM vtiger_rsnpreconfiguredexport rpe
			JOIN vtiger_crmentity ON rpe.rsnpreconfiguredexportid = vtiger_crmentity.crmid
			/*JOIN vtiger_tab tab ON rpe.tabid = tab.tabid*/
			WHERE rpe.relatedmodule = ?
			/*AND rpe.disabled = FALSE*/
			AND vtiger_crmentity.deleted = FALSE';

		$params[] = $moduleName;

		/*$query .= ' ORDER BY sortorderid';*/
		$result = $db->pquery($query, $params);
			
		$noOfRecords = $db->num_rows($result);
		for($i=0; $i<$noOfRecords; $i++) {
			$class = "toto";//self::getClassFromName($db->query_result($result, $i, 'class'));
			array_push($return_values, array(
				id 			=> $db->query_result($result, $i, 'crmid'),
				module 		=> $moduleName,
				classname 	=> $db->query_result($result, $i, 'classname'),
				exportname 	=> $db->query_result($result, $i, 'exportname'),
				description => $db->query_result($result, $i, 'description'),
				exporttype 	=> $db->query_result($result, $i, 'exporttype')
			));
		}
        return $return_values;
	}

	/**
	 * Method to get the export class name.
	 * @param  int $exportSourceId : the export source id.
	 * @return string: the export class name.
	 */
	public static function getExportClassName($exportSourceId) {
		$db = PearDatabase::getInstance();
		$return_values = array();
		//TMP ........
		$query = 	'SELECT classname FROM vtiger_rsnpreconfiguredexport rpe' .
					' JOIN vtiger_crmentity ON rpe.rsnpreconfiguredexportid = vtiger_crmentity.crmid' .
					' WHERE rpe.rsnpreconfiguredexportid = ?' .
					/*' AND rpe.disabled = FALSE' .*/
					' AND vtiger_crmentity.deleted = FALSE' .
					' LIMIT 1';

		$result = $db->pquery($query, array($importSourcesId));


		if ($db->num_rows($result) == 1) {
			return$db->query_result($result, $i, 'classname');
		}
		
        
        return '';
	}

	public static function getExportClassFromName($className, $moduleName) {
		try {
			$class = Vtiger_Loader::getComponentClassName('Export', $className, $moduleName);
			return new $class;
		} catch (Exception $e) {
			// class not found
		}

		try {
			$class = Vtiger_Loader::getComponentClassName('Action', $className, 'Export');
			return new $class;
		} catch (Exception $e) {
			//class not found
		}

		return null;
	}

	public static function getDefaultExport() {
        return 'default';
	}
}