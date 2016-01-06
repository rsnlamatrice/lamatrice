<?php
/*+***********************************************************************************
 * Exportation des règlements vers la compta
 *************************************************************************************/
if(1){ //0 pour debug
	define('ROWSEPAR', "\r\n");
	define('COLSEPAR', "\t");
} else {//debug
	define('ROWSEPAR', '<tr><td>');
	define('COLSEPAR', '<td>');
}

class RsnReglements_Send2Compta_View extends Vtiger_MassActionAjax_View {
	function __construct() {
		parent::__construct();
		$this->exposeMethod('showSend2ComptaForm');
		$this->exposeMethod('downloadSend2Compta');
		$this->exposeMethod('validateSend2Compta');
	}

	/**
	 * Function returns the mass edit form
	 * @param Vtiger_Request $request
	 */
	function showSend2ComptaForm (Vtiger_Request $request){
		$moduleName = $request->getModule();
		$cvId = $request->get('viewname');
		$selectedIds = $request->get('selected_ids');
		$excludedIds = $request->get('excluded_ids');

		$viewer = $this->getViewer($request);

		$moduleModel = Vtiger_Module_Model::getInstance($moduleName);

		if(!$this->validateRsnReglementsData($request))
			return false;
		
		$this->initSend2ComptaForm ($request);

		$viewer->assign('MODE', 'send2compta');
		$viewer->assign('MODULE', $moduleName);
		$viewer->assign('CVID', $cvId);
		$viewer->assign('MODULE_MODEL',$moduleModel); 
		$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
		$viewer->assign('MODULE_MODEL', $moduleModel);
	
		echo $viewer->view('Send2ComptaForm.tpl',$moduleName,true);
	}
	
	function initSend2ComptaForm (Vtiger_Request $request){
		
		$this->initSend2ComptaFormExportableRsnReglements ($request);
		$this->initSend2ComptaFormValidatableRsnReglements ($request);
	}
	
