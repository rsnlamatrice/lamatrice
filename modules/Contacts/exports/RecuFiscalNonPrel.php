<?php
/*+***********************************************************************************
	AV1511
 *************************************************************************************/

include("Numbers/Words.php");

class Contacts_RecuFiscalNonPrel_Export extends Export_ExportData_Action { // TMP € -> check encodage ....
	//tmp check mailing ou other address !!!!!!!
	function getExportStructure() {
		return array(
			"Numero Ordre" => function ($row) { return Contacts_RecuFiscalPrel_Export::getRecuFiscalDisplayNumber($row); }, //Reçu n° 2014 / 010375
			"Ref" => "contact_no",
			"Ligne2" => "grpnomllong",
			"Prenom-Nom" => function ($row) { return $row["firstname"] . " " . $row["lastname"]; },
			"Ligne3" => function ($row) { return Contacts_RecuFiscalNonPrel_Export::getAddressField($row, "street2"); },
			"Ligne4" => function ($row) { return Contacts_RecuFiscalNonPrel_Export::getAddressField($row, "street3"); },
			"Ligne5" => function ($row) { return Contacts_RecuFiscalNonPrel_Export::getAddressField($row, "street"); },
			"BP" => function ($row) { return Contacts_RecuFiscalNonPrel_Export::getAddressField($row, "pobox"); },
			"CP-Ville" => function ($row) { return Contacts_RecuFiscalNonPrel_Export::getAddressField($row, "zip") . " " .
													Contacts_RecuFiscalNonPrel_Export::getAddressField($row, "city"); },
			"Pays" => function ($row) { return Contacts_RecuFiscalNonPrel_Export::getAddressField($row, "country"); },
			"Salutations" => function ($row) { return Contacts_RecuFiscalNonPrel_Export::getSalutation($row); },
			"Dons" => function ($row) { return Contacts_RecuFiscalNonPrel_Export::getTotalDons($row) . " " . utf8_encode(chr(128)); },
			"Dons en lettres" => function ($row) { return Contacts_RecuFiscalNonPrel_Export::getTotalDonsLetter($row). " euros"; },
			"Dons après déduction" => function ($row) { return Contacts_RecuFiscalNonPrel_Export::getRealDons($row) . " " . utf8_encode(chr(128)); },
		);
	}
	
	function displayHeaderLine() {
		return true;
	}

	function getExportEncoding(Vtiger_Request $request) {
		return 'ISO-8859-1';
		//return 'ASCII';
	}

	function getExportFileName($request) {
		$moduleName = $request->get('source_module');
		return str_replace(' ','_',vtranslate($moduleName, $moduleName)) . "_Recu_fiscaux_sans_prel";
	}

	function getAddressToUse($row) {
		if ($row["use_address2_for_recu_fiscal"]) {
			return "other";
		}

		return "mailing";
	}

	function getAddressField($row, $field) {
		return $row[$this->getAddressToUse($row) . $field];
	}

	function getSalutation($row) {
		// var_dump($row);

		if ($row["firstname"] != "") {
			$return_value .= " " . $row["firstname"];
		}

		$return_value .= ",";

		return $return_value;
	}

	function getRecuFiscalDisplayNumber($row) {
		$current_year = date("Y") - 1;//TMP Date !!!
		$number = $this->getRecuFiscalNumber($row, $current_year);//TMP ....

		return "Reçu n° " . $current_year . " / " . $number;
	}

