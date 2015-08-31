<?php


/* Phase de migration
 * Importation des liens entre ref4d et donateurWeb Id depuis le fichier provenant de 4D LienRefDonateurWeb
 * Crée des RSNDonateursWeb vides si ce n'est le contactId
 */
class RSNImportSources_ImportRSNDonateursWebLienRefFrom4D_View extends RSNImportSources_ImportRSNDonateursWebFromSite_View {
    

	/**
	 * Method to get the modules that are concerned by the import.
	 * @return array - An array containing concerned module names.
	 */
	public function getImportModules() {
		//La désactivation de l'import de contact fait qu'on ne voit pas ImportStatus depuis for_module=Contacts
		return array(/*'Contacts', */'RSNDonateursWeb');
	}

	/**
	 * Method to get the modules that are concerned by the import.
	 * @return array - An array containing concerned module names.
	 */
	public function getLockModules() {
		return array_merge($this->getImportModules(), array('ContactEmails'));
	}
	
	/**
	 * Method to get the source import label to display.
	 * @return string - The label.
	 */
	public function getSource() {
		return 'LBL_DONATEURSWEB_4D';
	}

	/**
	 * Method to default file enconding for this import.
	 * @return string - the default file encoding.
	 */
	public function getDefaultFileEncoding() {
		return 'ISO-8859-1';
	}

	/**
	 * Method that check if a line of the file is a client information line.
	 *  It assume that the line is a client information line only and only if the first data is a date.
	 * @param array $line : the data of the file line.
	 * @return boolean - true if the line is a client information line.
	 */
	function isRecordHeaderInformationLine($line) {
		if (sizeof($line) > 0 && is_numeric($line[0]) && is_numeric($line[1])) {
			return true;
		}

		return false;
	}
	
	

	/**
	 * Method to get the imported fields for the invoice module.
	 * @return array - the imported fields for the invoice module.
	 */
	function getRSNDonateursWebFields() {
		return array(
			'ref4d',
			'externalid',
			'actif',
			'isvalid',
			
			/* pre-import */
			'_contactid',
		);
	}

	/**
	 * Method to process to the import of a one donateur.
	 * @param $rsndonateurswebData : the data of the donateur to import
	 * @param RSNImportSources_Data_Action $importDataController : an instance of the import data controller.
	 */
	function importOneRSNDonateursWeb($rsndonateurswebData, $importDataController) {
					
		global $log;
		
		$contactId = $rsndonateurswebData[0]['_contactid'];
		if(false && $contactId) //'[Migration] post pre import
			$contactId = $this->getContactIdFromRef4D($rsndonateurswebData[0]['ref4d']);
		if ($contactId) {
			$sourceId = $rsndonateurswebData[0]['externalid'];
	
			//test sur externalid == $sourceId
			$query = "SELECT crmid
				FROM vtiger_rsndonateursweb
				JOIN vtiger_crmentity
					ON vtiger_rsndonateursweb.rsndonateurswebid = vtiger_crmentity.crmid
				WHERE deleted = FALSE
				AND contactid = ? 
				AND externalid = ? 
				LIMIT 1
			";
			$db = PearDatabase::getInstance();
			$result = $db->pquery($query, array($contactId, $sourceId));
			if($db->num_rows($result)){
				//already imported !!
				$row = $db->fetch_row($result, 0); 
				$entryId = $this->getEntryId("RSNDonateursWeb", $row['crmid']);
				foreach ($rsndonateurswebData as $rsndonateurswebLine) {
					$entityInfo = array(
						'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_SKIPPED,
						'id'		=> $entryId
					);
					
					//TODO update all with array
					$importDataController->updateImportStatus($rsndonateurswebLine[id], $entityInfo);
				}
			}
			else {
				$record = Vtiger_Record_Model::getCleanInstance('RSNDonateursWeb');
				$record->set('mode', 'create');
				foreach($rsndonateurswebData[0] as $fieldName => $value)
					if(!is_numeric($fieldName) && $fieldName != 'id')
						$record->set($fieldName, $value);
				
				$fieldName = 'contactid';
				$value = $contactId;
				$record->set($fieldName, $value);
				
				//$db->setDebug(true);
				$record->save();
				$rsndonateurswebId = $record->getId();

				if(!$rsndonateurswebId){
					//TODO: manage error
					echo "<pre><code>Impossible d'enregistrer le nouveau RSNDonateursWeb</code></pre>";
					foreach ($rsndonateurswebData as $rsndonateurswebLine) {
						$entityInfo = array(
							'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
						);
						
						//TODO update all with array
						$importDataController->updateImportStatus($rsndonateurswebLine[id], $entityInfo);
					}

					return false;
				}
				
				
				$entryId = $this->getEntryId("RSNDonateursWeb", $rsndonateurswebId);
				foreach ($rsndonateurswebData as $rsndonateurswebLine) {
					$entityInfo = array(
						'status'	=> RSNImportSources_Data_Action::$IMPORT_RECORD_CREATED,
						'id'		=> $entryId
					);
					$importDataController->updateImportStatus($rsndonateurswebLine[id], $entityInfo);
				}
				
				return $record;
			}
		} else {
			foreach ($rsndonateurswebData as $rsndonateurswebLine) {//TODO: remove duplicated code
				$entityInfo = array(
					'status'	=>	RSNImportSources_Data_Action::$IMPORT_RECORD_FAILED,
				);
				
				$importDataController->updateImportStatus($rsndonateurswebLine[id], $entityInfo);
			}

			return false;
		}

		return true;
	}
	
	/**
	 * Method that return the formated information of an invoice found in the file.
	 * @param $rsndonateursweb : the invoice data found in the file.
	 * @return array : the formated data of the invoice.
	 */
	function getRSNDonateursWebValues($rsndonateursweb) {
	//TODO end implementation of this method
		$rsndonateurswebValues = array();
		$rsndonateurswebHeader = array(
			'ref4d'		=> $rsndonateursweb['donInformations'][0],
			'externalid'		=> $rsndonateursweb['donInformations'][1],
			'actif'		=> $rsndonateursweb['donInformations'][2],
			'isvalid'		=> $rsndonateursweb['donInformations'][3],
		);
		return $rsndonateurswebHeader;
	}

	/**
	 * Method called after the file is processed.
	 *  This method must be overload in the child class.
	 */
	function postPreImportData() {
		// Pré-identifie les contacts
		
		RSNImportSources_Utils_Helper::setPreImportDataContactIdByRef4D(
			$this->user,
			'RSNDonateursWeb',
			'ref4d',
			'_contactid',
			/*$changeStatus*/ false
		);
	
		RSNImportSources_Utils_Helper::skipPreImportDataForMissingContactsByRef4D(
			$this->user,
			'RSNDonateursWeb',
			'_contactid'
		);
	}
	
}