	function initSend2ComptaFormExportableRsnReglements(Vtiger_Request $request){
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		
		$controller = new Vtiger_MassSave_Action();
		
		$query = $controller->getRecordsQueryFromRequest($request);
		
		$excludeRsnReglementStatus = array('Created', 'Cancelled');
		
		//$query retourne autant de lignes que de lignes de réglements
		$query = 'SELECT DISTINCT rsnreglementsid
				FROM ('.$query.') _source_';
		$query = 'SELECT vtiger_rsnreglements.rsnreglementsid, vtiger_rsnreglements.amount as total
			FROM ('.$query.') _source_ids_
			JOIN vtiger_rsnreglements
				ON vtiger_rsnreglements.rsnreglementsid = _source_ids_.rsnreglementsid
			WHERE IFNULL(vtiger_rsnreglements.error, 0) = 0
			AND vtiger_rsnreglements.sent2compta IS NULL
			AND NOT vtiger_rsnreglements.reglementstatus IN ('.generateQuestionMarks($excludeRsnReglementStatus).')
			LIMIT 200 /*too long URL*/
		';
		
		$selectedIds = array();
		$total = 0;
		
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, $excludeRsnReglementStatus);
		if(!$result){
			$db->echoError();
			echo "<pre>$query</pre>";
			var_dump($params);
		}
		else {
			while($invoice = $db->fetch_row($result)){
				$total += $invoice['total'];
				$selectedIds[] = $invoice['rsnreglementsid'];
			}
			
			$viewer->assign('INVOICES_TOTAL', $total);
			$viewer->assign('INVOICES_COUNT', count($selectedIds));
		}
		$viewer->assign('SELECTED_IDS', $selectedIds);
	}
		
	//Compte les factures "En cours" qui pourraient passer en Validé
	function initSend2ComptaFormValidatableRsnReglements (Vtiger_Request $request){
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		
		$controller = new Vtiger_MassSave_Action();
		
		$query = $controller->getRecordsQueryFromRequest($request);
		
		$onlyRsnReglementStatus = array('Created');
		
		//$query retourne autant de lignes que de lignes de factures
		$query = 'SELECT DISTINCT rsnreglementsid
				FROM ('.$query.') _source_';
		$query = 'SELECT COUNT(*) AS `count`
			, SUM(vtiger_rsnreglements.amount) AS `amount`
			, MIN(vtiger_rsnreglements.dateregl) AS `datemini`
			, MAX(vtiger_rsnreglements.dateregl) AS `datemaxi`
			FROM ('.$query.') _source_ids_
			JOIN vtiger_rsnreglements
				ON vtiger_rsnreglements.rsnreglementsid = _source_ids_.rsnreglementsid
			WHERE IFNULL(vtiger_rsnreglements.error, 0) = 0
			AND vtiger_rsnreglements.reglementstatus IN ('.generateQuestionMarks($onlyRsnReglementStatus).')
		';
		
		$total = 0;
		
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, $onlyRsnReglementStatus);
		if(!$result){
			$db->echoError();
			echo "<pre>$query</pre>";
			var_dump($params);
		}
		else {
			$row = $db->fetch_row($result);
			
			$viewer->assign('VALIDATABLE_TOTAL', $row['amount']);
			$viewer->assign('VALIDATABLE_COUNT', $row['count']);
			$viewer->assign('VALIDATABLE_DATEMINI', $row['datemini']);
			$viewer->assign('VALIDATABLE_DATEMAXI', $row['datemaxi']);
		}
	}
		
	//Controle des données avant affichage
	function validateRsnReglementsData(Vtiger_Request $request){
		
		$controller = new Vtiger_MassSave_Action();
		$query = $controller->getRecordsQueryFromRequest($request);
		
		//Contrôle que tous les produits et services on
		$query = 'SELECT DISTINCT vtiger_rsnreglements.rsnmoderegl
			FROM ('.$query.') _source_ids_
			JOIN vtiger_rsnreglements
				ON vtiger_rsnreglements.rsnreglementsid = _source_ids_.rsnreglementsid
			LEFT JOIN vtiger_receivedmoderegl
				ON vtiger_receivedmoderegl.receivedmoderegl = vtiger_rsnreglements.rsnmoderegl
			WHERE IFNULL(vtiger_rsnreglements.error, 0) = 0
			AND (IFNULL(vtiger_receivedmoderegl.comptevente, "") = ""
				OR IFNULL(vtiger_receivedmoderegl.compteencaissement, "") = ""
				OR IFNULL(vtiger_receivedmoderegl.journalencaissement, "") = "")
			AND vtiger_rsnreglements.sent2compta IS NULL
		';
		$params = array();
		
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, $params);
		if(!$result){
			$db->echoError();
			echo "<pre>$query</pre>";
			return false;
		}
		if($db->getRowCount($result)) {
			echo "<ul><h4 style=\"color: red;\">Modes de règlement sans compte de vente ou d'encaissement !!!</h4>";
			while($product = $db->fetch_row($result)){
				echo "<li>".$product['rsnmoderegl']."</li>";
			}
			echo "</ul>";
			return false;
		}
		return true;
	}
	
	/******************************************
	 *
	 *	downloadSend2Compta
	 *
	 *	les données doivent être exportées triées par journal, sinon COgilog refuse l'import
	 ******************************************/
	
	var $exportBuffers = array();
	function addExportRow($journal, $row){
		if(!$this->exportBuffers)
			$this->exportBuffers[$journal] = array();
		$this->exportBuffers[$journal][] = $journal . COLSEPAR . $row;
	}
	function sanitizeExport($str){
		return str_replace(array(',', ';', "\t", "\r", "\n",), '-', $str);
	}
	function echoExportBuffer(){
		foreach($this->exportBuffers as $journal => $exportBuffer){
			echo implode(ROWSEPAR, $exportBuffer);
			echo ROWSEPAR;
		}
	}
	
	function downloadSend2Compta (Vtiger_Request $request, $setHeaders = true){
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
			
		$exclusiveRsnReglementStatus = array('Validated');
		
		$selectedIds = $request->get('selected_ids');
		$query = 'SELECT vtiger_rsnreglements.*
			, vtiger_account.accountname
			, "" AS codeaffaire
			, invoices.invoicesubject
			, invoices.invoice_no
			, invoices.invoicedate
			, invoices.invoicecount
			, invoices.invoicetypedossier
			, vtiger_account.accountname
			, vtiger_account.account_type
		';
		$query .= '
			FROM vtiger_rsnreglements
			LEFT JOIN vtiger_account
				ON vtiger_account.accountid = vtiger_rsnreglements.accountid
			JOIN vtiger_crmentity
				ON vtiger_crmentity.crmid = vtiger_rsnreglements.rsnreglementsid
		';
		// Factures associées.
		// il peut y avoir plusieurs vtiger_rsnreglements pour une facture, et inversement
		$query .= '
			LEFT JOIN (SELECT rsnreglementsid
				, GROUP_CONCAT(vtiger_invoice.subject) AS invoicesubject
				, GROUP_CONCAT(vtiger_invoice.invoice_no) AS invoice_no
				, GROUP_CONCAT(vtiger_invoice.invoicedate) AS invoicedate
				, GROUP_CONCAT(vtiger_invoicecf.typedossier) AS invoicetypedossier
				, COUNT(vtiger_invoice.invoiceid) AS invoicecount
				FROM vtiger_invoice
				JOIN vtiger_invoicecf
					ON vtiger_invoicecf.invoiceid = vtiger_invoice.invoiceid
				JOIN vtiger_crmentity
					ON vtiger_crmentity.crmid = vtiger_invoice.invoiceid
				JOIN vtiger_crmentityrel
					ON vtiger_crmentityrel.crmid = vtiger_invoice.invoiceid
					OR vtiger_crmentityrel.relcrmid = vtiger_invoice.invoiceid
				JOIN vtiger_rsnreglements
					ON vtiger_crmentityrel.crmid = vtiger_rsnreglements.rsnreglementsid
					OR vtiger_crmentityrel.relcrmid = vtiger_rsnreglements.rsnreglementsid
				WHERE vtiger_crmentity.deleted = 0
				AND vtiger_invoice.invoicestatus != "Cancelled"
				AND vtiger_rsnreglements.rsnreglementsid IN ('. generateQuestionMarks( $selectedIds ) . ')
				GROUP BY rsnreglementsid
			) invoices
				ON vtiger_rsnreglements.rsnreglementsid = invoices.rsnreglementsid';
			
		$query .= '
			WHERE vtiger_crmentity.deleted = 0
			AND vtiger_rsnreglements.rsnreglementsid IN ('. generateQuestionMarks( $selectedIds ) . ')
			AND vtiger_rsnreglements.sent2compta IS NULL
			AND vtiger_rsnreglements.amount <> 0
			AND vtiger_rsnreglements.reglementstatus IN ('.generateQuestionMarks($exclusiveRsnReglementStatus).')
		';
		
		if(FALSE){
			$query .= " AND vtiger_rsnreglements.numpiece = '6140;CP;26-10-15-22:44:39'";
		
		}
			
		$query .= '
			ORDER BY vtiger_rsnreglements.dateregl ASC
			, vtiger_crmentity.createdtime
		';
		$params = $selectedIds; //invoices
		$params = array_merge($params, $selectedIds);//reglements
		$params = array_merge($params, $exclusiveRsnReglementStatus);
		
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, $params);
		if(!$result){
			$db->echoError();
			echo "<pre>$query</pre>";
			var_dump($params);
			$response = new Vtiger_Response();
			$response->setError('Erreur de requête');
			$response->emit();
		}
		else {
			
			$isDebug = COLSEPAR;
			$isDebug = $isDebug[0] === '<';
			
			$fileName = 'EcrituresCompta.Reglements.Compta.'.date('Ymd_His').'.csv';
			$exportType = 'text/csv';
			if($isDebug)
				echo '<table border="1"><tr><td>';//debug
			elseif($setHeaders) {
				header("Content-Disposition:attachment;filename=$fileName");
				header("Content-Type:$exportType;charset=UTF-8");
				header("Expires: Mon, 31 Dec 2000 00:00:00 GMT" );
				header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT" );
				header("Cache-Control: post-check=0, pre-check=0", false );
			}
						
			$this->addExportRow( "**Compta\tEcritures", '');
			
			
			/*
			 * 1) Encaissements de chaque règlement
			 * Journal et compte : dépend du mode de règlement
			 * 1 ligne par règlement
			 * 	N° de pièce : numpiece
			 * 	Montant en crédit
			 * 	
			 * 2) 1 ligne par jour 
			 * 	Compte 512107 : compte bancaire NEF
			 * 	Compte 514000 : compte banque postale pour les chèques
			 * 	Section analytique : ?
			 * 	Au débit
			 * 
			 * ******
			 * ******
			 * Seuls les comptes en 6 ou en 7 on une section analytique
			 * 
			 */
			
			$prevDate = '';
			$totalPerDate = array();
			while($reglement = $db->fetch_row($result, false)){
				
				//ligne de facture de l'encaissement
				$codeAffaire = $reglement['codeaffaire'];
				$modeRegl = $reglement['rsnmoderegl'];
				
				$piece = $reglement['numpiece'];
				if(!$piece)
					$piece = $modeRegl . ' ? ' . self::formatDateForCogilog($reglement['dateoperation']) . '#' . $reglement['rsnreglementsid'];
				$piece = $reglement['origine'] . ' ' . $piece;
				
				$reglementCodeAnal = self::getCodeAffaireCodeAnal($codeAffaire);
				$reglementJournal = self::getCodeAffaireJournal($codeAffaire, $modeRegl);
				$compteVente = self::getInvoiceCompteVenteSolde($reglement); 
				$compteEnc = self::getModeReglCompteEncaissement($modeRegl); 
				$reglementAmount = self::formatAmountForCogilog($reglement['amount']);
				$date = self::formatDateForCogilog($reglement['dateregl']);
				if(!$reglement['invoicescount'])
					$basicSubject = $modeRegl . ' ' . $reglement['invoice_no'] . ' ' . $reglement['accountname'] . ' / ' . $date;
				elseif($reglement['invoicecount'] == 1)
					$basicSubject = $modeRegl . ' ' . $reglement['invoice_no'] . ' ' . $reglement['accountname'] . ' / ' . $reglement['invoicedate'];
				else
					$basicSubject = $modeRegl . ' ' . $piece . ' ' . $reglement['contactname'] . ' / ' . $date;
				$reglementSubject = $basicSubject . ($codeAffaire ? ' - ' . $codeAffaire : '');
				
				$key = $modeRegl . ($reglementCodeAnal ? '-' . $reglementCodeAnal : '') . '-' . $date;
			
				if(!$totalPerDate[$key])
					$totalPerDate[$key] = array(
									'codeAnal' => $reglementCodeAnal,
									'moderegl' => $modeRegl,
									'compte' => $compteEnc,
									'journal' => $reglementJournal,
									'date' => $date,
									'total' => 0.0
								);
				$totalPerDate[$key]['total'] += str_to_float($reglementAmount);
				
				/* Ligne d'encaissement */
				self::exportEncaissement($reglementJournal, $date, $piece, $compteVente, $reglementCodeAnal, $reglementSubject, $reglementAmount);
				
			}
			
			/* Lignes des encaissements par jour */
			foreach($totalPerDate as $key => $data){
				$total = $data['total'];
				$date = $data['date'];
				$modeRegl = $data['moderegl'];
				$compteEnc = $data['compte'];
				$journal = $data['journal'];
				//$journal = self::getCompteEncaissementJournal($compteEnc);
				if($compteEnc[0] === '7' || $compteEnc[0] === '6')
					$codeAnal = $data['codeAnal'];
				else	$codeAnal = '';
				$amount= self::formatAmountForCogilog($total);
				$piece = 'ENC-' . $key;
				$descriptif = 'Paiements par ' . $this->sanitizeExport($modeRegl). ' du ' . $date;
				$this->addExportRow($journal,
					$date
					.COLSEPAR.$piece
					.COLSEPAR.$compteEnc
					.COLSEPAR.$codeAnal
					.COLSEPAR.$descriptif
					.COLSEPAR.''
					.COLSEPAR.$amount
				);
				
			}
		}
		
		$this->echoExportBuffer();
		
		if($isDebug)
			echo '</table>';//debug
		
		return $fileName;
	}
	
	private function exportEncaissement($invoiceJournal, $date, $piece, $compte, $invoiceCodeAnal, $invoiceSubject, $invoiceAmount){
		/* Ligne d'encaissement du règlement */
		$this->addExportRow($invoiceJournal,
			$date
			.COLSEPAR.$this->sanitizeExport($piece)
			.COLSEPAR.$compte
			.COLSEPAR.$invoiceCodeAnal
			.COLSEPAR.$this->sanitizeExport($invoiceSubject)
			.COLSEPAR.''
			.COLSEPAR.''
			.COLSEPAR.$invoiceAmount
		);
	}
	
	private static function getModeReglCompteEncaissement($modeRegl){
		return getModeReglementInfo($modeRegl, 'compteencaissement');
	}
	private static function getModeReglJournal($modeRegl){
		return getModeReglementInfo($modeRegl, 'journalencaissement');
	}
	
	private static function getCodeAffaireCodeAnal($codeAffaire){
		switch(strtoupper($codeAffaire)){
		default :
			return strtoupper($codeAffaire);
		}
	}
	
	private static function getInvoiceCompteVenteSolde($reglementData){
		
		$accountType = $reglementData['account_type'];
		switch($accountType){
		case 'Depot-vente' :
			return '411DEP';
		
		default :
			$modeRegl = $reglementData['rsnmoderegl'];
			switch($invoiceData['invoicetypedossier']){
			case 'Facture de dépôt-vente' :
				return '411DEP';
		
			case 'Credit Invoice' ://Avoir
			case 'Avoir' :
			case 'Remboursement' :
				return '511200';//TODO
			default:
					
				return getModeReglementInfo($modeRegl, 'comptevente');
			}
		}
	}
	
	private static function getCodeAffaireJournal($codeAffaire, $modeRegl = false){
		return getModeReglementInfo($modeRegl, 'journalencaissement');
		//switch(strtoupper($codeAffaire)){
		//case 'PAYBOX' :
		//case 'PAYBOXP' :
		//	return 'BFC';
		//default :
		//	if($modeRegl)
		//		switch(strtoupper($modeRegl)){
		//		case 'PAYBOX' :
		//		case 'PAYBOXP' :
		//			return 'BFC';
		//		case 'ESPèCES' :
		//		case 'ESPÈCES' :
		//		case 'ESPECES' :
		//			return 'CS';
		//		}
		//	return 'LBP';
		//}
	}
	
	private static function formatDateForCogilog($myDate){
		$myDate = explode(' ', $myDate);
		$parts = explode('-', $myDate[0]);
		return $parts[2] . '/' . $parts[1] . '/' . $parts[0];
	}
	
	private static function formatAmountForCogilog($amount){
		return str_replace('.', ',', round($amount, 2));
	}
	
	//Marque les factures comme étant envoyées en compta (champ sent2compta)
	function validateSend2Compta(Vtiger_Request $request){
		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);
		
		$excludeRsnReglementStatus = array('Created', 'Cancelled');
		$selectedIds = $request->get('selected_ids');
		$query = 'UPDATE vtiger_rsnreglements
			SET vtiger_rsnreglements.sent2compta = NOW()
			, vtiger_rsnreglements.reglementstatus = ?
			WHERE vtiger_rsnreglements.rsnreglementsid IN ('. generateQuestionMarks( $selectedIds ) . ')
			AND vtiger_rsnreglements.sent2compta IS NULL
			AND NOT vtiger_rsnreglements.reglementstatus IN ('.generateQuestionMarks($excludeRsnReglementStatus).')
		';
		$params = array('Compta');
		$params = array_merge($params, $selectedIds);
		$params = array_merge($params, $excludeRsnReglementStatus);
		
		if($selectedIds)
			$this->storeFile($request);
		
		$db = PearDatabase::getInstance();
		$result = $db->pquery($query, $params);
		if(!$result){
			$db->echoError();
			echo "<pre>$query</pre>";
			var_dump($params);
			$response = new Vtiger_Response();
			$response->setError('Erreur de requête');
			$response->emit();
		}
	}
	
	//regénère le fichier et l'enregistre dans le répertoire storage
	function storeFile(Vtiger_Request $request){
		ob_start();
		$fileName = $this->downloadSend2Compta ($request, false);
		$fileContent =  ob_get_contents();
		ob_end_clean();
		global $root_directory;
		$firstDayOfMonth = strtotime(date("Y-m-01", time()));
		$weekNum = date("W") - date("W", $firstDayOfMonth) + (date('w', $firstDayOfMonth) === '0' ? 0 : 1);
		$path = $root_directory . '/storage/'.date('Y').'/'.date('F').'/week'.$weekNum;
		if(!file_exists($path))
			mkdir($path);
		file_put_contents($path.'/'.$fileName, $fileContent);
	}
	
	
}