	function getDons($row) {//tmp requete executé pour chaque contact ...
		$db = PearDatabase::getInstance();
		$current_year = date("Y") - 1;
		$date_debut = $current_year . "-01-01";
		$date_fin = $current_year . "-12-31";
		$query = "SELECT SUM(vtiger_inventoryproductrel.listprice * vtiger_inventoryproductrel.quantity) total_dons
					FROM vtiger_inventoryproductrel
	                
					 
					JOIN vtiger_invoice ON vtiger_inventoryproductrel.id = vtiger_invoice.invoiceid 
					JOIN vtiger_service ON vtiger_inventoryproductrel.productid = vtiger_service.serviceid
					JOIN vtiger_crmentity vtiger_service_crmentity ON vtiger_service_crmentity.crmid = vtiger_service.serviceid
	                JOIN vtiger_crmentity vtiger_invoice_crmentity ON vtiger_invoice_crmentity.crmid = vtiger_invoice.invoiceid
	                
					WHERE vtiger_invoice_crmentity.deleted = false
					AND vtiger_service_crmentity.deleted = false
					AND vtiger_inventoryproductrel.quantity != 0 
					AND vtiger_inventoryproductrel.listprice != 0
					AND vtiger_invoice.invoicedate BETWEEN ? AND ?
					AND vtiger_service.servicecategory = 'Dons'
					AND vtiger_invoice.invoicestatus != 'Cancelled'
	                AND vtiger_invoice.accountid = ?";
				// echo $query;
				// exit;
	    $params = array($date_debut, $date_fin, $row["accountid"]);
		$result = $db->pquery($query, $params);
		return $db->fetchByAssoc($result, 0)["total_dons"];
	}

	function getPrel($row) {//tmp requete executé pour chaque contact ...
		$db = PearDatabase::getInstance();
		$current_year = date("Y") - 1;//TMP Year !
		$date_debut = $current_year . "-01-01";
		$date_fin = $current_year . "-12-31";
		$query = "SELECT DISTINCT SUM(vtiger_rsnprelvirement.montant) total_prelevements 
				FROM vtiger_rsnprelevements 
				JOIN vtiger_crmentity vtiger_rsnprelevements_crmentity ON vtiger_rsnprelevements_crmentity.crmid = vtiger_rsnprelevements.rsnprelevementsid 
				JOIN vtiger_rsnprelvirement ON vtiger_rsnprelvirement.rsnprelevementsid = vtiger_rsnprelevements.rsnprelevementsid
				JOIN vtiger_crmentity vtiger_rsnprelvirement_crmentity ON vtiger_rsnprelvirement_crmentity.crmid = vtiger_rsnprelvirement.rsnprelvirementid 
				WHERE vtiger_rsnprelevements_crmentity.deleted = 0
				AND vtiger_rsnprelvirement_crmentity.deleted = 0
				AND vtiger_rsnprelvirement.rsnprelvirstatus = 'Ok'
				AND vtiger_rsnprelvirement.dateexport BETWEEN ? AND ?
				AND vtiger_rsnprelevements.accountid = ?";

				// echo $query;
				// exit;
		$params = array($date_debut, $date_fin, $row["accountid"]);
		$result = $db->pquery($query, $params);
		return $db->fetchByAssoc($result, 0)["total_prelevements"];
	}

	function getTotalDons($row) {
		$total_dons = $this->getDons($row) + $this->getPrel($row);

		return round($total_dons, 2);
	}

	function getTotalDonsLetter($row) {
		$total_dons = $this->getTotalDons($row);
		
 
		// créer l'objet
		$nw = new Numbers_Words();
		 
		// convertir en chaîne
		//echo "600 en lettres donne " . $nw->toWords(600);
		return $nw->toWords($total_dons, 'fr');
	}

	function getRealDons($row) {
		$rate = 0.34;
		$total_dons = $this->getDons($row) + $this->getPrel($row);

		return (int) ($total_dons * $rate);
	}

	function getRecuFiscalDocumentRecordModel($current_year) {
		global $adb;
		$query = "SELECT notesid
			FROM vtiger_notes
			WHERE folderid = 17
			AND title LIKE '%" . $current_year . "'
			LIMIT 1";

		$result = $adb->pquery($query, $params);
		if($adb->num_rows($result) === 0){
			// Error !!!
		}
		$row = $adb->fetchByAssoc($result, 0, false);

		return Vtiger_Record_Model::getInstanceById($row['notesid'], 'Documents');
	}

