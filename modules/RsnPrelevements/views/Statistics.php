<?php
/*+**********************************************************************************
 * 
 ************************************************************************************/

class RsnPrelevements_Statistics_View extends Vtiger_Index_View {

	public function process(Vtiger_Request $request) {
		$moduleName = $request->getModule();
		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);
		
		
		$viewer = $this->getViewer($request);

		$dateVir = $moduleModel->getNextDateToGenerateVirnts();
		$periodicites = $moduleModel->getPeriodicitesToGenerateVirnts($dateVir);

		$prelevementsActifs = $this->getPrelevementsActifs($moduleModel, $periodicites, $dateVir);
		
		$viewer->assign('DATE_PRELEVEMENTS', $dateVir);
		$viewer->assign('PERIODICITES', $periodicites);
		$viewer->assign('PRELEVEMENTS_ACTIFS', $prelevementsActifs);
		$viewer->assign('FIRST_RECUR_LIST', array('first', 'recur', 'total'));
	
		echo $viewer->view('Statistics.tpl',$moduleName,true);
		
	}
	
	function getPrelevementsActifs($moduleModel, $periodicitesDateVir, $dateVir){
		$db = PearDatabase::getInstance();
		
		$params = array();
		
		$prelvtypes = $moduleModel->getTypesPrelvntsToGenerateVirnts();
		
		$query = 'SELECT vtiger_rsnprelevements.periodicite
			, IF(vtiger_rsnprelevements.dejapreleve IS NULL OR vtiger_rsnprelevements.dejapreleve = \'0000-00-00\', 0,
				IF(dejapreleve 
					BETWEEN DATE_SUB( ?, INTERVAL 7 DAY)
					AND DATE_ADD( ?, INTERVAL 7 DAY), 0, 1)
				) AS is_recur
			, COUNT(*) AS nombre
			, SUM(montant) AS montant
			, IFNULL(vtiger_periodicite.sortorderid, 999) AS sortorderid
			FROM vtiger_rsnprelevements
			JOIN vtiger_crmentity
				ON vtiger_rsnprelevements.rsnprelevementsid = vtiger_crmentity.crmid
			LEFT JOIN vtiger_periodicite
				ON vtiger_periodicite.periodicite = vtiger_rsnprelevements.periodicite
			WHERE vtiger_crmentity.deleted  = 0
			AND vtiger_rsnprelevements.etat = 0
			AND vtiger_rsnprelevements.prelvtype IN (' . generateQuestionMarks($prelvtypes) . ')
			GROUP BY is_recur, vtiger_rsnprelevements.periodicite
			ORDER BY sortorderid, vtiger_rsnprelevements.periodicite
			';
		$params[] = $dateVir->format('Y-m-d');
		$params[] = $dateVir->format('Y-m-d');
		$params = array_merge($params, $prelvtypes);
		$result = $db->pquery($query, $params);
		if(!$result){
			$db->echoError($query);
			return;
		}
		$rows = array();
		$totals = array('first'=>array('nombre'=>0, 'montant'=>0), 'recur'=>array('nombre'=>0, 'montant'=>0), );
		$periodicitesDateVirTotals = array('first'=>array('nombre'=>0, 'montant'=>0), 'recur'=>array('nombre'=>0, 'montant'=>0), );
		$nbRows = $db->num_rows($result);
		if(!$nbRows)
			return false;
		for($nRow = 0; $nRow < $nbRows; $nRow++){
			$row = $db->fetchByAssoc($result, $nRow);
			$periodicite = $row['periodicite'];
			$first_recur = $row['is_recur'] ? 'recur' : 'first';
			if(!$rows[$periodicite])
				$rows[$periodicite] = array('first'=>false, 'recur'=>false);
			$rows[$periodicite][$first_recur] = $row;
			$totals[$first_recur]['nombre'] += $row['nombre'];
			$totals[$first_recur]['montant'] += $row['montant'];
			if(in_array($periodicite, $periodicitesDateVir)){
				$periodicitesDateVirTotals[$first_recur]['nombre'] += $row['nombre'];
				$periodicitesDateVirTotals[$first_recur]['montant'] += $row['montant'];
			}
			
			//if(preg_match('/\d$/', $periodicite)){
			//	$periodicite = preg_replace('/\d+$/', '', $periodicite);
			//	if(!$rows[$periodicite])
			//		$rows[$periodicite] = array('first'=>0, 'recur'=>0, )
			//	$rows[$periodicite][$row['is_first'] ? 'first' : 'recur'] = $row;
			//}
		}
		$rows['Totaux'] = $totals;
		$rows['Totaux au '.$dateVir->format('d/m/Y')] = $periodicitesDateVirTotals;
		
		foreach($rows as $rowKey=>&$row){
			$row['total'] = array('nombre'=>0, 'montant'=>0);
			foreach(array('first', 'recur') as $first_recur){
				$row['total']['nombre'] = $row[$first_recur]['nombre'];
				$row['total']['montant'] = $row[$first_recur]['montant'];
			}
		}
		
		return $rows;
	}
}