	function getExportQuery($request) {//tmp ...
		$query = $parentQuery = parent::getExportQuery($request);

		$fromPos = strpos($parentQuery, 'FROM');//tmp attention si il y a plusieurs clauses FROM
		$wherePos = strpos($parentQuery, 'WHERE');//tmp attention si il y a plusieurs clauses WHERE
		$orderbyPos = strrpos($parentQuery, 'ORDER BY');//tmp attention si il y a plusieurs clauses ORDER BY

		$query = substr($parentQuery, 0, $fromPos) . ", vtiger_contactdetails.contactid " .
				 substr($parentQuery, $fromPos, ($wherePos - $fromPos)) . 
				 substr($parentQuery, $wherePos, ($orderbyPos - $wherePos)) . 
				 substr($parentQuery, $orderbyPos);

		// echo '<br/><br/><br/>' . $query;

		return $query;
	}

	function getRecuFiscalNumber($row, $year){
		$documentRecordModel = $this->getRecuFiscalDocumentRecordModel($year);
		$accountRecordModel = Vtiger_Record_Model::getInstanceById($row['accountid'], 'Accounts');;
		$contactRecordModel = Vtiger_Record_Model::getInstanceById($row['contactid'], 'Contacts');;

		$infos = $accountRecordModel->getInfosRecuFiscal($year, $documentRecordModel, $contactRecordModel);

		//tmp check if total dons = info total don !!!
		// var_dump($infos);
		// exit;

		// if ($infos["montant"] != $this->getTotalDons($row)) {
		// 	var_dump($infos["montant"]);
		// 	var_dump($this->getTotalDons($row));
		// 	var_dump($infos);
		// 	echo $row["contact_no"] . "=>" . $infos["montant"] . "!=" . $this->getTotalDons($row) . "<br/>";
		// 	exit();
		// }

		return $infos["recu_fiscal_num"];
	}
/////////////////////////////////////// TMP !!!! /////////////////////////////////////////////////////
	/*function getInfosRecuFiscal($year, $documentRecordModel, $contactRecordModel){
		
		global $adb;
		$query = "SELECT dateapplication, data
			FROM vtiger_senotesrel
			WHERE notesid = ?
			AND crmid IN ( ?, ?)
			AND IFNULL(data, '') LIKE '%Montant : %'
			ORDER BY dateapplication DESC
			LIMIT 1";
		$params = array($documentRecordModel->getId(), $this->getId(), $contactRecordModel->getId());//recu fiscal 2015 id / contact id  / account id
		$result = $adb->pquery($query, $params);
		if($adb->num_rows($result) === 0){
			return $this->createRecuFiscalRelation($year, $documentRecordModel);
		}
		$row = $adb->fetchByAssoc($result, 0, false);
		$dateApplication = $row['dateapplication'];
		$data = $row['data'];
		
		$infos = $this->extractInfosRecuFiscal($dateApplication, $data);
		
		return $this->createRecuFiscalRelation($year, $documentRecordModel, $infos);
	}*/
	
	/*function extractInfosRecuFiscal($dateApplication, $data){
		$dateApplication = preg_replace('/^(\d+)\-(\d+)\-(\d+)(\s.*)?$/', '$3/$2/$1', $dateApplication);
		
		if($data){
			//Montant : 
			$matches = array();
			if(! preg_match_all('/Montant\s*:\s*(?<montant>\d+([,\.]\d+)?)(\D|$)/i', $data, $matches)){
				die('Erreur regex Montant dans ' . print_r($data, true));
			}
			$montant = $matches['montant'][0];
			
			//Reçu n° : n'existe pas sur les anciens
			//je n'ai pas réussi à ne faire qu'un seul regex pour <montant> et <num>
			$matches = array();
			if(preg_match_all('/'.preg_quote('Reçu n° : ').'(?<num>\d+)/', $data, $matches))
				$numRecu = $matches['num'][0];
			else
				$numRecu = '';
		}
		return array(
			'montant' => $montant,
			'recu_fiscal_num' => $numRecu,
			'date_edition' => $dateApplication,
		);
	}*/
	
	/**
	 * Calcul du reçu fiscal et création de la relation
	 *
	 * @return true if needed (> 3€) and exists
	 */
	/*function createRecuFiscalRelation($year, $documentRecordModel, $existingInfos = false){
		
		global $adb;
		$query = "
		SELECT SUM(montant) AS montant
		FROM (
			SELECT SUM(vtiger_inventoryproductrel.quantity * vtiger_inventoryproductrel.listprice) AS montant
				FROM vtiger_inventoryproductrel
				JOIN vtiger_invoice
					ON vtiger_invoice.invoiceid = vtiger_inventoryproductrel.id
				JOIN vtiger_crmentity as vtiger_crmentity_invoice
					ON vtiger_invoice.invoiceid = vtiger_crmentity_invoice.crmid
				JOIN vtiger_service
					ON vtiger_service.serviceid = vtiger_inventoryproductrel.productid
				JOIN vtiger_crmentity as vtiger_crmentity_service
					ON vtiger_service.serviceid = vtiger_crmentity_service.crmid
				WHERE vtiger_crmentity_invoice.deleted = false
				AND vtiger_crmentity_service.deleted = false
				AND vtiger_service.servicecategory = 'Dons'
				AND vtiger_invoice.accountid = ?
				AND vtiger_invoice.invoicedate BETWEEN ? AND ?
				AND vtiger_invoice.invoicestatus != 'Cancelled'
			UNION
				SELECT SUM(vtiger_rsnprelvirement.montant)
				FROM vtiger_rsnprelvirement
				JOIN vtiger_crmentity as vtiger_crmentity_prelvir
					ON vtiger_rsnprelvirement.rsnprelvirementid = vtiger_crmentity_prelvir.crmid
				JOIN vtiger_rsnprelevements
					ON vtiger_rsnprelvirement.rsnprelevementsid = vtiger_rsnprelevements.rsnprelevementsid
				JOIN vtiger_crmentity as vtiger_crmentity_prelevements
					ON vtiger_rsnprelevements.rsnprelevementsid = vtiger_crmentity_prelevements.crmid
				WHERE vtiger_crmentity_prelvir.deleted = false
				AND vtiger_crmentity_prelevements.deleted = false
				AND vtiger_rsnprelvirement.rsnprelvirstatus = 'Ok'
				AND vtiger_rsnprelevements.prelvtype = 'Prélèvement périodique'
				AND vtiger_rsnprelevements.accountid = ?
				AND vtiger_rsnprelvirement.dateexport BETWEEN ? AND ?
		) _source
		";
		$params = array(
				$this->getId(),
				"$year-01-01",
				"$year-12-31 23:59:59",
				$this->getId(),
				"$year-01-01",
				"$year-12-31 23:59:59",
		);
		$result = $adb->pquery($query, $params);
		if(!$result){
			echo "<pre>$query</pre>";
			var_dump($params);
			$adb->echoError();
			return false;
		}
		$montant = $adb->query_result($result, 0, 0);
		if(!$montant){ // || $montant < 3 on génère quand même le reçu, on ne l'envoie pas 
			return false;
		}
		$montant = round($montant, 2);
		
		//Une relation existe déjà
		if($existingInfos && $existingInfos['recu_fiscal_num'] && $existingInfos['montant'] == $montant){
			//même montant => même n° de reçu
			$numRecu = $existingInfos['recu_fiscal_num'];
		}
		else
			$numRecu = $documentRecordModel->getNexRelatedCounterValue();
		
		//create relation or update if same date (current date)
		
		$data = "Montant : $montant €, Reçu n° : $numRecu";
		
		$query = "INSERT INTO vtiger_senotesrel (notesid, crmid, dateapplication, data)
			VALUES(?, ?, CURRENT_DATE, ?)
			ON DUPLICATE KEY UPDATE data = ?";
		$params = array(
			$documentRecordModel->getId(),
			$this->getId(),
			$data,
			$data,
		);
		$result = $adb->pquery($query, $params);
		if(!$result){
			echo "<pre>$query</pre>";
			var_dump($params);
			$adb->echoError();
			return false;
		}
		return array(
			'montant' => $montant,
			'recu_fiscal_num' => $numRecu,
			'date_edition' => date('d-m-Y'),
		);
	}*/
}